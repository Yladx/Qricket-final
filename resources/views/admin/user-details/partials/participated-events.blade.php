@if($participatedEvents->count())
    <div class="space-y-4">
        @foreach($participatedEvents as $event)
            @php
                // Get all tickets for this user and event, but exclude tickets that have been shared/given away
                $userTickets = \App\Models\TicketInformation::where('event_id', $event->id)
                    ->whereHas('ticketPurchase', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    })
                    ->where('user_id', $user->id) // Only show tickets that still belong to this user
                    ->with('ticketPurchase')
                    ->get();
            @endphp
            
            @if($userTickets->count() > 0)
                <!-- Event Group -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                    <!-- Event Header -->
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-qricket-100 dark:bg-qricket-900 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-qricket-600 dark:text-qricket-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $event->title }}</h3>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ optional($event->start_date)->format('M j, Y') }} • {{ ucfirst($event->status) }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-600 dark:text-gray-400">{{ $userTickets->count() }} ticket{{ $userTickets->count() !== 1 ? 's' : '' }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-500">
                                    {{ $userTickets->where('checked_in_at', '!=', null)->count() }} attended
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tickets Table -->
                    <div class="overflow-x-auto participated-events-scroll">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ticket ID</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Check In</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Invoice</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($userTickets as $ticket)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-4 py-2 text-xs font-mono text-gray-900 dark:text-gray-100">#{{ $ticket->ticket_id }}</td>
                                        <td class="px-4 py-2">
                                            @if($ticket->checked_in_at)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Attended
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Not Attended
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-xs text-gray-600 dark:text-gray-400">
                                            @if($ticket->checked_in_at)
                                                {{ $ticket->checked_in_at->format('M j, Y g:i A') }}
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500">Not checked in</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">
                                            @if($ticket->ticketPurchase->status === 'paid')
                                                <a href="{{ route('tickets.invoice', ['purchaseId' => $ticket->ticketPurchase->purchase_id ?? $ticket->ticketPurchase->id, 'ticketId' => $ticket->id]) }}" 
                                                   class="text-xs font-mono text-qricket-600 dark:text-qricket-400 hover:text-qricket-800 dark:hover:text-qricket-300 hover:underline">
                                                    #{{ $ticket->ticketPurchase->purchase_id ?? $ticket->ticketPurchase->id }}
                                                </a>
                                            @else
                                                <span class="text-xs text-gray-400 dark:text-gray-500">No Invoice</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
    
    <!-- Summary Statistics -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $participatedEvents->count() }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Events Participated</div>
            </div>
        </div>
        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
            <div class="text-center">
                @php
                    $totalTickets = \App\Models\TicketInformation::whereHas('ticketPurchase', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    })->count();
                @endphp
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $totalTickets }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Total Tickets</div>
            </div>
        </div>
        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
            <div class="text-center">
                @php
                    $attendedTickets = \App\Models\TicketInformation::whereHas('ticketPurchase', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    })->whereNotNull('checked_in_at')->count();
                @endphp
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $attendedTickets }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Tickets Used</div>
            </div>
        </div>
        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
            <div class="text-center">
                @php
                    $paidTickets = \App\Models\TicketInformation::whereHas('ticketPurchase', function($q) use ($user) {
                        $q->where('user_id', $user->id)->where('status', 'paid');
                    })->count();
                @endphp
                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $paidTickets }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Paid Tickets</div>
            </div>
        </div>
    </div>
@else
    <div class="text-center py-8">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No participated events</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This user hasn't purchased tickets for any events yet.</p>
    </div>
@endif

<style>
/* Custom Scrollbar Styling */
.participated-events-scroll {
    scrollbar-width: thin;
    scrollbar-color: #4a5568 #1a202c;
}

.participated-events-scroll::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.participated-events-scroll::-webkit-scrollbar-track {
    background: #1a202c;
    border-radius: 4px;
}

.participated-events-scroll::-webkit-scrollbar-thumb {
    background: #4a5568;
    border-radius: 4px;
}

.participated-events-scroll::-webkit-scrollbar-thumb:hover {
    background: #718096;
}

/* Dark mode scrollbar */
.dark .participated-events-scroll {
    scrollbar-color: #6b7280 #111827;
}

.dark .participated-events-scroll::-webkit-scrollbar-track {
    background: #111827;
}

.dark .participated-events-scroll::-webkit-scrollbar-thumb {
    background: #6b7280;
}

.dark .participated-events-scroll::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}
</style>

