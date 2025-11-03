<nav class="bg-white shadow-sm border-b border-gray-100 dark:bg-gray-800 dark:border-gray-700 transition-all duration-300 sticky top-0 z-50">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('welcome') }}" class="flex items-center transition-all duration-300 hover:scale-105">
                        <img src="{{ asset('Logo.svg') }}" alt="Qricket Logo" class="h-10 w-auto">
                    </a>
                </div>

                <!-- Navigation Links - Dynamic based on mode -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-6 sm:flex items-center">
                    @auth
                    <!-- User Mode Navigation -->
                    <div id="user-nav-links" class="flex items-center space-x-8 {{ $isOrganizerSide ?? false ? 'hidden' : '' }}">
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-gray-700 dark:text-gray-300 hover:text-qricket-500 dark:hover:text-qricket-400 transition-colors duration-200">
                            {{ __('Homepage') }}
                        </x-nav-link>
                        <x-nav-link :href="route('events.browse')" :active="request()->routeIs('events.*')" class="text-gray-700 dark:text-gray-300 hover:text-qricket-500 dark:hover:text-qricket-400 transition-colors duration-200">
                            {{ __('Events') }}
                        </x-nav-link>
                        
                    </div>
                    
                    <!-- Organizer Mode Navigation -->
                    <div id="organizer-nav-links" class="flex items-center space-x-8 {{ $isOrganizerSide ?? false ? '' : 'hidden' }}">
                        <x-nav-link :href="route('organizer.dashboard')" :active="request()->routeIs('organizer.dashboard')" class="text-gray-700 dark:text-gray-300 hover:text-qricket-500 dark:hover:text-qricket-400 transition-colors duration-200">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('organizer.events.index')" :active="request()->routeIs('organizer.events.*')" class="text-gray-700 dark:text-gray-300 hover:text-qricket-500 dark:hover:text-qricket-400 transition-colors duration-200">
                            {{ __('Events') }}
                        </x-nav-link>
                    </div>
                    @endauth
                    
                    <!-- Subscription Link - Public route -->
                    <x-nav-link :href="route('subscription.index')" :active="request()->routeIs('subscription.*')" class="text-gray-700 dark:text-gray-300 hover:text-qricket-500 dark:hover:text-qricket-400 transition-colors duration-200">
                        {{ __('Subscription') }}
                    </x-nav-link>
                    
                    <!-- Help & Support Link - Always visible -->
                    <x-nav-link :href="route('help.index')" :active="request()->routeIs('help.*')" class="text-gray-700 dark:text-gray-300 hover:text-qricket-500 dark:hover:text-qricket-400 transition-colors duration-200">
                        {{ __('Help & Support') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown - Only show if authenticated -->
            @auth
            <div class="hidden sm:flex sm:items-center sm:ms-6 space-x-4">
                <!-- Dark Mode Toggle -->
                <button id="dark-mode-toggle" class="p-2 text-gray-500 hover:text-qricket-500 dark:text-gray-400 dark:hover:text-qricket-400 focus:outline-none focus:text-qricket-500 dark:focus:text-qricket-400 transition-all duration-200 transform hover:scale-110 dark-mode-toggle">
                    <!-- Sun icon for dark mode -->
                    <svg id="sun-icon" class="h-6 w-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <!-- Moon icon for light mode -->
                    <svg id="moon-icon" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                </button>

                <!-- Notification Bell -->
                <div class="relative">
                    <a href="{{ route('notifications.index') }}" id="notifications-toggle" class="p-2 text-gray-500 hover:text-qricket-500 dark:text-gray-400 dark:hover:text-qricket-400 focus:outline-none focus:text-qricket-500 dark:focus:text-qricket-400 transition-all duration-200 transform hover:scale-110 relative inline-block">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <!-- Notification Badge -->
                        <span class="notification-badge absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 hidden min-w-[1.25rem] h-5 inline-flex items-center justify-center px-1.5 text-[10px] font-bold leading-none text-white bg-red-500 rounded-full border-2 border-white dark:border-gray-800 shadow-sm">0</span>
                    </a>
                </div>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-2 py-1.5 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-qricket-500 dark:text-gray-400 dark:bg-gray-800 dark:hover:text-qricket-400 focus:outline-none transition-all duration-200 transform hover:scale-105 min-w-0">
                            @if(Auth::user()->profile_picture)
                                <img class="h-8 w-8 rounded-full object-cover border border-gray-200 dark:border-gray-600 shadow" src="{{ Auth::user()->profile_picture_url }}" alt="{{ Auth::user()->full_name }}">
                            @else
                                @php
                                    $name = Auth::user()->full_name;
                                    $initials = strtoupper(substr($name, 0, 1) . (str_contains($name, ' ') ? substr($name, strpos($name, ' ') + 1, 1) : ''));
                                    $colors = ['bg-qricket-500', 'bg-qricket-600', 'bg-qricket-500'];
                                    $bgColor = $colors[crc32($name)%count($colors)];
                                @endphp
                                <div class="h-8 w-8 rounded-full overflow-hidden {{ $bgColor }} flex items-center justify-center text-white font-medium text-sm shadow">
                                    {{ $initials }}
                                </div>
                            @endif
                            <div class="truncate max-w-32 hidden lg:block ml-2">{{ Auth::user()->full_name }}</div>

                            <div class="ms-1 flex-shrink-0 hidden lg:block">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Mode Toggle Switch -->
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <button id="mode-toggle" onclick="confirmModeSwitch()" class="relative inline-flex h-6 w-11 items-center rounded-full bg-gray-200 dark:bg-gray-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-qricket-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" role="switch" aria-checked="false">
                                    <span id="mode-toggle-slider" class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200 translate-x-1"></span>
                                </button>
                                <span id="mode-text" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $isOrganizerSide ? 'Organizer' : 'User' }}</span>
                            </div>
                        </div>

                        @if(!Auth::guard('admin')->check())
                        <x-dropdown-link :href="route('profile.edit')" class="text-gray-700 dark:text-gray-300 hover:text-qricket-500 dark:hover:text-qricket-400 transition-all duration-200">
                            {{ __('Profile') }}
                        </x-dropdown-link>
                        @endif

                        @if(!$isOrganizerSide)
                        <x-dropdown-link :href="route('events.saved')" class="text-gray-700 dark:text-gray-300 hover:text-qricket-500 dark:hover:text-qricket-400 transition-all duration-200">
                            {{ __('Saved Events') }}
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('my-tickets.index')" class="text-gray-700 dark:text-gray-300 hover:text-qricket-500 dark:hover:text-qricket-400 transition-all duration-200">
                            {{ __('My Tickets') }}
                        </x-dropdown-link>
                        @endif

                        @if(Auth::guard('admin')->check())
                            <x-dropdown-link :href="route('admin.dashboard')" class="text-gray-700 dark:text-gray-300 hover:text-qricket-500 dark:hover:text-qricket-400 transition-all duration-200">
                                {{ __('Go back to Admin Dashboard') }}
                            </x-dropdown-link>
                        @else
                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}" id="logout-form-desktop">
                            @csrf
                            <button type="button" 
                                    onclick="confirmLogout('logout-form-desktop')"
                                    class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 transition duration-150 ease-in-out">
                                {{ __('Log Out') }}
                            </button>
                        </form>
                        @endif
                    </x-slot>
                </x-dropdown>
            </div>
            @else
            <!-- Sign In Button for non-authenticated users -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 space-x-4">
                <!-- Dark Mode Toggle -->
                <button id="dark-mode-toggle" class="p-2 text-gray-500 hover:text-qricket-500 dark:text-gray-400 dark:hover:text-qricket-400 focus:outline-none focus:text-qricket-500 dark:focus:text-qricket-400 transition-all duration-200 transform hover:scale-110 dark-mode-toggle">
                    <!-- Sun icon for dark mode -->
                    <svg id="sun-icon" class="h-6 w-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <!-- Moon icon for light mode -->
                    <svg id="moon-icon" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                </button>

                <!-- Sign In Button -->
                <div>
                    <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-qricket-500 hover:bg-qricket-600 dark:bg-qricket-500 dark:hover:bg-qricket-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-qricket-500 dark:focus:ring-offset-gray-800 transition-all duration-200 transform hover:scale-105">
                        {{ __('Sign In') }}
                    </a>
                </div>
            </div>
            @endauth

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden space-x-2">
                <!-- Mobile Dark Mode Toggle -->
                <button id="dark-mode-toggle-mobile" class="p-2 text-gray-400 hover:text-qricket-500 dark:hover:text-qricket-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 focus:text-qricket-500 dark:focus:text-qricket-400 transition duration-150 ease-in-out dark-mode-toggle" aria-label="Toggle theme">
                    <!-- Sun icon for dark mode -->
                    <svg id="sun-icon-mobile" class="h-6 w-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <!-- Moon icon for light mode -->
                    <svg id="moon-icon-mobile" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                </button>
                
                <!-- Mobile Notification Bell -->
                <a href="{{ route('notifications.index') }}" id="notifications-toggle-mobile" class="relative inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-qricket-500 dark:hover:text-qricket-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 focus:text-qricket-500 dark:focus:text-qricket-400 transition duration-150 ease-in-out" aria-label="Notifications">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span class="notification-badge absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 z-10 hidden min-w-[1.1rem] h-4 inline-flex items-center justify-center px-1 text-[10px] font-bold leading-none text-white bg-red-500 rounded-full border-2 border-white dark:border-gray-700 shadow-sm">0</span>
                </a>
                <!-- Mobile Mode Toggle (outside off-canvas) -->
                @auth
                <button id="mode-toggle-mobile-inline" onclick="confirmModeSwitch()" class="relative inline-flex h-6 w-11 items-center rounded-full bg-gray-200 dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-qricket-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" role="switch" aria-checked="false" aria-label="Switch mode">
                    <span id="mode-toggle-slider-mobile-inline" class="inline-block h-4 w-4 transform rounded-full bg-white shadow translate-x-1"></span>
                </button>
                @endauth
                
                <!-- Bootstrap-style Offcanvas Toggle -->
                <button id="offcanvas-toggle" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-qricket-500 dark:hover:text-qricket-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 focus:text-qricket-500 dark:focus:text-qricket-400 transition duration-150 ease-in-out" aria-label="Toggle menu" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-nav">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

</nav>

<!-- Bootstrap-style Offcanvas Sidebar (mobile) -->
<div id="offcanvas-backdrop" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden transition-opacity duration-300"></div>
<aside id="offcanvas-nav" class="fixed top-0 end-0 h-full w-320px max-w-[85vw] bg-white dark:bg-gray-800 shadow-xl z-50 transition-transform duration-300 ease-in-out transform translate-x-full flex flex-col">
    <!-- Offcanvas Header -->
    <div class="offcanvas-header px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h4 class="offcanvas-title text-lg font-semibold text-gray-900 dark:text-gray-100">Navigation Menu</h4>
        <button id="offcanvas-close" class="btn-close p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors duration-200" aria-label="Close">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    
    <!-- Offcanvas Body -->
    <div class="offcanvas-body flex-1 overflow-auto">
        <!-- Mode Toggle Section -->
        @auth
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Switch Mode</span>
                <button id="offcanvas-mode-toggle" onclick="confirmModeSwitch()" class="relative inline-flex h-6 w-11 items-center rounded-full bg-gray-200 dark:bg-gray-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-qricket-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" role="switch" aria-checked="false">
                    <span id="offcanvas-mode-toggle-slider" class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200 translate-x-1"></span>
                </button>
            </div>
            <div class="mt-2">
                <span id="offcanvas-mode-text" class="text-xs text-gray-600 dark:text-gray-400">Currently in User mode</span>
            </div>
        </div>
        @endauth
        
        <!-- Navigation Links -->
        <div class="px-4 py-4 space-y-2">
            @auth
            <!-- User Mode Links -->
            <div id="offcanvas-user-nav" class="{{ $isOrganizerSide ?? false ? 'hidden' : '' }}">
                <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-qricket-500 dark:hover:text-qricket-400 rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                    </svg>
                    Homepage
                </a>
                <a href="{{ route('events.browse') }}" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-qricket-500 dark:hover:text-qricket-400 rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Events
                </a>
                
                <!-- Organizer Button for Offcanvas -->
                <button onclick="confirmModeSwitch()" class="flex items-center w-full px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-qricket-500 dark:hover:text-qricket-400 rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    Organizer Mode
                </button>
            </div>
            
            <!-- Organizer Mode Links -->
            <div id="offcanvas-organizer-nav" class="{{ $isOrganizerSide ?? false ? '' : 'hidden' }}">
                <a href="{{ route('organizer.dashboard') }}" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-qricket-500 dark:hover:text-qricket-400 rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('organizer.events.index') }}" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-qricket-500 dark:hover:text-qricket-400 rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Events
                </a>
            </div>
            @else
            <!-- Guest Links -->
            <a href="{{ route('login') }}" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-qricket-500 dark:hover:text-qricket-400 rounded-lg transition-colors duration-200">
                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                </svg>
                Sign In
            </a>
            @if (Route::has('register'))
            <a href="{{ route('register') }}" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-qricket-500 dark:hover:text-qricket-400 rounded-lg transition-colors duration-200">
                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
                Sign Up
            </a>
            @endif
            @endauth
            
            <!-- Always Visible Links -->
            <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('subscription.index') }}" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-qricket-500 dark:hover:text-qricket-400 rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.118-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    Subscription
                </a>
                <a href="{{ route('help.index') }}" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-qricket-500 dark:hover:text-qricket-400 rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Help & Support
                </a>
            </div>
        </div>
    </div>
    <!-- Profile (pinned at bottom) -->
    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
        @auth
        <div class="flex items-center space-x-3">
            <div class="flex-shrink-0">
                @if(Auth::user()->profile_picture)
                    <img class="h-10 w-10 rounded-full object-cover border-2 border-gray-200 dark:border-gray-600" src="{{ Auth::user()->profile_picture_url }}" alt="{{ Auth::user()->full_name }}">
                @else
                    @php
                        $name = Auth::user()->full_name;
                        $initials = strtoupper(substr($name, 0, 1) . (str_contains($name, ' ') ? substr($name, strpos($name, ' ') + 1, 1) : ''));
                        $colors = ['bg-qricket-500','bg-qricket-600','bg-qricket-500'];
                        $bgColor = $colors[crc32($name)%count($colors)];
                    @endphp
                    <div class="h-10 w-10 rounded-full overflow-hidden {{ $bgColor }} flex items-center justify-center text-white font-medium">{{ $initials }}</div>
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">{{ Auth::user()->full_name }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ Auth::user()->email }}</div>
            </div>
        </div>
        <!-- Off-canvas Logout -->
        <div class="mt-3">
            <form method="POST" action="{{ route('logout') }}" id="logout-form-offcanvas">
                @csrf
                <button type="button"
                        onclick="confirmLogout('logout-form-offcanvas')"
                        class="w-full inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-red-500 hover:bg-red-600 rounded-md transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
        @endauth
    </div>
    <!-- Bottom spacer -->
    <div class="p-2 border-t border-transparent"></div>
</aside>

<!-- Bootstrap-style Offcanvas JavaScript -->
<script src="{{ asset('js/bootstrap-offcanvas.js') }}"></script>

<script>
function confirmModeSwitch() {
    // Get current mode from session or determine from current state
    const currentMode = '{{ session("user_mode", "user") }}';
    const targetMode = currentMode === 'organizer' ? 'user' : 'organizer';
    
    // Show SweetAlert confirmation
    const isDark = document.documentElement.classList.contains('dark');
    
    Swal.fire({
        title: 'Switch Mode',
        text: `Are you sure you want to switch to ${targetMode === 'organizer' ? 'Organizer' : 'User'} mode?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: `Switch to ${targetMode === 'organizer' ? 'Organizer' : 'User'} Mode`,
        cancelButtonText: 'Cancel',
        customClass: {
            popup: isDark ? 'dark:bg-gray-800 dark:text-gray-100' : '',
            title: isDark ? 'dark:text-gray-100' : '',
            htmlContainer: isDark ? 'dark:text-gray-300' : '',
            confirmButton: 'bg-qricket-500 hover:bg-qricket-600',
        },
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create a form and submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/user/mode-switch';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            form.appendChild(csrfToken);
            
            const modeInput = document.createElement('input');
            modeInput.type = 'hidden';
            modeInput.name = 'mode';
            modeInput.value = targetMode;
            form.appendChild(modeInput);
            
            document.body.appendChild(form);
            
            // Update switch states before form submission
            updateModeSwitchStates(targetMode);
            
            form.submit();
        }
    });
}

// Function to update all mode switch states
function updateModeSwitchStates(targetMode) {
    const isOrganizerMode = targetMode === 'organizer';
    const sliderPosition = isOrganizerMode ? 'translate-x-5' : 'translate-x-1';
    const bgColor = isOrganizerMode ? 'bg-qricket-500' : 'bg-gray-200 dark:bg-gray-700';
    const modeText = isOrganizerMode ? 'Organizer' : 'User';
    
    // Update all toggle switches
    const switches = [
        'mode-toggle',
        'mode-toggle-mobile',
        'mode-toggle-mobile-inline',
        'offcanvas-mode-toggle'
    ];
    
    const sliders = [
        'mode-toggle-slider',
        'mode-toggle-slider-mobile',
        'mode-toggle-slider-mobile-inline',
        'offcanvas-mode-toggle-slider'
    ];
    
    const modeTexts = [
        'mode-text',
        'mode-text-mobile',
        'offcanvas-mode-text'
    ];
    
    // Update switch backgrounds and sliders
    switches.forEach((switchId, index) => {
        const switchElement = document.getElementById(switchId);
        const sliderElement = document.getElementById(sliders[index]);
        
        if (switchElement && sliderElement) {
            // Update background color
            switchElement.className = switchElement.className.replace(/bg-(qricket-500|gray-200|gray-700)/g, '');
            switchElement.className += ' ' + bgColor;
            
            // Update slider position
            sliderElement.classList.remove('translate-x-1', 'translate-x-5');
            sliderElement.classList.add(sliderPosition.replace(' ', ''));
        }
    });
    
    // Update mode text
    modeTexts.forEach(textId => {
        const textElement = document.getElementById(textId);
        if (textElement) {
            if (textId === 'offcanvas-mode-text') {
                textElement.textContent = `Currently in ${modeText} mode`;
            } else {
                textElement.textContent = modeText;
            }
        }
    });
}

// Initialize switch states on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeModeSwitchStates();
});

// Function to initialize switch states on page load
function initializeModeSwitchStates() {
    // Check current mode from the mode text elements
    const modeTextElement = document.getElementById('mode-text');
    if (modeTextElement) {
        const currentMode = modeTextElement.textContent.trim().toLowerCase();
        const isOrganizerMode = currentMode === 'organizer';
        
        updateModeSwitchStates(isOrganizerMode ? 'organizer' : 'user');
    }
}
</script>


