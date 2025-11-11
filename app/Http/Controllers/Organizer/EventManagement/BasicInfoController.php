<?php

namespace App\Http\Controllers\Organizer\EventManagement;

use App\Http\Controllers\Controller;

use App\Models\Event;
use App\Models\EventActivityLog;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Organizer\EventManagement\Traits\DraftHelpers;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BasicInfoController extends Controller
{
    use DraftHelpers;

    /**
     * Normalize value for change comparison.
     * - Datetime fields compared to minute precision in app timezone
     * - Other fields cast to string, preserving null
     */
    private function normalizeFieldValueForComparison(string $field, $value)
    {
        if (is_null($value)) return null;

        if (in_array($field, ['start_date', 'end_date'])) {
            try {
                $dt = $value instanceof \DateTimeInterface ? Carbon::instance($value) : Carbon::parse((string)$value);
                // Compare to minute precision to avoid seconds/microseconds noise
                return $dt->timezone(config('app.timezone', 'UTC'))->format('Y-m-d H:i');
            } catch (\Throwable $e) {
                return (string) $value;
            }
        }

        return (string) $value;
    }

    public function step1(Request $request)
    {
        $eventId = $request->get('id');

        if ($request->boolean('new')) {
            // For new events, don't create in database yet - just show empty form
            $eventData = [
                'title' => '', 
                'description' => '', 
                'category' => '',
                'start_date' => '', 
                'end_date' => '', 
                'location' => '',
                'location_url' => '',
                'is_free' => false,
                'tags' => '',
                'latitude' => '',
                'longitude' => '',
                'max_participants' => '',
                'poster' => '',
                'thumbnail' => '',
                'existing_poster_url' => '',
                'existing_thumbnail_url' => '',
            ];
            return view('organizer.events.create.step1', compact('eventData', 'eventId'));
        }

        if ($request->filled('continue')) {
            $id = (int) $request->get('continue');
            if ($id > 0) {
                $existing = Event::find($id);
                $isAdmin = Auth::guard('admin')->check();
                $isOwner = $existing && $existing->user_id === Auth::id();
                $isAdminEvent = $existing && $existing->user_id === null && $isAdmin;
                
                // Allow both draft and rejected events to be continued
                if ($existing && in_array($existing->status, ['draft', 'rejected']) && ($isOwner || $isAdminEvent)) {
                    return redirect()->route('organizer.events.create.basicinfo', ['id' => $id]);
                }
            }
        }

        if (!$eventId) {
            // For new events without ID, show empty form
            $eventData = [
                'title' => '', 
                'description' => '', 
                'category' => '',
                'start_date' => '', 
                'end_date' => '', 
                'location' => '',
                'location_url' => '',
                'is_free' => false,
                'tags' => '',
                'latitude' => '',
                'longitude' => '',
                'max_participants' => '',
                'poster' => '',
                'thumbnail' => '',
                'existing_poster_url' => '',
                'existing_thumbnail_url' => '',
            ];
            return view('organizer.events.create.step1', compact('eventData', 'eventId'));
        }

        // Load existing event data (for continue flow)
        $event = Event::find($eventId);
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event && $event->user_id === Auth::id();
        $isAdminEvent = $event && $event->user_id === null && $isAdmin;
        
        if (!$event || $event->status !== 'draft' || (!$isOwner && !$isAdminEvent)) {
            return redirect()->route('organizer.events.index')->with('error', 'Event not found or not authorized.');
        }

        $eventData = [
            'title' => $event->title ?? '',
            'description' => $event->description ?? '',
            'category' => $event->category ?? '',
            'start_date' => $event->start_date ?? '',
            'end_date' => $event->end_date ?? '',
            'location' => $event->location ?? '',
            'location_url' => $event->location_url ?? '',
            'is_free' => (bool)($event->is_free ?? false),
            'tags' => $event->tags ?? '',
            'latitude' => $event->latitude ?? '',
            'longitude' => $event->longitude ?? '',
            'max_participants' => $event->max_participants ?? '',
            'poster' => $event->poster ?? '',
            'thumbnail' => $event->thumbnail ?? '',
            'existing_poster_url' => $event->poster_url ?? '',
            'existing_thumbnail_url' => $event->thumbnail_url ?? '',
        ];

        // Determine if event is free (for locking the checkbox in the UI)
        // If event is already saved as free, lock it to prevent changing to paid
        $isFreeEvent = (bool)($event->is_free ?? false);
        
        // For step1 (create/edit), check if event has sales (only if event exists)
        $hasSales = false;
        $eventIsPaidWithSales = false;
        $isFreeEventLocked = false;
        
        if ($event) {
            // Check if paid event has sales (to prevent changing to free)
            if (!$isFreeEvent) {
                $hasSales = $event->eventTickets()->where('sold_count', '>', 0)->exists() 
                         || \App\Models\TicketPurchase::whereHas('ticketInformation', function($query) use ($event) {
                                $query->where('event_id', $event->id);
                            })
                            ->where('status', 'paid')
                            ->exists();
                $eventIsPaidWithSales = $hasSales;
            }
            
            // Lock the checkbox if:
            // 1. Event is free (can't change to paid), OR
            // 2. Event is paid and has sales (can't change to free)
            $isFreeEventLocked = $isFreeEvent || $eventIsPaidWithSales;
        }

        return view('organizer.events.create.step1', compact('eventData', 'eventId', 'event', 'isFreeEvent', 'hasSales', 'isFreeEventLocked', 'eventIsPaidWithSales'));
    }

    public function step1Store(Request $request)
    {
        $eventId = $request->get('id');
        
        // If no eventId, this is a new event - create it in database
        if (!$eventId) {
            $event = new Event();
            // Check if admin is creating event - set user_id to NULL for admin events
            if (Auth::guard('admin')->check()) {
                $event->user_id = null; // Admin-created event
            } else {
            $event->user_id = Auth::id();
            }
            $event->status = 'draft';
        } else {
            // Find existing event
            $event = Event::find($eventId);
            
            if (!$event) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Event not found.'], 404);
                }
                return redirect()->route('organizer.events.index')->with('error', 'Event not found.');
            }
            
            // Check authorization - allow editing draft and published events
            if (!in_array($event->status, ['draft', 'published', 'approved', 'pending_approval', 'scheduled'])) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'This event cannot be edited in its current status.'], 403);
                }
                return redirect()->route('organizer.events.index')->with('error', 'This event cannot be edited in its current status.');
            }
            
            // Check authorization - allow admin, owner, or staff to edit events
            $isAdmin = Auth::guard('admin')->check();
            $isOwner = $event->user_id === Auth::id();
            $isAdminEvent = $event->user_id === null && $isAdmin;
            $isStaff = $event->staff()->where('user_id', Auth::id())->exists();
            
            // Allow staff members to manage events even without explicit can_manage_event permission
            if (!$isOwner && !$isAdminEvent && !$isStaff) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized access to this event.'], 403);
                }
                return redirect()->route('organizer.events.index')->with('error', 'Unauthorized access to this event.');
            }
        }

        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255', new \App\Rules\NoProfanity()],
            'description' => 'required|string|min:10',
            'category' => 'required|string|in:conference,workshop,seminar,meetup,training,networking,social,sports,entertainment,other',
            'tags' => 'required|string|min:2',
            'start_date' => 'required|date|after:now',
            'end_date' => 'nullable|date|after:start_date',
            'location' => 'required|string|max:500',
            'location_url' => 'nullable|url|max:1000',
        ], [
            'title.required' => 'Event title is required.',
            'title.max' => 'Event title cannot be longer than 255 characters.',
            'description.required' => 'Event description is required.',
            'description.min' => 'Event description must be at least 10 characters long.',
            'category.required' => 'Please select an event category.',
            'category.in' => 'Please select a valid event category.',
            'start_date.required' => 'Event start date and time is required.',
            'start_date.date' => 'Please enter a valid start date.',
            'start_date.after' => 'Event start date must be in the future.',
            'end_date.date' => 'Please enter a valid end date.',
            'end_date.after' => 'Event end date must be after the start date.',
            'location.required' => 'Event location is required.',
            'location.max' => 'Location cannot be longer than 500 characters.',
            'location_url.url' => 'Please enter a valid URL for the location.',
            'location_url.max' => 'Location URL cannot be longer than 1000 characters.',
        ]);
        
        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                $errors = $validator->errors()->all();
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $errors,
                    'error_details' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            $step1Data = $request->only(['title','description','category','start_date','end_date','location','location_url','tags']);
            
            $wasExistingEvent = !is_null($eventId);
            $originalData = $wasExistingEvent ? $event->only(['title','description','category','start_date','end_date','location','location_url','tags','is_free']) : null;
            
            // Prevent changing event type if it has sales
            if ($wasExistingEvent) {
                // Check if event has any ticket sales
                $hasSales = $event->eventTickets()->where('sold_count', '>', 0)->exists() 
                         || \App\Models\TicketPurchase::whereHas('ticketInformation', function($query) use ($event) {
                                $query->where('event_id', $event->id);
                            })
                            ->where('status', 'paid')
                            ->exists();
                
                if ($event->is_free) {
                // Event is already free - keep it free and ignore any attempt to change it
                $step1Data['is_free'] = true;
                
                // If user tried to uncheck the free event checkbox, show an error
                if (!$request->boolean('is_free')) {
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Cannot change free event to paid event. Once an event is created as free, it cannot be changed to a paid event.',
                            'errors' => ['is_free' => ['Cannot change free event to paid event.']]
                        ], 422);
                    }
                    return back()->withErrors(['is_free' => 'Cannot change free event to paid event. Once an event is created as free, it cannot be changed to a paid event.'])->withInput();
                    }
                } elseif ($hasSales && $request->boolean('is_free')) {
                    // Event is paid and has sales - prevent changing to free
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Cannot change paid event to free event. This event has ticket sales and cannot be changed to a free event.',
                            'errors' => ['is_free' => ['Cannot change paid event with sales to free event.']]
                        ], 422);
                    }
                    return back()->withErrors(['is_free' => 'Cannot change paid event to free event. This event has ticket sales and cannot be changed to a free event.'])->withInput();
                } else {
                    // Normalize boolean for is_free (paid event without sales can be changed)
                    $step1Data['is_free'] = $request->boolean('is_free');
                }
            } else {
                // Normalize boolean for is_free (for new events)
                $step1Data['is_free'] = $request->boolean('is_free');
            }
            
            \Log::info('Basic info update request', [
                'event_id' => $eventId,
                'request_data' => $step1Data,
                'user_id' => Auth::id(),
                'was_free' => $wasExistingEvent ? $event->is_free : false,
                'is_free_locked' => $wasExistingEvent && $event->is_free
            ]);
            
            \Log::info('Original event data', [
                'event_id' => $eventId,
                'original_data' => $originalData
            ]);
            
            // Fill the event with data and save
            $event->fill($step1Data);
            
            // Capture subscription_id only when creating a new event (not on updates)
            // This preserves the subscription that was active at event creation time
            if (!$wasExistingEvent && !$event->subscription_id) {
                $user = Auth::user();
                if ($user && !Auth::guard('admin')->check()) {
                    $currentSubscription = $user->currentSubscription();
                    if ($currentSubscription) {
                        $event->subscription_id = $currentSubscription->id;
                        \Log::info('Captured subscription_id for new event', [
                            'event_id' => $event->id,
                            'subscription_id' => $currentSubscription->id,
                            'user_id' => $user->id
                        ]);
                    }
                }
            }
            
            $event->save();
            
            // Log admin activity for event creation (when user_id is null)
            if (!$wasExistingEvent && Auth::guard('admin')->check() && $event->user_id === null) {
                Auth::guard('admin')->user()->logActivity('admin_event_create', [
                    'event_id' => $event->id,
                    'event_title' => $event->title,
                    'event_category' => $event->category,
                    'event_location' => $event->location,
                    'event_start_date' => $event->start_date,
                    'event_tags' => $event->tags,
                ], 'Created admin event: ' . $event->title);
            }
            
            \Log::info('Event saved successfully', [
                'event_id' => $event->id,
                'saved_data' => $event->only(['title','description','category','start_date','end_date','location','location_url','tags','latitude','longitude','is_free'])
            ]);
            
            // Get the final event ID (for new events, this will be the generated ID)
            $finalEventId = $event->id;
            
            // Log activity for event edit only when there are actual changes
            if ($wasExistingEvent && $originalData) {
                $changedFields = [];
                $fieldChanges = [];
                foreach ($step1Data as $field => $newValue) {
                    $oldValue = $originalData[$field] ?? null;
                    // Normalize values for reliable comparison (handle dates precisely to minute)
                    $oldNorm = $this->normalizeFieldValueForComparison($field, $oldValue);
                    $newNorm = $this->normalizeFieldValueForComparison($field, $newValue);
                    if ($oldNorm !== $newNorm) {
                        $changedFields[] = $field;
                        $fieldChanges[$field] = [
                            'from' => $oldValue,
                            'to' => $newValue,
                        ];
                    }
                }
                if (!empty($changedFields)) {
                    // Build human-friendly summary
                    $friendly = [
                        'title' => 'Title',
                        'description' => 'Description',
                        'category' => 'Category',
                        'start_date' => 'Start date',
                        'end_date' => 'End date',
                        'location' => 'Location',
                        'location_url' => 'Location URL',
                        'is_free' => 'Free event',
                        'latitude' => 'Latitude',
                        'longitude' => 'Longitude',
                    ];
                    $labels = array_map(function($f) use ($friendly) {
                        return $friendly[$f] ?? ucfirst(str_replace('_',' ', $f));
                    }, $changedFields);
                    $summary = 'Updated: ' . implode(', ', $labels);

                    EventActivityLog::create([
                        'event_id' => $event->id,
                        'user_id' => Auth::id(),
                        'action' => 'event_basic_info_updated',
                        'description' => $summary,
                        'metadata' => [
                            'updated_fields' => $changedFields,
                            'changes' => $fieldChanges,
                            'event_title' => $event->title,
                            'summary' => $summary
                        ],
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent()
                    ]);
                }
            }
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Event information saved successfully.',
                    'redirect_url' => route('organizer.events.create.layout', ['id' => $finalEventId])
                ]);
            }
            
            return redirect()->route('organizer.events.create.layout', ['id' => $finalEventId]);
            
        } catch (\Exception $e) {
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save event information. Please try again.'
                ], 500);
            }
            
            return back()->with('error', 'Failed to save event information. Please try again.');
        }
    }

    public function cancel(Request $request, int $id)
    {
        // Simply redirect back to events index - don't delete the event
        // The event will remain as a draft that can be continued later
        
        return redirect()->route('organizer.events.index')->with('info', 'Returned to events list.');
    }

    public function destroy(Request $request, int $id)
    {
        \Log::info('Event delete request', [
            'event_id' => $id,
            'user_id' => Auth::id(),
            'is_admin' => Auth::guard('admin')->check(),
            'is_ajax' => $request->expectsJson() || $request->ajax(),
            'request_method' => $request->method()
        ]);
        
        $event = Event::find($id);
        
        // Handle AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            if (!$event) {
                return response()->json(['success' => false, 'message' => 'Event not found.']);
            }
            // Check authorization - allow admin to delete admin-created events (user_id = null) or user's own events or organizers
            $isAdmin = Auth::guard('admin')->check();
            $isOwner = $event->user_id === Auth::id();
            $isAdminEvent = $event->user_id === null && $isAdmin;
            $isOrganizer = $event->staff->where('user_id', Auth::id())->where('role', 'organizer')->isNotEmpty();
            
            \Log::info('Event delete authorization check', [
                'event_id' => $id,
                'event_user_id' => $event->user_id,
                'current_user_id' => Auth::id(),
                'is_admin' => $isAdmin,
                'is_owner' => $isOwner,
                'is_admin_event' => $isAdminEvent,
                'is_organizer' => $isOrganizer,
                'authorized' => $isOwner || $isAdminEvent || $isOrganizer
            ]);
            
            if (!$isOwner && !$isAdminEvent && !$isOrganizer) {
                return response()->json(['success' => false, 'message' => 'Not authorized to delete this event.']);
            }
            
            try {
                try { 
                    Storage::disk('r2')->deleteDirectory("events/{$event->id}"); 
                } catch (\Throwable $t) {
                    \Log::warning('Failed to delete R2 directory', ['event_id' => $id, 'error' => $t->getMessage()]);
                }
                $event->delete();
                
                \Log::info('Event deleted successfully', [
                    'event_id' => $id,
                    'user_id' => Auth::id()
                ]);
                
                return response()->json(['success' => true, 'message' => 'Event deleted successfully.']);
            } catch (\Throwable $t) {
                \Log::error('Failed to delete event', ['event_id' => $id, 'error' => $t->getMessage()]);
                return response()->json(['success' => false, 'message' => 'Failed to delete event.']);
            }
        }
        
        // Handle regular form submissions (redirect responses)
        if (!$event) return redirect()->route('organizer.events.index')->with('error', 'Event not found.');
        
        // Check authorization - allow admin to delete admin-created events (user_id = null) or user's own events or organizers
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event->user_id === Auth::id();
        $isAdminEvent = $event->user_id === null && $isAdmin;
        $isOrganizer = $event->staff->where('user_id', Auth::id())->where('role', 'organizer')->isNotEmpty();
        
        if (!$isOwner && !$isAdminEvent && !$isOrganizer) {
            return redirect()->route('organizer.events.index')->with('error', 'Not authorized.');
        }
        
        try {
            try { 
                Storage::disk('r2')->deleteDirectory("events/{$event->id}"); 
            } catch (\Throwable $t) {}
            $event->delete();
        } catch (\Throwable $t) {
            return redirect()->back()->with('error', 'Failed to delete event.');
        }
        
        return redirect()->route('organizer.events.index')->with('success', 'Event deleted.');
    }

    /**
     * Get tag-category mappings from existing events in the database
     */
    public function getTagCategoryMappings()
    {
        \Log::info('🔍 getTagCategoryMappings() called');
        
        // Cache the mappings for 1 hour to avoid repeated database queries
        return Cache::remember('tag_category_mappings', 3600, function () {
            \Log::info('📊 Loading tag-category mappings from database...');
            $mappings = [];
            
            // Get all events with tags and categories
            $events = Event::whereNotNull('tags')
                ->whereNotNull('category')
                ->where('tags', '!=', '')
                ->where('category', '!=', '')
                ->select('tags', 'category')
                ->get();
            
            foreach ($events as $event) {
                if (empty($event->tags)) continue;
                
                // Split tags by comma and clean them
                $tags = array_map('trim', explode(',', $event->tags));
                $category = $event->category;
                
                foreach ($tags as $tag) {
                    $tag = strtolower($tag);
                    if (empty($tag)) continue;
                    
                    // Count occurrences of this tag with this category
                    if (!isset($mappings[$tag])) {
                        $mappings[$tag] = [];
                    }
                    
                    if (!isset($mappings[$tag][$category])) {
                        $mappings[$tag][$category] = 0;
                    }
                    
                    $mappings[$tag][$category]++;
                }
            }
            
            // For each tag, find the most common category
            $tagCategoryMap = [];
            foreach ($mappings as $tag => $categories) {
                // Sort categories by frequency (descending)
                arsort($categories);
                // Get the most common category
                $mostCommonCategory = array_key_first($categories);
                $tagCategoryMap[$tag] = $mostCommonCategory;
            }
            
            // Add fallback mappings to supplement existing mappings
            $fallbackMappings = [
                'music' => 'music',
                'concert' => 'music',
                'festival' => 'music',
                'party' => 'music',
                'dance' => 'music',
                'conference' => 'conference',
                'seminar' => 'conference',
                'workshop' => 'workshop',
                'training' => 'workshop',
                'meeting' => 'conference',
                'sports' => 'sports',
                'football' => 'sports',
                'basketball' => 'sports',
                'marathon' => 'sports',
                'tournament' => 'sports',
                'art' => 'art',
                'exhibition' => 'art',
                'gallery' => 'art',
                'painting' => 'art',
                'sculpture' => 'art',
                'technology' => 'technology',
                'programming' => 'technology',
                'coding' => 'technology',
                'ai' => 'technology',
                'blockchain' => 'technology',
                'business' => 'business',
                'networking' => 'business',
                'startup' => 'business',
                'entrepreneur' => 'business',
                'finance' => 'business',
                'education' => 'education',
                'learning' => 'education',
                'course' => 'education',
                'certification' => 'education',
                'degree' => 'education',
                'health' => 'health',
                'wellness' => 'health',
                'fitness' => 'health',
                'yoga' => 'health',
                'meditation' => 'health',
                'food' => 'food',
                'cooking' => 'food',
                'restaurant' => 'food',
                'chef' => 'food',
                'culinary' => 'food',
                'travel' => 'travel',
                'adventure' => 'travel',
                'tourism' => 'travel',
                'culture' => 'travel',
                'heritage' => 'travel',
                'charity' => 'charity',
                'fundraising' => 'charity',
                'volunteer' => 'charity',
                'community' => 'charity',
                'social' => 'charity',
                'entertainment' => 'entertainment',
                'comedy' => 'entertainment',
                'theater' => 'entertainment',
                'movie' => 'entertainment',
                'gaming' => 'entertainment'
            ];
            
            // Add fallback mappings only for tags that don't have existing mappings
            foreach ($fallbackMappings as $tag => $category) {
                if (!isset($tagCategoryMap[$tag])) {
                    $tagCategoryMap[$tag] = $category;
                }
            }
            
            return $tagCategoryMap;
        });
    }

    /**
     * Get existing tags from database for autocomplete suggestions
     */
    public function getExistingTags()
    {
        // Cache the tags for 30 minutes
        return Cache::remember('existing_tags', 1800, function () {
            $tags = [];
            
            // Get all unique tags from events
            $events = Event::whereNotNull('tags')
                ->where('tags', '!=', '')
                ->select('tags')
                ->get();
            
            \Log::info('📋 Found events with tags:', ['count' => $events->count()]);
            
            foreach ($events as $event) {
                if (empty($event->tags)) continue;
                
                \Log::info('📝 Processing event tags:', ['tags' => $event->tags]);
                
                // Split tags by comma and clean them
                $eventTags = array_map('trim', explode(',', $event->tags));
                
                foreach ($eventTags as $tag) {
                    $tag = strtolower($tag);
                    if (empty($tag)) continue;
                    
                    if (!isset($tags[$tag])) {
                        $tags[$tag] = 0;
                    }
                    $tags[$tag]++;
                }
            }
            
            \Log::info('📊 Processed tags from events:', ['count' => count($tags), 'tags' => array_keys($tags)]);
            
            // Add fallback tags to supplement existing tags
            $fallbackTags = [
                'music', 'concert', 'festival', 'party', 'dance',
                'conference', 'seminar', 'workshop', 'training', 'meeting',
                'sports', 'football', 'basketball', 'marathon', 'tournament',
                'art', 'exhibition', 'gallery', 'painting', 'sculpture',
                'technology', 'programming', 'coding', 'ai', 'blockchain',
                'business', 'networking', 'startup', 'entrepreneur', 'finance',
                'education', 'learning', 'course', 'certification', 'degree',
                'health', 'wellness', 'fitness', 'yoga', 'meditation',
                'food', 'cooking', 'restaurant', 'chef', 'culinary',
                'travel', 'adventure', 'tourism', 'culture', 'heritage',
                'charity', 'fundraising', 'volunteer', 'community', 'social',
                'entertainment', 'comedy', 'theater', 'movie', 'gaming'
            ];
            
            // Add fallback tags with lower priority (count = 0.5) so existing tags rank higher
            foreach ($fallbackTags as $tag) {
                if (!isset($tags[$tag])) {
                    $tags[$tag] = 0.5; // Lower priority than real event tags
                }
            }
            
            // Sort by frequency (most used tags first)
            arsort($tags);
            
            $finalTags = array_keys($tags);
            \Log::info('✅ Final tags array:', ['count' => count($finalTags), 'first_10' => array_slice($finalTags, 0, 10)]);
            
            // Return only the tag names as array
            return $finalTags;
        });
    }
}


