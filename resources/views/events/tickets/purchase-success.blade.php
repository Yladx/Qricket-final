<x-app-layout>
    <x-slot name="title">Payment Successful - {{ $purchase->event->title }}</x-slot>
    
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-4">
            <!-- Back to Event (top) -->
            <div>
                <a href="{{ route('events.show', $purchase->event) }}" class="inline-flex items-center text-sm text-qricket-600 dark:text-qricket-400 hover:underline">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to Event
                </a>
            </div>
            <div class="text-center">
                <!-- Success Icon -->
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 dark:bg-green-900 mb-4">
                    <svg class="h-10 w-10 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                    Payment Successful!
                </h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    Your ticket purchase has been completed successfully.
                </p>
            </div>

            <!-- Purchase Details -->
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-4">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-3">Purchase Details</h3>
                
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-300">Event:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $purchase->event->title }}</span>
                    </div>
                    
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-300">Ticket Type:</span>
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ isset($purchase->unit_price) ? ('Ticket (₱' . number_format($purchase->unit_price, 2) . ')') : 'Ticket' }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-300">Quantity:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $purchase->quantity }}</span>
                    </div>
                    
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-300">Unit Price:</span>
                        <span class="font-medium text-gray-900 dark:text-white">₱{{ number_format($purchase->unit_price, 0, ',', '.') }}</span>
                    </div>
                    
                    <div class="border-t border-gray-200 dark:border-gray-600 pt-2">
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-900 dark:text-white">Total Paid:</span>
                            <span class="font-semibold text-green-600 dark:text-green-400">₱{{ number_format($purchase->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 p-3 bg-green-50 dark:bg-green-900 rounded-lg">
                    <div class="flex items-center">
                        <svg class="h-4 w-4 text-green-600 dark:text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-xs text-green-700 dark:text-green-300">
                            Purchase ID: <span class="font-mono">{{ $purchase->purchase_id }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Event Details -->
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-4">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-3">Event Information</h3>
                
                <div class="space-y-2">
                    <div class="flex items-center text-sm">
                        <svg class="h-4 w-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span class="text-gray-600 dark:text-gray-300">{{ \Carbon\Carbon::parse($purchase->event->start_date)->format('M d, Y g:i A') }}</span>
                    </div>
                    
                    @if($purchase->event->location)
                    <div class="flex items-center text-sm">
                        <svg class="h-4 w-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="text-gray-600 dark:text-gray-300">{{ $purchase->event->location }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                @php
                    $invoiceUrl = url('/tickets/invoice/' . ($purchase->purchase_id ?? $purchase->id) . '/' . ($purchase->ticketInformation->first()->ticket_id ?? '')) . '?from=profile';
                @endphp
                <a href="{{ $invoiceUrl }}" 
                   class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 text-center">
                    View Invoice
                </a>
                <a href="{{ route('my-tickets.index') }}" 
                   class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 text-center">
                    View Tickets
                </a>
                <a href="{{ route('events.show', $purchase->event) }}" 
                   class="bg-qricket-500 hover:bg-qricket-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 text-center">
                    View Event
                </a>
            </div>
            
            <div class="text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    A confirmation email has been sent to {{ $purchase->customer_details['email'] }}
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
