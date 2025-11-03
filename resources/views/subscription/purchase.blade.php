<x-app-layout title="Purchase Subscription">
    <x-slot name="header">
        <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Purchase Subscription</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Choose a subscription plan and complete your purchase
                        </p>
                    </div>
                    <a href="{{ route('subscription.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Plans
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6">
        <div class="max-w-4xl mx-auto">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Complete Your Subscription Purchase</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Select your plan, apply discounts, and provide your information</p>
                
                @if($currentSubscription && $currentSubscription->getPlan())
                    <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 dark:text-blue-400 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                                    Current Plan: <span class="font-bold">{{ $currentSubscription->getPlan()->name }}</span>
                                </p>
                                <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">
                                    @if($currentSubscription->expires_at)
                                        Your current subscription expires on {{ $currentSubscription->expires_at->format('M j, Y') }}.
                                    @else
                                        Your current subscription is active.
                                    @endif
                                    Purchasing a new plan will cancel your current subscription and activate the new plan immediately.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
                    <form id="subscriptionPurchaseForm" method="POST" action="{{ route('subscription.create') }}">
                        @csrf
                        
                        <!-- Step 1: Select Subscription Plan -->
                        <div class="mb-8">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Choose Your Plan</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                @foreach($plans as $plan)
                                    @php
                                        $currentPlan = $currentSubscription?->getPlan();
                                        $isFreePlan = $plan->slug === 'free' || $plan->price == 0;
                                        // If user has no subscription, they're effectively on the free plan
                                        // So if this is the free plan, it's their current plan
                                        if (!$currentSubscription && $isFreePlan) {
                                            $isCurrentPlan = true;
                                        } else {
                                            $isCurrentPlan = $currentPlan && $currentPlan->id === $plan->id;
                                        }
                                        $isHigherPlan = $currentPlan && $plan->sort_order > $currentPlan->sort_order;
                                        $isLowerPlan = $currentPlan && $plan->sort_order < $currentPlan->sort_order;
                                        // Check if user has free subscription or no subscription at all (defaults to free)
                                        $hasFreeSubscription = ($currentPlan && ($currentPlan->slug === 'free' || $currentPlan->price == 0)) || (!$currentSubscription && $isFreePlan);
                                        $isFreePlanForFreeUser = $isFreePlan && $hasFreeSubscription;
                                        $isDisabled = $isCurrentPlan || $isLowerPlan || $isFreePlanForFreeUser;
                                    @endphp
                                    <div class="relative">
                                        <input type="radio" name="plan_id" value="{{ $plan->id }}" id="plan_{{ $plan->id }}" 
                                               class="sr-only peer" {{ $selectedPlan && $selectedPlan->id == $plan->id ? 'checked' : '' }}
                                               @if($isDisabled) disabled @endif>
                                        <label for="plan_{{ $plan->id }}" 
                                               class="block bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden {{ $isDisabled ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer peer-checked:ring-2 peer-checked:ring-qricket-500 peer-checked:scale-105 hover:shadow-xl' }} transition-all duration-200 h-full relative">
                                            
                                            @if($isCurrentPlan)
                                                <div class="absolute top-2 right-2 z-10">
                                                    <span class="px-2 py-1 text-xs font-semibold bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full">
                                                        Current Plan
                                                    </span>
                                                </div>
                                            @elseif($isFreePlanForFreeUser)
                                                <div class="absolute top-2 right-2 z-10">
                                                    <span class="px-2 py-1 text-xs font-semibold bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-full">
                                                        Not Available
                                                    </span>
                                                </div>
                                            @elseif($isHigherPlan)
                                                <div class="absolute top-2 right-2 z-10">
                                                    <span class="px-2 py-1 text-xs font-semibold bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full">
                                                        Upgrade
                                                    </span>
                                                </div>
                                            @elseif($isLowerPlan)
                                                <div class="absolute top-2 right-2 z-10">
                                                    <span class="px-2 py-1 text-xs font-semibold bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-full">
                                                        Not Available
                                                    </span>
                                                </div>
                                            @endif
                                            
                                            <div class="p-4 h-full flex flex-col">
                                                <div class="flex-grow">
                                                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">
                                                        {{ $plan->name }}
                                                    </h3>
                                                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
                                                        {{ $plan->description }}
                                                    </p>

                                                    <div class="mb-4">
                                                        <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                                            @if($plan->price == 0)
                                                                Free
                                                            @else
                                                                ₱{{ number_format($plan->price, 0) }}
                                                            @endif
                                                        </span>
                                                        @if($plan->price > 0)
                                                        <span class="text-sm text-gray-600 dark:text-gray-400">/month</span>
                                                        @endif
                                                    </div>

                                                    <!-- Features -->
                                                    <ul class="space-y-2 mb-6">
                                                        <li class="flex items-center">
                                                            <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                            <span class="text-xs text-gray-700 dark:text-gray-300">
                                                                {{ $plan->events_per_month === null ? 'Unlimited' : $plan->events_per_month }} events/month
                                                            </span>
                                                        </li>
                                                        <li class="flex items-center">
                                                            <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                            <span class="text-xs text-gray-700 dark:text-gray-300">
                                                                {{ $plan->tickets_per_event === null ? 'Unlimited' : number_format($plan->tickets_per_event) }} tickets/event
                                                            </span>
                                                        </li>
                                                        @if($plan->advanced_layouts)
                                                        <li class="flex items-center">
                                                            <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                            <span class="text-xs text-gray-700 dark:text-gray-300">
                                                                Advanced layouts
                                                            </span>
                                                        </li>
                                                        @endif
                                                        @if($plan->advanced_analytics)
                                                        <li class="flex items-center">
                                                            <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                            <span class="text-xs text-gray-700 dark:text-gray-300">
                                                                Advanced analytics
                                                            </span>
                                                        </li>
                                                        @endif
                                                    </ul>
                                                </div>

                                                <!-- Selection Indicator -->
                                                <div class="mt-auto">
                                                    <div class="text-center">
                                                        @if($isCurrentPlan)
                                                            <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                                </svg>
                                                                Current Plan
                                                            </div>
                                                        @elseif($isFreePlanForFreeUser)
                                                            <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                                </svg>
                                                                Not Available
                                                            </div>
                                                        @elseif($isLowerPlan)
                                                            <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                                </svg>
                                                                Downgrade Not Available
                                                            </div>
                                                        @else
                                                            <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium peer-checked:bg-qricket-100 peer-checked:text-qricket-800 dark:peer-checked:bg-qricket-900 dark:peer-checked:text-qricket-200 bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                                </svg>
                                                                <span class="peer-checked:hidden">Click to Select</span>
                                                                <span class="hidden peer-checked:inline">Selected</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Step 2: Discount Code -->
                        <div class="mb-8">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Discount Code (Optional)</h4>
                            <div class="flex flex-wrap gap-3 mb-4">
                                <input 
                                    type="text" 
                                    name="discount_code" 
                                    id="discount_code" 
                                    placeholder="Enter discount code..." 
                                    class="flex-1 min-w-[200px] px-4 py-2 bg-white text-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-qricket-500 focus:border-transparent dark:bg-gray-800 dark:text-white"
                                    maxlength="20"
                                >
                                <button 
                                    type="button" 
                                    id="applyDiscountBtn" 
                                    class="px-6 py-2 bg-qricket-500 hover:bg-qricket-600 text-white rounded-lg font-medium transition-colors duration-200"
                                >
                                    Apply
                                </button>
                            </div>
                            
                            <!-- Available Discount Codes -->
                            @php
                                $discounts = isset($availableDiscounts) ? $availableDiscounts : collect([]);
                            @endphp
                            @if($discounts->count() > 0)
                                <div class="mb-2">
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Available discounts:</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($discounts as $discount)
                                            <button 
                                                type="button" 
                                                class="discount-code-btn px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-qricket-100 dark:hover:bg-qricket-900 hover:border-qricket-500 dark:hover:border-qricket-500 transition-colors text-gray-700 dark:text-gray-300"
                                                data-code="{{ $discount->code }}"
                                            >
                                                {{ $discount->code }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            
                            <div id="discountResult" class="mt-2 text-sm text-center hidden"></div>
                        </div>

                        <!-- Step 3: Customer Information -->
                        <div class="mb-8">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Customer Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">First Name *</label>
                                    <input type="text" name="first_name" id="first_name" 
                                           value="{{ auth()->user()->first_name ?? '' }}" required 
                                           class="w-full bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                                </div>
                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Last Name *</label>
                                    <input type="text" name="last_name" id="last_name" 
                                           value="{{ auth()->user()->last_name ?? '' }}" required 
                                           class="w-full bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Address *</label>
                                    <input type="email" name="email" id="email" 
                                           value="{{ auth()->user()->email ?? '' }}" required 
                                           class="w-full bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone Number *</label>
                                    <input type="tel" name="phone" id="phone" 
                                           value="{{ auth()->user()->phone ?? '' }}" required 
                                           class="w-full bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                                </div>
                                <div class="md:col-span-2">
                                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Address *</label>
                                    <textarea name="address" id="address" rows="3" required 
                                              class="w-full bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500"
                                              placeholder="Enter your complete address">{{ auth()->user()->address ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4: Order Summary -->
                        <div class="mb-8">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Order Summary</h4>
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <div id="orderSummary">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-gray-600 dark:text-gray-300">Selected Plan:</span>
                                        <span id="selectedPlanName" class="font-medium text-gray-900 dark:text-gray-100">{{ $selectedPlan->name ?? 'Select a plan' }}</span>
                                    </div>
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-gray-600 dark:text-gray-300">Price:</span>
                                        <span id="planPrice" class="font-medium text-gray-900 dark:text-gray-100">
                                            @if($selectedPlan)
                                                @if($selectedPlan->price == 0)
                                                    Free
                                                @else
                                                    ₱{{ number_format($selectedPlan->price) }}
                                                @endif
                                            @else
                                                ₱0
                                            @endif
                                        </span>
                                    </div>
                                    <!-- Discount Row (hidden until applied) -->
                                    <div id="discountRow" class="flex justify-between items-center mb-2 hidden">
                                        <span class="text-gray-600 dark:text-gray-300">Discount:</span>
                                        <span id="discountAmount" class="font-medium text-green-600 dark:text-green-400">-₱0.00</span>
                                    </div>
                                    <hr class="my-2 border-gray-300 dark:border-gray-600">
                                    <div class="flex justify-between items-center">
                                        <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">Total:</span>
                                        <span id="totalAmount" class="text-lg font-bold text-qricket-600 dark:text-qricket-400">
                                            @if($selectedPlan)
                                                @if($selectedPlan->price == 0)
                                                    Free
                                                @else
                                                    ₱{{ number_format($selectedPlan->price) }}
                                                @endif
                                            @else
                                                ₱0
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button type="submit" id="purchaseBtn" 
                                    class="px-6 py-3 bg-qricket-500 hover:bg-qricket-600 text-white font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                <span id="purchaseBtnText">Proceed to Payment</span>
                                <svg id="purchaseBtnSpinner" class="hidden w-4 h-4 ml-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('subscriptionPurchaseForm');
            const discountCodeInput = document.getElementById('discount_code');
            const applyDiscountBtn = document.getElementById('applyDiscountBtn');
            const discountResult = document.getElementById('discountResult');
            const discountRow = document.getElementById('discountRow');
            const discountAmount = document.getElementById('discountAmount');
            const selectedPlanName = document.getElementById('selectedPlanName');
            const planPrice = document.getElementById('planPrice');
            const totalAmount = document.getElementById('totalAmount');
            const purchaseBtn = document.getElementById('purchaseBtn');
            const purchaseBtnText = document.getElementById('purchaseBtnText');
            const purchaseBtnSpinner = document.getElementById('purchaseBtnSpinner');

            let currentPlan = null;
            let appliedDiscount = null;

            // Update order summary when plan changes
            function updateOrderSummary() {
                const selectedPlanInput = document.querySelector('input[name="plan_id"]:checked');
                if (selectedPlanInput) {
                    const planId = selectedPlanInput.value;
                    const planLabel = selectedPlanInput.nextElementSibling;
                    // Our markup uses h3 for name and .text-2xl for price
                    const planNameEl = planLabel.querySelector('h3');
                    const planPriceEl = planLabel.querySelector('.text-2xl');
                    const planName = planNameEl ? planNameEl.textContent.trim() : 'Selected Plan';
                    const planPriceText = planPriceEl ? planPriceEl.textContent.trim() : '₱0';
                    
                    currentPlan = {
                        id: planId,
                        name: planName,
                        price: planPriceText.includes('Free') ? 0 : parseFloat(planPriceText.replace(/[₱,]/g, ''))
                    };
                    
                    selectedPlanName.textContent = planName;
                    planPrice.textContent = planPriceText;
                    calculateTotal();
                }
            }

            // Calculate total with discount
            function calculateTotal() {
                if (!currentPlan) return;
                
                let total = currentPlan.price;
                let discountValue = 0;
                
                if (appliedDiscount) {
                    if (appliedDiscount.type === 'percentage') {
                        discountValue = (currentPlan.price * appliedDiscount.value) / 100;
                    } else {
                        discountValue = appliedDiscount.value;
                    }
                    total = Math.max(0, total - discountValue);
                    
                    if (discountRow) {
                        discountRow.classList.remove('hidden');
                    }
                    if (discountAmount) {
                        discountAmount.textContent = `-₱${discountValue.toFixed(2)}`;
                    }
                } else {
                    if (discountRow) {
                        discountRow.classList.add('hidden');
                    }
                }
                
                if (total === 0) {
                    totalAmount.textContent = 'Free';
                } else {
                    totalAmount.textContent = `₱${total.toFixed(2)}`;
                }
            }

            // Apply discount code
            applyDiscountBtn.addEventListener('click', function() {
                const code = discountCodeInput.value.trim();
                if (!code) return;
                
                applyDiscountBtn.disabled = true;
                applyDiscountBtn.textContent = 'Applying...';
                
                fetch('/api/discount/validate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        code: code,
                        type: 'subscription'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        appliedDiscount = data.discount;
                        discountResult.innerHTML = `<div class="text-green-600 dark:text-green-400 text-sm">✓ ${data.message}</div>`;
                        discountResult.classList.remove('hidden');
                        calculateTotal();
                    } else {
                        appliedDiscount = null;
                        discountResult.innerHTML = `<div class="text-red-600 dark:text-red-400 text-sm">✗ ${data.message}</div>`;
                        discountResult.classList.remove('hidden');
                        calculateTotal();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    discountResult.innerHTML = `<div class="text-red-600 dark:text-red-400 text-sm">✗ Error applying discount code</div>`;
                    discountResult.classList.remove('hidden');
                })
                .finally(() => {
                    applyDiscountBtn.disabled = false;
                    applyDiscountBtn.textContent = 'Apply';
                });
            });

            // Update order summary when plan selection changes
            document.querySelectorAll('input[name="plan_id"]').forEach(input => {
                input.addEventListener('change', updateOrderSummary);
            });

            // Handle clickable discount code buttons
            document.querySelectorAll('.discount-code-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const code = this.getAttribute('data-code');
                    discountCodeInput.value = code;
                    // Trigger the apply button click
                    applyDiscountBtn.click();
                });
            });

            // Form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                purchaseBtn.disabled = true;
                purchaseBtnText.textContent = 'Processing...';
                purchaseBtnSpinner.classList.remove('hidden');
                
                // Submit form data
                const formData = new FormData(form);
                
                // Debug: Log form data
                console.log('Submitting subscription form...');
                for (let [key, value] of formData.entries()) {
                    console.log(key, value);
                }
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    // Try to parse response even if not ok, to get error message
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json().then(data => {
                            if (!response.ok) {
                                // Extract error message from validation errors or message field
                                let errorMessage = data.message || data.error || `HTTP error! status: ${response.status}`;
                                
                                // If there are validation errors, format them nicely
                                if (data.errors && typeof data.errors === 'object') {
                                    const validationErrors = [];
                                    for (const field in data.errors) {
                                        if (Array.isArray(data.errors[field])) {
                                            validationErrors.push(...data.errors[field]);
                                        } else {
                                            validationErrors.push(data.errors[field]);
                                        }
                                    }
                                    if (validationErrors.length > 0) {
                                        errorMessage = validationErrors.join('. ');
                                    }
                                }
                                
                                throw {
                                    status: response.status,
                                    message: errorMessage,
                                    data: data
                                };
                            }
                            return data;
                        }).catch(err => {
                            // If JSON parsing fails, throw with status
                            throw {
                                status: response.status,
                                message: `Server error (${response.status}). Please try again.`
                            };
                        });
                    } else {
                        // Not JSON response
                        throw {
                            status: response.status,
                            message: `Server error (${response.status}). Please try again.`
                        };
                    }
                })
                .then(data => {
                    if (data.success) {
                        if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                        } else {
                            window.location.href = data.invoice_url;
                        }
                    } else {
                        if (typeof Swal !== 'undefined' && window.Swal) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || data.error || 'Failed to process subscription',
                            });
                        } else {
                            alert('Error: ' + (data.message || data.error || 'Failed to process subscription'));
                        }
                        purchaseBtn.disabled = false;
                        purchaseBtnText.textContent = 'Proceed to Payment';
                        purchaseBtnSpinner.classList.add('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Try to get error message from backend response
                    let errorMessage = 'Error processing subscription. Please try again.';
                    if (error.message) {
                        errorMessage = error.message;
                    } else if (error.data && error.data.message) {
                        errorMessage = error.data.message;
                    } else if (error.data && error.data.error) {
                        errorMessage = error.data.error;
                    }
                    if (typeof Swal !== 'undefined' && window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage,
                        });
                    } else {
                        alert(errorMessage);
                    }
                    purchaseBtn.disabled = false;
                    purchaseBtnText.textContent = 'Proceed to Payment';
                    purchaseBtnSpinner.classList.add('hidden');
                });
            });

            // Initialize order summary
            updateOrderSummary();
        });
    </script>
</x-app-layout>

