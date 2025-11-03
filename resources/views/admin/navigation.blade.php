<nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-40 shadow-sm">
    <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-10">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center space-x-4 lg:space-x-8">
                <a href="{{ route('admin.dashboard') }}" class="text-qricket-600 dark:text-qricket-400 font-bold text-xl lg:text-2xl leading-none tracking-tight hover:text-qricket-700 dark:hover:text-qricket-300 transition-colors duration-200">Qricket Admin</a>
                <div class="hidden md:flex items-center space-x-2 lg:space-x-4 xl:space-x-6">
                    <a href="{{ route('admin.dashboard') }}" class="@if(request()->routeIs('admin.dashboard')) text-qricket-600 dark:text-qricket-400 bg-qricket-50 dark:bg-qricket-900/20 @else text-gray-700 dark:text-gray-300 hover:text-qricket-600 dark:hover:text-qricket-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 @endif px-2 lg:px-4 py-2 rounded-lg text-xs lg:text-sm font-semibold transition-all duration-200">Dashboard</a>
                    
                    <!-- Users Dropdown -->
                    <div class="relative group">
                        <a href="{{ route('admin.users') }}" class="@if(request()->routeIs('admin.users')||request()->routeIs('admin.users.*')) text-qricket-600 dark:text-qricket-400 bg-qricket-50 dark:bg-qricket-900/20 @else text-gray-700 dark:text-gray-300 hover:text-qricket-600 dark:hover:text-qricket-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 @endif px-2 lg:px-4 py-2 rounded-lg text-xs lg:text-sm font-semibold flex items-center transition-all duration-200">
                            Users
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </a>
                        <div class="absolute left-0 mt-3 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                            <div class="py-2">
                                <a href="{{ route('admin.users') }}" class="block px-5 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-qricket-600 dark:hover:text-qricket-400 transition-colors duration-200">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                        </svg>
                                        All Users
                                    </div>
                                </a>
                                <a href="{{ route('admin.verifications') }}" class="block px-5 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-qricket-600 dark:hover:text-qricket-400 transition-colors duration-200">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Verify Organizer
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Events Dropdown -->
                    <div class="relative group">
                        <a href="{{ route('admin.events.approval.index') }}" class="@if(request()->routeIs('admin.events.*') || request()->routeIs('admin.payouts.*')) text-qricket-600 dark:text-qricket-400 bg-qricket-50 dark:bg-qricket-900/20 @else text-gray-700 dark:text-gray-300 hover:text-qricket-600 dark:hover:text-qricket-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 @endif px-2 lg:px-4 py-2 rounded-lg text-xs lg:text-sm font-semibold flex items-center transition-all duration-200">
                            Events
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </a>
                        <div class="absolute left-0 mt-3 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                            <div class="py-2">
                                <a href="{{ route('admin.events.approval.index') }}" class="block px-5 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-qricket-600 dark:hover:text-qricket-400 transition-colors duration-200">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        All Events
                                    </div>
                                </a>
                                <a href="{{ route('admin.events.pending') }}" class="block px-5 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-qricket-600 dark:hover:text-qricket-400 transition-colors duration-200">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Pending Approval
                                    </div>
                                </a>
                                <a href="{{ route('admin.payouts.index') }}" class="block px-5 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-qricket-600 dark:hover:text-qricket-400 transition-colors duration-200">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                        Payouts
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sales Dropdown (with Refunds inside) -->
                    <div class="relative group">
                        <a href="{{ route('admin.reports.sales') }}" class="@if(request()->routeIs('admin.reports.sales') || request()->routeIs('admin.refunds.*')) text-qricket-600 dark:text-qricket-400 bg-qricket-50 dark:bg-qricket-900/20 @else text-gray-700 dark:text-gray-300 hover:text-qricket-600 dark:hover:text-qricket-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 @endif px-2 lg:px-4 py-2 rounded-lg text-xs lg:text-sm font-semibold flex items-center transition-all duration-200">
                            Sales
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </a>
                        <div class="absolute left-0 mt-3 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                            <div class="py-2">
                                <a href="{{ route('admin.reports.sales') }}" class="block px-5 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-qricket-600 dark:hover:text-qricket-400 transition-colors duration-200">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Sales Reports
                                    </div>
                                </a>
                                <div class="px-5 py-2 text-[11px] uppercase tracking-wide text-gray-400 dark:text-gray-500">Refunds</div>
                                <a href="{{ route('admin.refunds.index') }}" class="block px-5 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-qricket-600 dark:hover:text-qricket-400 transition-colors duration-200">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        All Refunds
                                    </div>
                                </a>
                                <a href="{{ route('admin.refunds.index', ['status' => 'pending']) }}" class="block px-5 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-qricket-600 dark:hover:text-qricket-400 transition-colors duration-200">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Pending Approval
                                    </div>
                                </a>
                                <a href="{{ route('admin.refunds.index', ['status' => 'approved']) }}" class="block px-5 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-qricket-600 dark:hover:text-qricket-400 transition-colors duration-200">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Approved
                                    </div>
                                </a>
                                <a href="{{ route('admin.refunds.index', ['status' => 'rejected']) }}" class="block px-5 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-qricket-600 dark:hover:text-qricket-400 transition-colors duration-200">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Rejected
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- Service Plans Dropdown -->
                    <div class="relative group">
                        <a href="{{ route('admin.subscriptions.index') }}" class="@if(request()->routeIs('admin.subscriptions.*')) text-qricket-600 dark:text-qricket-400 bg-qricket-50 dark:bg-qricket-900/20 @else text-gray-700 dark:text-gray-300 hover:text-qricket-600 dark:hover:text-qricket-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 @endif px-2 lg:px-4 py-2 rounded-lg text-xs lg:text-sm font-semibold flex items-center transition-all duration-200">
                            Service Plans
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </a>
                        <div class="absolute left-0 mt-3 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                            <div class="py-2">
                                <a href="{{ route('admin.subscriptions.index', ['section' => 'pricing']) }}" class="block px-5 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-qricket-600 dark:hover:text-qricket-400 transition-colors duration-200">Ticket Pricing</a>
                                <a href="{{ route('admin.subscriptions.index', ['section' => 'discounts']) }}" class="block px-5 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-qricket-600 dark:hover:text-qricket-400 transition-colors duration-200">Discounts</a>
                                <a href="{{ route('admin.subscriptions.index', ['section' => 'subscriptions']) }}" class="block px-5 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-qricket-600 dark:hover:text-qricket-400 transition-colors duration-200">Subscriptions</a>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('admin.support') }}" class="@if(request()->routeIs('admin.support')||request()->routeIs('admin.support.*')) text-qricket-600 dark:text-qricket-400 bg-qricket-50 dark:bg-qricket-900/20 @else text-gray-700 dark:text-gray-300 hover:text-qricket-600 dark:hover:text-qricket-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 @endif px-2 lg:px-4 py-2 rounded-lg text-xs lg:text-sm font-semibold transition-all duration-200">Support</a>

                    <!-- Feedback -->
                    <a href="{{ route('admin.feedback.index') }}" class="@if(request()->routeIs('admin.feedback.*')) text-qricket-600 dark:text-qricket-400 bg-qricket-50 dark:bg-qricket-900/20 @else text-gray-700 dark:text-gray-300 hover:text-qricket-600 dark:hover:text-qricket-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 @endif px-2 lg:px-4 py-2 rounded-lg text-xs lg:text-sm font-semibold transition-all duration-200">Feedback</a>
                </div>
            </div>

        <!-- Right controls (desktop) -->
        @php($adminUser = Auth::guard('admin')->user())
        <div class="hidden md:flex items-center space-x-3">
            <!-- Dark Mode Toggle -->
            <button id="desktop-darkModeToggle" class="p-2 text-gray-500 dark:text-gray-300 hover:text-qricket-600 dark:hover:text-qricket-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-200 focus:outline-none" aria-label="Toggle dark mode">
                <svg id="desktop-sunIcon" class="h-5 w-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <svg id="desktop-moonIcon" class="h-5 w-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </button>
            
            <a href="{{ route('admin.notifications') }}" id="admin-nav-bell-desktop" class="relative p-2 text-gray-500 dark:text-gray-300 hover:text-qricket-600 dark:hover:text-qricket-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-200 focus:outline-none" aria-label="Notifications">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                <span class="notification-badge absolute top-0 right-0 hidden min-w-[1.25rem] h-5 inline-flex items-center justify-center px-1.5 text-xs font-bold leading-none text-white bg-red-500 rounded-full">0</span>
            </a>
            @if($adminUser)
                <div class="flex items-center space-x-3">
                @if($adminUser->profile_picture ?? false)
                        <img class="h-10 w-10 rounded-full object-cover border-2 border-gray-200 dark:border-gray-600 hover:border-qricket-300 dark:hover:border-qricket-500 transition-colors duration-200" src="{{ $adminUser->profile_picture_url }}" alt="{{ $adminUser->username }}">
                @else
                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-qricket-500 to-qricket-600 text-white flex items-center justify-center text-sm font-bold shadow-lg">{{ strtoupper(substr($adminUser->username,0,1)) }}</div>
                @endif
                    <div class="hidden lg:block">
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $adminUser->username }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Administrator</div>
                    </div>
                    
                    <!-- Profile Dropdown -->
                    <div class="relative group" x-data="{ open: false }">
                        <button @click="open = !open" class="p-2 text-gray-500 dark:text-gray-300 hover:text-qricket-600 dark:hover:text-qricket-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-200" aria-label="Profile Menu">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 z-50">
                            <div class="py-2">
                                <!-- My Activity -->
                                <a href="{{ route('admin.activity-log.index') }}" class="block px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-qricket-600 dark:hover:text-qricket-400 transition-colors duration-200">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                        </svg>
                                        My Activity Log
                                    </div>
                                </a>
                                
                                <div class="border-t border-gray-200 dark:border-gray-700 my-2"></div>
                                
                                <!-- Logout -->
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                                    <button type="submit" class="w-full text-left block px-4 py-3 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-200">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                            </svg>
                                            Logout
                                        </div>
                    </button>
                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="flex items-center space-x-2 md:hidden">
                <a href="{{ route('admin.notifications') }}" id="admin-nav-bell" class="relative p-2 text-gray-500 dark:text-gray-300 hover:text-qricket-600 dark:hover:text-qricket-400 focus:outline-none" aria-label="Notifications">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <span class="notification-badge absolute top-0 right-0 hidden min-w-[1.25rem] h-5 inline-flex items-center justify-center px-1.5 text-xs font-bold leading-none text-white bg-red-500 rounded-full">0</span>
                </a>
            @if($adminUser)
            <div class="flex items-center space-x-2">
                @if($adminUser->profile_picture ?? false)
                    <img class="h-8 w-8 rounded-full object-cover border border-gray-300 dark:border-gray-600" src="{{ $adminUser->profile_picture_url }}" alt="{{ $adminUser->username }}">
                @else
                    <div class="h-8 w-8 rounded-full bg-qricket-600 text-white flex items-center justify-center text-[11px] font-semibold">{{ strtoupper(substr($adminUser->username,0,1)) }}</div>
                @endif
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="p-2 text-gray-500 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400" aria-label="Logout">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
            @endif
                <button id="admin-nav-menu" class="p-2 text-gray-500 dark:text-gray-300 hover:text-qricket-600 dark:hover:text-qricket-400 focus:outline-none" aria-label="Menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Mobile Menu Overlay -->
    <div id="admin-mobile-menu-overlay" class="hidden fixed inset-0 z-50 md:hidden">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
        
        <!-- Menu Panel -->
        <div class="fixed top-0 left-0 h-full w-64 bg-white dark:bg-gray-800 shadow-xl transform transition-transform duration-300 ease-in-out">
            <!-- Menu Header -->
            <div class="flex items-center justify-between px-4 py-4 border-b border-gray-200 dark:border-gray-700">
                <span class="text-lg font-bold text-qricket-600 dark:text-qricket-400">Menu</span>
                <div class="flex items-center space-x-2">
                    <!-- Dark Mode Toggle -->
                    <button id="mobile-darkModeToggle" class="p-2 text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-100 transition-colors" aria-label="Toggle dark mode">
                        <svg id="mobile-sunIcon" class="h-5 w-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <svg id="mobile-moonIcon" class="h-5 w-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </button>
                    <button id="admin-mobile-menu-close" class="p-2 text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-100 transition-colors">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Menu Items -->
            <div class="overflow-y-auto h-[calc(100vh-4rem)] px-2 py-2 space-y-1" x-data="{
                usersOpen: false,
                eventsOpen: false,
                salesOpen: false,
                refundsOpen: false
            }">
            
            <!-- Dashboard -->
            <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-qricket-600 dark:hover:text-qricket-400 rounded-md transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            
            <!-- Users Section -->
            <div>
                <button @click="usersOpen = !usersOpen" class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                        Users
                    </div>
                    <svg class="w-4 h-4 transition-transform" :class="{'rotate-180': usersOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="usersOpen" class="pl-6 space-y-1 mt-1">
                    <a href="{{ route('admin.users') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-qricket-600 dark:hover:text-qricket-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">All Users</a>
                    <a href="{{ route('admin.verifications') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-qricket-600 dark:hover:text-qricket-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">Verify Organizer</a>
                </div>
            </div>
            
            <!-- Events Section -->
            <div>
                <button @click="eventsOpen = !eventsOpen" class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Events
                    </div>
                    <svg class="w-4 h-4 transition-transform" :class="{'rotate-180': eventsOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="eventsOpen" class="pl-6 space-y-1 mt-1">
                    <a href="{{ route('admin.events.approval.index') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-qricket-600 dark:hover:text-qricket-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">All Events</a>
                    <a href="{{ route('admin.events.pending') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-qricket-600 dark:hover:text-qricket-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">Pending Approval</a>
                    <a href="{{ route('admin.payouts.index') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-qricket-600 dark:hover:text-qricket-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">Payouts</a>
                </div>
            </div>
            
            <!-- Sales Section -->
            <div>
                <button @click="salesOpen = !salesOpen" class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Sales
                    </div>
                    <svg class="w-4 h-4 transition-transform" :class="{'rotate-180': salesOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="salesOpen" class="pl-6 space-y-1 mt-1">
                    <a href="{{ route('admin.reports.sales') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-qricket-600 dark:hover:text-qricket-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">Sales Reports</a>
                    
                    <!-- Refunds Sub-menu -->
                    <div>
                        <button @click="refundsOpen = !refundsOpen" class="flex items-center justify-between w-full px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-qricket-600 dark:hover:text-qricket-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                            <span>Refunds</span>
                            <svg class="w-3 h-3 transition-transform" :class="{'rotate-180': refundsOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="refundsOpen" class="pl-4 space-y-1 mt-1">
                            <a href="{{ route('admin.refunds.index') }}" class="flex items-center px-3 py-1.5 text-xs text-gray-500 dark:text-gray-500 hover:text-qricket-600 dark:hover:text-qricket-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">All Refunds</a>
                            <a href="{{ route('admin.refunds.index', ['status' => 'pending']) }}" class="flex items-center px-3 py-1.5 text-xs text-gray-500 dark:text-gray-500 hover:text-qricket-600 dark:hover:text-qricket-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">Pending</a>
                            <a href="{{ route('admin.refunds.index', ['status' => 'approved']) }}" class="flex items-center px-3 py-1.5 text-xs text-gray-500 dark:text-gray-500 hover:text-qricket-600 dark:hover:text-qricket-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">Approved</a>
                            <a href="{{ route('admin.refunds.index', ['status' => 'rejected']) }}" class="flex items-center px-3 py-1.5 text-xs text-gray-500 dark:text-gray-500 hover:text-qricket-600 dark:hover:text-qricket-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">Rejected</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Service Plans (mobile) -->
            <div>
                <button @click="salesOpen = false; usersOpen = false; eventsOpen = false; refundsOpen = false; plansOpen = !plansOpen" x-data="{ plansOpen: false }" class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                        Service Plans
                    </div>
                    <svg class="w-4 h-4 transition-transform" :class="{'rotate-180': plansOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="plansOpen" class="pl-6 space-y-1 mt-1">
                    <a href="{{ route('admin.subscriptions.index', ['section' => 'pricing']) }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-qricket-600 dark:hover:text-qricket-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">Ticket Pricing</a>
                    <a href="{{ route('admin.subscriptions.index', ['section' => 'discounts']) }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-qricket-600 dark:hover:text-qricket-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">Discounts</a>
                    <a href="{{ route('admin.subscriptions.index', ['section' => 'subscriptions']) }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-qricket-600 dark:hover:text-qricket-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">Subscriptions</a>
                </div>
            </div>
            
            <!-- Support -->
            <a href="{{ route('admin.support') }}" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-qricket-600 dark:hover:text-qricket-400 rounded-md transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Support
            </a>
            
            @if($adminUser)
            <div class="border-t border-gray-200 dark:border-gray-700 my-2 pt-2">
                <a href="{{ route('admin.activity-log.index') }}" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-qricket-600 dark:hover:text-qricket-400 rounded-md transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    My Activity Log
                </a>
            </div>
            @endif
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuButton = document.getElementById('admin-nav-menu');
    const mobileMenuOverlay = document.getElementById('admin-mobile-menu-overlay');
    const closeButton = document.getElementById('admin-mobile-menu-close');
    
    // Open menu
    if (menuButton && mobileMenuOverlay) {
        menuButton.addEventListener('click', function() {
            mobileMenuOverlay.classList.remove('hidden');
        });
    }
    
    // Close menu
    if (closeButton && mobileMenuOverlay) {
        closeButton.addEventListener('click', function() {
            mobileMenuOverlay.classList.add('hidden');
        });
    }
    
    // Close menu when clicking backdrop
    if (mobileMenuOverlay) {
        mobileMenuOverlay.addEventListener('click', function(e) {
            if (e.target === mobileMenuOverlay || e.target.classList.contains('bg-black')) {
                mobileMenuOverlay.classList.add('hidden');
            }
        });
    }
    
    // Dark mode toggle (both mobile and desktop)
    const toggleDarkMode = function() {
        const html = document.documentElement;
        const isDark = html.classList.contains('dark');
        
        if (isDark) {
            html.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        } else {
            html.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        }
    };
    
    // Mobile dark mode toggle
    const mobileDarkModeToggle = document.getElementById('mobile-darkModeToggle');
    if (mobileDarkModeToggle) {
        mobileDarkModeToggle.addEventListener('click', toggleDarkMode);
    }
    
    // Desktop dark mode toggle
    const desktopDarkModeToggle = document.getElementById('desktop-darkModeToggle');
    if (desktopDarkModeToggle) {
        desktopDarkModeToggle.addEventListener('click', toggleDarkMode);
    }
});
</script>


