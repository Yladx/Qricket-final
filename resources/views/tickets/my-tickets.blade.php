@extends('layouts.app')

@section('title', 'My Tickets')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-4 sm:mb-6">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">My Tickets</h1>
            <p class="mt-1 text-xs sm:text-sm text-gray-600 dark:text-gray-400">Manage and share your event tickets</p>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3 sm:p-4 mb-4 sm:mb-6">
            <div class="flex flex-col sm:flex-row sm:flex-wrap items-start sm:items-center gap-3">
                <div class="flex items-center w-full sm:w-auto">
                    <input type="checkbox" id="hide_shared" name="hide_shared" 
                           class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-gray-300 rounded flex-shrink-0"
                           {{ $hideShared ? 'checked' : '' }}>
                    <label for="hide_shared" class="ml-2 text-xs text-gray-700 dark:text-gray-300">
                        Hide shared tickets
                    </label>
                </div>
                
                <div class="flex items-center w-full sm:w-auto">
                    <input type="checkbox" id="hide_past_events" name="hide_past_events" 
                           class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-gray-300 rounded flex-shrink-0"
                           {{ $hidePastEvents ? 'checked' : '' }}>
                    <label for="hide_past_events" class="ml-2 text-xs text-gray-700 dark:text-gray-300">
                        Hide past events
                    </label>
                </div>
                
                <div class="flex items-center w-full sm:w-auto flex-1 sm:flex-none">
                    <label for="sort_by" class="text-xs text-gray-700 dark:text-gray-300 mr-2 whitespace-nowrap">Sort by:</label>
                    <select id="sort_by" name="sort_by" 
                            class="border border-gray-300 dark:border-gray-600 rounded-md px-2 py-1.5 text-xs bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-full sm:w-auto min-w-[160px]">
                        <option value="date_desc" {{ $sortBy === 'date_desc' ? 'selected' : '' }}>Date (Newest First)</option>
                        <option value="date_asc" {{ $sortBy === 'date_asc' ? 'selected' : '' }}>Date (Oldest First)</option>
                        <option value="title_asc" {{ $sortBy === 'title_asc' ? 'selected' : '' }}>Title (A-Z)</option>
                        <option value="title_desc" {{ $sortBy === 'title_desc' ? 'selected' : '' }}>Title (Z-A)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        @if(session('success'))
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-8">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700 dark:text-green-300">{{ session('success') }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Error Message -->
        @if(isset($error))
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-8">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700 dark:text-red-300">{{ $error }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Main Content with Sidebar -->
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Main Content -->
            <div class="flex-1">
                <!-- Tickets List -->
                <div id="tickets-container">
                    @include('tickets.partials.tickets-list', ['ticketsByEvent' => $ticketsByEvent, 'eventSharingSettings' => $eventSharingSettings ?? []])
                </div>

                <!-- Empty State -->
                @if($ticketsByEvent->isEmpty())
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No tickets found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if($hideShared || $hidePastEvents)
                            Try adjusting your filters to see more tickets.
                        @else
                            You don't have any tickets yet. Purchase tickets for events to see them here.
                        @endif
                    </p>
                </div>
                @endif
            </div>

            <!-- Right Sidebar: Pending Ticket Shares -->
            @if($pendingSharesReceived->isNotEmpty() || $pendingSharesSent->isNotEmpty())
            <div class="w-full lg:w-80 flex-shrink-0">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 sticky top-6">
                    <div class="p-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Pending Ticket Shares</h2>
                        
                        <!-- Shares Received -->
                        @if($pendingSharesReceived->isNotEmpty())
                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Shares Received</h3>
                            <div class="space-y-3 max-h-96 overflow-y-auto">
                                @foreach($pendingSharesReceived as $eventId => $shares)
                                    @php $event = $shares->first()->event; @endphp
                                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-3">
                                        <div class="mb-2">
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ $event->title }}</h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ \Carbon\Carbon::parse($event->start_date)->format('M j, Y \a\t g:i A') }}
                                            </p>
                                        </div>
                                        
                                        <div class="space-y-2">
                                            @foreach($shares as $share)
                                            <div class="p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                                <div class="flex items-center space-x-2 mb-2">
                                                    <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                        <span class="text-white text-xs font-medium">
                                                            {{ substr($share->sharer->first_name, 0, 1) }}{{ substr($share->sharer->last_name, 0, 1) }}
                                                        </span>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-xs font-medium text-gray-900 dark:text-white truncate">
                                                            {{ $share->sharer->first_name }} {{ $share->sharer->last_name }}
                                                        </p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                                            {{ $share->ticketType->name }} • {{ $share->quantity }} ticket(s)
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="flex space-x-1 mt-2">
                                                    @if($share->canBeAccepted())
                                                        <form method="POST" action="{{ route('ticket-shares.accept', $share->id) }}" class="inline flex-1">
                                                            @csrf
                                                            <button type="submit" 
                                                                    class="w-full px-2 py-1 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 transition-colors">
                                                                Accept
                                                            </button>
                                                        </form>
                                                        <form method="POST" action="{{ route('ticket-shares.decline', $share->id) }}" class="inline flex-1">
                                                            @csrf
                                                            <button type="submit" 
                                                                    class="w-full px-2 py-1 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 transition-colors">
                                                                Decline
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="w-full px-2 py-1 bg-gray-500 text-white text-xs font-medium rounded text-center">
                                                            @if($share->status !== 'pending')
                                                                {{ ucfirst($share->status) }}
                                                            @elseif($share->isExpired())
                                                                Expired
                                                            @else
                                                                Unavailable
                                                            @endif
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Shares Sent -->
                        @if($pendingSharesSent->isNotEmpty())
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Shares Sent</h3>
                            <div class="space-y-3 max-h-96 overflow-y-auto">
                                @foreach($pendingSharesSent as $eventId => $shares)
                                    @php $event = $shares->first()->event; @endphp
                                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-3">
                                        <div class="mb-2">
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ $event->title }}</h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ \Carbon\Carbon::parse($event->start_date)->format('M j, Y \a\t g:i A') }}
                                            </p>
                                        </div>
                                        
                                        <div class="space-y-2">
                                            @foreach($shares as $share)
                                            <div class="p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                                <div class="flex items-center space-x-2 mb-2">
                                                    <div class="w-6 h-6 bg-teal-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                        <span class="text-white text-xs font-medium">
                                                            {{ substr($share->recipient->first_name, 0, 1) }}{{ substr($share->recipient->last_name, 0, 1) }}
                                                        </span>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-xs font-medium text-gray-900 dark:text-white truncate">
                                                            {{ $share->recipient->first_name }} {{ $share->recipient->last_name }}
                                                        </p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                                            {{ $share->ticketType->name }} • {{ $share->quantity }} ticket(s)
                                                        </p>
                                                        <p class="text-xs text-yellow-600 dark:text-yellow-400">
                                                            Expires: {{ \Carbon\Carbon::parse($share->expires_at)->format('M j, Y \a\t g:i A') }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="mt-2">
                                                    <form method="POST" action="{{ route('ticket-shares.cancel', $share->id) }}" class="inline w-full">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="w-full px-2 py-1 bg-gray-600 text-white text-xs font-medium rounded hover:bg-gray-700 transition-colors">
                                                            Cancel
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Share Tickets Modal -->
<div id="shareModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-0 border-0 w-full max-w-md shadow-2xl rounded-xl bg-white dark:bg-gray-800">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-teal-500 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Share Tickets</h3>
                </div>
                <button onclick="closeShareModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div id="shareModalContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
let currentEventId = null;
let currentTicketTypeId = null;
let currentMaxQuantity = 0;
let selectedRecipients = [];

// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const hideSharedCheckbox = document.getElementById('hide_shared');
    const hidePastEventsCheckbox = document.getElementById('hide_past_events');
    const sortSelect = document.getElementById('sort_by');

    function applyFilters() {
        const hideShared = hideSharedCheckbox.checked;
        const hidePastEvents = hidePastEventsCheckbox.checked;
        const sortBy = sortSelect.value;

        fetch('/my-tickets/filter', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                hide_shared: hideShared,
                hide_past_events: hidePastEvents,
                sort_by: sortBy
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('tickets-container').innerHTML = data.html;
            } else {
                console.error('Failed to load tickets:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    hideSharedCheckbox.addEventListener('change', applyFilters);
    hidePastEventsCheckbox.addEventListener('change', applyFilters);
    sortSelect.addEventListener('change', applyFilters);
});

// Share modal functionality
function openShareModal(eventId) {
    currentEventId = eventId;
    selectedRecipients = [];
    
    fetch(`/my-tickets/event-tickets?event_id=${eventId}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Debug: Log the received data
            console.log('Share modal data received:', {
                event: data.event,
                tickets_by_type: data.tickets_by_type,
                tickets_by_type_keys: Object.keys(data.tickets_by_type || {})
            });
            // Log each ticket type's data
            Object.values(data.tickets_by_type || {}).forEach((ticketTypeData, index) => {
                console.log(`Ticket type ${index}:`, {
                    name: ticketTypeData.ticket_type?.name,
                    available_count: ticketTypeData.available_count,
                    pending_count: ticketTypeData.pending_count,
                    pending_recipients: ticketTypeData.pending_recipients,
                    total_count: ticketTypeData.total_count
                });
            });
            
            renderShareModalContent(data.event, data.tickets_by_type);
            document.getElementById('shareModal').classList.remove('hidden');
        } else {
            console.error('Failed to load tickets:', data.message);
            alert('Failed to load tickets for sharing');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading tickets');
    });
}

function closeShareModal() {
    document.getElementById('shareModal').classList.add('hidden');
    currentEventId = null;
    currentTicketTypeId = null;
    currentMaxQuantity = 0;
    selectedRecipients = [];
}

function renderShareModalContent(event, ticketsByType) {
    const content = document.getElementById('shareModalContent');
    
    // Check if event has ended
    const eventEndDate = new Date(event.end_date);
    const now = new Date();
    const hasEnded = eventEndDate < now;
    
    // Calculate total available and pending tickets
    let totalAvailableTickets = 0;
    let totalPendingTickets = 0;
    Object.values(ticketsByType).forEach(ticketTypeData => {
        totalAvailableTickets += ticketTypeData.available_count || 0;
        totalPendingTickets += ticketTypeData.pending_count || 0;
    });
    
    let html = `
        <!-- Information Box -->
        <div class="mb-6 p-4 bg-teal-50 dark:bg-teal-900/20 border border-teal-200 dark:border-teal-800 rounded-lg">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-teal-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-teal-700 dark:text-teal-300 font-medium">Each selected user will receive 1 ticket from your available tickets.</p>
                </div>
            </div>
            <div class="flex items-center mt-2">
                <div class="flex-shrink-0">
                    ${totalAvailableTickets > 0 ? 
                        `<svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>` :
                        totalPendingTickets > 0 ?
                            `<svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>` :
                            `<svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>`
                    }
                </div>
                <div class="ml-3">
                    ${totalAvailableTickets > 0 && totalPendingTickets > 0 ? 
                        `<p class="text-sm text-green-700 dark:text-green-300 font-medium">Available: ${totalAvailableTickets} ticket${totalAvailableTickets > 1 ? 's' : ''}</p><p class="text-sm text-yellow-700 dark:text-yellow-300 font-medium mt-1">Pending: ${totalPendingTickets} ticket${totalPendingTickets > 1 ? 's' : ''}</p>` :
                        totalAvailableTickets > 0 ?
                            `<p class="text-sm text-green-700 dark:text-green-300 font-medium">Available: ${totalAvailableTickets} ticket${totalAvailableTickets > 1 ? 's' : ''}</p>` :
                            totalPendingTickets > 0 ?
                                `<p class="text-sm text-yellow-700 dark:text-yellow-300 font-medium">${totalPendingTickets} ticket${totalPendingTickets > 1 ? 's' : ''} pending (none available for new shares)</p>` :
                                `<p class="text-sm text-gray-700 dark:text-gray-300 font-medium">No tickets available</p>`
                    }
                </div>
            </div>
            ${hasEnded ? '<div class="mt-3 p-2 bg-red-100 dark:bg-red-900 border border-red-300 dark:border-red-700 rounded-md"><p class="text-xs text-red-700 dark:text-red-300">⚠️ This event has ended. Ticket sharing is no longer available.</p></div>' : ''}
        </div>
        
        <form id="shareTicketsForm" class="space-y-6">
            <input type="hidden" name="event_id" value="${event.id}">
            
            <!-- Ticket Type Selection -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-100 mb-3">Select Ticket Type</label>
                <div class="space-y-3">
    `;
    
    Object.values(ticketsByType).forEach(ticketTypeData => {
        const ticketType = ticketTypeData.ticket_type;
        const availableCount = ticketTypeData.available_count || 0;
        const pendingCount = ticketTypeData.pending_count || 0;
        const pendingRecipients = ticketTypeData.pending_recipients || [];
        
        // Only show ticket types that have at least 1 available ticket
        if (availableCount > 0) {
            html += `
                <label class="flex items-center p-4 border border-gray-300 dark:border-gray-500 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors bg-white dark:bg-gray-800">
                    <input type="radio" name="ticket_type_id" value="${ticketType.id}" 
                           class="text-teal-500 focus:ring-teal-500 focus:ring-2" 
                           onchange="onTicketTypeChange(${ticketType.id}, ${availableCount})">
                    <div class="ml-4 flex-1">
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">${ticketType.name}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            ₱${ticketType.price} each
                            ${availableCount > 0 && pendingCount > 0 ? 
                                `<span class="text-green-600 dark:text-green-400">• ${availableCount} available</span><span class="text-yellow-600 dark:text-yellow-400 ml-2">• ${pendingCount} pending</span>` :
                                availableCount > 0 ? 
                                    `<span class="text-green-600 dark:text-green-400">• ${availableCount} available</span>` :
                                    pendingCount > 0 ?
                                        `<span class="text-yellow-600 dark:text-yellow-400">• ${pendingCount} pending (none available)</span>` :
                                        ''
                            }
                        </div>
                        ${pendingCount > 0 && pendingRecipients.length > 0 ? `
                            <div class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                                Pending to: ${pendingRecipients.join(', ')}
                            </div>
                        ` : ''}
                    </div>
                </label>
            `;
        } else if (pendingCount > 0) {
            // Show ticket types with only pending shares (but disabled)
            html += `
                <div class="flex items-center p-4 border border-yellow-300 dark:border-yellow-600 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 opacity-60 cursor-not-allowed">
                    <input type="radio" name="ticket_type_id" value="${ticketType.id}" disabled
                           class="text-gray-400 cursor-not-allowed">
                    <div class="ml-4 flex-1">
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">${ticketType.name}</div>
                        <div class="text-xs text-yellow-600 dark:text-yellow-400">
                            ₱${ticketType.price} each • <span class="font-medium">${pendingCount} pending</span> (none available for new shares)
                        </div>
                        ${pendingRecipients.length > 0 ? `
                            <div class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                                Pending to: ${pendingRecipients.join(', ')}
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        }
    });
        
    html += `
                </div>
            </div>
            
            <!-- Recipient Search -->
            <div>
                <label for="recipient_search" class="block text-sm font-semibold text-gray-700 dark:text-gray-100 mb-3">Search users to share with</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" id="recipient_search" placeholder="Search by name or email..."
                           class="block w-full pl-10 pr-3 py-3 bg-white text-gray-900 border border-gray-300 dark:border-gray-500 dark:bg-gray-700 dark:text-gray-100 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                           onkeyup="searchRecipients(this.value)"
                           onfocus="showSearchResults()"
                           onblur="hideSearchResults()">
                    <div id="search_results" class="absolute z-10 w-full mt-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-500 rounded-lg shadow-xl hidden max-h-60 overflow-y-auto"></div>
                </div>
            </div>
            
            <!-- Selected Recipients -->
            <div id="selected_recipients" class="space-y-3 hidden">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-100">Selected Recipients</label>
                <div id="recipients_list" class="space-y-2"></div>
            </div>
            
            <!-- Auto quantity info -->
            <div id="quantity_info" class="hidden">
                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <p class="text-sm text-blue-700 dark:text-blue-300 font-medium">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span id="quantity_message">1 ticket will be shared per recipient</span>
                    </p>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 pt-6">
                <button type="button" onclick="closeShareModal()" 
                        class="px-6 py-3 border border-gray-300 dark:border-gray-500 text-sm font-semibold rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-transparent hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Cancel
                </button>
                <button type="submit" id="share_button" disabled
                        class="px-6 py-3 bg-green-500 text-sm font-semibold rounded-lg text-white hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    Share Tickets
                </button>
            </div>
        </form>
    `;
    
    content.innerHTML = html;
    
    // Add form submission handler
    document.getElementById('shareTicketsForm').addEventListener('submit', handleShareSubmit);
}

function onTicketTypeChange(ticketTypeId, maxQuantity) {
    currentTicketTypeId = ticketTypeId;
    currentMaxQuantity = maxQuantity;
    updateShareButton();
}

function searchRecipients(query) {
    console.log('Searching for:', query);
    
    if (query.length < 2) {
        hideSearchResults();
        return;
    }
    
    fetch('/my-tickets/search-users', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ q: query })
    })
    .then(response => {
        console.log('Search response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Search response data:', data);
        if (data.success) {
            displaySearchResults(data.users);
        } else {
            console.error('Search failed:', data.message);
        }
    })
    .catch(error => {
        console.error('Search error:', error);
    });
}

function displaySearchResults(users) {
    const resultsDiv = document.getElementById('search_results');
    
    if (users.length === 0) {
        resultsDiv.innerHTML = '<div class="p-4 text-sm text-gray-600 dark:text-gray-400 text-center">No users found</div>';
    } else {
        resultsDiv.innerHTML = users.map(user => `
            <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer border-b border-gray-200 dark:border-gray-500 last:border-b-0 transition-colors"
                 onclick="addRecipient(${user.id}, '${user.first_name}', '${user.last_name}', '${user.email}')">
                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">${user.first_name} ${user.last_name}</div>
                <div class="text-xs text-gray-600 dark:text-gray-400">${user.email}</div>
            </div>
        `).join('');
    }
    
    showSearchResults();
}

function showSearchResults() {
    document.getElementById('search_results').classList.remove('hidden');
}

function hideSearchResults() {
    setTimeout(() => {
        document.getElementById('search_results').classList.add('hidden');
    }, 200);
}

function addRecipient(id, firstName, lastName, email) {
    if (selectedRecipients.find(r => r.id === id)) {
        return; // Already added
    }
    
    if (selectedRecipients.length >= currentMaxQuantity) {
        alert(`You can only share up to ${currentMaxQuantity} tickets`);
        return;
    }
    
    selectedRecipients.push({ id, firstName, lastName, email });
    updateRecipientsList();
    updateShareButton();
    
    // Clear search
    document.getElementById('recipient_search').value = '';
    hideSearchResults();
}

function removeRecipient(id) {
    selectedRecipients = selectedRecipients.filter(r => r.id !== id);
    updateRecipientsList();
    updateShareButton();
}

function updateRecipientsList() {
    const recipientsDiv = document.getElementById('recipients_list');
    const selectedRecipientsDiv = document.getElementById('selected_recipients');
    const quantityInfo = document.getElementById('quantity_info');
    const quantityMessage = document.getElementById('quantity_message');
    
    if (selectedRecipients.length === 0) {
        selectedRecipientsDiv.classList.add('hidden');
        quantityInfo.classList.add('hidden');
        return;
    }
    
    selectedRecipientsDiv.classList.remove('hidden');
    quantityInfo.classList.remove('hidden');
    
    // Update quantity message based on recipients
    const maxQuantity = Math.min(selectedRecipients.length, currentMaxQuantity);
    quantityMessage.textContent = `${maxQuantity} ticket${maxQuantity > 1 ? 's' : ''} will be shared (1 per recipient)`;
    
    recipientsDiv.innerHTML = selectedRecipients.map(recipient => `
        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-500">
            <div>
                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">${recipient.firstName} ${recipient.lastName}</div>
                <div class="text-xs text-gray-600 dark:text-gray-400">${recipient.email}</div>
            </div>
            <button type="button" onclick="removeRecipient(${recipient.id})" 
                    class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-colors p-1 rounded hover:bg-red-50 dark:hover:bg-red-900/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `).join('');
}

function updateShareButton() {
    const shareButton = document.getElementById('share_button');
    
    // Enable button only if ticket type selected and recipients added
    const isEnabled = currentTicketTypeId && selectedRecipients.length > 0 && selectedRecipients.length <= currentMaxQuantity;
    
    shareButton.disabled = !isEnabled;
}

async function handleShareSubmit(e) {
    e.preventDefault();
    
    if (!currentTicketTypeId || selectedRecipients.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Missing Information',
            text: 'Please select a ticket type and add at least one recipient.'
        });
        return;
    }
    
    const quantity = selectedRecipients.length; // Auto-set to 1 per recipient
    
    if (quantity > currentMaxQuantity) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Quantity',
            text: `You can only share up to ${currentMaxQuantity} tickets.`
        });
        return;
    }
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Sharing...';
    
    try {
        const response = await fetch('/my-tickets/share', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                event_id: currentEventId,
                ticket_type_id: currentTicketTypeId,
                quantity: quantity,
                recipients: selectedRecipients.map(r => r.id)
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message
            });
            closeShareModal();
            // Refresh the page to show updated ticket status
            window.location.reload();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to share tickets'
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while sharing tickets'
        });
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
}

// View individual ticket details
function viewTicketDetails(ticketId) {
    window.location.href = `/tickets/details/ticket/${ticketId}`;
}
</script>
@endsection
