<x-app-layout title="Tutorials - Qricket" page-title="Tutorials">
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-8 animate-fade-in">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Qricket Tutorials</h1>
                <p class="text-gray-600 dark:text-gray-300 mt-2">Step-by-step guides to help you master Qricket's features</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Tutorial Content -->
                <div class="lg:col-span-2">
                    <!-- Tabs Navigation -->
                    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg border border-gray-200 dark:border-gray-700 mb-6">
                        <div class="border-b border-gray-200 dark:border-gray-700">
                            <nav class="flex overflow-x-auto -mb-px" aria-label="Tabs">
                                <button type="button" 
                                    class="tutorial-tab flex-shrink-0 px-4 py-3 text-sm font-medium border-b-2 transition-colors duration-200 tutorial-tab-active"
                                    data-tab="event-creation"
                                    aria-selected="true">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-qricket-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span>Event Creation</span>
                                    </div>
                                </button>
                                
                                <button type="button" 
                                    class="tutorial-tab flex-shrink-0 px-4 py-3 text-sm font-medium border-b-2 transition-colors duration-200"
                                    data-tab="ticket-management"
                                    aria-selected="false">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4v-3a2 2 0 00-2-2H5z"></path>
                                        </svg>
                                        <span>Tickets</span>
                                    </div>
                                </button>
                                
                                <button type="button" 
                                    class="tutorial-tab flex-shrink-0 px-4 py-3 text-sm font-medium border-b-2 transition-colors duration-200"
                                    data-tab="event-layout"
                                    aria-selected="false">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                                        </svg>
                                        <span>Layouts</span>
                                    </div>
                                </button>
                                
                                <button type="button" 
                                    class="tutorial-tab flex-shrink-0 px-4 py-3 text-sm font-medium border-b-2 transition-colors duration-200"
                                    data-tab="analytics"
                                    aria-selected="false">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        <span>Analytics</span>
                                    </div>
                                </button>
                                
                                <button type="button" 
                                    class="tutorial-tab flex-shrink-0 px-4 py-3 text-sm font-medium border-b-2 transition-colors duration-200"
                                    data-tab="advanced-features"
                                    aria-selected="false">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                        </svg>
                                        <span>Advanced</span>
                                    </div>
                                </button>
                                
                                <button type="button" 
                                    class="tutorial-tab flex-shrink-0 px-4 py-3 text-sm font-medium border-b-2 transition-colors duration-200"
                                    data-tab="refunds-payouts"
                                    aria-selected="false">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <span>Refunds & Payouts</span>
                                    </div>
                                </button>
                            </nav>
                        </div>
                    </div>

                    <!-- Tutorial Content Area -->
                    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            @include('tutorials.partials.event-creation')
                            @include('tutorials.partials.ticket-management')
                            @include('tutorials.partials.event-layout')
                            @include('tutorials.partials.analytics')
                            @include('tutorials.partials.advanced-features')
                            @include('tutorials.partials.refunds-payouts')
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Links -->
                    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Quick Links</h3>
                            
                            <div class="space-y-3">
                                <a href="{{ route('help.index') }}" class="block p-3 rounded-lg bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-qricket-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-gray-700 dark:text-gray-300 font-medium">Help & Support</span>
                                    </div>
                                </a>
                                
                                @auth
                                @if(auth()->user()->is_organizer ?? false)
                                <a href="{{ route('organizer.events.index') }}" class="block p-3 rounded-lg bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-qricket-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-gray-700 dark:text-gray-300 font-medium">Create Event</span>
                                    </div>
                                </a>
                                @endif
                                @endauth
                                
                                <a href="{{ route('subscription.index') }}" class="block p-3 rounded-lg bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-qricket-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                        <span class="text-gray-700 dark:text-gray-300 font-medium">Subscription Plans</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Tips Section -->
                    <div class="bg-qricket-50 dark:bg-qricket-900/20 border border-qricket-200 dark:border-qricket-800 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-qricket-900 dark:text-qricket-100 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Pro Tips
                        </h3>
                        
                        <div class="space-y-3 text-sm">
                            <div class="text-qricket-800 dark:text-qricket-200">
                                <strong>💡 Auto-save:</strong> All forms automatically save your progress as you type, so you won't lose your work.
                            </div>
                            <div class="text-qricket-800 dark:text-qricket-200">
                                <strong>📸 Images:</strong> Use high-quality images for better event presentation. Optimize file sizes for faster loading.
                            </div>
                            <div class="text-qricket-800 dark:text-qricket-200">
                                <strong>🎫 Tickets:</strong> Create multiple ticket types to offer different pricing tiers and increase sales.
                            </div>
                            <div class="text-qricket-800 dark:text-qricket-200">
                                <strong>👥 Staff:</strong> Add trusted team members as staff to help manage your event without giving full access.
                            </div>
                            <div class="text-qricket-800 dark:text-qricket-200">
                                <strong>📊 Analytics:</strong> Regularly check your event dashboard to track sales and make data-driven decisions.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.tutorial-tab');
            const contents = document.querySelectorAll('.tutorial-content');
            const storageKey = 'qricket_tutorials_active_tab';

            // Load saved tab from localStorage
            function loadSavedTab() {
                try {
                    const saved = localStorage.getItem(storageKey);
                    if (saved) {
                        switchTab(saved);
                    }
                } catch (e) {
                    console.warn('Failed to load saved tab:', e);
                }
            }

            // Save active tab to localStorage
            function saveTab(tabId) {
                try {
                    localStorage.setItem(storageKey, tabId);
                } catch (e) {
                    console.warn('Failed to save tab state:', e);
                }
            }

            // Switch to a specific tab
            function switchTab(tabId) {
                // Hide all contents
                contents.forEach(content => {
                    content.style.display = 'none';
                });

                // Remove active state from all tabs
                tabs.forEach(tab => {
                    tab.classList.remove('tutorial-tab-active', 'border-qricket-500', 'text-qricket-600', 'dark:text-qricket-400');
                    tab.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'dark:text-gray-400', 'dark:hover:text-gray-300');
                    tab.setAttribute('aria-selected', 'false');
                    
                    // Reset icon colors
                    const icon = tab.querySelector('svg');
                    if (icon && !tab.classList.contains('tutorial-tab-active')) {
                        icon.classList.remove('text-qricket-500');
                    }
                });

                // Show selected content
                const selectedContent = document.getElementById(`${tabId}-content`);
                if (selectedContent) {
                    selectedContent.style.display = 'block';
                }

                // Add active state to selected tab
                const selectedTab = document.querySelector(`[data-tab="${tabId}"]`);
                if (selectedTab) {
                    selectedTab.classList.add('tutorial-tab-active', 'border-qricket-500', 'text-qricket-600', 'dark:text-qricket-400');
                    selectedTab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'dark:text-gray-400', 'dark:hover:text-gray-300');
                    selectedTab.setAttribute('aria-selected', 'true');
                    
                    // Set icon color
                    const icon = selectedTab.querySelector('svg');
                    if (icon) {
                        icon.classList.add('text-qricket-500');
                    }
                }

                saveTab(tabId);
            }

            // Initialize event listeners
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    switchTab(tabId);
                });
            });

            // Load saved tab or default to first
            loadSavedTab();
            
            // If no saved tab, show first one
            if (!localStorage.getItem(storageKey)) {
                switchTab('event-creation');
            }
        });
    </script>

    <style>
        .tutorial-tab {
            cursor: pointer;
            white-space: nowrap;
        }

        .tutorial-tab:hover {
            color: #10b981;
        }

        .tutorial-tab-active {
            border-color: #10b981;
            color: #059669;
        }

        .tutorial-tab-active svg {
            color: #10b981;
        }

        .tutorial-content {
            display: none;
            animation: fadeIn 0.3s ease-in;
        }

        .tutorial-content[style*="display: block"] {
            display: block !important;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Scrollbar styling for tabs */
        nav::-webkit-scrollbar {
            height: 4px;
        }

        nav::-webkit-scrollbar-track {
            background: transparent;
        }

        nav::-webkit-scrollbar-thumb {
            background: #10b981;
            border-radius: 2px;
        }
    </style>
</x-app-layout>
