@extends('layouts.app')
@php($isOrganizerSide = false)

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                My Subscription
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Manage your subscription and view usage details.
            </p>
        </div>

        @if($currentSubscription)
        <!-- Current Plan Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden mb-8">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                            {{ $currentSubscription->getPlan()?->name ?? 'Current Plan' }}
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">
                            {{ $currentSubscription->getPlan()?->description ?? 'Your current subscription plan' }}
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-qricket-600 dark:text-qricket-400">
                            ₱{{ number_format($currentSubscription->amount, 2) }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ ucfirst($currentSubscription->billing_cycle) }}
                        </div>
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        @php
                            $cancellationStatus = $currentSubscription->getCancellationStatus();
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($cancellationStatus === 'active') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                            @elseif($cancellationStatus === 'cancelled_at_period_end') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                            @elseif($cancellationStatus === 'cancelled') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                            @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                            @if($cancellationStatus === 'active')
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Active
                            @elseif($cancellationStatus === 'cancelled_at_period_end')
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                                Cancelling at Period End
                            @elseif($cancellationStatus === 'cancelled')
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                Cancelled
                            @else
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                                {{ ucfirst($currentSubscription->status) }}
                            @endif
                        </span>
                    </div>
                    
                    @if($cancellationStatus === 'active')
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Next billing: {{ $currentSubscription->next_billing_date ? $currentSubscription->next_billing_date->format('M j, Y') : 'N/A' }}
                    </div>
                    @elseif($cancellationStatus === 'cancelled_at_period_end')
                    <div class="text-sm text-yellow-600 dark:text-yellow-400">
                        Ends on: {{ $currentSubscription->getEffectiveEndDate()?->format('M j, Y') ?? 'N/A' }}
                    </div>
                    @endif
                </div>

                <!-- Usage Information -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Events This Month</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ auth()->user()->getRemainingEventsThisMonth() ?? 'Unlimited' }}
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Plan Features</div>
                        <div class="text-sm text-gray-900 dark:text-gray-100">
                            {{ $currentSubscription->getPlan()?->max_events_per_month ?? 'Unlimited' }} events/month
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Billing Cycle</div>
                        <div class="text-sm text-gray-900 dark:text-gray-100">
                            {{ ucfirst($currentSubscription->billing_cycle) }}
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('subscription.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-qricket-500 hover:bg-qricket-600 text-white rounded-lg font-medium transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        Change Plan
                    </a>
                    
                    @if($cancellationStatus === 'active')
                    <form method="POST" action="{{ route('subscription.cancel') }}" class="inline" onsubmit="event.preventDefault(); if (typeof Swal !== 'undefined' && window.Swal) { Swal.fire({ title: 'Cancel Subscription?', text: 'Are you sure you want to cancel your subscription? It will remain active until the end of your current billing period.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, cancel it', cancelButtonText: 'Keep subscription', confirmButtonColor: '#dc2626', cancelButtonColor: '#059669' }).then((result) => { if (result.isConfirmed) { event.target.submit(); } }); } else { if (confirm('Are you sure you want to cancel your subscription? It will remain active until the end of your current billing period.')) { event.target.submit(); } } return false;">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-medium transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Cancel Subscription
                        </button>
                    </form>
                    @elseif($cancellationStatus === 'cancelled_at_period_end')
                    <form method="POST" action="{{ route('subscription.reactivate') }}" class="inline" onsubmit="event.preventDefault(); if (typeof Swal !== 'undefined' && window.Swal) { Swal.fire({ title: 'Reactivate Subscription?', text: 'Are you sure you want to reactivate your subscription?', icon: 'question', showCancelButton: true, confirmButtonText: 'Yes, reactivate', cancelButtonText: 'Cancel', confirmButtonColor: '#059669', cancelButtonColor: '#6b7280' }).then((result) => { if (result.isConfirmed) { event.target.submit(); } }); } else { if (confirm('Are you sure you want to reactivate your subscription?')) { event.target.submit(); } } return false;">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-medium transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Reactivate Subscription
                        </button>
                    </form>
                    @elseif($cancellationStatus === 'cancelled')
                    <a href="{{ route('subscription.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-qricket-500 hover:bg-qricket-600 text-white rounded-lg font-medium transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                        Purchase Again
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @else
        <!-- No Subscription Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden mb-8">
            <div class="p-6 text-center">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No Active Subscription</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    You're currently on the free plan. Upgrade to unlock more features and create unlimited events.
                </p>
                <a href="{{ route('subscription.index') }}" class="inline-flex items-center px-6 py-3 bg-qricket-500 hover:bg-qricket-600 text-white rounded-lg font-medium transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    View Plans
                </a>
            </div>
        </div>
        @endif

        <!-- Available Plans -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Available Plans</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($plans as $plan)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 {{ $plan->slug === 'pro' ? 'ring-2 ring-qricket-500' : '' }}">
                        <div class="text-center">
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ $plan->name }}</h4>
                            <div class="text-2xl font-bold text-qricket-600 dark:text-qricket-400 mb-2">
                                ₱{{ number_format($plan->price, 2) }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ ucfirst($plan->billing_cycle) }}</div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $plan->description }}</p>
                            
                            @if($currentSubscription && $currentSubscription->getPlan()?->id === $plan->id)
                            <div class="text-sm text-green-600 dark:text-green-400 font-medium">Current Plan</div>
                            @else
                            <a href="{{ route('subscription.index') }}" class="inline-block w-full text-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-gray-100 rounded-lg text-sm font-medium transition-colors duration-200">
                                {{ $currentSubscription ? 'Switch to ' . $plan->name : 'Choose ' . $plan->name }}
                            </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Available Plans</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($plans as $plan)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 {{ $plan->slug === 'pro' ? 'ring-2 ring-qricket-500' : '' }}">
                        <div class="text-center">
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ $plan->name }}</h4>
                            <div class="text-2xl font-bold text-qricket-600 dark:text-qricket-400 mb-2">
                                ₱{{ number_format($plan->price, 2) }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ ucfirst($plan->billing_cycle) }}</div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $plan->description }}</p>
                            
                            @if($currentSubscription && $currentSubscription->getPlan()?->id === $plan->id)
                            <div class="text-sm text-green-600 dark:text-green-400 font-medium">Current Plan</div>
                            @else
                            <a href="{{ route('subscription.index') }}" class="inline-block w-full text-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-gray-100 rounded-lg text-sm font-medium transition-colors duration-200">
                                {{ $currentSubscription ? 'Switch to ' . $plan->name : 'Choose ' . $plan->name }}
                            </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Available Plans</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($plans as $plan)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 {{ $plan->slug === 'pro' ? 'ring-2 ring-qricket-500' : '' }}">
                        <div class="text-center">
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ $plan->name }}</h4>
                            <div class="text-2xl font-bold text-qricket-600 dark:text-qricket-400 mb-2">
                                ₱{{ number_format($plan->price, 2) }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ ucfirst($plan->billing_cycle) }}</div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $plan->description }}</p>
                            
                            @if($currentSubscription && $currentSubscription->getPlan()?->id === $plan->id)
                            <div class="text-sm text-green-600 dark:text-green-400 font-medium">Current Plan</div>
                            @else
                            <a href="{{ route('subscription.index') }}" class="inline-block w-full text-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-gray-100 rounded-lg text-sm font-medium transition-colors duration-200">
                                {{ $currentSubscription ? 'Switch to ' . $plan->name : 'Choose ' . $plan->name }}
                            </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
