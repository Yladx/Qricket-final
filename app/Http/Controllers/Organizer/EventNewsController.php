<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventNews;
use App\Models\SavedEvent;
use App\Models\Notification;
use App\Events\EventNewsPublished;
use App\Notifications\EventNewsNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EventNewsController extends Controller
{
    /**
     * Check if user has access to manage news for this event
     */
    private function hasNewsAccess(Event $event): bool
    {
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event->user_id === Auth::id();
        $isAdminEvent = $event->user_id === null && $isAdmin;
        
        if ($isAdmin || $isOwner || $isAdminEvent) {
            return true;
        }
        
        // Check if user is staff with can_manage_news permission
        // Only can_manage_news or organizer role can manage news (can_manage_event does NOT include news)
        $staff = $event->staff()->where('user_id', Auth::id())->first();
        if (!$staff) {
            return false;
        }
        
        // Allow if staff has can_manage_news or organizer role
        return ($staff->can_manage_news ?? false) || ($staff->role === 'organizer');
    }

    /**
     * Generate a reliable R2 URL for the given path
     */
    private function getR2Url(string $path, int $eventId, int $newsId): string
    {
        // Use R2 Cloudflare Storage with signed URLs (like the example you provided)
        $disk = Storage::disk('r2');
        
        // Generate a temporary signed URL (valid for 1 hour)
        $url = $disk->temporaryUrl($path, now()->addHour());
        
        \Log::info('Generated R2 signed URL', [
            'path' => $path,
            'url' => $url,
            'event_id' => $eventId,
            'news_id' => $newsId
        ]);
        
        return $url;
    }

    /**
     * Send notifications to users who have saved this event or bought tickets
     */
    private function notifyEventSubscribers(EventNews $news, Event $event)
    {
        // Get all users who have saved this event
        $savedEvents = SavedEvent::where('event_id', $event->id)->with('user')->get();
        $savedUserIds = $savedEvents->pluck('user_id')->toArray();
        
        // Get all users who bought tickets for this event (using correct status)
        $ticketBuyerIds = \App\Models\TicketPurchase::where('event_id', $event->id)
            ->where('status', 'paid')
            ->distinct()
            ->pluck('user_id')
            ->toArray();
        
        // Combine both groups and remove duplicates
        $userIds = array_unique(array_merge($savedUserIds, $ticketBuyerIds));
        
        $notificationsCreated = 0;
        
        // Send notification to each user
        foreach ($userIds as $userId) {
            try {
                $user = \App\Models\User::find($userId);
                if (!$user) continue;
                
                // Create in-app notification
                Notification::create([
                    'user_id' => $userId,
                    'type' => 'event',
                    'action' => 'news',
                    'reference_id' => $event->id,
                    'title' => $news->title,
                    'message' => $news->title,
                    'is_read' => false,
                    'data' => [
                        'event_id' => $event->id,
                        'event_title' => $event->title,
                        'news_id' => $news->id,
                        'news_title' => $news->title,
                    ],
                ]);
                
                // Send email notification (this will also store in database via toArray method)
                $user->notify(new EventNewsNotification($event, $news));
                
                // Broadcast real-time notification
                event(new EventNewsPublished($userId, $news, $event));
                
                $notificationsCreated++;
            } catch (\Exception $e) {
                \Log::error('Failed to send news notification to user', [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        \Log::info('Event news notifications sent', [
            'event_id' => $event->id,
            'news_id' => $news->id,
            'notifications_sent' => $notificationsCreated,
            'saved_users_count' => count($savedUserIds),
            'ticket_buyers_count' => count($ticketBuyerIds),
            'total_users_notified' => count($userIds),
            'news_title' => $news->title
        ]);
    }

    public function index(Event $event)
    {
        // Check if user has access to manage news for this event
        if (!$this->hasNewsAccess($event)) {
            abort(403, 'Unauthorized access to this event.');
        }

        $news = $event->news()->latest()->paginate(10);
        
        // Regenerate signed URLs for existing images
        $news->getCollection()->transform(function ($article) use ($event) {
            if (!empty($article->attachments)) {
                $article->attachments = collect($article->attachments)->map(function ($url) use ($event, $article) {
                    // Extract filename from URL
                    $filename = basename(parse_url($url, PHP_URL_PATH));
                    $path = "events/{$event->id}/news/{$article->id}/{$filename}";
                    
                    // Generate new signed URL
                    return Storage::disk('r2')->temporaryUrl($path, now()->addHour());
                })->toArray();
            }
            return $article;
        });

        // Determine layout type
        $isAdmin = Auth::guard('admin')->check();
        $layoutType = $isAdmin ? 'admin' : 'organizer';

        return view('organizer.events.news.index', compact('event', 'news', 'isAdmin', 'layoutType'));
    }

    public function create(Event $event)
    {
        // Check if user has access to manage news for this event
        if (!$this->hasNewsAccess($event)) {
            abort(403, 'Unauthorized access to this event.');
        }

        // Determine layout type
        $isAdmin = Auth::guard('admin')->check();
        $layoutType = $isAdmin ? 'admin' : 'organizer';

        return view('organizer.events.news.create', compact('event', 'isAdmin', 'layoutType'));
    }

    public function store(Request $request, Event $event)
    {
        // Check if user has access to manage news for this event
        if (!$this->hasNewsAccess($event)) {
            abort(403, 'Unauthorized access to this event.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255', new \App\Rules\NoProfanity()],
            'body' => ['required', 'string', new \App\Rules\NoProfanity()],
            'visibility' => 'required|in:public,ticket_buyers,saved_users',
            'publish_option' => 'required|in:draft,publish,schedule',
            'scheduled_at' => 'nullable|date|after:now',
            'images' => 'nullable|array',
            'images.*' => 'file|image|mimes:jpeg,jpg,png,webp|max:8192',
        ]);

        // Set publish date based on option
        $publishData = [
            'event_id' => $event->id,
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'body' => $validated['body'],
            'visibility' => $validated['visibility'],
            'published_at' => null,
        ];

        switch ($validated['publish_option']) {
            case 'publish':
                $publishData['published_at'] = now();
                break;
            case 'schedule':
                if ($validated['scheduled_at']) {
                    $publishData['published_at'] = $validated['scheduled_at'];
                }
                break;
            case 'draft':
            default:
                // Keep published_at as null for draft
                break;
        }

        $news = EventNews::create($publishData);

        // Store uploaded images to R2 under news/{newsId}
        if ($request->hasFile('images')) {
            $stored = [];
            foreach ($request->file('images') as $file) {
                if (!$file->isValid()) { continue; }
                $ext = $file->getClientOriginalExtension() ?: 'jpg';
                $fileName = uniqid('img_').'.'.$ext;
                $storedPath = Storage::disk('r2')->putFileAs('events/'.$event->id.'/news/'.$news->id, $file, $fileName); // events/{eventId}/news/{newsId}
                $url = $this->getR2Url($storedPath, $event->id, $news->id);
                
                \Log::info('News image upload (create)', [
                    'event_id' => $event->id,
                    'news_id' => $news->id,
                    'stored_path' => $storedPath,
                    'generated_url' => $url,
                    'r2_url_config' => config('filesystems.disks.r2.url')
                ]);
                
                $stored[] = $url;
            }
            if (!empty($stored)) {
                $existing = (array)($news->attachments ?? []);
                $news->attachments = array_values(array_unique(array_merge($existing, $stored)));
                $news->save();
            }
        }

        // Send notifications to saved users and ticket buyers if news is published immediately
        if ($validated['publish_option'] === 'publish' && $news->published_at) {
            $this->notifyEventSubscribers($news, $event);
        }

        $message = match($validated['publish_option']) {
            'publish' => 'News article published successfully.',
            'schedule' => 'News article scheduled successfully.',
            default => 'News article saved as draft successfully.'
        };

        return redirect()->route('organizer.events.news.index', $event)
            ->with('success', $message);
    }

    public function edit(Event $event, EventNews $news)
    {
        // Check if user has access to manage news for this event
        if (!$this->hasNewsAccess($event)) {
            abort(403, 'Unauthorized access to this event.');
        }

        // Regenerate signed URLs for existing images
        if (!empty($news->attachments)) {
            $news->attachments = collect($news->attachments)->map(function ($url) use ($event, $news) {
                // Extract filename from URL
                $filename = basename(parse_url($url, PHP_URL_PATH));
                $path = "events/{$event->id}/news/{$news->id}/{$filename}";
                
                // Generate new signed URL
                return Storage::disk('r2')->temporaryUrl($path, now()->addHour());
            })->toArray();
        }

        // Determine layout type
        $isAdmin = Auth::guard('admin')->check();
        $layoutType = $isAdmin ? 'admin' : 'organizer';

        return view('organizer.events.news.edit', compact('event', 'news', 'isAdmin', 'layoutType'));
    }

    public function update(Request $request, Event $event, EventNews $news)
    {
        // Check if user has access to manage news for this event
        if (!$this->hasNewsAccess($event)) {
            abort(403, 'Unauthorized access to this event.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255', new \App\Rules\NoProfanity()],
            'body' => ['required', 'string', new \App\Rules\NoProfanity()],
            'visibility' => 'required|in:public,ticket_buyers,saved_users',
            'publish_option' => 'required|in:draft,publish,schedule',
            'scheduled_at' => 'nullable|date|after:now',
            'images' => 'nullable|array',
            'images.*' => 'file|image|mimes:jpeg,jpg,png,webp|max:8192',
            'images_to_delete' => 'nullable|string',
        ]);

        // Handle image deletions first
        if ($request->images_to_delete) {
            $existing = (array)($news->attachments ?? []);
            $indicesToDelete = array_map('intval', explode(',', $request->images_to_delete));
            $deletedCount = 0;
            
            foreach ($indicesToDelete as $index) {
                if (isset($existing[$index])) {
                    // Extract filename from URL and delete from R2
                    $url = $existing[$index];
                    $filename = basename(parse_url($url, PHP_URL_PATH));
                    $path = "events/{$event->id}/news/{$news->id}/{$filename}";
                    
                    if (Storage::disk('r2')->exists($path)) {
                        Storage::disk('r2')->delete($path);
                        $deletedCount++;
                        
                        \Log::info('News image deleted from R2', [
                            'event_id' => $event->id,
                            'news_id' => $news->id,
                            'path' => $path,
                            'filename' => $filename
                        ]);
                    }
                    
                    // Remove from array
                    unset($existing[$index]);
                }
            }
            
            // Reindex array and update
            $existing = array_values($existing);
            $news->attachments = $existing;
            $news->save();
            
            if ($deletedCount > 0) {
                \Log::info("Deleted {$deletedCount} images from R2 for news {$news->id}");
            }
        }

        // Track if this is a new publication (was draft, now being published)
        $wasAlreadyPublished = !empty($news->published_at);
        
        // Set publish date based on option
        $updateData = [
            'title' => $validated['title'],
            'body' => $validated['body'],
            'visibility' => $validated['visibility'],
        ];

        switch ($validated['publish_option']) {
            case 'publish':
                $updateData['published_at'] = now();
                break;
            case 'schedule':
                if ($validated['scheduled_at']) {
                    $updateData['published_at'] = $validated['scheduled_at'];
                }
                break;
            case 'draft':
            default:
                $updateData['published_at'] = null;
                break;
        }

        $news->update($updateData);
        
        // Send notifications if this is newly published (wasn't published before)
        if (!$wasAlreadyPublished && $updateData['published_at']) {
            $this->notifyEventSubscribers($news, $event);
        }

        // Append any newly uploaded images
        if ($request->hasFile('images')) {
            $existing = (array)($news->attachments ?? []);
            $stored = [];
            foreach ($request->file('images') as $file) {
                if (!$file->isValid()) { continue; }
                $ext = $file->getClientOriginalExtension() ?: 'jpg';
                $fileName = uniqid('img_').'.'.$ext;
                $storedPath = Storage::disk('r2')->putFileAs('events/'.$event->id.'/news/'.$news->id, $file, $fileName);
                $url = $this->getR2Url($storedPath, $event->id, $news->id);
                
                \Log::info('News image upload (update)', [
                    'event_id' => $event->id,
                    'news_id' => $news->id,
                    'stored_path' => $storedPath,
                    'generated_url' => $url,
                    'r2_url_config' => config('filesystems.disks.r2.url')
                ]);
                
                $stored[] = $url;
            }
            if (!empty($stored)) {
                $news->attachments = array_values(array_unique(array_merge($existing, $stored)));
                $news->save();
            }
        }

        $message = match($validated['publish_option']) {
            'publish' => 'News article published successfully.',
            'schedule' => 'News article scheduled successfully.',
            default => 'News article saved as draft successfully.'
        };

        return redirect()->route('organizer.events.news.index', $event)
            ->with('success', $message);
    }

    public function destroy(Event $event, EventNews $news)
    {
        // Check if user has access to manage news for this event
        if (!$this->hasNewsAccess($event)) {
            abort(403, 'Unauthorized access to this event.');
        }

        $news->delete();

        return redirect()->route('organizer.events.news.index', $event)
            ->with('success', 'News article deleted successfully.');
    }

    public function publish(Event $event, EventNews $news)
    {
        // Check if user has access to manage news for this event
        if (!$this->hasNewsAccess($event)) {
            abort(403, 'Unauthorized access to this event.');
        }

        $news->update(['published_at' => now()]);

        // Send notifications to saved users and ticket buyers when news is published
        $this->notifyEventSubscribers($news, $event);

        return back()->with('success', 'News article published successfully.');
    }

    /**
     * Serve news images as fallback if R2 URLs fail
     */
    public function serveImage(Request $request, Event $event, EventNews $news, string $filename)
    {
        // Check if user has access to manage news for this event
        if (!$this->hasNewsAccess($event)) {
            abort(403, 'Unauthorized access to this event.');
        }

        $path = "events/{$event->id}/news/{$news->id}/{$filename}";
        
        \Log::info('Serving news image', [
            'event_id' => $event->id,
            'news_id' => $news->id,
            'filename' => $filename,
            'path' => $path,
            'exists' => Storage::disk('r2')->exists($path)
        ]);
        
        if (!Storage::disk('r2')->exists($path)) {
            abort(404, 'Image not found');
        }

        $stream = Storage::disk('r2')->readStream($path);
        $mimeType = Storage::disk('r2')->mimeType($path);
        
        return response()->stream(function() use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
