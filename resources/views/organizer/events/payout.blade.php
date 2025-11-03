<x-unified-layout :layout-type="'organizer'" :title="'Event Payout - ' . $event->title" :page-title="'Payout Request'">
    <x-slot name="header">
        <x-manage-header :event="$event" />
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if($payout)
                <!-- Payout Status Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <!-- Status Header -->
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center
                                    @if($payout->status === 'paid') bg-green-100 dark:bg-green-900
                                    @elseif($payout->status === 'released') bg-blue-100 dark:bg-blue-900
                                    @elseif($payout->status === 'approved') bg-yellow-100 dark:bg-yellow-900
                                    @elseif($payout->status === 'rejected') bg-red-100 dark:bg-red-900
                                    @else bg-gray-100 dark:bg-gray-700 @endif">
                                    @if($payout->status === 'paid')
                                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @elseif($payout->status === 'released')
                                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                        </svg>
                                    @elseif($payout->status === 'approved')
                                        <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @elseif($payout->status === 'rejected')
                                        <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @endif
                    </div>
                    <div>
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ ucfirst(str_replace('_', ' ', $payout->status)) }}</h2>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        @if($payout->status === 'paid') Payout completed successfully
                                        @elseif($payout->status === 'released') Ready to collect
                                        @elseif($payout->status === 'approved') Approved, waiting for release
                                        @elseif($payout->status === 'rejected') Request was rejected
                                        @else Under review
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full text-sm font-medium
                                @if($payout->status === 'paid') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                @elseif($payout->status === 'released') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                @elseif($payout->status === 'approved') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                @elseif($payout->status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $payout->status)) }}
                            </span>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-4">
                        <!-- Amount Breakdown - Compact -->
                        <div class="flex justify-between items-center mb-3">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Total Earnings</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format($payout->total_earnings, 2) }}</div>
                        </div>
                        <div class="flex justify-between items-center mb-3">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Qricket Fee ({{ config('app.qricket_fee_percentage', 5) }}%)</div>
                            <div class="text-lg font-semibold text-red-600">₱{{ number_format($payout->qricket_fee, 2) }}</div>
                        </div>
                        <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-200 dark:border-gray-600">
                            <div class="text-base font-medium text-gray-700 dark:text-gray-300">Net Amount</div>
                            <div class="text-xl font-bold text-green-600">₱{{ number_format($payout->net_amount, 2) }}</div>
                        </div>

                        <!-- Timeline - Compact -->
                        <div class="space-y-1 mb-3">
                            @if($payout->requested_at)
                                <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                                    <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div>
                                    <span>Requested {{ $payout->requested_at->format('M d, Y g:i A') }}</span>
                                </div>
                            @endif
                            
                            @if($payout->processed_at)
                                <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                                    <div class="w-1.5 h-1.5 bg-green-500 rounded-full mr-2"></div>
                                    <span>Processed {{ $payout->processed_at->format('M d, Y g:i A') }}</span>
                                </div>
                            @endif
                        </div>

                        @if($payout->notes)
                            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded">
                                <div class="text-xs font-medium text-blue-900 dark:text-blue-100 mb-1">Notes</div>
                                <div class="text-xs text-blue-800 dark:text-blue-200">{{ $payout->notes }}</div>
                            </div>
                        @endif
                    </div>

                    <!-- Action Button -->
                    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-600">
                        @if($payout->status === 'released' && $canPayout)
                            <button onclick="openCollectPayoutModal()" class="w-full inline-flex items-center justify-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                </svg>
                                Collect Payout
                            </button>
                        @elseif($payout->status === 'approved' && $canPayout)
                            <div class="text-center py-1">
                                <div class="text-xs text-blue-600 dark:text-blue-400 font-medium">✓ Approved - Waiting for Release</div>
                            </div>
                        @elseif(in_array($payout->status, ['pending', 'under_review']))
                            <div class="text-center py-1">
                                <div class="text-xs text-yellow-600 dark:text-yellow-400 font-medium">⏳ Under Review</div>
                            </div>
                        @elseif($payout->status === 'paid')
                            <div class="text-center py-1">
                                <div class="text-xs text-green-600 dark:text-green-400 font-medium">✓ Payout Completed</div>
                            </div>
                        @elseif($payout->status === 'rejected')
                            <div class="text-center py-1">
                                <div class="text-xs text-red-600 dark:text-red-400 font-medium">❌ Rejected</div>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <!-- No Payout Request -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="p-6 text-center">
                        <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-2">No Payout Request</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">You haven't created a payout request for this event yet.</p>
                        
                        @if($canPayout)
                            <button onclick="openPayoutRequestModal()" class="inline-flex items-center px-4 py-2 bg-qricket-600 hover:bg-qricket-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Request Payout
                            </button>
                        @else
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                Payout becomes available after the event ends and if you are the event owner.
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Payout Request Modal -->
    <div id="payoutRequestModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Request Payout</h3>
                    <button onclick="closePayoutRequestModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Payout Calculation -->
                <div class="mb-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Payout Calculation</h4>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Total Earnings:</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100" id="totalEarnings">₱0.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Qricket Fee ({{ config('app.qricket_fee_percentage', 5) }}%):</span>
                            <span class="text-sm font-semibold text-red-600" id="qricketFee">₱0.00</span>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-600 pt-3">
                            <div class="flex justify-between">
                                <span class="text-base font-medium text-gray-700 dark:text-gray-300">Net Amount:</span>
                                <span class="text-lg font-bold text-green-600" id="netAmount">₱0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label for="payoutNotes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes (Optional)</label>
                    <textarea id="payoutNotes" name="notes" rows="3" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:ring-qricket-500 focus:border-qricket-500" placeholder="Add any additional notes..."></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-3">
                    <button onclick="closePayoutRequestModal()" class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Cancel</button>
                    <button onclick="submitPayoutRequest()" class="flex-1 px-4 py-2 bg-qricket-600 hover:bg-qricket-700 text-white rounded-md text-sm font-medium transition-colors">Submit Request</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Collect Payout Modal -->
    <div id="collectPayoutModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Collect Payout</h3>
                    <button onclick="closeCollectPayoutModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Payout Information -->
                <div class="mb-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Payout Information</h4>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Amount to Collect:</span>
                            <span class="text-lg font-bold text-green-600" id="collectAmount">₱{{ $payout ? number_format($payout->net_amount, 2) : '0.00' }}</span>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Transfer will be processed within 1-2 business days via Xendit</div>
                    </div>
                </div>

                <!-- Bank Details Form -->
                <form id="collectPayoutForm">
                    <div class="mb-4">
                        <label for="bankCode" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Bank</label>
                        <select id="bankCode" name="bank_code" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:ring-qricket-500 focus:border-qricket-500">
                            <option value="">Select Bank</option>
                            <option value="BANK_TRANSFER">Bank Transfer (Generic)</option>
                            <option value="BDO_ONLINE_BANKING">BDO Bank Transfer</option>
                            <option value="BPI_ONLINE_BANKING">BPI Online Banking</option>
                            <option value="METROBANK_ONLINE_BANKING">Metrobank Bank Transfer</option>
                            <option value="PNB_ONLINE_BANKING">PNB Bank Transfer</option>
                            <option value="RCBC_ONLINE_BANKING">RCBC Online Banking</option>
                            <option value="SECURITY_BANK_ONLINE_BANKING">Security Bank Bank Transfer</option>
                            <option value="UNIONBANK_ONLINE_BANKING">UnionBank Online Banking</option>
                            <option value="CHINABANK_ONLINE_BANKING">Chinabank Online Banking</option>
                            <option value="PSBANK_ONLINE_BANKING">PSBank Bank Transfer</option>
                            <option value="LANDBANK_ONLINE_BANKING">LandBank Bank Transfer</option>
                            <option value="MAYBANK_ONLINE_BANKING">Maybank Bank Transfer</option>
                            <option value="BOC_ONLINE_BANKING">Bank of China Bank Transfer</option>
                            <option value="ROBINSONS_BANK_ONLINE_BANKING">Robinsons Bank Bank Transfer</option>
                            <option value="INSTAPAY_ONLINE_BANKING">InstaPay Bank Transfer</option>
                            <option value="PESONET_ONLINE_BANKING">PESONet Bank Transfer</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="accountNumber" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Account Number</label>
                        <input type="text" id="accountNumber" name="account_number" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:ring-qricket-500 focus:border-qricket-500" placeholder="Enter your account number">
                    </div>

                    <div class="mb-6">
                        <label for="accountName" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Account Name</label>
                        <input type="text" id="accountName" name="account_name" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:ring-qricket-500 focus:border-qricket-500" placeholder="Enter account holder name">
                            </div>

                    <!-- Processing Notice -->
                    <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="text-sm text-blue-800 dark:text-blue-200">
                                <strong>Direct Bank Transfer:</strong> The payout will be sent directly to your bank account via Xendit disbursement within 1-2 business days.
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex space-x-3">
                        <button type="button" onclick="closeCollectPayoutModal()" class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Cancel</button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md text-sm font-medium transition-colors">Process Payout</button>
                    </div>
                    </form>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let currentEventId = {{ $event->id }};

        // Modal functions
        function openPayoutRequestModal() {
            document.getElementById('payoutRequestModal').classList.remove('hidden');
            calculatePayout();
        }

        function closePayoutRequestModal() {
            document.getElementById('payoutRequestModal').classList.add('hidden');
        }

        function openCollectPayoutModal() {
            document.getElementById('collectPayoutModal').classList.remove('hidden');
        }

        function closeCollectPayoutModal() {
            document.getElementById('collectPayoutModal').classList.add('hidden');
        }

        // Calculate payout
        function calculatePayout() {
            fetch(`/organizer/events/${currentEventId}/payout/calculate`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('totalEarnings').textContent = '₱' + data.total_earnings.toLocaleString('en-US', {minimumFractionDigits: 2});
                    document.getElementById('qricketFee').textContent = '₱' + data.qricket_fee.toLocaleString('en-US', {minimumFractionDigits: 2});
                    document.getElementById('netAmount').textContent = '₱' + data.net_amount.toLocaleString('en-US', {minimumFractionDigits: 2});
                }
            })
            .catch(err => {
                console.error('Error calculating payout:', err);
            });
        }

        // Submit payout request
        function submitPayoutRequest() {
            const notes = document.getElementById('payoutNotes').value;
            const totalEarnings = document.getElementById('totalEarnings').textContent.replace(/[₱,]/g, '');
            const qricketFee = document.getElementById('qricketFee').textContent.replace(/[₱,]/g, '');
            const netAmount = document.getElementById('netAmount').textContent.replace(/[₱,]/g, '');
            
            fetch(`/organizer/events/${currentEventId}/payout/request-new`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    total_earnings: totalEarnings,
                    qricket_fee: qricketFee,
                    net_amount: netAmount,
                    notes: notes
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Payout Request Submitted!',
                        text: 'Your payout request has been submitted and is under review.',
                        confirmButtonColor: '#10b981'
                    }).then(() => {
                        closePayoutRequestModal();
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Request Failed',
                        text: data.message,
                        confirmButtonColor: '#ef4444'
                    });
                }
            })
            .catch(err => {
                console.error('Error submitting payout request:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Request Failed',
                    text: 'An error occurred while submitting the request.',
                    confirmButtonColor: '#ef4444'
                });
            });
        }

        // Handle form submission for disbursement
        document.getElementById('collectPayoutForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const bankCode = formData.get('bank_code');
            const accountNumber = formData.get('account_number');
            const accountName = formData.get('account_name');
            
            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.textContent = 'Processing...';
            submitButton.disabled = true;
            
            fetch(`/organizer/events/${currentEventId}/payout/collect-new`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    bank_code: bankCode,
                    account_number: accountNumber,
                    account_name: accountName
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Payout Processed Successfully!',
                        html: `
                            <div class="text-left">
                                <p class="mb-3">${data.message}</p>
                                ${data.disbursement_id ? `<p class="text-sm text-gray-600"><strong>Disbursement ID:</strong> ${data.disbursement_id}</p>` : ''}
                                <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                                    <p class="text-sm text-green-800">
                                        <strong>Next Steps:</strong><br>
                                        • Check your bank account within 1-2 business days<br>
                                        • You'll receive a confirmation email from Xendit<br>
                                        • Contact support if you don't receive the funds
                                    </p>
                                </div>
                            </div>
                        `,
                        confirmButtonColor: '#10b981',
                        confirmButtonText: 'Got it!'
                    }).then(() => {
                        closeCollectPayoutModal();
                        window.location.reload();
                    });
                } else {
                    // Reset button state
                    submitButton.textContent = originalText;
                    submitButton.disabled = false;
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Processing Failed',
                        text: data.message,
                        confirmButtonColor: '#ef4444'
                    });
                }
            })
            .catch(err => {
                console.error('Error processing payout:', err);
                
                // Reset button state
                submitButton.textContent = originalText;
                submitButton.disabled = false;
                
                Swal.fire({
                    icon: 'error',
                    title: 'Processing Failed',
                    text: 'An error occurred while processing the payout.',
                    confirmButtonColor: '#ef4444'
                });
            });
        });

        // Close modals when clicking outside
        document.getElementById('payoutRequestModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePayoutRequestModal();
            }
        });

        document.getElementById('collectPayoutModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCollectPayoutModal();
            }
        });
    </script>
</x-unified-layout>