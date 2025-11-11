<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventTicketType;
use App\Models\EventActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    /**
     * Display a listing of events for organizers.
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $isAdmin = Auth::guard('admin')->check();
        
        $query = Event::with(['ticketTypes', 'staff', 'user'])
            ->where(function($q) use ($userId, $isAdmin) {
                // Owner can see all their events (including drafts)
                $q->where('user_id', $userId)
                  // Admin can see admin-created events (user_id = null)
                  ->orWhere(function($qq) use ($isAdmin) {
                      if ($isAdmin) {
                          $qq->whereNull('user_id');
                      }
                  })
                  // Staff can only see published events
                  ->orWhere(function($qq) use ($userId) {
                      $qq->where('status', 'published')
                         ->whereHas('staff', function($sub) use ($userId) {
                             $sub->where('user_id', $userId);
                         });
                  });
            });

        // Optional filters from UI
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        if ($request->filled('category')) {
            $query->where('category', $request->get('category'));
        }
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%'.$request->get('search').'%')
                  ->orWhere('location', 'like', '%'.$request->get('search').'%');
            });
        }

        $events = $query->latest()->paginate(12);

        $categories = ['conference', 'workshop', 'seminar', 'meetup', 'webinar', 'exhibition', 'festival', 'sports', 'music', 'art', 'technology', 'business', 'education', 'health', 'charity'];

        return view('organizer.events', compact('events', 'categories'));
    }

    /**
     * Display a listing of public events.
     */
    public function publicIndex()
    {
        $events = Event::with(['ticketTypes', 'user'])
            ->whereIn('status', ['published', 'approved'])
            ->latest()
            ->paginate(12);

        $featuredEvents = $this->getFeaturedEvents(3);
        $recentlyViewed = $this->getRecentlyViewedEventsForUser(6);

        return view('events.browse', compact('events', 'featuredEvents', 'recentlyViewed'));
    }

    // browse() moved to EventBrowsingController

    /**
     * Update event status (organizer can cancel pending_approval back to draft).
     */
    public function updateStatus(Request $request, Event $event)
    {
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = (int)$event->user_id === (int)auth()->id();
        $isAdminEvent = $event->user_id === null && $isAdmin;
        
        if (!auth()->check() || (!$isOwner && !$isAdminEvent)) {
            abort(403);
        }
        $request->validate([
            'status' => 'required|in:draft',
        ]);
        // Only allow cancelling from pending_approval to draft
        if ($event->status === 'pending_approval' && $request->input('status') === 'draft') {
            $event->status = 'draft';
            $event->save();
            // Mark the latest verification request as cancelled
            try {
                $latestRequest = \App\Models\EventVerificationRequest::where('event_id', $event->id)
                    ->where('status', 'pending')
                    ->latest('id')
                    ->first();
                if ($latestRequest) {
                    $latestRequest->update([
                        'status' => 'cancelled',
                        'reviewed_at' => now(),
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to cancel verification request when reverting to draft', [
                    'event_id' => $event->id,
                    'error' => $e->getMessage()
                ]);
            }
            return back()->with('success', 'Approval request cancelled. Event set to draft.');
        }
        return back()->with('error', 'Invalid status change.');
    }

    /**
     * Show the form for creating a new event.
     */
    public function create()
    {
        return view('events.create');
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'start_date' => 'required|date|after:now',
            'end_date' => 'nullable|date|after:start_date',
            'location' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->except(['poster', 'thumbnail']);

        // Handle file uploads
        if ($request->hasFile('poster')) {
            $data['poster'] = $request->file('poster')->store('events/posters', 'public');
        }

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('events/thumbnails', 'public');
        }

        // Set user_id based on who is creating the event
        if (Auth::guard('admin')->check()) {
            $data['user_id'] = null; // Admin-created event
        } else {
            $data['user_id'] = Auth::id();
            
            // Capture subscription_id for tracking which subscription was active at event creation
            $user = Auth::user();
            if ($user) {
                $currentSubscription = $user->currentSubscription();
                if ($currentSubscription) {
                    $data['subscription_id'] = $currentSubscription->id;
                }
            }
        }

        $event = Event::create($data);

        // Log activity
        EventActivityLog::create([
            'event_id' => $event->id,
            'user_id' => Auth::id(),
            'action' => 'event_created',
            'description' => 'Event created: ' . $event->title,
        ]);

        return redirect()->route('events.show', $event)
            ->with('success', 'Event created successfully.');
    }

    /**
     * Display the specified event.
     */
    public function show(Request $request, Event $event)
    {
        // Log event view (nullable user allowed)
        try {
            EventActivityLog::log(
                $event->id,
                $this->getActivityLogUserId(),
                'event_viewed',
                'Event viewed: ' . $event->title,
                [
                    'referrer' => (string) $request->headers->get('referer'),
                    'section' => $request->get('section', 'info')
                ]
            );
        } catch (\Throwable $e) { /* ignore */ }

        // Determine a context-aware back URL based on the referrer (organizer vs public)
        $referer = $request->headers->get('referer');
        $defaultBackUrl = route('events.browse');
        $backUrl = $defaultBackUrl; // Always default to browsing page
        
        // Determine which section is being requested
        $section = $request->get('section', 'info'); // Default to 'info' if no parameter
        
        // Always load basic relationships
        $event->load([
            'user', // Event creator
            'ticketTypes', 
            'staff.user', // Staff members with their user data
        ]);
        
        // Load section-specific data
        $layoutData = null;
        $news = null;
        
        if ($section === 'info') {
            // Load layout data from R2 if available (same approach as LayoutController)
            if ($event->layout_path) {
                $layoutData = $this->loadLayoutFromR2($event->layout_path);
            }
            
            // Load layout data for debugging
            \Log::info('Event Show - Layout Data Debug', [
                'event_id' => $event->id,
                'layout_path' => $event->layout_path,
                'layout_data_type' => gettype($layoutData),
                'layout_data_count' => is_array($layoutData) ? count($layoutData) : 'N/A',
                'layout_data_preview' => is_array($layoutData) ? array_slice($layoutData, 0, 2) : $layoutData
            ]);
        } elseif ($section === 'news') {
            // Load news data for news section
            // Only show published news (published_at <= now())
            $event->load('news');
            $news = $event->news()
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->orderBy('published_at', 'desc')
                ->get();
            
            // Regenerate signed URLs for existing images (same logic as EventNewsController)
            $news->transform(function ($article) use ($event) {
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
        }
        
        // Get related events (only load if not requesting news section to optimize performance)
        $relatedEvents = null;
        if ($section !== 'news') {
        $relatedEvents = Event::where('category', $event->category)
            ->where('id', '!=', $event->id)
            ->whereIn('status', ['published', 'approved'])
            ->limit(4)
            ->get();
        }

        return view('events.show', compact('event', 'layoutData', 'relatedEvents', 'news', 'section', 'backUrl'));
    }

    /**
     * Get events recently viewed by the current user (from activity logs).
     */
    public function getRecentlyViewedEventsForUser(int $limit = 6)
    {
        try {
            if (!auth()->check()) { return collect(); }
            $userId = (int) auth()->id();
            $eventIds = \App\Models\EventActivityLog::where('user_id', $userId)
                ->where('action', 'event_viewed')
                ->orderByDesc('created_at')
                ->limit(50)
                ->pluck('event_id')
                ->filter()
                ->unique()
                ->take($limit)
                ->all();
            if (empty($eventIds)) { return collect(); }
            return Event::whereIn('id', $eventIds)->with(['user'])->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    /**
     * Compute featured upcoming events based on sales (primary) and views (secondary).
     */
    public function getFeaturedEvents(int $limit = 3)
    {
        try {
            $windowDays = 30;
            $now = now();
            $windowFrom = $now->copy()->subDays($windowDays);

            // Candidate events: upcoming and published/approved
            $candidateIds = Event::whereIn('status', ['published', 'approved'])
                ->where('start_date', '>=', $now)
                ->pluck('id');

            if ($candidateIds->isEmpty()) { return collect(); }

            // Sales (paid tickets) in window
            $sales = \App\Models\EventTicketInformation::select('event_id', DB::raw('COUNT(*) as tickets'))
                ->whereIn('event_id', $candidateIds)
                ->whereHas('ticketPurchase', function($q) use ($windowFrom){ $q->where('status','paid')->where('paid_at','>=',$windowFrom); })
                ->groupBy('event_id')
                ->pluck('tickets','event_id');

            // Views (event_viewed) in window
            $views = \App\Models\EventActivityLog::whereIn('event_id', $candidateIds)
                ->where('action','event_viewed')
                ->where('created_at','>=',$windowFrom)
                ->select('event_id', DB::raw('COUNT(*) as v'))
                ->groupBy('event_id')
                ->pluck('v','event_id');

            $maxTickets = max(1, (int) ($sales->max() ?? 0));
            $maxViews = max(1, (int) ($views->max() ?? 0));

            // Build scores
            $events = Event::whereIn('id', $candidateIds)->with(['user'])->get();
            $scored = $events->map(function($e) use ($sales,$views,$maxTickets,$maxViews,$now,$windowDays){
                $t = (int) ($sales[$e->id] ?? 0);
                $v = (int) ($views[$e->id] ?? 0);
                $ticketNorm = $t / $maxTickets; // 0..1
                $viewNorm = $v / $maxViews;     // 0..1
                $daysUntil = max(0, $now->diffInDays(optional($e->start_date)));
                $recency = 1 - min(1, $daysUntil / $windowDays);
                $score = 0.6 * $ticketNorm + 0.3 * $viewNorm + 0.1 * $recency;
                $e->featured_score = $score;
                $e->featured_tickets = $t;
                $e->featured_views = $v;
                return $e;
            })->sortByDesc('featured_score')->take($limit)->values();

            return $scored;
        } catch (\Throwable $e) {
            return collect();
        }
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(Event $event)
    {
        return view('events.edit', compact('event'));
    }

    /**
     * Update the specified event in storage.
     */
    public function update(Request $request, Event $event)
    {
        // Check authorization - allow admin to update admin-created events (user_id = 0) or user's own events
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event->user_id === Auth::id();
        $isAdminEvent = $event->user_id === null && $isAdmin;
        
        if (!$isOwner && !$isAdminEvent) {
            abort(403, 'Unauthorized access to this event.');
        }
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'location' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->except(['poster', 'thumbnail']);

        // Handle file uploads
        if ($request->hasFile('poster')) {
            // Delete old poster
            if ($event->poster) {
                Storage::disk('public')->delete($event->poster);
            }
            $data['poster'] = $request->file('poster')->store('events/posters', 'public');
        }

        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($event->thumbnail) {
                Storage::disk('public')->delete($event->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('events/thumbnails', 'public');
        }

        $event->update($data);

        // Log activity
        EventActivityLog::create([
            'event_id' => $event->id,
            'user_id' => Auth::id(),
            'action' => 'event_updated',
            'description' => 'Event updated: ' . $event->title,
        ]);

        return redirect()->route('events.show', $event)
            ->with('success', 'Event updated successfully.');
    }

    /**
     * Remove the specified event from storage.
     */
    public function destroy(Event $event)
    {
        // Check authorization - allow admin to delete admin-created events (user_id = 0) or user's own events
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event->user_id === Auth::id();
        $isAdminEvent = $event->user_id === null && $isAdmin;
        
        if (!$isOwner && !$isAdminEvent) {
            abort(403, 'Unauthorized access to this event.');
        }
        
        // Delete associated files
        if ($event->poster) {
            Storage::disk('public')->delete($event->poster);
        }
        if ($event->thumbnail) {
            Storage::disk('public')->delete($event->thumbnail);
        }

        // Log activity before deletion
        EventActivityLog::create([
            'event_id' => $event->id,
            'user_id' => Auth::id(),
            'action' => 'event_deleted',
            'description' => 'Event deleted: ' . $event->title,
        ]);

        $event->delete();

        return redirect()->route('events.index')
            ->with('success', 'Event deleted successfully.');
    }

    /**
     * Register for an event.
     */
    public function register(Request $request, Event $event)
    {
        $validator = Validator::make($request->all(), [
            'ticket_type_id' => 'required|exists:event_ticket,id',
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $ticketType = EventTicketType::findOrFail($request->ticket_type_id);
        
        // Check if enough tickets are available
        if ($ticketType->sold_count + $request->quantity > $ticketType->quantity) {
            return back()->withErrors(['quantity' => 'Not enough tickets available.'])->withInput();
        }

        $totalAmount = $ticketType->price * $request->quantity;

        $registration = EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => Auth::id(),
            'ticket_type_id' => $ticketType->id,
            'quantity' => $request->quantity,
            'total_amount' => $totalAmount,
            'status' => 'confirmed',
            'payment_status' => 'pending',
        ]);

        // Update sold count
        $ticketType->increment('sold_count', $request->quantity);

        // Log activity
        EventActivityLog::create([
            'event_id' => $event->id,
            'user_id' => Auth::id(),
            'action' => 'event_registered',
            'description' => 'Registered for event: ' . $event->title,
        ]);

        return redirect()->route('events.show', $event)
            ->with('success', 'Registration successful! Please complete payment.');
    }

    /**
     * Get events by category.
     */
    public function category($category)
    {
        $events = Event::where('category', $category)
            ->whereIn('status', ['published', 'approved'])
            ->latest()
            ->paginate(12);

        return view('events.category', compact('events', 'category'));
    }

    /**
     * Search events.
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        $category = $request->get('category');
        $location = $request->get('location');

        $events = Event::whereIn('status', ['published', 'approved'])
            ->when($query, function ($q) use ($query) {
                return $q->where('title', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
            })
            ->when($category, function ($q) use ($category) {
                return $q->where('category', $category);
            })
            ->when($location, function ($q) use ($location) {
                return $q->where('location', 'like', "%{$location}%");
            })
            ->latest()
            ->paginate(12);

        return view('events.search', compact('events', 'query', 'category', 'location'));
    }

    /**
     * Get featured events.
     */
    public function featured()
    {
        $events = Event::whereIn('status', ['published', 'approved'])
            ->whereHas('ticketTypes', function ($query) {
                $query->where('is_featured', true);
            })
            ->latest()
            ->limit(8)
            ->get();

        return view('events.featured', compact('events'));
    }

    /**
     * Get upcoming events.
     */
    public function upcoming()
    {
        $events = Event::whereIn('status', ['published', 'approved'])
            ->where('start_date', '>', now())
            ->orderBy('start_date')
            ->paginate(12);

        return view('events.upcoming', compact('events'));
    }

    /**
     * Export event data.
     */
    public function export(Event $event)
    {
        // This would implement CSV/Excel export functionality
        // For now, just return a simple response
        return response()->json([
            'message' => 'Export functionality would be implemented here',
            'event' => $event->title
        ]);
    }

    /**
     * Render layout component for public event show pages.
     */
    public function renderLayout(Request $request, $layout)
    {
        try {
            $eventData = $request->input('eventData', []);
            $details = $request->input('details', []);
            
            // Get event ID from request
            $eventId = $request->get('id');
            
            if (!$eventId) {
                return response()->json(['error' => 'Event ID required'], 400);
            }
            
            // Load event data if not provided
            if (empty($eventData)) {
                $event = Event::findOrFail($eventId);
                $eventData = $event->toArray();
            }
            // Path converter available for both template path and fallback rendering
            $convertPath = function($path) use ($eventId) {
                if (!$path) return $path;
                if (str_starts_with($path, 'http')) return $path;
                $fullPath = str_contains($path, 'events/') ? $path : "events/{$eventId}/layoutimages/" . ltrim($path, '/');
                try { return Storage::disk('r2')->temporaryUrl($fullPath, now()->addHour()); } catch (\Throwable $t) { return config('filesystems.disks.r2.url') . '/' . $fullPath; }
            };
            
            // Map premium aliases to paths under premium/
            $viewPath = "events.layouts.{$layout}";
            if (str_starts_with($layout, 'premium-')) {
                $map = [
                    'premium-hero-spotlight' => 'events.layouts.premium.hero-spotlight',
                    'premium-gallery-mosaic' => 'events.layouts.premium.gallery-mosaic',
                    'premium-schedule-timeline' => 'events.layouts.premium.schedule-timeline',
                    'premium-testimonials-slider' => 'events.layouts.premium.testimonials-slider',
                    'premium-sponsors-grid' => 'events.layouts.premium.sponsors-grid',
                ];
                if (isset($map[$layout])) {
                    $viewPath = $map[$layout];
                }
            }
            
            // For tickets-pricing, load model with tickets so the Blade can render DB-driven list
            $eventModel = null;
            if ($layout === 'tickets-pricing') {
                try { $eventModel = Event::with('eventTickets')->find((int)$eventId); } catch (\Throwable $t) { $eventModel = null; }
            }

            // Convert relative image paths in details to signed/public URLs similar to organizer preview
            $convertPath = function($path) use ($eventId) {
                if (!$path) return $path;
                if (str_starts_with($path, 'http')) return $path;
                $fullPath = str_contains($path, 'events/') ? $path : "events/{$eventId}/layoutimages/" . ltrim($path, '/');
                try { 
                    return Storage::disk('r2')->temporaryUrl($fullPath, now()->addHour()); 
                } catch (\Throwable $t) { 
                    return config('filesystems.disks.r2.url') . '/' . $fullPath; 
                }
            };
            
            // Convert single image fields
            if (isset($details['image'])) $details['image'] = $convertPath($details['image']);
            if (isset($details['image_url'])) $details['image_url'] = $convertPath($details['image_url']);
            if (isset($details['image1'])) $details['image1'] = $convertPath($details['image1']);
            if (isset($details['image2'])) $details['image2'] = $convertPath($details['image2']);
            
            // Convert background_image and background_image_path for premium-hero-spotlight
            if (isset($details['background_image'])) $details['background_image'] = $convertPath($details['background_image']);
            if (isset($details['background_image_path'])) {
                $details['background_image'] = $convertPath($details['background_image_path']);
            }
            
            // Handle flattened speaker image format (speakers_0_image, speakers_1_image, etc.) first
            // This maps flattened format back to speakers array if needed
            foreach ($details as $key => $value) {
                if (preg_match('/^speakers_(\d+)_image(_path)?$/', $key, $matches) && $value) {
                    $index = (int) $matches[1];
                    if (!isset($details['speakers']) || !is_array($details['speakers'])) {
                        $details['speakers'] = [];
                    }
                    if (!isset($details['speakers'][$index]) || !is_array($details['speakers'][$index])) {
                        $details['speakers'][$index] = [];
                    }
                    // Convert and store in speakers array
                    $details['speakers'][$index]['image'] = $convertPath($value);
                }
            }
            
            // Convert speakers array images for speakers-grid (after handling flattened format)
            if (isset($details['speakers']) && is_array($details['speakers'])) {
                foreach ($details['speakers'] as &$speaker) {
                    if (is_array($speaker)) {
                        if (isset($speaker['image']) && $speaker['image'] && !str_starts_with($speaker['image'], 'http')) {
                            $speaker['image'] = $convertPath($speaker['image']);
                        }
                        if (isset($speaker['image_path']) && $speaker['image_path']) {
                            $speaker['image'] = $convertPath($speaker['image_path']);
                        }
                        // Also check for flattened format like speakers_0_image
                        if (isset($speaker['image_url']) && $speaker['image_url'] && !str_starts_with($speaker['image_url'], 'http')) {
                            $speaker['image'] = $convertPath($speaker['image_url']);
                        }
                    }
                }
            }
            
            // Convert photos array
            if (isset($details['photos']) && is_array($details['photos'])) {
                foreach ($details['photos'] as &$p) { 
                    if (isset($p['url'])) $p['url'] = $convertPath($p['url']); 
                }
            }
            
            // Convert slides array
            if (isset($details['slides']) && is_array($details['slides'])) {
                foreach ($details['slides'] as &$s) { 
                    if (isset($s['background_image'])) $s['background_image'] = $convertPath($s['background_image']); 
                }
            }
            
            // Handle flattened sponsor logo format (sponsor_0_logo, sponsor_1_logo, etc.) first
            foreach ($details as $key => $value) {
                if (preg_match('/^sponsor_(\d+)_logo(_path)?$/', $key, $matches) && $value) {
                    $index = (int) $matches[1];
                    if (!isset($details['sponsors']) || !is_array($details['sponsors'])) {
                        $details['sponsors'] = [];
                    }
                    if (!isset($details['sponsors'][$index]) || !is_array($details['sponsors'][$index])) {
                        $details['sponsors'][$index] = [];
                    }
                    // Convert and store in sponsors array
                    // Only process if value is a string
                    if (!is_string($value)) {
                        continue;
                    }
                    $currentLogo = $details['sponsors'][$index]['logo'] ?? null;
                    if (!$currentLogo || (is_string($currentLogo) && !str_starts_with($currentLogo, 'http'))) {
                        $details['sponsors'][$index]['logo'] = $convertPath($value);
                    }
                }
            }
            
            // Premium Sponsors Grid: convert each sponsor.logo path to signed/public URL (after handling flattened format)
            if (isset($details['sponsors']) && is_array($details['sponsors'])) {
                foreach ($details['sponsors'] as &$s) {
                    if (is_array($s)) {
                        // Handle logo_path field
                        if (isset($s['logo_path']) && $s['logo_path'] && !isset($s['logo'])) {
                            // Ensure logo_path is a string
                            if (is_string($s['logo_path'])) {
                                $s['logo'] = $convertPath($s['logo_path']);
                            }
                        }
                        // Convert logo if it's a relative path (not already a URL)
                        // Check that logo is a string before using str_starts_with
                        if (isset($s['logo']) && is_string($s['logo']) && $s['logo'] && !str_starts_with($s['logo'], 'http')) {
                            $s['logo'] = $convertPath($s['logo']);
                        } elseif (isset($s['logo']) && !is_string($s['logo'])) {
                            // If logo is not a string (could be array or object), try to extract string path
                            if (is_array($s['logo'])) {
                                $logoPath = $s['logo']['path'] ?? $s['logo']['url'] ?? $s['logo'][0] ?? null;
                                if ($logoPath && is_string($logoPath)) {
                                    $s['logo'] = $convertPath($logoPath);
                                } else {
                                    unset($s['logo']); // Remove invalid logo data
                                }
                            } else {
                                unset($s['logo']); // Remove invalid logo data
                            }
                        }
                    } elseif (is_string($s) && !str_starts_with($s, 'http')) {
                        // Legacy format: just a string path
                        $s = $convertPath($s);
                    }
                }
            }

            $html = view($viewPath, [
                'eventData' => $eventData,
                'details' => $details,
                'event' => $eventModel ?: (object) $eventData
            ])->render();
            
            return response($html);
            
        } catch (\Exception $e) {
            \Log::error('Error rendering layout component', [
                'layout' => $layout,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Failed to render layout component'], 500);
        }
    }

    /**
     * Load layout data from R2 storage (copied from LayoutController).
     */
    private function loadLayoutFromR2($path)
    {
        try {
            \Log::info("loadLayoutFromR2 - Starting to load layout from path: {$path}");
            
            $disk = Storage::disk('r2');
            
            // Check if file exists
            if (!$disk->exists($path)) {
                \Log::warning("Layout file not found: {$path}");
                return null;
            }
            
            \Log::info("loadLayoutFromR2 - File exists, reading content");
            $content = $disk->get($path);
            
            if ($content) {
                \Log::info("loadLayoutFromR2 - Content loaded, length: " . strlen($content));
                \Log::info("loadLayoutFromR2 - Raw content: " . substr($content, 0, 500) . "...");
                
                $data = json_decode($content, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    \Log::error("loadLayoutFromR2 - JSON decode error: " . json_last_error_msg());
                    return null;
                }
                
                \Log::info("loadLayoutFromR2 - JSON decoded successfully", [
                    'data_type' => gettype($data),
                    'data_is_array' => is_array($data),
                    'data_keys' => is_array($data) ? array_keys($data) : 'N/A',
                    'data_count' => is_array($data) ? count($data) : 'N/A'
                ]);
                
                return $data;
            } else {
                \Log::warning("loadLayoutFromR2 - No content found in file: {$path}");
                return null;
            }
            
        } catch (\Exception $e) {
            \Log::error("loadLayoutFromR2 - Error loading layout from R2: " . $e->getMessage(), [
                'path' => $path,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Display activity log for a specific event
     */
    public function activityLog(Event $event, Request $request)
    {
        // Check if user has access to this event (admin bypass)
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event->user_id === Auth::id();
        $isAdminEvent = $event->user_id === null && $isAdmin;
        $isStaff = $event->staff()->where('user_id', Auth::id())->exists();
        
        $hasAccess = $isAdmin || $isOwner || $isAdminEvent || $isStaff;
        
        if (!$hasAccess) {
            abort(403, 'Unauthorized access to this event.');
        }

        // Get filter parameters
        $action = $request->get('action');
        $user = $request->get('user');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $perPage = $request->get('per_page', 25);

        // Build query
        $query = EventActivityLog::where('event_id', $event->id)
            ->with(['user:id,first_name,last_name']);

        // Apply filters
        if ($action) {
            $query->where('action', $action);
        }

        if ($user) {
            $query->where('user_id', $user);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Get all activities (not paginated for grouping)
        $allActivities = $query->latest()->get();

        // Group activities by user and date
        $groupedActivities = $allActivities->groupBy(function($activity) {
            $date = $activity->created_at->format('Y-m-d');
            $userName = $activity->user ? $activity->user->first_name . ' ' . $activity->user->last_name : 'Unknown User';
            return $date . '|' . $userName . '|' . $activity->user_id;
        });

        // Convert grouped activities to paginated format
        $groupedActivitiesArray = $groupedActivities->map(function($activities, $key) {
            $parts = explode('|', $key);
            $date = $parts[0];
            $userName = $parts[1];
            $userId = $parts[2];
            $firstActivity = $activities->first();
            
            return [
                'date' => $date,
                'user_name' => $userName,
                'user_id' => $userId,
                'user' => $firstActivity->user,
                'activities' => $activities,
                'count' => $activities->count(),
                'latest_time' => $activities->first()->created_at,
                'time_ago' => $activities->first()->time_ago
            ];
        })->values();

        // Create pagination manually
        $currentPage = $request->get('page', 1);
        $perPage = $request->get('per_page', 25);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedGroups = $groupedActivitiesArray->slice($offset, $perPage);
        
        // Create paginator instance
        $activities = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedGroups,
            $groupedActivitiesArray->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'page']
        );
        $activities->appends(request()->query());

        // Get filter options
        $availableActions = EventActivityLog::where('event_id', $event->id)
            ->distinct()
            ->pluck('action')
            ->sort()
            ->values();

        $availableUsers = User::whereHas('eventActivityLogs', function($q) use ($event) {
                $q->where('event_id', $event->id);
            })
            ->select('id', 'first_name', 'last_name')
            ->get();

        // Get activity statistics
        $totalActivities = EventActivityLog::where('event_id', $event->id)->count();
        $todayActivities = EventActivityLog::where('event_id', $event->id)
            ->whereDate('created_at', today())
            ->count();
        $thisWeekActivities = EventActivityLog::where('event_id', $event->id)
            ->where('created_at', '>=', now()->startOfWeek())
            ->count();

        // Determine layout type
        $layoutType = $isAdmin ? 'admin' : 'organizer';

        return view('events.activity-log', compact(
            'event',
            'activities',
            'availableActions',
            'availableUsers',
            'totalActivities',
            'todayActivities',
            'thisWeekActivities',
            'action',
            'user',
            'dateFrom',
            'dateTo',
            'perPage',
            'isAdmin',
            'layoutType'
        ));
    }

    /**
     * Step 2 Preview - Show layout preview during event creation
     */
    public function step2Preview(Request $request, $id = null)
    {
        // Support both route parameter and query parameter
        $eventId = $id ?? $request->get('id');
        
        if (!$eventId) {
            return redirect()->route('organizer.events.index')->with('error', 'Event ID is required.');
        }

        $event = Event::find($eventId);
        if (!$event) {
            return redirect()->route('organizer.events.index')->with('error', 'Event not found.');
        }

        // Check access - allow owner, admin, or staff
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event->user_id === Auth::id();
        $isAdminEvent = $event->user_id === null && $isAdmin;
        $isStaff = $event->staff()->where('user_id', Auth::id())->exists();
        
        $hasAccess = $isAdmin || $isOwner || $isAdminEvent || $isStaff;
        
        if (!$hasAccess) {
            return redirect()->route('organizer.events.index')->with('error', 'Unauthorized access to this event.');
        }

        // Load layout data from R2 if available
        $layoutData = null;
        if ($event->layout_path) {
            $layoutData = $this->loadLayoutFromR2($event->layout_path);
        }

        return view('organizer.events.create.step2.preview', compact('event', 'layoutData', 'eventId'));
    }

    /**
     * Get the correct user ID for activity logging.
     * Returns null for admin users, regular user ID for regular users.
     */
    private function getActivityLogUserId()
    {
        // If admin is logged in, return null (admin-created events have null user_id)
        if (Auth::guard('admin')->check()) {
            return null;
        }
        
        // Return regular user ID
        return Auth::id();
    }
}
