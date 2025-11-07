<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Xendit Disbursement Tester</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Xendit Disbursement Tester</h1>
                <p class="text-gray-600">Test and debug Xendit disbursement functionality</p>
            </div>

            <!-- Test Form -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Test Disbursement</h2>
                
                <form id="disbursementTestForm">
                    <div class="space-y-4">
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount (PHP)</label>
                            <input type="number" name="amount" id="amount" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   placeholder="100" min="1" step="0.01" value="100">
                        </div>

                        <div>
                            <label for="bank_code" class="block text-sm font-medium text-gray-700 mb-2">Bank Code</label>
                            <select name="bank_code" id="bank_code" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Bank</option>
                                <option value="BANK_TRANSFER">Bank Transfer (Generic)</option>
                                <option value="BDO_ONLINE_BANKING" selected>BDO Bank Transfer</option>
                                <option value="BPI_ONLINE_BANKING">BPI Online Banking</option>
                                <option value="METROBANK_ONLINE_BANKING">Metrobank Bank Transfer</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Account Number</label>
                            <input type="text" name="account_number" id="account_number" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   placeholder="1234567890" value="1234567890">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Account Name</label>
                            <input type="text" name="account_name" id="account_name" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   placeholder="John Doe" value="John Doe">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <input type="text" name="description" id="description" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   placeholder="Test disbursement" value="Test disbursement">
                        </div>

                        <button type="submit" 
                                class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Test Disbursement
                        </button>
                    </div>
                </form>
            </div>

            <!-- Results -->
            <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Test Results</h2>
                <div id="results" class="bg-gray-50 rounded-md p-4 min-h-32">
                    <p class="text-gray-500">Test results will appear here...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Disbursement Test Form
        document.getElementById('disbursementTestForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            showLoading('Testing disbursement...');
            
            fetch('/test/disbursement', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess('Disbursement test completed successfully!', data.data);
                } else {
                    showError('Disbursement test failed', data.message);
                }
            })
            .catch(error => {
                showError('Network error', error.message);
            });
        });

        // Helper Functions
        function showLoading(message) {
            document.getElementById('results').innerHTML = `
                <div class="flex items-center">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mr-3"></div>
                    <span class="text-blue-600">${message}</span>
                </div>
            `;
        }

        function showSuccess(message, data) {
            document.getElementById('results').innerHTML = `
                <div class="text-green-600 mb-4">
                    <div class="flex items-center mb-2">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <strong>${message}</strong>
                    </div>
                </div>
                <pre class="bg-gray-100 p-4 rounded-md text-sm overflow-auto">${JSON.stringify(data, null, 2)}</pre>
            `;
        }

        function showError(message, error) {
            document.getElementById('results').innerHTML = `
                <div class="text-red-600 mb-4">
                    <div class="flex items-center mb-2">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <strong>${message}</strong>
                    </div>
                    <p class="text-sm">${error}</p>
                </div>
            `;
        }
    </script>
</body>
</html>