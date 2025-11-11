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

    <!-- Top Row: Quick Stats + Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Quick Stats Cards (Left Side) -->
        <div class="lg:col-span-1 grid grid-cols-2 gap-4">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 flex flex-col" style="min-height: 438px;">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-qricket-100 dark:bg-qricket-900 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-qricket-600 dark:text-qricket-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M9 8h6M5 4h14a2 2 0 012 2v12l-4-2-4 2-4-2-4 2V6a2 2 0 012-2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="flex-1 flex flex-col justify-center">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Total Plans</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $subscriptionPlans->count() }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 flex flex-col" style="min-height: 438px;">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="flex-1 flex flex-col justify-center">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Active Plans</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $subscriptionPlans->where('is_active', true)->count() }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 flex flex-col" style="min-height: 438px;">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="flex-1 flex flex-col justify-center">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Total Subscribers</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $totalSubscribers }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 flex flex-col" style="min-height: 438px;">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="flex-1 flex flex-col justify-center">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Monthly Revenue</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">₱{{ number_format($monthlyRevenue, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Charts (Right Side) -->
        <div class="lg:col-span-2 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Subscription Growth Chart -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 flex flex-col border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Subscription Growth</h3>
                    <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                        <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                        <span>New Subscriptions</span>
                    </div>
                </div>
                <div class="flex-1 min-h-0" style="height: 350px;">
                    <canvas id="subscriptionGrowthChart"></canvas>
                </div>
            </div>

            <!-- Plan Distribution Chart -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 flex flex-col border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Plan Distribution</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">By Subscribers</span>
                </div>
                <div class="flex-1 min-h-0 flex items-center justify-center" style="height: 350px;">
                    <canvas id="planDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Row: Subscription Plans on Left Side -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Subscription Plans (Left Side - Full Height) -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg h-full flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Subscription Plans</h3>
                </div>
                <div class="p-6 flex-1 overflow-y-auto" style="max-height: calc(100vh - 600px); min-height: 400px;">
            @if($subscriptionPlans->count())
                <div class="space-y-4">
                    @foreach($subscriptionPlans as $plan)
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 p-4 md:p-5 hover:shadow-md transition-all duration-200">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                <!-- Left Section: Plan Info -->
                                <div class="flex-1 flex flex-col md:flex-row md:items-center md:space-x-6 gap-4 md:gap-0">
                                    <div class="flex-shrink-0">
                                        <div class="flex flex-wrap items-center gap-2 mb-2">
                                            <h4 class="text-base md:text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $plan->name }}</h4>
                                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $plan->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' }}">
                                                {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                        @if($plan->description)
                                            <p class="text-xs md:text-sm text-gray-600 dark:text-gray-400">{{ $plan->description }}</p>
                                        @endif
                                    </div>
                                    
                                    <!-- Middle Section: Details -->
                                    <div class="flex flex-wrap md:flex-nowrap items-center gap-4 md:gap-6 md:space-x-8 border-t md:border-t-0 md:border-l md:border-r border-gray-200 dark:border-gray-600 pt-4 md:pt-0 md:px-6">
                                        <div class="text-center min-w-[100px]">
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Price</div>
                                            <div class="text-sm md:text-base font-semibold text-qricket-600 dark:text-qricket-400">
                                                @if($plan->price == 0)
                                                    Free
                                                @else
                                                    ₱{{ number_format($plan->price, 0) }}/mo
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-center min-w-[100px]">
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Subscribers</div>
                                            <div class="text-sm md:text-base font-semibold text-gray-900 dark:text-gray-100">{{ $plan->subscribers_count ?? 0 }}</div>
                                        </div>
                                        <div class="text-center min-w-[120px]">
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Events/Month</div>
                                            <div class="text-sm md:text-base font-semibold text-gray-900 dark:text-gray-100">
                                                {{ $plan->events_per_month ? $plan->events_per_month : 'Unlimited' }}
                                            </div>
                                        </div>
                                        <div class="text-center min-w-[120px]">
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Tickets/Event</div>
                                            <div class="text-sm md:text-base font-semibold text-gray-900 dark:text-gray-100">
                                                {{ $plan->tickets_per_event ? $plan->tickets_per_event : 'Unlimited' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Right Section: Actions -->
                                <div class="flex-shrink-0 flex items-center justify-end md:justify-start space-x-2 md:ml-4">
                                    <button onclick="editSubscriptionPlan({{ $plan->id }})" class="p-2 text-qricket-600 dark:text-qricket-400 hover:bg-qricket-50 dark:hover:bg-qricket-900/20 rounded-md transition-colors" title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button onclick="deleteSubscriptionPlan({{ $plan->id }})" class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors" title="Delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M9 8h6M5 4h14a2 2 0 012 2v12l-4-2-4 2-4-2-4 2V6a2 2 0 012-2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No subscription plans</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new subscription plan.</p>
                </div>
            @endif
                </div>
            </div>
        </div>
        
        <!-- Empty space on right (2 columns) -->
        <div class="lg:col-span-2 hidden lg:block"></div>
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
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#10b981',
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
                        display: true,
                        position: 'top',
                        labels: {
                            color: isDark ? '#e5e7eb' : '#374151',
                            font: { size: 12 }
                        }
                    },
                    tooltip: {
                        enabled: true,
                        mode: 'index',
                        intersect: false,
                        backgroundColor: isDark ? 'rgba(17, 24, 39, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                        titleColor: isDark ? '#e5e7eb' : '#374151',
                        bodyColor: isDark ? '#e5e7eb' : '#374151',
                        borderColor: isDark ? '#4b5563' : '#e5e7eb',
                        borderWidth: 1,
                        padding: 12
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: isDark ? '#e5e7eb' : '#374151',
                            font: { size: 11 },
                            stepSize: 1
                        },
                        grid: {
                            color: isDark ? '#374151' : '#e5e7eb'
                        }
                    },
                    x: {
                        ticks: {
                            color: isDark ? '#e5e7eb' : '#374151',
                            font: { size: 11 },
                            maxRotation: 45,
                            minRotation: 45
                        },
                        grid: {
                            color: isDark ? '#374151' : '#e5e7eb',
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
                        display: true,
                        position: 'right',
                        labels: {
                            color: isDarkMode ? '#e5e7eb' : '#374151',
                            font: { size: 12 },
                            padding: 15,
                            boxWidth: 15,
                            boxHeight: 15
                        }
                    },
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        },
                        backgroundColor: isDarkMode ? 'rgba(17, 24, 39, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                        titleColor: isDarkMode ? '#e5e7eb' : '#374151',
                        bodyColor: isDarkMode ? '#e5e7eb' : '#374151',
                        borderColor: isDarkMode ? '#4b5563' : '#e5e7eb',
                        borderWidth: 1,
                        padding: 12
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
            document.querySelector('input[name="tickets_per_event"]').value = plan.tickets_per_event || '';
            document.querySelector('input[name="events_per_month"]').value = plan.events_per_month || '';
            document.querySelector('input[name="sort_order"]').value = plan.sort_order || '';
            document.querySelector('textarea[name="description"]').value = plan.description || '';
            
            // Set checkboxes
            const advancedLayoutsCheckbox = document.querySelector('input[name="advanced_layouts"]');
            if (advancedLayoutsCheckbox) {
                advancedLayoutsCheckbox.checked = plan.advanced_layouts || false;
            }
            const advancedAnalyticsCheckbox = document.querySelector('input[name="advanced_analytics"]');
            if (advancedAnalyticsCheckbox) {
                advancedAnalyticsCheckbox.checked = plan.advanced_analytics || false;
            }
            
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