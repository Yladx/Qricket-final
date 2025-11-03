<x-unified-layout :layout-type="$layoutType" :title="'Purchase Additional Tickets - ' . $event->title" :page-title="'Purchase Additional Tickets'">
    <x-slot name="header">
        <x-manage-header :event="$event" />
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="mb-6 bg-green-100 dark:bg-green-900 border border-green-400 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 bg-red-100 dark:bg-red-900 border border-red-400 text-red-700 dark:text-red-300 px-4 py-3 rounded">
                    <ul class="list-discount list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $isAdmin = auth()->guard('admin')->check();
                $eventEnded = $event->end_date && now()->gt($event->end_date);
            @endphp

            <div class="max-w-4xl mx-auto space-y-6">
                @if($eventEnded)
                    <!-- Event Ended - Show Purchase History Only -->
                    @if($additionalPurchases->count() > 0)
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Purchase History</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Total: {{ $totalAdditionalTickets }} tickets • Revenue: ₱{{ number_format($totalRevenue, 2) }}</p>
                            </div>
                            <div class="p-6 overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Purchase #</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">User</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Plan</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tickets</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Amount</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Invoice</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($additionalPurchases as $purchase)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">#{{ $purchase->purchase_id }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $purchase->user?->full_name ?? ($purchase->user?->first_name ?? 'Unknown') . ' ' . ($purchase->user?->last_name ?? '') }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $purchase->ticketLimitPricing?->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $purchase->additional_tickets }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 font-semibold">₱{{ number_format($purchase->net_amount ?? $purchase->amount, 2) }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $purchase->paid_at?->format('M d, Y H:i') ?? $purchase->created_at->format('M d, Y H:i') }}</td>
                                            <td class="px-4 py-2 text-sm">
                                                @if($purchase->xendit_invoice_id)
                                                    @php
                                                        $isAdmin = auth()->guard('admin')->check();
                                                        $invoiceRoute = $isAdmin ? 'admin.ticket-limits.view-invoice' : 'organizer.ticket-limits.view-invoice';
                                                    @endphp
                                                    <a href="{{ route($invoiceRoute, $purchase->purchase_id) }}" class="text-qricket-600 hover:text-qricket-800 dark:text-qricket-400 dark:hover:text-qricket-300" target="_blank">View</a>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <!-- No Purchase History -->
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                            <div class="p-12 text-center">
                                <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-gray-100">No Additional Ticket Purchases</h3>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">There are no additional ticket purchases for this event.</p>
                            </div>
                        </div>
                    @endif
                @else
                    <!-- Purchase History Summary (for admins and owners) -->
                    @if((auth()->guard('admin')->check() || $event->user_id === auth()->id()) && $additionalPurchases->count() > 0)
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Purchase History</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Total: {{ $totalAdditionalTickets }} tickets • Revenue: ₱{{ number_format($totalRevenue, 2) }}</p>
                            </div>
                            <div class="p-6 overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Purchase #</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">User</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Plan</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tickets</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Amount</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Invoice</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($additionalPurchases as $purchase)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">#{{ $purchase->purchase_id }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $purchase->user?->full_name ?? ($purchase->user?->first_name ?? 'Unknown') . ' ' . ($purchase->user?->last_name ?? '') }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $purchase->ticketLimitPricing?->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $purchase->additional_tickets }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 font-semibold">₱{{ number_format($purchase->net_amount ?? $purchase->amount, 2) }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $purchase->paid_at?->format('M d, Y H:i') ?? $purchase->created_at->format('M d, Y H:i') }}</td>
                                            <td class="px-4 py-2 text-sm">
                                                @if($purchase->xendit_invoice_id)
                                                    @php
                                                        $isAdmin = auth()->guard('admin')->check();
                                                        $invoiceRoute = $isAdmin ? 'admin.ticket-limits.view-invoice' : 'organizer.ticket-limits.view-invoice';
                                                    @endphp
                                                    <a href="{{ route($invoiceRoute, $purchase->purchase_id) }}" class="text-qricket-600 hover:text-qricket-800 dark:text-qricket-400 dark:hover:text-qricket-300" target="_blank">View</a>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- Purchase Form - Hidden for Admins -->
                    @if(!$isAdmin)
                <!-- Purchase Form -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Purchase Additional Tickets</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Choose a plan to purchase additional tickets for your event</p>
                        </div>
                        <div class="p-6">
                            <form id="purchaseForm" method="POST" action="{{ route('organizer.events.ticket-limits.purchase', $event) }}">
                                @csrf
                            
                            <!-- Step 1: Select Additional Ticket Plan -->
                            <div class="mb-8">
                                <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Select Additional Tickets</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($pricingPlans as $plan)
                                        <div class="relative">
                                            <input type="radio" name="pricing_id" value="{{ $plan->id }}" id="plan_{{ $plan->id }}" class="sr-only peer" {{ $loop->first ? 'checked' : '' }}>
                                            <label for="plan_{{ $plan->id }}" class="block p-4 border-2 border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer peer-checked:border-qricket-500 peer-checked:bg-qricket-50 dark:peer-checked:bg-qricket-900/20 hover:border-gray-300 dark:hover:border-gray-600 transition-colors">
                                                <div class="text-center">
                                                    <h5 class="font-semibold text-gray-900 dark:text-gray-100">{{ $plan->name }}</h5>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $plan->description }}</p>
                                                    <div class="mt-3">
                                                        <span class="text-xl font-bold text-qricket-600 dark:text-qricket-400">{{ $plan->formatted_price }}</span>
                                                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">{{ $plan->formatted_price_per_ticket }} per ticket</p>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                </div>

                            <!-- Step 2: Discount Code -->
                            <div class="mb-8">
                                <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Discount Code (Optional)</h4>
                                <div class="flex flex-wrap gap-3 mb-4">
                                    <input type="text" name="discount_code" id="discount_code" placeholder="Enter discount code" class="flex-1 min-w-[200px] bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-lg focus:ring-2 focus:ring-qricket-500 focus:border-transparent px-4 py-2">
                                    <button type="button" id="validateDiscountBtn" class="px-6 py-2 bg-qricket-500 hover:bg-qricket-600 text-white rounded-lg font-medium transition-colors duration-200">
                                            Validate
                                        </button>
                                </div>
                                
                                <!-- Available Discount Codes -->
                                @php
                                    $availableDiscounts = $discounts ?? collect([]);
                                @endphp
                                @if($availableDiscounts->count() > 0)
                                    <div class="mb-2">
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Available discounts:</p>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($availableDiscounts as $discount)
                                                <button 
                                                    type="button" 
                                                    class="discount-code-btn px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-qricket-100 dark:hover:bg-qricket-900 hover:border-qricket-500 dark:hover:border-qricket-500 transition-colors text-gray-700 dark:text-gray-300"
                                                    data-code="{{ $discount->code }}"
                                                >
                                                    {{ $discount->code }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                
                                    <div id="discountResult" class="mt-3 hidden"></div>
                                </div>

                            <!-- Step 3: Customer Information -->
                            <div class="mb-8">
                                <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Customer Information</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">First Name *</label>
                                        <input type="text" name="first_name" id="first_name" value="{{ auth()->user()->first_name }}" required class="w-full bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                                    </div>
                                    <div>
                                        <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Last Name *</label>
                                        <input type="text" name="last_name" id="last_name" value="{{ auth()->user()->last_name }}" required class="w-full bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                                    </div>
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Address *</label>
                                        <input type="email" name="email" id="email" value="{{ auth()->user()->email }}" required class="w-full bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                                    </div>
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone Number *</label>
                                        <input type="tel" name="phone" id="phone" placeholder="+63 912 345 6789" required class="w-full bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Complete Address *</label>
                                        <textarea name="address" id="address" rows="3" placeholder="Street, Barangay, City, Province, Postal Code" required class="w-full bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500"></textarea>
                                    </div>
                                 
                                </div>
                                </div>

                                <!-- Purchase Button -->
                            <div class="flex justify-end">
                                <button type="submit" class="px-8 py-3 bg-qricket-600 hover:bg-qricket-700 text-white font-semibold rounded-lg transition-colors">
                                        Purchase Additional Tickets
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                                            @endif
                            @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const validateBtn = document.getElementById('validateDiscountBtn');
            const discountInput = document.getElementById('discount_code');
            const discountResult = document.getElementById('discountResult');

            validateBtn.addEventListener('click', function() {
                const code = discountInput.value.trim();
                
                if (!code) {
                    showDiscountResult('Please enter a discount code', 'error');
                    return;
                }

                fetch('{{ route("organizer.ticket-limits.validate-discount") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ code: code })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.valid) {
                        showDiscountResult(`Valid! ${data.discount.formatted_value} discount applied`, 'success');
                    } else {
                        showDiscountResult(data.message, 'error');
                    }
                })
                .catch(error => {
                    showDiscountResult('Error validating discount code', 'error');
                });
            });

            function showDiscountResult(message, type) {
                discountResult.innerHTML = message;
                discountResult.className = `mt-3 p-3 rounded-md ${type === 'success' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'}`;
                discountResult.classList.remove('hidden');
            }

            // Handle clickable discount code buttons
            document.querySelectorAll('.discount-code-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const code = this.getAttribute('data-code');
                    discountInput.value = code;
                    // Trigger the validate button click
                    validateBtn.click();
                });
            });
        });
    </script>
</x-unified-layout>
