<x-app-layout title="Qrciket - Profile">

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Profile</h2>
        </div>
    </x-slot>
    <div class="pt-0 lg:pt-12 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div id="profile-metadata" data-profile-update-action="{{ route('profile.update') }}" class="hidden"></div>


            <div class="mb-8 animate-fade-in">
                <p class="hidden lg:block text-gray-600 dark:text-gray-300 mt-2 transition-colors duration-300">Manage your account settings and preferences</p>
            </div>

            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Mobile Full-screen Menu -->
                <div id="profile-mobile-menu" class="lg:hidden">
                    <div>
                        <div class="px-4 py-3">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Settings</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Manage your account settings and preferences</p>
                        </div>
                        <nav class="divide-y divide-gray-200 dark:divide-gray-700">
                            <button type="button" data-section="account-settings" class="w-full text-left px-4 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700">
                                <span class="flex items-center text-gray-900 dark:text-gray-100">
                                    <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Account Settings
                                </span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                            <button type="button" data-section="activity-log" class="w-full text-left px-4 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700">
                                <span class="flex items-center text-gray-900 dark:text-gray-100">
                                    <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 002-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    Activity Log
                                </span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                            <a href="{{ route('support.my', ['from' => 'profile']) }}" class="block px-4 py-4 hover:bg-gray-50 dark:hover:bg-gray-700" onclick="// Let it navigate normally - this is an external route">
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center text-gray-900 dark:text-gray-100">
                                        <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h8m-8 4h6M5 6a2 2 0 00-2 2v8a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2H5z" />
                                        </svg>
                                        Support Tickets
                                    </span>
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </a>
                            <button type="button" data-section="subscription" class="w-full text-left px-4 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700">
                                <span class="flex items-center text-gray-900 dark:text-gray-100">
                                    <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                    Subscription
                                </span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                            <a href="#purchase-history" class="block px-4 py-4 hover:bg-gray-50 dark:hover:bg-gray-700" onclick="event.preventDefault(); const mobileMenu = document.getElementById('profile-mobile-menu'); const profileContent = document.getElementById('profile-content'); if (mobileMenu && profileContent) { mobileMenu.classList.add('hidden'); profileContent.classList.remove('hidden'); } showSection('purchase-history')">
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center text-gray-900 dark:text-gray-100">
                                        <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l2-2 4 4m0 0l4-4m-4 4V7" />
                                        </svg>
                                        Purchase History
                                    </span>
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </a>
                        </nav>
                    </div>
                </div>

                <!-- Sidebar (Desktop) -->
                <div class="lg:w-64 flex-shrink-0 animate-slide-in-left hidden lg:block">
                    <div class="profile-card bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4 border border-gray-200 dark:border-gray-700 transition-all duration-300">
                        <nav class="space-y-2">
                            <a href="#account-settings"
                                class="sidebar-link flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-qricket-500 dark:hover:text-qricket-400 transition-all duration-200"
                                onclick="showSection('account-settings')">
                                <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Account Settings
                            </a>

                            <a href="#activity-log"
                                class="sidebar-link flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-qricket-500 dark:hover:text-qricket-400 transition-all duration-200"
                                onclick="showSection('activity-log')">
                                <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                Activity Log
                            </a>

                            <a href="{{ route('support.my', ['from' => 'profile']) }}"
                                class="sidebar-link flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-qricket-500 dark:hover:text-qricket-400 transition-all duration-200">
                                <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h8m-8 4h6M5 6a2 2 0 00-2 2v8a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2H5z" />
                                </svg>
                                Support Tickets
                            </a>

                            <a href="#subscription"
                                class="sidebar-link flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-qricket-500 dark:hover:text-qricket-400 transition-all duration-200"
                                onclick="showSection('subscription')">
                                <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                Subscription
                            </a>
                            <a href="#purchase-history"
                                class="sidebar-link flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-qricket-500 dark:hover:text-qricket-400 transition-all duration-200"
                                onclick="showSection('purchase-history')">
                                <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l2-2 4 4m0 0l4-4m-4 4V7"></path>
                                </svg>
                                Purchase History
                            </a>
                        </nav>
                    </div>
                </div>

                <!-- Main Content -->
                <div id="profile-content" class="flex-1 animate-fade-in">
                    <!-- Mobile Back -->
                    <div class="lg:hidden mb-2">
                        <button id="profile-mobile-back" type="button" class="inline-flex items-center text-qricket-600 hover:text-qricket-800 dark:text-qricket-400 dark:hover:text-qricket-300">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back
                        </button>
                    </div>
                    <!-- Account Settings Section -->
                    <div id="account-settings" class="section-content">
                        <div class="space-y-6">
                            <div class="profile-card p-4 lg:p-8 lg:bg-white lg:dark:bg-gray-800 lg:shadow lg:rounded-lg lg:border lg:border-gray-200 lg:dark:border-gray-700 transition-all duration-300">
                                <div class="max-w-xl">
                                    @include('profile.partials.update-profile-information-form')
                                </div>
                            </div>

                            <!-- Organizer Verification Card -->
                            <div class="profile-card p-4 lg:p-8 lg:bg-white lg:dark:bg-gray-800 lg:shadow lg:rounded-lg lg:border lg:border-gray-200 lg:dark:border-gray-700 transition-all duration-300">
                                <div class="max-w-xl">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                                Organizer Verification
                                            </h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                @if(auth()->user()->isVerifiedOrganizer())
                                                You are a verified organizer with full access to organizer features.
                                                @else
                                                Complete your verification to access organizer features and tools.
                                                @endif
                                            </p>
                                        </div>

                                        <div class="flex items-center space-x-3">
                                            @if(auth()->user()->isVerifiedOrganizer())
                                            <div class="inline-flex items-center px-3 py-2 text-sm font-medium text-green-600 dark:text-green-400">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Verified
                                            </div>
                                            @else
                                            <a href="{{ route('organizer.verification') }}"
                                                class="inline-flex items-center px-4 py-2 bg-qricket-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-qricket-600 focus:bg-qricket-600 active:bg-qricket-700 focus:outline-none focus:ring-2 focus:ring-qricket-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Verify Now
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="profile-card p-4 lg:p-8 lg:bg-white lg:dark:bg-gray-800 lg:shadow lg:rounded-lg lg:border lg:border-gray-200 lg:dark:border-gray-700 transition-all duration-300">
                                <div class="max-w-xl">
                                    @include('profile.partials.social-media-links-form')
                                </div>
                            </div>

                            <div class="profile-card p-4 lg:p-8 lg:bg-white lg:dark:bg-gray-800 lg:shadow lg:rounded-lg lg:border lg:border-gray-200 lg:dark:border-gray-700 transition-all duration-300">
                                <div class="max-w-xl">
                                    @include('profile.partials.update-password-form')
                                </div>
                            </div>

                            <div class="profile-card p-4 lg:p-8 lg:bg-white lg:dark:bg-gray-800 lg:shadow lg:rounded-lg lg:border lg:border-gray-200 lg:dark:border-gray-700 transition-all duration-300">
                                <div class="max-w-xl">
                                    @include('profile.partials.delete-user-form')
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Log Section -->
                    <div id="activity-log" class="section-content hidden">
                        <div class="profile-card p-4 lg:p-8 lg:bg-white lg:dark:bg-gray-800 lg:shadow lg:rounded-lg lg:border lg:border-gray-200 lg:dark:border-gray-700 transition-all duration-300">
                            <div class="max-w-4xl">
                                @include('profile.partials.activity-log')
                            </div>
                        </div>
                    </div>

                    <!-- Subscription Section -->
                    <div id="subscription" class="section-content hidden">
                        <div class="profile-card p-4 lg:p-8 lg:bg-white lg:dark:bg-gray-800 lg:shadow lg:rounded-lg lg:border lg:border-gray-200 lg:dark:border-gray-700 transition-all duration-300">
                            <div class="max-w-4xl">
                                @include('profile.partials.subscription-info')
                            </div>
                        </div>
                    </div>

                    @include('profile.partials.purchase-history')
                </div>

            </div>
        </div>
    </div>
    </div>

    <!-- Refund Request Modal -->
    <div id="refundModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto max-w-md w-full">
            <div class="bg-white dark:bg-gray-800 shadow-xl rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-qricket-500 to-qricket-600 rounded-t-xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <h3 class="text-xl font-semibold text-white">Request Refund</h3>
                        </div>
                        <button onclick="closeRefundModal()" class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-1 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="px-6 py-5">

                    <form id="refundForm" method="POST" action="{{ route('refund.request') }}">
                        @csrf
                        <input type="hidden" id="refund_type" name="type">
                        <input type="hidden" id="refund_item_id" name="item_id">
                        <input type="hidden" id="refund_purchase_id" name="purchase_id">
                        <input type="hidden" id="refund_amount" name="amount">

                        <div class="mb-5">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Refund Amount</label>
                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                                <div class="text-2xl font-bold text-qricket-600 dark:text-qricket-400" id="refundAmountDisplay"></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="refund_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reason for Refund *</label>
                            <textarea id="refund_reason" name="reason" rows="3" class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-qricket-500" placeholder="Please explain why you need a refund..." required></textarea>
                        </div>

                        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-blue-900 dark:text-blue-100">Processing Time</p>
                                    <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">Please allow 3-5 business days for your refund to be processed. You will receive a notification once your refund has been processed.</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeRefundModal()" class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-qricket-600 hover:bg-qricket-700 rounded-lg shadow-sm hover:shadow-md transition-all">
                                Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openRefundModal(type, itemId, purchaseId, amount) {
            document.getElementById('refund_type').value = type;
            document.getElementById('refund_item_id').value = itemId;
            document.getElementById('refund_purchase_id').value = purchaseId;
            document.getElementById('refund_amount').value = amount;
            document.getElementById('refundAmountDisplay').textContent = '₱' + parseFloat(amount).toFixed(2);
            document.getElementById('refundModal').classList.remove('hidden');
        }

        function closeRefundModal() {
            document.getElementById('refundModal').classList.add('hidden');
            document.getElementById('refundForm').reset();
        }

        // Close modal when clicking outside
        document.getElementById('refundModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRefundModal();
            }
        });
    </script>

    <script>
        function showSection(sectionId, linkEl) {
            // Hide all sections
            document.querySelectorAll('.section-content').forEach(section => {
                section.classList.add('hidden');
            });
            // Show the selected section
            const section = document.getElementById(sectionId);
            if (section) section.classList.remove('hidden');

            // Mobile: switch from menu to content
            const mobileMenu = document.getElementById('profile-mobile-menu');
            const profileContent = document.getElementById('profile-content');
            if (mobileMenu && profileContent) {
                if (window.matchMedia('(max-width: 1023px)').matches) { // < lg
                    mobileMenu.classList.add('hidden');
                    profileContent.classList.remove('hidden');
                }
            }
            // Update active state in sidebar
            document.querySelectorAll('nav a').forEach(link => {
                link.classList.remove('bg-gray-100', 'text-gray-900');
                link.classList.add('text-gray-700');
            });
            if (linkEl) {
                linkEl.classList.remove('text-gray-700');
                linkEl.classList.add('bg-gray-100', 'text-gray-900');
            }
            // Persist to hash only (no localStorage)
            if (window.history && window.history.replaceState) {
                const hash = '#' + sectionId;
                window.history.replaceState(null, '', hash);
            }
        }

        let lastIsMobileLayout = null;

        function adjustLayout(force = false) {
            const mobileMenu = document.getElementById('profile-mobile-menu');
            const profileContent = document.getElementById('profile-content');

            if (!mobileMenu || !profileContent) return;

            const isMobile = window.innerWidth < 1024;
            if (!force && isMobile === lastIsMobileLayout) {
                return;
            }
            lastIsMobileLayout = isMobile;

            if (isMobile) {
                mobileMenu.classList.remove('hidden');
                profileContent.classList.add('hidden');
            } else {
                mobileMenu.classList.add('hidden');
                profileContent.classList.remove('hidden');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu item clicks
            document.querySelectorAll('#profile-mobile-menu [data-section]').forEach(btn => {
                btn.addEventListener('click', function() {
                    const target = this.getAttribute('data-section');
                    // Hide mobile menu and show content
                    const mobileMenu = document.getElementById('profile-mobile-menu');
                    const profileContent = document.getElementById('profile-content');
                    if (mobileMenu && profileContent) {
                        mobileMenu.classList.add('hidden');
                        profileContent.classList.remove('hidden');
                    }
                    showSection(target);
                });
            });

            // Mobile back button
            const backBtn = document.getElementById('profile-mobile-back');
            if (backBtn) {
                backBtn.addEventListener('click', function() {
                    const mobileMenu = document.getElementById('profile-mobile-menu');
                    const profileContent = document.getElementById('profile-content');
                    if (mobileMenu && profileContent) {
                        mobileMenu.classList.remove('hidden');
                        profileContent.classList.add('hidden');
                    }
                });
            }

            // Bind sidebar links to use showSection without full navigation
            document.querySelectorAll('nav a[href^="#"]').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = this.getAttribute('href').replace('#', '');
                    showSection(target, this);
                });
            });

            // Determine initial section from hash only (no localStorage)
            let initial = (location.hash || '').replace('#', '');
            if (!initial) {
                initial = 'account-settings';
            }

            // Find corresponding link
            const link = document.querySelector('nav a[href="#' + initial + '"]') || document.querySelector('a[href="#account-settings"]');

            // Initial layout adjustment
            adjustLayout(true);

            // Show initial section on desktop
            if (!window.matchMedia('(max-width: 1023px)').matches) {
                showSection(initial, link);
            }

            // Listen for screen size changes
            window.addEventListener('resize', () => adjustLayout());
        });
    </script>

    <x-slot name="scripts">
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const fileInput = document.getElementById('profile_picture');
                const previewContainer = document.getElementById('preview-container');
                const imagePreview = document.getElementById('image-preview');
                const currentPicture = document.getElementById('current-picture');

                if (fileInput) {
                    fileInput.addEventListener('change', function(e) {
                        const file = e.target.files[0];

                        if (file) {
                            console.log('File selected:', {
                                name: file.name,
                                size: file.size,
                                type: file.type
                            });

                            // Validate file type
                            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                            if (!allowedTypes.includes(file.type)) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Invalid File Type',
                                    text: 'Please select a valid image file (JPG or PNG).',
                                    confirmButtonColor: '#00B388'
                                });
                                fileInput.value = '';
                                return;
                            }

                            // Validate file size (5MB = 5 * 1024 * 1024 bytes)
                            const maxSize = 5 * 1024 * 1024;
                            if (file.size > maxSize) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'File Too Large',
                                    text: 'File size must be less than 5MB.',
                                    confirmButtonColor: '#00B388'
                                });
                                fileInput.value = '';
                                return;
                            }

                            // Show loading preview
                            imagePreview.innerHTML = '<div class="h-full w-full flex items-center justify-center bg-gray-100 dark:bg-gray-700"><svg class="animate-spin h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>';
                            previewContainer.classList.remove('hidden');
                            currentPicture.style.opacity = '0.5';

                            // Create FileReader to preview image
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                console.log('Image loaded for preview');

                                // Clear loading and create new image element
                                imagePreview.innerHTML = '';
                                const img = document.createElement('img');
                                img.src = e.target.result;
                                img.className = 'h-full w-full object-cover';
                                img.alt = 'Preview';

                                // Add image to preview container
                                imagePreview.appendChild(img);

                                // Show success message
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Image Ready',
                                    text: 'Click Save to upload your new profile picture.',
                                    timer: 2000,
                                    showConfirmButton: false,
                                    toast: true,
                                    position: 'top-end'
                                });
                            };

                            reader.onerror = function() {
                                console.error('Error reading file');
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Failed to load image preview.',
                                    confirmButtonColor: '#00B388'
                                });
                                previewContainer.classList.add('hidden');
                                currentPicture.style.opacity = '1';
                            };

                            reader.readAsDataURL(file);
                        } else {
                            // No file selected, hide preview
                            previewContainer.classList.add('hidden');
                            currentPicture.style.opacity = '1';
                        }
                    });
                }

                // Add form submission feedback
                const profileMeta = document.getElementById('profile-metadata');
                const profileUpdateAction = profileMeta ? profileMeta.getAttribute('data-profile-update-action') : null;
                const form = profileUpdateAction ? document.querySelector(`form[action="${profileUpdateAction}"]`) : null;
                if (form) {
                    form.addEventListener('submit', function(e) {
                        const fileInput = document.getElementById('profile_picture');
                        if (fileInput && fileInput.files.length > 0) {
                            // Show loading state
                            const submitBtn = form.querySelector('button[type="submit"]');
                            const originalText = submitBtn.textContent;
                            submitBtn.textContent = 'Uploading...';
                            submitBtn.disabled = true;

                            // Reset button after a delay (in case of errors)
                            setTimeout(() => {
                                submitBtn.textContent = originalText;
                                submitBtn.disabled = false;
                            }, 5000);
                        }
                    });
                }
            });

            // Password matching validation with SweetAlert
            const passwordField = document.getElementById('update_password_password');
            const confirmPasswordField = document.getElementById('update_password_password_confirmation');

            if (passwordField && confirmPasswordField) {
                // Check on form submission
                const passwordForm = document.querySelector('form[action*="password.update"]');

                if (passwordForm) {
                    passwordForm.addEventListener('submit', function(e) {
                        const password = passwordField.value;
                        const confirmPassword = confirmPasswordField.value;

                        if (password !== confirmPassword) {
                            e.preventDefault();
                            Swal.fire({
                                icon: 'error',
                                title: 'Password Mismatch',
                                text: 'The password confirmation does not match the password you entered. Please make sure both passwords are identical.',
                                confirmButtonColor: '#00B388',
                                confirmButtonText: 'OK'
                            });
                            return false;
                        }
                    });
                }

                // Check on blur (when user leaves the confirm password field)
                confirmPasswordField.addEventListener('blur', function() {
                    const password = passwordField.value;
                    const confirmPassword = confirmPasswordField.value;

                    if (confirmPassword && password !== confirmPassword) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Password Mismatch',
                            text: 'The password confirmation does not match the password you entered. Please make sure both passwords are identical.',
                            confirmButtonColor: '#00B388',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        </script>
    </x-slot>
</x-app-layout>
