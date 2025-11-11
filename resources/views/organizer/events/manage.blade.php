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
                $eventEnded = $event->end_date && now()->gte($event->end_date);
            @endphp
            <div class="space-y-6">
                <!-- Main Content -->
                <div class="space-y-6">
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

                    <!-- Three Column Layout: Ticket Performance, Rules & Instructions, Recent Sales/Activity -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Left Column: Ticket Performance -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Ticket Performance</h3>
                                
                                @if($canManageSales && $ticketPerformance->count() > 0)
                                    <div class="space-y-4">
                                        @foreach($ticketPerformance as $ticket)
                                            <div>
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $ticket['name'] }}</span>
                                                </div>
                                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                                    @php
                                                        $maxTickets = $ticket['total_sold'] + ($ticket['remaining'] ?? 0);
                                                        $percentage = $maxTickets > 0 ? ($ticket['total_sold'] / $maxTickets) * 100 : 0;
                                                    @endphp
                                                    <div class="bg-gray-400 dark:bg-gray-600 h-2.5 rounded-full" style="width: {{ min($percentage, 100) }}%"></div>
                                                </div>
                                            </div>
                                        @endforeach
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

                        <!-- Middle Column: Rules & Instructions -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Rules & Instructions</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Event Guidelines and Staff Expectations</p>
                                @if(!empty($staffInstructions))
                                    <div class="text-sm text-gray-700 dark:text-gray-300 space-y-2">
                                        {!! nl2br(e($staffInstructions)) !!}
                                    </div>
                                @else
                                    <div class="text-sm text-gray-700 dark:text-gray-300 space-y-2">
                                        <ul class="list-disc list-inside space-y-2">
                                            <li>Arrive at least 30 minutes before your assigned schedule.</li>
                                            <li>Always wear your staff ID badge and appropriate attire.</li>
                                            <li>Check your designated area and make sure everything is ready before attendees arrive.</li>
                                            <li>Maintain a professional and friendly attitude when interacting with guests and participants.</li>
                                            <li>Report any issues or emergencies immediately to the event organizer.</li>
                                            <li>Stay updated on announcements in the staff communication channel or dashboard.</li>
                                            <li>After the event, assist with cleanup or post-event wrap-up as directed.</li>
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Right Column: Recent Sales and Activity -->
                        <div class="space-y-4">
                            @if($canManageSales)
                            <!-- Recent Sales -->
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg" id="recent-sales-section">
                                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                    <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">Recent Sales</h3>
                                </div>
                                <div class="max-h-64 overflow-y-auto divide-y divide-gray-200 dark:divide-gray-700" id="recent-sales-list">
                                    @forelse($recentSales->take(5) as $sale)
                                    <div class="p-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200" data-sale-id="{{ $sale->id }}">
                                        <div class="flex items-center space-x-2">
                                            <div class="flex-shrink-0">
                                                <div class="w-1.5 h-1.5 bg-green-500 rounded-full"></div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs font-medium text-gray-900 dark:text-gray-100 truncate">
                                                    {{ $sale->user->first_name }} {{ $sale->user->last_name }}
                                                </p>
                                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                                    {{ $sale->quantity }}x {{ $sale->ticketType->name ?? 'Ticket' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="p-6 text-center text-gray-500 dark:text-gray-400">
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

                            <!-- Recent Activity -->
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg" id="recent-activity-section">
                                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                    <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">Recent Activity</h3>
                                    @if($totalActivityCount > 5)
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $totalActivityCount }} total</span>
                                    @endif
                                </div>
                                <div class="max-h-64 overflow-y-auto divide-y divide-gray-200 dark:divide-gray-700" id="recent-activity-list">
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
                </div>

                <!-- Payout Section (Only for owners, only when event has ended) -->
                @if($isOwner && $eventEnded && !$event->is_free)
                    @php
                        // Check if there's revenue from sales stats, or if there's a payout request (which indicates revenue existed)
                        $hasRevenue = ($salesStats && ($salesStats['totalRevenue'] ?? 0) > 0) || ($payoutRequest !== null);
                    @endphp
                    @if($hasRevenue)
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
                            @if($payoutRequest)
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Status</div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ ucfirst(str_replace('_',' ', $payoutRequest->status)) }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Net Amount</div>
                                            <div class="text-lg font-semibold text-green-600 dark:text-green-400">₱{{ number_format($payoutRequest->net_amount, 2) }}</div>
                                        </div>
                                    </div>
                                    
                                    @if($payoutRequest->requested_at)
                                        <div class="flex items-center justify-between">
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Requested</div>
                                            <div class="text-sm text-gray-900 dark:text-gray-100">{{ $payoutRequest->requested_at->format('M d, Y') }}</div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-4">
                                    @if($payoutRequest->status === 'released' && $canPayout)
                                        <a href="{{ route('organizer.events.payout', $event) }}" class="w-full inline-flex items-center justify-center px-3 py-1.5 rounded-md bg-green-600 hover:bg-green-700 text-white text-sm">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                            </svg>
                                            Collect Payout
                                        </a>
                                    @elseif($payoutRequest->status === 'approved')
                                        <div class="text-center py-2">
                                            <div class="text-sm text-blue-600 dark:text-blue-400">✓ Approved - Waiting for release</div>
                                        </div>
                                    @elseif(in_array($payoutRequest->status, ['pending','under_review']))
                                        <div class="text-center py-2">
                                            <div class="text-sm text-yellow-600 dark:text-yellow-400">⏳ Under Review</div>
                                        </div>
                                    @elseif($payoutRequest->status === 'paid')
                                        <div class="text-center py-2">
                                            <div class="text-sm text-green-600 dark:text-green-400">✓ Payout Completed</div>
                                            @if($payoutRequest->processed_at)
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Completed on {{ $payoutRequest->processed_at->format('M d, Y') }}</div>
                                            @endif
                                        </div>
                                    @elseif($payoutRequest->status === 'rejected')
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
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Payout becomes available after the event ends.</div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif
                @endif
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
