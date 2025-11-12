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

<!-- Hidden data container for JavaScript -->
<div id="subscriptions-data"
    data-subscription-plans="{{ htmlspecialchars(json_encode($subscriptionPlans->keyBy('id')), ENT_QUOTES, 'UTF-8') }}"
    data-subscription-growth-data="{{ htmlspecialchars(json_encode($subscriptionGrowthData ?? []), ENT_QUOTES, 'UTF-8') }}"
    data-plan-distribution-data="{{ htmlspecialchars(json_encode($planDistributionData ?? []), ENT_QUOTES, 'UTF-8') }}"
    style="display: none;"></div>

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
<div id="subscriptionPlanModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="subscriptionPlanModalTitle" aria-modal="true" role="dialog">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity" onclick="closeSubscriptionPlanModal()"></div>

    <!-- Modal Container -->
    <div class="flex min-h-full items-center justify-center p-4" onclick="event.stopPropagation()">
        <div class="relative w-full max-w-3xl transform overflow-hidden rounded-xl bg-white dark:bg-gray-800 shadow-2xl transition-all" onclick="event.stopPropagation()">
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 px-6 py-4">
                <div class="flex items-center space-x-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-qricket-100 dark:bg-qricket-900/50">
                        <svg class="h-6 w-6 text-qricket-600 dark:text-qricket-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M9 8h6M5 4h14a2 2 0 012 2v12l-4-2-4 2-4-2-4 2V6a2 2 0 012-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100" id="subscriptionPlanModalTitle">Add Subscription Plan</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Create a new subscription plan for organizers</p>
                    </div>
                </div>
                <button type="button" onclick="closeSubscriptionPlanModal()" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-qricket-500">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Form Content -->
            <form id="subscriptionPlanForm" method="POST" class="p-6">
                @csrf
                <div class="space-y-6">
                    <!-- Basic Information -->
                    <div>
                        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">Basic Information</h4>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <span class="flex items-center">
                                        <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                        </svg>
                                        Plan Name
                                    </span>
                                </label>
                                <input type="text" name="name" required placeholder="e.g., Premium Plan" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-gray-300 px-3 py-2 shadow-sm transition-colors focus:border-qricket-500 focus:ring-qricket-500 dark:focus:border-qricket-400 dark:focus:ring-qricket-400">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <span class="flex items-center">
                                        <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                        </svg>
                                        Slug
                                    </span>
                                </label>
                                <input type="text" name="slug" required placeholder="premium-plan" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-gray-300 px-3 py-2 shadow-sm transition-colors focus:border-qricket-500 focus:ring-qricket-500 dark:focus:border-qricket-400 dark:focus:ring-qricket-400">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">URL-friendly identifier</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                <span class="flex items-center">
                                    <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Description
                                </span>
                            </label>
                            <textarea name="description" rows="3" placeholder="Enter plan description..." class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-gray-300 px-3 py-2 shadow-sm transition-colors focus:border-qricket-500 focus:ring-qricket-500 dark:focus:border-qricket-400 dark:focus:ring-qricket-400"></textarea>
                        </div>
                    </div>

                    <!-- Pricing & Limits -->
                    <div>
                        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">Pricing & Limits</h4>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <span class="flex items-center">
                                        <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                        Price (PHP)
                                    </span>
                                </label>
                                <input type="number" name="price" required min="0" step="0.01" placeholder="0.00" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-gray-300 px-3 py-2 shadow-sm transition-colors focus:border-qricket-500 focus:ring-qricket-500 dark:focus:border-qricket-400 dark:focus:ring-qricket-400">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <span class="flex items-center">
                                        <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                        </svg>
                                        Sort Order
                                    </span>
                                </label>
                                <input type="number" name="sort_order" min="0" placeholder="0" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-gray-300 px-3 py-2 shadow-sm transition-colors focus:border-qricket-500 focus:ring-qricket-500 dark:focus:border-qricket-400 dark:focus:ring-qricket-400">
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <span class="flex items-center">
                                        <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Events per Month
                                    </span>
                                </label>
                                <input type="number" name="events_per_month" min="1" placeholder="Unlimited" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-gray-300 px-3 py-2 shadow-sm transition-colors focus:border-qricket-500 focus:ring-qricket-500 dark:focus:border-qricket-400 dark:focus:ring-qricket-400">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave empty for unlimited</p>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <span class="flex items-center">
                                        <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                        </svg>
                                        Tickets per Event
                                    </span>
                                </label>
                                <input type="number" name="tickets_per_event" min="1" placeholder="Unlimited" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-gray-300 px-3 py-2 shadow-sm transition-colors focus:border-qricket-500 focus:ring-qricket-500 dark:focus:border-qricket-400 dark:focus:ring-qricket-400">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave empty for unlimited</p>
                            </div>
                        </div>
                    </div>

                    <!-- Free Event Settings -->
                    <div>
                        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">Free Event Settings</h4>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <span class="flex items-center">
                                        <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Free Events Included
                                    </span>
                                </label>
                                <input type="number" name="free_events_count" min="0" placeholder="0" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-gray-300 px-3 py-2 shadow-sm transition-colors focus:border-qricket-500 focus:ring-qricket-500 dark:focus:border-qricket-400 dark:focus:ring-qricket-400">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Set to 0 to disable</p>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <span class="flex items-center">
                                        <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Free Event Ticket Limit
                                    </span>
                                </label>
                                <input type="number" name="free_event_ticket_limit" min="0" placeholder="0" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-gray-300 px-3 py-2 shadow-sm transition-colors focus:border-qricket-500 focus:ring-qricket-500 dark:focus:border-qricket-400 dark:focus:ring-qricket-400">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Set to 0 to disable</p>
                            </div>
                        </div>
                    </div>

                    <!-- Features -->
                    <div>
                        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">Features</h4>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <label class="flex items-start space-x-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700/50 p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <input type="checkbox" name="advanced_layouts" class="mt-1 h-4 w-4 rounded border-gray-300 text-qricket-600 focus:ring-qricket-500 dark:border-gray-600">
                                <div>
                                    <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Advanced Layouts</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Access to premium event layouts</span>
                                </div>
                            </label>
                            <label class="flex items-start space-x-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700/50 p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <input type="checkbox" name="advanced_analytics" class="mt-1 h-4 w-4 rounded border-gray-300 text-qricket-600 focus:ring-qricket-500 dark:border-gray-600">
                                <div>
                                    <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Advanced Analytics</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Access to detailed analytics and reports</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="mt-8 flex items-center justify-end space-x-3 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <button type="button" onclick="closeSubscriptionPlanModal()" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm transition-colors hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-qricket-500 focus:ring-offset-2">
                        Cancel
                    </button>
                    <button type="submit" class="rounded-lg bg-qricket-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-qricket-700 focus:outline-none focus:ring-2 focus:ring-qricket-500 focus:ring-offset-2 dark:bg-qricket-500 dark:hover:bg-qricket-600">
                        <span class="flex items-center">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Save Subscription Plan
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const subscriptionsContainer = document.getElementById('subscriptions-data');
    const parseJsonData = (container, {
        attribute,
        datasetKey,
        fallback
    }) => {
        const fallbackJson = fallback || '{}';
        let raw = '';

        if (container) {
            if (datasetKey && container.dataset && container.dataset[datasetKey] !== undefined) {
                raw = container.dataset[datasetKey];
            }

            if (!raw && attribute) {
                raw = container.getAttribute(attribute) || '';
            }
        }

        if (!raw) {
            raw = fallbackJson;
        }

        const normalized = raw
            .replace(/&quot;/g, '"')
            .replace(/&#39;/g, "'")
            .replace(/&amp;/g, '&');

        try {
            return JSON.parse(normalized);
        } catch (error) {
            console.error('Failed to parse data attribute', attribute || datasetKey, error);
            try {
                return JSON.parse(fallbackJson);
            } catch (fallbackError) {
                console.error('Failed to parse fallback JSON', fallbackJson, fallbackError);
                return {};
            }
        }
    };

    const subscriptionGrowthData = parseJsonData(subscriptionsContainer, {
        attribute: 'data-subscription-growth-data',
        datasetKey: 'subscriptionGrowthData',
        fallback: '{"labels":[],"values":[]}'
    });
    const planDistributionData = parseJsonData(subscriptionsContainer, {
        attribute: 'data-plan-distribution-data',
        datasetKey: 'planDistributionData',
        fallback: '{"labels":[],"values":[]}'
    });

    if (typeof Chart === 'undefined') {
        console.error('Chart.js failed to load');
    } else {
        const isDark = document.documentElement.classList.contains('dark');
        const subscriptionGrowthCanvas = document.getElementById('subscriptionGrowthChart');
        if (subscriptionGrowthCanvas && subscriptionGrowthCanvas.getContext) {
            const subscriptionGrowthCtx = subscriptionGrowthCanvas.getContext('2d');
            new Chart(subscriptionGrowthCtx, {
                type: 'line',
                data: {
                    labels: Array.isArray(subscriptionGrowthData.labels) ? subscriptionGrowthData.labels : [],
                    datasets: [{
                        label: 'New Subscriptions',
                        data: Array.isArray(subscriptionGrowthData.values) ? subscriptionGrowthData.values : [],
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
                                    return `Subscriptions: ${context.parsed.y}`;
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
        }

        const planDistributionCanvas = document.getElementById('planDistributionChart');
        if (planDistributionCanvas && planDistributionCanvas.getContext) {
            const planDistributionCtx = planDistributionCanvas.getContext('2d');
            const isDarkMode = document.documentElement.classList.contains('dark');
            new Chart(planDistributionCtx, {
                type: 'doughnut',
                data: {
                    labels: Array.isArray(planDistributionData.labels) ? planDistributionData.labels : [],
                    datasets: [{
                        data: Array.isArray(planDistributionData.values) ? planDistributionData.values : [],
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
                                    const label = context.label || 'Unknown';
                                    const value = context.parsed || 0;
                                    return `${label}: ${value} subscriber${value === 1 ? '' : 's'}`;
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }
    }

    // Modal Functions
    function openSubscriptionPlanModal() {
        const modal = document.getElementById('subscriptionPlanModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
        document.getElementById('subscriptionPlanModalTitle').textContent = 'Add Subscription Plan';
        document.querySelector('#subscriptionPlanModal .text-sm.text-gray-500').textContent = 'Create a new subscription plan for organizers';
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
        const modal = document.getElementById('subscriptionPlanModal');
        modal.classList.add('hidden');
        document.body.style.overflow = ''; // Restore scrolling
    }

    function editSubscriptionPlan(id) {
        // Find the subscription plan data
        const subscriptionsContainer = document.getElementById('subscriptions-data');
        const planData = parseJsonData(subscriptionsContainer, {
            attribute: 'data-subscription-plans',
            datasetKey: 'subscriptionPlans',
            fallback: '{}'
        });
        const plan = planData[id];

        if (!plan) {
            console.error('Subscription plan not found:', id);
            return;
        }

        // Open modal and populate with existing data
        const modal = document.getElementById('subscriptionPlanModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
        document.getElementById('subscriptionPlanModalTitle').textContent = 'Edit Subscription Plan';
        document.querySelector('#subscriptionPlanModal .text-sm.text-gray-500').textContent = 'Update subscription plan information';

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
