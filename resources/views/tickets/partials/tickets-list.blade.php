@foreach($ticketsByEvent as $eventId => $tickets)
    @php
        $event = $tickets->first()->event;
        $eventEnded = $event->end_date && now()->gt($event->end_date);
        $allTicketsShared = $tickets->every(function($ticket) {
            return $ticket->shared_by_user_id == auth()->id() && is_null($ticket->checked_in_at);
        });
        $hasAvailableTickets = $tickets->contains(function($ticket) {
            return $ticket->checked_in_at === null && 
                   (is_null($ticket->shared_by_user_id) || $ticket->shared_by_user_id != auth()->id()) &&
                   (!$ticket->shares || $ticket->shares->where('status', 'pending')->count() === 0);
        });
    @endphp

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6 overflow-hidden">
        <!-- Event Header with Thumbnail Background -->
        <div class="relative p-6 border-b border-gray-200 dark:border-gray-700 overflow-hidden" 
             @if($event->thumbnail_url) 
             style="background-size: cover; background-position: center;"
             @endif>
            @if($event->thumbnail_url)
                <!-- Black and white grayscale thumbnail background -->
                <div class="absolute inset-0 bg-cover bg-center" 
                     style="background-image: url('{{ $event->thumbnail_url }}'); filter: grayscale(100%) contrast(120%) brightness(70%); z-index: 0;"></div>
                <!-- Dark overlay for better text contrast -->
                <div class="absolute inset-0 bg-gradient-to-b from-black/70 via-black/60 to-black/70 z-0"></div>
            @endif
            <div class="flex items-center justify-between relative z-10">
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-semibold {{ $event->thumbnail_url ? 'text-white drop-shadow-lg' : 'text-gray-900 dark:text-white' }} truncate">{{ $event->title }}</h3>
                    <div class="mt-2 flex items-center text-sm {{ $event->thumbnail_url ? 'text-gray-100 drop-shadow-md' : 'text-gray-500 dark:text-gray-400' }}">
                        <svg class="flex-shrink-0 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>{{ \Carbon\Carbon::parse($event->start_date)->format('M d, Y \a\t g:i A') }} - {{ \Carbon\Carbon::parse($event->end_date)->format('M d, Y \a\t g:i A') }}</span>
                    </div>
                    <div class="mt-1 flex items-center text-sm {{ $event->thumbnail_url ? 'text-gray-100 drop-shadow-md' : 'text-gray-500 dark:text-gray-400' }}">
                        <svg class="flex-shrink-0 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span>{{ $event->location }}</span>
                    </div>
                </div>
                <div class="ml-4 flex-shrink-0 relative z-10">
                    @if(!$eventEnded && $hasAvailableTickets && ($eventSharingSettings[$eventId] ?? false))
                        <button onclick="openShareModal({{ $eventId }})" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-colors shadow-lg">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                            </svg>
                            Share Tickets
                        </button>
                    @elseif($eventEnded)
                        <span class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700">
                            Event Ended
                        </span>
                    @elseif($allTicketsShared)
                        <span class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700">
                            All Tickets Shared
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tickets by Type - Collapsible -->
        <div class="p-4">
            @foreach($tickets->groupBy('ticket_type_id') as $ticketTypeId => $ticketsOfType)
                @php
                    $ticketType = $ticketsOfType->first()->ticketType;
                    $availableTickets = $ticketsOfType->filter(function($ticket) {
                        return is_null($ticket->checked_in_at) && 
                               (is_null($ticket->shared_by_user_id) || $ticket->shared_by_user_id != auth()->id()) &&
                               (!$ticket->shares || $ticket->shares->where('status', 'pending')->count() === 0);
                    });
                    $sharedTickets = $ticketsOfType->filter(function($ticket) {
                        return $ticket->shared_by_user_id == auth()->id() && is_null($ticket->checked_in_at);
                    });
                    $receivedTickets = $ticketsOfType->filter(function($ticket) {
                        return $ticket->shared_by_user_id && $ticket->shared_by_user_id != auth()->id();
                    });
                    $pendingShares = $ticketsOfType->filter(function($ticket) {
                        return is_null($ticket->checked_in_at) && 
                               $ticket->shares && 
                               $ticket->shares->where('status', 'pending')->count() > 0;
                    });
                @endphp

                <div class="mb-4 last:mb-0">
                    <!-- Collapsible Ticket Type Header -->
                    <button onclick="toggleTicketType({{ $ticketTypeId }})" 
                            class="w-full flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $ticketType->name }}</h4>
                                <div class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    <span>₱{{ number_format($ticketType->price) }}</span>
                                    <span>•</span>
                                    <span>{{ $availableTickets->count() }} available</span>
                                    @if($sharedTickets->count() > 0)
                                        <span class="text-orange-600 dark:text-orange-400">• {{ $sharedTickets->count() }} shared</span>
                                    @endif
                                    @if($receivedTickets->count() > 0)
                                        <span class="text-blue-600 dark:text-blue-400">• {{ $receivedTickets->count() }} received</span>
                                    @endif
                                    @if($pendingShares->count() > 0)
                                        <span class="text-yellow-600 dark:text-yellow-400">• {{ $pendingShares->count() }} pending</span>
                                    @endif
                                    @php
                                        $checkedInTickets = $ticketsOfType->where('checked_in_at', '!=', null)->count();
                                    @endphp
                                    @if($checkedInTickets > 0)
                                        <span class="text-green-600 dark:text-green-400">• {{ $checkedInTickets }} checked in</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $ticketsOfType->count() }} tickets</span>
                            <svg id="chevron-{{ $ticketTypeId }}" class="w-4 h-4 text-gray-400 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>

                    <!-- Collapsible Ticket List -->
                    <div id="tickets-{{ $ticketTypeId }}" class="hidden mt-2 space-y-2">

                        <!-- Individual Tickets - Compact Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                            @foreach($ticketsOfType as $ticket)
                                @php
                                    $isCheckedIn = $ticket->checked_in_at !== null;
                                    $pendingShares = $ticket->shares ? $ticket->shares->where('status', 'pending') : collect();
                                    $hasPendingShares = $pendingShares->count() > 0;
                                    $isSharedToOthers = $ticket->shared_by_user_id && $ticket->shared_by_user_id == auth()->id();
                                    $wasReceivedThroughShare = $ticket->shared_by_user_id && $ticket->shared_by_user_id != auth()->id(); // Show even if checked in
                                    $sharedFromName = $ticket->sharedByUser ? ($ticket->sharedByUser->first_name . ' ' . $ticket->sharedByUser->last_name) : 'Someone';
                                    
                                    // Get pending share details
                                    $pendingShareSent = null; // Share sent by current user (outgoing)
                                    $pendingShareReceived = null; // Share received by current user (incoming)
                                    
                                    if ($hasPendingShares) {
                                        foreach ($pendingShares as $share) {
                                            // If sharer is current user, this is a pending share we sent
                                            if ($share->sharer_id == auth()->id()) {
                                                $pendingShareSent = $share;
                                            }
                                            // If recipient is current user, this is a pending share we received
                                            if ($share->recipient_id == auth()->id()) {
                                                $pendingShareReceived = $share;
                                            }
                                        }
                                    }
                                    
                                    // Find who this ticket was sent to (if shared by current user and accepted)
                                    $sentToRecipient = null;
                                    if ($isSharedToOthers) {
                                        // Find accepted share for this ticket - check by shared_ticket_id or shared_ticket_ids
                                        $acceptedShare = \App\Models\EventTicketShare::where(function($q) use ($ticket) {
                                                $q->where('shared_ticket_id', $ticket->id)
                                                  ->orWhereJsonContains('shared_ticket_ids', $ticket->id);
                                            })
                                            ->where('status', 'accepted')
                                            ->with('recipient:id,first_name,last_name')
                                            ->first();
                                        if ($acceptedShare && $acceptedShare->recipient) {
                                            $sentToRecipient = $acceptedShare->recipient->first_name . ' ' . $acceptedShare->recipient->last_name;
                                        } elseif ($ticket->user && $ticket->user->id != auth()->id()) {
                                            // If user_id changed, the current owner is the recipient
                                            $sentToRecipient = $ticket->user->first_name . ' ' . $ticket->user->last_name;
                                        }
                                    }
                                    
                                    $canShare = !$eventEnded && !$isCheckedIn && !$hasPendingShares && !$isSharedToOthers;
                                @endphp

                                <div class="flex items-center justify-between p-2 border border-gray-200 dark:border-gray-600 rounded-md {{ $wasReceivedThroughShare ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800' : ($isSharedToOthers ? 'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800' : ($isCheckedIn ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : ($eventEnded ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' : ($hasPendingShares ? 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800' : 'bg-gray-50 dark:bg-gray-700')))) }}">
                                    <div class="flex-1 min-w-0">
                                        <div class="text-xs font-medium text-gray-900 dark:text-gray-100 truncate">
                                            {{ $ticket->ticket_id }}
                                        </div>
                                        @if($wasReceivedThroughShare)
                                            <div class="text-xs text-blue-600 dark:text-blue-400 truncate font-medium">
                                                Received from {{ $sharedFromName }}
                                            </div>
                                            @if($isCheckedIn)
                                                <div class="text-xs text-green-600 dark:text-green-400 truncate mt-0.5">
                                                    Checked In
                                                </div>
                                            @endif
                                        @elseif($isSharedToOthers)
                                            @if($sentToRecipient)
                                                <div class="text-xs text-orange-600 dark:text-orange-400 truncate font-medium">
                                                    Sent to {{ $sentToRecipient }}
                                                </div>
                                            @else
                                                <div class="text-xs text-orange-600 dark:text-orange-400 truncate">
                                                    Shared to recipients
                                                </div>
                                            @endif
                                            @if($isCheckedIn)
                                                <div class="text-xs text-green-600 dark:text-green-400 truncate mt-0.5">
                                                    Checked In
                                                </div>
                                            @endif
                                        @elseif($isCheckedIn)
                                            <div class="text-xs text-green-600 dark:text-green-400 truncate">
                                                Checked In
                                            </div>
                                        @elseif($eventEnded)
                                            <div class="text-xs text-red-600 dark:text-red-400 truncate">
                                                Event Ended
                                            </div>
                                        @elseif($hasPendingShares)
                                            @if($pendingShareSent && $pendingShareSent->recipient)
                                                <div class="text-xs text-yellow-600 dark:text-yellow-400 truncate font-medium">
                                                    Pending to {{ $pendingShareSent->recipient->first_name }} {{ $pendingShareSent->recipient->last_name }}
                                                </div>
                                            @elseif($pendingShareReceived && $pendingShareReceived->sharer)
                                                <div class="text-xs text-yellow-600 dark:text-yellow-400 truncate font-medium">
                                                    Pending from {{ $pendingShareReceived->sharer->first_name }} {{ $pendingShareReceived->sharer->last_name }}
                                                </div>
                                            @else
                                                <div class="text-xs text-yellow-600 dark:text-yellow-400 truncate">
                                                    Pending Share Request
                                                </div>
                                            @endif
                                        @else
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Available</div>
                                        @endif
                                    </div>
                                    <div class="flex items-center space-x-1 ml-2">
                                        @if($wasReceivedThroughShare || $isSharedToOthers)
                                            @if($isCheckedIn)
                                                <span class="text-xs px-2 py-1 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-md">
                                                    Checked
                                                </span>
                                            @elseif($eventEnded)
                                                <span class="text-xs px-2 py-1 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded-md">
                                                    Ended
                                                </span>
                                            @endif
                                        @elseif($canShare)
                                            <button onclick="viewTicketDetails({{ $ticket->id }})" 
                                                    class="text-xs px-2 py-1 bg-teal-600 hover:bg-teal-700 text-white rounded-md transition-colors">
                                                View
                                            </button>
                                        @elseif($eventEnded)
                                            <span class="text-xs px-2 py-1 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded-md">
                                                Ended
                                            </span>
                                        @elseif($isCheckedIn)
                                            <span class="text-xs px-2 py-1 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-md">
                                                Checked
                                            </span>
                                        @elseif($hasPendingShares)
                                            @if($pendingShareSent)
                                                <span class="text-xs px-2 py-1 bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300 rounded-md" title="Pending share sent">
                                                    Pending Out
                                                </span>
                                            @elseif($pendingShareReceived)
                                                <span class="text-xs px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded-md" title="Pending share received">
                                                    Pending In
                                                </span>
                                            @else
                                                <span class="text-xs px-2 py-1 bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300 rounded-md">
                                                    Pending
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endforeach

<!-- JavaScript for Collapsible Functionality -->
<script>
function toggleTicketType(ticketTypeId) {
    const ticketsContainer = document.getElementById(`tickets-${ticketTypeId}`);
    const chevron = document.getElementById(`chevron-${ticketTypeId}`);
    
    if (ticketsContainer.classList.contains('hidden')) {
        ticketsContainer.classList.remove('hidden');
        chevron.style.transform = 'rotate(180deg)';
    } else {
        ticketsContainer.classList.add('hidden');
        chevron.style.transform = 'rotate(0deg)';
    }
}

// All ticket types start collapsed - no auto-expand on load
</script>

