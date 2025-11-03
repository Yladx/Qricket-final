<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Subscription Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 dark:text-gray-400">
            {{ __('Manage your subscription and view plan details.') }}
        </p>
    </header>

    <div class="mt-6">
        @php
            $currentSubscription = auth()->user()->currentSubscription();
            $remainingEvents = auth()->user()->getRemainingEventsThisMonth();
        @endphp

        @if($currentSubscription)
            <!-- Current Subscription Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 bg-indigo-100 dark:bg-indigo-900/20 rounded-lg flex items-center justify-center">
                                    <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    {{ $currentSubscription->getPlan()?->name }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ ucfirst($currentSubscription->status) }} subscription
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                ₱{{ number_format($currentSubscription->getPlan()?->price ?? $currentSubscription->amount, 2) }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                per month
                            </div>
                        </div>
                    </div>

                    @php
                        $planLimit = $currentSubscription->getPlan()?->events_per_month;
                        $usedPublished = auth()->user()->events()
                            ->where('status', 'published')
                            ->where('created_at', '>=', now()->startOfMonth())
                            ->count();
                        $percentage = $planLimit ? min(100, ($usedPublished / max(1, $planLimit)) * 100) : 100;
                    @endphp
                    @if($remainingEvents === null)
                        <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300">
                            Unlimited published events this month
                        </div>
                    @else
                        <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $remainingEvents > 0 ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-300' : 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-300' }}">
                            {{ $remainingEvents }} published events remaining this month
                        </div>
                    @endif

                    <!-- Events usage progress (paid events/month) -->
                    <div class="mt-3">
                        <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 dark:text-gray-400 mb-1">
                            <span>Events This Month</span>
                            <span>{{ $usedPublished }} / {{ $planLimit ?? '∞' }}</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-qricket-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>

                    @php
                        $plan = $currentSubscription->getPlan();
                        $freeLimit = (int)($plan?->free_events_count ?? 0);
                        $freeUsed = auth()->user()->events()
                            ->where('status','published')
                            ->where('is_free', true)
                            ->where('created_at', '>=', now()->startOfMonth())
                            ->count();
                        $freePct = $freeLimit > 0 ? min(100, ($freeUsed / max(1,$freeLimit)) * 100) : 0;
                    @endphp
                    @if($freeLimit > 0)
                    <!-- Free events usage progress -->
                    <div class="mt-3">
                        <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
                            <span>Free Events Used (This Month)</span>
                            <span>{{ $freeUsed }} / {{ $freeLimit }}</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $freePct }}%"></div>
                        </div>
                    </div>
                    @endif

                    <!-- Subscription Details -->
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Start Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ optional($currentSubscription->start_date ?? $currentSubscription->starts_at)->format('M d, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">End Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ optional($currentSubscription->end_date ?? $currentSubscription->expires_at)->format('M d, Y') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $currentSubscription->status === 'active' ? 'bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-300' : '' }}
                                    {{ $currentSubscription->status === 'cancelled' ? 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300' : '' }}
                                    {{ $currentSubscription->status === 'expired' ? 'bg-yellow-100 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-300' : '' }}
                                ">
                                    {{ ucfirst($currentSubscription->status) }}
                                </span>
                            </dd>
                        </div>
                    </div>

                    <!-- Plan Features -->
                    <div class="mt-6">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Plan Features</h4>
                        <ul class="space-y-2">
                            @php $planSlug = $currentSubscription->getPlan()?->slug; @endphp
                            @if($planSlug === 'basic')
                                <li class="flex items-center text-sm text-gray-600 dark:text-gray-400 dark:text-gray-400">
                                    <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Basic features
                                </li>
                                <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                    <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Email support
                                </li>
                                <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                    <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    1 user
                                </li>
                            @elseif($planSlug === 'pro')
                                <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                    <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    All Basic features
                                </li>
                                <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                    <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Priority support
                                </li>
                                <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                    <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    5 users
                                </li>
                                <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                    <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Advanced features
                                </li>
                            @elseif($planSlug === 'enterprise')
                                <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                    <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    All Pro features
                                </li>
                                <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                    <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    24/7 support
                                </li>
                                <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                    <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Unlimited users
                                </li>
                                <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                    <svg class="h-4 w-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Custom features
                                </li>
                            @endif
                        </ul>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('subscription.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Manage Subscription
                        </a>
                        @if(($currentSubscription->metadata['cancel_at_period_end'] ?? false) === false)
                        <form method="POST" action="{{ route('organizer.subscription.cancel') }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-red-300 dark:border-red-600 text-sm font-medium rounded-md text-red-700 dark:text-red-300 bg-white dark:bg-gray-800 hover:bg-red-50 dark:hover:bg-red-900/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Cancel at Period End
                            </button>
                        </form>
                        @else
                        <span class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md bg-yellow-50 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-300 border border-yellow-200 dark:border-yellow-700">
                            Cancels on {{ optional($currentSubscription->expires_at)->format('M d, Y') }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <!-- No Active Subscription (Free Plan) -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-4 py-5 sm:p-6">
                    <div class="text-center mb-4">
                        <div class="mx-auto h-12 w-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center mb-4">
                            <svg class="h-6 w-6 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Free Plan</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            You're currently on the free plan. Upgrade to unlock more features.
                        </p>
                    </div>

                    @php
                        $freePlan = \App\Models\SubscriptionPlan::where('slug', 'free')->first();
                        $freeLimit = (int)($freePlan?->free_events_count ?? 0);
                        $freeUsed = auth()->user()->events()
                            ->where('status','published')
                            ->where('is_free', true)
                            ->where('created_at', '>=', now()->startOfMonth())
                            ->count();
                        $freePct = $freeLimit > 0 ? min(100, ($freeUsed / max(1,$freeLimit)) * 100) : 0;
                        $freeRemaining = max(0, $freeLimit - $freeUsed);
                    @endphp
                    
                    @if($freeLimit > 0)
                    <!-- Free events usage progress -->
                    <div class="mb-4">
                        <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
                            <span>Free Events Used (This Month)</span>
                            <span>{{ $freeUsed }} / {{ $freeLimit }}</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $freePct }}%"></div>
                        </div>
                        <div class="mt-2 text-xs text-gray-600 dark:text-gray-400">
                            @if($freeRemaining > 0)
                                <span class="text-green-600 dark:text-green-400">{{ $freeRemaining }} free events remaining</span>
                            @else
                                <span class="text-red-600 dark:text-red-400">Free event limit reached</span>
                            @endif
                        </div>
                    </div>
                    @endif

                    <div class="text-center">
                        <a href="{{ route('subscription.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            View Plans
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Subscription History -->
        @if(auth()->user()->subscriptions()->count() > 0)
            <div class="mt-8">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Subscription History</h3>
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-600">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Plan
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Amount
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Period
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach(auth()->user()->subscriptions()->latest()->take(5)->get() as $subscription)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ optional($subscription->getPlan())->name ?? ucfirst($subscription->plan_id).' Plan' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        ₱{{ number_format($subscription->amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            @if($subscription->status === 'active') bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-300
                                            @elseif($subscription->status === 'cancelled') bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300
                                            @elseif($subscription->status === 'expired') bg-yellow-100 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-300
                                            @elseif($subscription->status === 'failed') bg-red-100 dark:bg-red-900/20 text-red-800 dark:text-red-300
                                            @else bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300
                                            @endif">
                                            {{ ucfirst($subscription->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ optional($subscription->start_date ?? $subscription->starts_at)->format('M d, Y') }} - {{ optional($subscription->end_date ?? $subscription->expires_at)->format('M d, Y') ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>