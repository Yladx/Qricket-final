<div class="space-y-4">
<div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                  Manage Ticket
                </h1>
                <p class="mt-0.5 text-xs text-gray-600 dark:text-gray-400">Create ticket types and pricing. Set limit and restriction for tickets.</p>
            </div>
            

    @php
        $isFreeEvent = isset($event) ? (bool)($event->is_free ?? false) : (bool)($eventData['is_free'] ?? false);
        $currentPlan = auth()->user()->currentSubscription()?->getPlan();
        $freeEventsCount = (int)($currentPlan->free_events_count ?? 0);
        $freeEventTicketLimit = (int)($currentPlan->free_event_ticket_limit ?? 0);
    @endphp

    @if($isFreeEvent && $freeEventTicketLimit > 0)
        <div class="mb-2 p-2 rounded border border-yellow-300 bg-yellow-50 dark:bg-yellow-900/20 text-xs text-yellow-800 dark:text-yellow-200">
            This is a Free Event. Ticket quantity is limited to {{ $freeEventTicketLimit }} by your plan ({{ $currentPlan->name ?? 'Plan' }}).
        </div>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Ticket Types</div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Add options like General Admission, VIP, or Student. Fields autosave.</p>
        </div>
        <div class="flex items-center space-x-2">
            @php
                $eventEnded = isset($event) && $event->end_date && now()->gt($event->end_date);
                $isCreateFlow = request()->routeIs('organizer.events.create*') || request()->routeIs('organizer.events.create');
                $showBuyLink = ($canBuyAdditionalTickets ?? true) && !$isCreateFlow && !($isAdmin ?? false) && !$eventEnded;
            @endphp
            @if($showBuyLink)
            <a href="{{ route('organizer.events.ticket-limits', $eventId) }}" 
               class="inline-flex items-center px-2.5 py-1 rounded-md bg-green-600 text-white hover:bg-green-700 text-xs font-medium transition-colors">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Buy Additional Tickets
            </a>
            @endif
            <button type="button" class="px-2.5 py-1 rounded-md bg-qricket-500 text-white hover:bg-qricket-600 text-xs" onclick="addTicketType()">Add Ticket Type</button>
        </div>
    </div>

    <div id="ticketTypes" class="space-y-2 mt-1">
        <!-- Ticket rows render here via JS; autosave serializes to hidden input -->
        <div id="ticket-empty" class="border border-dashed border-gray-300 dark:border-gray-700 rounded-md p-4 text-center text-xs text-gray-500 dark:text-gray-400">No ticket types yet. Click "Add Ticket Type".</div>
    </div>

    <!-- Name must be ticket_types for controller validation on submit -->
    <input type="hidden" id="ticket_types_json" name="ticket_types" value='@json($eventData['ticket_types'] ?? [])' data-initial='@json($eventData['ticket_types'] ?? [])'>

    <!-- Ticket Preview Section -->
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 mb-4">
        <div class="text-xs font-semibold text-gray-900 dark:text-gray-100 mb-2">Ticket Preview</div>
        <div id="ticket-preview-container" class="scale-75 origin-center mx-auto">
            @include('organizer.events.preview.ticket-preview', [
                'holderName' => auth()->user()->full_name ?? 'John Doe',
                'eventTitle' => $eventData['title'] ?? 'Event Title',
                'dateTime' => isset($eventData['start_date']) ? \Carbon\Carbon::parse($eventData['start_date'])->format('M j, Y \a\t g:i A') : 'Date & Time',
                'venue' => $eventData['location'] ?? 'Venue Location',
                'ticketType' => 'General Admission',
                'ticketId' => 'TKT-XXXX-1',
                'status' => 'PAID',
                'bgImage' => $eventData['existing_thumbnail_url'] ?? null,
                'primaryColor' => $primaryColor ?? '#10b981',
                'secondaryColor' => $secondaryColor ?? '#f59e0b',
                'accentColor' => $accentColor ?? '#059669',
            ])
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
        <div class="text-xs font-semibold text-gray-900 dark:text-gray-100 mb-1">Ticket Settings</div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Applies to checkout and order flow.</p>
        
        <!-- Ticket Color Customization -->
        <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="text-xs font-medium text-gray-900 dark:text-gray-100 mb-2">Ticket Color Customization</div>
            @php
                // Load saved ticket colors from eventData or use defaults
                $ticketSettings = $eventData['ticket_settings'] ?? [];
                $ticketColors = $ticketSettings['ticket_colors'] ?? [];
                $primaryColor = $ticketColors['primary'] ?? '#10b981';
                $secondaryColor = $ticketColors['secondary'] ?? '#f59e0b';
                $accentColor = $ticketColors['accent'] ?? '#059669';
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-0.5">Primary Color</label>
                    <div class="flex items-center space-x-1">
                        <input type="color" id="ticket-primary-color" class="w-6 h-6 rounded border border-gray-300 dark:border-gray-600 cursor-pointer" value="{{ $primaryColor }}">
                        <input type="text" id="ticket-primary-color-hex" class="flex-1 rounded bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 h-6 text-xs" value="{{ $primaryColor }}" placeholder="#10b981">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-0.5">Secondary Color</label>
                    <div class="flex items-center space-x-1">
                        <input type="color" id="ticket-secondary-color" class="w-6 h-6 rounded border border-gray-300 dark:border-gray-600 cursor-pointer" value="{{ $secondaryColor }}">
                        <input type="text" id="ticket-secondary-color-hex" class="flex-1 rounded bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 h-6 text-xs" value="{{ $secondaryColor }}" placeholder="#f59e0b">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-0.5">Accent Color</label>
                    <div class="flex items-center space-x-1">
                        <input type="color" id="ticket-accent-color" class="w-6 h-6 rounded border border-gray-300 dark:border-gray-600 cursor-pointer" value="{{ $accentColor }}">
                        <input type="text" id="ticket-accent-color-hex" class="flex-1 rounded bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 h-6 text-xs" value="{{ $accentColor }}" placeholder="#059669">
                    </div>
                </div>
            </div>
            <div class="mt-2 flex items-center justify-between">
                <button type="button" id="reset-ticket-colors" class="px-2 py-1 text-xs bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-500">
                    Reset to Default
                </button>
                <div id="color-save-indicator" class="hidden text-xs text-green-600 dark:text-green-400">
                    <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Auto-saved
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-0.5">Max tickets per order</label>
                <input id="ts-max" name="max_tickets_per_order" type="number" min="1" value="1" class="w-full rounded bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 h-7 text-xs">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-0.5">Refund policy</label>
                <textarea id="ts-refund" name="refund_policy" rows="2" class="w-full rounded bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-xs"></textarea>
            </div>
            <div class="flex items-center space-x-1">
                <input id="ts-allow-refunds" name="allow_refunds" type="checkbox" class="rounded border-gray-300 text-qricket-600 w-3 h-3">
                <label for="ts-allow-refunds" class="text-xs text-gray-600 dark:text-gray-300">Allow refunds</label>
            </div>
            <div class="flex items-center space-x-1">
                <input id="ts-require-approval" name="require_approval" type="checkbox" class="rounded border-gray-300 text-qricket-600 w-3 h-3">
                <label for="ts-require-approval" class="text-xs text-gray-600 dark:text-gray-300">Require approval</label>
            </div>
            <div class="flex items-center space-x-1">
                <input id="ts-allow-share" name="allow_share_ticket" type="checkbox" class="rounded border-gray-300 text-qricket-600 w-3 h-3">
                <label for="ts-allow-share" class="text-xs text-gray-600 dark:text-gray-300">Allow ticket sharing</label>
            </div>
            <div class="flex items-center space-x-1">
                <input id="ts-limit-per-user" name="limit_per_user" type="checkbox" class="rounded border-gray-300 text-qricket-600 w-3 h-3" onchange="toggleMaxTicketsPerUser()">
                <label for="ts-limit-per-user" class="text-xs text-gray-600 dark:text-gray-300">Set max limit per user</label>
            </div>
            <div id="max-tickets-per-user-container" class="hidden">
                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-0.5">Max tickets per user</label>
                <input id="ts-max-per-user" name="max_tickets_per_user" type="number" min="1" class="w-full rounded bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 h-7 text-xs">
            </div>
        </div>
    </div>

    <input type="hidden" id="ticket_settings_json" name="ticket_settings_json" value='@json($eventData['ticket_settings'] ?? [])' data-initial='@json($eventData['ticket_settings'] ?? [])'>
</div>

<script>
// Enforce plan limits when event is free
document.addEventListener('DOMContentLoaded', function() {
    const isFreeEvent = @json($isFreeEvent);
    const freeTicketLimit = @json($freeEventTicketLimit);
    if (!isFreeEvent || !freeTicketLimit) return;

    function getTotalConfiguredQuantity() {
        let total = 0;
        document.querySelectorAll('#ticketTypes .ticket-row input[name$="[quantity]"]').forEach(inp => {
            const v = parseInt(inp.value || '0', 10);
            if (!isNaN(v)) total += v;
        });
        return total;
    }

    function setPerInputMaxes(activeInput) {
        const qtyInputs = Array.from(document.querySelectorAll('#ticketTypes .ticket-row input[name$="[quantity]"]'));
        const total = qtyInputs.reduce((acc, el) => acc + (parseInt(el.value || '0', 10) || 0), 0);
        const current = parseInt(activeInput?.value || '0', 10) || 0;
        const remaining = Math.max(0, freeTicketLimit - (total - current));
        qtyInputs.forEach(inp => {
            const base = parseInt(inp.value || '0', 10) || 0;
            const max = inp === activeInput ? remaining + base : Math.max(0, freeTicketLimit - (total - base));
            inp.setAttribute('min', '0');
            inp.setAttribute('max', String(max));
        });
        updateLimitNotice();
    }

    function clampQuantities(e) {
        let total = getTotalConfiguredQuantity();
        if (total <= freeTicketLimit) return;
        // Reduce the last edited or last row to fit the limit
        const rows = Array.from(document.querySelectorAll('#ticketTypes .ticket-row input[name$="[quantity]"]'));
        for (let i = rows.length - 1; i >= 0 && total > freeTicketLimit; i--) {
            const inp = rows[i];
            let v = parseInt(inp.value || '0', 10) || 0;
            if (v <= 0) continue;
            const excess = total - freeTicketLimit;
            const newVal = Math.max(0, v - excess);
            if (newVal !== v) {
                inp.value = String(newVal);
                total = getTotalConfiguredQuantity();
            }
        }
        setPerInputMaxes(e?.target);
    }

    function updateLimitNotice() {
        const msgId = 'free-limit-notice';
        let el = document.getElementById(msgId);
        if (!el) {
            el = document.createElement('div');
            el.id = msgId;
            el.className = 'mt-2 text-xs text-yellow-700 dark:text-yellow-300';
            const container = document.getElementById('ticketTypes');
            container && container.prepend(el);
        }
        const remaining = Math.max(0, freeTicketLimit - getTotalConfiguredQuantity());
        el.textContent = `Free event limit: up to ${freeTicketLimit} total tickets across all types. Remaining: ${remaining}`;
    }

    // Observe changes in ticket rows (added by JS) and quantities
    const observer = new MutationObserver(() => {
        document.querySelectorAll('#ticketTypes .ticket-row input[name$="[quantity]"]').forEach(inp => {
            if (inp.dataset._limitBound) return;
            inp.dataset._limitBound = '1';
            inp.addEventListener('input', (e)=>{ setPerInputMaxes(e.target); clampQuantities(e); });
            inp.addEventListener('change', (e)=>{ setPerInputMaxes(e.target); clampQuantities(e); });
        });
        setPerInputMaxes();
        clampQuantities();
    });
    observer.observe(document.getElementById('ticketTypes'), { childList: true, subtree: true });

    // Initial attempt
    setPerInputMaxes();
    clampQuantities();
});

function toggleMaxTicketsPerUser() {
    const checkbox = document.getElementById('ts-limit-per-user');
    const container = document.getElementById('max-tickets-per-user-container');
    
    if (checkbox.checked) {
        container.classList.remove('hidden');
    } else {
        container.classList.add('hidden');
    }
}

// Initialize the toggle state on page load
document.addEventListener('DOMContentLoaded', function() {
    const checkbox = document.getElementById('ts-limit-per-user');
    if (checkbox && checkbox.checked) {
        document.getElementById('max-tickets-per-user-container').classList.remove('hidden');
    }
});
</script>
