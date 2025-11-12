@extends('admin.service-plans.layout')

@section('content')
    <x-slot name="headerActions">
        <button onclick="openDiscountModal()" class="inline-flex items-center px-4 py-2 bg-qricket-600 hover:bg-qricket-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add Discount
        </button>
    </x-slot>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="mb-6 bg-green-100 dark:bg-green-900 border border-green-400 text-green-700 dark:text-green-300 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-red-100 dark:bg-red-900 border border-red-400 text-red-700 dark:text-red-300 px-4 py-3 rounded">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Analytics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-qricket-100 dark:bg-qricket-900 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-qricket-600 dark:text-qricket-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Discounts</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $discounts->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Discounts</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $discounts->where('is_active', true)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Usage</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $totalUsage }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Savings</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format($totalSavings, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Usage Chart -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 flex flex-col">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Discount Usage Over Time</h3>
            <div class="flex-1 min-h-0" style="height: 350px;">
                <canvas id="usageChart"></canvas>
            </div>
        </div>

        <!-- Discount Types Chart -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 flex flex-col">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Discount Types Distribution</h3>
            <div class="flex-1 min-h-0 flex items-center justify-center" style="height: 350px;">
                <canvas id="discountTypesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Full-width Discount Codes Section -->
    <div class="mt-8 -mx-6 sm:-mx-6 lg:-mx-8 overflow-x-hidden">
        <div class="px-6 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Discount Codes</h3>
                </div>
                <div class="p-6 overflow-x-hidden">
                    @if($discounts->count())
                        <div class="space-y-4">
                            @foreach($discounts as $discount)
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 p-4 lg:p-5 hover:shadow-md transition-all duration-200 overflow-hidden">
                                    <!-- Header: Name, Code, Status -->
                                    <div class="flex flex-wrap items-start justify-between gap-3 mb-4 pb-4 border-b border-gray-200 dark:border-gray-600">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                                <h4 class="text-base lg:text-lg font-semibold text-gray-900 dark:text-gray-100 break-words">{{ $discount->name }}</h4>
                                                <span class="font-mono bg-gray-200 dark:bg-gray-600 px-2 py-1 rounded text-xs font-semibold text-gray-900 dark:text-gray-100 whitespace-nowrap flex-shrink-0">{{ $discount->code }}</span>
                                                <span class="px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap flex-shrink-0 {{ $discount->isValid() ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' }}">
                                                    {{ $discount->isValid() ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                            @if($discount->description)
                                                <p class="text-xs lg:text-sm text-gray-600 dark:text-gray-400 break-words">{{ $discount->description }}</p>
                                            @endif
                                        </div>
                                        <!-- Actions -->
                                        <div class="flex items-center space-x-2 flex-shrink-0">
                                            <button onclick="editDiscount({{ $discount->id }})" class="p-2 text-qricket-600 dark:text-qricket-400 hover:bg-qricket-50 dark:hover:bg-qricket-900/20 rounded-md transition-colors" title="Edit">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button onclick="deleteDiscount({{ $discount->id }})" class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors" title="Delete">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Details Grid -->
                                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                                        <div class="min-w-0">
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Type</div>
                                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ ucfirst(str_replace('_', ' ', $discount->type)) }}</div>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Value</div>
                                            <div class="text-sm font-semibold text-qricket-600 dark:text-qricket-400 truncate">{{ $discount->formatted_value }}</div>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Usage</div>
                                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $discount->used_count }}/{{ $discount->usage_limit ?: '∞' }}</div>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Applicable To</div>
                                            <div class="text-xs font-semibold text-gray-900 dark:text-gray-100 truncate">{{ ucfirst(str_replace('_', ' ', $discount->applicable_to)) }}</div>
                                        </div>
                                        @if($discount->starts_at || $discount->expires_at)
                                        <div class="min-w-0 col-span-2 md:col-span-1">
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Validity</div>
                                            <div class="text-xs font-medium text-gray-900 dark:text-gray-100 break-words">
                                                @if($discount->starts_at)
                                                    {{ \Carbon\Carbon::parse($discount->starts_at)->format('M d') }} -
                                                @endif
                                                @if($discount->expires_at)
                                                    {{ \Carbon\Carbon::parse($discount->expires_at)->format('M d, Y') }}
                                                @else
                                                    No expiry
                                                @endif
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No discounts</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new discount.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

<!-- Discount Modal -->
<div id="discountModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4" id="discountModalTitle">Add Discount</h3>
                <form id="discountForm" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                            <input type="text" name="name" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Code</label>
                            <input type="text" name="code" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                            <select name="type" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                                <option value="percentage">Percentage</option>
                                <option value="fixed_amount">Fixed Amount</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Value</label>
                            <input type="number" name="value" required min="0" step="0.01" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Currency</label>
                            <select name="currency" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                                <option value="PHP">PHP</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Applicable To</label>
                            <select name="applicable_to" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                                <option value="ticket_limit_purchase">Ticket Limit Purchase</option>
                                <option value="subscription">Subscription</option>
                                <option value="all">All</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Usage Limit</label>
                            <input type="number" name="usage_limit" min="1" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Starts At</label>
                            <input type="datetime-local" name="starts_at" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expires At</label>
                            <input type="datetime-local" name="expires_at" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <textarea name="description" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeDiscountModal()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-qricket-600 hover:bg-qricket-700 rounded-md">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
        // Chart data from backend
        const usageData = @json($usageData);
        const discountTypesData = @json($discountTypesData);

        // Usage Chart
        const usageCtx = document.getElementById('usageChart').getContext('2d');
        new Chart(usageCtx, {
            type: 'bar',
            data: {
                labels: usageData.labels,
                datasets: [{
                    label: 'Usage Count',
                    data: usageData.values,
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151',
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        enabled: true,
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151',
                            font: {
                                size: 11
                            },
                            stepSize: 1
                        },
                        grid: {
                            color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb'
                        }
                    },
                    x: {
                        ticks: {
                            color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151',
                            font: {
                                size: 11
                            },
                            maxRotation: 45,
                            minRotation: 45
                        },
                        grid: {
                            color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb',
                            display: false
                        }
                    }
                }
            }
        });

        // Discount Types Chart
        const discountTypesCtx = document.getElementById('discountTypesChart').getContext('2d');
        new Chart(discountTypesCtx, {
            type: 'doughnut',
            data: {
                labels: discountTypesData.labels,
                datasets: [{
                    data: discountTypesData.values,
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)'
                    ],
                    borderWidth: 2,
                    borderColor: document.documentElement.classList.contains('dark') ? '#1f2937' : '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'right',
                        labels: {
                            color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151',
                            font: {
                                size: 12
                            },
                            padding: 15,
                            boxWidth: 15,
                            boxHeight: 15
                        }
                    },
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    label += context.parsed;
                                }
                                return label;
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });

        // Modal Functions
        function openDiscountModal() {
            document.getElementById('discountModal').classList.remove('hidden');
            document.getElementById('discountModalTitle').textContent = 'Add Discount';
            document.getElementById('discountForm').action = '{{ route("admin.subscriptions.discounts.store") }}';
            document.getElementById('discountForm').method = 'POST';
            
            // Remove method override if it exists
            let methodInput = document.getElementById('discountForm').querySelector('input[name="_method"]');
            if (methodInput) {
                methodInput.remove();
            }
            
            // Reset form
            document.getElementById('discountForm').reset();
        }

        function closeDiscountModal() {
            document.getElementById('discountModal').classList.add('hidden');
        }

        function editDiscount(id) {
            // Find the discount data
            const discountData = @json($discounts->keyBy('id'));
            const discount = discountData[id];
            
            if (!discount) {
                console.error('Discount not found:', id);
                return;
            }
            
            // Open modal and populate with existing data
            document.getElementById('discountModal').classList.remove('hidden');
            document.getElementById('discountModalTitle').textContent = 'Edit Discount';
            
            // Set form action for update
            document.getElementById('discountForm').action = `{{ url('admin/subscriptions/discounts') }}/${id}`;
            document.getElementById('discountForm').method = 'POST';
            
            // Add method override for PUT request
            let methodInput = document.getElementById('discountForm').querySelector('input[name="_method"]');
            if (!methodInput) {
                methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                document.getElementById('discountForm').appendChild(methodInput);
            }
            methodInput.value = 'PUT';
            
            // Populate form fields
            document.querySelector('input[name="code"]').value = discount.code || '';
            document.querySelector('input[name="name"]').value = discount.name || '';
            document.querySelector('select[name="type"]').value = discount.type || 'percentage';
            document.querySelector('input[name="value"]').value = discount.value || '';
            document.querySelector('input[name="min_amount"]').value = discount.min_amount || '';
            document.querySelector('input[name="max_uses"]').value = discount.max_uses || '';
            document.querySelector('input[name="expires_at"]').value = discount.expires_at ? discount.expires_at.split(' ')[0] : '';
            document.querySelector('select[name="applicable_to"]').value = discount.applicable_to || 'all';
            document.querySelector('textarea[name="description"]').value = discount.description || '';
            document.querySelector('input[name="is_active"]').checked = discount.is_active || false;
        }

        function deleteDiscount(id) {
            const confirmDelete = () => {
                if (typeof Swal !== 'undefined' && window.Swal) {
                    Swal.fire({
                        title: 'Delete Discount?',
                        text: 'Are you sure you want to delete this discount?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, delete it',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#6b7280'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            proceedWithDelete(id);
                        }
                    });
                } else {
                    if (confirm('Are you sure you want to delete this discount?')) {
                        proceedWithDelete(id);
                    }
                }
            };
            
            const proceedWithDelete = (id) => {
                fetch(`{{ url('admin/subscriptions/discounts') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (typeof Swal !== 'undefined' && window.Swal) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted',
                                text: 'Discount deleted successfully.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        } else {
                            location.reload();
                        }
                    } else {
                        if (typeof Swal !== 'undefined' && window.Swal) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error deleting discount',
                            });
                        } else {
                            alert('Error deleting discount');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (typeof Swal !== 'undefined' && window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error deleting discount',
                        });
                    } else {
                        alert('Error deleting discount');
                    }
                });
            };
            
            confirmDelete();
        }
    </script>
@endsection