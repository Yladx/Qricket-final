@extends('admin.service-plans.layout')

@section('content')
<x-slot name="headerActions">
    {{-- The button is now fixed to the bottom-right --}}
</x-slot>

<!-- Hidden data container for JavaScript -->
<div id="pricing-data"
    data-pricing-plans="{{ htmlspecialchars(json_encode($pricingPlans->keyBy('id')), ENT_QUOTES, 'UTF-8') }}"
    data-revenue-data="{{ htmlspecialchars(json_encode($revenueData ?? []), ENT_QUOTES, 'UTF-8') }}"
    data-popular-plans-data="{{ htmlspecialchars(json_encode($popularPlansData ?? []), ENT_QUOTES, 'UTF-8') }}"
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

<div class="fixed bottom-6 right-6 z-40 drop-shadow-lg">
    <button onclick="openPricingModal()" class="inline-flex items-center px-4 py-2 bg-qricket-600 hover:bg-qricket-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Add Additional Ticket
    </button>
</div>

<!-- Analytics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-qricket-100 dark:bg-qricket-900 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-qricket-600 dark:text-qricket-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Plans</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $pricingPlans->count() }}</p>
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
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $pricingPlans->where('is_active', true)->count() }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Purchases</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $totalPurchases }}</p>
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
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format($totalRevenue, 2) }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Revenue Chart -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 flex flex-col">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Revenue Over Time</h3>
        <div class="flex-1 min-h-0" style="height: 350px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Popular Plans Chart -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 flex flex-col">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Most Popular Plans</h3>
        <div class="flex-1 min-h-0 flex items-center justify-center" style="height: 350px;">
            <canvas id="popularPlansChart"></canvas>
        </div>
    </div>
</div>

<!-- Full-width Pricing Plans Cards Section -->
<div class="mt-8 -mx-6 sm:-mx-6 lg:-mx-8 overflow-x-hidden">
    <div class="px-6 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Pricing Plans</h3>
            </div>
            <div class="p-6 overflow-x-hidden">
                @if($pricingPlans->count())
                <div class="space-y-4">
                    @foreach($pricingPlans as $plan)
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 p-4 lg:p-5 hover:shadow-md transition-all duration-200 overflow-hidden">
                        <!-- Header: Name, Badges, Actions -->
                        <div class="flex flex-wrap items-start justify-between gap-3 mb-4 pb-4 border-b border-gray-200 dark:border-gray-600">
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <h4 class="text-base lg:text-lg font-semibold text-gray-900 dark:text-gray-100 break-words">{{ $plan->name }}</h4>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap flex-shrink-0 {{ $plan->type === 'free' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' }}">
                                        {{ ucfirst($plan->type) }}
                                    </span>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap flex-shrink-0 {{ $plan->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' }}">
                                        {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                @if($plan->description)
                                <p class="text-xs lg:text-sm text-gray-600 dark:text-gray-400 break-words">{{ $plan->description }}</p>
                                @endif
                            </div>
                            <!-- Actions -->
                            <div class="flex items-center space-x-2 flex-shrink-0">
                                <button onclick="editPricingPlan({{ $plan->id }})" class="p-2 text-qricket-600 dark:text-qricket-400 hover:bg-qricket-50 dark:hover:bg-qricket-900/20 rounded-md transition-colors" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button onclick="deletePricingPlan({{ $plan->id }})" class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors" title="Delete">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Details Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="min-w-0">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Tickets</div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ number_format($plan->ticket_quantity) }}</div>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Price</div>
                                <div class="text-sm font-semibold text-qricket-600 dark:text-qricket-400 truncate">{{ $plan->formatted_price }}</div>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Purchases</div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $plan->purchases_count ?? 0 }}</div>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Revenue</div>
                                <div class="text-sm font-semibold text-green-600 dark:text-green-400 truncate">₱{{ number_format(($plan->purchases_count ?? 0) * $plan->price, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No pricing plans</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new pricing plan.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Pricing Plan Modal -->
<div id="pricingModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="pricingModalTitle" aria-modal="true" role="dialog">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity" onclick="closePricingModal()"></div>

    <!-- Modal Container -->
    <div class="flex min-h-full items-center justify-center p-4" onclick="event.stopPropagation()">
        <div class="relative w-full max-w-2xl transform overflow-hidden rounded-xl bg-white dark:bg-gray-800 shadow-2xl transition-all" onclick="event.stopPropagation()">
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 px-6 py-4">
                <div class="flex items-center space-x-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-qricket-100 dark:bg-qricket-900/50">
                        <svg class="h-6 w-6 text-qricket-600 dark:text-qricket-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100" id="pricingModalTitle">Add Additional Ticket</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Create a new pricing plan for additional tickets</p>
                    </div>
                </div>
                <button type="button" onclick="closePricingModal()" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-qricket-500">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Form Content -->
            <form id="pricingForm" method="POST" class="p-6">
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
                                <input type="text" name="name" required placeholder="e.g., Premium Package" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-gray-300 px-3 py-2 shadow-sm transition-colors focus:border-qricket-500 focus:ring-qricket-500 dark:focus:border-qricket-400 dark:focus:ring-qricket-400">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <span class="flex items-center">
                                        <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                        </svg>
                                        Ticket Quantity
                                    </span>
                                </label>
                                <input type="number" name="ticket_quantity" required min="1" placeholder="10" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-gray-300 px-3 py-2 shadow-sm transition-colors focus:border-qricket-500 focus:ring-qricket-500 dark:focus:border-qricket-400 dark:focus:ring-qricket-400">
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

                    <!-- Pricing Information -->
                    <div>
                        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">Pricing Information</h4>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <span class="flex items-center">
                                        <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                        Price
                                    </span>
                                </label>
                                <input type="number" name="price" required min="0" step="0.01" placeholder="0.00" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-gray-300 px-3 py-2 shadow-sm transition-colors focus:border-qricket-500 focus:ring-qricket-500 dark:focus:border-qricket-400 dark:focus:ring-qricket-400">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <span class="flex items-center">
                                        <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                        Currency
                                    </span>
                                </label>
                                <select name="currency" required class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-gray-300 px-3 py-2 shadow-sm transition-colors focus:border-qricket-500 focus:ring-qricket-500 dark:focus:border-qricket-400 dark:focus:ring-qricket-400">
                                    <option value="PHP">PHP</option>
                                    <option value="USD">USD</option>
                                </select>
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
                    </div>

                    <!-- Event Settings -->
                    <div>
                        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">Event Settings</h4>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <span class="flex items-center">
                                        <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Event Type
                                    </span>
                                </label>
                                <select name="type" required class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-gray-300 px-3 py-2 shadow-sm transition-colors focus:border-qricket-500 focus:ring-qricket-500 dark:focus:border-qricket-400 dark:focus:ring-qricket-400">
                                    <option value="paid">Paid Event</option>
                                    <option value="free">Free Event</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <label class="flex items-center space-x-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700/50 p-4 w-full cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-gray-300 text-qricket-600 focus:ring-qricket-500 dark:border-gray-600">
                                    <div>
                                        <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Active</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Enable this pricing plan</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="mt-8 flex items-center justify-end space-x-3 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <button type="button" onclick="closePricingModal()" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm transition-colors hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-qricket-500 focus:ring-offset-2">
                        Cancel
                    </button>
                    <button type="submit" class="rounded-lg bg-qricket-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-qricket-700 focus:outline-none focus:ring-2 focus:ring-qricket-500 focus:ring-offset-2 dark:bg-qricket-500 dark:hover:bg-qricket-600">
                        <span class="flex items-center">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Save Pricing Plan
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Chart data from backend
    const pricingContainer = document.getElementById('pricing-data');
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

    const revenueData = parseJsonData(pricingContainer, {
        attribute: 'data-revenue-data',
        datasetKey: 'revenueData',
        fallback: '{"labels":[],"values":[]}'
    });
    const popularPlansData = parseJsonData(pricingContainer, {
        attribute: 'data-popular-plans-data',
        datasetKey: 'popularPlansData',
        fallback: '{"labels":[],"values":[]}'
    });

    if (typeof Chart !== 'undefined') {
        const revenueCanvas = document.getElementById('revenueChart');
        if (revenueCanvas && revenueCanvas.getContext) {
            const revenueCtx = revenueCanvas.getContext('2d');
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: Array.isArray(revenueData.labels) ? revenueData.labels : [],
                    datasets: [{
                        label: 'Revenue (₱)',
                        data: Array.isArray(revenueData.values) ? revenueData.values : [],
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
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
                                color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151',
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            enabled: true,
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151',
                                font: {
                                    size: 11
                                }
                            },
                            grid: {
                                color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb'
                            }
                        },
                        x: {
                            ticks: {
                                color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151',
                                font: {
                                    size: 11
                                },
                                maxRotation: 45,
                                minRotation: 45
                            },
                            grid: {
                                color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb',
                                display: false
                            }
                        }
                    }
                }
            });
        }

        const popularPlansCanvas = document.getElementById('popularPlansChart');
        if (popularPlansCanvas && popularPlansCanvas.getContext) {
            const popularPlansCtx = popularPlansCanvas.getContext('2d');
            new Chart(popularPlansCtx, {
                type: 'doughnut',
                data: {
                    labels: Array.isArray(popularPlansData.labels) ? popularPlansData.labels : [],
                    datasets: [{
                        data: Array.isArray(popularPlansData.values) ? popularPlansData.values : [],
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(139, 92, 246, 0.8)',
                            'rgba(236, 72, 153, 0.8)'
                        ],
                        borderColor: '#0f172a',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151',
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || 'Unknown';
                                    const value = context.parsed || 0;
                                    return `${label}: ${value} orders`;
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
    function openPricingModal() {
        const modal = document.getElementById('pricingModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
        document.getElementById('pricingModalTitle').textContent = 'Add Additional Ticket';
        document.querySelector('#pricingModal .text-sm.text-gray-500').textContent = 'Create a new pricing plan for additional tickets';
        document.getElementById('pricingForm').action = '{{ route("admin.subscriptions.pricing.store") }}';
        document.getElementById('pricingForm').method = 'POST';

        // Remove method override if it exists
        let methodInput = document.getElementById('pricingForm').querySelector('input[name="_method"]');
        if (methodInput) {
            methodInput.remove();
        }

        // Reset form
        document.getElementById('pricingForm').reset();
    }

    function closePricingModal() {
        const modal = document.getElementById('pricingModal');
        modal.classList.add('hidden');
        document.body.style.overflow = ''; // Restore scrolling
    }

    function editPricingPlan(id) {
        // Find the pricing plan data
        const pricingContainer = document.getElementById('pricing-data');
        const planData = parseJsonData(pricingContainer, {
            attribute: 'data-pricing-plans',
            datasetKey: 'pricingPlans',
            fallback: '{}'
        });
        const plan = planData[id];

        if (!plan) {
            console.error('Pricing plan not found:', id);
            return;
        }

        // Open modal and populate with existing data
        const modal = document.getElementById('pricingModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
        document.getElementById('pricingModalTitle').textContent = 'Edit Additional Ticket';
        document.querySelector('#pricingModal .text-sm.text-gray-500').textContent = 'Update pricing plan information';

        // Set form action for update
        document.getElementById('pricingForm').action = `{{ url('admin/subscriptions/pricing') }}/${id}`;
        document.getElementById('pricingForm').method = 'POST';

        // Add method override for PUT request
        let methodInput = document.getElementById('pricingForm').querySelector('input[name="_method"]');
        if (!methodInput) {
            methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            document.getElementById('pricingForm').appendChild(methodInput);
        }
        methodInput.value = 'PUT';

        // Populate form fields
        document.querySelector('input[name="name"]').value = plan.name || '';
        document.querySelector('input[name="ticket_quantity"]').value = plan.ticket_quantity || '';
        document.querySelector('input[name="price"]').value = plan.price || '';
        document.querySelector('select[name="currency"]').value = plan.currency || 'PHP';
        document.querySelector('select[name="type"]').value = plan.type || 'paid';
        document.querySelector('textarea[name="description"]').value = plan.description || '';
        document.querySelector('input[name="sort_order"]').value = plan.sort_order || '';

        // Handle checkbox
        const isActiveCheckbox = document.querySelector('input[name="is_active"]');
        if (isActiveCheckbox) {
            isActiveCheckbox.checked = plan.is_active || false;
        }
    }

    function deletePricingPlan(id) {
        const confirmDelete = () => {
            if (typeof Swal !== 'undefined' && window.Swal) {
                Swal.fire({
                    title: 'Delete Pricing Plan?',
                    text: 'Are you sure you want to delete this pricing plan?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280'
                }).then((result) => {
                    if (result.isConfirmed) {
                        proceedWithDelete(id);
                    }
                });
            } else {
                if (confirm('Are you sure you want to delete this pricing plan?')) {
                    proceedWithDelete(id);
                }
            }
        };

        const proceedWithDelete = (id) => {
            fetch(`{{ url('admin/subscriptions/pricing') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (typeof Swal !== 'undefined' && window.Swal) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted',
                                text: 'Pricing plan deleted successfully.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        } else {
                            location.reload();
                        }
                    } else {
                        if (typeof Swal !== 'undefined' && window.Swal) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error deleting pricing plan',
                            });
                        } else {
                            alert('Error deleting pricing plan');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (typeof Swal !== 'undefined' && window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error deleting pricing plan',
                        });
                    } else {
                        alert('Error deleting pricing plan');
                    }
                });
        };

        confirmDelete();
    }
</script>
@endsection
