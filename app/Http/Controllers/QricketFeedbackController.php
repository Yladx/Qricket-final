<?php

namespace App\Http\Controllers;

use App\Models\QricketFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class QricketFeedbackController extends Controller
{
    /**
     * Show the feedback form
     */
    public function show()
    {
        return view('feedback.qricket-feedback');
    }

    /**
     * Store the feedback
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'navigation_ease' => 'nullable|integer|min:1|max:5',
            'information_found' => 'nullable|integer|min:1|max:5',
            'design_rating' => 'nullable|integer|min:1|max:5',
            'performance_rating' => 'nullable|integer|min:1|max:5',
            'process_satisfaction' => 'nullable|integer|min:1|max:5',
            'improvements_suggestions' => ['nullable', 'string', 'max:2000', new \App\Rules\NoProfanity()],
            'additional_comments' => ['nullable', 'string', 'max:2000', new \App\Rules\NoProfanity()],
            'feedback_type' => 'nullable|string|in:website,event',
            'event_id' => 'nullable|exists:events,id',
            'ticket_purchase_id' => 'nullable|exists:ticket_purchases,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $feedback = QricketFeedback::create([
                'user_id' => Auth::id(),
                'session_id' => session()->getId(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'navigation_ease' => $request->navigation_ease,
                'information_found' => $request->information_found,
                'design_rating' => $request->design_rating,
                'performance_rating' => $request->performance_rating,
                'process_satisfaction' => $request->process_satisfaction,
                'improvements_suggestions' => $request->improvements_suggestions,
                'additional_comments' => $request->additional_comments,
                'feedback_type' => $request->feedback_type ?? 'website',
                'event_id' => $request->event_id,
                'ticket_purchase_id' => $request->ticket_purchase_id,
            ]);

            // Store in session that user has given feedback
            session(['qricket_feedback_given' => true]);
            session(['qricket_feedback_date' => now()->toDateString()]);

            return response()->json([
                'success' => true,
                'message' => 'Thank you for your feedback!',
                'feedback_id' => $feedback->id
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to store Qricket feedback', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save feedback. Please try again.'
            ], 500);
        }
    }

    /**
     * Get feedback statistics for admin dashboard
     */
    public function getStats()
    {
        $totalFeedback = QricketFeedback::website()->count();
        $recentFeedback = QricketFeedback::website()->recent(30)->count();
        
        $averageRatings = [
            'navigation_ease' => QricketFeedback::website()->whereNotNull('navigation_ease')->avg('navigation_ease'),
            'information_found' => QricketFeedback::website()->whereNotNull('information_found')->avg('information_found'),
            'design_rating' => QricketFeedback::website()->whereNotNull('design_rating')->avg('design_rating'),
            'performance_rating' => QricketFeedback::website()->whereNotNull('performance_rating')->avg('performance_rating'),
            'process_satisfaction' => QricketFeedback::website()->whereNotNull('process_satisfaction')->avg('process_satisfaction'),
        ];

        $recentComments = QricketFeedback::website()
            ->whereNotNull('additional_comments')
            ->where('additional_comments', '!=', '')
            ->recent(30)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['additional_comments', 'created_at', 'user_id']);

        return response()->json([
            'total_feedback' => $totalFeedback,
            'recent_feedback' => $recentFeedback,
            'average_ratings' => $averageRatings,
            'recent_comments' => $recentComments
        ]);
    }

    /**
     * Check if user should see feedback modal
     */
    public function shouldShowModal()
    {
        // Don't show if user already gave feedback today
        if (session('qricket_feedback_given') && session('qricket_feedback_date') === now()->toDateString()) {
            return response()->json(['should_show' => false]);
        }

        // Don't show if dismissed today
        if (session('qricket_feedback_dismissed') && session('qricket_feedback_dismissed_date') === now()->toDateString()) {
            return response()->json(['should_show' => false]);
        }

        // Show modal based on user actions (can be customized)
        $userActions = session('qricket_user_actions', []);
        
        // Show after ticket purchase, event registration, etc.
        $triggerActions = ['ticket_purchased', 'event_registered', 'subscription_purchased'];
        
        foreach ($triggerActions as $action) {
            if (in_array($action, $userActions)) {
                return response()->json(['should_show' => true]);
            }
        }

        return response()->json(['should_show' => false]);
    }

    /**
     * Mark feedback modal as dismissed
     */
    public function dismissModal()
    {
        session(['qricket_feedback_dismissed' => true]);
        session(['qricket_feedback_dismissed_date' => now()->toDateString()]);
        
        return response()->json(['success' => true]);
    }
}