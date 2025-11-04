@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100 mb-4">
                Choose Your Plan
            </h1>
            <p class="text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">
                Select the perfect plan for your event management needs. Upgrade or downgrade at any time.
            </p>
        </div>

        <!-- Current Subscription Alert -->
        @if($currentSubscription)
        <div class="mb-8">
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                         <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                             Current Plan: {{ $currentSubscription->getPlan()?->name }}
                         </h3>
                        <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                            You can change your plan at any time. Changes take effect immediately.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Pricing Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            @foreach($plans as $plan)
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden {{ $plan->slug === 'pro' ? 'ring-2 ring-qricket-500 scale-105' : '' }}">
                @if($plan->slug === 'pro')
                <div class="absolute top-0 right-0 bg-qricket-500 text-white px-3 py-1 text-xs font-medium">
                    Most Popular
                </div>
                @endif

                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        {{ $plan->name }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        {{ $plan->description }}
                    </p>

                    <div class="mb-6">
                        <span class="text-4xl font-bold text-gray-900 dark:text-gray-100">
                            @if($plan->price == 0)
                                Free
                            @else
                                ₱{{ number_format($plan->price, 0) }}
                            @endif
                        </span>
                        @if($plan->price > 0)
                        <span class="text-gray-600 dark:text-gray-400">/month</span>
                        @endif
                    </div>

                    <!-- Features -->
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                {{ $plan->events_per_month === null ? 'Unlimited' : $plan->events_per_month }} events per month
                            </span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                {{ $plan->tickets_per_event === null ? 'Unlimited' : number_format($plan->tickets_per_event) }} tickets per event
                            </span>
                        </li>
                        @if($plan->advanced_layouts)
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                Advanced layouts
                            </span>
                        </li>
                        @endif
                        @if($plan->advanced_analytics)
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                Advanced analytics
                            </span>
                        </li>
                        @endif
                    </ul>

                    <!-- Action Button -->
                    <form method="POST" action="{{ route('organizer.subscription.subscribe') }}" class="subscription-form" data-plan-id="{{ $plan->id }}">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        
                         @php
                             $currentPlan = $currentSubscription?->getPlan();
                             $isCurrentPlan = $currentPlan && $currentPlan->id === $plan->id;
                             $isFreePlan = $plan->slug === 'free';
                             $hasNoSubscription = !$currentSubscription;
                             $isFreeCurrent = $hasNoSubscription && $isFreePlan;
                             $isHigherPlan = $currentPlan && $plan->sort_order > $currentPlan->sort_order;
                             $isLowerPlan = $currentPlan && $plan->sort_order < $currentPlan->sort_order;
                         @endphp

                         @if($isCurrentPlan || $isFreeCurrent)
                         <button type="button" disabled class="w-full bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 py-2 px-4 rounded-lg font-medium cursor-not-allowed">
                             Current Plan
                         </button>
                         @elseif($isLowerPlan)
                         <button type="button" disabled class="w-full bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 py-2 px-4 rounded-lg font-medium cursor-not-allowed">
                             Downgrade Not Available
                         </button>
                         @elseif(auth()->guard('admin')->check())
                         <button type="button" disabled class="w-full bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 py-2 px-4 rounded-lg font-medium cursor-not-allowed">
                             Admin Account
                         </button>
                         @else
                         <button type="submit" class="subscription-btn w-full {{ $plan->slug === 'pro' ? 'bg-qricket-500 hover:bg-qricket-600 text-white' : 'bg-gray-900 dark:bg-gray-700 hover:bg-gray-800 dark:hover:bg-gray-600 text-white' }} py-2 px-4 rounded-lg font-medium transition-colors duration-200" data-plan-name="{{ $plan->name }}">
                             @if($plan->slug === 'enterprise')
                                 Contact Sales
                             @else
                                 {{ $currentSubscription ? 'Upgrade to ' . $plan->name : 'Get Started' }}
                             @endif
                         </button>
                         @endif
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        <!-- FAQ Section -->
        <div class="mt-16">
            <h2 class="text-3xl font-bold text-center text-gray-900 dark:text-gray-100 mb-8">
                Frequently Asked Questions
            </h2>
            <div class="max-w-3xl mx-auto space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Can I change my plan anytime?
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Yes! You can upgrade or downgrade your plan at any time. Changes take effect immediately.
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        What happens to my events if I downgrade?
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Your existing events will continue to work. You'll only be limited by the new plan's restrictions for creating new events.
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Do you offer refunds?
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        We offer a 30-day money-back guarantee for all paid plans. Contact support for assistance.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100 mb-4">
                Choose Your Plan
            </h1>
            <p class="text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">
                Select the perfect plan for your event management needs. Upgrade or downgrade at any time.
            </p>
        </div>

        <!-- Current Subscription Alert -->
        @if($currentSubscription)
        <div class="mb-8">
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                         <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                             Current Plan: {{ $currentSubscription->getPlan()?->name }}
                         </h3>
                        <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                            You can change your plan at any time. Changes take effect immediately.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Pricing Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            @foreach($plans as $plan)
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden {{ $plan->slug === 'pro' ? 'ring-2 ring-qricket-500 scale-105' : '' }}">
                @if($plan->slug === 'pro')
                <div class="absolute top-0 right-0 bg-qricket-500 text-white px-3 py-1 text-xs font-medium">
                    Most Popular
                </div>
                @endif

                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        {{ $plan->name }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        {{ $plan->description }}
                    </p>

                    <div class="mb-6">
                        <span class="text-4xl font-bold text-gray-900 dark:text-gray-100">
                            @if($plan->price == 0)
                                Free
                            @else
                                ₱{{ number_format($plan->price, 0) }}
                            @endif
                        </span>
                        @if($plan->price > 0)
                        <span class="text-gray-600 dark:text-gray-400">/month</span>
                        @endif
                    </div>

                    <!-- Features -->
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                {{ $plan->events_per_month === null ? 'Unlimited' : $plan->events_per_month }} events per month
                            </span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                {{ $plan->tickets_per_event === null ? 'Unlimited' : number_format($plan->tickets_per_event) }} tickets per event
                            </span>
                        </li>
                        @if($plan->advanced_layouts)
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                Advanced layouts
                            </span>
                        </li>
                        @endif
                        @if($plan->advanced_analytics)
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                Advanced analytics
                            </span>
                        </li>
                        @endif
                    </ul>

                    <!-- Action Button -->
                    <form method="POST" action="{{ route('organizer.subscription.subscribe') }}">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        
                         @php
                             $currentPlan = $currentSubscription?->getPlan();
                             $isCurrentPlan = $currentPlan && $currentPlan->id === $plan->id;
                             $isFreePlan = $plan->slug === 'free';
                             $hasNoSubscription = !$currentSubscription;
                             $isFreeCurrent = $hasNoSubscription && $isFreePlan;
                             $isHigherPlan = $currentPlan && $plan->sort_order > $currentPlan->sort_order;
                             $isLowerPlan = $currentPlan && $plan->sort_order < $currentPlan->sort_order;
                         @endphp

                         @if($isCurrentPlan || $isFreeCurrent)
                         <button type="button" disabled class="w-full bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 py-2 px-4 rounded-lg font-medium cursor-not-allowed">
                             Current Plan
                         </button>
                         @elseif($isLowerPlan)
                         <button type="button" disabled class="w-full bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 py-2 px-4 rounded-lg font-medium cursor-not-allowed">
                             Downgrade Not Available
                         </button>
                         @else
                         <button type="submit" class="w-full {{ $plan->slug === 'pro' ? 'bg-qricket-500 hover:bg-qricket-600 text-white' : 'bg-gray-900 dark:bg-gray-700 hover:bg-gray-800 dark:hover:bg-gray-600 text-white' }} py-2 px-4 rounded-lg font-medium transition-colors duration-200">
                             @if($plan->slug === 'enterprise')
                                 Contact Sales
                             @else
                                 {{ $currentSubscription ? 'Upgrade to ' . $plan->name : 'Get Started' }}
                             @endif
                         </button>
                         @endif
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        <!-- FAQ Section -->
        <div class="mt-16">
            <h2 class="text-3xl font-bold text-center text-gray-900 dark:text-gray-100 mb-8">
                Frequently Asked Questions
            </h2>
            <div class="max-w-3xl mx-auto space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Can I change my plan anytime?
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Yes! You can upgrade or downgrade your plan at any time. Changes take effect immediately.
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        What happens to my events if I downgrade?
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Your existing events will continue to work. You'll only be limited by the new plan's restrictions for creating new events.
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Do you offer refunds?
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        We offer a 30-day money-back guarantee for all paid plans. Contact support for assistance.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

