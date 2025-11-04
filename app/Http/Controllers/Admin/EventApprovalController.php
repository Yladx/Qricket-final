<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use App\Models\AdminActivityLog;
use App\Events\AdminDashboardUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\NotificationService;

class EventApprovalController extends Controller
{
    public function index(Request $request)
    {
        // Left side: All events with filters
        $allQuery = Event::with('user')
            ->orderBy('created_at', 'desc');

        $hideDrafts = $request->boolean('hide_drafts', false);
        if ($hideDrafts) {
            $allQuery->where('status', '!=', 'draft');
        }

        $allStatus = $request->get('status'); // optional; when null => all
        if ($allStatus && $allStatus !== 'all') {
            $allQuery->where('status', $allStatus);
        }

        if ($request->filled('organizer')) {
            $allQuery->whereHas('user', function($q) use ($request){
                $q->where('first_name', 'like', '%'.$request->organizer.'%')
                  ->orWhere('last_name', 'like', '%'.$request->organizer.'%')
                  ->orWhere('email', 'like', '%'.$request->organizer.'%');
            });
        }

        // Filter for "My Posted Events" (admin-created events)
        if ($request->boolean('my_events', false)) {
            $allQuery->whereNull('user_id');
        }

        // Filter for "Free Events"
        if ($request->boolean('free_events', false)) {
            $allQuery->where('is_free', true);
        }

        if ($request->filled('q')) {
            $allQuery->where('title', 'like', '%'.$request->q.'%');
        }

        $allEvents = $allQuery->paginate(12)->appends($request->query());
        
        // For admin-created events, load the admin user
        $currentAdminId = Auth::guard('admin')->id();
        foreach ($allEvents as $event) {
            if ($event->user_id === null && $currentAdminId) {
                // Load the admin user for this event
                $event->user = \App\Models\Admin::find($currentAdminId);
            }
        }

        // Organizer list for autocomplete in filters
        $organizerList = User::select('id', 'first_name', 'last_name', 'email')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        // Right side: Approvals only (with sorting)
        $pendingQuery = Event::where('events.status', 'pending_approval')
            ->with('user');

        // Join latest pending verification request for sorting
        $latestPendingSub = \DB::table('event_verification_requests as evr1')
            ->select('evr1.event_id', \DB::raw('MAX(evr1.id) as latest_id'))
            ->where('evr1.status', 'pending')
            ->groupBy('evr1.event_id');
        $pendingQuery->leftJoinSub($latestPendingSub, 'evr_latest', function($join) {
            $join->on('evr_latest.event_id', '=', 'events.id');
        })->leftJoin('event_verification_requests as evr', function($join) {
            $join->on('evr.id', '=', 'evr_latest.latest_id');
        })->addSelect('events.*', 'evr.submitted_at as evr_submitted_at');

        // Handle sorting for pending events
        $pendingSort = $request->get('pending_sort', 'submitted_desc');
        switch ($pendingSort) {
            case 'submitted_asc':
                $pendingQuery->orderBy('evr_submitted_at', 'asc');
                break;
            case 'name_asc':
                $pendingQuery->orderBy('title', 'asc');
                break;
            case 'name_desc':
                $pendingQuery->orderBy('title', 'desc');
                break;
            case 'start_date_asc':
                $pendingQuery->orderBy('start_date', 'asc');
                break;
            case 'start_date_desc':
                $pendingQuery->orderBy('start_date', 'desc');
                break;
            case 'submitted_desc':
            default:
                $pendingQuery->orderBy('evr_submitted_at', 'desc');
                break;
        }

        // For sidebar display, limit to 5 events
        $pendingEventsSidebar = $pendingQuery->get()->take(5);
        
        // For admin-created events, load the admin user
        foreach ($pendingEventsSidebar as $event) {
            if ($event->user_id === null && $currentAdminId) {
                $event->user = \App\Models\Admin::find($currentAdminId);
            }
        }
        
        // For pagination, get full results
        $pendingEvents = $pendingQuery->paginate(12)->appends($request->query());

        // If this is an AJAX request, return only the pending events section
        if ($request->ajax()) {
            return view('admin.events.partials.pending-events', compact('pendingEvents'));
        }

        return view('admin.events.approval', compact('allEvents', 'pendingEvents', 'pendingEventsSidebar', 'hideDrafts', 'allStatus', 'organizerList'));
    }

    public function pending(Request $request)
    {
        // Only show pending approval events
        $pendingQuery = Event::with('user')
            ->where('events.status', 'pending_approval');
        // Join latest pending request for sorting
        $latestPendingSub = \DB::table('event_verification_requests as evr1')
            ->select('evr1.event_id', \DB::raw('MAX(evr1.id) as latest_id'))
            ->where('evr1.status', 'pending')
            ->groupBy('evr1.event_id');
        $pendingQuery->leftJoinSub($latestPendingSub, 'evr_latest', function($join) {
            $join->on('evr_latest.event_id', '=', 'events.id');
        })->leftJoin('event_verification_requests as evr', function($join) {
            $join->on('evr.id', '=', 'evr_latest.latest_id');
        })->addSelect('events.*', 'evr.submitted_at as evr_submitted_at')
          ->orderBy('evr_submitted_at', 'desc');

        // Filter for "My Posted Events" (admin-created events)
        if ($request->boolean('my_events', false)) {
            $pendingQuery->whereNull('user_id');
        }

        if ($request->filled('organizer')) {
            $pendingQuery->whereHas('user', function($q) use ($request){
                $q->where('first_name', 'like', '%'.$request->organizer.'%')
                  ->orWhere('last_name', 'like', '%'.$request->organizer.'%')
                  ->orWhere('email', 'like', '%'.$request->organizer.'%');
            });
        }

        if ($request->filled('q')) {
            $pendingQuery->where('title', 'like', '%'.$request->q.'%');
        }

        $pendingEvents = $pendingQuery->paginate(12)->appends($request->query());
        
        // For admin-created events, load the admin user
        $currentAdminId = Auth::guard('admin')->id();
        foreach ($pendingEvents as $event) {
            if ($event->user_id === null && $currentAdminId) {
                $event->user = \App\Models\Admin::find($currentAdminId);
            }
        }

        // Organizer list for autocomplete in filters
        $organizerList = User::select('id', 'first_name', 'last_name', 'email')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('admin.events.pending', compact('pendingEvents', 'organizerList'));
    }

    public function show($id)
    {
        $event = Event::where('events.id', $id)
                     ->whereIn('status', ['pending_approval', 'draft'])
                     ->with('user')
                     ->first();

        if (!$event) {
            return redirect()->route('admin.events.approval.index')
                           ->with('error', 'Event not found.');
        }

        // Load event data from R2 and DB
        $layoutData = $this->loadLayoutDataFromR2($id);
        // Load ticket types from database (preferred) or R2 fallback
        try {
            $ticketTypes = $event->eventTickets()->orderBy('id')->get()->map(function($t){
                return [
                    'id' => $t->id,
                    'name' => $t->name,
                    'price' => $t->price,
                    'description' => $t->description,
                    'quantity' => $t->quantity,
                    'sold_count' => $t->sold_count ?? 0,
                    'is_active' => $t->is_active ?? true,
                    'is_featured' => $t->is_featured ?? false,
                    'popular' => (bool)($t->is_featured ?? false),
                ];
            })->toArray();
            
            \Log::info('Loaded ticket types from database (admin approval)', [
                'event_id' => $id,
                'ticket_count' => count($ticketTypes),
                'tickets' => $ticketTypes
            ]);
        } catch (\Throwable $th) {
            \Log::warning('Failed to load ticket types from DB, falling back to R2 (admin approval)', [
                'event_id' => $id, 
                'error' => $th->getMessage()
            ]);
            $ticketTypes = $this->loadTicketTypesFromR2($id);
        }
        
        $ticketSettings = $this->loadTicketSettingsFromR2($id);
        
        // Ensure ticketSettings is always an array
        if (!is_array($ticketSettings)) {
            $ticketSettings = [];
        }
        
        // Ensure ticket_colors always exists in ticketSettings
        if (!isset($ticketSettings['ticket_colors']) || empty($ticketSettings['ticket_colors']) || !is_array($ticketSettings['ticket_colors'])) {
            $ticketSettings['ticket_colors'] = [
                'primary' => '#10b981',
                'secondary' => '#f59e0b',
                'accent' => '#059669',
            ];
        }
        
        \Log::info('Loaded ticket settings from R2 (admin approval)', [
            'event_id' => $id,
            'ticket_settings' => $ticketSettings,
            'ticket_colors' => $ticketSettings['ticket_colors'] ?? 'missing'
        ]);
        
        // Load staff data from database (preferred) or R2 fallback
        try {
            $staffMembers = $event->staff()->with('user:id,first_name,last_name,email')->get()->map(function($staff) {
                return [
                    'id' => $staff->user_id,
                    'name' => trim(($staff->user->first_name ?? '') . ' ' . ($staff->user->last_name ?? '')) ?: $staff->user->email,
                    'email' => $staff->user->email,
                    'role' => $staff->role,
                    'can_manage_event' => $staff->can_manage_event,
                    'can_manage_staff' => $staff->can_manage_staff,
                    'can_manage_ticket' => $staff->can_manage_ticket,
                    'can_manage_news' => $staff->can_manage_news,
                    'can_manage_attendance' => $staff->can_manage_attendance,
                    'can_manage_sales' => $staff->can_manage_sales,
                ];
            })->toArray();
            
            $staffSettings = $this->loadStaffSettingsFromR2($id);
            
            $staffData = [
                'members' => $staffMembers,
                'settings' => $staffSettings
            ];
            
            \Log::info('Loaded staff data from database (admin approval)', [
                'event_id' => $id,
                'staff_count' => count($staffMembers),
                'staff_members' => $staffMembers,
                'staff_settings' => $staffSettings
            ]);
        } catch (\Throwable $th) {
            \Log::warning('Failed to load staff data from DB, falling back to R2 (admin approval)', [
                'event_id' => $id, 
                'error' => $th->getMessage()
            ]);
            $staffData = $this->loadStaffDataFromR2($id);
        }
        
        // Load certificate data only if enabled
        $certificateData = $this->loadCertificateDataFromR2($id);
        // Load certificate settings and merge into data for preview
        $certificateSettings = $this->loadCertificateSettingsFromR2($id);
        if (!empty($certificateSettings)) {
            $certificateData = array_merge($certificateSettings, $certificateData ?? []);
        }
        // Only enabled if DB column is explicitly true (respect database setting)
        $certificateEnabled = (bool)($event->certificate_enabled ?? false);
        
        \Log::info('Certificate loading status (admin approval)', [
            'event_id' => $id,
            'certificate_enabled' => $certificateEnabled,
            'has_certificate_data' => !empty($certificateData),
            'certificate_data_keys' => array_keys($certificateData ?? [])
        ]);

        // Load latest verification request timestamps
        $submittedAt = null;
        try {
            $latestRequest = \App\Models\EventVerificationRequest::where('event_id', $id)
                ->latest('id')
                ->first();
            $submittedAt = $latestRequest?->submitted_at;
        } catch (\Exception $e) {
            // ignore
        }

        return view('admin.events.approval-show', compact('event', 'layoutData', 'ticketTypes', 'ticketSettings', 'staffData', 'certificateData', 'certificateEnabled', 'submittedAt'));
    }

    public function approve(Request $request, $id)
    {
        $event = Event::where('id', $id)
                     ->where('status', 'pending_approval')
                     ->first();

        if (!$event) {
            return redirect()->route('admin.events.approval.index')
                           ->with('error', 'Event not found.');
        }

        $event->update([
            'status' => 'published',
        ]);

        // Update latest verification request as approved
        try {
            $latestRequest = \App\Models\EventVerificationRequest::where('event_id', $event->id)
                ->where('status', 'pending')
                ->latest('id')
                ->first();
            if ($latestRequest) {
                $latestRequest->update([
                    'status' => 'approved',
                    'notes' => $request->input('approval_notes'),
                    'reviewed_by' => Auth::guard('admin')->id(),
                    'reviewed_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to update verification request on approve', [
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);
        }

        // Log admin activity
        Auth::guard('admin')->user()->logActivity('event_approve', [
            'event_id' => $event->id,
            'event_title' => $event->title,
            'organizer_id' => $event->user_id,
            'approval_notes' => $request->input('approval_notes'),
        ], 'Approved event: ' . $event->title);

        // Notify organizer (DB + realtime toast)
        $this->notifyOrganizer($event, 'accept');
        NotificationService::sendUserNotification(
            (int) $event->user_id,
            'Your event "' . ($event->title ?? 'Untitled Event') . '" was approved and published.',
            'success',
            'Event Approved'
        );

        // Trigger admin dashboard update
        try {
            $adminController = new \App\Http\Controllers\Admin\DashboardAnalyticsController();
            $adminController->triggerUpdate(null, 'event_approved');
        } catch (\Exception $e) {
            \Log::error('Failed to trigger admin dashboard update after event approval: ' . $e->getMessage());
        }

        return redirect()->route('admin.events.approval.index')
                       ->with('success', 'Event approved successfully.');
    }

    public function reject(Request $request, $id)
    {
        $event = Event::where('id', $id)
                     ->where('status', 'pending_approval')
                     ->first();

        if (!$event) {
            return redirect()->route('admin.events.approval.index')
                           ->with('error', 'Event not found.');
        }

        $event->update([
            'status' => 'rejected',
        ]);

        // Update latest verification request as rejected
        try {
            $latestRequest = \App\Models\EventVerificationRequest::where('event_id', $event->id)
                ->where('status', 'pending')
                ->latest('id')
                ->first();
            if ($latestRequest) {
                $latestRequest->update([
                    'status' => 'rejected',
                    'notes' => $request->input('rejection_reason'),
                    'reviewed_by' => Auth::guard('admin')->id(),
                    'reviewed_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to update verification request on reject', [
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);
        }

        // Log admin activity
        Auth::guard('admin')->user()->logActivity('event_reject', [
            'event_id' => $event->id,
            'event_title' => $event->title,
            'organizer_id' => $event->user_id,
            'rejection_reason' => $request->input('rejection_reason'),
        ], 'Rejected event: ' . $event->title);

        // Notify organizer (DB + realtime toast)
        $this->notifyOrganizer($event, 'reject');
        NotificationService::sendUserNotification(
            (int) $event->user_id,
            'Your event "' . ($event->title ?? 'Untitled Event') . '" was rejected.' . ($request->input('rejection_reason') ? ' Reason: ' . $request->input('rejection_reason') : ''),
            'warning',
            'Event Rejected'
        );

        // Trigger admin dashboard update
        try {
            $adminController = new \App\Http\Controllers\Admin\DashboardAnalyticsController();
            $adminController->triggerUpdate(null, 'event_rejected');
        } catch (\Exception $e) {
            \Log::error('Failed to trigger admin dashboard update after event rejection: ' . $e->getMessage());
        }

        return redirect()->route('admin.events.approval.index')
                       ->with('success', 'Event rejected successfully.');
    }

    public function reset(Request $request, $id)
    {
        $event = Event::where('id', $id)
                     ->whereIn('status', ['published', 'draft'])
                     ->first();

        if (!$event) {
            return redirect()->route('admin.events.approval.index')
                           ->with('error', 'Event not found.');
        }

        $event->update([
            'status' => 'pending_approval',
        ]);

        // Create a fresh verification request record
        try {
            \App\Models\EventVerificationRequest::create([
                'event_id' => $event->id,
                'organizer_id' => $event->user_id,
                'status' => 'pending',
                'submitted_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to create verification request on reset', [
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);
        }

        // Notify organizer that event was reset for approval
        $this->notifyOrganizer($event, 'reset');
        NotificationService::sendUserNotification(
            (int) $event->user_id,
            'Your event "' . ($event->title ?? 'Untitled Event') . '" has been reset and is pending approval again.',
            'info',
            'Event Reset for Approval'
        );

        return redirect()->route('admin.events.approval.index')
                       ->with('success', 'Event reset for approval successfully.');
    }

    private function loadTicketTypesFromR2($eventId)
    {
        try {
            $eventDir = "events/{$eventId}";
            $ticketTypesPath = $eventDir . '/ticket_types.json';
            
            if (Storage::disk('r2')->exists($ticketTypesPath)) {
                $ticketTypesJson = Storage::disk('r2')->get($ticketTypesPath);
                return json_decode($ticketTypesJson, true) ?? [];
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load ticket types from R2: ' . $e->getMessage());
        }
        
        return [];
    }

    private function isCertificateEnabled($eventId)
    {
        try {
            $eventDir = "events/{$eventId}";
            $certificateSettingsPath = $eventDir . '/certificate_settings.json';
            
            if (Storage::disk('r2')->exists($certificateSettingsPath)) {
                $certificateSettingsJson = Storage::disk('r2')->get($certificateSettingsPath);
                $settings = json_decode($certificateSettingsJson, true) ?? [];
                return (bool)($settings['enabled'] ?? false);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load certificate settings from R2: ' . $e->getMessage());
        }
        
        return false;
    }

    private function loadStaffDataFromR2($eventId)
    {
        try {
            $eventDir = "events/{$eventId}";
            $staffDataPath = $eventDir . '/staff_data.json';
            
            if (Storage::disk('r2')->exists($staffDataPath)) {
                $staffDataJson = Storage::disk('r2')->get($staffDataPath);
                return json_decode($staffDataJson, true) ?? [];
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load staff data from R2: ' . $e->getMessage());
        }
        
        return [];
    }

    private function loadCertificateDataFromR2($eventId)
    {
        try {
            $eventDir = "events/{$eventId}";
            $certificateDataPath = $eventDir . '/certificate_data.json';
            
            if (Storage::disk('r2')->exists($certificateDataPath)) {
                $certificateDataJson = Storage::disk('r2')->get($certificateDataPath);
                return json_decode($certificateDataJson, true) ?? [];
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load certificate data from R2: ' . $e->getMessage());
        }
        
        return [];
    }

    private function loadLayoutDataFromR2($eventId)
    {
        try {
            $eventDir = "events/{$eventId}";
            $layoutPath = $eventDir . '/layout.json';

            if (Storage::disk('r2')->exists($layoutPath)) {
                $layoutJson = Storage::disk('r2')->get($layoutPath);
                return json_decode($layoutJson, true) ?? [];
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load layout from R2: ' . $e->getMessage());
        }

        return [];
    }

    private function loadStaffSettingsFromR2($eventId)
    {
        try {
            $eventDir = "events/{$eventId}";
            $staffSettingsPath = $eventDir . '/staff_settings.json';
            
            if (Storage::disk('r2')->exists($staffSettingsPath)) {
                $staffSettingsJson = Storage::disk('r2')->get($staffSettingsPath);
                return json_decode($staffSettingsJson, true) ?? [];
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load staff settings from R2: ' . $e->getMessage());
        }
        
        return [];
    }

    private function loadCertificateSettingsFromR2($eventId)
    {
        try {
            $eventDir = "events/{$eventId}";
            $certificateSettingsPath = $eventDir . '/certificate_settings.json';
            if (Storage::disk('r2')->exists($certificateSettingsPath)) {
                $json = Storage::disk('r2')->get($certificateSettingsPath);
                return json_decode($json, true) ?? [];
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load certificate settings from R2: ' . $e->getMessage());
        }
        return [];
    }

    private function loadTicketSettingsFromR2($eventId)
    {
        try {
            $eventDir = "events/{$eventId}";
            $settingsPath = $eventDir . '/ticket_settings.json';

            if (Storage::disk('r2')->exists($settingsPath)) {
                $json = Storage::disk('r2')->get($settingsPath);
                $data = json_decode($json, true);
                
                if (is_array($data)) {
                    // Ensure ticket_colors are present with defaults if missing
                    if (!isset($data['ticket_colors']) || empty($data['ticket_colors']) || !is_array($data['ticket_colors'])) {
                        $data['ticket_colors'] = [
                            'primary' => '#10b981',
                            'secondary' => '#f59e0b',
                            'accent' => '#059669',
                        ];
                    }
                    // Ensure all color keys exist
                    $defaultColors = [
                        'primary' => '#10b981',
                        'secondary' => '#f59e0b',
                        'accent' => '#059669',
                    ];
                    $data['ticket_colors'] = array_merge($defaultColors, $data['ticket_colors'] ?? []);
                    return $data;
                }
                // Return defaults if parsing failed
                return [
                    'ticket_colors' => [
                        'primary' => '#10b981',
                        'secondary' => '#f59e0b',
                        'accent' => '#059669',
                    ],
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load ticket settings from R2: ' . $e->getMessage());
        }
        
        // Return default settings with ticket_colors if file doesn't exist or parsing fails
        return [
            'ticket_colors' => [
                'primary' => '#10b981',
                'secondary' => '#f59e0b',
                'accent' => '#059669',
            ],
        ];
    }

    private function notifyOrganizer($event, $action)
    {
        try {
            // Map action to title/message and ensure enums match migration
            $title = null;
            $message = null;
            if ($action === 'accept') {
                $title = 'Event Approved';
                $message = 'Your event "' . ($event->title ?? 'Untitled Event') . '" was approved and published.';
            } elseif ($action === 'reject') {
                $title = 'Event Rejected';
                $rejectionReason = $event->rejection_reason ? ' Reason: ' . $event->rejection_reason : '';
                $message = 'Your event "' . ($event->title ?? 'Untitled Event') . '" was rejected.' . $rejectionReason;
            } elseif ($action === 'reset') {
                $title = 'Event Reset for Approval';
                $message = 'Your event "' . ($event->title ?? 'Untitled Event') . '" has been reset and is pending approval again.';
            }

            \App\Models\Notification::create([
                'user_id' => (int) $event->user_id,
                'type' => 'event', // enum: organizer|ticket|event
                'action' => $action, // enum: reply|verified|accept|reject
                'reference_id' => (int) $event->id,
                'title' => $title,
                'message' => $message,
                'is_read' => false,
            ]);

            \Log::info('Organizer notified of event ' . $action, [
                'event_id' => $event->id,
                'organizer_id' => $event->user_id,
                'action' => $action
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to notify organizer: ' . $e->getMessage());
        }
    }
}