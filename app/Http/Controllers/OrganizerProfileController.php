<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SocialMediaLink;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganizerProfileController extends Controller
{
    /**
     * Show organizer profile page.
     */
    public function show(Request $request, $userId)
    {
        $organizer = User::with(['socialMediaLinks'])->findOrFail($userId);
        $socialLinks = $organizer->socialMediaLinks()->get()->keyBy('platform');
        
        $type = $request->get('type', 'organized'); // organized, staff, all
        $filter = $request->get('filter', 'all'); // all, upcoming, ended

        // Base query for organized events - show events where user is the owner
        // Exclude events where user is also a co-owner (co-owners should show in "Part Of" instead)
        // Only show published events
        $organizedQuery = $organizer->organizedEvents()
            ->where('status', 'published')
            ->whereDoesntHave('staff', function($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->where('role', 'co-owner');
            });

        // Base query for staff events - show all events where user is staff (including co-owners)
        // This includes co-owners even if they also own the event (co-owners always show in "Part Of")
        // Only show published events
        $staffQuery = Event::where('status', 'published')
            ->whereHas('staff', function($query) use ($userId) {
                $query->where('user_id', $userId);
            });

        // Apply date filter
        $now = now();
        if ($filter === 'upcoming') {
            $organizedQuery->where('start_date', '>=', $now);
            $staffQuery->where('start_date', '>=', $now);
        } elseif ($filter === 'ended') {
            $organizedQuery->where('end_date', '<', $now);
            $staffQuery->where('end_date', '<', $now);
        }

        // Get events based on type
        if ($type === 'organized') {
            $events = $organizedQuery->orderBy('start_date', 'asc')->paginate(10);
        } elseif ($type === 'staff') {
            $events = $staffQuery->orderBy('start_date', 'asc')->paginate(10);
        } else {
            // All events - combine both (both queries already filter by published status)
            $organizedIds = $organizedQuery->pluck('id');
            $staffIds = $staffQuery->pluck('id');
            $allIds = $organizedIds->merge($staffIds)->unique();
            
            $events = Event::whereIn('id', $allIds)
                ->orderBy('start_date', 'asc')
                ->paginate(10);
        }

        // Get counts for filters
        $organizedCount = $organizer->organizedEvents()
            ->where('status', 'published')
            ->whereDoesntHave('staff', function($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->where('role', 'co-owner');
            })
            ->count();
        $staffCount = Event::whereHas('staff', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('status', 'published')
            ->count();

        return view('organizer.profile', compact(
            'organizer', 
            'events', 
            'socialLinks', 
            'type', 
            'filter',
            'organizedCount',
            'staffCount'
        ));
    }
}
