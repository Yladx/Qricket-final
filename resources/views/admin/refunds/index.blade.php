<x-admin-layout title="Refund Management" page-title="Refund Requests">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Refund Management</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Review and process refund requests from users.</p>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    Total: {{ $stats['total'] ?? 0 }} requests
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['total'] ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['pending'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Approved</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['approved'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Processed</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['processed'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-red-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Rejected</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['rejected'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-qricket-500 dark:bg-gray-700 dark:text-white">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="processed" {{ request('status') === 'processed' ? 'selected' : '' }}>Processed</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type</label>
                            <select name="type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-qricket-500 dark:bg-gray-700 dark:text-white">
                                <option value="">All Types</option>
                                <option value="ticket" {{ request('type') === 'ticket' ? 'selected' : '' }}>Ticket</option>
                                <option value="subscription" {{ request('type') === 'subscription' ? 'selected' : '' }}>Subscription</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search User</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-qricket-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full px-4 py-2 bg-qricket-600 hover:bg-qricket-700 text-white rounded-md">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Refund Requests Cards -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Refund Requests</h3>
                </div>
                <div class="p-6">
                    @forelse($refunds as $refund)
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300 border-yellow-200 dark:border-yellow-700',
                                'approved' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 border-green-200 dark:border-green-700',
                                'processed' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 border-purple-200 dark:border-purple-700',
                                'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 border-red-200 dark:border-red-700',
                            ];
                            $typeColors = [
                                'ticket' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                                'subscription' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
                                'additional_ticket' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
                            ];
                        @endphp
                        <div class="mb-6 border {{ $statusColors[$refund->status] ?? 'border-gray-200 dark:border-gray-700' }} rounded-xl bg-white dark:bg-gray-800/50 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                            <div class="p-6">
                                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                                    <!-- Left Section: User & Item Info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start space-x-4">
                                            <!-- User Avatar -->
                                            <div class="flex-shrink-0">
                                                @if($refund->user->profile_picture_url)
                                                    <img src="{{ $refund->user->profile_picture_url }}" 
                                                         alt="{{ $refund->user->full_name }}" 
                                                         class="h-14 w-14 rounded-full object-cover border-2 border-gray-200 dark:border-gray-600"
                                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                @endif
                                                <div class="h-14 w-14 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center border-2 border-gray-200 dark:border-gray-600 {{ $refund->user->profile_picture_url ? 'hidden' : '' }}">
                                                    <svg class="w-7 h-7 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            
                                            <!-- User & Item Details -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center space-x-3 mb-2">
                                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 truncate">
                                                        {{ $refund->user->first_name }} {{ $refund->user->last_name }}
                                                    </h4>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full {{ $typeColors[$refund->type] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-900/40 dark:text-gray-300' }}">
                                                        {{ ucfirst(str_replace('_', ' ', $refund->type)) }}
                                                    </span>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full {{ $statusColors[$refund->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300' }}">
                                                        {{ ucfirst($refund->status ?? 'Unknown') }}
                                                    </span>
                                                </div>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ $refund->user->email }}</p>
                                                
                                                <!-- Item Info -->
                                                <div class="flex items-center space-x-4 text-sm text-gray-700 dark:text-gray-300 mb-3">
                                                    <span class="flex items-center">
                                                        <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                                        </svg>
                                                        @if($refund->type === 'ticket')
                                                            {{ $refund->ticketPurchase->event?->title ?? 'Event #' . $refund->ticketPurchase->event_id }}
                                                        @elseif($refund->type === 'additional_ticket')
                                                            {{ $refund->additionalTicketPurchase->event?->title ?? 'Event #' . $refund->additionalTicketPurchase->event_id }} (Additional Tickets)
                                                        @else
                                                            Organizer Subscription
                                                        @endif
                                                    </span>
                                                    <span class="flex items-center">
                                                        <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                        {{ $refund->created_at->format('M j, Y g:i A') }}
                                                    </span>
                                                </div>
                                                
                                                <!-- Refund Reason -->
                                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Refund Reason:</p>
                                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ $refund->reason }}</p>
                                                </div>
                                                
                                                @if($refund->admin_notes)
                                                    <div class="mt-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg p-4 border border-amber-200 dark:border-amber-700">
                                                        <p class="text-xs font-medium text-amber-700 dark:text-amber-300 mb-1">Admin Notes:</p>
                                                        <p class="text-sm text-amber-900 dark:text-amber-100">{{ $refund->admin_notes }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Right Section: Amount & Actions -->
                                    <div class="flex-shrink-0 lg:ml-6">
                                        <div class="text-right mb-4">
                                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Refund Amount</p>
                                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">₱{{ number_format($refund->amount, 2) }}</p>
                                        </div>
                                        
                                        <!-- Actions -->
                                        @if($refund->status === 'pending')
                                            @php
                                                $userName = $refund->user->first_name . ' ' . $refund->user->last_name;
                                                $itemName = 'Organizer Subscription';
                                                if ($refund->type === 'ticket' && $refund->ticketPurchase) {
                                                    $itemName = $refund->ticketPurchase->event?->title ?? 'Event #' . $refund->ticketPurchase->event_id;
                                                } elseif ($refund->type === 'additional_ticket' && $refund->additionalTicketPurchase) {
                                                    $itemName = ($refund->additionalTicketPurchase->event?->title ?? 'Event #' . $refund->additionalTicketPurchase->event_id) . ' (Additional Tickets)';
                                                }
                                            @endphp
                                            <button 
                                                type="button"
                                                class="process-refund-btn w-full lg:w-auto px-6 py-2.5 bg-qricket-600 hover:bg-qricket-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-qricket-500 focus:ring-offset-2 flex items-center justify-center space-x-2"
                                                data-refund-id="{{ $refund->id }}"
                                                data-user-name="{{ htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') }}"
                                                data-amount="{{ $refund->amount }}"
                                                data-reason="{{ htmlspecialchars($refund->reason, ENT_QUOTES, 'UTF-8') }}"
                                                data-type="{{ $refund->type }}"
                                                data-item-name="{{ htmlspecialchars($itemName, ENT_QUOTES, 'UTF-8') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                <span>Process Refund</span>
                                            </button>
                                        @elseif($refund->status === 'approved')
                                            <div class="text-center">
                                                <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded-lg">
                                                    <svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                    </svg>
                                                    Waiting to be processed...
                                                </span>
                                            </div>
                                        @else
                                            <div class="text-center">
                                                <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No refund requests</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No refund requests found matching your filters.</p>
                        </div>
                    @endforelse
                </div>
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $refunds->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        // Format type name
        function formatTypeName(type) {
            return type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
        }

        // Process refund button click handler
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.process-refund-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const refundId = this.getAttribute('data-refund-id');
                    const userName = this.getAttribute('data-user-name');
                    const amount = parseFloat(this.getAttribute('data-amount'));
                    const reason = this.getAttribute('data-reason');
                    const type = this.getAttribute('data-type');
                    const itemName = this.getAttribute('data-item-name');
                    
                    processRefund(refundId, userName, amount, reason, type, itemName);
                });
            });
        });

        function processRefund(refundId, userName, amount, reason, type, itemName) {
            if (typeof Swal === 'undefined' || !window.Swal) {
                alert('SweetAlert2 is not loaded. Please refresh the page.');
                return;
            }

            // Escape all user-provided content
            const safeUserName = escapeHtml(userName);
            const safeItemName = escapeHtml(itemName);
            const safeReason = escapeHtml(reason);
            const formattedType = escapeHtml(formatTypeName(type));
            const formattedAmount = parseFloat(amount).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            // Show refund details in SweetAlert
            Swal.fire({
                title: 'Process Refund Request',
                html: `
                    <div class="text-left space-y-4">
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Refund Details</p>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">User:</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100">${safeUserName}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Item:</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100">${safeItemName}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Type:</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100">${formattedType}</span>
                                </div>
                                <div class="flex justify-between border-t border-gray-200 dark:border-gray-600 pt-2 mt-2">
                                    <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">Refund Amount:</span>
                                    <span class="text-lg font-bold text-qricket-600 dark:text-qricket-400">₱${formattedAmount}</span>
                                </div>
                            </div>
                        </div>
                        <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-4 border border-amber-200 dark:border-amber-700">
                            <p class="text-xs font-medium text-amber-700 dark:text-amber-300 mb-2">Refund Reason:</p>
                            <p class="text-sm text-amber-900 dark:text-amber-100 whitespace-pre-wrap">${safeReason}</p>
                        </div>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Approve',
                denyButtonText: '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Reject',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#059669',
                denyButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
                customClass: {
                    popup: 'text-left',
                    htmlContainer: 'text-left'
                },
                width: '600px'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Approve refund
                    approveRefund(refundId);
                } else if (result.isDenied) {
                    // Reject refund - show input for rejection reason
                    rejectRefund(refundId);
                }
            });
        }

        function approveRefund(refundId) {
            // Show loading state
            Swal.fire({
                title: 'Processing...',
                text: 'Please wait while we approve the refund request.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Create a form and submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ url('/admin/refunds') }}/${refundId}/approve`;
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken.getAttribute('content');
                form.appendChild(csrfInput);
            }
            
            document.body.appendChild(form);
            form.submit();
        }

        function rejectRefund(refundId) {
            Swal.fire({
                title: 'Reject Refund Request',
                html: `
                    <div class="text-left">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Please provide a reason for rejecting this refund request:</p>
                        <textarea id="reject-reason" rows="4" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="Enter rejection reason..."></textarea>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Reject Request',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                customClass: {
                    popup: 'text-left',
                    htmlContainer: 'text-left'
                },
                preConfirm: () => {
                    const reason = document.getElementById('reject-reason').value;
                    if (!reason || reason.trim() === '') {
                        Swal.showValidationMessage('Please provide a reason for rejection');
                        return false;
                    }
                    return reason.trim();
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    // Show loading state
                    Swal.fire({
                        title: 'Processing...',
                        text: 'Please wait while we reject the refund request.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Create a form and submit it
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `{{ url('/admin/refunds') }}/${refundId}/reject`;
                    
                    const csrfToken = document.querySelector('meta[name="csrf-token"]');
                    if (csrfToken) {
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = csrfToken.getAttribute('content');
                        form.appendChild(csrfInput);
                    }
                    
                    const reasonInput = document.createElement('input');
                    reasonInput.type = 'hidden';
                    reasonInput.name = 'admin_notes';
                    reasonInput.value = result.value;
                    form.appendChild(reasonInput);
                    
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
</x-admin-layout>

