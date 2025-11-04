@php
    $user = auth()->user();
    $staff = $event->staff->firstWhere('user_id', $user?->id);
    $isOwner = $user && (int)$event->user_id === (int)$user->id;
    $staffWelcome = $staffNotes['staff_welcome_message'] ?? null;
    $staffInstructions = $staffNotes['staff_instructions'] ?? null;
@endphp
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <div class="lg:col-span-3 space-y-6">
                @if($canManageSales)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</p>
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" data-stat="event-total-revenue">₱{{ number_format($salesStats['totalRevenue'], 2) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tickets Sold</p>
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" data-stat="event-tickets-sold">{{ number_format($salesStats['totalTicketsSold']) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Orders</p>
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" data-stat="event-total-orders">{{ number_format($salesStats['totalOrders']) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-teal-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
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
                        <div class="h-64 md:h-80"><canvas id="salesTrendChart"></canvas></div>
                    </div>
                </div>

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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $ticket['name'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">₱{{ number_format($ticket['price'], 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ number_format($ticket['total_sold']) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">@if($ticket['remaining'] !== null){{ number_format($ticket['remaining']) }}@else<span class="text-green-600 dark:text-green-400">Unlimited</span>@endif</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min($ticket['utilization'], 100) }}%"></div>
                                                </div>
                                                <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">{{ number_format($ticket['utilization'], 1) }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">₱{{ number_format($ticket['total_revenue'], 2) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No tickets configured</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure tickets to see performance metrics.</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Activity (kept in main column) -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center"><h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Recent Activity</h3>@if(($totalActivityCount ?? 0) > 10)<span class="text-sm text-gray-500 dark:text-gray-400">{{ $totalActivityCount }} total activities</span>@endif</div>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700" id="recent-activity-list">
                        @forelse($recentActivity as $activity)
                        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200" data-activity-id="{{ $activity->id }}">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0"><div class="w-2 h-2 bg-emerald-500 rounded-full mt-2"></div></div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm text-gray-900 dark:text-gray-100 mb-2"><div class="font-medium">{{ ucfirst(str_replace('_',' ', $activity->action ?? 'Activity')) }}</div><div class="text-gray-600 dark:text-gray-300 text-xs mt-1">{{ $activity->description }}</div></div>
                                    <div class="flex items-center justify-between mt-3 pt-2 border-t border-gray-100 dark:border-gray-700"><div class="text-xs text-gray-500 dark:text-gray-400">{{ $activity->created_at->diffForHumans() }}</div><div class="text-xs text-gray-500 dark:text-gray-400">by {{ $activity->user_id === Auth::id() ? 'You' : ($activity->user ? trim($activity->user->first_name . ' ' . $activity->user->last_name) : 'Unknown') }}</div></div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="p-8 text-center text-gray-500 dark:text-gray-400"><div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div><p class="text-sm font-medium">No recent activity</p><p class="text-xs mt-1">Activity will appear here as you make changes</p></div>
                        @endforelse
                    </div>
                    @if(($totalActivityCount ?? 0) > 10)
                    <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50"><button id="load-more-activity" class="w-full text-sm text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 font-medium">Load More Activities</button></div>
                    @endif
                </div>
            </div>

            <!-- Sidebar: Payout, Sales Summary, Quick Actions, Event Info -->
            @php
                $payout = \App\Models\PayoutRequest::where('event_id', $event->id)->orderBy('created_at', 'desc')->first();
                $canPayout = $event->end_date && now()->gte($event->end_date) && $isOwner;
                $eventEnded = $event->end_date && now()->gte($event->end_date);
            @endphp
            <div class="space-y-6">
                @if($eventEnded && !$event->is_free && (($salesStats['expectedRevenue'] ?? 0) > 0))
                <!-- Payout -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Payout</h3>
                    </div>
                    <div class="p-6">
                        @if($payout)
                            <div class="space-y-4">
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
                                        <div class="text-sm text-gray-900 dark:text-gray-100">{{ $payout->requested_at->format('M d, Y g:i A') }}</div>
                                    </div>
                                @endif

                                @if($payout->notes)
                                    <div class="mt-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <div class="text-xs text-gray-500 mb-1">Notes</div>
                                        <div class="text-sm text-gray-700 dark:text-gray-300">{{ Str::limit($payout->notes, 100) }}</div>
                                    </div>
                                @endif
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-4">
                                @if($payout->status === 'released' && $canPayout)
                                    <button onclick="openCollectPayoutModal({{ $event->id }})" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-md bg-green-600 hover:bg-green-700 text-white text-sm font-medium">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                        </svg>
                                        Collect Payout
                                    </button>
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
                            <div class="text-center py-4">
                                <div class="text-sm text-gray-600 dark:text-gray-400 mb-3">No payout created yet.</div>
                                @if($canPayout)
                                    <button onclick="openPayoutRequestModal({{ $event->id }})" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-md bg-qricket-600 hover:bg-qricket-700 text-white text-sm font-medium">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Request Payout
                                    </button>
                                @else
                                    <div class="text-xs text-gray-500">Payout becomes available after the event ends and if you are the owner.</div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Sales Summary -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700"><h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Sales Summary</h3></div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-center"><span class="text-sm font-medium text-gray-700 dark:text-gray-300">Avg Order Value</span><span class="text-sm font-semibold text-gray-900 dark:text-gray-100" data-sales-update="averageOrderValue">₱{{ number_format($salesStats['averageOrderValue'], 2) }}</span></div>
                        <div class="flex justify-between items-center"><span class="text-sm font-medium text-gray-700 dark:text-gray-300">This Month</span><span class="text-sm font-semibold text-gray-900 dark:text-gray-100" data-sales-update="thisMonthRevenue">₱{{ number_format($salesStats['thisMonthRevenue'], 2) }}</span></div>
                        <div class="flex justify-between items-center"><span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Spent on Additional Tickets</span><span class="text-sm font-semibold text-gray-900 dark:text-gray-100" data-sales-update="additionalTicketSpend">₱{{ number_format($salesStats['additionalTicketSpend'] ?? 0, 2) }}</span></div>
                        <div class="flex justify-between items-center"><span class="text-sm font-medium text-gray-700 dark:text-gray-300">Free Tickets Sold</span><span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($salesStats['freeTicketsSold'] ?? 0) }}</span></div>
                        @if(($salesStats['totalCapacity'] ?? 0) > 0)
                        <div class="flex justify-between items-center"><span class="text-sm font-medium text-gray-700 dark:text-gray-300">Capacity Utilization</span><span class="text-sm font-semibold text-gray-900 dark:text-gray-100" data-sales-update="capacityUtilization">{{ number_format($salesStats['capacityUtilization'], 1) }}%</span></div>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700"><h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Quick Actions</h3></div>
                    <div class="p-6 space-y-3">
                        <a href="{{ route('organizer.events.attendance', $event) }}" class="flex items-center p-3 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"><svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>Manage Attendance</a>
                        <a href="{{ route('organizer.events.tickets', $event) }}" class="flex items-center p-3 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"><svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>View Tickets</a>
                        <a href="{{ route('organizer.events.staff', $event) }}" class="flex items-center p-3 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"><svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path></svg>Manage Staff</a>
                        <a href="{{ route('organizer.events.qr', $event) }}" class="flex items-center p-3 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"><svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1z"></path></svg>QR Code Scanner</a>
                        <a href="{{ route('organizer.events.news.index', $event) }}" class="flex items-center p-3 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"><svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>Event News</a>
                        <a href="{{ route('organizer.events.activity-log', $event) }}" class="flex items-center p-3 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"><svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>Activity Log</a>
                    </div>
                </div>

                <!-- Event Info -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700"><h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Event Info</h3></div>
                    <div class="p-6 space-y-3 text-sm">
                        <div><span class="font-medium text-gray-700 dark:text-gray-300">Start Date:</span><p class="text-gray-600 dark:text-gray-400">{{ $event->start_date->format('M d, Y g:i A') }}</p></div>
                        <div><span class="font-medium text-gray-700 dark:text-gray-300">End Date:</span><p class="text-gray-600 dark:text-gray-400">{{ $event->end_date->format('M d, Y g:i A') }}</p></div>
                        <div><span class="font-medium text-gray-700 dark:text-gray-300">Location:</span><p class="text-gray-600 dark:text-gray-400">{{ $event->location }}</p></div>
                        <div><span class="font-medium text-gray-700 dark:text-gray-300">Category:</span><p class="text-gray-600 dark:text-gray-400">{{ $event->category }}</p></div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>

    <!-- Include the payout modals -->
    @include('organizer.events.partials.payout-modals')
