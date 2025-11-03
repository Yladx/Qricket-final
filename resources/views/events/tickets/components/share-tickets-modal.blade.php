<!-- Share Tickets Modal -->
<div id="shareModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-6 border w-full max-w-2xl shadow-xl rounded-lg bg-white dark:bg-gray-800">
        <div class="mt-2">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-qricket-100 dark:bg-qricket-900 rounded-lg">
                        <svg class="w-6 h-6 text-qricket-600 dark:text-qricket-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Share Tickets</h3>
                </div>
                <button onclick="closeShareModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Ticket Information -->
            <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                <div class="flex items-center space-x-2 mb-2">
                    <svg class="w-5 h-5 text-qricket-600 dark:text-qricket-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Each selected user will receive 1 ticket from your available tickets.
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-qricket-100 text-qricket-800 dark:bg-qricket-900 dark:text-qricket-200">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Available: <span id="availableTicketCount" class="font-semibold">10</span> tickets
                    </span>
                </div>
            </div>
            
            <!-- User Search -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                    Search users to share with
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" id="userSearch" placeholder="Search by name or email..." 
                           class="w-full pl-10 pr-4 py-3 bg-white text-gray-900 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-qricket-500 focus:border-qricket-500 transition-colors duration-200">
                    <div id="searchResults" class="absolute z-10 w-full mt-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-48 overflow-y-auto hidden">
                        <!-- Search results will be populated here -->
                    </div>
                </div>
            </div>
            
            <!-- Selected Users List (Hidden when no users selected) -->
            <div id="selectedUsersSection" class="mb-6 hidden">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                    Users to share with
                </label>
                <div id="selectedUsers" class="min-h-[120px] max-h-48 overflow-y-auto p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                    <!-- Selected users will be displayed here -->
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                <button onclick="closeShareModal()" 
                        class="px-6 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-all duration-200 border border-gray-300 dark:border-gray-600">
                    Cancel
                </button>
                <button onclick="shareTickets()" 
                        class="px-6 py-3 text-sm font-semibold text-white bg-qricket-600 hover:bg-qricket-700 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                    Share Tickets
                </button>
            </div>
        </div>
    </div>
</div>
