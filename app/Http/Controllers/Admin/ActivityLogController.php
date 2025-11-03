<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        // Get activity logs for the current admin
        $query = AdminActivityLog::where('admin_id', $admin->id)
            ->orderBy('created_at', 'desc');
        
        // Filter by action if provided
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        
        // Filter by date range if provided
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $logs = $query->paginate(20);
        
        // Get available actions for filter dropdown
        $availableActions = AdminActivityLog::where('admin_id', $admin->id)
            ->distinct()
            ->pluck('action')
            ->sort()
            ->values();
        
        return view('admin.activity-log.index', compact('logs', 'availableActions'));
    }
    
    public function show(AdminActivityLog $log)
    {
        // Ensure the log belongs to the current admin
        if ($log->admin_id !== Auth::guard('admin')->user()->id) {
            abort(403, 'Unauthorized access to activity log.');
        }
        
        return view('admin.activity-log.show', compact('log'));
    }
}
