@extends('admin.service-plans.layout')

@section('content')
    <x-slot name="headerActions">
        <button onclick="openSubscriptionPlanModal()" class="inline-flex items-center px-4 py-2 bg-qricket-600 hover:bg-qricket-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add Plan
        </button>
    </x-slot>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="mb-6 bg-green-100 dark:bg-green-900 border border-green-400 text-green-700 dark:text-green-300 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-red-100 dark:bg-red-900 border border-red-400 text-red-700 dark:text-red-300 px-4 py-3 rounded">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Analytics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-qricket-100 dark:bg-qricket-900 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-qricket-600 dark:text-qricket-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M9 8h6M5 4h14a2 2 0 012 2v12l-4-2-4 2-4-2-4 2V6a2 2 0 012-2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Plans</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $subscriptionPlans->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Plans</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $subscriptionPlans->where('is_active', true)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Subscribers</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $totalSubscribers }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Monthly Revenue</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format($monthlyRevenue, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Subscription Growth Chart -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Subscription Growth</h3>
                <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                    <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                    <span>New Subscriptions</span>
                </div>
            </div>
            <div class="relative" style="height: 250px;">
                <canvas id="subscriptionGrowthChart"></canvas>
            </div>
        </div>

        <!-- Plan Distribution Chart -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Plan Distribution</h3>
                <span class="text-sm text-gray-500 dark:text-gray-400">By Subscribers</span>
            </div>
            <div class="relative" style="height: 250px;">
                <canvas id="planDistributionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Subscription Plans Grid -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Subscription Plans</h3>
        </div>
        <div class="p-6">
            @if($subscriptionPlans->count())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($subscriptionPlans as $plan)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                            <div class="text-center">
                                <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $plan->name }}</h4>
                                <div class="mt-2">
                                    @if($plan->price == 0)
                                        <span class="text-3xl font-bold text-gray-900 dark:text-gray-100">Free</span>
                                    @else
                                        <span class="text-3xl font-bold text-gray-900 dark:text-gray-100">₱{{ number_format($plan->price, 0) }}</span>
                                        <span class="text-gray-600 dark:text-gray-400">/month</span>
                                    @endif
                                </div>
                                @if($plan->description)
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $plan->description }}</p>
                                @endif
                                
                                <!-- Subscriber Count -->
                                <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Subscribers</p>
                                    <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $plan->subscribers_count ?? 0 }}</p>
                                </div>
                                
                                <!-- Features -->
                                <div class="mt-4 space-y-2 text-left">
                                    <div class="flex items-center text-sm">
                                        <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span class="text-gray-700 dark:text-gray-300">
                                            {{ $plan->events_per_month ? $plan->events_per_month . ' events/month' : 'Unlimited events' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center text-sm">
                                        <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span class="text-gray-700 dark:text-gray-300">
                                            {{ $plan->tickets_per_event ? $plan->tickets_per_event . ' tickets/event' : 'Unlimited tickets' }}
                                        </span>
                                    </div>
                                    @if($plan->advanced_layouts)
                                        <div class="flex items-center text-sm">
                                            <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <span class="text-gray-700 dark:text-gray-300">Advanced layouts</span>
                                        </div>
                                    @endif
                                    @if($plan->advanced_analytics)
                                        <div class="flex items-center text-sm">
                                            <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <span class="text-gray-700 dark:text-gray-300">Advanced analytics</span>
                                        </div>
                                    @endif
                                    @if($plan->priority_support)
                                        <div class="flex items-center text-sm">
                                            <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <span class="text-gray-700 dark:text-gray-300">Priority support</span>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Status -->
                                <div class="mt-4">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $plan->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' }}">
                                        {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                
                                <!-- Actions -->
                                <div class="mt-4 flex justify-center space-x-2">
                                    <button onclick="editSubscriptionPlan({{ $plan->id }})" class="text-qricket-600 dark:text-qricket-400 hover:text-qricket-800 dark:hover:text-qricket-300">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M9 8h6M5 4h14a2 2 0 012 2v12l-4-2-4 2-4-2-4 2V6a2 2 0 012-2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No subscription plans</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating subscription plans.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Subscription Plan Modal -->
    <div id="subscriptionPlanModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4" id="subscriptionPlanModalTitle">Add Subscription Plan</h3>
                <form id="subscriptionPlanForm" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                            <input type="text" name="name" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Slug</label>
                            <input type="text" name="slug" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <textarea name="description" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Price (PHP)</label>
                            <input type="number" name="price" required min="0" step="0.01" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Events per Month</label>
                            <input type="number" name="events_per_month" min="1" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Leave empty for unlimited</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tickets per Event</label>
                            <input type="number" name="tickets_per_event" min="1" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Leave empty for unlimited</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Free Events Included</label>
                                <input type="number" name="free_events_count" min="0" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                                <p class="text-xs text-gray-500 dark:text-gray-400">0 to disable</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Free Event Ticket Limit</label>
                                <input type="number" name="free_event_ticket_limit" min="0" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                                <p class="text-xs text-gray-500 dark:text-gray-400">0 to disable</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="advanced_layouts" class="rounded border-gray-300 text-qricket-600 shadow-sm focus:border-qricket-300 focus:ring focus:ring-qricket-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Advanced Layouts</span>
                                </label>
                            </div>
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="advanced_analytics" class="rounded border-gray-300 text-qricket-600 shadow-sm focus:border-qricket-300 focus:ring focus:ring-qricket-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Advanced Analytics</span>
                                </label>
                            </div>
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="priority_support" class="rounded border-gray-300 text-qricket-600 shadow-sm focus:border-qricket-300 focus:ring focus:ring-qricket-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Priority Support</span>
                                </label>
                            </div>
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="api_access" class="rounded border-gray-300 text-qricket-600 shadow-sm focus:border-qricket-300 focus:ring focus:ring-qricket-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">API Access</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sort Order</label>
                            <input type="number" name="sort_order" min="0" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeSubscriptionPlanModal()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-qricket-600 hover:bg-qricket-700 rounded-md">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Chart data from backend
        const subscriptionGrowthData = @json($subscriptionGrowthData);
        const planDistributionData = @json($planDistributionData);

        // Subscription Growth Chart
        const subscriptionGrowthCtx = document.getElementById('subscriptionGrowthChart').getContext('2d');
        const isDark = document.documentElement.classList.contains('dark');
        new Chart(subscriptionGrowthCtx, {
            type: 'line',
            data: {
                labels: subscriptionGrowthData.labels || [],
                datasets: [{
                    label: 'New Subscriptions',
                    data: subscriptionGrowthData.values || [],
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.05)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: 'rgb(16, 185, 129)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgb(16, 185, 129)',
                    pointHoverBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: isDark ? 'rgba(17, 24, 39, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                        titleColor: isDark ? '#e5e7eb' : '#374151',
                        bodyColor: isDark ? '#e5e7eb' : '#374151',
                        borderColor: isDark ? '#4b5563' : '#e5e7eb',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return 'Subscriptions: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: isDark ? '#9ca3af' : '#6b7280',
                            font: {
                                size: 11
                            },
                            stepSize: 1
                        },
                        grid: {
                            color: isDark ? 'rgba(75, 85, 99, 0.3)' : 'rgba(229, 231, 235, 0.5)',
                            drawBorder: false
                        }
                    },
                    x: {
                        ticks: {
                            color: isDark ? '#9ca3af' : '#6b7280',
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });

        // Plan Distribution Chart
        const planDistributionCtx = document.getElementById('planDistributionChart').getContext('2d');
        const isDarkMode = document.documentElement.classList.contains('dark');
        new Chart(planDistributionCtx, {
            type: 'doughnut',
            data: {
                labels: planDistributionData.labels || [],
                datasets: [{
                    data: planDistributionData.values || [],
                    backgroundColor: [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)',
                        'rgb(236, 72, 153)',
                        'rgb(14, 165, 233)'
                    ],
                    borderWidth: 2,
                    borderColor: isDarkMode ? '#1f2937' : '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: isDarkMode ? '#d1d5db' : '#4b5563',
                            font: {
                                size: 11
                            },
                            padding: 12,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: isDarkMode ? 'rgba(17, 24, 39, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                        titleColor: isDarkMode ? '#e5e7eb' : '#374151',
                        bodyColor: isDarkMode ? '#e5e7eb' : '#374151',
                        borderColor: isDarkMode ? '#4b5563' : '#e5e7eb',
                        borderWidth: 1,
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                },
                cutout: '60%',
                animation: {
                    animateRotate: true,
                    animateScale: true
                }
            }
        });

        // Modal Functions
        function openSubscriptionPlanModal() {
            document.getElementById('subscriptionPlanModal').classList.remove('hidden');
            document.getElementById('subscriptionPlanModalTitle').textContent = 'Add Subscription Plan';
            document.getElementById('subscriptionPlanForm').action = '{{ route("admin.subscriptions.plans.store") }}';
            document.getElementById('subscriptionPlanForm').method = 'POST';
            
            // Remove method override if it exists
            let methodInput = document.getElementById('subscriptionPlanForm').querySelector('input[name="_method"]');
            if (methodInput) {
                methodInput.remove();
            }
            
            // Reset form
            document.getElementById('subscriptionPlanForm').reset();
        }

        function closeSubscriptionPlanModal() {
            document.getElementById('subscriptionPlanModal').classList.add('hidden');
        }

        function editSubscriptionPlan(id) {
            // Find the subscription plan data
            const planData = @json($subscriptionPlans->keyBy('id'));
            const plan = planData[id];
            
            if (!plan) {
                console.error('Subscription plan not found:', id);
                return;
            }
            
            // Open modal and populate with existing data
            document.getElementById('subscriptionPlanModal').classList.remove('hidden');
            document.getElementById('subscriptionPlanModalTitle').textContent = 'Edit Subscription Plan';
            
            // Set form action for update
            document.getElementById('subscriptionPlanForm').action = `{{ url('admin/subscriptions/plans') }}/${id}`;
            document.getElementById('subscriptionPlanForm').method = 'POST';
            
            // Add method override for PUT request
            let methodInput = document.getElementById('subscriptionPlanForm').querySelector('input[name="_method"]');
            if (!methodInput) {
                methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                document.getElementById('subscriptionPlanForm').appendChild(methodInput);
            }
            methodInput.value = 'PUT';
            
            // Populate form fields
            document.querySelector('input[name="name"]').value = plan.name || '';
            document.querySelector('input[name="slug"]').value = plan.slug || '';
            document.querySelector('input[name="price"]').value = plan.price || '';
            document.querySelector('select[name="currency"]').value = plan.currency || 'PHP';
            document.querySelector('input[name="tickets_per_event"]').value = plan.tickets_per_event || '';
            document.querySelector('input[name="events_per_month"]').value = plan.events_per_month || '';
            document.querySelector('input[name="sort_order"]').value = plan.sort_order || '';
            document.querySelector('textarea[name="description"]').value = plan.description || '';
            document.querySelector('input[name="features"]').value = plan.features ? JSON.stringify(plan.features) : '';
            document.querySelector('input[name="is_active"]').checked = plan.is_active || false;
            document.querySelector('input[name="is_popular"]').checked = plan.is_popular || false;
            // Free event fields
            if (document.querySelector('input[name="free_events_count"]')) {
                document.querySelector('input[name="free_events_count"]').value = plan.free_events_count ?? '';
            }
            if (document.querySelector('input[name="free_event_ticket_limit"]')) {
                document.querySelector('input[name="free_event_ticket_limit"]').value = plan.free_event_ticket_limit ?? '';
            }
        }
    </script>
@endsection