<x-app-layout title="Welcome to Qricket - Your Event Management Platform">
 
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-qricket-500 to-qricket-600 dark:from-qricket-600 dark:to-qricket-700 py-12 px-6">
        <div class="max-w-6xl mx-auto flex flex-col-reverse md:flex-row items-center gap-8">

            <!--Left Text Content-->
            <div class="md:w-1/2 text-center md:text-left">
                <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">Welcome to Qricket</h1>
                <p class="text-qricket-100 dark:text-qricket-200 mb-6 text-lg">Your one-stop solution for event management. Create, scan, and manage events effortlessly with QR technology.</p>
                
                <div class="flex flex-col sm:flex-row gap-3 justify-center md:justify-start">
                    <a href="{{ route('events.browse') }}" class="bg-white hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-qricket-600 dark:text-qricket-400 px-6 py-3 rounded-lg font-semibold transition-all duration-200 transform hover:scale-105 shadow-lg">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Browse Events
                    </a>
                    @auth
                        @php
                            $isOrganizerMode = session('user_mode') === 'organizer';
                            $organizerDashboardRoute = route('organizer.events.index');
                            $buttonText = $isOrganizerMode ? 'Organizer Dashboard' : 'Host Your Events';
                            $buttonIcon = 'M12 6v6m0 0v6m0-6h6m-6 0H6';
                        @endphp
                        @if($isOrganizerMode)
                        <a href="{{ $organizerDashboardRoute }}" class="bg-qricket-700 hover:bg-qricket-800 dark:bg-qricket-600 dark:hover:bg-qricket-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 transform hover:scale-105 shadow-lg">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $buttonIcon }}"></path>
                            </svg>
                            {{ $buttonText }}
                        </a>
                        @else
                        <a href="javascript:void(0)" onclick="switchToOrganizerAndGo()" class="bg-qricket-700 hover:bg-qricket-800 dark:bg-qricket-600 dark:hover:bg-qricket-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 transform hover:scale-105 shadow-lg">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $buttonIcon }}"></path>
                            </svg>
                            {{ $buttonText }}
                        </a>
                        @endif
                    @else
                        <a href="{{ route('register') }}" class="bg-qricket-700 hover:bg-qricket-800 dark:bg-qricket-600 dark:hover:bg-qricket-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 transform hover:scale-105 shadow-lg">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            Get Started
                        </a>
                    @endauth
                </div>
            </div>

            <!--Right Content - Hero Image-->
            <div class="w-full max-w-md">
                <div class="bg-white/10 dark:bg-white/5 backdrop-blur-sm rounded-2xl p-8 shadow-2xl">
                    <div class="text-center">
                        <div class="w-24 h-24 bg-white/20 dark:bg-white/10 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2">QR-Powered Events</h3>
                        <p class="text-qricket-100 dark:text-qricket-200 text-sm">Scan, check-in, and manage with ease</p>
                    </div>
                </div>
            </div>    

        </div>
    </section>

    <!-- How it Works Section -->
    <section class="bg-gray-50 dark:bg-gray-800 py-12 px-6">
        <div class="max-w-6xl mx-auto text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-3">How it Works</h2>
            <p class="text-gray-600 dark:text-gray-300">Get started with Qricket in just a few simple steps</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
            <!--Step 1-->
            <div class="bg-white dark:bg-gray-700 p-6 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                <div class="flex justify-center mb-4">
                    <div class="w-16 h-16 bg-qricket-100 dark:bg-qricket-200 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-qricket-600 dark:text-qricket-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                </div>
                
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2">Create or Join Events</h3>
                <p class="text-gray-600 dark:text-gray-300 text-sm">Organizers can set up events while users can browse and join in seconds.</p>
            </div>

            <!--Step 2-->
            <div class="bg-white dark:bg-gray-700 p-6 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                <div class="flex justify-center mb-4">
                    <div class="w-16 h-16 bg-qricket-100 dark:bg-qricket-200 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-qricket-600 dark:text-qricket-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                        </svg>
                    </div>
                </div>
                
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2">QR Code Check-ins</h3>
                <p class="text-gray-600 dark:text-gray-300 text-sm">Use QR codes for quick event access and seamless check-ins.</p>
            </div>

            <!--Step 3-->
            <div class="bg-white dark:bg-gray-700 p-6 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                <div class="flex justify-center mb-4">
                    <div class="w-16 h-16 bg-qricket-100 dark:bg-qricket-200 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-qricket-600 dark:text-qricket-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
                
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2">Track & Manage</h3>
                <p class="text-gray-600 dark:text-gray-300 text-sm">Monitor attendance, sales, and analytics with powerful dashboard tools.</p>
            </div>
        </div>
    </section>

    <!-- Upcoming Events Section -->
    <section class="bg-white dark:bg-gray-900 py-12 px-6">
        <div class="max-w-6xl mx-auto text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-3">Upcoming Events</h2>
            <p class="text-gray-600 dark:text-gray-300">Discover upcoming experiences you'll love</p>
        </div>
        @if(isset($upcomingEvents) && $upcomingEvents->count())
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
                @foreach($upcomingEvents as $event)
                    <a href="{{ route('events.show', $event) }}" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-5 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-105 block text-left group">
                        <div class="mb-4 h-32 overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-700">
                            @if($event->thumbnail_url)
                                <img src="{{ $event->thumbnail_url }}" alt="{{ $event->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-qricket-100 dark:bg-qricket-200">
                                    <svg class="w-8 h-8 text-qricket-600 dark:text-qricket-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2 group-hover:text-qricket-600 dark:group-hover:text-qricket-400 transition-colors">{{ $event->title }}</h3>
                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-3">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            {{ optional($event->start_date)->format('M j, Y') }}
                            <svg class="w-4 h-4 ml-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            {{ $event->location }}
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">{{ \Illuminate\Support\Str::limit($event->description, 100) }}</p>
                        <span class="inline-flex items-center text-qricket-600 dark:text-qricket-400 font-semibold text-sm group-hover:text-qricket-700 dark:group-hover:text-qricket-300 transition-colors">
                            View Details
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="max-w-5xl mx-auto text-center text-gray-500 dark:text-gray-400 py-8">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <p>No upcoming events yet. Check back soon for exciting events!</p>
            </div>
        @endif
    </section>

    <!-- CTA Section -->
    <section class="bg-gradient-to-r from-qricket-500 to-qricket-600 dark:from-qricket-600 dark:to-qricket-700 py-12 px-6">
        <div class="max-w-5xl mx-auto text-center">
            <h2 class="text-2xl font-bold text-white mb-3">Planning an Event?</h2>
            <p class="text-qricket-100 dark:text-qricket-200 mb-6 text-lg">Join Qricket as an organizer today and experience powerful tools for managing your events and analytics.</p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('subscription.index') }}" class="bg-white hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-qricket-600 dark:text-qricket-400 px-8 py-3 rounded-lg font-semibold transition-all duration-200 transform hover:scale-105 shadow-lg">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    View Plans
                </a>
                <a href="{{ route('help.index') }}" class="bg-qricket-700 hover:bg-qricket-800 dark:bg-qricket-600 dark:hover:bg-qricket-700 text-white px-8 py-3 rounded-lg font-semibold transition-all duration-200 transform hover:scale-105 shadow-lg">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Learn More
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <x-footer />

    @auth
    <script>
        function switchToOrganizerAndGo() {
            const csrf = document.querySelector('meta[name="csrf-token"]');
            fetch("{{ route('user.mode-switch') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') : ''
                },
                body: JSON.stringify({ mode: 'organizer' })
            })
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(() => { window.location.href = "{{ route('organizer.events.index') }}"; })
            .catch(() => { window.location.href = "{{ route('organizer.events.index') }}"; });
        }
    </script>
    @endauth
</x-app-layout>
