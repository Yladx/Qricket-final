<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventActivityLog;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LogsController extends Controller
{
    public function index()
    {
        $logs = EventActivityLog::with(['event', 'user'])
            ->latest()
            ->paginate(50);

        return view('admin.logs.index', compact('logs'));
    }

    public function eventLogs(Event $event)
    {
        $logs = EventActivityLog::where('event_id', $event->id)
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('admin.logs.event', compact('logs', 'event'));
    }

    public function userLogs(User $user)
    {
        $logs = EventActivityLog::where('user_id', $user->id)
            ->with('event')
            ->latest()
            ->paginate(20);

        return view('admin.logs.user', compact('logs', 'user'));
    }

    public function search(Request $request)
    {
        $query = EventActivityLog::with(['event', 'user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('event', function($eventQuery) use ($search) {
                      $eventQuery->where('title', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->latest()->paginate(50);

        return view('admin.logs.index', compact('logs'));
    }

    public function export(Request $request)
    {
        $query = EventActivityLog::with(['event', 'user']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->latest()->get();

        // Log admin activity
        Auth::guard('admin')->user()->logActivity('logs_export', [
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'total_logs_exported' => $logs->count(),
            'export_format' => 'CSV',
        ], 'Exported activity logs to CSV');

        $csvData = [];
        $csvData[] = ['Date', 'User', 'Event', 'Action', 'Description'];

        foreach ($logs as $log) {
            $csvData[] = [
                $log->created_at->format('Y-m-d H:i:s'),
                $log->user ? $log->user->name : 'N/A',
                $log->event ? $log->event->title : 'N/A',
                $log->action,
                $log->description,
            ];
        }

        $filename = 'activity_logs_' . date('Y-m-d_H-i-s') . '.csv';
        
        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function statistics()
    {
        $stats = [
            'total_logs' => EventActivityLog::count(),
            'today_logs' => EventActivityLog::whereDate('created_at', today())->count(),
            'this_week_logs' => EventActivityLog::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'this_month_logs' => EventActivityLog::whereMonth('created_at', now()->month)->count(),
            'top_actions' => EventActivityLog::select('action', DB::raw('count(*) as count'))
                ->groupBy('action')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            'active_users' => EventActivityLog::select('user_id', DB::raw('count(*) as count'))
                ->with('user')
                ->groupBy('user_id')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
        ];

        return response()->json($stats);
    }
}