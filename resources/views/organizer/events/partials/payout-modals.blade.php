<!-- Payout Request Modal -->
<div id="payoutRequestModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 dark:bg-gray-900 dark:bg-opacity-70 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border border-gray-200 dark:border-gray-700 w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Request Payout</h3>
                <button onclick="closePayoutRequestModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Loading State -->
            <div id="payoutModalLoading" class="text-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-qricket-600 mx-auto"></div>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Calculating payout...</p>
            </div>

            <!-- Payout Calculation -->
            <div id="payoutModalContent" class="hidden">
                <div class="mb-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payout Calculation</h4>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Total Earnings:</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100" id="totalEarnings">₱0.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Qricket Fee (<span id="feePercentage">5</span>%):</span>
                            <span class="text-sm font-semibold text-red-600 dark:text-red-400" id="qricketFee">₱0.00</span>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-600 pt-3">
                            <div class="flex justify-between">
                                <span class="text-base font-medium text-gray-700 dark:text-gray-300">Net Amount:</span>
                                <span class="text-lg font-bold text-green-600 dark:text-green-400" id="netAmount">₱0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-4">
                    <label for="payoutNotes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes (Optional)</label>
                    <textarea id="payoutNotes" rows="3" class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 focus:ring-qricket-500 focus:border-qricket-500" placeholder="Add any notes for this payout request..."></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-3">
                    <button onclick="closePayoutRequestModal()" class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Cancel</button>
                    <button onclick="submitPayoutRequest()" class="flex-1 px-4 py-2 bg-qricket-600 hover:bg-qricket-700 text-white rounded-md text-sm font-medium">Request Payout</button>
                </div>
            </div>

            <!-- Error State -->
            <div id="payoutModalError" class="hidden text-center py-8">
                <div class="text-red-600 dark:text-red-400 mb-2">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">Unable to calculate payout</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4" id="payoutErrorText">Please try again later.</p>
                <button onclick="closePayoutRequestModal()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md text-sm font-medium">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Collect Payout Modal -->
<div id="collectPayoutModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 dark:bg-gray-900 dark:bg-opacity-70 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border border-gray-200 dark:border-gray-700 w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <!-- Modal Header -->
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
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payout Information</h4>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Amount to Collect:</span>
                            <span class="text-lg font-bold text-green-600 dark:text-green-400" id="collectAmount">₱0.00</span>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Transfer will be processed within 1-2 business days via Xendit</div>
                    </div>
                </div>

                <!-- Bank Details Form -->
                <form id="collectPayoutForm" data-cache-bust="{{ time() }}">
                    <div class="mb-4">
                        <label for="bankCode" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Bank</label>
                        <select id="bankCode" name="bank_code" required class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 focus:ring-qricket-500 focus:border-qricket-500">
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
                        <input type="text" id="accountNumber" name="account_number" required class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 focus:ring-qricket-500 focus:border-qricket-500" placeholder="Enter your account number">
                    </div>

                    <div class="mb-6">
                        <label for="accountName" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Account Name</label>
                        <input type="text" id="accountName" name="account_name" required class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 focus:ring-qricket-500 focus:border-qricket-500" placeholder="Enter account holder name">
                    </div>

                    <!-- Processing Notice -->
                    <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="text-sm text-blue-800 dark:text-blue-200">
                                <strong>Direct Bank Transfer:</strong> The payout will be sent directly to your bank account via Xendit disbursement within 1-2 business days.
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex space-x-3">
                        <button type="button" onclick="closeCollectPayoutModal()" class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Cancel</button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md text-sm font-medium">Process Payout</button>
                    </div>
                </form>
        </div>
    </div>
</div>

<!-- JavaScript for Payout Modals -->
<script>
    let currentEventId = null;
    let currentCollectEventId = null;

    // Make functions globally accessible
    window.openPayoutRequestModal = function(eventId) {
        console.log('openPayoutRequestModal called with eventId:', eventId);
        currentEventId = eventId;
        const modal = document.getElementById('payoutRequestModal');
        const loading = document.getElementById('payoutModalLoading');
        const content = document.getElementById('payoutModalContent');
        const error = document.getElementById('payoutModalError');
        
        if (!modal) {
            console.error('Modal not found!');
            return;
        }
        
        // Show modal and loading state
        modal.classList.remove('hidden');
        loading.classList.remove('hidden');
        content.classList.add('hidden');
        error.classList.add('hidden');
        
        // Calculate payout
        calculatePayout(eventId);
    }

    window.closePayoutRequestModal = function() {
        document.getElementById('payoutRequestModal').classList.add('hidden');
        currentEventId = null;
    }

    function calculatePayout(eventId) {
        console.log('calculatePayout called with eventId:', eventId);
        const url = `/organizer/events/${eventId}/payout/calculate`;
        console.log('Fetching URL:', url);
        
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            const loading = document.getElementById('payoutModalLoading');
            const content = document.getElementById('payoutModalContent');
            const error = document.getElementById('payoutModalError');
            
            if (data.total_earnings !== undefined) {
                // Show calculation
                document.getElementById('totalEarnings').textContent = `₱${parseFloat(data.total_earnings).toLocaleString('en-US', {minimumFractionDigits: 2})}`;
                document.getElementById('feePercentage').textContent = data.qricket_fee_percentage;
                document.getElementById('qricketFee').textContent = `₱${parseFloat(data.qricket_fee).toLocaleString('en-US', {minimumFractionDigits: 2})}`;
                document.getElementById('netAmount').textContent = `₱${parseFloat(data.net_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}`;
                
                loading.classList.add('hidden');
                content.classList.remove('hidden');
            } else {
                // Show error
                loading.classList.add('hidden');
                error.classList.remove('hidden');
                document.getElementById('payoutErrorText').textContent = data.message || 'Unable to calculate payout.';
            }
        })
        .catch(err => {
            console.error('Error calculating payout:', err);
            const loading = document.getElementById('payoutModalLoading');
            const errorDiv = document.getElementById('payoutModalError');
            
            loading.classList.add('hidden');
            errorDiv.classList.remove('hidden');
            document.getElementById('payoutErrorText').textContent = 'An error occurred while calculating payout.';
        });
    }

    window.submitPayoutRequest = function() {
        if (!currentEventId) return;
        
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
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Payout Request Submitted',
                    text: data.message,
                    confirmButtonColor: '#10b981'
                }).then(() => {
                    closePayoutRequestModal();
                    // Reload the page to show updated payout status
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
                text: 'An error occurred while submitting the payout request.',
                confirmButtonColor: '#ef4444'
            });
        });
    }

    window.openCollectPayoutModal = function(eventId) {
        currentCollectEventId = eventId;
        const modal = document.getElementById('collectPayoutModal');
        modal.classList.remove('hidden');
        
        // Load payout amount
        loadPayoutAmount(eventId);
    }

    window.closeCollectPayoutModal = function() {
        document.getElementById('collectPayoutModal').classList.add('hidden');
        document.getElementById('collectPayoutForm').reset();
        currentCollectEventId = null;
    }

    function loadPayoutAmount(eventId) {
        fetch(`/organizer/events/${eventId}/payout/status`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.payout_request && data.payout_request.status === 'released') {
                document.getElementById('collectAmount').textContent = `₱${parseFloat(data.payout_request.net_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}`;
            }
        })
        .catch(err => {
            console.error('Error loading payout amount:', err);
        });
    }

    // Handle form submission for disbursement
    document.getElementById('collectPayoutForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!currentCollectEventId) return;
        
        const formData = new FormData(this);
        const bankCode = formData.get('bank_code');
        const accountNumber = formData.get('account_number');
        const accountName = formData.get('account_name');
        
        // Debug logging
        console.log('Bank Code Selected:', bankCode);
        console.log('Account Number:', accountNumber);
        console.log('Account Name:', accountName);
        
        // Show loading state
        const submitButton = this.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        submitButton.textContent = 'Processing...';
        submitButton.disabled = true;
        
        fetch(`/organizer/events/${currentCollectEventId}/payout/collect-new`, {
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
