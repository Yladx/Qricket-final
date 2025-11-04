<x-admin-layout title="Subscription Management" page-title="Subscription Management">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Subscription Management
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Manage ticket limit pricing and discounts</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="mb-6 bg-green-100 dark:bg-green-900 border border-green-400 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 bg-red-100 dark:bg-red-900 border border-red-400 text-red-700 dark:text-red-300 px-4 py-3 rounded">
                    <ul class="list-discount list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Subscription Plans -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg mb-8">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Subscription Plans</h3>
                        <button onclick="openSubscriptionPlanModal()" class="inline-flex items-center px-3 py-2 bg-qricket-600 hover:bg-qricket-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Plan
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    @if($subscriptionPlans->count())
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            @foreach($subscriptionPlans as $plan)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 {{ $plan->slug === 'enterprise' ? 'ring-2 ring-qricket-500' : '' }}">
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
                                            <button onclick="deleteSubscriptionPlan({{ $plan->id }})" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
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

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Ticket Limit Pricing -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Ticket Limit Pricing</h3>
                            <button onclick="openPricingModal()" class="inline-flex items-center px-3 py-2 bg-qricket-600 hover:bg-qricket-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Pricing Plan
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        @if($pricingPlans->count())
                            <div class="space-y-4">
                                @foreach($pricingPlans as $plan)
                                    <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $plan->name }}</h4>
                                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $plan->type === 'free' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' }}">
                                                    {{ ucfirst($plan->type) }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $plan->ticket_quantity }} additional tickets</p>
                                            <p class="text-lg font-bold text-qricket-600 dark:text-qricket-400">{{ $plan->formatted_price }}</p>
                                            @if($plan->description)
                                                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">{{ $plan->description }}</p>
                                            @endif
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $plan->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' }}">
                                                {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                            <button onclick="editPricingPlan({{ $plan->id }})" class="text-qricket-600 dark:text-qricket-400 hover:text-qricket-800 dark:hover:text-qricket-300">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button onclick="deletePricingPlan({{ $plan->id }})" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No pricing plans</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new pricing plan.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Discounts -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Discounts</h3>
                            <button onclick="openDiscountModal()" class="inline-flex items-center px-3 py-2 bg-qricket-600 hover:bg-qricket-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Discount
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        @if($discounts->count())
                            <div class="space-y-4">
                                @foreach($discounts as $discount)
                                    <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $discount->name }}</h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">Code: <span class="font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">{{ $discount->code }}</span></p>
                                            <p class="text-lg font-bold text-qricket-600 dark:text-qricket-400">{{ $discount->formatted_value }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-500">Applies to: {{ ucfirst(str_replace('_', ' ', $discount->applicable_to)) }}</p>
                                            @if($discount->usage_limit)
                                                <p class="text-xs text-gray-500 dark:text-gray-500">Used: {{ $discount->used_count }}/{{ $discount->usage_limit }}</p>
                                            @endif
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $discount->isValid() ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' }}">
                                                {{ $discount->isValid() ? 'Active' : 'Inactive' }}
                                            </span>
                                            <button onclick="editDiscount({{ $discount->id }})" class="text-qricket-600 dark:text-qricket-400 hover:text-qricket-800 dark:hover:text-qricket-300">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button onclick="deleteDiscount({{ $discount->id }})" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No discounts</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new discount.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Purchases -->
            <div class="mt-8 bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Ticket Limit Purchases</h3>
                        <a href="{{ route('admin.subscriptions.purchases') }}" class="text-qricket-600 dark:text-qricket-400 hover:text-qricket-800 dark:hover:text-qricket-300 text-sm font-medium">
                            View All
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    @if($recentPurchases->count())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Event</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tickets</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($recentPurchases as $purchase)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $purchase->user->first_name }} {{ $purchase->user->last_name }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ $purchase->event->title }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ $purchase->additional_tickets }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ $purchase->formatted_amount }}</td>
                                            <td class="px-4 py-2">
                                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $purchase->status === 'paid' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                                    {{ ucfirst($purchase->status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ $purchase->created_at->format('M j, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No purchases yet</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ticket limit purchases will appear here.</p>
                        </div>
                    @endif
                </div>
            </div>
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

    <!-- Pricing Plan Modal -->
    <div id="pricingModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4" id="pricingModalTitle">Add Pricing Plan</h3>
                <form id="pricingForm" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                            <input type="text" name="name" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ticket Quantity</label>
                            <input type="number" name="ticket_quantity" required min="1" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Price</label>
                            <input type="number" name="price" required min="0" step="0.01" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Currency</label>
                            <select name="currency" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                                <option value="PHP">PHP</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <textarea name="description" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Event Type</label>
                            <select name="type" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                                <option value="paid">Paid Event</option>
                                <option value="free">Free Event</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sort Order</label>
                            <input type="number" name="sort_order" min="0" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-qricket-600 shadow-sm focus:border-qricket-500 focus:ring-qricket-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closePricingModal()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md">
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

    <!-- Discount Modal -->
    <div id="discountModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4" id="discountModalTitle">Add Discount</h3>
                <form id="discountForm" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                            <input type="text" name="name" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Code</label>
                            <input type="text" name="code" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                            <select name="type" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                                <option value="percentage">Percentage</option>
                                <option value="fixed_amount">Fixed Amount</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Value</label>
                            <input type="number" name="value" required min="0" step="0.01" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Currency</label>
                            <select name="currency" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                                <option value="PHP">PHP</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Applicable To</label>
                            <select name="applicable_to" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                                <option value="ticket_limit_purchase">Ticket Limit Purchase</option>
                                <option value="subscription">Subscription</option>
                                <option value="all">All</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Usage Limit</label>
                            <input type="number" name="usage_limit" min="1" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Starts At</label>
                            <input type="datetime-local" name="starts_at" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expires At</label>
                            <input type="datetime-local" name="expires_at" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <textarea name="description" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeDiscountModal()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md">
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

    <script>
        // Subscription Plan Modal Functions
        function openSubscriptionPlanModal() {
            document.getElementById('subscriptionPlanModal').classList.remove('hidden');
            document.getElementById('subscriptionPlanModalTitle').textContent = 'Add Subscription Plan';
            document.getElementById('subscriptionPlanForm').action = '{{ route("admin.subscriptions.plans.store") }}';
            document.getElementById('subscriptionPlanForm').method = 'POST';
            document.getElementById('subscriptionPlanForm').reset();
        }

        function closeSubscriptionPlanModal() {
            document.getElementById('subscriptionPlanModal').classList.add('hidden');
        }

        function editSubscriptionPlan(id) {
            // Implementation for editing subscription plan
            console.log('Edit subscription plan:', id);
        }

        function deleteSubscriptionPlan(id) {
            const confirmDelete = () => {
                if (typeof Swal !== 'undefined' && window.Swal) {
                    Swal.fire({
                        title: 'Delete Subscription Plan?',
                        text: 'Are you sure you want to delete this subscription plan?',
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
                    if (confirm('Are you sure you want to delete this subscription plan?')) {
                        proceedWithDelete(id);
                    }
                }
            };
            
            const proceedWithDelete = (id) => {
                fetch(`{{ url('admin/subscriptions/plans') }}/${id}`, {
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
                                text: 'Subscription plan deleted successfully.',
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
                                text: 'Error deleting subscription plan',
                            });
                        } else {
                            alert('Error deleting subscription plan');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (typeof Swal !== 'undefined' && window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error deleting subscription plan',
                        });
                    } else {
                        alert('Error deleting subscription plan');
                    }
                });
            };
            
            confirmDelete();
        }

        // Pricing Modal Functions
        function openPricingModal() {
            document.getElementById('pricingModal').classList.remove('hidden');
            document.getElementById('pricingModalTitle').textContent = 'Add Pricing Plan';
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
            document.getElementById('pricingModal').classList.add('hidden');
        }

        function editPricingPlan(id) {
            // Find the pricing plan data
            const planData = @json($pricingPlans->keyBy('id'));
            const plan = planData[id];
            
            if (!plan) {
                console.error('Pricing plan not found:', id);
                return;
            }
            
            // Open modal and populate with existing data
            document.getElementById('pricingModal').classList.remove('hidden');
            document.getElementById('pricingModalTitle').textContent = 'Edit Pricing Plan';
            
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
            document.querySelector('#pricingForm input[name="name"]').value = plan.name || '';
            document.querySelector('#pricingForm input[name="ticket_quantity"]').value = plan.ticket_quantity || '';
            document.querySelector('#pricingForm input[name="price"]').value = plan.price || '';
            document.querySelector('#pricingForm select[name="currency"]').value = plan.currency || 'PHP';
            document.querySelector('#pricingForm select[name="type"]').value = plan.type || 'paid';
            document.querySelector('#pricingForm textarea[name="description"]').value = plan.description || '';
            document.querySelector('#pricingForm input[name="sort_order"]').value = plan.sort_order || '';
            document.querySelector('#pricingForm input[name="is_active"]').checked = plan.is_active || false;
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

        // Discount Modal Functions
        function openDiscountModal() {
            document.getElementById('discountModal').classList.remove('hidden');
            document.getElementById('discountModalTitle').textContent = 'Add Discount';
            document.getElementById('discountForm').action = '{{ route("admin.subscriptions.discounts.store") }}';
            document.getElementById('discountForm').method = 'POST';
            document.getElementById('discountForm').reset();
        }

        function closeDiscountModal() {
            document.getElementById('discountModal').classList.add('hidden');
        }

        function editDiscount(id) {
            // Implementation for editing discount
            console.log('Edit discount:', id);
        }

        function deleteDiscount(id) {
            const confirmDelete = () => {
                if (typeof Swal !== 'undefined' && window.Swal) {
                    Swal.fire({
                        title: 'Delete Discount?',
                        text: 'Are you sure you want to delete this discount?',
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
                    if (confirm('Are you sure you want to delete this discount?')) {
                        proceedWithDelete(id);
                    }
                }
            };
            
            const proceedWithDelete = (id) => {
                fetch(`{{ url('admin/subscriptions/discounts') }}/${id}`, {
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
                                text: 'Discount deleted successfully.',
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
                                text: 'Error deleting discount',
                            });
                        } else {
                            alert('Error deleting discount');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (typeof Swal !== 'undefined' && window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error deleting discount',
                        });
                    } else {
                        alert('Error deleting discount');
                    }
                });
            };
            
            confirmDelete();
        }
    </script>
</x-admin-layout>
