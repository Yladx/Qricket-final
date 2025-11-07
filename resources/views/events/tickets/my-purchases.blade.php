<x-app-layout title="My Tickets" >
    <x-slot name="header">
        <div >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-6 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">My Tickets</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Your ticket purchase history grouped by event
                        </p>
                    </div>
                    <a href="{{ route('events.browse') }}" 
                       class="inline-flex items-center px-4 py-2 bg-qricket-500 hover:bg-qricket-600 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Browse Events
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if($events->count() > 0)
                <!-- Events Grid -->
                <div class="space-y-6">
                    @foreach($events as $eventGroup)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden cursor-pointer" onclick="toggleIndividualTickets({{ $eventGroup->event->id }})">
                            <div class="p-6">
                                <div class="flex items-start justify-between">
                                    <!-- Event Info -->
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-4">
                                            <!-- Event Thumbnail -->
                                            <div class="flex-shrink-0">
                                                @if($eventGroup->event->thumbnail_url)
                                                    <img src="{{ $eventGroup->event->thumbnail_url }}" 
                                                         alt="{{ $eventGroup->event->title }}" 
                                                         class="w-20 h-20 object-cover rounded-lg">
                                                @else
                                                    <div class="w-20 h-20 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <!-- Event Details -->
                                            <div class="flex-1">
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ $eventGroup->event->title }}
                                                </h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                                    {{ $eventGroup->purchases->pluck('ticketType.name')->unique()->implode(', ') }}
                                                </p>
                                                <div class="flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                                                    <div class="flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                        {{ \Carbon\Carbon::parse($eventGroup->event->start_date)->format('M d, Y g:i A') }}
                                                    </div>
                                                    @if($eventGroup->event->location)
                                                    <div class="flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        </svg>
                                                        {{ $eventGroup->event->location }}
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Ticket Summary -->
                                    <div class="text-right ml-6">
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            @if(isset($eventGroup->has_received) && $eventGroup->has_received)
                                                <div class="text-xs text-green-600 dark:text-green-400 mb-1">Received Ticket</div>
                                            @endif
                                            <div>Tickets: <span id="tickets-count-{{ $eventGroup->event->id }}">{{ $eventGroup->total_tickets }}</span></div>

                                            <div class="flex items-center justify-end gap-3 mt-3">
                                                <a href="{{ route('events.show', $eventGroup->event) }}" 
                                                   onclick="event.stopPropagation()"
                                                   class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors duration-200">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    View Event
                                                </a>
                                                @if($eventGroup->allow_sharing && isset($eventGroup->has_purchased) && $eventGroup->has_purchased)
                                                <button onclick="event.stopPropagation(); openShareModal({{ $eventGroup->event->id }}, {{ $eventGroup->total_tickets }})" 
                                                        class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                                                    </svg>
                                                    Share Tickets
                                                </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Individual Tickets Section -->
                                <div id="individual-tickets-{{ $eventGroup->event->id }}" class="hidden mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Individual Tickets</h4>
                                        <div class="flex items-center space-x-2">
                                            <label for="hide-shared-{{ $eventGroup->event->id }}" class="flex items-center text-xs text-gray-500 dark:text-gray-400" onclick="event.stopPropagation()">
                                                <input type="checkbox" id="hide-shared-{{ $eventGroup->event->id }}" 
                                                       class="rounded border-gray-300 text-qricket-600 focus:ring-qricket-500" 
                                                       onclick="event.stopPropagation()" 
                                                       onchange="event.stopPropagation(); toggleSharedTickets({{ $eventGroup->event->id }})">
                                                <span class="ml-1">Hide shared tickets</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="space-y-2" id="tickets-list-{{ $eventGroup->event->id }}">
                                        @foreach($eventGroup->individual_tickets as $ticket)
                                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg ticket-item" 
                                             data-ticket-id="{{ $ticket->id }}" 
                                             data-shared="{{ (isset($ticket->ticket_type) && $ticket->ticket_type === 'gifted') ? 'true' : 'false' }}"
                                             data-shareable="{{ (isset($ticket->ticket_type) && $ticket->ticket_type === 'gifted') ? 'false' : 'true' }}">
                                            <div class="flex items-center space-x-3">
                                                <span class="inline-flex items-center justify-center w-8 h-8 bg-green-500 text-white text-sm font-medium rounded-full">
                                                    {{ $loop->iteration }}
                                                </span>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        Ticket #{{ $loop->iteration }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        ID: {{ $ticket->ticket_id ?? ($ticket->event_id . '_' . strtoupper(substr(md5($ticket->id), 0, 8))) }}
                                                    </div>
                                                    @if(isset($ticket->ticket_type) && $ticket->ticket_type === 'gifted')
                                                    <div class="text-xs text-orange-600 dark:text-orange-400 mt-0.5">
                                                        Gifted to {{ $ticket->user->full_name ?? 'Recipient' }}
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-3">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Valid
                                                </span>
                                                @if(!isset($ticket->ticket_type) || $ticket->ticket_type != 'gifted')
                                                <button onclick="event.stopPropagation(); viewTicketDetails('{{ $ticket->id }}')" 
                                                        class="inline-flex items-center px-3 py-1 bg-qricket-500 hover:bg-qricket-600 text-white text-xs font-medium rounded transition-colors duration-200">
                                                    View Details
                                                </button>
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    
                                    <!-- Share Requests Section -->
                                    @if($eventGroup->share_requests && $eventGroup->share_requests->count() > 0)
                                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-4">Ticket Share Requests</h4>
                                        <div class="space-y-3">
                                            @foreach($eventGroup->share_requests as $request)
                                            <div id="share-request-{{ $request->id }}" data-event-id="{{ $eventGroup->event->id }}" class="flex items-center justify-between p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                                <div class="flex items-center space-x-3">
                                                    <div class="w-8 h-8 bg-yellow-500 text-white text-sm font-medium rounded-full flex items-center justify-center">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                            {{ $request->sharer->full_name }} wants to share 1 ticket
                                                        </div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                                            {{ $request->created_at->diffForHumans() }}
                                                        </div>
                                                        @if($request->message)
                                                        <div class="text-xs text-gray-600 dark:text-gray-300 mt-1">
                                                            "{{ $request->message }}"
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <button onclick="respondToShareRequest({{ $request->id }}, 'accept')" 
                                                            class="inline-flex items-center px-3 py-1 bg-green-500 hover:bg-green-600 text-white text-xs font-medium rounded transition-colors duration-200">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                        Accept
                                                    </button>
                                                    <button onclick="respondToShareRequest({{ $request->id }}, 'decline')" 
                                                            class="inline-flex items-center px-3 py-1 bg-red-500 hover:bg-red-600 text-white text-xs font-medium rounded transition-colors duration-200">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                        Decline
                                                    </button>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No tickets found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">You haven't purchased any tickets yet.</p>
                    <div class="mt-6">
                        <a href="{{ route('events.browse') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-qricket-600 hover:bg-qricket-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-qricket-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Browse Events
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Include Share Tickets Modal Component -->
    @include('events.tickets.components.share-tickets-modal')

    <!-- Include SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/js/events/my-purchases.js"></script>
</x-app-layout>