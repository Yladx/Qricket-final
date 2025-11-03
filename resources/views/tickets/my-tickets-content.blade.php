@if($ticketsByEvent->isEmpty())
    <!-- Empty State -->
    <div class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2V7a2 2 0 00-2-2H5z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No tickets found</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">You haven't purchased any tickets yet.</p>
        <div class="mt-6">
            <a href="{{ route('events.browse') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Browse Events
            </a>
        </div>
    </div>
@else
    <!-- Events with Tickets -->
    <div class="space-y-6">
        @foreach($ticketsByEvent as $eventId => $tickets)
            @php
                $event = $tickets->first()->event;
                $ticketsByType = $tickets->groupBy('ticket_type_id');
            @endphp
        
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <!-- Event Header with Thumbnail Background -->
                <div class="relative p-4 sm:p-6 border-b border-gray-200 dark:border-gray-700">
                    @if($event->thumbnail_url)
                        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat opacity-10 dark:opacity-5" 
                             style="background-image: url('{{ $event->thumbnail_url }}');"></div>
                        <div class="absolute inset-0 bg-gradient-to-r from-black/20 to-transparent dark:from-black/40"></div>
                    @endif
                    <div class="relative flex items-start justify-between">
                        <div class="flex-1 pr-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ $event->title }}</h3>
                            <div class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                <div class="flex items-center">
                                    <svg class="flex-shrink-0 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    {{ \Carbon\Carbon::parse($event->start_date)->format('M d, Y \a\t g:i A') }}
                                    @if($event->end_date)
                                        - {{ \Carbon\Carbon::parse($event->end_date)->format('M d, Y \a\t g:i A') }}
                                    @endif
                                </div>
                                @if($event->location)
                                    <div class="flex items-center">
                                        <svg class="flex-shrink-0 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        {{ $event->location }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <button onclick="openShareModal({{ $event->id }})" 
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                                </svg>
                                Share Tickets
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tickets by Type -->
                <div class="p-3 sm:p-4">
                    @foreach($ticketsByType as $ticketTypeId => $ticketsOfType)
                        @php
                            $ticketType = $ticketsOfType->first()->ticketType;
                            $availableTickets = $ticketsOfType->whereNull('shared_to_user_id');
                            $sharedTickets = $ticketsOfType->whereNotNull('shared_to_user_id');
                        @endphp
                        
                        @if($ticketType)
                        <div class="mb-3 last:mb-0">
                            <!-- Collapsible Ticket Type Header -->
                            <button onclick="toggleTicketType('{{ $event->id }}_{{ $ticketTypeId }}')" 
                                    class="w-full flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                <div class="flex items-center space-x-3">
                                    <svg id="chevron_{{ $event->id }}_{{ $ticketTypeId }}" class="w-4 h-4 text-gray-500 dark:text-gray-400 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                    <div class="text-left">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $ticketType->name }}</h4>
                                        <div class="flex items-center space-x-3 text-xs text-gray-500 dark:text-gray-400">
                                            <span>₱{{ number_format($ticketType->price) }}</span>
                                            <span>{{ $availableTickets->count() }} available</span>
                                            @if($sharedTickets->count() > 0)
                                                <span class="text-orange-600 dark:text-orange-400">{{ $sharedTickets->count() }} shared</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $ticketsOfType->count() }} tickets
                                </div>
                            </button>
                            
                            <!-- Collapsible Ticket List -->
                            <div id="tickets_{{ $event->id }}_{{ $ticketTypeId }}" class="hidden mt-2">
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2 mx-2">
                                    @foreach($ticketsOfType as $ticket)
                                        <div class="flex items-center justify-between p-3 mx-1 border border-gray-200 dark:border-gray-600 rounded-md {{ $ticket->shared_to_user_id ? 'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800' : 'bg-gray-50 dark:bg-gray-700' }}">
                                            <div class="flex-1 min-w-0">
                                                <div class="text-xs font-medium text-gray-900 dark:text-gray-100 truncate">{{ $ticket->ticket_id }}</div>
                                                @if($ticket->shared_to_user_id)
                                                    <div class="text-xs text-orange-600 dark:text-orange-400 truncate">
                                                        Shared with {{ $ticket->sharedToUser->first_name }} {{ $ticket->sharedToUser->last_name }}
                                                    </div>
                                                @else
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">Available</div>
                                                @endif
                                            </div>
                                            <div class="flex items-center space-x-1 ml-2">
                                                @if(!$ticket->shared_to_user_id)
                                                    <button onclick="viewTicketDetails({{ $ticket->id }})" 
                                                            class="text-xs px-2 py-1 bg-teal-600 hover:bg-teal-700 text-white rounded-md transition-colors">
                                                        View Ticket
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@endif
