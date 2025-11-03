<x-app-layout>
    <x-slot name="title">Share Tickets - {{ $event->title }}</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Share Tickets - ') . $event->title }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <!-- Event Info -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            @if($event->thumbnail_url)
                                <img class="h-16 w-16 rounded-lg object-cover" src="{{ $event->thumbnail_url }}" alt="{{ $event->title }}">
                            @else
                                <div class="h-16 w-16 rounded-lg bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                    <svg class="h-8 w-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $event->title }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ \Carbon\Carbon::parse($event->start_date)->format('M j, Y \a\t g:i A') }}
                                @if($event->location)
                                    • {{ $event->location }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Share Tickets Form -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Share Your Tickets</h3>
                        
                        @if($userTickets->isEmpty())
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No tickets to share</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">You don't have any tickets for this event.</p>
                            </div>
                        @else
                            <form id="shareTicketForm" class="space-y-4">
                                @csrf
                                <input type="hidden" name="event_id" value="{{ $event->id }}">
                                
                                <!-- Ticket Type Selection -->
                                <div>
                                    <label for="ticket_type_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ticket Type</label>
                                    <select id="ticket_type_id" name="ticket_type_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        <option value="">Select a ticket type</option>
                                        @foreach($userTickets as $ticketTypeId => $tickets)
                                            @php
                                                $ticketType = $tickets->first()->ticketPurchase->ticketType;
                                                $availableCount = $tickets->where('shared_to_user_id', null)->count();
                                            @endphp
                                            @if($availableCount > 0)
                                                <option value="{{ $ticketTypeId }}" data-available="{{ $availableCount }}">
                                                    {{ $ticketType->name }} ({{ $availableCount }} available)
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Quantity -->
                                <div>
                                    <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantity</label>
                                    <input type="number" id="quantity" name="quantity" min="1" max="1" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" id="quantity-help">Select a ticket type first</p>
                                </div>

                                <!-- Recipient Email -->
                                <div>
                                    <label for="recipient_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Recipient Email</label>
                                    <input type="email" id="recipient_email" name="recipient_email" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="friend@example.com" required>
                                </div>

                                <!-- Message -->
                                <div>
                                    <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Message (Optional)</label>
                                    <textarea id="message" name="message" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Add a personal message..."></textarea>
                                </div>

                                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Send Share Request
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Pending Shares -->
                <div class="space-y-6">
                    <!-- Received Shares -->
                    @if($pendingShares->isNotEmpty())
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Pending Share Requests</h3>
                                <div class="space-y-4">
                                    @foreach($pendingShares as $share)
                                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0">
                                                            <div class="h-8 w-8 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                                                                <span class="text-sm font-medium text-indigo-600 dark:text-indigo-300">
                                                                    {{ substr($share->sharer->first_name, 0, 1) }}{{ substr($share->sharer->last_name, 0, 1) }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="ml-3">
                                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                {{ $share->sharer->first_name }} {{ $share->sharer->last_name }}
                                                            </p>
                                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                                Wants to share {{ $share->quantity }} {{ $share->ticketType->name }} ticket(s)
                                                            </p>
                                                            @if($share->message)
                                                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">"{{ $share->message }}"</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex space-x-2">
                                                    <button onclick="acceptShare({{ $share->id }})" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                        Accept
                                                    </button>
                                                    <button onclick="declineShare({{ $share->id }})" class="inline-flex items-center px-3 py-1 border border-gray-300 dark:border-gray-600 text-xs font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        Decline
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                                Expires: {{ $share->expires_at->format('M j, Y g:i A') }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Sent Shares -->
                    @if($sentShares->isNotEmpty())
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Your Share Requests</h3>
                                <div class="space-y-4">
                                    @foreach($sentShares as $share)
                                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0">
                                                            <div class="h-8 w-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                                                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                                                    {{ substr($share->recipient->first_name, 0, 1) }}{{ substr($share->recipient->last_name, 0, 1) }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="ml-3">
                                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                {{ $share->recipient->first_name }} {{ $share->recipient->last_name }}
                                                            </p>
                                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                                {{ $share->quantity }} {{ $share->ticketType->name }} ticket(s) - Pending
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button onclick="cancelShare({{ $share->id }})" class="inline-flex items-center px-3 py-1 border border-red-300 dark:border-red-600 text-xs font-medium rounded-md text-red-700 dark:text-red-300 bg-white dark:bg-gray-700 hover:bg-red-50 dark:hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                    Cancel
                                                </button>
                                            </div>
                                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                                Expires: {{ $share->expires_at->format('M j, Y g:i A') }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($pendingShares->isEmpty() && $sentShares->isEmpty())
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No pending shares</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">You don't have any pending ticket share requests.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Include SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Update quantity max when ticket type changes
        document.getElementById('ticket_type_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const availableCount = selectedOption ? parseInt(selectedOption.dataset.available) : 0;
            
            const quantityInput = document.getElementById('quantity');
            const quantityHelp = document.getElementById('quantity-help');
            
            if (availableCount > 0) {
                quantityInput.max = availableCount;
                quantityInput.value = 1;
                quantityHelp.textContent = `You can share up to ${availableCount} tickets of this type`;
            } else {
                quantityInput.max = 1;
                quantityInput.value = '';
                quantityHelp.textContent = 'Select a ticket type first';
            }
        });

        // Handle form submission
        document.getElementById('shareTicketForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';
            
            try {
                const response = await fetch('{{ route("ticket-shares.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(Object.fromEntries(formData))
                });
                
                const data = await response.json();
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Share Request Sent!',
                        text: data.message,
                        timer: 3000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Something went wrong. Please try again.'
                });
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });

        // Accept share
        async function acceptShare(shareId) {
            try {
                const response = await fetch(`/ticket-shares/${shareId}/accept`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Share Accepted!',
                        text: data.message,
                        timer: 3000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Something went wrong. Please try again.'
                });
            }
        }

        // Decline share
        async function declineShare(shareId) {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: 'This will decline the ticket share request.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, decline it!'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/ticket-shares/${shareId}/decline`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Share Declined',
                            text: data.message,
                            timer: 3000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Something went wrong. Please try again.'
                    });
                }
            }
        }

        // Cancel share
        async function cancelShare(shareId) {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: 'This will cancel your ticket share request.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, cancel it!'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/ticket-shares/${shareId}/cancel`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Share Cancelled',
                            text: data.message,
                            timer: 3000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Something went wrong. Please try again.'
                    });
                }
            }
        }
    </script>
</x-app-layout>
