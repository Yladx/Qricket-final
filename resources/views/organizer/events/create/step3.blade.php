<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-4">
            {{ __('Create Event - Step 3: Ticket Pricing') }}
        </h2>

        @include('organizer.events.components.progress-bar', ['currentStep' => 3])
    </x-slot>

    @php
        $step1 = $eventData['step1'] ?? $eventData ?? [];
        $eventTitle = $step1['title'] ?? 'Event Title';
        $startDate  = $step1['start_date'] ?? null;
        $endDate    = $step1['end_date'] ?? null;
        $location   = $step1['location'] ?? 'Venue';
        $thumb = $eventData['existing_thumbnail_url'] ?? null;
        $dateTime  = $startDate ? \Carbon\Carbon::parse($startDate)->format('n/j/Y \\a\\t g:i A') : 'Date & Time';
    @endphp


    <!-- Ticket Form -->
    <div class="pb-12 pt-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Form Column -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            @php 
                                $user = auth()->user();
                                $subscription = $user?->currentSubscription();
                                $plan = $subscription?->getPlan();
                                $event = \App\Models\Event::find($eventId);
                                
                                // If no subscription, check free plan
                                if (!$plan) {
                                    $plan = \App\Models\SubscriptionPlan::where('slug', 'free')->first();
                                }
                                
                                $baseCap = $plan?->tickets_per_event;
                                
                                // Get additional tickets purchased for THIS event only
                                $additionalTicketsPurchased = \App\Models\TicketLimitPurchase::where('user_id', $user->id)
                                    ->where('event_id', $eventId)
                                    ->where('status', 'paid')
                                    ->sum('additional_tickets');

                                // Calculate the total maximum tickets allowed
                                $cap = ($baseCap === null) ? null : $baseCap + $additionalTicketsPurchased;

                                // If the event is FREE, use free_event_ticket_limit as base and add additional tickets
                                $freeCap = (int)($plan?->free_event_ticket_limit ?? 0);
                                if (($event?->is_free ?? false) && $freeCap > 0) {
                                    $cap = $freeCap + $additionalTicketsPurchased;
                                }
                            @endphp
                            <meta name="tickets-per-event" content="{{ $cap === null ? 'null' : $cap }}">
                            @if(($event?->is_free ?? false) && ($freeCap ?? 0) > 0)
                                <meta name="event-is-free" content="1">
                                <meta name="free-event-ticket-limit" content="{{ $freeCap }}">
                            @endif
                            <form method="POST" action="{{ route('organizer.events.ticket.store') }}?id={{ $eventId }}" class="space-y-8" data-autosave-url="{{ route('organizer.events.ticket.autosave') }}?id={{ $eventId }}">
                                @csrf
                                @include('organizer.events.forms.tickets', ['eventId' => $eventId, 'eventData' => $eventData, 'event' => $event])

                                <!-- Navigation Buttons -->
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                                    <a href="{{ route('organizer.events.create.layout') }}?id={{ $eventId }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                        Back to Design
                                    </a>
                                    <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        Next: Certificate
                                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar Column -->
                <div class="lg:col-span-1">
                    <div class="sticky top-6 space-y-4">
                        <!-- Ticket Pricing Guide Sidebar -->
                        <div class="bg-teal-50 dark:bg-teal-900/20 border border-teal-200 dark:border-teal-800 rounded-lg p-4 shadow-sm">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-teal-600 dark:text-teal-400 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-teal-900 dark:text-teal-100 mb-2">Ticket Pricing Guide</h4>
                                    <div class="text-sm text-teal-800 dark:text-teal-200 space-y-2">
                                        <ul class="list-disc list-inside space-y-1.5 ml-2 text-xs">
                                            <li><strong>Ticket Types:</strong> Create different ticket types (e.g., General Admission, VIP, Student, Early Bird) with varying prices and quantities.</li>
                                            <li><strong>Pricing:</strong> Set the price for each ticket type. For free events, all tickets are automatically set to ₱0.00.</li>
                                            <li><strong>Quantity:</strong> Define how many tickets are available for each type. The total quantity across all ticket types cannot exceed your subscription plan limit.</li>
                                            <li><strong>Active Status:</strong> Toggle ticket types on/off. Inactive tickets won't be available for purchase but remain in your system.</li>
                                            <li><strong>Ticket Limit:</strong> Your subscription plan determines the maximum number of tickets you can sell per event. You can purchase additional tickets if needed.</li>
                                            <li><strong>Autosave:</strong> All changes are automatically saved as you type. No need to manually save.</li>
                                        </ul>
                                        <div class="mt-3 p-2 bg-teal-100 dark:bg-teal-900/30 rounded border border-teal-200 dark:border-teal-700">
                                            <p class="text-xs font-medium text-teal-900 dark:text-teal-100 mb-1">💡 Tip:</p>
                                            <p class="text-xs text-teal-800 dark:text-teal-200">Create multiple ticket types to offer different pricing tiers and increase revenue. You can preview how your tickets will look before publishing.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Dates for JavaScript -->
    <script>
        window.eventDates = {
            startDate: '{{ $startDate }}',
            endDate: '{{ $endDate }}'
        };
    </script>
    
    <!-- Include Step 3 JavaScript -->
    <script src="{{ asset('js/events/step3.js') }}?v={{ time() }}" defer></script>
</x-app-layout>