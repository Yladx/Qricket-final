<x-app-layout title="Invoice - {{ $purchase->event->title }}">
    <x-slot name="header">
        <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Invoice</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Purchase ID: {{ $purchase->purchase_id }}
                        </p>
                    </div>
                    <div class="flex space-x-3 no-print">
                        <button onclick="window.print()" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Print Invoice
                        </button>
                        <a href="{{ route('my-tickets.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-qricket-500 hover:bg-qricket-600 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to My Tickets
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Invoice Header -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-8 mb-8 print:shadow-none print:border-0">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Company Info -->
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Qricket</h2>
                        <p class="text-gray-600 dark:text-gray-400">Event Ticket Platform</p>
                        <p class="text-gray-600 dark:text-gray-400">Philippines</p>
                    </div>
                    
                    <!-- Invoice Details -->
                    <div class="text-right">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Invoice</h3>
                        <div class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                            <div><span class="font-medium">Invoice #:</span> {{ $purchase->purchase_id }}</div>
                            <div><span class="font-medium">Date:</span> {{ $purchase->created_at->format('M d, Y') }}</div>
                            <div><span class="font-medium">Status:</span> 
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Paid
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer & Event Info -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-8 mb-8 print:shadow-none print:border-0">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Customer Info -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Bill To</h3>
                        <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $purchase->customer_details['name'] ?? $purchase->user->name }}</div>
                            <div>{{ $purchase->customer_details['email'] ?? $purchase->user->email }}</div>
                            @if(isset($purchase->customer_details['phone']))
                                <div>{{ $purchase->customer_details['phone'] }}</div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Event Info -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Event Details</h3>
                        <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $purchase->event->title }}</div>
                            <div>{{ \Carbon\Carbon::parse($purchase->event->start_date)->format('M d, Y \a\t g:i A') }}</div>
                            <div>{{ $purchase->event->location }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-8 mb-8 print:shadow-none print:border-0">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Items</h3>
                @php
                    $rows = [];
                    $breakdown = $purchase->payment_details['ticket_breakdown'] ?? [];
                    if (!empty($breakdown)) {
                        foreach ($breakdown as $d) {
                            $name = is_array($d['ticket_type'] ?? null)
                                ? ($d['ticket_type']['name'] ?? 'Ticket')
                                : ($d['ticket_type']->name ?? 'Ticket');
                            $qty = (int)($d['quantity'] ?? 1);
                            $unit = (float)($d['unit_price'] ?? ($d['ticket_type']['price'] ?? ($d['ticket_type']->price ?? 0)));
                            $total = (float)($d['total_price'] ?? ($unit * $qty));
                            $rows[] = compact('name','qty','unit','total');
                        }
                    } else {
                        $rows[] = [
                            'name' => $purchase->ticketType->name ?? 'Ticket',
                            'qty' => (int) $purchase->quantity,
                            'unit' => (float) $purchase->unit_price,
                            'total' => (float) $purchase->total_amount,
                        ];
                    }
                    $subtotal = collect($rows)->sum('total');
                    $tax = (float)($purchase->payment_details['tax_amount'] ?? 0);
                    $grand = $subtotal + $tax;
                @endphp
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Qty</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Unit Price</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($rows as $r)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $r['name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 text-right">{{ $r['qty'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 text-right">₱{{ number_format($r['unit'], 2, '.', ',') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white text-right">₱{{ number_format($r['total'], 2, '.', ',') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-300">Subtotal</td>
                                <td class="px-6 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">₱{{ number_format($subtotal, 2, '.', ',') }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-300">Tax</td>
                                <td class="px-6 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">₱{{ number_format($tax, 2, '.', ',') }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="px-6 py-3 text-right text-base font-bold text-gray-900 dark:text-white">Total</td>
                                <td class="px-6 py-3 text-right text-base font-bold text-gray-900 dark:text-white">₱{{ number_format($grand, 2, '.', ',') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-8 print:shadow-none print:border-0">
                <div class="flex justify-end">
                    <div class="w-full max-w-sm">
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                                <span class="text-gray-900 dark:text-white">₱{{ number_format($purchase->total_amount, 2, '.', ',') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Payment Method:</span>
                                <span class="text-gray-900 dark:text-white">
                                    @if($purchase->payment_details['bypass_mode'] ?? false)
                                        Bypass Mode
                                    @else
                                        Xendit Payment
                                    @endif
                                </span>
                            </div>
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-2">
                                <div class="flex justify-between text-lg font-semibold">
                                    <span class="text-gray-900 dark:text-white">Total Paid:</span>
                                    <span class="text-gray-900 dark:text-white">₱{{ number_format($purchase->total_amount, 2, '.', ',') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Refund Policy -->
            <div class="mt-8 bg-blue-50 dark:bg-blue-900 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">Refund Policy</h3>
                <p class="text-sm text-blue-800 dark:text-blue-200 mb-2">
                    You may request a full refund within 7 days of your purchase. After the 7-day period, all sales are final and non-refundable.
                </p>
                <p class="text-xs text-blue-700 dark:text-blue-300 mt-2">
                    <strong>How to Request a Refund:</strong> Contact our support team through your account dashboard or email for assistance with refund requests.
                </p>
            </div>

            <!-- Terms & Conditions -->
            <div class="mt-6 bg-gray-50 dark:bg-gray-800 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Purchase Terms</h3>
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    Tickets are valid for the event date and cannot be transferred or refunded after the 7-day refund period. 
                    Please keep this invoice for your records.
                </p>
            </div>
        </div>
    </div>

    <style>
        @page { size: A4; margin: 12mm; }
        @media print {
            .no-print { display: none !important; }
            body { font-size: 12px; background: #fff !important; }
            .bg-white, .dark\:bg-gray-800, .border, .shadow, .shadow-sm { background: #fff !important; box-shadow: none !important; border: 0 !important; }
            .text-gray-900, .dark\:text-white { color: #000 !important; }
            .text-gray-600, .dark\:text-gray-400 { color: #444 !important; }
            .print\:shadow-none { box-shadow: none !important; }
            .print\:border-0 { border: 0 !important; }
            .page-break { page-break-after: always; }
        }
    </style>
</x-app-layout>
