<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventFeedbackController extends Controller
{
    public function show(Event $event)
    {
        if (!Auth::check()) { return redirect()->route('login'); }
        // Only after event ended
        if ($event->end_date && now()->lt($event->end_date)) {
            abort(403, 'Feedback is available after the event ends.');
        }
        // Only attendees or purchasers (basic check)
        $hasTicket = \App\Models\EventTicketInformation::where('event_id', $event->id)
            ->where('user_id', Auth::id())
            ->exists();
        if (!$hasTicket) { abort(403, 'Feedback available to attendees only.'); }

        $existing = EventFeedback::where('event_id', $event->id)
            ->where('user_id', Auth::id())
            ->first();
        return view('events.feedback', [ 'event' => $event, 'existing' => $existing ]);
    }

    public function submit(Request $request, Event $event)
    {
        if (!Auth::check()) { return redirect()->route('login'); }
        if ($event->end_date && now()->lt($event->end_date)) { abort(403); }
        $request->validate([ 'responses' => 'required|array' ]);

        EventFeedback::updateOrCreate(
            [ 'event_id' => $event->id, 'user_id' => Auth::id() ],
            [ 'responses' => $request->input('responses') ]
        );

        return redirect()->route('events.show', $event)->with('success', 'Thanks for your feedback!');
    }

    /**
     * Notify attendees (email notification only) to send feedback.
     */
    public function notifyAttendees(Event $event)
    {
        if (!Auth::check()) { abort(403); }
        // Allow owners and admins
        $isOwner = (int)$event->user_id === (int)Auth::id();
        $isAdmin = \Illuminate\Support\Facades\Auth::guard('admin')->check();
        if (!$isOwner && !$isAdmin) { abort(403); }
        if ($event->end_date && now()->lt($event->end_date)) { abort(403, 'Event not ended yet'); }

        $feedbackUrl = route('events.feedback.show', $event);
        $attendeeIds = \App\Models\EventTicketInformation::where('event_id', $event->id)
            ->whereHas('ticketPurchase', function($q){ $q->where('status','paid'); })
            ->pluck('user_id')
            ->unique()
            ->filter();

        $users = \App\Models\User::whereIn('id', $attendeeIds)->get();
        foreach ($users as $u) {
            try { 
                // Send email notification (existing)
                $u->notify(new \App\Notifications\EventFeedbackRequest($event, $feedbackUrl)); 
                
                // Create in-app notification
                \App\Models\Notification::create([
                    'user_id' => $u->id,
                    'admin_id' => null,
                    'type' => 'event',
                    'action' => 'feedback_request',
                    'reference_id' => $event->id,
                    'is_read' => false,
                ]);
            } catch (\Throwable $e) { /* ignore */ }
        }

        // Mark as notified
        try { $event->forceFill(['feedback_notified_at' => now()])->save(); } catch (\Throwable $e) { /* ignore */ }

        // Manually trigger the Event End Notification Service for this event only
        try {
            \App\Services\EventEndNotificationService::notifyUsersForEndedEvent($event);
        } catch (\Throwable $e) {
            \Log::warning('Manual event end notification failed', ['event_id' => $event->id, 'error' => $e->getMessage()]);
        }
        return back()->with('success', 'Feedback invitations sent.');
    }

    /**
     * Manage feedback for organizers and admins
     */
    public function manage(Request $request, Event $event)
    {
        $isAdmin = Auth::guard('admin')->check();
        $user = Auth::user();
        
        // Check access permissions
        if (!$isAdmin) {
            // Check if user is event owner or has staff permissions
            $isOwner = $user && (int)$event->user_id === (int)$user->id;
            $staff = $event->staff->firstWhere('user_id', $user?->id);
            $canManageEvent = $isOwner || ($staff?->can_manage_event ?? false) || ($staff?->role === 'organizer');
            
            if (!$canManageEvent) {
                abort(403, 'Unauthorized access to feedback management');
            }
        }

        // Get filter parameter
        $filter = $request->get('filter', 'all');
        
        // Get all feedback submissions for this event (excluding Qricket feedback)
        $feedbackSubmissions = EventFeedback::where('event_id', $event->id)
            ->where(function($query) {
                $query->whereNull('responses')
                      ->orWhere('responses', 'not like', '%qricket%')
                      ->orWhere('responses', 'not like', '%Qricket%')
                      ->orWhere('responses', 'not like', '%QRICKET%');
            });
        
        // Apply comment filter if specified
        if ($filter !== 'all') {
            $feedbackSubmissions->where(function($query) use ($filter) {
                $query->whereRaw("JSON_EXTRACT(responses, '$.comments.{$filter}') IS NOT NULL")
                      ->whereRaw("JSON_EXTRACT(responses, '$.comments.{$filter}') != ''")
                      ->whereRaw("JSON_EXTRACT(responses, '$.comments.{$filter}') != 'null'");
            });
        }
        
        $feedbackSubmissions = $feedbackSubmissions->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Calculate feedback statistics (excluding Qricket feedback)
        $totalSubmissions = EventFeedback::where('event_id', $event->id)
            ->where(function($query) {
                $query->whereNull('responses')
                      ->orWhere('responses', 'not like', '%qricket%')
                      ->orWhere('responses', 'not like', '%Qricket%')
                      ->orWhere('responses', 'not like', '%QRICKET%');
            })
            ->count();
        $totalAttendees = \App\Models\EventTicketInformation::where('event_id', $event->id)
            ->whereHas('ticketPurchase', function($q){ $q->where('status','paid'); })
            ->distinct('user_id')
            ->count();
        
        $responseRate = $totalAttendees > 0 ? round(($totalSubmissions / $totalAttendees) * 100, 1) : 0;

        // Calculate average ratings for each question (excluding Qricket feedback)
        $averageRatings = [];
        if ($totalSubmissions > 0) {
            $allResponses = EventFeedback::where('event_id', $event->id)
                ->where(function($query) {
                    $query->whereNull('responses')
                          ->orWhere('responses', 'not like', '%qricket%')
                          ->orWhere('responses', 'not like', '%Qricket%')
                          ->orWhere('responses', 'not like', '%QRICKET%');
                })
                ->pluck('responses')
                ->map(function($responses) {
                    return is_string($responses) ? json_decode($responses, true) : $responses;
                })
                ->filter();

            // Calculate averages for each category
            $categoryAverages = [];
            
            // Overall Experience category
            $overallExperienceRatings = $allResponses->pluck('overall_experience')->filter();
            if ($overallExperienceRatings->count() > 0) {
                $overallSubRatings = collect();
                foreach ($overallExperienceRatings as $rating) {
                    if (is_array($rating)) {
                        $overallSubRatings = $overallSubRatings->merge(array_values($rating));
                    }
                }
                $categoryAverages['overall_experience'] = [
                    'average' => round($overallSubRatings->avg(), 1),
                    'count' => $overallSubRatings->count(),
                    'subcategories' => [
                        'experience' => $this->calculateSubcategoryAverage($overallExperienceRatings, 'experience'),
                        'expectations' => $this->calculateSubcategoryAverage($overallExperienceRatings, 'expectations'),
                        'organization' => $this->calculateSubcategoryAverage($overallExperienceRatings, 'organization'),
                        'attend_again' => $this->calculateSubcategoryAverage($overallExperienceRatings, 'attend_again'),
                        'recommend' => $this->calculateSubcategoryAverage($overallExperienceRatings, 'recommend'),
                    ]
                ];
            }
            
            // Logistics category
            $logisticsRatings = $allResponses->pluck('logistics')->filter();
            if ($logisticsRatings->count() > 0) {
                $logisticsSubRatings = collect();
                foreach ($logisticsRatings as $rating) {
                    if (is_array($rating)) {
                        $logisticsSubRatings = $logisticsSubRatings->merge(array_values($rating));
                    }
                }
                $categoryAverages['logistics'] = [
                    'average' => round($logisticsSubRatings->avg(), 1),
                    'count' => $logisticsSubRatings->count(),
                    'subcategories' => [
                        'schedule' => $this->calculateSubcategoryAverage($logisticsRatings, 'schedule'),
                        'registration' => $this->calculateSubcategoryAverage($logisticsRatings, 'registration'),
                        'venue' => $this->calculateSubcategoryAverage($logisticsRatings, 'venue'),
                        'technical' => $this->calculateSubcategoryAverage($logisticsRatings, 'technical'),
                        'staff' => $this->calculateSubcategoryAverage($logisticsRatings, 'staff'),
                    ]
                ];
            }
            
            $averageRatings = $categoryAverages;
        }

        $layoutType = $isAdmin ? 'admin' : 'organizer';

        // Check subscription for detailed rating access
        $subscription = $user?->currentSubscription();
        $plan = $subscription?->getPlan();
        if (!$plan) {
            $plan = \App\Models\SubscriptionPlan::where('slug', 'free')->first();
        }
        // Check if plan has advanced_analytics feature OR is in the legacy pro tier list
        $hasAdvancedAnalytics = $plan && ($plan->advanced_analytics === true);
        $hasProSubscription = $plan && in_array($plan->slug, ['professional', 'enterprise', 'starter', 'pro', 'intermediate']);
        $canViewDetailedRatings = $hasAdvancedAnalytics || $hasProSubscription || $isAdmin;
        $canViewDetailedFeedback = $hasAdvancedAnalytics || $hasProSubscription || $isAdmin;

        // If AJAX request, return only the feedback list section
        if ($request->ajax()) {
            return response()->json([
                'html' => view('organizer.events.feedback', compact(
                    'event', 
                    'feedbackSubmissions', 
                    'totalSubmissions', 
                    'totalAttendees', 
                    'responseRate', 
                    'averageRatings',
                    'isAdmin',
                    'layoutType',
                    'canViewDetailedRatings',
                    'canViewDetailedFeedback'
                ))->render()
            ]);
        }

        return view('organizer.events.feedback', compact(
            'event', 
            'feedbackSubmissions', 
            'totalSubmissions', 
            'totalAttendees', 
            'responseRate', 
            'averageRatings',
            'isAdmin',
            'layoutType',
            'canViewDetailedRatings',
            'canViewDetailedFeedback'
        ));
    }

    /**
     * Calculate average for a specific subcategory
     */
    private function calculateSubcategoryAverage($categoryRatings, $subcategory)
    {
        $ratings = collect();
        foreach ($categoryRatings as $rating) {
            if (is_array($rating) && isset($rating[$subcategory])) {
                $ratings->push($rating[$subcategory]);
            }
        }
        
        return $ratings->count() > 0 ? round($ratings->avg(), 1) : 0;
    }
}








