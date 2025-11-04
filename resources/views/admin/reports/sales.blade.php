<x-admin-layout title="Sales Reports - Qrickett" page-title="Sales Reports">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Sales Reports</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Comprehensive sales analytics and revenue insights.</p>
            </div>
            <div class="flex items-center space-x-3">
                <!-- Real-time connection status -->
                <div class="flex items-center">
                    <div id="admin-sales-connection-indicator" class="w-2 h-2 bg-red-500 rounded-full"></div>
                </div>
                <button onclick="openReportModal()" class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-qricket-600 hover:bg-qricket-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-qricket-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Generate Report
                </button>
                <button onclick="window.testRealtimeAdmin && window.testRealtimeAdmin();" class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Test Realtime
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <!-- Total Revenue Banner (full width, single row) -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Total Revenue (Tickets + Subscriptions + Additional Tickets)</div>
                        <div class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100" data-stat="grand-revenue">₱{{ number_format($grandRevenue ?? 0, 2) }}</div>
                        <div class="mt-1 text-sm"><span class="text-blue-600 dark:text-blue-400">Qricket Earnings (5% of ticket net + subs net + addl net)</span>: ₱{{ number_format($adminEarningsTotal ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
            <!-- KPI Cards Row 1 -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Ticket Revenue</div>
                    <div class="mt-1 text-lg font-bold text-gray-900 dark:text-gray-100" data-stat="ticket-revenue">₱{{ number_format($ticketRevenue, 2) }}</div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Net: ₱{{ number_format($ticketNetRevenue ?? 0, 2) }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Free Tickets Sold</div>
                    <div class="mt-1 text-lg font-bold text-gray-900 dark:text-gray-100">{{ number_format($freeTicketsSold ?? 0) }}</div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Unit price ₱0</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Total Refunded</div>
                    <div class="mt-1 text-lg font-bold text-red-600 dark:text-red-400">₱{{ number_format($totalRefundedAmount ?? 0, 2) }}</div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ number_format($totalRefundedCount ?? 0) }} refunds • Tickets: {{ number_format($refundTicketsCount ?? 0) }} • Subs: {{ number_format($refundSubsCount ?? 0) }} • Addl: {{ number_format($refundAddlCount ?? 0) }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Total Payouts</div>
                    <div class="mt-1 text-lg font-bold text-blue-600 dark:text-blue-400">₱{{ number_format($payoutPaidTotal ?? 0, 2) }}</div>
                    <div class="mt-1 text-xs text-blue-600 dark:text-blue-400">Processed: ₱{{ number_format($payoutProcessedTotal ?? 0, 2) }}</div>
                </div>
                <!-- Additional Ticket Settings moved here as 4th KPI -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Additional Ticket Settings</div>
                    <div class="mt-1 text-lg font-bold text-green-600 dark:text-green-400" data-stat="additional-tickets-revenue">₱{{ number_format($ticketLimitRevenue ?? 0, 2) }}</div>
                    <div class="mt-1 text-xs"><span class="text-teal-600 dark:text-teal-400">Net: ₱{{ number_format($ticketLimitNetRevenue ?? 0, 2) }}</span> • <span class="text-gray-600 dark:text-gray-400" data-stat="additional-tickets-count">{{ number_format($additionalTicketsPurchased ?? 0) }} tix purchased</span></div>
                </div>
            </div>

@push('scripts')
<script src="https://js.pusher.com/8.2/pusher.min.js"></script>
<script>
    // Set Pusher configuration for real-time updates
    window.PUSHER_APP_KEY = @json(config('broadcasting.connections.pusher.key'));
    window.PUSHER_APP_CLUSTER = @json(config('broadcasting.connections.pusher.options.cluster') ?? config('broadcasting.connections.pusher.cluster'));
    window.PUSHER_AUTH_ENDPOINT = '/admin/broadcasting/auth';
    window.ADMIN_ID = {{ Auth::guard('admin')->id() ?? 'null' }};
    
    // Fallback refresh function (called by realtime script as fallback)
    window.refreshSales = function(){
        const url = new URL('{{ route('realtime.metrics') }}', window.location.origin);
        url.searchParams.set('scope','admin_sales');
        fetch(url.toString(), { credentials:'same-origin' })
            .then(r=>r.json()).then((data)=>{
                if (!data) return;
                const statsMap = {
                    'ticketRevenue': data.ticketRevenue,
                    'ticketNetRevenue': data.ticketNetRevenue,
                    'payoutPaidTotal': data.payoutPaidTotal,
                    'payoutProcessedTotal': data.payoutProcessedTotal,
                    'totalRefundedAmount': data.totalRefundedAmount,
                };
                Object.entries(statsMap).forEach(([k,v])=>{
                    const el = document.querySelector(`[data-stat="${k}"]`);
                    if (el && typeof v !== 'undefined') el.textContent = '₱' + (Number(v||0)).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
                });
            }).catch(()=>{});
    };
</script>
<script src="{{ asset('js/admin-sales-realtime.js') }}?v={{ time() }}"></script>
@endpush
            <!-- Subscription Plans Row 2 (Free plan removed in controller) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Subscription Revenue KPI placed first in second row -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Subscription Revenue</div>
                    <div class="mt-1 text-lg font-bold text-green-600 dark:text-green-400" data-stat="subscription-revenue">₱{{ number_format($subRevenue ?? 0, 2) }}</div>
                    <div class="mt-1 text-xs"><span class="text-teal-600 dark:text-teal-400">Net: ₱{{ number_format($subNetRevenue ?? 0, 2) }}</span> • <span class="text-gray-600 dark:text-gray-400" data-stat="subscription-count">{{ number_format($subscriptionCount ?? 0) }} users subscribed</span></div>
                </div>
                @foreach($subscriptionPlans as $plan)
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $plan['plan_name'] }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">₱{{ number_format($plan['plan_price'], 2) }} per month</p>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-green-600 dark:text-green-400">₱{{ number_format($plan['total_revenue'], 2) }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $plan['total_subscriptions'] }} users</div>
                            </div>
                        </div>
                    </div>
                @endforeach
                
            </div>

            <!-- Top Events & Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                <!-- Top Events - 1 column with fixed height -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-4 flex flex-col">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Top Events by Revenue</h3>
                        <div class="overflow-x-auto flex-1">
                        <table id="topEventsTable" class="min-w-full text-xs">
                            <thead class="text-left text-gray-500 dark:text-gray-400 sticky top-0 bg-white dark:bg-gray-800">
                                <tr>
                                    <th class="py-2 pr-4">Event</th>
                                    <th class="py-2 pr-4">Orders</th>
                                    <th class="py-2 pr-4">Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-900 dark:text-gray-100">
                                @forelse($topEvents->take(8) as $row)
                                    <tr class="border-t border-gray-200 dark:border-gray-700">
                                        <td class="py-2 pr-4">{{ Str::limit($row['title'], 20) }}</td>
                                        <td class="py-2 pr-4">{{ number_format($row['orders']) }}</td>
                                        <td class="py-2 pr-4">₱{{ number_format($row['revenue'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td class="py-3 text-gray-500 dark:text-gray-400" colspan="3">No data</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Charts - 3 columns (1.5 each) with bigger height -->
                <div class="lg:col-span-3 grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-4 flex flex-col">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Ticket Sales (30d)</h3>
                        </div>
                        <div class="flex-1 min-h-0">
                            <canvas id="ticketTrendChart" height="200"></canvas>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-4 flex flex-col">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Subscription Revenue (30d)</h3>
                        </div>
                        <div class="flex-1 min-h-0">
                            <canvas id="subTrendChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabbed Content Section -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                <!-- Windows-style tabs -->
                <div class="flex border-b border-gray-200 dark:border-gray-700">
                    <button id="eventRevenueTab" class="tab-button active px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 border-b-2 border-teal-500">
                        Event Revenue
                    </button>
                    <button id="subscriptionPlansTab" class="tab-button px-4 py-3 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 border-b-2 border-transparent hover:border-gray-300 dark:hover:border-gray-600">
                        Subscription Plans
                    </button>
                    <button id="additionalTicketsTab" class="tab-button px-4 py-3 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 border-b-2 border-transparent hover:border-gray-300 dark:hover:border-gray-600">
                        Additional Tickets
                    </button>
                </div>

                <!-- Tab Content -->
                <div class="p-4">
                    <!-- Event Revenue Tab Content -->
                    <div id="eventRevenueContent" class="tab-content">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">All Events Revenue</h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $allEventsRevenue->count() }} events</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table id="eventRevenueTable" class="min-w-full text-xs">
                                <thead class="text-left text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="py-2 pr-4 font-medium cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 sortable" data-column="title">
                                            Event Title <span class="sort-indicator">↕</span>
                                        </th>
                                        <th class="py-2 pr-4 font-medium cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 sortable" data-column="start_date">
                                            Event Date <span class="sort-indicator">↕</span>
                                        </th>
                                        <th class="py-2 pr-4 font-medium cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 sortable" data-column="status">
                                            Status <span class="sort-indicator">↕</span>
                                        </th>
                                        <th class="py-2 pr-4 font-medium cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 sortable" data-column="orders">
                                            Orders <span class="sort-indicator">↕</span>
                                        </th>
                                        <th class="py-2 pr-4 font-medium cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 sortable" data-column="total_revenue">
                                            Total Sales <span class="sort-indicator">↕</span>
                                        </th>
                                        <th class="py-2 pr-4 font-medium cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 sortable" data-column="net_revenue">
                                            Net Amount <span class="sort-indicator">↕</span>
                                        </th>
                                        <th class="py-2 pr-4 font-medium cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 sortable" data-column="admin_earnings">
                                            Qricket Earnings <span class="sort-indicator">↕</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-900 dark:text-gray-100">
                                    @forelse($allEventsRevenue as $event)
                                        <tr class="border-t border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="py-2 pr-4 font-medium">
                                                <a href="{{ route('admin.events.approval.show', $event['event_id']) }}" class="text-teal-600 dark:text-teal-400 hover:text-teal-800 dark:hover:text-teal-300 hover:underline">
                                                    {{ $event['title'] }}
                                                </a>
                                            </td>
                                            <td class="py-2 pr-4">{{ $event['start_date'] }}</td>
                                            <td class="py-2 pr-4">
                                                <span class="px-2 py-1 text-xs rounded-full 
                                                    @if($event['status'] === 'published') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                    @elseif($event['status'] === 'draft') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                                    @elseif($event['status'] === 'cancelled') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                    @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                                    {{ ucfirst($event['status']) }}
                                                </span>
                                            </td>
                                            <td class="py-2 pr-4">{{ number_format($event['orders']) }}</td>
                                            <td class="py-2 pr-4 font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format($event['total_revenue'] ?? $event['revenue'] ?? 0, 2) }}</td>
                                            <td class="py-2 pr-4 font-semibold text-blue-600 dark:text-blue-400">₱{{ number_format($event['net_revenue'] ?? $event['total_revenue'] ?? $event['revenue'] ?? 0, 2) }}</td>
                                            <td class="py-2 pr-4 font-semibold text-teal-600 dark:text-teal-400">₱{{ number_format($event['admin_earnings'] ?? 0, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td class="py-3 text-gray-500 dark:text-gray-400" colspan="7">No events with revenue found</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination for Event Revenue -->
                        @if($allEventsRevenue->hasPages())
                            <div class="mt-4 flex items-center justify-between">
                                <div class="text-sm text-gray-700 dark:text-gray-300">
                                    Showing {{ $allEventsRevenue->firstItem() }} to {{ $allEventsRevenue->lastItem() }} of {{ $allEventsRevenue->total() }} events
                                </div>
                                <div class="flex items-center space-x-2">
                                    {{ $allEventsRevenue->links() }}
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Subscription Plans Tab Content -->
                    <div id="subscriptionPlansContent" class="tab-content hidden">
                        <!-- Subscription Users Table -->
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Subscription Users</h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $subscriptionUsers->total() }} users</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table id="subscriptionUsersTable" class="min-w-full text-xs">
                                <thead class="text-left text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="py-2 pr-4 font-medium cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 sortable" data-column="user_name">
                                            User Name <span class="sort-indicator">↕</span>
                                        </th>
                                        <th class="py-2 pr-4 font-medium cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 sortable" data-column="email">
                                            Email <span class="sort-indicator">↕</span>
                                        </th>
                                        <th class="py-2 pr-4 font-medium cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 sortable" data-column="plan_name">
                                            Plan Name <span class="sort-indicator">↕</span>
                                        </th>
                                        <th class="py-2 pr-4 font-medium cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 sortable" data-column="amount">
                                            Amount <span class="sort-indicator">↕</span>
                                        </th>
                                        <th class="py-2 pr-4 font-medium cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 sortable" data-column="status">
                                            Status <span class="sort-indicator">↕</span>
                                        </th>
                                        <th class="py-2 pr-4 font-medium cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 sortable" data-column="created_at">
                                            Subscribed <span class="sort-indicator">↕</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-900 dark:text-gray-100">
                                    @forelse($subscriptionUsers as $user)
                                        <tr class="border-t border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="py-2 pr-4 font-medium">
                                                <a href="{{ route('admin.users.show', $user['user_id']) }}" class="text-teal-600 dark:text-teal-400 hover:text-teal-800 dark:hover:text-teal-300 hover:underline">
                                                    {{ $user['name'] }}
                                                </a>
                                            </td>
                                            <td class="py-2 pr-4">{{ $user['email'] }}</td>
                                            <td class="py-2 pr-4">{{ $user['plan_name'] }}</td>
                                            <td class="py-2 pr-4 font-semibold text-green-600 dark:text-green-400">₱{{ number_format($user['amount'], 2) }}</td>
                                            <td class="py-2 pr-4">
                                                <span class="px-2 py-1 text-xs rounded-full 
                                                    @if($user['status'] === 'active') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                    @elseif($user['status'] === 'cancelled') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                    @elseif($user['status'] === 'expired') bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                                                    @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 @endif">
                                                    {{ ucfirst($user['status']) }}
                                                </span>
                                            </td>
                                            <td class="py-2 pr-4">{{ $user['created_at'] }}</td>
                                        </tr>
                                    @empty
                                        <tr><td class="py-3 text-gray-500 dark:text-gray-400" colspan="6">No subscription users found</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination for Subscription Users -->
                        @if($subscriptionUsers->hasPages())
                            <div class="mt-4 flex items-center justify-between">
                                <div class="text-sm text-gray-700 dark:text-gray-300">
                                    Showing {{ $subscriptionUsers->firstItem() }} to {{ $subscriptionUsers->lastItem() }} of {{ $subscriptionUsers->total() }} users
                                </div>
                                <div class="flex items-center space-x-2">
                                    {{ $subscriptionUsers->links() }}
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Additional Tickets Tab Content -->
                    <div id="additionalTicketsContent" class="tab-content hidden">
                        @if(!empty($ticketLimitPlans) && count($ticketLimitPlans) > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                            @foreach($ticketLimitPlans as $p)
                                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $p['plan_name'] }}</h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">₱{{ number_format($p['plan_price'], 2) }} per pack</p>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-bold text-green-600 dark:text-green-400">₱{{ number_format($p['total_revenue'], 2) }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($p['total_purchases']) }} orders • {{ number_format($p['total_tickets']) }} tix</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @endif
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Additional Tickets by Event</h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ count($ticketLimitByEvent ?? []) }} events</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-xs">
                                <thead class="text-left text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="py-2 pr-4">Event Title</th>
                                        <th class="py-2 pr-4">Orders</th>
                                        <th class="py-2 pr-4">Tickets</th>
                                        <th class="py-2 pr-4">Revenue (Gross)</th>
                                        <th class="py-2 pr-4">Net Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-900 dark:text-gray-100">
                                    @forelse(($ticketLimitByEvent ?? []) as $row)
                                        <tr class="border-t border-gray-200 dark:border-gray-700">
                                            <td class="py-2 pr-4">
                                                <a href="{{ route('admin.events.approval.show', $row['event_id']) }}" class="text-teal-600 dark:text-teal-400 hover:underline">{{ $row['title'] }}</a>
                                            </td>
                                            <td class="py-2 pr-4">{{ number_format($row['orders']) }}</td>
                                            <td class="py-2 pr-4">{{ number_format($row['tickets']) }}</td>
                                            <td class="py-2 pr-4 font-semibold text-green-600 dark:text-green-400">₱{{ number_format($row['revenue'], 2) }}</td>
                                            <td class="py-2 pr-4 font-semibold text-blue-600 dark:text-blue-400">₱{{ number_format($row['net_revenue'] ?? 0, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td class="py-3 text-gray-500 dark:text-gray-400" colspan="5">No additional ticket purchases found</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Filter Modal -->
    <div id="reportModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-10 mx-auto p-0 border-0 w-full max-w-2xl shadow-2xl rounded-xl bg-white dark:bg-gray-800">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-qricket-500 to-emerald-600 rounded-t-xl">
                <div>
                    <h3 class="text-xl font-bold text-white">Generate Sales Report</h3>
                    <p class="text-sm text-qricket-100 mt-1">Create comprehensive sales analytics</p>
                </div>
                <button onclick="closeReportModal()" class="p-2 text-white hover:bg-white hover:bg-opacity-20 rounded-lg transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6">
                <form id="reportForm" onsubmit="generateFilteredReport(event)" class="space-y-6">
                    <!-- Date Range -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">📅 Date Range</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                                <input type="radio" name="dateRange" value="today" class="h-4 w-4 text-qricket-600 focus:ring-qricket-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700" checked>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Today</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Current day only</div>
                                </div>
                            </label>
                            <label class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                                <input type="radio" name="dateRange" value="week" class="h-4 w-4 text-qricket-600 focus:ring-qricket-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">This Week</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Last 7 days</div>
                                </div>
                            </label>
                            <label class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                                <input type="radio" name="dateRange" value="month" class="h-4 w-4 text-qricket-600 focus:ring-qricket-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">This Month</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Current month</div>
                                </div>
                            </label>
                            <label class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                                <input type="radio" name="dateRange" value="year" class="h-4 w-4 text-qricket-600 focus:ring-qricket-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">This Year</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Current year</div>
                                </div>
                            </label>
                        </div>
                        
                        <!-- Custom Date Range -->
                        <div class="mt-4">
                            <label class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                                <input type="radio" name="dateRange" value="custom" class="h-4 w-4 text-qricket-600 focus:ring-qricket-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Custom Range</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Select specific dates</div>
                                </div>
                            </label>
                            
                            <div id="customDateRange" class="mt-4 grid grid-cols-2 gap-4 hidden">
                                <div>
                                    <label for="startDate" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Start Date</label>
                                    <input type="date" id="startDate" name="startDate" class="w-full px-4 py-3 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-qricket-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-colors">
                                </div>
                                <div>
                                    <label for="endDate" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">End Date</label>
                                    <input type="date" id="endDate" name="endDate" class="w-full px-4 py-3 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-qricket-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-colors">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Report Type -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">📊 Report Type</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                                <input type="radio" name="reportType" value="all" class="h-4 w-4 text-qricket-600 focus:ring-qricket-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700" checked>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">All Sales</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Events + Subscriptions + Additional Tickets + Payouts</div>
                                </div>
                            </label>
                            <label class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                                <input type="radio" name="reportType" value="events" class="h-4 w-4 text-qricket-600 focus:ring-qricket-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Events Only</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Ticket sales</div>
                                </div>
                            </label>
                            <label class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                                <input type="radio" name="reportType" value="subscriptions" class="h-4 w-4 text-qricket-600 focus:ring-qricket-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Subscriptions</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Plan sales</div>
                                </div>
                            </label>
                            <label class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                                <input type="radio" name="reportType" value="additional_tickets" class="h-4 w-4 text-qricket-600 focus:ring-qricket-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Additional Tickets</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Ticket limit purchases</div>
                                </div>
                            </label>
                            <label class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                                <input type="radio" name="reportType" value="payouts" class="h-4 w-4 text-qricket-600 focus:ring-qricket-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Payouts</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Event payouts (processed/paid)</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Export Format -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">📁 Export Format</label>
                        <div class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-qricket-50 dark:bg-qricket-900/20">
                            <input type="radio" name="format" value="excel" class="h-4 w-4 text-qricket-600 focus:ring-qricket-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700" checked>
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Excel (.xls)</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Multi-sheet workbook</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" onclick="closeReportModal()" class="px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors duration-200">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-3 text-sm font-medium text-white bg-gradient-to-r from-qricket-600 to-emerald-600 hover:from-qricket-700 hover:to-emerald-700 rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Generate Report
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        function openReportModal() {
            document.getElementById('reportModal').classList.remove('hidden');
            // Set default dates for custom range
            const today = new Date().toISOString().split('T')[0];
            const lastWeek = new Date();
            lastWeek.setDate(lastWeek.getDate() - 7);
            const lastWeekStr = lastWeek.toISOString().split('T')[0];
            
            document.getElementById('startDate').value = lastWeekStr;
            document.getElementById('endDate').value = today;
        }

        function closeReportModal() {
            document.getElementById('reportModal').classList.add('hidden');
            document.getElementById('reportForm').reset();
            // Reset to today as default
            document.querySelector('input[name="dateRange"][value="today"]').checked = true;
            document.getElementById('customDateRange').classList.add('hidden');
        }

        function toggleCustomDate() {
            const customRadio = document.querySelector('input[name="dateRange"][value="custom"]');
            const customDateRange = document.getElementById('customDateRange');
            
            if (customRadio && customRadio.checked) {
                customDateRange.classList.remove('hidden');
                // Ensure dates are set when showing custom range
                const today = new Date().toISOString().split('T')[0];
                const lastWeek = new Date();
                lastWeek.setDate(lastWeek.getDate() - 7);
                const lastWeekStr = lastWeek.toISOString().split('T')[0];
                
                if (!document.getElementById('startDate').value) {
                    document.getElementById('startDate').value = lastWeekStr;
                }
                if (!document.getElementById('endDate').value) {
                    document.getElementById('endDate').value = today;
                }
            } else {
                customDateRange.classList.add('hidden');
            }
        }

        // Add event listeners for all radio buttons
        document.addEventListener('DOMContentLoaded', function() {
            const dateRangeRadios = document.querySelectorAll('input[name="dateRange"]');
            dateRangeRadios.forEach(radio => {
                radio.addEventListener('change', toggleCustomDate);
            });
            
            // Initialize custom date range visibility
            toggleCustomDate();
        });

        function generateFilteredReport(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const reportData = Object.fromEntries(formData.entries());
            
            // Show loading state
            const submitButton = event.target.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            submitButton.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Generating...';
            submitButton.disabled = true;

            // Send request to backend
            fetch('/admin/reports/sales/generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(reportData)
            })
            .then(response => response.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `admin-sales-report-${new Date().toISOString().split('T')[0]}.xls`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
                
                closeReportModal();
            })
            .catch(error => {
                console.error('Error generating report:', error);
                alert('Error generating report. Please try again.');
            })
            .finally(() => {
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
            });
        }

        document.addEventListener('DOMContentLoaded', function(){
            // Tab functionality
            const eventRevenueTab = document.getElementById('eventRevenueTab');
            const subscriptionPlansTab = document.getElementById('subscriptionPlansTab');
            const eventRevenueContent = document.getElementById('eventRevenueContent');
            const subscriptionPlansContent = document.getElementById('subscriptionPlansContent');

            function switchTab(activeTab, activeContent, ...others) {
                // Remove active classes from all tabs
                document.querySelectorAll('.tab-button').forEach(tab => {
                    tab.classList.remove('active', 'text-gray-900', 'dark:text-gray-100', 'border-teal-500');
                    tab.classList.add('text-gray-500', 'dark:text-gray-400', 'border-transparent');
                });

                // Hide all content
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.add('hidden');
                });

                // Show active tab and content
                activeTab.classList.add('active', 'text-gray-900', 'dark:text-gray-100', 'border-teal-500');
                activeTab.classList.remove('text-gray-500', 'dark:text-gray-400', 'border-transparent');
                activeContent.classList.remove('hidden');
            }

            eventRevenueTab.addEventListener('click', () => {
                switchTab(eventRevenueTab, eventRevenueContent, subscriptionPlansTab, subscriptionPlansContent);
            });

            subscriptionPlansTab.addEventListener('click', () => {
                switchTab(subscriptionPlansTab, subscriptionPlansContent);
            });

            // Additional tickets tab
            const additionalTicketsTab = document.getElementById('additionalTicketsTab');
            const additionalTicketsContent = document.getElementById('additionalTicketsContent');
            additionalTicketsTab.addEventListener('click', () => {
                switchTab(additionalTicketsTab, additionalTicketsContent);
            });

            // Chart initialization
            const ticketCtx = document.getElementById('ticketTrendChart');
            if (ticketCtx) {
                new Chart(ticketCtx, {
                    type: 'line',
                    data: {
                        labels: @json($ticketTrendLabels),
                        datasets: [
                            {
                                label: 'Revenue',
                                data: @json($ticketTrendRevenue),
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16,185,129,0.1)',
                                tension: 0.3,
                                yAxisID: 'y',
                            },
                            {
                                label: 'Orders',
                                data: @json($ticketTrendOrders),
                                borderColor: '#6366f1',
                                backgroundColor: 'rgba(99,102,241,0.1)',
                                tension: 0.3,
                                yAxisID: 'y1',
                            }
                        ]
                    },
                    options: {responsive:true, scales: {y: {beginAtZero:true}, y1: {beginAtZero:true, position:'right'}}}
                });
            }
            const subCtx = document.getElementById('subTrendChart');
            if (subCtx) {
                new Chart(subCtx, {
                    type: 'line',
                    data: {
                        labels: @json($subTrendLabels),
                        datasets: [{ label: 'Revenue', data: @json($subTrendRevenue), borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,0.1)', tension:0.3 }]
                    },
                    options: {responsive:true, scales: {y: {beginAtZero:true}}}
                });
            }

            // Table sorting functionality
            function initTableSorting() {
                document.querySelectorAll('.sortable').forEach(header => {
                    header.addEventListener('click', function() {
                        const column = this.dataset.column;
                        const table = this.closest('table');
                        const tbody = table.querySelector('tbody');
                        const rows = Array.from(tbody.querySelectorAll('tr'));
                        
                        // Determine sort direction
                        const currentSort = this.dataset.sort || 'asc';
                        const newSort = currentSort === 'asc' ? 'desc' : 'asc';
                        
                        // Reset all sort indicators
                        document.querySelectorAll('.sort-indicator').forEach(indicator => {
                            indicator.textContent = '↕';
                        });
                        
                        // Set current sort indicator
                        const indicator = this.querySelector('.sort-indicator');
                        indicator.textContent = newSort === 'asc' ? '↑' : '↓';
                        
                        // Store sort direction
                        this.dataset.sort = newSort;
                        
                        // Sort rows
                        rows.sort((a, b) => {
                            const aValue = a.querySelector(`td:nth-child(${Array.from(this.parentNode.children).indexOf(this) + 1})`).textContent.trim();
                            const bValue = b.querySelector(`td:nth-child(${Array.from(this.parentNode.children).indexOf(this) + 1})`).textContent.trim();
                            
                            // Handle numeric values
                            if (column === 'revenue' || column === 'amount' || column === 'orders') {
                                const aNum = parseFloat(aValue.replace(/[₱,]/g, '')) || 0;
                                const bNum = parseFloat(bValue.replace(/[₱,]/g, '')) || 0;
                                return newSort === 'asc' ? aNum - bNum : bNum - aNum;
                            }
                            
                            // Handle date values
                            if (column === 'start_date' || column === 'created_at') {
                                const aDate = new Date(aValue);
                                const bDate = new Date(bValue);
                                return newSort === 'asc' ? aDate - bDate : bDate - aDate;
                            }
                            
                            // Handle text values
                            return newSort === 'asc' ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
                        });
                        
                        // Re-append sorted rows
                        rows.forEach(row => tbody.appendChild(row));
                    });
                });
            }
            
            // Initialize sorting when DOM is loaded
            initTableSorting();
        });
    </script>

    <!-- Pass admin ID to JavaScript for private channel subscription -->
    <script>
        window.ADMIN_ID = {{ Auth::guard('admin')->id() ?? 'null' }};
    </script>
    
    <!-- Real-time admin sales updates -->
    <script src="{{ asset('js/admin-sales-realtime.js') }}?v={{ time() }}"></script>
</x-admin-layout>


