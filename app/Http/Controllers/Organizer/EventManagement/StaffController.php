<?php

namespace App\Http\Controllers\Organizer\EventManagement;

use App\Http\Controllers\Controller;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\User;
use App\Notifications\StaffAssignedNotification;

class StaffController extends Controller
{
    /**
     * Determine if the event is in the initial creation window.
     */
    private function isNewEventCreation(Event $event): bool
    {
        try {
            return ($event->status === 'draft') && $event->created_at && $event->created_at->diffInMinutes(now()) < 5;
        } catch (\Throwable $t) {
            return false;
        }
    }

    /**
     * Normalize staff settings for accurate comparisons.
     */
    private function normalizeStaffSettings(array $settings): array
    {
        return [
            'allow_staff_registration' => (bool)($settings['allow_staff_registration'] ?? false),
            'require_staff_approval' => (bool)($settings['require_staff_approval'] ?? false),
            'send_staff_notifications' => (bool)($settings['send_staff_notifications'] ?? false),
            'staff_welcome_message' => isset($settings['staff_welcome_message']) ? trim((string)$settings['staff_welcome_message']) : null,
            'staff_instructions' => isset($settings['staff_instructions']) ? trim((string)$settings['staff_instructions']) : null,
        ];
    }

    /**
     * Load staff settings from R2.
     */
    private function loadStaffSettingsFromR2(int $eventId): array
    {
        $eventDir = "events/{$eventId}";
        $path = $eventDir . '/staff_settings.json';
        if (Storage::disk('r2')->exists($path)) {
            $json = json_decode(Storage::disk('r2')->get($path), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                return $json;
            }
        }
        return [];
    }

    /**
     * Load staff data (members) from R2.
     */
    private function loadStaffDataFromR2(int $eventId): array
    {
        $eventDir = "events/{$eventId}";
        $path = $eventDir . '/staff_data.json';
        if (Storage::disk('r2')->exists($path)) {
            $json = json_decode(Storage::disk('r2')->get($path), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                return $json;
            }
        }
        return [];
    }

    /**
     * Queue staff activity into a 60s buffer and flush as one log entry.
     */
    private function queueStaffActivity(Event $event, int $userId, array $entry): void
    {
        // Skip activity logging while the event is being created (initial setup)
        if ($this->isNewEventCreation($event)) {
            \Log::info('Staff activity skipped during creation window', [
                'event_id' => $event->id,
                'user_id' => $userId,
            ]);
            return;
        }

        $key = "activity_buffer:staff:{$event->id}:{$userId}";
        $buffer = Cache::get($key, [
            'first_at' => now(),
            'last_at' => null,
            'entries' => [],
        ]);
        $buffer['entries'][] = $entry;
        $buffer['last_at'] = now();
        Cache::put($key, $buffer, now()->addMinutes(5));

        \Log::info('Staff activity queued', [
            'event_id' => $event->id,
            'user_id' => $userId,
            'first_at' => $buffer['first_at'],
            'seconds_since_first' => now()->diffInSeconds($buffer['first_at']),
            'entries_count' => count($buffer['entries'])
        ]);

        // Auto-flush after 60 seconds (only check if we have multiple entries to avoid false flush)
        if (count($buffer['entries']) >= 2 && now()->diffInSeconds($buffer['first_at']) >= 60) {
            \Log::info('Staff auto-flush triggered after 60s', [
                'event_id' => $event->id,
                'user_id' => $userId,
                'first_at' => $buffer['first_at'],
                'entries_count' => count($buffer['entries'])
            ]);
            $this->flushStaffActivity($event, $userId);
        }
    }

    /**
     * Flush staff activity buffer into a single activity log row.
     */
    private function flushStaffActivity(Event $event, int $userId, ?array $mergeEntry = null): void
    {
        // Skip flushing while in creation window
        if ($this->isNewEventCreation($event)) {
            $keyTmp = "activity_buffer:staff:{$event->id}:{$userId}";
            Cache::forget($keyTmp);
            \Log::info('Staff flush skipped during creation window', [
                'event_id' => $event->id,
                'user_id' => $userId,
            ]);
            return;
        }

        $key = "activity_buffer:staff:{$event->id}:{$userId}";
        $buffer = Cache::get($key, [
            'first_at' => now(),
            'last_at' => null,
            'entries' => [],
        ]);
        \Log::info('Staff flush start', [
            'event_id' => $event->id,
            'user_id' => $userId,
            'buffer_entries' => count($buffer['entries']),
            'merge_entry' => $mergeEntry ? true : false
        ]);
        if ($mergeEntry) {
            $buffer['entries'][] = $mergeEntry;
        }
        if (empty($buffer['entries'])) {
            \Log::info('Staff flush skipped - no entries');
            return;
        }
        $added = [];
        $removed = [];
        $edits = [];
        $memberEdits = [];
        foreach ($buffer['entries'] as $e) {
            if (!empty($e['added'])) { $added = array_merge($added, $e['added']); }
            if (!empty($e['removed'])) { $removed = array_merge($removed, $e['removed']); }
            if (!empty($e['edits'])) { $edits = array_merge($edits, $e['edits']); }
            if (!empty($e['member_edits'])) { $memberEdits = array_merge($memberEdits, $e['member_edits']); }
        }
        $added = array_values(array_unique($added));
        $removed = array_values(array_unique($removed));
        // Cancel out staff both added and removed within window
        $cancelled = array_intersect($added, $removed);
        if (!empty($cancelled)) {
            $added = array_values(array_diff($added, $cancelled));
            $removed = array_values(array_diff($removed, $cancelled));
        }
        // Collapse edits per field: keep first->last change
        $collapsedEdits = [];
        foreach ($edits as $field => $diff) {
            if (!isset($collapsedEdits[$field])) {
                $collapsedEdits[$field] = ['from' => $diff['from'] ?? null, 'to' => $diff['to'] ?? null];
            } else {
                $collapsedEdits[$field]['to'] = $diff['to'] ?? $collapsedEdits[$field]['to'];
            }
        }
        // Collapse member-specific edits by member name  
        $collapsedMemberEdits = [];
        foreach ($memberEdits as $me) {
            $memberName = $me['member'] ?? 'Unknown';
            $changes = $me['changes'] ?? [];
            if (!isset($collapsedMemberEdits[$memberName])) {
                $collapsedMemberEdits[$memberName] = [];
            }
            foreach ($changes as $field => $diff) {
                if (!isset($collapsedMemberEdits[$memberName][$field])) {
                    $collapsedMemberEdits[$memberName][$field] = ['from' => $diff['from'] ?? null, 'to' => $diff['to'] ?? null];
                } else {
                    $collapsedMemberEdits[$memberName][$field]['to'] = $diff['to'] ?? $collapsedMemberEdits[$memberName][$field]['to'];
                }
            }
        }
        
        // Build comprehensive description like tickets
        $descParts = [];
        if (!empty($added)) { $descParts[] = 'Added '.count($added).' staff'; }
        if (!empty($removed)) { $descParts[] = 'Removed '.count($removed).' staff'; }
        
        $editsSummaryParts = [];
        // Settings changes
        foreach ($collapsedEdits as $field => $diff) {
            $from = is_array($diff['from']) ? json_encode($diff['from']) : (string)($diff['from'] ?? '');
            $to = is_array($diff['to']) ? json_encode($diff['to']) : (string)($diff['to'] ?? '');
            $editsSummaryParts[] = ucfirst(str_replace('_',' ', $field)).' '.$from.' → '.$to;
        }
        // Member edits per member
        foreach ($collapsedMemberEdits as $memberName => $changes) {
            $parts = [];
            foreach ($changes as $field => $diff) {
                // Handle boolean permissions specially
                if (strpos($field, 'can_') === 0 || $field === 'allow_staff_registration' || $field === 'require_staff_approval' || $field === 'send_staff_notifications') {
                    $fromBool = filter_var($diff['from'], FILTER_VALIDATE_BOOLEAN);
                    $toBool = filter_var($diff['to'], FILTER_VALIDATE_BOOLEAN);
                    $fieldName = ucfirst(str_replace(['can_', '_'], ['', ' '], $field));
                    if (!$fromBool && $toBool) {
                        $parts[] = "Grant {$fieldName} access";
                    } elseif ($fromBool && !$toBool) {
                        $parts[] = "Remove {$fieldName} access";
                    }
                } else {
                    $from = is_array($diff['from']) ? json_encode($diff['from']) : (string)($diff['from'] ?? '');
                    $to = is_array($diff['to']) ? json_encode($diff['to']) : (string)($diff['to'] ?? '');
                    $parts[] = ucfirst(str_replace('_',' ', $field)).' '.$from.' → '.$to;
                }
            }
            if (!empty($parts)) {
                $editsSummaryParts[] = $memberName.': '.implode(', ', $parts);
            }
        }
        
        $description = trim(implode(' — ', array_filter([
            !empty($descParts) ? implode(', ', $descParts) : null,
            !empty($editsSummaryParts) ? ('Edits: '.implode(' | ', $editsSummaryParts)) : null,
        ]))) ?: 'Staff changes';
        $description = Str::limit($description, 255, '…');

        if (!empty($descParts) || !empty($editsSummaryParts)) {
            \Log::info('Staff activity log write', [
                'event_id' => $event->id,
                'user_id' => $userId,
                'action' => 'staff_edited',
                'description' => $description,
                'added' => $added,
                'removed' => $removed,
                'edits' => $collapsedEdits,
                'member_edits' => $collapsedMemberEdits,
            ]);
            \App\Models\EventActivityLog::log(
                $event->id,
                $userId,
                'staff_edited',
                $description,
                [
                    'event_title' => $event->title,
                    'added' => $added,
                    'removed' => $removed,
                    'edits' => $collapsedEdits,
                    'window_seconds' => now()->diffInSeconds($buffer['first_at']),
                    'entries_count' => count($buffer['entries'])
                ]
            );
        }
        Cache::forget($key);
    }
    public function step5(Request $request)
    {
        $eventId = $request->get('id');
        
        if (!$eventId) {
            return redirect()->route('organizer.events.index')->with('error', 'Event ID is required.');
        }

        $isAdmin = Auth::guard('admin')->check();
        
        $event = Event::where('id', $eventId)
                     ->where('status', 'draft');
        
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

        // Load existing staff data from event_staff table (not R2 JSON)
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
        
        // Load staff settings from R2
        $staffSettings = $this->loadStaffSettingsFromR2($eventId);
        
        $eventData = [
            'id' => $event->id,
            'title' => $event->title,
            'staff_data' => $staffMembers,
            'staff_settings' => $staffSettings
        ];

        return view('organizer.events.create.step5', compact('eventData', 'eventId', 'event'));
    }

    /**
     * Manage staff for an existing event (pretty URL: /organizer/events/{id}/staff)
     */
    public function manage(Event $event)
    {
        // Check if user has access to this event (creator, staff, or admin)
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event->user_id === Auth::id();
        $isAdminEvent = $event->user_id === null && $isAdmin;
        $isStaff = $event->staff()->where('user_id', Auth::id())->exists();
        
        $hasAccess = $isAdmin || $isOwner || $isAdminEvent || $isStaff;
        
        if (!$hasAccess || !in_array($event->status, ['draft','published','approved','pending_approval','scheduled'])) {
            abort(403, 'Unauthorized access to this event.');
        }

        // Determine layout type
        $isAdmin = Auth::guard('admin')->check();
        $layoutType = $isAdmin ? 'admin' : 'organizer';

        // Check if event has ended - if so, show view-only mode
        $eventEnded = $event->end_date && now()->gt($event->end_date);
        if ($eventEnded || $isAdmin) {
            return view('organizer.events.info.staff', compact('event', 'layoutType'));
        }

        // Check if user can manage staff
        $user = Auth::user();
        $isOwner = $user && (int)$event->user_id === (int)$user->id;
        $staff = $event->staff()->where('user_id', $user?->id)->first();
        $canManageStaff = $isOwner || ($staff?->can_manage_staff ?? false) || ($staff?->role === 'organizer');

        // If user can't manage staff, show info view instead
        if (!$canManageStaff) {
            return view('organizer.events.info.staff', compact('event', 'layoutType'));
        }

        // Load data for editable form
        $eventId = $event->id;
        
        // Load existing staff data from event_staff table (not R2 JSON)
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
        
        $staffSettings = $this->loadStaffSettingsFromR2($eventId);
        $eventData = [
            'id' => $event->id,
            'title' => $event->title,
            'staff_data' => $staffMembers,
            'staff_settings' => $staffSettings,
        ];
        
        return view('organizer.events.staff', compact('event', 'eventId', 'eventData', 'isAdmin', 'layoutType'));
    }

    public function step5Store(Request $request)
    {
        $eventId = $request->get('id');
        
        if (!$eventId) {
            return redirect()->route('organizer.events.index')->with('error', 'Event ID is required.');
        }

        $isAdmin = Auth::guard('admin')->check();
        
        // Allow editing staff for draft/pending_approval OR published/approved events
        $eventQuery = Event::where('id', $eventId);
        
        // Allow admins to access events with user_id === null (admin-created events)
        if ($isAdmin) {
            $eventQuery->where(function($query) {
                $query->where('user_id', Auth::id())
                      ->orWhereNull('user_id');
            });
        } else {
            $eventQuery->where('user_id', Auth::id());
        }
        
        $event = $eventQuery->first();

        if (!$event) {
            return redirect()->route('organizer.events.index')->with('error', 'Event not found or not authorized.');
        }
        
        // Check if event status allows staff editing
        $isUpcoming = !$event->end_date || now()->lt($event->end_date);
        $canEditStaff = in_array($event->status, ['draft', 'pending_approval'])
                      || ($isUpcoming && in_array($event->status, ['published', 'approved', 'scheduled']));
        
        if (!$canEditStaff) {
            return redirect()->route('organizer.events.index')->with('error', 'Cannot edit staff for this event.');
        }

        $validator = Validator::make($request->all(), [
            'staff_data' => 'nullable|string',
            'allow_staff_registration' => 'nullable|boolean',
            'require_staff_approval' => 'nullable|boolean',
            'send_staff_notifications' => 'nullable|boolean',
            'staff_welcome_message' => 'nullable|string|max:1000',
            'staff_instructions' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Persist staff members to event_staff table instead of JSON
            $staffData = $request->input('staff_data');
            if ($staffData) {
                $staffArray = json_decode($staffData, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($staffArray)) {
                    $incoming = collect($staffArray);
                    // Ensure creator always exists as owner
                    $incoming = $incoming->filter(function($m) use ($event) {
                        return isset($m['id']) && (int)$m['id'] !== (int)$event->user_id ? true : true;
                    });

                    // Current staff by user_id
                    $current = \App\Models\EventStaff::where('event_id', $eventId)->get()->keyBy('user_id');

                    // Upsert incoming
                    foreach ($incoming as $m) {
                        $userId = (int)($m['id'] ?? 0);
                        if ($userId <= 0) continue;
                        if ($userId === (int)$event->user_id) {
                            // skip writing owner as event_staff
                            continue;
                        }
                        
                        // Check if this is a new staff member (not in current)
                        $isNewStaff = !$current->has($userId);
                        
                        $payload = [
                            'role' => $m['role'] ?? 'staff',
                            'can_manage_event' => (bool)($m['can_manage_event'] ?? false),
                            'can_manage_staff' => (bool)($m['can_manage_staff'] ?? false),
                            'can_manage_ticket' => (bool)($m['can_manage_ticket'] ?? false),
                            'can_manage_news' => (bool)($m['can_manage_news'] ?? false),
                            'can_manage_attendance' => (bool)($m['can_manage_attendance'] ?? false),
                            'can_manage_sales' => (bool)($m['can_manage_sales'] ?? false),
                        ];
                        \App\Models\EventStaff::updateOrCreate(
                            ['event_id' => $eventId, 'user_id' => $userId],
                            $payload
                        );
                        // Remove from current set so remaining are deletions
                        $current->forget($userId);
                        
                        // Notify user if this is a new staff member and event is published/approved/scheduled
                        // Only send notification once - check if notification already exists
                        if ($isNewStaff && in_array($event->status, ['published', 'approved', 'scheduled'])) {
                            try {
                                if ($u = User::find($userId)) {
                                    // Check if notification was already sent for this staff assignment
                                    $notificationExists = \App\Models\Notification::where('user_id', $userId)
                                        ->where('type', 'event')
                                        ->where('action', 'staff_added')
                                        ->where('reference_id', $eventId)
                                        ->exists();
                                    
                                    if (!$notificationExists) {
                                        // Create in-app notification using custom Notification model
                                        $perms = array_keys(array_filter($payload));
                                        $permList = empty($perms) ? 'No special permissions' : implode(', ', array_map(function ($k) {
                                            return str_replace('can_', '', str_replace('_', ' ', $k));
                                        }, $perms));
                                        
                                        \App\Models\Notification::create([
                                            'user_id' => $userId,
                                            'admin_id' => null,
                                            'type' => 'event',
                                            'action' => 'staff_added',
                                            'reference_id' => $eventId,
                                            'title' => 'Added as Event Staff',
                                            'message' => "You have been added as {$payload['role']} for the event '{$event->title}'. Permissions: {$permList}",
                                            'is_read' => false,
                                            'data' => [
                                                'event_id' => $eventId,
                                                'event_title' => $event->title,
                                                'role' => $payload['role'],
                                                'permissions' => $payload,
                                            ],
                                        ]);
                                        
                                        // Send email notification
                                        $u->notify(new StaffAssignedNotification($event, $payload['role'], $payload));
                                        
                                        // Send real-time notification
                                        \App\Services\NotificationService::sendUserNotification(
                                            $userId,
                                            "You have been added as {$payload['role']} for the event '{$event->title}'.",
                                            'info',
                                            'Added as Event Staff'
                                        );
                                        
                                        \Log::info('Staff notification sent via step5Store', [
                                            'event_id' => $eventId,
                                            'user_id' => $userId,
                                            'event_status' => $event->status
                                        ]);
                                    } else {
                                        \Log::info('Staff notification skipped - already sent', [
                                            'event_id' => $eventId,
                                            'user_id' => $userId
                                        ]);
                                    }
                                }
                            } catch (\Throwable $t) { 
                                \Log::warning('Staff notify failed', [
                                    'event_id' => $eventId, 
                                    'error' => $t->getMessage()
                                ]); 
                            }
                        }
                    }

                    // Delete staff not present anymore
                    foreach ($current as $st) {
                        // Don't delete owner rows if any
                        if ((int)$st->user_id === (int)$event->user_id) continue;
                        $st->delete();
                    }
                }
            }

            // Store staff settings in R2 (kept in JSON for notes only)
            $staffSettings = [
                'allow_staff_registration' => $request->boolean('allow_staff_registration'),
                'require_staff_approval' => $request->boolean('require_staff_approval'),
                'send_staff_notifications' => $request->boolean('send_staff_notifications'),
                'staff_welcome_message' => $request->input('staff_welcome_message'),
                'staff_instructions' => $request->input('staff_instructions'),
            ];

            $eventDir = "events/{$eventId}";
            Storage::disk('r2')->put($eventDir . '/staff_settings.json', json_encode($staffSettings));

            \Log::info('Staff data saved to R2:', [
                'event_id' => $eventId,
                'staff_count' => $staffData ? count(json_decode($staffData, true)) : 0
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to save staff data: ' . $e->getMessage());
            return back()->with('error', 'Failed to save staff data. Please try again.');
        }

        return redirect()->route('organizer.events.create.review', ['id' => $eventId]);
    }

    public function autosaveStep5(Request $request)
    {
        $eventId = $request->get('id');
        \Log::info('Staff autosave request received', [
            'event_id' => $eventId,
            'user_id' => Auth::id(),
            'path' => $request->path(),
            'method' => $request->method(),
        ]);
        
        if (!$eventId) {
            return response()->json(['success' => false, 'message' => 'Event ID is required.']);
        }

        $isAdmin = Auth::guard('admin')->check();
        
        // Find the event without status restriction initially
        $eventQuery = Event::where('id', $eventId);
        
        // Allow admins to access events with user_id === null (admin-created events)
        if ($isAdmin) {
            $eventQuery->where(function($query) {
                $query->where('user_id', Auth::id())
                      ->orWhereNull('user_id');
            });
        } else {
            $eventQuery->where('user_id', Auth::id());
        }
        
        $event = $eventQuery->first();

        if (!$event) {
            return response()->json(['success' => false, 'message' => 'Event not found or not authorized.']);
        }
        
        // Check if event is upcoming
        $isUpcoming = !$event->end_date || now()->lt($event->end_date);
        
        // Allow autosave for draft/pending_approval OR for upcoming events with certain statuses
        $canAutosave = in_array($event->status, ['draft', 'pending_approval'])
                      || ($isUpcoming && in_array($event->status, ['published', 'approved', 'scheduled']));
        
        if (!$canAutosave) {
            return response()->json(['success' => false, 'message' => 'Event not found or not authorized.']);
        }

        try {
            // Event already validated above

            // Parse incoming members JSON
            $incomingStaffJson = $request->input('staff_data');
            $incoming = [];
            if ($incomingStaffJson) {
                $arr = json_decode($incomingStaffJson, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($arr)) {
                    $incoming = $arr;
                }
            }

            // Upsert members to event_staff as part of autosave
            $current = \App\Models\EventStaff::where('event_id', $eventId)->get()->keyBy('user_id');
            foreach ($incoming as $m) {
                $uid = (int)($m['id'] ?? 0);
                if ($uid <= 0 || $uid === (int)$event->user_id) continue;
                
                // Check if this is a new staff member (not in current)
                $isNewStaff = !$current->has($uid);
                
                $payload = [
                    'role' => $m['role'] ?? 'staff',
                    'can_manage_event' => (bool)($m['can_manage_event'] ?? false),
                    'can_manage_staff' => (bool)($m['can_manage_staff'] ?? false),
                    'can_manage_ticket' => (bool)($m['can_manage_ticket'] ?? false),
                    'can_manage_news' => (bool)($m['can_manage_news'] ?? false),
                    'can_manage_attendance' => (bool)($m['can_manage_attendance'] ?? false),
                    'can_manage_sales' => (bool)($m['can_manage_sales'] ?? false),
                ];
                \App\Models\EventStaff::updateOrCreate(['event_id' => $eventId, 'user_id' => $uid], $payload);
                
                // Notify user if this is a new staff member and event is published/approved/scheduled
                // Only send notification once - check if notification already exists
                if ($isNewStaff && in_array($event->status, ['published', 'approved', 'scheduled'])) {
                    try {
                        if ($u = User::find($uid)) {
                            // Check if notification was already sent for this staff assignment
                            $notificationExists = \App\Models\Notification::where('user_id', $uid)
                                ->where('type', 'event')
                                ->where('action', 'staff_added')
                                ->where('reference_id', $eventId)
                                ->exists();
                            
                            if (!$notificationExists) {
                                // Create in-app notification using custom Notification model
                                $perms = array_keys(array_filter($payload));
                                $permList = empty($perms) ? 'No special permissions' : implode(', ', array_map(function ($k) {
                                    return str_replace('can_', '', str_replace('_', ' ', $k));
                                }, $perms));
                                
                                \App\Models\Notification::create([
                                    'user_id' => $uid,
                                    'admin_id' => null,
                                    'type' => 'event',
                                    'action' => 'staff_added',
                                    'reference_id' => $eventId,
                                    'title' => 'Added as Event Staff',
                                    'message' => "You have been added as {$payload['role']} for the event '{$event->title}'. Permissions: {$permList}",
                                    'is_read' => false,
                                    'data' => [
                                        'event_id' => $eventId,
                                        'event_title' => $event->title,
                                        'role' => $payload['role'],
                                        'permissions' => $payload,
                                    ],
                                ]);
                                
                                // Send email notification
                                $u->notify(new StaffAssignedNotification($event, $payload['role'], $payload));
                                
                                // Send real-time notification
                                \App\Services\NotificationService::sendUserNotification(
                                    $uid,
                                    "You have been added as {$payload['role']} for the event '{$event->title}'.",
                                    'info',
                                    'Added as Event Staff'
                                );
                                
                                \Log::info('Staff notification sent via autosave', [
                                    'event_id' => $eventId,
                                    'user_id' => $uid,
                                    'event_status' => $event->status
                                ]);
                            } else {
                                \Log::info('Staff notification skipped - already sent via autosave', [
                                    'event_id' => $eventId,
                                    'user_id' => $uid
                                ]);
                            }
                        }
                    } catch (\Throwable $t) { 
                        \Log::warning('Staff notify failed via autosave', [
                            'event_id' => $eventId, 
                            'user_id' => $uid,
                            'error' => $t->getMessage()
                        ]); 
                    }
                }
                
                $current->forget($uid);
            }
            // Remove entries not in incoming (except owner)
            foreach ($current as $st) {
                if ((int)$st->user_id === (int)$event->user_id) continue;
                $st->delete();
            }

            // Handle staff settings diffs
            $staffSettingsFromInput = $request->input('staff_settings');
            if (!$staffSettingsFromInput) {
                $staffSettingsFromInput = [
                    'staff_welcome_message' => $request->input('staff_welcome_message'),
                    'staff_instructions' => $request->input('staff_instructions'),
                    'allow_staff_registration' => $request->boolean('allow_staff_registration'),
                    'require_staff_approval' => $request->boolean('require_staff_approval'),
                    'send_staff_notifications' => $request->boolean('send_staff_notifications'),
                ];
            }
            $existingSettingsRaw = $this->loadStaffSettingsFromR2($eventId);
            $existingSettings = $this->normalizeStaffSettings($existingSettingsRaw);
            $newSettings = $this->normalizeStaffSettings((array)$staffSettingsFromInput);
            // Persist new settings
            $eventDir = "events/{$eventId}";
            Storage::disk('r2')->put($eventDir . '/staff_settings.json', json_encode($newSettings));

            // Compute changes
            $settingEdits = [];
            foreach ($newSettings as $k => $v) {
                $old = $existingSettings[$k] ?? null;
                if (json_encode($old) !== json_encode($v)) {
                    $settingEdits[$k] = ['from' => $old, 'to' => $v];
                }
            }
            if (!empty($settingEdits)) {
                \Log::info('Staff autosave queue edits', [
                    'event_id' => $event->id,
                    'user_id' => Auth::id(),
                    'edits' => $settingEdits,
                ]);
                $this->queueStaffActivity($event, Auth::id(), [
                    'edits' => $settingEdits,
                ]);
            }

            \Log::info('Staff autosave processed OK', [
                'event_id' => $event->id,
                'user_id' => Auth::id(),
            ]);
            return response()->json(['success' => true, 'message' => 'Staff data autosaved successfully.']);

        } catch (\Exception $e) {
            \Log::error('Failed to autosave staff data: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to autosave staff data.']);
        }
    }

    /**
     * Flush queued autosave activity for staff when user exits the page.
     */
    public function autosaveFlush(Request $request)
    {
        $eventId = (int)$request->get('id');
        $event = Event::find($eventId);
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event && $event->user_id === Auth::id();
        $isAdminEvent = $event && $event->user_id === null && $isAdmin;
        
        if (!$event || !in_array($event->status, ['draft', 'published', 'approved', 'pending_approval', 'scheduled']) || (!$isOwner && !$isAdminEvent)) {
            return response()->json(['success' => false, 'message' => 'Event not found or not authorized'], 404);
        }
        \Log::info('Manual staff flush trigger', [
            'event_id' => $event->id,
            'user_id' => Auth::id()
        ]);
        $this->flushStaffActivity($event, Auth::id());
        \Log::info('Staff autosave flush processed', [
            'event_id' => $event->id,
            'user_id' => Auth::id()
        ]);
        return response()->json(['success' => true]);
    }

    public function searchStaff(Request $request)
    {
        $query = trim((string)$request->get('q', ''));
        $eventId = $request->get('id'); // Get event ID from request

        // Get event to find the owner ID
        $event = null;
        $ownerId = null;
        if ($eventId) {
            $event = Event::find($eventId);
            if ($event) {
                $ownerId = $event->user_id;
            }
        }

        // Base query with owner exclusion
        $userQuery = \App\Models\User::query()
            ->select(['id','first_name','last_name','email'])
            ->where('status', 'active') // Only show active users
            ->where('email', 'not like', '%@admin.local%'); // Exclude admin shadow user accounts (admin+{id}@admin.local)
        
        // Exclude the owner if event exists
        if ($ownerId) {
            $userQuery->where('id', '!=', $ownerId);
        }

        // If short or empty query, return a small recent list (or first N)
        if (strlen($query) < 2) {
            $users = $userQuery->latest()->limit(8)->get();
        } else {
            $users = $userQuery->where(function($q) use ($query) {
                    $q->where('email', 'like', "%{$query}%")
                      ->orWhere('first_name', 'like', "%{$query}%")
                      ->orWhere('last_name', 'like', "%{$query}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$query}%"]);
                })
                ->limit(10)
                ->get();
        }

        $results = $users->map(function($u){
            $fullName = trim(($u->first_name ?: '') . ' ' . ($u->last_name ?: ''));
            return [
                'id' => $u->id,
                'name' => $fullName !== '' ? $fullName : ($u->email ?? null),
                'email' => $u->email,
            ];
        });

        return response()->json($results);
    }

    /**
     * Load ticket settings from R2 storage
     */
    private function loadTicketSettingsFromR2(int $eventId): array
    {
        try {
            $eventDir = "events/{$eventId}";
            $settingsPath = $eventDir . '/ticket_settings.json';
            
            if (\Storage::disk('r2')->exists($settingsPath)) {
                $json = \Storage::disk('r2')->get($settingsPath);
                $data = json_decode($json, true);
                
                if (is_array($data)) {
                    return $data;
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to load ticket settings from R2', [
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);
        }

        return [];
    }
}
