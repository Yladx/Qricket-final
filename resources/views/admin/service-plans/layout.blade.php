<x-admin-layout :title="$title" :page-title="$pageTitle">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $pageTitle }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $description }}</p>
            </div>
            {{ $headerActions ?? '' }}
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Quick Navigation Sidebar -->
                <div class="lg:w-64 flex-shrink-0">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 sticky top-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Service Plans</h3>
                        <nav class="space-y-2">
                            <a href="{{ route('admin.subscriptions.index', ['section' => 'ticket-pricing']) }}" 
                               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request('section') === 'ticket-pricing' ? 'bg-qricket-100 text-qricket-700 dark:bg-qricket-900 dark:text-qricket-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                                Ticket Pricing
                            </a>
                            <a href="{{ route('admin.subscriptions.index', ['section' => 'discounts']) }}" 
                               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request('section') === 'discounts' ? 'bg-qricket-100 text-qricket-700 dark:bg-qricket-900 dark:text-qricket-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                Discounts
                            </a>
                            <a href="{{ route('admin.subscriptions.index', ['section' => 'subscriptions']) }}" 
                               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request('section') === 'subscriptions' ? 'bg-qricket-100 text-qricket-700 dark:bg-qricket-900 dark:text-qricket-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M9 8h6M5 4h14a2 2 0 012 2v12l-4-2-4 2-4-2-4 2V6a2 2 0 012-2z"></path>
                                </svg>
                                Subscriptions
                            </a>
                        </nav>
                        
                        <!-- Quick Stats -->
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Quick Stats</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Total Plans</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $quickStats['total_plans'] ?? 0 }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Active Discounts</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $quickStats['active_discounts'] ?? 0 }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Subscribers</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $quickStats['subscribers'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="flex-1 min-w-0">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
