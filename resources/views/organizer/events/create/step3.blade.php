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
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @php 
                        $user = auth()->user();
                        $subscription = $user?->currentSubscription();
                        $plan = $subscription?->getPlan();
                        
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
                    @endphp
                    <meta name="tickets-per-event" content="{{ $cap === null ? 'null' : $cap }}">
                    <form method="POST" action="{{ route('organizer.events.ticket.store') }}?id={{ $eventId }}" class="space-y-8" data-autosave-url="{{ route('organizer.events.ticket.autosave') }}?id={{ $eventId }}">
                        @csrf
                        @include('organizer.events.forms.tickets', ['eventId' => $eventId, 'eventData' => $eventData])

                        <!-- Navigation Buttons -->
                        <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('organizer.events.create.layout') }}?id={{ $eventId }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                Back to Design
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Next: Certificate
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </div>
                    </form>
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
    <script src="{{ asset('js/events/step3.js') }}" defer></script>
</x-app-layout>