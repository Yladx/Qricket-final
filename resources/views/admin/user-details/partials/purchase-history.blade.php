@if($allPurchases->count())
    <!-- Purchase History Filter Dropdown -->
    <div class="mb-4">
        <label for="purchaseHistoryType" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Filter by Purchase Type:
        </label>
        <select id="purchaseHistoryType" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-qricket-500 dark:focus:ring-qricket-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            <option value="all">All Purchases</option>
            <option value="ticket">Ticket Purchases</option>
            <option value="additional_ticket">Additional Ticket History</option>
            <option value="subscription">Subscription History</option>
        </select>
    </div>

    <!-- DataTable Controls -->
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="relative">
                <input type="text" id="purchaseSearch" placeholder="Search purchases..." 
                       class="w-64 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-qricket-500 dark:focus:ring-qricket-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
            </div>
            <select id="purchaseStatusFilter" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-qricket-500 dark:focus:ring-qricket-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                <option value="">All Status</option>
                <option value="paid">Paid</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="refunded">Refunded</option>
                <option value="failed">Failed</option>
            </select>
        </div>
        <div class="text-sm text-gray-600 dark:text-gray-400">
            Showing <span id="purchaseCount">{{ $allPurchases->count() }}</span> purchases
        </div>
    </div>

    <div class="overflow-x-auto purchase-history-scroll">
        <table id="purchasesTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 sortable" data-column="created_at">
                        Date <span class="sort-indicator">↕</span>
                    </th>
                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 sortable" data-column="invoice_id">
                        Invoice ID <span class="sort-indicator">↕</span>
                    </th>
                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 sortable" data-column="type">
                        Type <span class="sort-indicator">↕</span>
                    </th>
                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 sortable" data-column="title">
                        Item <span class="sort-indicator">↕</span>
                    </th>
                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 sortable" data-column="amount">
                        Amount <span class="sort-indicator">↕</span>
                    </th>
                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 sortable" data-column="status">
                        Status <span class="sort-indicator">↕</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($allPurchases as $purchase)
                    <tr class="purchase-row @if($purchase['status'] === 'refunded') bg-gray-50 dark:bg-gray-900/50 @endif" data-type="{{ $purchase['type'] }}" data-status="{{ $purchase['status'] }}">
                        <!-- Date (first column) -->
                        <td class="px-2 py-2 text-xs text-gray-600 dark:text-gray-400">{{ $purchase['created_at']->format('M j, Y') }}</td>
                        
                        <!-- Invoice ID -->
                        <td class="px-2 py-2">
                            @if($purchase['type'] === 'ticket')
                                @if(!empty($purchase['route_params']['ticketId']))
                                    <a href="{{ route('tickets.invoice', $purchase['route_params']) }}" 
                                       class="text-qricket-600 dark:text-qricket-400 hover:text-qricket-800 dark:hover:text-qricket-300 hover:underline font-mono text-xs">
                                        #{{ $purchase['invoice_id'] }}
                                    </a>
                                @else
                                    <span class="text-gray-600 dark:text-gray-400 font-mono text-xs">#{{ $purchase['invoice_id'] }}</span>
                                @endif
                            @elseif($purchase['type'] === 'additional_ticket')
                                @php
                                    try {
                                        $invoiceUrl = route('admin.ticket-limits.view-invoice', $purchase['route_params']['purchaseId']);
                                    } catch (\Exception $e) {
                                        $invoiceUrl = '/admin/ticket-limits/invoice/' . $purchase['route_params']['purchaseId'];
                                    }
                                @endphp
                                <a href="{{ $invoiceUrl }}" 
                                   class="text-qricket-600 dark:text-qricket-400 hover:text-qricket-800 dark:hover:text-qricket-300 hover:underline font-mono text-xs">
                                    #{{ $purchase['invoice_id'] }}
                                </a>
                            @else
                                <a href="{{ route('subscription.invoice', $purchase['route_params']['subscriptionId']) }}" 
                                   class="text-qricket-600 dark:text-qricket-400 hover:text-qricket-800 dark:hover:text-qricket-300 hover:underline font-mono text-xs">
                                    #{{ $purchase['invoice_id'] }}
                                </a>
                            @endif
                        </td>
                        
                        <!-- Type -->
                        <td class="px-2 py-2">
                            @if($purchase['type'] === 'ticket')
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Ticket</span>
                            @elseif($purchase['type'] === 'additional_ticket')
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">Additional</span>
                            @else
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">Subscription</span>
                            @endif
                        </td>
                        
                        <!-- Item -->
                        <td class="px-2 py-2 text-xs text-gray-600 dark:text-gray-400">{{ $purchase['title'] }}</td>
                        
                        <!-- Amount -->
                        <td class="px-2 py-2 text-xs text-gray-600 dark:text-gray-400 font-semibold">₱{{ number_format($purchase['amount'] ?? $purchase['gross_amount'] ?? 0, 2) }}</td>
                        
                        <!-- Status (with refunded check) -->
                        <td class="px-2 py-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                @if($purchase['status'] === 'refunded') bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                @elseif($purchase['status'] === 'paid') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                @elseif($purchase['status'] === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                @elseif($purchase['status'] === 'processing') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                @elseif($purchase['status'] === 'failed') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                @if($purchase['status'] === 'refunded')
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                @endif
                                {{ ucfirst($purchase['status']) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center py-16">
        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">No Purchase History</h3>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">This user hasn't made any purchases yet.</p>
    </div>
@endif

<style>
/* Custom Scrollbar Styling */
.purchase-history-scroll {
    scrollbar-width: thin;
    scrollbar-color: #4a5568 #1a202c;
}

.purchase-history-scroll::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.purchase-history-scroll::-webkit-scrollbar-track {
    background: #1a202c;
    border-radius: 4px;
}

.purchase-history-scroll::-webkit-scrollbar-thumb {
    background: #4a5568;
    border-radius: 4px;
}

.purchase-history-scroll::-webkit-scrollbar-thumb:hover {
    background: #718096;
}

/* Dark mode scrollbar */
.dark .purchase-history-scroll {
    scrollbar-color: #6b7280 #111827;
}

.dark .purchase-history-scroll::-webkit-scrollbar-track {
    background: #111827;
}

.dark .purchase-history-scroll::-webkit-scrollbar-thumb {
    background: #6b7280;
}

.dark .purchase-history-scroll::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}
</style>

<script>
// DataTable functionality for purchases
document.addEventListener('DOMContentLoaded', function() {
    const purchasesTable = document.getElementById('purchasesTable');
    if (!purchasesTable) return;

    const searchInput = document.getElementById('purchaseSearch');
    const historyTypeFilter = document.getElementById('purchaseHistoryType');
    const statusFilter = document.getElementById('purchaseStatusFilter');
    const countElement = document.getElementById('purchaseCount');
    const rows = Array.from(purchasesTable.querySelectorAll('tbody tr'));

    // Initialize sorting indicators
    document.querySelectorAll('#purchasesTable .sortable').forEach(header => {
        const indicator = header.querySelector('.sort-indicator');
        indicator.textContent = '↕';
    });

    // Search functionality
    function filterRows() {
        const searchTerm = searchInput.value.toLowerCase();
        const historyTypeValue = historyTypeFilter.value;
        const statusValue = statusFilter.value;
        let visibleCount = 0;

        rows.forEach(row => {
            const type = row.dataset.type;
            const status = row.dataset.status;
            const text = row.textContent.toLowerCase();

            const matchesSearch = !searchTerm || text.includes(searchTerm);
            
            // Enhanced type filtering logic
            let matchesType = true;
            if (historyTypeValue === 'ticket') {
                matchesType = type === 'ticket';
            } else if (historyTypeValue === 'additional_ticket') {
                matchesType = type === 'additional_ticket';
            } else if (historyTypeValue === 'subscription') {
                matchesType = type === 'subscription';
            }
            
            const matchesStatus = !statusValue || status === statusValue;

            if (matchesSearch && matchesType && matchesStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (countElement) {
            countElement.textContent = visibleCount;
        }
    }
    
    // Initial filter on page load
    filterRows();

    // Add event listeners
    if (searchInput) searchInput.addEventListener('input', filterRows);
    if (historyTypeFilter) historyTypeFilter.addEventListener('change', filterRows);
    if (statusFilter) statusFilter.addEventListener('change', filterRows);

    // Sorting functionality
    document.querySelectorAll('#purchasesTable .sortable').forEach(header => {
        header.addEventListener('click', function() {
            const column = this.dataset.column;
            const tbody = purchasesTable.querySelector('tbody');
            const visibleRows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.style.display !== 'none');
            
            const currentSort = this.dataset.sort || 'asc';
            const newSort = currentSort === 'asc' ? 'desc' : 'asc';
            
            // Reset all sort indicators
            document.querySelectorAll('#purchasesTable .sort-indicator').forEach(indicator => {
                indicator.textContent = '↕';
            });
            
            // Set current sort indicator
            const indicator = this.querySelector('.sort-indicator');
            indicator.textContent = newSort === 'asc' ? '↑' : '↓';
            
            this.dataset.sort = newSort;
            
            // Sort visible rows
            visibleRows.sort((a, b) => {
                const aValue = a.querySelector(`td:nth-child(${Array.from(this.parentNode.children).indexOf(this) + 1})`).textContent.trim();
                const bValue = b.querySelector(`td:nth-child(${Array.from(this.parentNode.children).indexOf(this) + 1})`).textContent.trim();
                
                if (column === 'amount') {
                    const aNum = parseFloat(aValue.replace(/[₱,]/g, '')) || 0;
                    const bNum = parseFloat(bValue.replace(/[₱,]/g, '')) || 0;
                    return newSort === 'asc' ? aNum - bNum : bNum - aNum;
                }
                
                if (column === 'created_at') {
                    const aDate = new Date(aValue);
                    const bDate = new Date(bValue);
                    return newSort === 'asc' ? aDate - bDate : bDate - aDate;
                }
                
                return newSort === 'asc' ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
            });
            
            // Reorder rows in DOM
            visibleRows.forEach(row => tbody.appendChild(row));
        });
    });
});
</script>

