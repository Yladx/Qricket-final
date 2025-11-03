@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 text-center">
            <div class="mb-6">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>
            
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">
                Payment Successful!
            </h1>
            
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Your additional ticket purchase has been completed successfully. You now have access to create more tickets for your events.
            </p>
            
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Purchase Details</h3>
                <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                    <p><strong>Event:</strong> {{ $purchase->event->title }}</p>
                    <p><strong>Additional Tickets:</strong> {{ $purchase->additional_tickets }}</p>
                    <p><strong>Amount:</strong> ₱{{ number_format($purchase->amount, 2) }}</p>
                    <p><strong>Purchase ID:</strong> {{ $purchase->purchase_id }}</p>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('organizer.events.tickets', $purchase->event) }}" 
                   class="inline-flex items-center px-6 py-3 bg-qricket-600 hover:bg-qricket-700 text-white font-medium rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Manage Event Tickets
                </a>
                
                <a href="{{ route('dashboard') }}" 
                   class="inline-flex items-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show success alert
    Swal.fire({
        title: 'Payment Successful!',
        text: 'Your additional ticket purchase has been completed successfully. You now have access to create more tickets for your events.',
        icon: 'success',
        confirmButtonText: 'Great!',
        confirmButtonColor: '#10b981',
        timer: 5000,
        timerProgressBar: true,
        showConfirmButton: true
    });
});
</script>
@endsection
