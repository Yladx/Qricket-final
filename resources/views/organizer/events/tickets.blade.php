<x-unified-layout :layout-type="$layoutType" :title="'Ticket Management - ' . $event->title" :page-title="'Tickets'">
    <x-slot name="header">
        <x-manage-header :event="$event" />
    </x-slot>
    
    <div class="py-4">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 text-gray-900 dark:text-gray-100">
                    @php 
                        $currentUser = auth()->user();
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
                        
                        $baseCap = $plan?->tickets_per_event;
                        
                        // Get additional tickets purchased for THIS event only
                        // Use owner's user_id for additional tickets, not staff member's
                        $additionalTicketsPurchased = \App\Models\TicketLimitPurchase::where('user_id', $subscriptionUserId)
                            ->where('event_id', $event->id)
                            ->where('status', 'paid')
                            ->sum('additional_tickets');

                        // Calculate the total maximum tickets allowed
                        $cap = ($baseCap === null) ? null : $baseCap + $additionalTicketsPurchased;

                        // If event is FREE, prioritize free_event_ticket_limit exclusively
                        $freeCap = (int)($plan?->free_event_ticket_limit ?? 0);
                        if (($event->is_free ?? false) && $freeCap > 0) {
                            $cap = $freeCap;
                        }
                    @endphp
                    <meta name="tickets-per-event" content="{{ $cap === null ? 'null' : $cap }}">
                    @if($event->is_free ?? false)
                        <meta name="event-is-free" content="1">
                        @if(($freeCap ?? 0) > 0)
                            <meta name="free-event-ticket-limit" content="{{ $freeCap }}">
                        @endif
                    @else
                        <meta name="event-is-free" content="0">
                    @endif
                    
                    <!-- Subscription Limit Info -->
                    <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    @if(isset($maxTicketsPerEvent) && $maxTicketsPerEvent !== null)
                                    <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                                        Ticket Limit: {{ $currentTicketCount ?? 0 }} / {{ $maxTicketsPerEvent }} tickets
                                    </p>
                                    <p class="text-xs text-blue-700 dark:text-blue-300">
                                        {{ $plan->name ?? 'Current Plan' }} 
                                        @if(isset($baseMaxTicketsPerEvent) && $baseMaxTicketsPerEvent !== null)
                                            ({{ $baseMaxTicketsPerEvent }} base)
                                        @endif
                                        @if(isset($additionalTicketsPurchased) && $additionalTicketsPurchased > 0)
                                            + {{ $additionalTicketsPurchased }} additional
                                        @endif
                                        - {{ $maxTicketsPerEvent - ($currentTicketCount ?? 0) }} tickets remaining
                                    </p>
                                    @else
                                    <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                                        Ticket Limit: {{ $currentTicketCount ?? 0 }} / Unlimited tickets
                                    </p>
                                    <p class="text-xs text-blue-700 dark:text-blue-300">
                                        {{ $plan->name ?? 'Current Plan' }} 
                                        @if(isset($additionalTicketsPurchased) && $additionalTicketsPurchased > 0)
                                            + {{ $additionalTicketsPurchased }} additional purchased
                                        @endif
                                        - Unlimited tickets available
                                    </p>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right">
                                @php
                                    $eventEnded = $event->end_date && now()->gt($event->end_date);
                                    $showBuyMore = !$isAdmin && !$eventEnded;
                                @endphp
                                @if(isset($maxTicketsPerEvent) && $maxTicketsPerEvent !== null && $currentTicketCount >= $maxTicketsPerEvent)
                                <div class="flex items-center space-x-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    Limit Reached
                                </span>
                                    @if($showBuyMore)
                                    <a href="{{ route('organizer.events.ticket-limits', $event) }}" 
                                       class="inline-flex items-center px-3 py-1.5 bg-qricket-600 hover:bg-qricket-700 text-white text-xs font-medium rounded-md transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Buy More
                                    </a>
                                    @endif
                                </div>
                                @else
                                @if($showBuyMore)
                                <a href="{{ route('organizer.events.ticket-limits', $event) }}" 
                                   class="inline-flex items-center px-3 py-1.5 bg-qricket-600 hover:bg-qricket-700 text-white text-xs font-medium rounded-md transition-colors">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Buy More
                                </a>
                                @endif
                                @endif
                            </div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('organizer.events.ticket.store') }}?id={{ $event->id }}" class="space-y-4" data-autosave-url="{{ route('organizer.events.ticket.autosave') }}?id={{ $event->id }}">
                        @csrf
                        @include('organizer.events.forms.tickets', ['eventId' => $event->id, 'eventData' => $eventData ?? [], 'event' => $event, 'isAdmin' => $isAdmin ?? false])

                      
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Include SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Include Step 3 JavaScript -->
    <script src="{{ asset('js/events/step3.js') }}" defer></script>
    
    <!-- Additional JavaScript for ticket management page -->
    <script>
        // Ensure SweetAlert is available globally
        window.Swal = Swal;
        
        // Add purchase additional tickets button to the page
        document.addEventListener('DOMContentLoaded', function() {
            @php
                $eventEnded = $event->end_date && now()->gt($event->end_date);
                $showFloatingButton = ($canBuyAdditionalTickets ?? true) && !$isAdmin && !$eventEnded;
            @endphp
            @if($showFloatingButton)
            // Add a floating button for purchasing additional tickets
            const purchaseButton = document.createElement('div');
            purchaseButton.innerHTML = `
                <div class="fixed bottom-6 right-6 z-50">
                    <a href="{{ route('organizer.events.ticket-limits', $event) }}" 
                       class="inline-flex items-center px-4 py-3 bg-qricket-600 hover:bg-qricket-700 text-white text-sm font-semibold rounded-lg shadow-lg transition-all duration-200 hover:shadow-xl">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Buy Additional Tickets
                    </a>
                </div>
            `;
            document.body.appendChild(purchaseButton);
            @endif
            
            // Override any existing alert function to use SweetAlert
            window.alert = function(message) {
                Swal.fire({
                    title: 'Notice',
                    text: message,
                    icon: 'info',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#10b981'
                });
            };
        });
    </script>
</x-unified-layout>