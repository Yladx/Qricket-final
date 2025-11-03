<?php

namespace App\Http\Controllers\Organizer\EventManagement;

use App\Http\Controllers\Controller;

use App\Models\Event;
use App\Models\EventTicketType;
use App\Models\EventStaff;
use App\Models\EventActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Organizer\EventManagement\Traits\DraftHelpers;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    use DraftHelpers;

    public function step6(Request $request)
    {
        \Log::info('Step6 view load - session check', [
            'session_id' => $request->session()->getId(),
            'user_id' => Auth::id(),
            'publish_option' => $request->input('publish_option'),
        ]);

        $eventId = $request->get('id');
        
        if (!$eventId) {
            return redirect()->route('organizer.events.index')->with('error', 'Event ID is required.');
        }

        $isAdmin = Auth::guard('admin')->check();
        
        $event = Event::where('id', $eventId)
                     ->whereIn('status', ['draft', 'rejected']);
        
        // Allow admins to access events with user_id === null (admin-created events)
        if ($isAdmin) {
            $event->where(function($query) {
                $query->where('user_id', Auth::id())
                      ->orWhereNull('user_id');
            });
        } else {
            $event->where('user_id', Auth::id());
        }
        
        $event = $event->first();

        if (!$event) {
            return redirect()->route('organizer.events.index')->with('error', 'Event not found or not authorized.');
        }

        // Check if rejected event can be resubmitted
        if ($event->status === 'rejected' && !$event->canResubmit()) {
            $timeRemaining = $event->getResubmitTimeRemaining();
            return redirect()->route('organizer.events.index')->with('error', "Event was rejected. You can resubmit {$timeRemaining}.");
        }

        // Load all event data from database and R2
        $eventData = [
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'category' => $event->category,
            'start_date' => $event->start_date,
            'end_date' => $event->end_date,
            'location' => $event->location,
            'poster' => $event->poster_url,  // Use URL accessor
            'thumbnail' => $event->thumbnail_url,  // Use URL accessor
            'existing_poster_url' => $event->poster_url,
            'existing_thumbnail_url' => $event->thumbnail_url,
            'status' => $event->status,
            'rejection_reason' => $event->rejection_reason,
            'rejected_at' => $event->rejected_at,
        ];

        \Log::info('Review Step6 - Event data loaded', [
            'event_id' => $eventId,
            'poster_url' => $eventData['poster'],
            'thumbnail_url' => $eventData['thumbnail'],
            'poster_path' => $event->poster,
            'thumbnail_path' => $event->thumbnail,
        ]);

        // Load ticket types from database (EventTicket model)
        $ticketTypes = $event->eventTickets()->orderBy('id')->get()->map(function($t){
            return [
                'id' => $t->id,
                'name' => $t->name,
                'description' => $t->description,
                'price' => $t->price,
                'quantity' => $t->quantity,
                'sale_start' => optional($t->sale_start)->format('Y-m-d H:i'),
                'sale_end' => optional($t->sale_end)->format('Y-m-d H:i'),
                'is_active' => $t->is_active,
            ];
        })->toArray();
        $eventData['ticket_types'] = $ticketTypes;

        // Load ticket settings from R2
        $ticketSettings = $this->loadTicketSettingsFromR2($eventId);
        $eventData['ticket_settings'] = $ticketSettings;
        
        \Log::info('Review Step6 - Ticket settings loaded', [
            'event_id' => $eventId,
            'ticket_settings' => $ticketSettings,
            'ticket_colors' => $ticketSettings['ticket_colors'] ?? 'not_found',
            'ticket_colors_type' => gettype($ticketSettings['ticket_colors'] ?? null),
            'ticket_colors_is_array' => is_array($ticketSettings['ticket_colors'] ?? null)
        ]);

        // Load staff data from event_staff table (not R2 JSON)
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
        $eventData['staff_data'] = $staffMembers;

        // Load staff settings from R2
        $staffSettings = $this->loadStaffSettingsFromR2($eventId);
        $eventData['staff_settings'] = $staffSettings;

        // Load certificate data from R2
        $certificateData = $this->loadCertificateDataFromR2($eventId);
        $eventData['certificate_data'] = $certificateData;
        
        \Log::info('Review Step6 - Certificate data loaded', [
            'event_id' => $eventId,
            'certificate_data_keys' => array_keys($certificateData),
            'has_org_line1' => !empty($certificateData['org_line1']),
            'org_line1_value' => $certificateData['org_line1'] ?? 'not_found',
            'theme_primary' => $certificateData['theme_primary'] ?? 'not_found',
            'theme_secondary' => $certificateData['theme_secondary'] ?? 'not_found'
        ]);

        // Check if certificate is enabled (not skipped) - prioritize event table over settings
        $certificateEnabled = $event->certificate_enabled ?? true;
        $eventData['certificate_enabled'] = $certificateEnabled;
        
        \Log::info('Review Step6 - Certificate enabled status', [
            'event_id' => $eventId,
            'certificate_enabled_from_db' => $event->certificate_enabled,
            'certificate_enabled_final' => $certificateEnabled
        ]);

        return view('organizer.events.create.step6', compact('eventData', 'eventId', 'event'));
    }

    public function step6Store(Request $request)
    {
        \Log::info('Step6 store submit - session check', [
            'session_id' => $request->session()->getId(),
            'user_id' => Auth::id(),
            'publish_option' => $request->input('publish_option'),
            'publish_at' => $request->input('publish_at'),
        ]);

        $eventId = $request->get('id');
        
        if (!$eventId) {
            return redirect()->route('organizer.events.index')->with('error', 'Event ID is required.');
        }

        $event = Event::find($eventId);
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event && $event->user_id === Auth::id();
        $isAdminEvent = $event && $event->user_id === null && $isAdmin;
        
        if (!$event || (!$isOwner && !$isAdminEvent)) {
            return redirect()->route('organizer.events.index')->with('error', 'Event not found or unauthorized.');
        }

        // Check if rejected event can be resubmitted
        if ($event->status === 'rejected' && !$event->canResubmit()) {
            $timeRemaining = $event->getResubmitTimeRemaining();
            return redirect()->route('organizer.events.index')->with('error', "Event was rejected. You can resubmit {$timeRemaining}.");
        }

        $publishOption = $request->input('publish_option', 'draft');
        $publishAt = $request->input('publish_at');

        // Check subscription limits only when publishing (not when saving as draft)
        // Skip subscription check for admin-created events
        if ($publishOption !== 'draft' && !$isAdminEvent) {
            $user = Auth::user();
            if ($user) {
                // Distinguish free vs paid events: free events use free_events_count; paid use events_per_month
                $subscription = $user->currentSubscription();
                $plan = $subscription?->getPlan() ?? \App\Models\SubscriptionPlan::where('slug','free')->first();
                $currentMonthStart = now()->startOfMonth();

                if (($event->is_free ?? false) && ($plan?->free_events_count ?? 0) > 0) {
                    $publishedFree = $user->events()
                        ->where('status', 'published')
                        ->where('is_free', true)
                        ->where('created_at', '>=', $currentMonthStart)
                        ->count();
                    $freeRemaining = max(0, (int)$plan->free_events_count - $publishedFree);
                    if ($freeRemaining <= 0) {
                        $msg = 'You have reached your monthly free event publishing limit for your plan.';
                        if ($request->expectsJson()) {
                            return response()->json([
                                'error' => 'Free event publishing limit reached',
                                'message' => $msg,
                                'subscription_required' => true,
                                'remaining_events' => 0,
                            ], 403);
                        }
                        return redirect()->back()->with('error', $msg)->withInput();
                    }
                } else {
                    // Paid event check using existing helper (events_per_month)
                    if (!$user->canCreateEvent()) {
                        $remainingEvents = $user->getRemainingEventsThisMonth();
                        if ($request->expectsJson()) {
                            return response()->json([
                                'error' => 'Event publishing limit reached',
                                'message' => $remainingEvents === 0 
                                    ? 'You have reached your monthly event publishing limit. Please upgrade your subscription to publish more events.'
                                    : "You can publish {$remainingEvents} more events this month.",
                                'subscription_required' => true,
                                'remaining_events' => $remainingEvents,
                            ], 403);
                        }
                        return redirect()->back()
                            ->with('error', 'You have reached your monthly event publishing limit. Please upgrade your subscription to publish more events.')
                            ->withInput();
                    }
                }
            }
        }

        $status = $publishOption;
        $message = 'Event updated successfully!';

        if ($publishOption === 'pending_approval') {
            $event->update([
                'status' => 'pending_approval',
            ]);

            // Create a separate verification request record for cleaner tracking
            try {
                \App\Models\EventVerificationRequest::create([
                    'event_id' => $event->id,
                    'organizer_id' => $event->user_id,
                    'status' => 'pending',
                    'submitted_at' => now(),
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to create event verification request', [
                    'event_id' => $event->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            // Log event submission for approval
            EventActivityLog::log(
                $eventId,
                $this->getActivityLogUserId(),
                'event_submitted_for_approval',
                'Event submitted for admin approval',
                [
                    'event_title' => $event->title,
                    'submitted_at' => now()->toISOString()
                ]
            );
            
            // Create admin notification for event approval request
            try {
                $organizer = \App\Models\User::find($event->user_id);
                $organizerName = $organizer ? (trim(($organizer->first_name ?? '') . ' ' . ($organizer->last_name ?? '')) ?: $organizer->email) : 'Unknown Organizer';
                $notificationMessage = "{$organizerName} submitted event '{$event->title}' for approval";
                
                $notification = \App\Models\Notification::create([
                    'user_id' => null, // Admin notification
                    'admin_id' => null, // For all admins
                    'type' => 'event',
                    'action' => 'accept', // Using existing action for pending approval
                    'reference_id' => $event->id,
                    'title' => 'Event Approval Request',
                    'message' => $notificationMessage,
                    'data' => [
                        'event_id' => $event->id,
                        'event_title' => $event->title,
                        'organizer_id' => $event->user_id,
                        'organizer_name' => $organizerName,
                        'submitted_at' => now()->toISOString(),
                    ],
                    'is_read' => false,
                ]);

                // Broadcast admin notification with notification ID
                try {
                    event(new \App\Events\AdminNotification(
                        $notificationMessage,
                        'info',
                        'Event Approval Request',
                        $notification->id
                    ));
                } catch (\Exception $e) {
                    \Log::error('Failed to broadcast admin notification for event approval request: ' . $e->getMessage());
                }
            } catch (\Exception $e) {
                \Log::error('Failed to create admin notification for event approval request: ' . $e->getMessage());
            }
            
            $message = 'Event submitted for admin approval successfully!';
        } elseif ($publishOption === 'scheduled' && $publishAt) {
            $event->update([
                'status' => 'scheduled',
                'scheduled_publish_at' => $publishAt
            ]);
            
            // Log event scheduling
            EventActivityLog::log(
                $eventId,
                $this->getActivityLogUserId(),
                'event_scheduled',
                'Event scheduled for publication',
                [
                    'event_title' => $event->title,
                    'scheduled_publish_at' => $publishAt
                ]
            );
            
            $message = 'Event scheduled for publication successfully!';
        } else {
            $event->update(['status' => 'draft']);
            
            // Log event creation/save as draft
            EventActivityLog::log(
                $eventId,
                $this->getActivityLogUserId(),
                'event_created',
                'Event created and saved as draft',
                [
                    'event_title' => $event->title,
                    'status' => 'draft'
                ]
            );
            
            $message = 'Event saved as draft successfully!';
        }

        return redirect()->route('organizer.events.index')->with('success', $message);
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

    private function loadTicketSettingsFromR2($eventId)
    {
        try {
            $eventDir = "events/{$eventId}";
            $ticketSettingsPath = $eventDir . '/ticket_settings.json';
            
            if (Storage::disk('r2')->exists($ticketSettingsPath)) {
                $ticketSettingsJson = Storage::disk('r2')->get($ticketSettingsPath);
                $decoded = json_decode($ticketSettingsJson, true) ?? [];
                
                \Log::info('Raw ticket settings from R2', [
                    'event_id' => $eventId,
                    'raw_json' => $ticketSettingsJson,
                    'decoded' => $decoded,
                    'ticket_colors_in_decoded' => $decoded['ticket_colors'] ?? 'not_found'
                ]);
                
                // Ensure ticket_colors are present with defaults if missing
                if (!isset($decoded['ticket_colors']) || empty($decoded['ticket_colors'])) {
                    $decoded['ticket_colors'] = [
                        'primary' => '#10b981',
                        'secondary' => '#f59e0b',
                        'accent' => '#059669',
                    ];
                }
                
                return $decoded;
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load ticket settings from R2: ' . $e->getMessage());
        }
        
        return [
            'max_tickets_per_order' => 10,
            'refund_policy' => 'Refunds are available up to 7 days before the event. A processing fee may apply.',
            'allow_refunds' => true,
            'require_approval' => false,
            'allow_share_ticket' => true,
            'limit_per_user' => false,
            'max_tickets_per_user' => 1,
            'ticket_colors' => [
                'primary' => '#10b981',
                'secondary' => '#f59e0b',
                'accent' => '#059669',
            ],
        ];
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

    private function notifyAdminsForApproval($event)
    {
        try {
            // Get all admin users
            $admins = \App\Models\User::where('organizer', true)
                                     ->where('verified_organizer', '!=', null)
                                     ->get();

            foreach ($admins as $admin) {
                // Create notification for admin using correct fillable fields
                \App\Models\Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'event_approval_required',
                    'action' => 'approval_required',
                    'reference_id' => $event->id,
                    'is_read' => false,
                ]);
            }

            \Log::info('Admin notifications sent for event approval', [
                'event_id' => $event->id,
                'admin_count' => $admins->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to notify admins for approval: ' . $e->getMessage());
        }
    }

    private function loadCertificateDataFromR2($eventId)
    {
        try {
            // Try multiple possible paths for backward compatibility
            $candidates = [
                "events/{$eventId}/certificate/certificate.json",
                "events/{$eventId}/certificate_data.json",
                "events/{$eventId}/certificate/certificate_data.json",
                "events/{$eventId}/certificate.json",
            ];
            
            foreach ($candidates as $certificateDataPath) {
                if (Storage::disk('r2')->exists($certificateDataPath)) {
                    $certificateDataJson = Storage::disk('r2')->get($certificateDataPath);
                    $data = json_decode($certificateDataJson, true) ?? [];
                    \Log::info('Certificate data loaded from R2', ['event_id' => $eventId, 'path' => $certificateDataPath]);
                    return $data;
                }
            }
            
            \Log::info('No certificate data found in R2', ['event_id' => $eventId]);
            return [];
            
        } catch (\Exception $e) {
            \Log::error('Failed to load certificate data from R2: ' . $e->getMessage());
        }
        
        return [];
    }

    private function checkCertificateEnabled($eventId)
    {
        try {
            $eventDir = "events/{$eventId}";
            $settingsPath = $eventDir . '/settings.json';
            
            if (Storage::disk('r2')->exists($settingsPath)) {
                $settingsJson = Storage::disk('r2')->get($settingsPath);
                $settings = json_decode($settingsJson, true) ?? [];
                
                // If certificate_enabled is explicitly set to false, then it's disabled
                if (isset($settings['certificate_enabled'])) {
                    return (bool) $settings['certificate_enabled'];
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to check certificate enabled status: ' . $e->getMessage());
        }
        
        // Default to enabled if not explicitly disabled
        return true;
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
