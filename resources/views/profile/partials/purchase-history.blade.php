<!-- Purchase History Section -->
<div id="purchase-history" class="section-content hidden">
    <div class="profile-card p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg border border-gray-200 dark:border-gray-700 transition-all duration-300">
        <div class="w-full">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Purchase History</h3>
            <p class="mt-1 mb-4 text-sm text-gray-600 dark:text-gray-400">
                View all your ticket orders and subscriptions. Entries marked with a green badge are paid and include a downloadable invoice.
            </p>
            
            <!-- Refund Policy Notice -->
            <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-l-4 border-blue-500 dark:border-blue-400 rounded-r-lg p-4">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-blue-500 dark:text-blue-400 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-300 mb-1">Refund Policy</h4>
                        <p class="text-sm text-blue-800 dark:text-blue-200">
                            Refund requests are only allowed <strong>within 7 days</strong> from the date of purchase.
                        </p>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3 mb-4 flex-wrap">
                <button onclick="filterPurchases('all')" class="filter-btn inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium bg-qricket-600 text-white hover:bg-qricket-700 transition-colors" data-filter="all">
                    All
                </button>
                <button onclick="filterPurchases('paid')" class="filter-btn inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors" data-filter="paid">
                    Paid
                </button>
                <button onclick="filterPurchases('pending')" class="filter-btn inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors" data-filter="pending">
                    Pending
                </button>
                <button onclick="filterPurchases('other')" class="filter-btn inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors" data-filter="other">
                    Other
                </button>
            </div>
            @php
                $ticketPurchases = \App\Models\TicketPurchase::with(['event', 'ticketInformation'])->where('user_id', auth()->id())->orderByDesc('created_at')->get();
                $subscriptions = \App\Models\Subscription::where('user_id', auth()->id())->orderByDesc('created_at')->get();
                $additionalTickets = \App\Models\TicketLimitPurchase::with(['ticketLimitPricing', 'event'])->where('user_id', auth()->id())->orderByDesc('created_at')->get();
                $allPurchases = collect();
                
                // Get all pending refund requests with their IDs
                $pendingRefundRequests = \App\Models\RefundRequest::where('user_id', auth()->id())
                    ->where('status', 'pending')
                    ->get()
                    ->keyBy(function($request) {
                        return $request->type . '_' . $request->item_id;
                    });
                
                // Get all processed/refunded refund requests to get ticket IDs for invoice routes
                $refundedRequests = \App\Models\RefundRequest::where('user_id', auth()->id())
                    ->whereIn('status', ['processed', 'approved'])
                    ->where('type', 'ticket')
                    ->get()
                    ->keyBy('item_id');
                
                // Add ticket purchases
                foreach($ticketPurchases as $purchase) {
                    $firstTicketId = $purchase->ticketInformation->first()?->id ?? '';
                    
                    // If ticket info is deleted (refunded), try to get ticket ID from refund request
                    if (!$firstTicketId && $purchase->status === 'refunded') {
                        $refundedRequest = $refundedRequests->get($purchase->id);
                        if ($refundedRequest && $refundedRequest->refunded_ticket_ids && is_array($refundedRequest->refunded_ticket_ids) && !empty($refundedRequest->refunded_ticket_ids)) {
                            $firstTicketId = $refundedRequest->refunded_ticket_ids[0];
                        }
                    }
                    
                    $refundRequestKey = 'ticket_' . $purchase->id;
                    $pendingRefundRequest = $pendingRefundRequests->get($refundRequestKey);
                    $hasPendingRefund = $pendingRefundRequest !== null;
                    $purchaseDate = $purchase->paid_at ?? $purchase->created_at;
                    $daysSincePurchase = $purchaseDate->diffInDays(now());
                    
                    // Get participant info (may be empty for refunded purchases)
                    $ticketInfo = $purchase->ticketInformation;
                    $participantCount = $ticketInfo->count();
                    $checkedInCount = $ticketInfo->whereNotNull('checked_in_at')->count();
                    
                    $allPurchases->push([
                        'type' => 'ticket',
                        'id' => $purchase->id,
                        'purchase_id' => $purchase->purchase_id,
                        'date' => $purchaseDate,
                        'title' => $purchase->event?->title ?? 'Event #'.$purchase->event_id,
                        'quantity' => $purchase->quantity,
                        'amount' => $purchase->total_amount,
                        'status' => $purchase->status,
                        'refund_eligible' => !$hasPendingRefund && $purchase->status === 'paid' && $daysSincePurchase <= 7,
                        'refund_deadline' => $purchaseDate->copy()->addDays(7)->format('Y-m-d'),
                        'has_pending_refund' => $hasPendingRefund,
                        'pending_refund_id' => $pendingRefundRequest?->id,
                        'participants' => $participantCount,
                        'checked_in' => $checkedInCount,
                        'invoice_route' => $firstTicketId ? route('tickets.invoice', ['purchaseId' => $purchase->purchase_id, 'ticketId' => $firstTicketId]) : null
                    ]);
                }
                
                // Add subscriptions
                foreach($subscriptions as $subscription) {
                    $refundRequestKey = 'subscription_' . $subscription->id;
                    $pendingRefundRequest = $pendingRefundRequests->get($refundRequestKey);
                    $hasPendingRefund = $pendingRefundRequest !== null;
                    $daysSincePurchase = $subscription->created_at->diffInDays(now());
                    $allPurchases->push([
                        'type' => 'subscription',
                        'id' => $subscription->id,
                        'purchase_id' => $subscription->id,
                        'date' => $subscription->created_at,
                        'title' => 'Organizer Subscription',
                        'quantity' => 1,
                        'amount' => $subscription->amount,
                        'status' => $subscription->status,
                        'refund_eligible' => !$hasPendingRefund && $subscription->status === 'active' && $daysSincePurchase <= 7,
                        'refund_deadline' => $subscription->created_at->copy()->addDays(7)->format('Y-m-d'),
                        'has_pending_refund' => $hasPendingRefund,
                        'pending_refund_id' => $pendingRefundRequest?->id,
                        'invoice_route' => route('subscription.invoice', $subscription)
                    ]);
                }
                
                // Add additional tickets
                foreach($additionalTickets as $purchase) {
                    $refundRequestKey = 'additional_ticket_' . $purchase->id;
                    $pendingRefundRequest = $pendingRefundRequests->get($refundRequestKey);
                    $hasPendingRefund = $pendingRefundRequest !== null;
                    $purchaseDate = $purchase->paid_at ?? $purchase->created_at;
                    $daysSincePurchase = $purchaseDate->diffInDays(now());
                    $allPurchases->push([
                        'type' => 'additional_ticket',
                        'id' => $purchase->id,
                        'purchase_id' => $purchase->purchase_id,
                        'date' => $purchaseDate,
                        'title' => $purchase->event?->title ?? 'Event #'.$purchase->event_id . ' - Additional Tickets',
                        'quantity' => $purchase->additional_tickets,
                        'amount' => $purchase->amount,
                        'status' => $purchase->status,
                        'refund_eligible' => !$hasPendingRefund && $purchase->status === 'paid' && $daysSincePurchase <= 7,
                        'refund_deadline' => $purchaseDate->copy()->addDays(7)->format('Y-m-d'),
                        'has_pending_refund' => $hasPendingRefund,
                        'pending_refund_id' => $pendingRefundRequest?->id,
                        'invoice_route' => (function() use ($purchase) {
                            try {
                                return route('additional-tickets.invoice', $purchase->purchase_id);
                            } catch (\Exception $e) {
                                return '/additional-tickets/invoice/' . $purchase->purchase_id;
                            }
                        })()
                    ]);
                }
                
                $allPurchases = $allPurchases->sortByDesc('date');
            @endphp
            
            @if($allPurchases->count() === 0)
                <p class="text-gray-500 dark:text-gray-400">You have no purchases yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="py-2 pr-4">Date</th>
                                <th class="py-2 pr-4">Type</th>
                                <th class="py-2 pr-4">Item</th>
                                <th class="py-2 pr-4">Items</th>
                                <th class="py-2 pr-4">Total</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2 pr-4">Invoice</th>
                                <th class="py-2 pr-4">Refund</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-900 dark:text-gray-100">
                            @foreach($allPurchases as $purchase)
                                @php($status = strtolower($purchase['status']))
                                @php($badge = $status === 'paid' || $status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : ($status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'))
                                @php($filterStatus = in_array($status, ['paid', 'active']) ? 'paid' : ($status === 'pending' ? 'pending' : 'other'))
                                <tr class="purchase-row border-t border-gray-200 dark:border-gray-700 {{ ($status === 'paid' || $status === 'active') ? 'bg-green-50/30 dark:bg-green-900/10' : '' }}" data-status="{{ $filterStatus }}">
                                    <td class="py-2 pr-4">{{ $purchase['date']->format('Y-m-d H:i') }}</td>
                                    <td class="py-2 pr-4">
                                        @if($purchase['type'] === 'ticket')
                                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                                Ticket
                                            </span>
                                        @elseif($purchase['type'] === 'subscription')
                                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300">
                                                Subscription
                                            </span>
                                        @elseif($purchase['type'] === 'additional_ticket')
                                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300">
                                                Additional
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2 pr-4">{{ $purchase['title'] }}</td>
                                    <td class="py-2 pr-4">{{ $purchase['quantity'] }}</td>
                                    <td class="py-2 pr-4">₱{{ number_format((float)$purchase['amount'], 2) }}</td>
                                    <td class="py-2 pr-4">
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full {{ $badge }}">
                                            @if($status === 'paid' || $status === 'active')
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            @elseif($status === 'pending')
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @endif
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                    <td class="py-2 pr-4">
                                        @if($purchase['invoice_route'])
                                            <a href="{{ $purchase['invoice_route'] }}?from=profile" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium rounded-md bg-qricket-600 text-white hover:bg-qricket-700 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Invoice
                                            </a>
                                        @else
                                            <span class="text-gray-500">—</span>
                                        @endif
                                    </td>
                                    <td class="py-2 pr-4">
                                        @if($purchase['has_pending_refund'] && isset($purchase['pending_refund_id']))
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-md bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    Pending
                                                </span>
                                                <form method="POST" action="{{ route('refund.cancel', $purchase['pending_refund_id']) }}" class="inline" onsubmit="return confirm('Are you sure you want to cancel this refund request?');">
                                                    @csrf
                                                    @method('POST')
                                                    <button type="submit" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-md bg-gray-600 text-white hover:bg-gray-700 transition-colors">
                                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                        Cancel
                                                    </button>
                                                </form>
                                            </div>
                                        @elseif($purchase['refund_eligible'])
                                            <button onclick="openRefundModal('{{ $purchase['type'] }}', '{{ $purchase['id'] }}', '{{ $purchase['purchase_id'] }}', '{{ $purchase['amount'] }}')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-red-600 text-white hover:bg-red-700 transition-colors">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                                </svg>
                                                Request Refund
                                            </button>
                                        @elseif(($purchase['status'] === 'paid' || $purchase['status'] === 'active'))
                                            <span class="text-xs text-gray-500 dark:text-gray-400 flex items-center" title="Refund period has expired (7 days from purchase)">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                                Not Available
                                            </span>
                                        @else
                                            <span class="text-gray-500">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function filterPurchases(status) {
    const rows = document.querySelectorAll('.purchase-row');
    const buttons = document.querySelectorAll('.filter-btn');
    
    // Update button styles
    buttons.forEach(btn => {
        if (btn.getAttribute('data-filter') === status) {
            btn.classList.remove('bg-gray-100', 'text-gray-700', 'dark:bg-gray-700', 'dark:text-gray-200');
            btn.classList.add('bg-qricket-600', 'text-white', 'hover:bg-qricket-700');
        } else {
            btn.classList.add('bg-gray-100', 'text-gray-700', 'dark:bg-gray-700', 'dark:text-gray-200');
            btn.classList.remove('bg-qricket-600', 'text-white', 'hover:bg-qricket-700');
        }
    });
    
    // Filter rows
    rows.forEach(row => {
        if (status === 'all') {
            row.style.display = '';
        } else {
            const rowStatus = row.getAttribute('data-status');
            if (rowStatus === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });
}

// Initialize - show all by default
document.addEventListener('DOMContentLoaded', function() {
    filterPurchases('all');
});
</script>

