<?php

namespace App\Http\Controllers\Organizer\EventManagement;

use App\Http\Controllers\Controller;

use App\Models\Event;
use App\Models\EventTicket;
use App\Models\EventTicketInformation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use App\Models\EventActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Organizer\EventManagement\Traits\DraftHelpers;

class TicketsController extends Controller
{
    use DraftHelpers;

    /**
     * Normalize a datetime string/value to minute precision in app timezone.
     */
    private function normalizeDateMinute($value)
    {
        if (is_null($value) || $value === '') return null;
        try {
            $dt = $value instanceof \DateTimeInterface ? \Carbon\Carbon::instance($value) : \Carbon\Carbon::parse((string)$value);
            return $dt->timezone(config('app.timezone', 'UTC'))->format('Y-m-d H:i');
        } catch (\Throwable $e) {
            return (string)$value;
        }
    }

    /**
     * Normalize a single ticket type row for comparison.
     */
    private function normalizeTicketType(array $type): array
    {
        $normalized = [
            'id' => isset($type['id']) ? (string)$type['id'] : null,
            'name' => isset($type['name']) ? trim((string)$type['name']) : null,
            'description' => isset($type['description']) ? (string)$type['description'] : null,
            'price' => isset($type['price']) && $type['price'] !== '' ? (float)$type['price'] : 0.0,
            'quantity' => isset($type['quantity']) && $type['quantity'] !== '' ? (int)$type['quantity'] : null,
            'sale_start' => $this->normalizeDateMinute($type['sale_start'] ?? null),
            'sale_end' => $this->normalizeDateMinute($type['sale_end'] ?? null),
            'is_active' => isset($type['is_active']) ? $type['is_active'] : null,
        ];
        // Preserve is_active if it exists (even if false), but remove other nulls
        $result = [];
        foreach ($normalized as $key => $value) {
            // Always include is_active if it exists (even if false/0/'0')
            if ($key === 'is_active' && array_key_exists('is_active', $normalized)) {
                $result[$key] = $value;
            } elseif ($value !== null) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * Normalize list of ticket types with stable ordering (by id then name).
     */
    private function normalizeTicketTypesArray($types): array
    {
        if (!is_array($types)) return [];
        // CRITICAL: Preserve ALL tickets in their original order
        // Don't filter or sort aggressively - just normalize each ticket
        // This ensures all 20 tickets are preserved, not just 11
        $normalized = [];
        foreach ($types as $index => $t) {
            $normalizedTicket = $this->normalizeTicketType((array)$t);
            // Preserve the original array index to ensure uniqueness
            $normalizedTicket['_array_index'] = $index;
            $normalized[] = $normalizedTicket;
        }
        // Don't sort - maintain exact order as received
        // Sorting was causing tickets to be reordered and potentially lost
        // The backend will handle matching by ID for existing tickets
        return $normalized;
    }

    /**
     * Normalize ticket settings for comparison.
     */
    private function normalizeTicketSettings(array $settings): array
    {
        $defaultColors = [
            'primary' => '#10b981',
            'secondary' => '#f59e0b',
            'accent' => '#059669'
        ];
        
        $ticketColors = $settings['ticket_colors'] ?? $defaultColors;
        if (!is_array($ticketColors) || empty($ticketColors)) {
            $ticketColors = $defaultColors;
        }
        
        return [
            'max_tickets_per_order' => isset($settings['max_tickets_per_order']) ? (int)$settings['max_tickets_per_order'] : 10,
            'refund_policy' => isset($settings['refund_policy']) ? (string)$settings['refund_policy'] : '',
            'allow_refunds' => (bool)($settings['allow_refunds'] ?? true),
            'require_approval' => (bool)($settings['require_approval'] ?? false),
            'allow_share_ticket' => (bool)($settings['allow_share_ticket'] ?? true),
            'limit_per_user' => (bool)($settings['limit_per_user'] ?? false),
            'max_tickets_per_user' => isset($settings['max_tickets_per_user']) ? (int)$settings['max_tickets_per_user'] : 1,
            'ticket_colors' => [
                'primary' => $ticketColors['primary'] ?? $defaultColors['primary'],
                'secondary' => $ticketColors['secondary'] ?? $defaultColors['secondary'],
                'accent' => $ticketColors['accent'] ?? $defaultColors['accent'],
            ],
        ];
    }

    /**
     * Queue ticket activity into a 60s buffer and flush as one log entry.
     */
    private function queueTicketActivity(Event $event, int $userId, array $entry): void
    {
        $key = "activity_buffer:tickets:{$event->id}:{$userId}";
        $buffer = Cache::get($key, [
            'first_at' => now(),
            'last_at' => null,
            'entries' => [],
        ]);
        $buffer['entries'][] = $entry;
        $buffer['last_at'] = now();
        Cache::put($key, $buffer, now()->addMinutes(5));

        // If buffer is older than 60s, flush as single activity log
        if (now()->diffInSeconds($buffer['first_at']) >= 60) {
            $added = [];
            $removed = [];
            $edits = [];
            foreach ($buffer['entries'] as $e) {
                if (!empty($e['added'])) { $added = array_merge($added, $e['added']); }
                if (!empty($e['removed'])) { $removed = array_merge($removed, $e['removed']); }
                if (!empty($e['edits'])) { $edits = array_merge($edits, $e['edits']); }
            }
            $added = array_values(array_unique($added));
            $removed = array_values(array_unique($removed));

            // Cancel out tickets that were both added and removed in the same window
            $cancelled = array_intersect($added, $removed);
            if (!empty($cancelled)) {
                $added = array_values(array_diff($added, $cancelled));
                $removed = array_values(array_diff($removed, $cancelled));
            }
            $descParts = [];
            if (!empty($added)) { $descParts[] = 'Added '.count($added).' ticket(s)'; }
            if (!empty($removed)) { $descParts[] = 'Removed '.count($removed).' ticket(s)'; }

            if (!empty($descParts) || !empty($edits)) {
                // Group edits per ticket and collapse multiple edits of same field to first->last
                $grouped = [];
                foreach ($edits as $edit) {
                    $ticketName = $edit['name'] ?? 'Unnamed';
                    $changes = (array)($edit['changes'] ?? []);
                    foreach ($changes as $field => $diff) {
                        $fromVal = $diff['from'] ?? null;
                        $toVal = $diff['to'] ?? null;
                        if (!isset($grouped[$ticketName][$field])) {
                            $grouped[$ticketName][$field] = [
                                'from' => $fromVal,
                                'to' => $toVal,
                            ];
                        } else {
                            // keep original from, overwrite to with the most recent
                            $grouped[$ticketName][$field]['to'] = $toVal;
                        }
                    }
                }
                // Remove no-op and tickets that were removed; then build summary
                $editsSummaryParts = [];
                foreach ($grouped as $ticketKey => $fields) {
                    $ticketName = $fields['__name'] ?? (is_string($ticketKey) ? $ticketKey : 'Unnamed');
                    unset($fields['__name']);
                    if (in_array($ticketName, $removed, true)) {
                        unset($grouped[$ticketKey]);
                        continue;
                    }
                    // prune no-op
                    foreach ($fields as $fname => $diff) {
                        $fv = is_scalar($diff['from'] ?? null) ? (string)$diff['from'] : json_encode($diff['from'] ?? null);
                        $tv = is_scalar($diff['to'] ?? null) ? (string)$diff['to'] : json_encode($diff['to'] ?? null);
                        if ($fv === $tv) unset($fields[$fname]);
                    }
                    if (empty($fields)) { unset($grouped[$ticketKey]); continue; }
                    $fieldParts = [];
                    foreach ($fields as $field => $diff) {
                        $from = is_scalar($diff['from'] ?? null) ? (string)$diff['from'] : json_encode($diff['from'] ?? null);
                        $to = is_scalar($diff['to'] ?? null) ? (string)$diff['to'] : json_encode($diff['to'] ?? null);
                        $label = ucfirst(str_replace('_',' ', $field));
                        $fieldParts[] = "$label $from → $to";
                    }
                    if (!empty($fieldParts)) {
                        $editsSummaryParts[] = ($ticketName).': '.implode(', ', $fieldParts);
                        // write back pruned
                        $grouped[$ticketKey] = array_merge(['__name'=>$ticketName], $fields);
                    } else {
                        unset($grouped[$ticketKey]);
                    }
                }
                $description = trim(implode(' — ', array_filter([
                    !empty($descParts) ? implode(', ', $descParts) : null,
                    !empty($editsSummaryParts) ? ('Edits: '.implode(' | ', $editsSummaryParts)) : null,
                ]))) ?: 'Ticket changes';
                // Ensure description fits DB column
                $description = Str::limit($description, 255, '…');

                \App\Models\EventActivityLog::log(
                    $event->id,
                    $userId,
                    'ticket_edited',
                    $description,
                    [
                        'event_title' => $event->title,
                        'added' => $added,
                        'removed' => $removed,
                        'edits' => array_values(array_map(function($fields, $key){ $nm = $fields['__name'] ?? (is_string($key)?$key:'Unnamed'); unset($fields['__name']); return ['name'=>$nm,'changes'=>$fields]; }, $grouped, array_keys($grouped))),
                        'window_seconds' => now()->diffInSeconds($buffer['first_at']),
                        'entries_count' => count($buffer['entries'])
                    ]
                );
            }
            // reset buffer
            Cache::forget($key);
        }
    }

    /**
     * Flush any queued ticket activity immediately (used on page exit).
     */
    private function flushTicketActivity(Event $event, int $userId, ?array $mergeEntry = null): void
    {
        $key = "activity_buffer:tickets:{$event->id}:{$userId}";
        $buffer = Cache::get($key, [
            'first_at' => now(),
            'last_at' => null,
            'entries' => [],
        ]);
        if ($mergeEntry) {
            $buffer['entries'][] = $mergeEntry;
        }
        if (empty($buffer['entries'])) {
            return; // nothing to flush
        }
        $added = [];
        $removed = [];
        $edits = [];
        foreach ($buffer['entries'] as $e) {
            if (!empty($e['added'])) { $added = array_merge($added, $e['added']); }
            if (!empty($e['removed'])) { $removed = array_merge($removed, $e['removed']); }
            if (!empty($e['edits'])) { $edits = array_merge($edits, $e['edits']); }
        }
        $added = array_values(array_unique($added));
        $removed = array_values(array_unique($removed));
        
        // Cancel out tickets that were both added and removed in the same window
        $cancelled = array_intersect($added, $removed);
        if (!empty($cancelled)) {
            $added = array_values(array_diff($added, $cancelled));
            $removed = array_values(array_diff($removed, $cancelled));
        }
        
        $descParts = [];
        if (!empty($added)) { $descParts[] = 'Added '.count($added).' ticket(s)'; }
        if (!empty($removed)) { $descParts[] = 'Removed '.count($removed).' ticket(s)'; }
        
        // Group edits per ticket and collapse multiple edits per field
        $grouped = [];
        foreach ($edits as $edit) {
            $ticketKey = $edit['id'] ?? ($edit['name'] ?? 'Unnamed');
            $ticketName = $edit['name'] ?? 'Unnamed';
            $changes = (array)($edit['changes'] ?? []);
            foreach ($changes as $field => $diff) {
                $fromVal = $diff['from'] ?? null;
                $toVal = $diff['to'] ?? null;
                if (!isset($grouped[$ticketKey][$field])) {
                    $grouped[$ticketKey]['__name'] = $ticketName;
                    $grouped[$ticketKey][$field] = [
                        'from' => $fromVal,
                        'to' => $toVal,
                    ];
                } else {
                    $grouped[$ticketKey][$field]['to'] = $toVal;
                }
            }
        }
        
        // Only create log entry if there are actual changes
        if (!empty($descParts) || !empty($grouped)) {
            $editsSummaryParts = [];
            foreach ($grouped as $ticketKey => $fields) {
                $ticketName = $fields['__name'] ?? (is_string($ticketKey) ? $ticketKey : 'Unnamed');
                unset($fields['__name']);
                $fieldParts = [];
                foreach ($fields as $field => $diff) {
                    $from = is_scalar($diff['from'] ?? null) ? (string)$diff['from'] : json_encode($diff['from'] ?? null);
                    $to = is_scalar($diff['to'] ?? null) ? (string)$diff['to'] : json_encode($diff['to'] ?? null);
                    $label = ucfirst(str_replace('_',' ', $field));
                    $fieldParts[] = "$label $from → $to";
                }
                if (!empty($fieldParts)) {
                    $editsSummaryParts[] = ($ticketName).': '.implode(', ', $fieldParts);
                }
            }
            
            $description = trim(implode(' — ', array_filter([
                !empty($descParts) ? implode(', ', $descParts) : null,
                !empty($editsSummaryParts) ? ('Edits: '.implode(' | ', $editsSummaryParts)) : null,
            ]))) ?: 'Ticket changes';
            
            // Ensure description fits DB column
            $description = Str::limit($description, 255, '…');

            \Log::info('Creating ticket activity log entry', [
                'event_id' => $event->id,
                'user_id' => $userId,
                'description' => $description,
                'added_count' => count($added),
                'removed_count' => count($removed),
                'edits_count' => count($grouped)
            ]);

            \App\Models\EventActivityLog::log(
                $event->id,
                $userId,
                'ticket_edited',
                $description,
                [
                    'event_title' => $event->title,
                    'added' => $added,
                    'removed' => $removed,
                    'edits' => array_values(array_map(function($fields, $key){ 
                        $nm = $fields['__name'] ?? (is_string($key)?$key:'Unnamed'); 
                        unset($fields['__name']); 
                        return ['name'=>$nm,'changes'=>$fields]; 
                    }, $grouped, array_keys($grouped))),
                    'window_seconds' => now()->diffInSeconds($buffer['first_at']),
                    'entries_count' => count($buffer['entries'])
                ]
            );
        }
        Cache::forget($key);
    }

    public function step3(Request $request)
    {
        $eventId = $request->get('id');
        
        if (!$eventId) {
            return redirect()->route('organizer.events.step1')->with('error', 'Event ID is required.');
        }

        $event = Event::find($eventId);
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event && $event->user_id === Auth::id();
        $isAdminEvent = $event && $event->user_id === null && $isAdmin;
        
        if (!$event || !in_array($event->status, ['draft', 'rejected', 'pending_approval']) || (!$isOwner && !$isAdminEvent)) {
            return redirect()->route('organizer.events.index')->with('error', 'Event not found or not authorized.');
        }

        // Load event data from database
        $eventData = [
            'title' => $event->title,
            'description' => $event->description,
            'category' => $event->category,
            'start_date' => $event->start_date,
            'end_date' => $event->end_date,
            'location' => $event->location,
            'poster' => $event->poster,
            'thumbnail' => $event->thumbnail,
            'existing_poster_url' => $event->poster_url,
            'existing_thumbnail_url' => $event->thumbnail_url,
            'is_free' => $event->is_free ?? false,
            'ticket_types' => $event->eventTickets()->orderBy('id')->get()->map(function($t){
                $sold = (int)($t->sold_count ?? 0);
                return [
                    'id' => $t->id,
                    'name' => $t->name,
                    'description' => $t->description,
                    'price' => $t->price,
                    'quantity' => $t->quantity,
                    'sale_start' => optional($t->sale_start)->format('Y-m-d H:i'),
                    'sale_end' => optional($t->sale_end)->format('Y-m-d H:i'),
                    'purchased_count' => $sold,
                    'can_delete' => $sold === 0,
                    'is_active' => (bool)($t->is_active ?? true), // Default to true if not set
                ];
            })->toArray(),
            'ticket_settings' => $this->loadTicketSettingsFromR2($event),
        ];

        return view('organizer.events.create.step3', compact('eventData', 'eventId'));
    }

    public function step3Store(Request $request)
    {
        $eventId = $request->get('id');
        
        if (!$eventId) {
            return redirect()->route('organizer.events.step1')->with('error', 'Event ID is required.');
        }

        $event = Event::find($eventId);
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event && $event->user_id === Auth::id();
        $isAdminEvent = $event && $event->user_id === null && $isAdmin;
        
        if (!$event || !in_array($event->status, ['draft', 'published', 'approved', 'pending_approval', 'scheduled']) || (!$isOwner && !$isAdminEvent)) {
            return redirect()->route('organizer.events.index')->with('error', 'Event not found or not authorized.');
        }

        // Retrieve ticket types; allow JSON string from hidden field
        $ticketTypes = $request->input('ticket_types', []);
        if (is_string($ticketTypes)) {
            $ticketTypes = json_decode($ticketTypes, true);
        }
        if (!is_array($ticketTypes)) {
            $ticketTypes = [];
        }

        // If event is free, force all ticket prices to 0 before validation/save
        if ($event && $event->is_free && is_array($ticketTypes)) {
            foreach ($ticketTypes as &$t) {
                if (is_array($t)) {
                    $t['price'] = 0;
                }
            }
            unset($t);
        }

        // Enforce: ticket sale_end must match event end; remove restrictions on sale_start
        if ($event && $event->end_date && is_array($ticketTypes)) {
            foreach ($ticketTypes as &$t) {
                if (is_array($t)) {
                    $t['sale_end'] = optional($event->end_date)->format('Y-m-d H:i');
                }
            }
            unset($t);
        }

        // Validate decoded array structure instead of original request
        $validator = Validator::make([
            'ticket_types' => $ticketTypes,
        ], [
            'ticket_types' => 'required|array|min:1',
            'ticket_types.*.name' => ['required', 'string', 'max:255', new \App\Rules\NoProfanity()],
            'ticket_types.*.description' => ['nullable', 'string', 'max:200', new \App\Rules\NoProfanity()],
            'ticket_types.*.price' => 'required|numeric|min:0',
            'ticket_types.*.quantity' => 'nullable|integer|min:1',
            'ticket_types.*.sale_start' => 'nullable|date',
            'ticket_types.*.sale_end' => 'nullable|date',
        ], [
            'ticket_types.min' => 'You must add at least one ticket type to continue.',
            'ticket_types.required' => 'You must add at least one ticket type to continue.',
            'ticket_types.array' => 'Invalid ticket data format.',
        ]);

        if ($validator->fails()) {
            // Check if the main issue is missing tickets
            if ($validator->errors()->has('ticket_types') && (empty($ticketTypes) || count($ticketTypes) === 0)) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'At least one ticket is required',
                        'title' => 'No Tickets Added',
                        'text' => 'You must add at least one ticket type before proceeding to the next step.',
                    ], 400);
                }
                return back()->withErrors(['ticket_types' => 'You must add at least one ticket type before proceeding to the next step.'])->withInput();
            }
            return back()->withErrors($validator)->withInput();
        }
        
        // Check if this is a new event creation or editing existing event
        $isNewEventCreation = $event->status === 'draft' && $event->created_at->diffInMinutes(now()) < 5;
        
        // Load existing ticket types from R2 to detect changes (normalized)
        $existingTypes = $this->loadTicketTypesFromR2($event);
        $existingTypesNorm = $this->normalizeTicketTypesArray($existingTypes);
        $newTypesNorm = $this->normalizeTicketTypesArray($ticketTypes);
        $typesChanged = json_encode($existingTypesNorm) !== json_encode($newTypesNorm);

        // Enforce subscription ticket limit per event (including additional tickets)
        // Use event owner's subscription, not staff member's subscription
        // Skip limit check for admin-created events (user_id === null)
        $isAdmin = Auth::guard('admin')->check();
        $isAdminEvent = $event->user_id === null && $isAdmin;
        
        try {
            // Admins creating events have unlimited tickets - skip all limit checks
            if ($isAdminEvent) {
                // Skip ticket limit enforcement for admin-created events
            } else {
                $currentUser = Auth::user();
                $isOwner = $event->user_id === $currentUser->id;
                
                // If staff is managing tickets, use owner's subscription
                $subscriptionUserId = $isOwner ? $currentUser->id : $event->user_id;
                $ownerUser = $subscriptionUserId ? \App\Models\User::find($subscriptionUserId) : null;
                
                $subscription = $ownerUser?->currentSubscription();
                $plan = $subscription?->getPlan();
                
                // If no subscription, check free plan
                if (!$plan) {
                    $plan = \App\Models\SubscriptionPlan::where('slug', 'free')->first();
                }
                
                $baseMaxTicketsPerEvent = $plan?->tickets_per_event; // null means unlimited
                
                // Get additional tickets purchased for THIS event only (per-event scope)
                // Use owner's user_id for additional tickets, not staff member's
                $additionalTicketsPurchased = \App\Models\TicketLimitPurchase::where('user_id', $subscriptionUserId)
                    ->where('event_id', $event->id)
                    ->where('status', 'paid') // Only count paid additional tickets
                    ->sum('additional_tickets');

                // Calculate the total maximum tickets allowed
                // If baseMaxTicketsPerEvent is null (unlimited), the total is also unlimited.
                // Otherwise, add the additional tickets to the base limit.
                $maxTicketsPerEvent = ($baseMaxTicketsPerEvent === null) 
                    ? null 
                    : $baseMaxTicketsPerEvent + $additionalTicketsPurchased;

                // If event is marked free, use free_event_ticket_limit as base and add additional tickets
                if ($event->is_free) {
                    $freeCap = (int)($plan?->free_event_ticket_limit ?? 0);
                    if ($freeCap > 0) {
                        // Add additional tickets purchased to the free cap base
                        $maxTicketsPerEvent = $freeCap + $additionalTicketsPurchased;
                    }
                }
                
                if ($maxTicketsPerEvent !== null) {
                    $totalTicketsRequested = 0;
                    foreach ($ticketTypes as $t) {
                        $qty = isset($t['quantity']) ? (int)$t['quantity'] : 0;
                        $totalTicketsRequested += max(0, $qty);
                    }
                    // Include existing tickets already configured for this event
                    $existingQty = (int) $event->eventTickets()->sum('quantity');
                    // When updating, incoming payload includes existing tickets too; use the incoming sum as the new desired total
                    $desiredTotal = max($totalTicketsRequested, 0);
                    if ($desiredTotal > $maxTicketsPerEvent) {
                        // Return JSON response for AJAX requests to show SweetAlert modal
                        if ($request->ajax() || $request->wantsJson()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Ticket limit reached',
                                'title' => 'Plan Limit Reached',
                                'text' => "Your total ticket limit is {$maxTicketsPerEvent} tickets per event (including additional tickets purchased). You are attempting to configure {$desiredTotal}.",
                                'showPurchaseButton' => true,
                                'purchaseUrl' => route('organizer.events.ticket-limits', $event)
                            ], 400);
                        }
                        
                        // Fallback for non-AJAX requests
                        $errorMessage = "Your total ticket limit is {$maxTicketsPerEvent} tickets per event (including additional tickets purchased). You are attempting to configure {$desiredTotal}. ";
                        $errorMessage .= "You can purchase additional tickets by visiting the <a href='" . route('organizer.events.ticket-limits', $event) . "' class='text-qricket-600 hover:text-qricket-800 underline'>Ticket Limits</a> page.";
                        return back()->withErrors(['ticket_types' => $errorMessage])->withInput();
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silent fail - do not block if something goes wrong; continue with save
        }

        // Upsert ticket types into DB (normalize) and detect add/remove
        if (!empty($ticketTypes)) {
            try {
                $incoming = $this->normalizeTicketTypesArray($ticketTypes);
                $existing = $event->eventTickets()->get();
                $existingByKey = [];
                foreach ($existing as $row) {
                    $k = $row->id ? (string)$row->id : strtolower(trim(preg_replace('/\s+/', '-', (string)$row->name)));
                    $existingByKey[$k] = $row;
                }
                $incomingByKey = [];
                foreach ($incoming as $t) {
                    $k = isset($t['id']) ? (string)$t['id'] : strtolower(trim(preg_replace('/\s+/', '-', (string)($t['name'] ?? ''))));
                    $incomingByKey[$k] = $t;
                }

                $added = array_diff(array_keys($incomingByKey), array_keys($existingByKey));
                $removed = array_diff(array_keys($existingByKey), array_keys($incomingByKey));
                // Detect edited tickets (field-level diffs) for keys present in both
                $common = array_intersect(array_keys($incomingByKey), array_keys($existingByKey));
                $editedChanges = [];
                foreach ($common as $k) {
                    $dbRow = $existingByKey[$k];
                    $old = [
                        'name' => $dbRow->name,
                        'description' => $dbRow->description,
                        'price' => $dbRow->price,
                        'quantity' => $dbRow->quantity,
                        'sale_start' => $this->normalizeDateMinute($dbRow->sale_start),
                        'sale_end' => $this->normalizeDateMinute($dbRow->sale_end),
                    ];
                    $new = [
                        'name' => $incomingByKey[$k]['name'] ?? $dbRow->name,
                        'description' => $incomingByKey[$k]['description'] ?? $dbRow->description,
                        'price' => isset($incomingByKey[$k]['price']) ? (float)$incomingByKey[$k]['price'] : $dbRow->price,
                        'quantity' => isset($incomingByKey[$k]['quantity']) ? (int)$incomingByKey[$k]['quantity'] : $dbRow->quantity,
                        'sale_start' => $this->normalizeDateMinute($incomingByKey[$k]['sale_start'] ?? $dbRow->sale_start),
                        'sale_end' => $this->normalizeDateMinute($incomingByKey[$k]['sale_end'] ?? $dbRow->sale_end),
                    ];
                    $fields = ['name','description','price','quantity','sale_start','sale_end'];
                    $fieldDiff = [];
                    foreach ($fields as $f) {
                        $o = is_null($old[$f]) ? null : (string)$old[$f];
                        $n = is_null($new[$f]) ? null : (string)$new[$f];
                        if ($o !== $n) {
                            $fieldDiff[$f] = ['from' => $old[$f], 'to' => $new[$f]];
                        }
                    }
                    if (!empty($fieldDiff)) {
                        $editedChanges[] = [
                            'ticket' => $dbRow->name,
                            'changes' => $fieldDiff,
                        ];
                    }
                }

                // Perform upserts
                foreach ($incomingByKey as $k => $t) {
                    if (isset($existingByKey[$k])) {
                        $row = $existingByKey[$k];
                        $existingByKey[$k]->update([
                            'name' => $t['name'] ?? $existingByKey[$k]->name,
                            'description' => $t['description'] ?? $existingByKey[$k]->description,
                            'price' => ($event->is_free ?? false) ? 0 : ($t['price'] ?? $existingByKey[$k]->price),
                            'quantity' => max((int)($row->sold_count ?? 0), (int)($t['quantity'] ?? $existingByKey[$k]->quantity)),
                            'sale_start' => $t['sale_start'] ?? $existingByKey[$k]->sale_start,
                            'sale_end' => $t['sale_end'] ?? $existingByKey[$k]->sale_end,
                        ]);
                    } else {
                        EventTicket::create([
                            'event_id' => $event->id,
                            'name' => $t['name'] ?? 'Unnamed',
                            'description' => $t['description'] ?? null,
                            'price' => ($event->is_free ?? false) ? 0 : ($t['price'] ?? 0),
                            'quantity' => $t['quantity'] ?? null,
                            'sale_start' => $t['sale_start'] ?? null,
                            'sale_end' => $t['sale_end'] ?? null,
                        ]);
                    }
                }
                // Delete removed tickets (with purchase protection)
                foreach ($removed as $k) {
                    $ticketToDelete = $existingByKey[$k];
                    
                    // Check if this ticket has been purchased
                    $purchasedCount = \App\Models\TicketPurchase::where('event_id', $event->id)
                        ->where('unit_price', $ticketToDelete->price)
                        ->where('status', 'paid')
                        ->sum('quantity');
                    
                    if ($purchasedCount > 0) {
                        // Skip deletion and log warning
                        \Log::warning('Attempted to delete ticket with purchases', [
                            'event_id' => $event->id,
                            'ticket_id' => $ticketToDelete->id,
                            'ticket_name' => $ticketToDelete->name,
                            'purchased_count' => $purchasedCount,
                            'user_id' => Auth::id()
                        ]);
                        
                        // Remove from removed array to avoid logging as deleted
                        $removed = array_diff($removed, [$k]);
                        continue;
                    }
                    
                    $ticketToDelete->delete();
                }

                // Only log activity if this is not a new event creation
                if (!$isNewEventCreation && (!empty($added) || !empty($removed) || !empty($editedChanges))) {
                    EventActivityLog::create([
                        'event_id' => $eventId,
                        'user_id' => Auth::id(),
                        'action' => 'tickets_types_changed',
                        'description' => 'Updated ticket types',
                        'metadata' => [
                            'event_title' => $event->title,
                            'added' => array_values($added),
                            'removed' => array_values($removed),
                            'edited' => $editedChanges,
                        ]
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to save ticket types to R2: ' . $e->getMessage());
                // Don't save to database if R2 fails
                return back()->with('error', 'Failed to save ticket types to cloud storage.');
            }
        } else {
            // Clear the path if no ticket types
            $event->update(['ticket_types_path' => null]);
        }

        // Store ticket settings data in R2
        // Get ticket_colors from request or existing settings
        $ticketColors = null;
        
        // Priority 1: Check directly in request (top-level ticket_colors)
        if ($request->has('ticket_colors')) {
            $ticketColors = $request->input('ticket_colors');
            // Decode if it's a JSON string
            if (is_string($ticketColors)) {
                $decoded = json_decode($ticketColors, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $ticketColors = $decoded;
                }
            }
        }
        
        // Priority 2: Check in ticket_settings nested object
        $ticketSettingsInput = $request->input('ticket_settings');
        if ((!$ticketColors || !is_array($ticketColors) || empty($ticketColors)) && $ticketSettingsInput && is_array($ticketSettingsInput)) {
            if (isset($ticketSettingsInput['ticket_colors']) && is_array($ticketSettingsInput['ticket_colors'])) {
                $ticketColors = $ticketSettingsInput['ticket_colors'];
            }
        }
        
        // If still no colors, load from existing settings or use defaults
        if (!is_array($ticketColors) || empty($ticketColors)) {
            try {
                $existingSettings = $this->loadTicketSettingsFromR2($event);
                if (isset($existingSettings['ticket_colors']) && is_array($existingSettings['ticket_colors'])) {
                    $ticketColors = $existingSettings['ticket_colors'];
                }
            } catch (\Exception $e) {
                // Ignore, will use defaults
            }
        }
        
        // Default colors if still not set
        $defaultColors = [
            'primary' => '#10b981',
            'secondary' => '#f59e0b',
            'accent' => '#059669'
        ];
        
        if (!is_array($ticketColors) || empty($ticketColors)) {
            $ticketColors = $defaultColors;
        }
        
        // Ensure all color keys exist
        $ticketColors = [
            'primary' => $ticketColors['primary'] ?? $defaultColors['primary'],
            'secondary' => $ticketColors['secondary'] ?? $defaultColors['secondary'],
            'accent' => $ticketColors['accent'] ?? $defaultColors['accent'],
        ];
        
        $ticketSettings = [
            'max_tickets_per_order' => $request->input('max_tickets_per_order', 10),
            'refund_policy' => $request->input('refund_policy', ''),
            'allow_refunds' => $request->boolean('allow_refunds', true),
            'require_approval' => $request->boolean('require_approval', false),
            'allow_share_ticket' => $request->boolean('allow_share_ticket', true),
            'limit_per_user' => $request->boolean('limit_per_user', false),
            'max_tickets_per_user' => $request->input('max_tickets_per_user', 1),
            'ticket_colors' => $ticketColors, // Always include ticket_colors
        ];

        try {
            // Load existing settings to detect changes (normalized)
            $existingSettingsRaw = $this->loadTicketSettingsFromR2($event);
            $existingSettings = $this->normalizeTicketSettings($existingSettingsRaw);
            $normalizedNewSettings = $this->normalizeTicketSettings($ticketSettings);
            $settingsChanged = json_encode($existingSettings) !== json_encode($normalizedNewSettings);
            $eventDir = "events/{$eventId}";
            $ticketSettingsJson = json_encode($ticketSettings);
            Storage::disk('r2')->put($eventDir . '/ticket_settings.json', $ticketSettingsJson);
            
            // Log activity for ticket settings changes only if actually changed and not during creation
            if (!$isNewEventCreation && $settingsChanged) {
                EventActivityLog::create([
                    'event_id' => $eventId,
                    'user_id' => Auth::id(),
                    'action' => 'tickets_settings_updated',
                    'description' => 'Updated ticket settings',
                    'metadata' => [
                        'event_title' => $event->title,
                        'processing_fee' => $normalizedNewSettings['processing_fee_percentage'],
                        'allow_refunds' => $normalizedNewSettings['allow_refunds'],
                        'changes' => [
                            'from' => $existingSettings,
                            'to' => $normalizedNewSettings,
                        ]
                    ]
                ]);
            }
            
        } catch (\Exception $e) {
            \Log::error('Failed to save ticket settings to R2: ' . $e->getMessage());
            return back()->with('error', 'Failed to save ticket settings to cloud storage.');
        }

        return redirect()->route('organizer.events.create.certificate', ['id' => $eventId]);
    }

    public function autosaveStep3(Request $request)
    {
        try {
            $eventId = $request->get('id');
            
            
            if (!$eventId) {
                \Log::error('Autosave Step 3: Event ID is required');
                return response()->json(['success' => false, 'message' => 'Event ID is required'], 400);
            }

            $event = Event::find($eventId);
            $isAdmin = Auth::guard('admin')->check();
            $isOwner = $event && $event->user_id === Auth::id();
            $isAdminEvent = $event && $event->user_id === null && $isAdmin;
            
            if (!$event || !in_array($event->status, ['draft', 'published', 'approved', 'pending_approval', 'scheduled']) || (!$isOwner && !$isAdminEvent)) {
                \Log::error('Autosave Step 3: Event not found or not authorized', [
                    'event_id' => $eventId,
                    'event_exists' => $event ? true : false,
                    'event_status' => $event ? $event->status : 'not found',
                    'user_id' => Auth::id()
                ]);
                return response()->json(['success' => false, 'message' => 'Event not found or not authorized'], 404);
            }

            $ticketTypes = $request->input('ticket_types', []);
            
            // Decode JSON string if it's a string
            if (is_string($ticketTypes)) {
                $ticketTypes = json_decode($ticketTypes, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    \Log::error('Autosave Step 3: Invalid JSON in ticket_types', [
                        'event_id' => $eventId,
                        'raw_input' => $request->input('ticket_types'),
                        'json_error' => json_last_error_msg()
                    ]);
                    return response()->json(['success' => false, 'message' => 'Invalid ticket data format'], 400);
                }
            }
            
            // Log the count of incoming tickets for debugging
            \Log::info('Autosave Step 3: Received ticket types', [
                'event_id' => $eventId,
                'ticket_count' => is_array($ticketTypes) ? count($ticketTypes) : 0,
                'ticket_types' => is_array($ticketTypes) ? array_map(function($t) {
                    return [
                        'id' => $t['id'] ?? 'null',
                        'name' => $t['name'] ?? 'unnamed',
                        'has_id' => isset($t['id']) && !empty($t['id']) && $t['id'] !== 'null'
                    ];
                }, $ticketTypes) : []
            ]);

            // If event is free, force all ticket prices to 0 before processing
            if ($event && $event->is_free && is_array($ticketTypes)) {
                foreach ($ticketTypes as &$t) {
                    if (is_array($t)) {
                        $t['price'] = 0;
                    }
                }
                unset($t);
            }

            // Enforce: ticket sale_end must match event end during autosave as well
            if ($event && $event->end_date && is_array($ticketTypes)) {
                foreach ($ticketTypes as &$t) {
                    if (is_array($t)) {
                        $t['sale_end'] = optional($event->end_date)->format('Y-m-d H:i');
                    }
                }
                unset($t);
            }
            // Upsert ticket types into DB and log only add/remove
            if (!empty($ticketTypes)) {
                // Normalize incoming
                $incoming = $this->normalizeTicketTypesArray($ticketTypes);
                
                \Log::info('Autosave Step 3: Normalized incoming tickets', [
                    'event_id' => $eventId,
                    'normalized_count' => count($incoming),
                    'sample_tickets' => array_slice($incoming, 0, 3) // Log first 3 for debugging
                ]);
                
                $existing = $event->eventTickets()->get();
                $existingByKey = [];
                foreach ($existing as $row) {
                    // Always use ID for existing tickets (they should all have IDs)
                    $k = $row->id ? (string)$row->id : 'existing_' . $row->getKey();
                    $existingByKey[$k] = $row;
                }
                
                \Log::info('Autosave Step 3: Existing tickets loaded', [
                    'event_id' => $eventId,
                    'existing_count' => count($existingByKey),
                    'existing_ids' => array_keys($existingByKey)
                ]);
                
                $incomingByKey = [];
                // CRITICAL FIX: Use array index to ensure ALL tickets are processed
                // This prevents tickets with same names from overwriting each other
                // Even if 20 tickets have the same name, they'll all be saved
                foreach ($incoming as $index => $t) {
                    // Remove temporary index field if present
                    $arrayIndex = $t['_array_index'] ?? $index;
                    unset($t['_array_index']);
                    
                    // For tickets with IDs, use the ID as key (existing tickets)
                    if (isset($t['id']) && !empty($t['id']) && $t['id'] !== 'null' && $t['id'] !== null && is_numeric($t['id'])) {
                        $k = (string)$t['id'];
                    } else {
                        // For new tickets without IDs, use array index to ensure uniqueness
                        // This is CRITICAL: prevents multiple tickets with same/empty names from overwriting each other
                        // Each ticket gets a unique key: new_0, new_1, new_2, etc.
                        $k = 'new_' . $arrayIndex;
                    }
                    
                    // If key already exists, append index to make it unique (shouldn't happen but safety check)
                    $originalKey = $k;
                    $keyIndex = 0;
                    while (isset($incomingByKey[$k])) {
                        $keyIndex++;
                        $k = $originalKey . '_' . $keyIndex;
                    }
                    
                    $incomingByKey[$k] = $t;
                }
                
                \Log::info('Autosave Step 3: Incoming tickets keyed', [
                    'event_id' => $eventId,
                    'incoming_count' => count($incomingByKey),
                    'incoming_keys' => array_keys($incomingByKey),
                    'new_ticket_keys' => array_filter(array_keys($incomingByKey), function($k) { return strpos($k, 'new_') === 0; })
                ]);

                // Match incoming tickets to existing tickets by ID
                // For new tickets without IDs, they will all be created
                $added = [];
                $removed = [];
                $ticketMapping = []; // Maps incoming key to existing row (if exists)
                
                // Build mapping: match incoming tickets with IDs to existing tickets
                foreach ($incomingByKey as $k => $t) {
                    if (isset($t['id']) && !empty($t['id']) && $t['id'] !== 'null' && $t['id'] !== null) {
                        $idKey = (string)$t['id'];
                        if (isset($existingByKey[$idKey])) {
                            $ticketMapping[$k] = $existingByKey[$idKey];
                            unset($existingByKey[$idKey]); // Remove from existing so it's not considered for removal
                        } else {
                            $added[] = $k; // Ticket with ID but doesn't exist in DB (shouldn't happen but handle it)
                        }
                    } else {
                        $added[] = $k; // New ticket without ID - will be created
                    }
                }
                
                // Any remaining existing tickets that weren't matched should be removed
                foreach ($existingByKey as $k => $row) {
                    $removed[] = $k;
                }

                // Upsert and delete
                $editChanges = [];
                $createdCount = 0;
                $updatedCount = 0;
                
                foreach ($incomingByKey as $k => $t) {
                    // Check if this ticket matches an existing one (by ID)
                    $row = $ticketMapping[$k] ?? null;
                    
                    if ($row) {
                        // Update existing ticket
                        $updatedCount++;
                        $diff = [];
                        $newName = $t['name'] ?? $row->name;
                        if (($t['name'] ?? $row->name) !== $row->name) { $diff['name'] = ['from' => $row->name, 'to' => $t['name']]; }
                        if (array_key_exists('description', $t) && (string)($t['description'] ?? '') !== (string)($row->description ?? '')) { $diff['description'] = ['from' => $row->description, 'to' => $t['description']]; }
                        if (array_key_exists('price', $t) && (float)$t['price'] !== (float)$row->price) { $diff['price'] = ['from' => $row->price, 'to' => $t['price']]; }
                        if (array_key_exists('quantity', $t) && (int)$t['quantity'] !== (int)$row->quantity) { $diff['quantity'] = ['from' => $row->quantity, 'to' => $t['quantity']]; }
                        // Check is_active changes
                        if (array_key_exists('is_active', $t)) {
                            $newIsActive = filter_var($t['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                            if ($newIsActive === null) {
                                $newIsActive = in_array(strtolower((string)$t['is_active']), ['1', 'true', 'yes'], true);
                            }
                            $oldIsActive = (bool)$row->is_active;
                            if ($newIsActive !== $oldIsActive) {
                                $diff['is_active'] = ['from' => $oldIsActive ? 'Active' : 'Inactive', 'to' => $newIsActive ? 'Active' : 'Inactive'];
                            }
                        }
                        $normDate = function($v){ return $this->normalizeDateMinute($v); };
                        if (array_key_exists('sale_start', $t) && $normDate($t['sale_start'] ?? null) !== $normDate($row->sale_start)) { $diff['sale_start'] = ['from' => optional($row->sale_start)->format('Y-m-d H:i'), 'to' => $t['sale_start']]; }
                        if (array_key_exists('sale_end', $t) && $normDate($t['sale_end'] ?? null) !== $normDate($row->sale_end)) { $diff['sale_end'] = ['from' => optional($row->sale_end)->format('Y-m-d H:i'), 'to' => $t['sale_end']]; }

                        if (!empty($diff)) {
                            $editChanges[] = [
                                'id' => $row->id,
                                'name' => $row->name,
                                'changes' => $diff,
                            ];
                        }

                        // Handle is_active field - convert string '1'/'0' to boolean
                        $isActive = true; // Default to active
                        if (isset($t['is_active'])) {
                            $isActive = filter_var($t['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                            if ($isActive === null) {
                                $isActive = in_array(strtolower((string)$t['is_active']), ['1', 'true', 'yes'], true);
                            }
                        }
                        
                        $row->update([
                            'name' => $newName,
                            'description' => $t['description'] ?? $row->description,
                            'price' => ($event->is_free ?? false) ? 0 : ($t['price'] ?? $row->price),
                            // Quantity cannot go below already sold
                            'quantity' => max((int)($row->sold_count ?? 0), (int)($t['quantity'] ?? $row->quantity)),
                            'sale_start' => $t['sale_start'] ?? $row->sale_start,
                            // Force sale_end to event end
                            'sale_end' => $event->end_date ?? ($t['sale_end'] ?? $row->sale_end),
                            'is_active' => $isActive,
                        ]);
                    } else {
                        // Create new ticket - ALL new tickets should be created
                        $createdCount++;
                        
                        // Handle is_active field - convert string '1'/'0' to boolean
                        $isActive = true; // Default to active for new tickets
                        if (isset($t['is_active'])) {
                            $isActive = filter_var($t['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                            if ($isActive === null) {
                                $isActive = in_array(strtolower((string)$t['is_active']), ['1', 'true', 'yes'], true);
                            }
                        }
                        
                        EventTicket::create([
                            'event_id' => $event->id,
                            'name' => $t['name'] ?? 'Unnamed',
                            'description' => $t['description'] ?? null,
                            'price' => $t['price'] ?? 0,
                            'quantity' => $t['quantity'] ?? null,
                            'sale_start' => $t['sale_start'] ?? null,
                            // Force sale_end to event end
                            'sale_end' => $event->end_date ?? ($t['sale_end'] ?? null),
                            'is_active' => $isActive,
                        ]);
                    }
                }
                
                // Verify all tickets were processed by checking final count
                $finalTicketCount = $event->eventTickets()->count();
                $expectedCount = count($incomingByKey) - count($removed);
                
                \Log::info('Autosave Step 3: Tickets processed', [
                    'event_id' => $eventId,
                    'received_raw_count' => is_array($ticketTypes) ? count($ticketTypes) : 0,
                    'normalized_count' => count($incoming),
                    'total_incoming_keyed' => count($incomingByKey),
                    'created_count' => $createdCount,
                    'updated_count' => $updatedCount,
                    'removed_count' => count($removed),
                    'final_db_count' => $finalTicketCount,
                    'expected_final_count' => $expectedCount,
                    'match' => $finalTicketCount === $expectedCount ? 'YES' : 'NO - MISMATCH!'
                ]);
                
                // Warn if not all tickets were saved
                if ($finalTicketCount < $expectedCount) {
                    \Log::warning('Autosave Step 3: Not all tickets were saved!', [
                        'event_id' => $eventId,
                        'expected' => $expectedCount,
                        'actual' => $finalTicketCount,
                        'missing' => $expectedCount - $finalTicketCount,
                        'created' => $createdCount,
                        'updated' => $updatedCount,
                        'removed' => count($removed),
                        'incoming_keys' => array_keys($incomingByKey),
                        'new_ticket_keys_count' => count(array_filter(array_keys($incomingByKey), function($k) { return strpos($k, 'new_') === 0; }))
                    ]);
                }
                
                $blockedDeletes = [];
                foreach ($removed as $k) {
                    $row = $existingByKey[$k];
                    $sold = (int)($row->sold_count ?? 0);
                    if ($sold > 0) {
                        $blockedDeletes[] = $row->name ?? (string)$row->id;
                        continue;
                    }
                    $row->delete();
                }
                if (!empty($blockedDeletes)) {
                    \Log::info('Skipped deleting purchased ticket types', [
                        'event_id' => $event->id,
                        'tickets' => $blockedDeletes,
                    ]);
                }

                if (!empty($added) || !empty($removed)) {
                    $addedNames = array_map(function($k) use ($incomingByKey){ return $incomingByKey[$k]['name'] ?? $k; }, array_values($added));
                    $removedNames = array_map(function($k) use ($existingByKey){ return $existingByKey[$k]->name ?? $k; }, array_values($removed));
                    // queue into 60s buffer
                    \Log::info('Tickets autosave queue add/remove', [
                        'event_id' => $event->id,
                        'user_id' => Auth::id(),
                        'added' => $addedNames,
                        'removed' => $removedNames,
                    ]);
                    $this->queueTicketActivity($event, Auth::id(), [
                        'added' => $addedNames,
                        'removed' => $removedNames,
                    ]);
                }
                if (!empty($editChanges)) {
                    \Log::info('Tickets autosave queue edits', [
                        'event_id' => $event->id,
                        'user_id' => Auth::id(),
                        'edits' => $editChanges,
                    ]);
                    $this->queueTicketActivity($event, Auth::id(), [
                        'edits' => $editChanges,
                    ]);
                }
            } else {
                // If empty list submitted, check for purchases before deleting all existing
                $had = $event->eventTickets()->count();
                if ($had > 0) {
                    // Check if any tickets have been purchased
                    $totalPurchased = \App\Models\TicketPurchase::where('event_id', $event->id)
                        ->where('status', 'paid')
                        ->sum('quantity');
                    
                    if ($totalPurchased > 0) {
                        // Don't allow bulk deletion if tickets have been purchased
                        \Log::warning('Attempted bulk ticket deletion with purchases', [
                            'event_id' => $event->id,
                            'total_purchased' => $totalPurchased,
                            'user_id' => Auth::id()
                        ]);
                        
                        // Return error response for AJAX requests
                        if ($request->ajax() || $request->wantsJson()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Cannot delete tickets that have been purchased',
                                'title' => 'Tickets Protected',
                                'text' => "You cannot delete tickets that have already been purchased by customers. {$totalPurchased} tickets have been sold.",
                            ], 400);
                        }
                        
                        return back()->withErrors(['ticket_types' => "Cannot delete tickets that have been purchased. {$totalPurchased} tickets have been sold."]);
                    }
                    
                    $names = $event->eventTickets()->pluck('name')->toArray();
                    $event->eventTickets()->delete();
                    
                    // Only log activity if this is not a new event creation
                    $isNewEventCreation = $event->status === 'draft' && $event->created_at->diffInMinutes(now()) < 5;
                    if (!$isNewEventCreation) {
                        EventActivityLog::create([
                            'event_id' => $eventId,
                            'user_id' => Auth::id(),
                            'action' => 'tickets_types_changed',
                            'description' => 'Removed all tickets',
                            'metadata' => [
                                'event_title' => $event->title,
                                'action_type' => 'autosave',
                                'removed' => $names,
                            ]
                        ]);
                    }
                }
            }

            // Store ticket settings data in R2
            $ticketSettingsInput = $request->input('ticket_settings');
            
            // Get ticket_colors from multiple possible locations (for backwards compatibility)
            $ticketColors = null;
            
            // Priority 1: Check directly in request (top-level ticket_colors)
            if ($request->has('ticket_colors')) {
                $ticketColors = $request->input('ticket_colors');
                // Decode if it's a JSON string
                if (is_string($ticketColors)) {
                    $decoded = json_decode($ticketColors, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $ticketColors = $decoded;
                    }
                }
            }
            
            // Priority 2: Check in ticket_settings nested object
            if ((!$ticketColors || !is_array($ticketColors) || empty($ticketColors)) && $ticketSettingsInput && is_array($ticketSettingsInput)) {
                if (isset($ticketSettingsInput['ticket_colors']) && is_array($ticketSettingsInput['ticket_colors'])) {
                    $ticketColors = $ticketSettingsInput['ticket_colors'];
                }
            }
            
            // Validate and ensure ticket_colors structure is correct
            $defaultColors = [
                'primary' => '#10b981',
                'secondary' => '#f59e0b',
                'accent' => '#059669'
            ];
            
            if (!is_array($ticketColors) || empty($ticketColors)) {
                // Try to get from existing settings if nothing was provided
                try {
                    $existingSettings = Storage::disk('r2')->get("events/{$eventId}/ticket_settings.json");
                    if ($existingSettings) {
                        $existing = json_decode($existingSettings, true);
                        if (isset($existing['ticket_colors']) && is_array($existing['ticket_colors'])) {
                            $ticketColors = $existing['ticket_colors'];
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore, will use defaults
                }
                
                // If still no colors, use defaults
                if (!is_array($ticketColors) || empty($ticketColors)) {
                    $ticketColors = $defaultColors;
                }
            }
            
            // Ensure all color keys exist with defaults
            $ticketColors = [
                'primary' => $ticketColors['primary'] ?? $defaultColors['primary'],
                'secondary' => $ticketColors['secondary'] ?? $defaultColors['secondary'],
                'accent' => $ticketColors['accent'] ?? $defaultColors['accent'],
            ];
            
            $ticketSettings = [
                'max_tickets_per_order' => $request->input('max_tickets_per_order', 10),
                'refund_policy' => $request->input('refund_policy', ''),
                'allow_refunds' => $request->boolean('allow_refunds', true),
                'require_approval' => $request->boolean('require_approval', false),
                'allow_share_ticket' => $request->boolean('allow_share_ticket', true),
                'limit_per_user' => $request->boolean('limit_per_user', false),
                'max_tickets_per_user' => $request->input('max_tickets_per_user', 1),
                'ticket_colors' => $ticketColors, // Always include ticket_colors
            ];
            
            \Log::info('Autosave Step 3 - Ticket settings being saved', [
                'event_id' => $eventId,
                'ticket_colors' => $ticketColors,
                'full_settings' => $ticketSettings
            ]);

            try {
                $eventDir = "events/{$eventId}";
                $ticketSettingsJson = json_encode($ticketSettings);
                Storage::disk('r2')->put($eventDir . '/ticket_settings.json', $ticketSettingsJson);
                // Do not log settings autosave to reduce noise
            } catch (\Exception $e) {
                \Log::error('Failed to autosave ticket settings to R2: ' . $e->getMessage(), [
                    'event_id' => $eventId,
                    'error' => $e->getMessage()
                ]);
                return response()->json(['success' => false, 'message' => 'Failed to save settings to cloud storage'], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ticket data saved successfully',
            ]);

        } catch (\Exception $e) {
            \Log::error('Autosave step 3 failed: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Autosave failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Flush queued autosave activity when user exits the page.
     */
    public function autosaveFlush(Request $request)
    {
        $eventId = $request->get('id');
        $event = Event::find($eventId);
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event && $event->user_id === Auth::id();
        $isAdminEvent = $event && $event->user_id === null && $isAdmin;
        
        if (!$event || !in_array($event->status, ['draft', 'published', 'approved', 'pending_approval', 'scheduled']) || (!$isOwner && !$isAdminEvent)) {
            return response()->json(['success' => false, 'message' => 'Event not found or not authorized'], 404);
        }
        $this->flushTicketActivity($event, Auth::id());
        \Log::info('Autosave flush processed', [
            'event_id' => $event->id,
            'user_id' => Auth::id()
        ]);
        return response()->json(['success' => true]);
    }

    private function updateDraftWithStepData($stepNumber, $stepData)
    {
        try {
            $draftId = session('event_data.draft_id');
            if (!$draftId) return;

            $draft = Event::find($draftId);
            if (!$draft || $draft->status !== 'draft') return;

            switch ($stepNumber) {
                case 3:
                    // Store ticket types data in a JSON field or related table
                    $draft->update([
                        'ticket_types' => json_encode($stepData['ticket_types'] ?? []),
                    ]);
                    break;
            }

        } catch (\Exception $e) {
            \Log::error('Failed to update draft with step data: ' . $e->getMessage());
        }
    }

    /**
     * Load ticket types from R2 storage
     */
    public function loadTicketTypesFromR2(Event $event): array
    {
        \Log::info('Loading ticket types from R2:', [
            'event_id' => $event->id,
            'ticket_types_path' => $event->ticket_types_path
        ]);

        // Prefer stored path, but gracefully fall back to default location used by autosave
        $path = $event->ticket_types_path ?: ("events/{$event->id}/ticket_types.json");

        try {
            $disk = Storage::disk('r2');
            if ($disk->exists($path)) {
                $ticketTypesJson = $disk->get($path);
                $ticketTypes = json_decode($ticketTypesJson, true);
                
                \Log::info('Successfully loaded ticket types from R2:', [
                    'event_id' => $event->id,
                    'path' => $path,
                    'ticket_count' => is_array($ticketTypes) ? count($ticketTypes) : 0,
                    'ticket_types' => $ticketTypes
                ]);
                
                return is_array($ticketTypes) ? $ticketTypes : [];
            } else {
                \Log::warning('Ticket types file does not exist in R2:', [
                    'event_id' => $event->id,
                    'path' => $event->ticket_types_path
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load ticket types from R2: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'path' => $event->ticket_types_path,
                'error' => $e->getMessage()
            ]);
        }

        return [];
    }

    /**
     * Load ticket settings from R2 storage
     */
    public function loadTicketSettingsFromR2(Event $event): array
    {
        \Log::info('Loading ticket settings from R2:', [
            'event_id' => $event->id
        ]);

        try {
            $eventDir = "events/{$event->id}";
            $disk = Storage::disk('r2');
            $settingsPath = $eventDir . '/ticket_settings.json';
            
            if ($disk->exists($settingsPath)) {
                $ticketSettingsJson = $disk->get($settingsPath);
                $ticketSettings = json_decode($ticketSettingsJson, true);
                
                \Log::info('Successfully loaded ticket settings from R2:', [
                    'event_id' => $event->id,
                    'path' => $settingsPath,
                    'settings' => $ticketSettings
                ]);
                
                if (is_array($ticketSettings)) {
                    // Ensure ticket_colors are present with defaults if missing
                    if (!isset($ticketSettings['ticket_colors']) || empty($ticketSettings['ticket_colors'])) {
                        $ticketSettings['ticket_colors'] = [
                            'primary' => '#10b981',
                            'secondary' => '#f59e0b',
                            'accent' => '#059669',
                        ];
                    }
                    return $ticketSettings;
                }
                
                return [];
            } else {
                \Log::info('Ticket settings file does not exist in R2, using defaults:', [
                    'event_id' => $event->id,
                    'path' => $settingsPath
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load ticket settings from R2: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);
        }

        // Return default settings if file doesn't exist or error occurs
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

    /**
     * Show ticket management page for an existing event
     */
    public function tickets(Event $event)
    {
        \Log::info('TicketsController::tickets method called', [
            'event_id' => $event->id,
            'user_id' => Auth::id(),
            'guard' => Auth::guard()->getName(),
            'is_admin_guard' => Auth::guard('admin')->check(),
            'is_default_guard' => Auth::check()
        ]);

        // Check if user has access to this event (creator, staff, or admin)
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event->user_id === Auth::id();
        $isAdminEvent = $event->user_id === null && $isAdmin;
        $isStaff = $event->staff()->where('user_id', Auth::id())->exists();
        
        $hasAccess = $isAdmin || $isOwner || $isAdminEvent || $isStaff;
        
        if (!$hasAccess) {
            abort(403, 'Unauthorized access to this event.');
        }

        // Determine layout type
        $isAdmin = Auth::guard('admin')->check();
        $layoutType = $isAdmin ? 'admin' : 'organizer';

        \Log::info('TicketsController::tickets called', [
            'event_id' => $event->id,
            'is_admin' => $isAdmin,
            'layout_type' => $layoutType,
            'user_id' => Auth::id(),
            'guard' => Auth::guard()->getName()
        ]);

        // Check if user can manage tickets
        // Note: Middleware already validates access, so if we reach here, user has permission
        $user = Auth::user();
        $isOwner = $user && (int)$event->user_id === (int)$user->id;
        $staff = $event->staff()->where('user_id', $user?->id)->first();
        $canManageTicket = $isAdmin || $isOwner || ($staff?->can_manage_ticket ?? false) || ($staff?->role === 'organizer') || ($staff?->can_manage_event ?? false);

        // Check if event has ended - if so, show view-only mode
        $eventEnded = $event->end_date && now()->gt($event->end_date);
        
        // Check if event is upcoming
        $isUpcoming = !$event->end_date || now()->lt($event->end_date);
        
        // Allow editing for draft, pending_approval, published, and approved statuses if event is upcoming
        // Staff with can_manage_ticket permission can edit the same way owners can
        $canEdit = in_array($event->status, ['draft', 'pending_approval']) 
                   || ($canManageTicket && $isUpcoming && in_array($event->status, ['published', 'approved']));
        
        // Show read-only view only if:
        // 1. Event has ended, OR
        // 2. User doesn't have ticket management permission, OR
        // 3. Event status doesn't allow editing
        if (!$canManageTicket || $eventEnded || !$canEdit) {
            \Log::info('Show info view (read-only)', [
                'is_admin' => $isAdmin,
                'can_manage_ticket' => $canManageTicket,
                'event_ended' => $eventEnded,
                'can_edit' => $canEdit
            ]);
            
            // Load ticket data for preview
            $ticketTypes = $event->eventTickets()->get()->toArray();
            $ticketSettings = $this->loadTicketSettingsFromR2($event);
            
            \Log::info('Loading ticket info view', [
                'ticket_types_count' => count($ticketTypes)
            ]);
            
            return view('organizer.events.info.tickets', compact('event', 'ticketTypes', 'ticketSettings', 'layoutType'));
        }

        // Load ticket types from database with purchase information
        $ticketTypes = $event->eventTickets()->orderBy('id')->get()->map(function($ticket) use ($event) {
            $purchasedCount = \App\Models\TicketPurchase::where('event_id', $event->id)
                ->where('unit_price', $ticket->price)
                ->where('status', 'paid')
                ->sum('quantity');
            
            $ticket->purchased_count = $purchasedCount;
            $ticket->can_delete = $purchasedCount === 0;
            return $ticket;
        });
        
        // Load ticket settings from R2
        $ticketSettings = $this->loadTicketSettingsFromR2($event);
        
        // Get ticket sales statistics
        $totalTicketsSold = EventTicketInformation::where('event_id', $event->id)
            ->whereHas('ticketPurchase', function($query) {
                $query->where('status', 'paid');
            })
            ->count();
        
        $totalRevenue = \App\Models\TicketPurchase::where('event_id', $event->id)
            ->where('status', 'paid')
            ->sum('total_amount');
        
        // Get ticket sales by type
        $salesByType = $event->eventTickets()
            ->get()
            ->map(function($ticket) {
                // Count sold tickets by matching price (since ticket_information doesn't have event_ticket_id)
                $soldCount = \App\Models\TicketPurchase::where('event_id', $ticket->event_id)
                    ->where('status', 'paid')
                    ->where('unit_price', $ticket->price)
                    ->sum('quantity');
                
                $revenue = \App\Models\TicketPurchase::where('event_id', $ticket->event_id)
                    ->where('status', 'paid')
                    ->where('unit_price', $ticket->price)
                    ->sum('total_amount');
                
                return [
                    'id' => $ticket->id,
                    'name' => $ticket->name,
                    'price' => $ticket->price,
                    'quantity' => $ticket->quantity,
                    'sold_count' => $soldCount,
                    'remaining' => $ticket->quantity ? ($ticket->quantity - $soldCount) : null,
                    'revenue' => $revenue
                ];
            });

        // Prepare eventData for the form (same structure as step3)
        $eventData = [
            'id' => $event->id,
            'title' => $event->title,
            'start_date' => $event->start_date,
            'end_date' => $event->end_date,
            'location' => $event->location,
            'ticket_types' => $ticketTypes->map(function($ticket) {
                return [
                    'id' => $ticket->id,
                    'name' => $ticket->name,
                    'description' => $ticket->description,
                    'price' => $ticket->price,
                    'quantity' => $ticket->quantity,
                    'sale_start' => optional($ticket->sale_start)->format('Y-m-d H:i'),
                    'sale_end' => optional($ticket->sale_end)->format('Y-m-d H:i'),
                    'purchased_count' => $ticket->purchased_count ?? 0,
                    'can_delete' => $ticket->can_delete ?? true,
                ];
            })->toArray(),
            'ticket_settings' => $ticketSettings,
            'existing_thumbnail_url' => $event->thumbnail_url,
        ];

        // Get subscription information - use event owner's subscription, not staff member's subscription
        $currentUser = Auth::user();
        $isOwner = $event->user_id === $currentUser->id;
        
        // If staff is managing tickets, use owner's subscription
        $subscriptionUserId = $isOwner ? $currentUser->id : $event->user_id;
        $ownerUser = $subscriptionUserId ? \App\Models\User::find($subscriptionUserId) : null;
        
        $subscription = $ownerUser?->currentSubscription();
        $plan = $subscription?->getPlan();
        
        // If no subscription, check free plan
        if (!$plan) {
            $plan = \App\Models\SubscriptionPlan::where('slug', 'free')->first();
        }
        
        $baseMaxTicketsPerEvent = $plan?->tickets_per_event; // null means unlimited

        // Get additional tickets purchased for THIS event only (per-event scope)
        // Use owner's user_id for additional tickets, not staff member's
        $additionalTicketsPurchased = \App\Models\TicketLimitPurchase::where('user_id', $subscriptionUserId)
            ->where('event_id', $event->id)
            ->where('status', 'paid') // Only count paid additional tickets
            ->sum('additional_tickets');

        // Calculate the total maximum tickets allowed
        // If baseMaxTicketsPerEvent is null (unlimited), the total is also unlimited.
        // Otherwise, add the additional tickets to the base limit.
        $maxTicketsPerEvent = ($baseMaxTicketsPerEvent === null) 
            ? null 
            : $baseMaxTicketsPerEvent + $additionalTicketsPurchased;

        // If event is marked free, use free_event_ticket_limit as base and add additional tickets
        if ($event->is_free) {
            $freeCap = (int)($plan?->free_event_ticket_limit ?? 0);
            if ($freeCap > 0) {
                // Add additional tickets purchased to the free cap base
                $maxTicketsPerEvent = $freeCap + $additionalTicketsPurchased;
            }
        }

        $currentTicketCount = $event->eventTickets()->sum('quantity');
        $canBuyAdditionalTickets = true; // Always allow purchasing additional tickets

        // Debug logging
        \Log::info('Ticket limit calculation', [
            'current_user_id' => $currentUser->id,
            'subscription_user_id' => $subscriptionUserId,
            'is_owner' => $isOwner,
            'base_max_tickets' => $baseMaxTicketsPerEvent,
            'additional_tickets_purchased' => $additionalTicketsPurchased,
            'total_max_tickets' => $maxTicketsPerEvent,
            'current_ticket_count' => $currentTicketCount,
            'additional_purchases_count' => \App\Models\TicketLimitPurchase::where('user_id', $subscriptionUserId)->where('status', 'paid')->count()
        ]);
        
        // Determine layout type
        $isAdmin = Auth::guard('admin')->check();
        $layoutType = $isAdmin ? 'admin' : 'organizer';

        return view('organizer.events.tickets', compact(
            'event', 
            'eventData',
            'ticketTypes', 
            'ticketSettings',
            'totalTicketsSold',
            'totalRevenue',
            'salesByType',
            'isAdmin',
            'layoutType',
            'subscription',
            'plan',
            'maxTicketsPerEvent',
            'currentTicketCount',
            'canBuyAdditionalTickets',
            'baseMaxTicketsPerEvent',
            'additionalTicketsPurchased'
        ));
    }
    }