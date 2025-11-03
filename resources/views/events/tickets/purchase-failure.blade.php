<x-app-layout>
    <x-slot name="title">Payment Failed - {{ $purchase->event->title }}</x-slot>
    
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <!-- Failure Icon -->
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-100 dark:bg-red-900 mb-6">
                    <svg class="h-12 w-12 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                
                <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-2">
                    Payment Failed
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-300 mb-8">
                    Unfortunately, your payment could not be processed.
                </p>
            </div>

            <!-- Purchase Details -->
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Purchase Details</h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">Event:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $purchase->event->title }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">Ticket Type:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $purchase->ticketType->name ?? 'Unknown' }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">Quantity:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $purchase->quantity }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">Amount:</span>
                        <span class="font-medium text-gray-900 dark:text-white">₱{{ number_format($purchase->total_amount, 0, ',', '.') }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-300">Status:</span>
                        <span class="font-medium text-red-600 dark:text-red-400 capitalize">{{ $purchase->status }}</span>
                    </div>
                </div>
                
                <div class="mt-6 p-4 bg-red-50 dark:bg-red-900 rounded-lg">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-red-600 dark:text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm text-red-700 dark:text-red-300">
                            Purchase ID: <span class="font-mono">{{ $purchase->purchase_id }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Help Information -->
            <div class="bg-blue-50 dark:bg-blue-900 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-3">What can you do?</h3>
                <ul class="space-y-2 text-sm text-blue-800 dark:text-blue-200">
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>Try purchasing again with a different payment method</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>Check your payment method details and balance</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>Contact support if the problem persists</span>
                    </li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('events.show', $purchase->event) }}" 
                   class="flex-1 bg-qricket-500 hover:bg-qricket-600 text-white px-6 py-3 rounded-lg text-center font-medium transition-colors duration-200">
                    Try Again
                </a>
                <a href="{{ route('dashboard') }}" 
                   class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg text-center font-medium transition-colors duration-200">
                    Back to Dashboard
                </a>
            </div>
            
            <div class="text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Need help? <a href="{{ route('help.index') }}" class="text-qricket-500 hover:text-qricket-600 underline">Contact Support</a>
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
