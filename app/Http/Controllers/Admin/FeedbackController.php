<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QricketFeedback;

class FeedbackController extends Controller
{
    public function index()
    {
        $feedback = QricketFeedback::website()
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Basic aggregates
        $stats = [
            'total' => QricketFeedback::website()->count(),
            'today' => QricketFeedback::website()->whereDate('created_at', today())->count(),
            'avg_navigation' => round(QricketFeedback::website()->whereNotNull('navigation_ease')->avg('navigation_ease') ?? 0, 1),
            'avg_information' => round(QricketFeedback::website()->whereNotNull('information_found')->avg('information_found') ?? 0, 1),
            'avg_design' => round(QricketFeedback::website()->whereNotNull('design_rating')->avg('design_rating') ?? 0, 1),
            'avg_performance' => round(QricketFeedback::website()->whereNotNull('performance_rating')->avg('performance_rating') ?? 0, 1),
            'avg_process' => round(QricketFeedback::website()->whereNotNull('process_satisfaction')->avg('process_satisfaction') ?? 0, 1),
        ];

        return view('admin.feedback.index', compact('feedback', 'stats'));
    }
}


