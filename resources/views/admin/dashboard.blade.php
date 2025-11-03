<x-admin-layout title="Admin Dashboard - Qrickett" page-title="Admin Dashboard">
    <x-slot name="header">
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Welcome back, {{ Auth::guard('admin')->user()->username }}!</h2>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Here's what's happening with your platform today.</p>
        </div>
    </x-slot>

    <!-- Success Message -->
    @if (session('success'))
        <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-green-400 dark:text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <!-- Top Row: Left (Users + Events) and Right Sidebar -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Left: span 2 with stacked cards -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Real-time Users Summary (Grouped) -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Logged-in Users -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Logged-in Users</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" id="logged-users-count">{{ $userCounts['users'] }}</p>
                            <div class="flex items-center mt-1">
                                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse" id="connection-indicator"></div>
                                <span class="text-xs text-green-600 dark:text-green-400 ml-2" id="connection-status">Live</span>
                            </div>
                        </div>
                    </div>
                    <!-- Guest Users -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Guest Users</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" id="guest-users-count">{{ $userCounts['guests'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Live</p>
                        </div>
                    </div>
                    <!-- Total Users -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Users</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($stats['total_users']) }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $userGrowthStats['new_users_today'] }} new today</p>
                        </div>
                    </div>
                    <!-- Verified Organizers -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Verified Organizers</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($stats['verified_organizers']) }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $stats['pending_verifications'] }} pending</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Event Statistics (Grouped) -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Events</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($eventStats['total_events']) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $eventStats['events_today'] }} created today</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Events</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($eventStats['active_events']) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $eventStats['events_this_month'] }} this month</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Approval</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($eventStats['pending_approval']) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Awaiting review</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Draft Events</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($eventStats['draft_events']) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">In progress</p>
                    </div>
                </div>
            </div>

            <!-- User Activity Chart (moved up to fill space) -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">User Activity</h3>
                    <div class="flex items-center space-x-2">
                        <button type="button" data-range="14d" class="px-2.5 py-1 text-[11px] rounded-md border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 range-btn active">14d</button>
                        <button type="button" data-range="month" class="px-2.5 py-1 text-[11px] rounded-md border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 range-btn">Month</button>
                        <button type="button" data-range="year" class="px-2.5 py-1 text-[11px] rounded-md border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 range-btn">Year</button>
                    </div>
                </div>
                <div class="relative h-72">
                    <canvas id="user-activity-chart" class="w-full h-full"></canvas>
                </div>
            </div>
        </div>

        <!-- Right: Sidebar stack -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Qricket Feedback Summary (replacing verification requests) -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Website Feedback</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($feedbackStats['total_feedback']) }}</p>
                    </div>
                    <div class="w-8 h-8 bg-teal-100 dark:bg-teal-900 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between">
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $feedbackStats['feedback_today'] }} today</p>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Avg: {{ number_format(($feedbackStats['average_navigation'] + $feedbackStats['average_design'] + $feedbackStats['average_performance']) / 3, 1) }}/5
                    </div>
                </div>
            </div>

            <!-- Event Approvals -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Event Approvals</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($eventApprovalStats['pending_event_approvals']) }}</p>
                    </div>
                    <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between">
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $eventApprovalStats['events_submitted_today'] }} submitted today</p>
                    <a href="{{ route('admin.events.approval.index') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-yellow-600 hover:bg-yellow-700 text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">Review</a>
                </div>
            </div>

            <!-- Support Tickets -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Support Tickets</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($supportStats['open_support_tickets']) }}</p>
                    </div>
                    <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 100 19.5 9.75 9.75 0 000-19.5z"></path></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between">
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $supportStats['support_tickets_today'] }} today</p>
                    <a href="{{ route('admin.support') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-orange-600 hover:bg-orange-700 text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">View</a>
                </div>
            </div>

            <!-- Refund Requests -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Refund Requests</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($refundStats['total_refund_requests']) }}</p>
                    </div>
                    <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m5 14v-5a2 2 0 00-2-2H6a2 2 0 00-2 2v5a2 2 0 002 2h12a2 2 0 002-2z"/></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between">
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($refundStats['pending_refunds']) }} pending</p>
                    <a href="{{ route('admin.refunds.index') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-red-600 hover:bg-red-700 text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">Manage</a>
                </div>
            </div>
        </div>
    </div>


    

    <!-- Sales & Revenue Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Qricket Total Revenue</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">₱{{ number_format($salesStats['admin_total_revenue'], 2) }}</p>
                </div>
                <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">₱{{ number_format($salesStats['revenue_today'], 2) }} today</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Qrciket Ticket Revenue</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">₱{{ number_format($salesStats['admin_ticket_revenue'], 2) }}</p>
                </div>
                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ number_format($salesStats['ticket_orders_paid']) }} paid orders</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Subscription Revenue</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">₱{{ number_format($salesStats['total_subscription_revenue'], 2) }}</p>
                </div>
                <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ number_format($salesStats['subscriptions_active']) }} active</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Additional Ticket Revenue (Net)</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">₱{{ number_format($salesStats['additional_ticket_revenue'] ?? 0, 2) }}</p>
                </div>
                <div class="w-8 h-8 bg-teal-100 dark:bg-teal-900 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1"></path>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ number_format($salesStats['additional_ticket_purchases'] ?? 0) }} purchases</p>
        </div>
    </div>

    <!-- Event Approval & Verification Statistics (removed duplicates) -->

    <!-- Sales Trends -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Ticket Sales (30d)</h3>
            <canvas id="ticketTrendChart" height="120"></canvas>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Subscription Revenue (30d)</h3>
            <canvas id="subTrendChart" height="120"></canvas>
        </div>
    </div>

    <!-- Pending Organizer Verifications -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Pending Organizer Verifications</h3>
            <a href="{{ route('admin.organizer-verifications.index') }}" class="text-qricket-600 dark:text-qricket-400 hover:text-qricket-700 dark:hover:text-qricket-300 text-sm font-medium">
                View All
            </a>
        </div>
        
        @if($pendingVerifications->count() > 0)
            <div class="space-y-3">
                @foreach($pendingVerifications->take(5) as $verification)
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-qricket-100 dark:bg-qricket-900 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-qricket-600 dark:text-qricket-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $verification->full_name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $verification->organization_name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $verification->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                {{ ucfirst(str_replace('_', ' ', $verification->document_type)) }}
                            </span>
                            <a href="{{ route('admin.organizer-verifications.show', $verification->id) }}" 
                               class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-qricket-600 hover:bg-qricket-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-qricket-500">
                                Review
                            </a>
                        </div>
                    </div>
                @endforeach
                
                @if($pendingVerifications->count() > 5)
                    <div class="text-center pt-2">
                        <a href="{{ route('admin.organizer-verifications.index') }}" 
                           class="text-sm text-qricket-600 dark:text-qricket-400 hover:text-qricket-700 dark:hover:text-qricket-300">
                            View {{ $pendingVerifications->count() - 5 }} more verification{{ $pendingVerifications->count() - 5 > 1 ? 's' : '' }}
                        </a>
                    </div>
                @endif
            </div>
        @else
            <div class="text-center py-8">
                <svg class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-gray-500 dark:text-gray-400">No pending organizer verifications</p>
            </div>
        @endif
    </div>

    <!-- Recent Feedback Comments -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Website Feedback</h3>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                {{ $feedbackStats['recent_feedback'] }} comments in last 30 days
            </div>
        </div>
        
        @if($feedbackStats['recent_comments']->count() > 0)
            <div class="space-y-4">
                @foreach($feedbackStats['recent_comments'] as $comment)
                    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 bg-teal-100 dark:bg-teal-900 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $comment->user ? $comment->user->first_name . ' ' . $comment->user->last_name : 'Anonymous User' }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $comment->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                        <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                            {{ Str::limit($comment->additional_comments, 200) }}
                        </p>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <svg class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                </svg>
                <p class="text-gray-500 dark:text-gray-400">No recent feedback comments</p>
            </div>
        @endif
    </div>

    <!-- Recent Activity & Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Activity -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-3 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">Recent Activity</h3>
            </div>
            <div class="p-4">
                @if($recentLogs->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentLogs as $log)
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    @if($log->action === 'login_success')
                                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    @elseif($log->action === 'logout')
                                        <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                            </svg>
                                        </div>
                                    @elseif($log->action === 'login_attempt')
                                        <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    @else
                                        <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $log->admin->username }} • {{ $log->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">No recent activity</p>
                @endif
            </div>
        </div>

        <!-- Top Earning Events -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-3 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">Top Earning Events</h3>
            </div>
            <div class="p-4">
                @if(isset($topEarningEvents) && $topEarningEvents->count() > 0)
                    <div class="space-y-3">
                        @foreach($topEarningEvents->take(5) as $event)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $event->title ?? 'Untitled Event' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $event->user?->first_name }} {{ $event->user?->last_name }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-green-600 dark:text-green-400">₱{{ number_format($event->total_revenue ?? 0, 2) }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $event->tickets_sold ?? 0 }} tickets</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 text-center">
                        <a href="{{ route('admin.events.approval.index') }}" class="text-sm text-qricket-600 dark:text-qricket-400 hover:text-qricket-700 dark:hover:text-qricket-300">View All Events</a>
                    </div>
                @else
                    <div class="text-center py-6">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No earning events</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Events will appear here once they start generating revenue.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Real-time User Counter + Chart Script -->
    <script src="{{ asset('js/admin/dashboard.js') }}"></script>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            const ticketCtx = document.getElementById('ticketTrendChart');
            if (ticketCtx) {
                new Chart(ticketCtx, {
                    type: 'line',
                    data: {
                        labels: @json($ticketTrendLabels ?? []),
                        datasets: [
                            {label:'Revenue', data:@json($ticketTrendRevenue ?? []), borderColor:'#10b981', backgroundColor:'rgba(16,185,129,0.1)', tension:0.3, yAxisID:'y'},
                            {label:'Orders', data:@json($ticketTrendOrders ?? []), borderColor:'#6366f1', backgroundColor:'rgba(99,102,241,0.1)', tension:0.3, yAxisID:'y1'}
                        ]
                    },
                    options: {responsive:true, scales:{y:{beginAtZero:true}, y1:{beginAtZero:true, position:'right'}}}
                });
            }
            const subCtx = document.getElementById('subTrendChart');
            if (subCtx) {
                new Chart(subCtx, {
                    type: 'line',
                    data: { labels:@json($subTrendLabels ?? []), datasets:[{label:'Revenue', data:@json($subTrendRevenue ?? []), borderColor:'#f59e0b', backgroundColor:'rgba(245,158,11,0.1)', tension:0.3}] },
                    options: {responsive:true, scales:{y:{beginAtZero:true}}}
                });
            }
            // Mini doughnut for approvals vs rejections (today)
            const approvalMini = document.getElementById('events-approval-mini-chart');
            if (approvalMini) {
                new Chart(approvalMini, {
                    type: 'doughnut',
                    data: {
                        labels: ['Approved','Rejected'],
                        datasets: [{
                            data: [
                                {{ (int)($eventApprovalStats['events_approved_today'] ?? 0) }},
                                {{ (int)($eventApprovalStats['events_rejected_today'] ?? 0) }}
                            ],
                            backgroundColor: ['#10b981','#ef4444'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        cutout: '70%',
                        plugins: { legend: { display: false } }
                    }
                });
            }
        });
    </script>
</x-admin-layout>

@push('scripts')
<script src="https://js.pusher.com/8.2/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
<script>
    (function(){
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        function refreshMetrics(){
            const url = new URL('{{ route('realtime.metrics') }}', window.location.origin);
            url.searchParams.set('scope','admin_dashboard');
            fetch(url.toString(), { credentials:'same-origin' })
                .then(r=>r.json()).then(data=>{
                    // Example: update commerce cards if data-stat present
                    if (data && data.commerce){
                        Object.entries(data.commerce).forEach(([k,v])=>{
                            const el = document.querySelector(`[data-stat="${k}"]`);
                            if (el) el.textContent = (typeof v === 'number') ? new Intl.NumberFormat().format(v) : v;
                        });
                    }
                }).catch(()=>{});
        }
        refreshMetrics();
        setInterval(refreshMetrics, 60000);

        // Real-time updates are now handled by admin-dashboard-realtime.js
        // Fallback to polling only if Pusher is not available

        // Tester buttons (admin only)
        window.testRealtimeAdmin = function(){
            fetch(@json(route('test.realtime.admin')), { method:'POST', headers:{'X-CSRF-TOKEN': csrf} })
                .then(()=>console.log('Admin realtime test sent'));
        }
        window.testRealtimeSale = function(){
            const body = new URLSearchParams();
            // Optional: add event_id here if you want to simulate a specific event broadcast
            fetch(@json(route('test.realtime.sale')), { method:'POST', headers:{'X-CSRF-TOKEN': csrf, 'Content-Type':'application/x-www-form-urlencoded' }, body })
                .then(()=>console.log('Sales realtime test sent'));
        }
    })();
</script>

    <!-- Pass admin ID to JavaScript for private channel subscription -->
    <script>
        window.ADMIN_ID = {{ Auth::guard('admin')->id() ?? 'null' }};
    </script>
    
    <!-- Real-time admin dashboard updates -->
    <script src="{{ asset('js/admin-dashboard-realtime.js') }}?v={{ time() }}"></script>
@endpush