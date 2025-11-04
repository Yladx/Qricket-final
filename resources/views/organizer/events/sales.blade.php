<x-unified-layout :layout-type="$layoutType" :title="'Sales Report - ' . $event->title" :page-title="'Sales Report'">
    <x-slot name="header">
        <x-manage-header :event="$event" />
    </x-slot>

    <!-- Add data attributes for JavaScript -->
    <div data-event-id="{{ $event->id }}" data-user-id="{{ auth()->id() }}" style="display: none;"></div>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Ticket Sales</h3>
                        <div class="flex items-center gap-3">
                            <!-- Real-time connection status -->
                            <div class="flex items-center">
                                <div id="sales-connection-indicator" class="w-2 h-2 bg-red-500 rounded-full"></div>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-300">
                                <span data-stat="total-orders">{{ $sales->total() }}</span> records • 
                                Total Amount: <span class="font-semibold" data-stat="total-revenue">₱{{ number_format($totalAmount ?? 0, 2) }}</span>
                                • Net Amount: <span class="font-semibold text-green-600" data-stat="net-amount">₱{{ number_format($netAmount ?? 0, 2) }}</span>
                                • Total Fees: <span class="font-semibold text-red-600" data-stat="total-fees">₱{{ number_format($totalFees ?? 0, 2) }}</span>
                                • Expected Earnings: <span class="font-semibold text-blue-600" data-stat="expected-earnings">₱{{ number_format($expectedEarnings ?? 0, 2) }}</span>
                                • Free Tickets Sold: <span class="font-semibold">{{ number_format($freeTicketsSold ?? 0) }}</span>
                            </div>
                            <a href="{{ route($layoutType === 'admin' ? 'admin.event.sales.export' : 'organizer.events.sales.export', $event) }}?{{ http_build_query(request()->query()) }}" 
                               class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white rounded-md transition-colors flex items-center text-xs font-medium">
                                <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Export XLS
                            </a>
                        </div>
                    </div>
                    <form method="GET" action="{{ route('organizer.events.sales', $event) }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search buyer…" class="md:col-span-2 bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md text-sm" />
                        <select name="status" class="bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md text-sm">
                            <option value="">All Statuses</option>
                            @foreach($statuses as $k=>$v)
                                <option value="{{ $k }}" {{ $status===$k?'selected':'' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md text-sm" />
                        <input type="date" name="date_to" value="{{ $dateTo }}" class="bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md text-sm" />
                        <div class="md:col-span-5 flex items-center gap-2">
                            <label class="text-xs text-gray-500 dark:text-gray-400">Sort by:</label>
                            <select name="sort" class="bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md text-sm">
                                <option value="paid_at" {{ $sort==='paid_at'?'selected':'' }}>Paid Date</option>
                                <option value="total_amount" {{ $sort==='total_amount'?'selected':'' }}>Total Amount</option>
                                <option value="quantity" {{ $sort==='quantity'?'selected':'' }}>Quantity</option>
                            </select>
                            <select name="dir" class="bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md text-sm">
                                <option value="desc" {{ $dir==='desc'?'selected':'' }}>Desc</option>
                                <option value="asc" {{ $dir==='asc'?'selected':'' }}>Asc</option>
                            </select>
                            <button class="ml-auto px-3 py-1.5 rounded bg-qricket-500 text-white text-sm">Filter</button>
                            <a href="{{ route('organizer.events.sales', $event) }}" class="px-3 py-1.5 rounded bg-gray-100 dark:bg-gray-700 text-sm text-gray-700 dark:text-gray-300">Reset</a>
                        </div>
                    </form>
                </div>
                <div class="p-4 overflow-x-auto">
                    <table id="sales-table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-200">Buyer</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-200">Purchase ID</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-200">Ticket</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-200">Status</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-200">Qty</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-200">Total</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-200">Payment Method</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-200">Transaction Fee</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-200">VAT</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-200">Net Amount</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-200">Paid At</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($sales as $sale)
                                <tr class="group">
                                    <td class="px-4 py-2 text-gray-900 dark:text-gray-100">{{ trim(($sale->user->first_name ?? '').' '.($sale->user->last_name ?? '')) ?: 'Unknown' }}</td>
                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $sale->id }}</td>
                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $sale->ticketType->name ?? 'Ticket' }}</td>
                                    <td class="px-4 py-2">
                                        @php
                                            $status = strtolower($sale->status ?? '');
                                            $badge = 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
                                            if ($status === 'paid') $badge = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
                                            elseif ($status === 'pending') $badge = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
                                            elseif ($status === 'refunded') $badge = 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
                                            elseif ($status === 'cancelled' || $status === 'failed') $badge = 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">{{ ucfirst($sale->status) }}</span>
                                    </td>
                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $sale->quantity }}</td>
                                    <td class="px-4 py-2 text-gray-900 dark:text-gray-100">₱{{ number_format($sale->total_amount, 2) }}</td>
                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $sale->payment_method ?? '—' }}</td>
                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">₱{{ number_format($sale->transaction_fee ?? 0, 2) }}</td>
                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">₱{{ number_format($sale->vat_amount ?? 0, 2) }}</td>
                                    <td class="px-4 py-2 text-gray-900 dark:text-gray-100 font-semibold text-green-600">₱{{ number_format($sale->net_amount ?? $sale->total_amount, 2) }}</td>
                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300 flex items-center gap-2">{{ optional($sale->paid_at)->format('M d, Y g:i A') }}
                                        <button type="button" class="ml-auto text-xs px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600" data-toggle-details="sale-{{ $sale->id }}">Details</button>
                                    </td>
                                </tr>
                                <tr id="sale-{{ $sale->id }}" class="hidden">
                                    <td colspan="11" class="px-6 py-4 bg-gray-50 dark:bg-gray-900">
                                        <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                            <div class="font-semibold">Tickets</div>
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full border border-gray-200 dark:border-gray-700 text-xs">
                                                    <thead class="bg-gray-100 dark:bg-gray-800">
                                                        <tr>
                                                            <th class="px-2 py-1 text-left">Ticket ID</th>
                                                            <th class="px-2 py-1 text-left">Shared?</th>
                                                            <th class="px-2 py-1 text-left">Holder</th>
                                                            <th class="px-2 py-1 text-left">Checked In</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach(($sale->ticketInformation ?? []) as $t)
                                                            <tr class="border-t border-gray-200 dark:border-gray-700">
                                                                <td class="px-2 py-1">{{ $t->ticket_id ?? $t->id }}</td>
                                                                <td class="px-2 py-1">{{ !empty($t->is_shared) ? 'Yes' : 'No' }}</td>
                                                                <td class="px-2 py-1">{{ trim(($t->user->first_name ?? '').' '.($t->user->last_name ?? '')) ?: ($t->user->email ?? 'Unknown') }}</td>
                                                                <td class="px-2 py-1">{{ $t->checked_in_at ? $t->checked_in_at->format('M d, Y g:i A') : '—' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No ticket sales yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $sales->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-unified-layout>

<script>
(function(){
    document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('[data-toggle-details]').forEach(function(btn){
            btn.addEventListener('click', function(){
                const id = btn.getAttribute('data-toggle-details');
                const row = document.getElementById(id);
                if (!row) return;
                row.classList.toggle('hidden');
            });
        });
    });
})();
</script>

<!-- Real-time sales updates -->
@push('scripts')
<script src="https://js.pusher.com/8.2/pusher.min.js"></script>
<script>
    // Set Pusher configuration for real-time updates
    window.PUSHER_APP_KEY = @json(config('broadcasting.connections.pusher.key'));
    window.PUSHER_APP_CLUSTER = @json(config('broadcasting.connections.pusher.options.cluster') ?? config('broadcasting.connections.pusher.cluster'));
    window.PUSHER_AUTH_ENDPOINT = {{ $isAdmin ? "'/admin/broadcasting/auth'" : "'/broadcasting/auth'" }};
</script>
<script src="{{ asset('js/sales-report-realtime.js') }}?v={{ time() }}"></script>
@endpush


