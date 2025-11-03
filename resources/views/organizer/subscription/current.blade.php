@extends('layouts.app')

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

        <!-- Current Plan Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden mb-8">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ $subscription->getPlan()?->name }} Plan
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400">
                            {{ $subscription->getPlan()?->description }}
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                            @if($subscription->getPlan()?->price == 0)
                                Free
                            @else
                                ₱{{ number_format($subscription->getPlan()?->price, 0) }}/month
                            @endif
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ ucfirst($subscription->status) }}
                        </div>
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="mb-6">
                    @if($subscription->isActive())
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Active
                        </span>
                    @elseif($subscription->isExpired())
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Expired
                        </span>
                    @elseif($subscription->isCancelled())
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            Cancelled
                        </span>
                    @endif
                </div>

                <!-- Subscription Details -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Started</h3>
                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ $subscription->starts_at->format('M j, Y') }}
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Expires</h3>
                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ $subscription->expires_at ? $subscription->expires_at->format('M j, Y') : 'Never' }}
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Payment Method</h3>
                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100 capitalize">
                            {{ $subscription->payment_method ?? 'Not specified' }}
                        </p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-4">
                    <a href="{{ route('organizer.subscription.plans') }}" class="bg-qricket-500 hover:bg-qricket-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                        Change Plan
                    </a>
                    @if($subscription->isActive())
                    <form method="POST" action="{{ route('organizer.subscription.cancel') }}" class="inline" onsubmit="event.preventDefault(); if (typeof Swal !== 'undefined' && window.Swal) { Swal.fire({ title: 'Cancel Subscription?', text: 'Are you sure you want to cancel your subscription?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, cancel it', cancelButtonText: 'Keep subscription', confirmButtonColor: '#dc2626', cancelButtonColor: '#059669' }).then((result) => { if (result.isConfirmed) { event.target.submit(); } }); } else { if (confirm('Are you sure you want to cancel your subscription?')) { event.target.submit(); } } return false;">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                            Cancel Subscription
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Usage Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <!-- Events Usage -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    Events This Month
                </h3>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
                            @php
                                $planLimit = $subscription->getPlan()?->events_per_month;
                                $usedPublished = $subscription->user->events()
                                    ->where('status', 'published')
                                    ->where('created_at', '>=', now()->startOfMonth())
                                    ->count();
                            @endphp
                            <span>Used</span>
                            <span>{{ $usedPublished }} / {{ $planLimit ?? '∞' }}</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            @if($planLimit)
                                @php $percentage = min(100, ($usedPublished / max(1, $planLimit)) * 100); @endphp
                                <div class="bg-qricket-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            @else
                                <div class="bg-qricket-500 h-2 rounded-full" style="width: 100%"></div>
                            @endif
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        @if($remainingEvents === null)
                            Unlimited published events available
                        @elseif($remainingEvents > 0)
                            {{ $remainingEvents }} published events remaining this month
                        @else
                            You've reached your monthly published event limit
                        @endif
                    </p>
                </div>
            </div>

            <!-- Plan Features -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    Plan Features
                </h3>
                <ul class="space-y-2">
                    @foreach($subscription->getPlan()?->features ?? [] as $feature)
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $feature }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Billing History (if applicable) -->
        @if($subscription->payment_reference)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                Billing Information
            </h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Payment Reference:</span>
                    <span class="font-mono text-sm">{{ $subscription->payment_reference }}</span>
                </div>
                @if($subscription->expires_at)
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Next Billing:</span>
                    <span>{{ $subscription->expires_at->format('M j, Y') }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection


