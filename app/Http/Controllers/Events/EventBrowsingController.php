<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventBrowsingController extends Controller
{
    /**
     * Browse events (public listing with filters, sorting, and featured).
     */
    public function browse(Request $request)
    {
        // Base public query - show all events
        $query = Event::whereIn('status', ['published', 'approved']);
        
        // Apply basic filters first (these apply regardless of upcoming/past filter)
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        
        // Tag filtering
        if ($request->filled('tags')) {
            $tags = array_map('trim', explode(',', $request->tags));
            $tags = array_filter($tags);
            
            if (!empty($tags)) {
                $query->where(function($q) use ($tags) {
                    foreach ($tags as $tag) {
                        $q->orWhere('tags', 'like', '%' . $tag . '%');
                    }
                });
            }
        }

        // Date filtering
        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('start_date', '<=', $request->date_to);
        }

        // Price range filtering
        if ($request->filled('price_min') || $request->filled('price_max')) {
            $query->whereHas('eventTickets', function($q) use ($request) {
                if ($request->filled('price_min')) {
                    $q->where('price', '>=', $request->price_min);
                }
                if ($request->filled('price_max')) {
                    $q->where('price', '<=', $request->price_max);
                }
            });
        }

        // Free events filter
        if ($request->boolean('show_free')) {
            $query->where('is_free', true);
        }

        // Handle upcoming vs past events filter
        // By default, show only upcoming events. If user checks "show_past", show all.
        $showPast = $request->boolean('show_past');
        $isShowingUpcomingOnly = false; // Track if we're filtering to upcoming only
        
        if (!$showPast) {
            // First, build a query to check if there are any upcoming events
            $upcomingQuery = clone $query;
            $upcomingQuery->where(function($q) {
                $q->where(function($subQ) {
                    // Events with end_date that hasn't passed
                    $subQ->whereNotNull('end_date')
                         ->where('end_date', '>=', now());
                })->orWhere(function($subQ) {
                    // Events without end_date but start_date hasn't passed
                    $subQ->whereNull('end_date')
                         ->where('start_date', '>=', now());
                });
            });
            
            // Check if there are upcoming events with current filters
            $hasUpcomingEvents = $upcomingQuery->count() > 0;
            
            if ($hasUpcomingEvents) {
                // Apply upcoming filter to main query
                $query->where(function($q) {
                    $q->where(function($subQ) {
                        // Events with end_date that hasn't passed
                        $subQ->whereNotNull('end_date')
                             ->where('end_date', '>=', now());
                    })->orWhere(function($subQ) {
                        // Events without end_date but start_date hasn't passed
                        $subQ->whereNull('end_date')
                             ->where('start_date', '>=', now());
                    });
                });
                $isShowingUpcomingOnly = true; // We are showing upcoming events only
            }
            // If no upcoming events found, query stays as-is (showing all events)
            // isShowingUpcomingOnly remains false
        }
        
        // Apply sorting
        $sortBy = $request->get('sort', 'date');
        $order = $request->get('order', 'asc');
        
        switch ($sortBy) {
            case 'name':
                $query->orderBy('title', $order);
                break;
            case 'created':
                $query->orderBy('created_at', $order);
                break;
            case 'date':
            default:
                $query->orderBy('start_date', $order);
                break;
        }
        
        // Eager load relationships
        $query->with(['ticketTypes', 'user']);
        
        $events = $query->paginate(12)->appends($request->query());
        
        // Only recently viewed - no featured events
        $recentlyViewed = app(EventController::class)->getRecentlyViewedEventsForUser(6);
        
        // Get categories for filter dropdown
        $categories = ['conference', 'workshop', 'seminar', 'meetup', 'webinar', 'exhibition', 'festival', 'sports', 'music', 'art', 'technology', 'business', 'education', 'health', 'charity'];
        
        return view('events.browse', compact('events', 'categories', 'recentlyViewed', 'isShowingUpcomingOnly'));
    }

    /**
     * Get tag suggestions for autocomplete
     */
    public function getTagSuggestions(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        // Get unique tags from events that contain the query
        $tags = Event::whereIn('status', ['published', 'approved'])
            ->whereNotNull('tags')
            ->where('tags', '!=', '')
            ->pluck('tags')
            ->flatMap(function($tagsString) {
                return array_map('trim', explode(',', $tagsString));
            })
            ->filter(function($tag) use ($query) {
                return stripos($tag, $query) !== false;
            })
            ->unique()
            ->values()
            ->take(10);
            
        return response()->json($tags);
    }
}