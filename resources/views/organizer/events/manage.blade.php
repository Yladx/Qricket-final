<x-unified-layout :layout-type="$layoutType" :title="'Manage Event - ' . $event->title" :page-title="'Manage Event'">
    <x-slot name="header">
        <x-manage-header :event="$event" />
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @php
                $user = auth()->user();
                $staff = $event->staff->firstWhere('user_id', $user?->id);
                $isOwner = $user && (int)$event->user_id === (int)$user->id;
                $staffWelcome = $staffNotes['staff_welcome_message'] ?? null;
                $staffInstructions = $staffNotes['staff_instructions'] ?? null;
            @endphp
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-3 space-y-6">
                    @if($canManageSales)
                    <!-- Sales Analytics Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Total Revenue -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</p>
                                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" data-stat="event-total-revenue">₱{{ number_format($salesStats['totalRevenue'], 2) }}</p>
                                        @php
                                            $netRevenueAfterFees = ($salesStats['totalRevenue'] ?? 0) - ($salesStats['transactionFees'] ?? 0) - ($salesStats['vatAmount'] ?? 0);
                                        @endphp
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Net: ₱{{ number_format($netRevenueAfterFees, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tickets Sold -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                        </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tickets Sold</p>
                                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" data-stat="event-tickets-sold">{{ number_format($salesStats['totalTicketsSold']) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Orders -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                        </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Orders</p>
                                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" data-stat="event-total-orders">{{ number_format($salesStats['totalOrders']) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Total Expected Revenue -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-teal-500 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Expected Revenue</p>
                                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" data-stat="event-expected-revenue">₱{{ number_format(max(0, $salesStats['expectedRevenue'] ?? 0), 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($canViewGraphs)
                    <!-- Charts Row (Advanced Analytics Feature) -->
                    <div class="grid grid-cols-1 {{ $canManageSales ? 'lg:grid-cols-2' : 'lg:grid-cols-1' }} gap-6">
                    @if($canManageSales)
                    <!-- Sales Trend Chart -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Sales Trend</h3>
                                    <div class="flex items-center gap-2 text-xs">
                                        <label class="text-gray-600 dark:text-gray-300">Range</label>
                                        <select id="salesTrendRange" class="border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded px-2 py-1">
                                            <option value="30">Last 30 days</option>
                                            <option value="90">Last 90 days</option>
                                            <option value="365">Last 12 months</option>
                                        </select>
        </div>
    </div>
                                <div class="h-64 md:h-80">
                                    <canvas id="salesTrendChart"></canvas>
                                </div>
                            </div>
                        </div>
                    @endif

                        <!-- Event Activity Chart -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Event Activity</h3>
                                    <div class="flex items-center gap-2 text-xs">
                                        <label class="text-gray-600 dark:text-gray-300">Range</label>
                                        <select id="activityTrendRange" class="border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded px-2 py-1">
                                            <option value="30">Last 30 days</option>
                                            <option value="90">Last 90 days</option>
                                            <option value="365">Last 12 months</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="h-64 md:h-80">
                                    <canvas id="activityTrendChart"></canvas>
                                </div>
                </div>
            </div>
        </div>
                    @endif

                    @if($canManageSales)
                    <!-- Ticket Performance -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                            <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">Ticket Performance</h3>
                            
                            @if($ticketPerformance->count() > 0)
                                <div class="overflow-x-auto">
                                    <table id="ticket-performance-table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ticket Type</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Price</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Sold</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Remaining</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Utilization</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Revenue</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($ticketPerformance as $ticket)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $ticket['name'] }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                        ₱{{ number_format($ticket['price'], 2) }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                        {{ number_format($ticket['total_sold']) }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                        @if($ticket['remaining'] !== null)
                                                            {{ number_format($ticket['remaining']) }}
                                                        @else
                                                            <span class="text-green-600 dark:text-green-400">Unlimited</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                                            <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min($ticket['utilization'], 100) }}%"></div>
                                                            </div>
                                                            <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">
                                                                {{ number_format($ticket['utilization'], 1) }}%
                                                            </span>
                                    </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                        ₱{{ number_format($ticket['total_revenue'], 2) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    </div>
                            @else
                                <div class="text-center py-8">
                                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                        </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No tickets configured</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure tickets to see performance metrics.</p>
                                    </div>
                            @endif
                                    </div>
                                </div>
                    @endif

                    @if(!$canManageSales && !$canManageEvent)
                        <!-- Limited Overview for non-owner/non-manager -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Overview</h3>
                                @if(!empty($staffWelcome))
                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $staffWelcome }}</p>
                                @else
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Welcome to the event management dashboard.</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if(!$isOwner && ($staff))
                        <!-- Staff Rules and Instructions -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Rules & Instructions</h3>
                                @if(!empty($staffInstructions))
                                    <div class="text-sm text-gray-700 dark:text-gray-300">{!! nl2br(e($staffInstructions)) !!}</div>
                                @else
                                    <p class="text-sm text-gray-600 dark:text-gray-400">No specific instructions provided for this event.</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Recent Sales and Activity -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        @if($canManageSales)
                        <!-- Recent Sales -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg" id="recent-sales-section">
                            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">Recent Sales</h3>
                            </div>
                            <div class="max-h-80 overflow-y-auto divide-y divide-gray-200 dark:divide-gray-700" id="recent-sales-list">
                                @forelse($recentSales->take(5) as $sale)
                                <div class="p-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200" data-sale-id="{{ $sale->id }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                    <div class="flex-shrink-0">
                                                <div class="w-6 h-6 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                                    <svg class="w-3 h-3 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                    </div>
                                    </div>
                                            <div>
                                                <p class="text-xs font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $sale->user->first_name }} {{ $sale->user->last_name }}
                                                </p>
                                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                                    {{ $sale->quantity }}x {{ $sale->ticketType->name ?? 'Ticket' }}
                                                </p>
                                </div>
                            </div>
                                        <div class="text-right">
                                            <p class="text-xs font-semibold text-green-600 dark:text-green-400">
                                                ₱{{ number_format($sale->total_amount, 2) }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $sale->paid_at->diffForHumans() }}
                                            </p>
                                    </div>
                                </div>
                            </div>
                                @empty
                                <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                    </div>
                                    <p class="text-sm font-medium">No recent sales</p>
                                    <p class="text-xs mt-1">Sales will appear here once customers purchase tickets</p>
                                </div>
                                @endforelse
                        </div>
                            
                            @if($recentSales->count() > 5)
                            <div class="px-4 py-2 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                                <a href="{{ route('organizer.events.sales', $event) }}" class="w-full text-xs text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 font-medium text-center block">
                                    View All Sales
                                </a>
                            </div>
                            @endif
                    </div>
                        @endif

                        <!-- Recent Activity (Compact with Max Height) -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg" id="recent-activity-section">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                            <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">Recent Activity</h3>
                            @if($totalActivityCount > 5)
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $totalActivityCount }} total</span>
                                @endif
                        </div>
                        <div class="max-h-80 overflow-y-auto divide-y divide-gray-200 dark:divide-gray-700" id="recent-activity-list">
                            @forelse($recentActivity->take(5) as $activity)
                                <div class="p-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200" data-activity-id="{{ $activity->id }}">
                                    <div class="flex items-start space-x-2">
                                    <div class="flex-shrink-0">
                                            <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full mt-2"></div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                            <div class="text-xs text-gray-900 dark:text-gray-100">
                                                <div class="font-medium">{{ ucfirst(str_replace('_',' ', $activity->action ?? 'Activity')) }}</div>
                                                <div class="text-gray-600 dark:text-gray-300 text-xs mt-0.5">{{ Str::limit($activity->description, 60) }}</div>
                                            </div>
                                            
                                            <div class="flex items-center justify-between mt-2 pt-1 border-t border-gray-100 dark:border-gray-700">
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $activity->created_at->diffForHumans() }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    by {{ $activity->user_id === Auth::id() ? 'You' : ($activity->user ? trim($activity->user->first_name . ' ' . $activity->user->last_name) : 'Unknown') }}
                                                </div>
                                            </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                                <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                    </div>
                                    <p class="text-sm font-medium">No recent activity</p>
                                    <p class="text-xs mt-1">Activity will appear here as you make changes</p>
                            </div>
                            @endforelse
                            </div>
                            
                        @if($totalActivityCount > 5)
                        <div class="px-4 py-2 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                            <a href="{{ route('organizer.events.activity-log', $event) }}" class="w-full text-xs text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 font-medium text-center block">
                                View All Activities
                            </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-4">
                    @php
                        $eventEnded = $event->end_date && now()->gte($event->end_date);
                    @endphp

                    @if($eventEnded && !$event->is_free && (($salesStats['expectedRevenue'] ?? 0) > 0))
                    <!-- Payout Status (Only when event ended - TOP POSITION) -->
                    @php
                        $payout = \App\Models\PayoutRequest::where('event_id', $event->id)->orderBy('created_at', 'desc')->first();
                        $canPayout = $event->end_date && now()->gte($event->end_date) && $isOwner;
                    @endphp
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-emerald-100 dark:bg-emerald-900 rounded-lg flex items-center justify-center mr-2">
                                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                                <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">Payout</h3>
                            </div>
                        </div>
                        <div class="p-4">
                            @if($payout)
                                <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-gray-500">Status</div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ ucfirst(str_replace('_',' ', $payout->status)) }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Net Amount</div>
                                        <div class="text-lg font-semibold text-green-600">₱{{ number_format($payout->net_amount,2) }}</div>
                                    </div>
                                </div>
                                    
                                    @if($payout->requested_at)
                                        <div class="flex items-center justify-between">
                                            <div class="text-xs text-gray-500">Requested</div>
                                            <div class="text-sm text-gray-900 dark:text-gray-100">{{ $payout->requested_at->format('M d, Y') }}</div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-4">
                                    @if($payout->status === 'released' && $canPayout)
                                        <a href="{{ route('organizer.events.payout', $event) }}" class="w-full inline-flex items-center justify-center px-3 py-1.5 rounded-md bg-green-600 hover:bg-green-700 text-white text-sm">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                            </svg>
                                            Collect Payout
                                        </a>
                                    @elseif($payout->status === 'approved' && $canPayout)
                                        <div class="text-center py-2">
                                            <div class="text-sm text-blue-600 dark:text-blue-400">✓ Approved - Waiting for release</div>
                                        </div>
                                @elseif(in_array($payout->status, ['pending','under_review']))
                                        <div class="text-center py-2">
                                            <div class="text-sm text-yellow-600 dark:text-yellow-400">⏳ Under Review</div>
                                        </div>
                                    @elseif($payout->status === 'paid')
                                        <div class="text-center py-2">
                                            <div class="text-sm text-green-600 dark:text-green-400">✓ Payout Completed</div>
                                            @if($payout->processed_at)
                                                <div class="text-xs text-gray-500 mt-1">Completed on {{ $payout->processed_at->format('M d, Y') }}</div>
                                            @endif
                                        </div>
                                    @elseif($payout->status === 'rejected')
                                        <div class="text-center py-2">
                                            <div class="text-sm text-red-600 dark:text-red-400">❌ Rejected</div>
                                        </div>
                                @endif
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-3">No payout created yet.</div>
                                @if($canPayout)
                                        <a href="{{ route('organizer.events.payout', $event) }}" class="w-full inline-flex items-center justify-center px-3 py-1.5 rounded-md bg-qricket-600 hover:bg-qricket-700 text-white text-sm">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                            </svg>
                                            Request Payout
                                        </a>
                                    @else
                                        <div class="text-xs text-gray-500">Payout becomes available after the event ends and if you are the owner.</div>
                                @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Sales Summary -->
                    @if($canManageSales)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mr-2">
                                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">Sales Summary</h3>
                            </div>
                        </div>
                        <div class="p-4 space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Avg Order Value</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100" data-sales-update="averageOrderValue">₱{{ number_format($salesStats['averageOrderValue'], 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">This Month</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100" data-sales-update="thisMonthRevenue">₱{{ number_format($salesStats['thisMonthRevenue'], 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Spent on Additional Tickets</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100" data-sales-update="additionalTicketSpend">₱{{ number_format($salesStats['additionalTicketSpend'] ?? 0, 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Free Tickets Sold</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($salesStats['freeTicketsSold'] ?? 0) }}</span>
                            </div>
                            @if($salesStats['totalCapacity'] > 0)
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Capacity Utilization</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100" data-sales-update="capacityUtilization">{{ number_format($salesStats['capacityUtilization'], 1) }}%</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Event Info -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mr-2">
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">Event Info</h3>
                            </div>
                        </div>
                        <div class="p-4 space-y-3 text-sm">
                            <div class="flex items-start">
                                <div class="w-5 h-5 bg-purple-100 dark:bg-purple-900 rounded flex items-center justify-center mr-2 mt-0.5">
                                    <svg class="w-3 h-3 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Start Date:</span>
                                    <p class="text-gray-600 dark:text-gray-400">{{ $event->start_date->format('M d, Y g:i A') }}</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="w-5 h-5 bg-red-100 dark:bg-red-900 rounded flex items-center justify-center mr-2 mt-0.5">
                                    <svg class="w-3 h-3 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">End Date:</span>
                                    <p class="text-gray-600 dark:text-gray-400">{{ $event->end_date->format('M d, Y g:i A') }}</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="w-5 h-5 bg-green-100 dark:bg-green-900 rounded flex items-center justify-center mr-2 mt-0.5">
                                    <svg class="w-3 h-3 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Location:</span>
                                    <p class="text-gray-600 dark:text-gray-400">{{ $event->location }}</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="w-5 h-5 bg-yellow-100 dark:bg-yellow-900 rounded flex items-center justify-center mr-2 mt-0.5">
                                    <svg class="w-3 h-3 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Category:</span>
                                    <p class="text-gray-600 dark:text-gray-400">{{ $event->category }}</p>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Quick Actions (At the bottom) -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mr-2">
                                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">Quick Actions</h3>
                            </div>
                        </div>
                        <div class="p-4 space-y-2">
                            <a href="{{ route('organizer.events.attendance', $event) }}" 
                               class="flex items-center p-2.5 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                <div class="w-5 h-5 bg-blue-100 dark:bg-blue-900 rounded flex items-center justify-center mr-3">
                                    <svg class="w-3 h-3 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                </div>
                                <span class="text-sm">Manage Attendance</span>
                            </a>
                            
                            <a href="{{ route('organizer.events.tickets', $event) }}" 
                               class="flex items-center p-2.5 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                <div class="w-5 h-5 bg-purple-100 dark:bg-purple-900 rounded flex items-center justify-center mr-3">
                                    <svg class="w-3 h-3 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                </svg>
                                </div>
                                <span class="text-sm">View Tickets</span>
                            </a>
                            
                            <a href="{{ route('organizer.events.staff', $event) }}" 
                               class="flex items-center p-2.5 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                <div class="w-5 h-5 bg-green-100 dark:bg-green-900 rounded flex items-center justify-center mr-3">
                                    <svg class="w-3 h-3 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                                </div>
                                <span class="text-sm">Manage Staff</span>
                            </a>
                            
                            <a href="{{ route('organizer.events.qr', $event) }}" 
                               class="flex items-center p-2.5 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                <div class="w-5 h-5 bg-orange-100 dark:bg-orange-900 rounded flex items-center justify-center mr-3">
                                    <svg class="w-3 h-3 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                </svg>
                                </div>
                                <span class="text-sm">QR Code Scanner</span>
                            </a>
                            
                            <a href="{{ route('organizer.events.news.index', $event) }}" 
                               class="flex items-center p-2.5 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                <div class="w-5 h-5 bg-pink-100 dark:bg-pink-900 rounded flex items-center justify-center mr-3">
                                    <svg class="w-3 h-3 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                </svg>
                                </div>
                                <span class="text-sm">Event News</span>
                            </a>
                            
                            <a href="{{ route('organizer.events.activity-log', $event) }}" 
                               class="flex items-center p-2.5 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                <div class="w-5 h-5 bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center mr-3">
                                    <svg class="w-3 h-3 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                </div>
                                <span class="text-sm">Activity Log</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- External Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    
    <!-- Configuration -->
    <script>
        // Global configuration for event management
        window.EventManageConfig = {
            pusherAppKey: '{{ env("PUSHER_APP_KEY") }}',
            pusherCluster: '{{ env("PUSHER_APP_CLUSTER") }}',
            eventId: '{{ $event->id }}',
            csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            salesTrendChartData: @json($canManageSales && $canViewGraphs && isset($salesTrend) ? $salesTrend : []),
            activityTrendChartData: @json($canViewGraphs && isset($activityTrend) ? $activityTrend : [])
        };
        
        // Global event management state
        window.EventManageState = {
            isInitialized: false,
            components: {
                pusher: null,
                stats: null,
                sales: null,
                ticketPerformance: null,
                attendance: null,
                chart: null
            }
        };
    </script>
    
    <!-- Event Management Dashboard System -->
    <script src="{{ asset('js/events/dashboard/pusher-config.js') }}"></script>
    <script src="{{ asset('js/events/dashboard/stats-updater.js') }}"></script>
    <script src="{{ asset('js/events/dashboard/sales-updater.js') }}"></script>
    <script src="{{ asset('js/events/dashboard/events-updater.js') }}"></script>
    <script src="{{ asset('js/events/dashboard/activity-updater.js') }}"></script>
    <script src="{{ asset('js/events/dashboard/chart-manager.js') }}"></script>
    <script src="{{ asset('js/events/dashboard/index.js') }}"></script>
    
    <!-- Debug and Test -->
    <script>
        // Debug function to test real-time updates
        window.debugEventManage = function() {
            // console.log('=== Event Management Debug ===');
            // console.log('Config:', window.EventManageConfig);
            // console.log('State:', window.EventManageState);
            // console.log('Components:', {
            //     pusher: !!window.EventManagePusher,
            //     stats: !!window.EventManageStats,
            //     sales: !!window.EventManageSales,
            //     ticketPerformance: !!window.EventManageTicketPerformance,
            //     attendance: !!window.EventManageAttendance,
            //     chart: !!window.EventManageChart,
            //     system: !!window.EventManageSystem
            // });
            
            // Test functions available but not auto-executed
            // Uncomment the line below to test stats updates manually
            // if (window.testUpdateEventStats) { window.testUpdateEventStats(); }
        };
        
        // Auto-run debug after 2 seconds
        setTimeout(() => {
            window.debugEventManage();
        }, 2000);
        
        // Test functions for real-time events (remove in production)
        window.testEventView = function() {
            fetch('/test/event-view', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ event_id: {{ $event->id }} })
            })
            .then(response => response.json())
            .then(data => {
                // console.log('Test event view result:', data);
                if (data.success) {
                    alert('Test event-view triggered! Check console for activity chart update.');
                }
            });
        };
        
        window.testEventSave = function() {
            fetch('/test/event-save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ event_id: {{ $event->id }} })
            })
            .then(response => response.json())
            .then(data => {
                // console.log('Test event save result:', data);
                if (data.success) {
                    alert('Test event-saved triggered! Check console for activity chart update.');
                }
            });
        };
        
        window.testTicketPurchase = function() {
            fetch('/test/ticket-purchase', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ event_id: {{ $event->id }} })
            })
            .then(response => response.json())
            .then(data => {
                // console.log('Test ticket purchase result:', data);
                if (data.success) {
                    alert('Test ticket-purchased triggered! Check console for activity chart update.');
                }
            });
        };

        // Fallback inline sales trend renderer (if external module fails or is not present)
        document.addEventListener('DOMContentLoaded', function(){
            // console.log('Chart initialization starting...');
            // console.log('Chart.js available:', typeof Chart !== 'undefined');
            // console.log('EventManageConfig:', window.EventManageConfig);
            
            try {
                if (window.EventManageState?.components?.chart) return; // external manager already created chart
                const canvas = document.getElementById('salesTrendChart');
                const dataset = (window.EventManageConfig && Array.isArray(window.EventManageConfig.salesTrendChartData))
                    ? window.EventManageConfig.salesTrendChartData
                    : [];
                // console.log('Sales trend dataset:', dataset);
                if (!canvas || typeof Chart === 'undefined') return;
                const ctx = canvas.getContext('2d');
                const labels = dataset.length > 0 ? dataset.map(d => d.display_date || d.date) : ['No data'];
                const revenue = dataset.length > 0 ? dataset.map(d => Number(d.revenue || 0)) : [0];
                const tickets = dataset.length > 0 ? dataset.map(d => Number(d.tickets_sold || 0)) : [0];
                window.__salesTrendChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [
                            {
                                label: 'Revenue',
                                data: revenue,
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16,185,129,0.2)',
                                yAxisID: 'y',
                                tension: 0.3,
                                pointRadius: 2
                            },
                            {
                                label: 'Tickets Sold',
                                data: tickets,
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59,130,246,0.15)',
                                yAxisID: 'y1',
                                tension: 0.3,
                                pointRadius: 2
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: { legend: { position: 'bottom' } },
                        scales: {
                            y: {
                                type: 'linear',
                                position: 'left',
                                ticks: { callback: (v) => '₱' + Number(v).toLocaleString() },
                                grid: { drawOnChartArea: true }
                            },
                            y1: {
                                type: 'linear',
                                position: 'right',
                                grid: { drawOnChartArea: false }
                            }
                        }
                    }
                });
                // console.log('Sales trend chart created successfully');
            } catch (e) { /* console.error('Sales trend fallback error', e); */ }
            
            // Activity trend chart initialization
            try {
                const activityCanvas = document.getElementById('activityTrendChart');
                const activityDataset = (window.EventManageConfig && Array.isArray(window.EventManageConfig.activityTrendChartData))
                    ? window.EventManageConfig.activityTrendChartData
                    : [];
                // console.log('Activity trend dataset:', activityDataset);
                if (!activityCanvas || typeof Chart === 'undefined') return;
                
                const activityCtx = activityCanvas.getContext('2d');
                const activityLabels = activityDataset.length > 0 ? activityDataset.map(d => d.date) : ['No data'];
                const views = activityDataset.length > 0 ? activityDataset.map(d => Number(d.views || 0)) : [0];
                const saves = activityDataset.length > 0 ? activityDataset.map(d => Number(d.saves || 0)) : [0];
                const purchases = activityDataset.length > 0 ? activityDataset.map(d => Number(d.purchases || 0)) : [0];
                const activities = activityDataset.length > 0 ? activityDataset.map(d => Number(d.activities || 0)) : [0];
                
                window.__activityTrendChart = new Chart(activityCtx, {
                    type: 'line',
                    data: {
                        labels: activityLabels,
                        datasets: [
                            {
                                label: 'Event Views',
                                data: views,
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16,185,129,0.2)',
                                yAxisID: 'y',
                                tension: 0.3,
                                pointRadius: 2
                            },
                            {
                                label: 'Event Saves',
                                data: saves,
                                borderColor: '#8b5cf6',
                                backgroundColor: 'rgba(139,92,246,0.2)',
                                yAxisID: 'y',
                                tension: 0.3,
                                pointRadius: 2
                            },
                            {
                                label: 'Ticket Purchases',
                                data: purchases,
                                borderColor: '#f59e0b',
                                backgroundColor: 'rgba(245,158,11,0.2)',
                                yAxisID: 'y',
                                tension: 0.3,
                                pointRadius: 2
                            },
                            {
                                label: 'Activities',
                                data: activities,
                                borderColor: '#ef4444',
                                backgroundColor: 'rgba(239,68,68,0.2)',
                                yAxisID: 'y',
                                tension: 0.3,
                                pointRadius: 2
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: { 
                            legend: { position: 'bottom' },
                            title: {
                                display: true,
                                text: 'Event Activity Over Time'
                            }
                        },
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'Count'
                                }
                            }
                        }
                    }
                });
                // console.log('Activity trend chart created successfully');
            } catch (e) { /* console.error('Activity trend chart error', e); */ }
            
            // Real-time update functions
            window.updateActivityTrendChart = function(newData) {
                try {
                    if (!window.__activityTrendChart) return;
                    
                    // console.log('Updating activity trend chart with:', newData);
                    
                    const chart = window.__activityTrendChart;
                    const labels = newData.length > 0 ? newData.map(d => d.date) : ['No data'];
                    const views = newData.length > 0 ? newData.map(d => Number(d.views || 0)) : [0];
                    const saves = newData.length > 0 ? newData.map(d => Number(d.saves || 0)) : [0];
                    const purchases = newData.length > 0 ? newData.map(d => Number(d.purchases || 0)) : [0];
                    const activities = newData.length > 0 ? newData.map(d => Number(d.activities || 0)) : [0];
                    
                    chart.data.labels = labels;
                    chart.data.datasets[0].data = views;      // Event Views
                    chart.data.datasets[1].data = saves;     // Event Saves
                    chart.data.datasets[2].data = purchases;  // Ticket Purchases
                    chart.data.datasets[3].data = activities; // Activities
                    
                    chart.update('none'); // Update without animation for real-time feel
                    // console.log('Activity trend chart updated successfully');
                } catch (e) {
                    // console.error('Error updating activity trend chart:', e);
                }
            };
            
            window.updateSalesTrendChart = function(newData) {
                try {
                    if (!window.__salesTrendChart) return;
                    
                    // console.log('Updating sales trend chart with:', newData);
                    
                    const chart = window.__salesTrendChart;
                    const labels = newData.length > 0 ? newData.map(d => d.display_date || d.date) : ['No data'];
                    const revenue = newData.length > 0 ? newData.map(d => Number(d.revenue || 0)) : [0];
                    const tickets = newData.length > 0 ? newData.map(d => Number(d.tickets_sold || 0)) : [0];
                    
                    chart.data.labels = labels;
                    chart.data.datasets[0].data = revenue;   // Revenue
                    chart.data.datasets[1].data = tickets;   // Tickets Sold
                    
                    chart.update('none'); // Update without animation for real-time feel
                    // console.log('Sales trend chart updated successfully');
                } catch (e) {
                    // console.error('Error updating sales trend chart:', e);
                }
            };
            
            // Global update function for both charts
            window.updateEventCharts = function(salesData, activityData) {
                if (salesData) {
                    window.updateSalesTrendChart(salesData);
                }
                if (activityData) {
                    window.updateActivityTrendChart(activityData);
                }
            };
            
            // Real-time Pusher event listeners for activity updates
            if (window.EventManagePusher) {
                // Listen for event view activities
                window.EventManagePusher.channel('event-updates').listen('event-viewed', (data) => {
                    // console.log('Event viewed:', data);
                    if (data.event_id == {{ $event->id }}) {
                        window.fetchActivityTrendData();
                    }
                });
                
                // Listen for event save activities
                window.EventManagePusher.channel('event-updates').listen('event-saved', (data) => {
                    // console.log('Event saved:', data);
                    if (data.event_id == {{ $event->id }}) {
                        window.fetchActivityTrendData();
                    }
                });
                
                // Listen for ticket purchase activities
                window.EventManagePusher.channel('event-updates').listen('ticket-purchased', (data) => {
                    // console.log('Ticket purchased:', data);
                    if (data.event_id == {{ $event->id }}) {
                        window.fetchActivityTrendData();
                        // Also update sales chart if function exists
                        if (typeof window.fetchSalesTrendData === 'function') {
                            window.fetchSalesTrendData();
                        }
                    }
                });
                
                // Listen for management activities
                window.EventManagePusher.channel('event-updates').listen('event-activity', (data) => {
                    // console.log('Event activity:', data);
                    if (data.event_id == {{ $event->id }}) {
                        window.fetchActivityTrendData();
                    }
                });
            }
            
            // Fallback data fetching function (only used when Pusher events trigger it)
            window.fetchActivityTrendData = function() {
                fetch(`{{ route('organizer.events.activity-trend', $event) }}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        window.updateActivityTrendChart(data.data);
                        // console.log('Activity trend data updated via real-time event:', data.timestamp);
                    }
                })
                .catch(error => {
                    // console.error('Error fetching activity trend data:', error);
                });
            };
        });

        // Lightweight realtime numbers via unified endpoint
        (function(){
            const eventId = {{ $event->id }};
            const userId = {{ auth()->id() }};
            
            function refreshEventNumbers(){
                try{
                    var url = new URL('{{ route('realtime.metrics') }}', window.location.origin);
                    url.searchParams.set('scope','event_sales');
                    url.searchParams.set('event_id','{{ $event->id }}');
                    fetch(url.toString(), { credentials:'same-origin' })
                        .then(function(r){ return r.json(); })
                        .then(function(data){
                            if(!data) return;
                            var revEl = document.querySelector('[data-stat="event-total-revenue"]');
                            if(revEl){ revEl.textContent = '₱' + (Number(data.ticketRevenue||0)).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2}); }
                            var expEl = document.querySelector('[data-stat="event-expected-revenue"]');
                            // Use expectedRevenue if available (calculated correctly), fallback to ticketNetRevenue (clamped)
                            var expectedRev = data.expectedRevenue !== undefined ? data.expectedRevenue : Math.max(0, data.ticketNetRevenue || 0);
                            if(expEl){ expEl.textContent = '₱' + (Number(expectedRev)).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2}); }
                        }).catch(function(){});
                }catch(e){}
            }
            
            function updateEventMetricsFromEvent(eventData) {
                if (!eventData) return;
                
                // Only process if it's for this event
                if (String(eventData.event_id) !== String(eventId)) return;
                
                // Update from event_stats if available
                if (eventData.event_stats) {
                    // Update total revenue
                    if (eventData.event_stats.total_revenue !== undefined) {
                        var revEl = document.querySelector('[data-stat="event-total-revenue"]');
                        if (revEl) {
                            revEl.textContent = '₱' + parseFloat(eventData.event_stats.total_revenue).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
                        }
                    }
                    
                    // Update tickets sold
                    if (eventData.event_stats.total_tickets_sold !== undefined) {
                        var ticketsEl = document.querySelector('[data-stat="event-tickets-sold"]');
                        if (ticketsEl) {
                            ticketsEl.textContent = Number(eventData.event_stats.total_tickets_sold).toLocaleString();
                        }
                    }
                }
                
                // Update from sales_data if available
                if (eventData.sales_data) {
                    // Update total orders (increment count)
                    if (eventData.sales_data.total_amount !== undefined) {
                        var ordersEl = document.querySelector('[data-stat="event-total-orders"]');
                        if (ordersEl) {
                            var currentOrders = parseInt(ordersEl.textContent.replace(/,/g, '')) || 0;
                            ordersEl.textContent = (currentOrders + 1).toLocaleString();
                        }
                    }
                }
                
                // Always refresh full metrics after update
                setTimeout(refreshEventNumbers, 500);
            }
            
            document.addEventListener('visibilitychange', function(){ if(!document.hidden) refreshEventNumbers(); });
            window.addEventListener('load', refreshEventNumbers);
            
            // Real-time updates are now handled by event-overview-realtime.js
        })();
    </script>
    
    <!-- Pass event ID and user ID to JavaScript for real-time updates -->
    <script>
        window.EVENT_ID = {{ $event->id }};
        window.USER_ID = {{ auth()->id() }};
    </script>
    
    <!-- Real-time event overview updates -->
    <script src="{{ asset('js/event-overview-realtime.js') }}?v={{ time() }}"></script>
</x-unified-layout>
