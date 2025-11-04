@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-6">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Compact Header -->
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                Choose Your Plan
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                Select the perfect plan for your event management needs. Upgrade or downgrade at any time.
            </p>
        </div>

        @auth
            <!-- Compact Current Subscription Alert -->
            @if(auth()->user()->currentSubscription())
            <div class="mb-6">
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h3 class="text-xs font-medium text-blue-800 dark:text-blue-200">
                                Current Plan: {{ auth()->user()->currentSubscription()->getPlan()?->name }}
                            </h3>
                            <p class="text-xs text-blue-700 dark:text-blue-300">
                                @if(auth()->user()->getRemainingEventsThisMonth() === null)
                                    Unlimited published events available this month
                                @elseif(auth()->user()->getRemainingEventsThisMonth() > 0)
                                    {{ auth()->user()->getRemainingEventsThisMonth() }} published events remaining this month
                                @else
                                    You've reached your monthly published event limit
                                @endif
                            </p>
                            @php
                                $plan = auth()->user()->currentSubscription()?->getPlan();
                                $freeCap = (int)($plan?->free_events_count ?? 0);
                                $freeUsed = auth()->user()->events()->where('status','published')->where('is_free', true)->where('created_at', '>=', now()->startOfMonth())->count();
                            @endphp
                            @if($freeCap > 0)
                            <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">
                                Free events this month: {{ $freeUsed }} / {{ $freeCap }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @else
            <!-- Free Plan Alert -->
            <div class="mb-6">
                <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-600 dark:text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h3 class="text-xs font-medium text-gray-800 dark:text-gray-200">
                                Current Plan: Free
                            </h3>
                            @php
                                $freePlan = \App\Models\SubscriptionPlan::where('slug', 'free')->first();
                                $freeCap = (int)($freePlan?->free_events_count ?? 0);
                                $freeUsed = auth()->user()->events()->where('status','published')->where('is_free', true)->where('created_at', '>=', now()->startOfMonth())->count();
                                $freeRemaining = max(0, $freeCap - $freeUsed);
                            @endphp
                            @if($freeCap > 0)
                            <p class="text-xs text-gray-700 dark:text-gray-300 mt-1">
                                Free events this month: {{ $freeUsed }} / {{ $freeCap }}
                                @if($freeRemaining > 0)
                                    <span class="text-green-600 dark:text-green-400">({{ $freeRemaining }} remaining)</span>
                                @else
                                    <span class="text-red-600 dark:text-red-400">(limit reached)</span>
                                @endif
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
        @endauth

        <!-- Compact Pricing Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($plans as $plan)
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden {{ $plan->slug === 'pro' ? 'ring-2 ring-qricket-500 scale-105' : '' }}">
                @if($plan->slug === 'pro')
                <div class="absolute top-0 right-0 bg-qricket-500 text-white px-3 py-1 text-xs font-medium">
                    Most Popular
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
                        <ul class="space-y-2">
                            <li class="flex items-center">
                                <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-xs text-gray-700 dark:text-gray-300">
                                    {{ $plan->events_per_month === null ? 'Unlimited' : $plan->events_per_month }} paid events/month
                                </span>
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-xs text-gray-700 dark:text-gray-300">
                                    {{ $plan->tickets_per_event === null ? 'Unlimited' : number_format($plan->tickets_per_event) }} tickets/event
                                </span>
                            </li>
                            @if(($plan->free_events_count ?? 0) > 0)
                            <li class="flex items-center">
                                <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-xs text-gray-700 dark:text-gray-300">
                                    {{ $plan->free_events_count }} free event{{ $plan->free_events_count > 1 ? 's' : '' }} included
                                </span>
                            </li>
                            @endif
                            @if(($plan->free_event_ticket_limit ?? 0) > 0)
                            <li class="flex items-center">
                                <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-xs text-gray-700 dark:text-gray-300">
                                    {{ number_format($plan->free_event_ticket_limit) }} tickets cap for free events
                                </span>
                            </li>
                            @endif
                            @if($plan->advanced_layouts)
                            <li class="flex items-center">
                                <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-xs text-gray-700 dark:text-gray-300">
                                    Advanced layouts
                                </span>
                            </li>
                            @endif
                            @if($plan->advanced_analytics)
                            <li class="flex items-center">
                                <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-xs text-gray-700 dark:text-gray-300">
                                    Advanced analytics
                                </span>
                            </li>
                            @endif
                        </ul>
                    </div>

                    <!-- Action Button -->
                    <div class="mt-auto pt-4">
                        @auth
                            @if(true)
                                <!-- Regular subscription form for other plans -->
                                <form method="POST" action="{{ route('subscription.create') }}" class="subscription-form" data-plan-id="{{ $plan->id }}">
                                    @csrf
                                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                    
                                    @if(auth()->user()->currentSubscription() && auth()->user()->currentSubscription()->getPlan() && auth()->user()->currentSubscription()->getPlan()->id === $plan->id)
                                        <button type="button" disabled class="w-full bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 py-1.5 px-3 rounded text-sm font-medium cursor-not-allowed">
                                            Current Plan
                                        </button>
                                    @elseif(auth()->user()->currentSubscription() && auth()->user()->currentSubscription()->getPlan() && $plan->sort_order < auth()->user()->currentSubscription()->getPlan()->sort_order)
                                        <button type="button" disabled class="w-full bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 py-1.5 px-3 rounded text-sm font-medium cursor-not-allowed">
                                            Downgrade Not Available
                                        </button>
                                    @elseif(auth()->guard('admin')->check())
                                        <button type="button" disabled class="w-full bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 py-1.5 px-3 rounded text-sm font-medium cursor-not-allowed">
                                            Admin Account
                                        </button>
                                    @else
                                        <button type="submit" class="subscription-btn w-full {{ $plan->slug === 'pro' ? 'bg-qricket-500 hover:bg-qricket-600 text-white' : 'bg-gray-900 dark:bg-gray-700 hover:bg-gray-800 dark:hover:bg-gray-600 text-white' }} py-1.5 px-3 rounded text-sm font-medium transition-colors duration-200" data-plan-name="{{ $plan->name }}" data-plan-slug="{{ $plan->slug }}" @if($plan->price == 0) disabled @endif>
                                            @if($plan->price == 0)
                                                Current Plan
                                            @else
                                                {{ auth()->user()->currentSubscription() ? 'Upgrade to ' . $plan->name : 'Get Started' }}
                                            @endif
                                        </button>
                                    @endif
                                </form>
                            @endif
                        @else
                            <div class="space-y-3">
                                <a href="{{ route('login') }}" class="w-full {{ $plan->slug === 'pro' ? 'bg-qricket-500 hover:bg-qricket-600 text-white' : 'bg-gray-900 dark:bg-gray-700 hover:bg-gray-800 dark:hover:bg-gray-600 text-white' }} py-1.5 px-3 rounded text-sm font-medium transition-colors duration-200 inline-block text-center">
                                    Login to Subscribe
                                </a>
                                <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                                    Login required to purchase
                                </p>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Compact Feature Comparison Table -->
        <div class="mt-12">
            <h2 class="text-xl font-bold text-center text-gray-900 dark:text-gray-100 mb-6">
                Compare Plans & Features
            </h2>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-900 dark:text-gray-100">Features</th>
                                @foreach($plans as $plan)
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-900 dark:text-gray-100 {{ $plan->slug === 'pro' ? 'bg-qricket-50 dark:bg-qricket-900/20' : '' }}">
                                    {{ $plan->name }}
                                    @if($plan->slug === 'pro')
                                    <div class="text-xs text-qricket-600 dark:text-qricket-400 mt-1">Most Popular</div>
                                    @endif
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <!-- Pricing Row -->
                            <tr>
                                <td class="px-4 py-3 text-xs font-medium text-gray-900 dark:text-gray-100">Monthly Price</td>
                                @foreach($plans as $plan)
                                <td class="px-4 py-3 text-center text-xs text-gray-900 dark:text-gray-100 {{ $plan->slug === 'pro' ? 'bg-qricket-50 dark:bg-qricket-900/20' : '' }}">
                                    <div data-original-price="{{ $plan->price }}">
                                        @if($plan->price == 0)
                                            <span class="text-sm font-bold text-green-600 dark:text-green-400">Free</span>
                                        @else
                                            <span class="text-sm font-bold">₱{{ number_format($plan->price, 0) }}</span>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">/month</div>
                                        @endif
                                    </div>
                                </td>
                                @endforeach
                            </tr>
                            
                            <!-- Events per Month -->
                            <tr>
                                <td class="px-4 py-3 text-xs font-medium text-gray-900 dark:text-gray-100">Events per Month</td>
                                @foreach($plans as $plan)
                                <td class="px-4 py-3 text-center text-xs text-gray-900 dark:text-gray-100 {{ $plan->slug === 'pro' ? 'bg-qricket-50 dark:bg-qricket-900/20' : '' }}">
                                    @if($plan->events_per_month === null)
                                        <span class="font-semibold text-blue-600 dark:text-blue-400">Unlimited</span>
                                    @else
                                        {{ $plan->events_per_month }}
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            
                            <!-- Tickets per Event -->
                            <tr>
                                <td class="px-4 py-3 text-xs font-medium text-gray-900 dark:text-gray-100">Tickets per Event</td>
                                @foreach($plans as $plan)
                                <td class="px-4 py-3 text-center text-xs text-gray-900 dark:text-gray-100 {{ $plan->slug === 'pro' ? 'bg-qricket-50 dark:bg-qricket-900/20' : '' }}">
                                    @if($plan->tickets_per_event === null)
                                        <span class="font-semibold text-blue-600 dark:text-blue-400">Unlimited</span>
                                    @else
                                        {{ number_format($plan->tickets_per_event) }}
                                    @endif
                                </td>
                                @endforeach
                            </tr>

                            <!-- Free Events Included -->
                            <tr>
                                <td class="px-4 py-3 text-xs font-medium text-gray-900 dark:text-gray-100">Free Events Included</td>
                                @foreach($plans as $plan)
                                <td class="px-4 py-3 text-center text-xs text-gray-900 dark:text-gray-100 {{ $plan->slug === 'pro' ? 'bg-qricket-50 dark:bg-qricket-900/20' : '' }}">
                                    {{ (int)($plan->free_events_count ?? 0) }}
                                </td>
                                @endforeach
                            </tr>

                            <!-- Free Event Ticket Limit -->
                            <tr>
                                <td class="px-4 py-3 text-xs font-medium text-gray-900 dark:text-gray-100">Free Event Ticket Limit</td>
                                @foreach($plans as $plan)
                                <td class="px-4 py-3 text-center text-xs text-gray-900 dark:text-gray-100 {{ $plan->slug === 'pro' ? 'bg-qricket-50 dark:bg-qricket-900/20' : '' }}">
                                    {{ number_format((int)($plan->free_event_ticket_limit ?? 0)) }}
                                </td>
                                @endforeach
                            </tr>
                            
                            <!-- Advanced Layouts -->
                            <tr>
                                <td class="px-4 py-3 text-xs font-medium text-gray-900 dark:text-gray-100">Advanced Layouts</td>
                                @foreach($plans as $plan)
                                <td class="px-4 py-3 text-center text-xs {{ $plan->slug === 'pro' ? 'bg-qricket-50 dark:bg-qricket-900/20' : '' }}">
                                    @if($plan->advanced_layouts)
                                        <svg class="w-4 h-4 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            
                            <!-- Advanced Analytics -->
                            <tr>
                                <td class="px-4 py-3 text-xs font-medium text-gray-900 dark:text-gray-100">Advanced Analytics</td>
                                @foreach($plans as $plan)
                                <td class="px-4 py-3 text-center text-xs {{ $plan->slug === 'pro' ? 'bg-qricket-50 dark:bg-qricket-900/20' : '' }}">
                                    @if($plan->advanced_analytics)
                                        <svg class="w-4 h-4 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            
                        </tbody>
                    </table>
                </div>
                
                <!-- Action Buttons Row -->
                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4">
                    <div class="flex space-x-4">
                        <div class="w-1/4"></div> <!-- Empty space for features column -->
                        @foreach($plans as $plan)
                        <div class="w-1/4 text-center {{ $plan->slug === 'pro' ? 'bg-qricket-50 dark:bg-qricket-900/20' : '' }}">
                            @auth
                                @if(true)
                                    <!-- Regular subscription form for other plans -->
                                    <form method="POST" action="{{ route('subscription.create') }}" class="subscription-form inline" data-plan-id="{{ $plan->id }}">
                                        @csrf
                                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                        
                                        @if(auth()->user()->currentSubscription() && auth()->user()->currentSubscription()->getPlan() && auth()->user()->currentSubscription()->getPlan()->id === $plan->id)
                                        <button type="button" disabled class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 rounded-lg font-medium cursor-not-allowed text-sm">
                                            Current Plan
                                        </button>
                                        @elseif(auth()->user()->currentSubscription() && auth()->user()->currentSubscription()->getPlan() && $plan->sort_order < auth()->user()->currentSubscription()->getPlan()->sort_order)
                                        <button type="button" disabled class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 rounded-lg font-medium cursor-not-allowed text-sm">
                                            Downgrade N/A
                                        </button>
                                        @elseif(auth()->guard('admin')->check())
                                        <button type="button" disabled class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 rounded-lg font-medium cursor-not-allowed text-sm">
                                            Admin Account
                                        </button>
                                    @else
                                        <button type="submit" class="subscription-btn px-4 py-2 {{ $plan->slug === 'pro' ? 'bg-qricket-500 hover:bg-qricket-600 text-white' : 'bg-gray-900 dark:bg-gray-700 hover:bg-gray-800 dark:hover:bg-gray-600 text-white' }} rounded-lg font-medium transition-colors duration-200 text-sm" data-plan-name="{{ $plan->name }}" data-plan-slug="{{ $plan->slug }}">
                                            {{ auth()->user()->currentSubscription() ? 'Upgrade' : 'Get Started' }}
                                        </button>
                                    @endif
                                </form>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="inline-block px-4 py-2 {{ $plan->slug === 'pro' ? 'bg-qricket-500 hover:bg-qricket-600 text-white' : 'bg-gray-900 dark:bg-gray-700 hover:bg-gray-800 dark:hover:bg-gray-600 text-white' }} rounded-lg font-medium transition-colors duration-200 text-sm">
                                    Login to Subscribe
                                </a>
                            @endauth
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Compact FAQ Section -->
        <div class="mt-12">
            <h2 class="text-xl font-bold text-center text-gray-900 dark:text-gray-100 mb-6">
                Frequently Asked Questions
            </h2>
            <div class="max-w-3xl mx-auto space-y-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Can I cancel my plan anytime?
                    </h3>
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        Yes! You can cancel your plan anytime. Your subscription will remain active until the end of the current billing period, or you can request a refund if within the refund period.
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Can I downgrade my plan?
                    </h3>
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        Downgrades are not allowed. You can only upgrade to a higher plan or cancel your subscription. This ensures you maintain access to all features you've been using.
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                        Do you offer refunds?
                    </h3>
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        We offer a 7-day money-back guarantee for all paid plans. Contact support for assistance.
                    </p>
                </div>
            </div>
        </div>

        @auth
        <!-- Compact Current Subscription Link -->
        <div class="mt-8 text-center">
            <a href="{{ route('subscription.current') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-900 dark:text-gray-100 rounded text-sm font-medium transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                View My Current Subscription
            </a>
        </div>
        @endauth
    </div>
</div>

<!-- Footer -->
<x-footer />
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle subscription form submissions
    const subscriptionForms = document.querySelectorAll('.subscription-form');
    
    subscriptionForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = form.querySelector('.subscription-btn');
            const planSlug = submitBtn.getAttribute('data-plan-slug');
            
            // Redirect to purchase page with selected plan
            window.location.href = `{{ route('subscription.purchase') }}/${planSlug}`;
        });
    });
});
</script>
@endsection