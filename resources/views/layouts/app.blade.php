<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @auth
        <meta name="user-id" content="{{ Auth::id() }}">
        @endauth
        <!-- Cache Control - Allow caching for better performance -->
        <meta http-equiv="Cache-Control" content="public, max-age=3600">
        <meta http-equiv="Expires" content="{{ gmdate('D, d M Y H:i:s', time() + 3600) }} GMT">

        <title>{{ $title ?? config('app.name', 'Qricket') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('Logo.svg') }}">
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

        <!-- Preload critical resources -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="preconnect" href="https://cdn.tailwindcss.com">
        <link rel="preconnect" href="https://cdnjs.cloudflare.com">
        
        <!-- Critical CSS - Load first -->
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="{{ asset('css/qricket-styles.css') }}">
        
        <!-- Non-critical CSS - Load after -->
        <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
        <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
        
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Defer non-critical JavaScript -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script defer src="{{ asset('js/sweetalert-utils.js') }}"></script>
        <script defer src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
        
        <!-- Broadcasting configuration MUST be set before notifications.js -->
        <script>
            // Broadcasting configuration for frontend (Pusher Cloud only)
            // This script runs synchronously to ensure config is available before deferred scripts
            // Use config() instead of env() to work with cached config
            window.PUSHER_APP_KEY = '{{ config('broadcasting.connections.pusher.key', '') }}';
            window.PUSHER_APP_CLUSTER = '{{ config('broadcasting.connections.pusher.options.cluster', 'mt1') }}';
            window.PUSHER_AUTH_ENDPOINT = '{{ url('/broadcasting/auth') }}';
            @auth
            window.USER_ID = {{ Auth::id() }};
            @endauth
        </script>
        
        <script defer src="{{ asset('js/notifications.js') }}"></script>
        <script defer src="{{ asset('js/notification-badge.js') }}"></script>
        <script defer src="{{ asset('js/dark-mode.js') }}"></script>
        <script defer src="{{ asset('js/organizer-verification.js') }}?v={{ time() }}"></script>
        <script defer src="{{ asset('js/qricket-scripts.js') }}?v={{ time() }}"></script>
        <script defer src="{{ asset('js/logout-prevention.js') }}?v={{ time() }}"></script>
        <script src="{{ asset('js/profanity-filter.js') }}?v={{ time() }}"></script>
        
        <script>
            // Configure Tailwind for better dark mode
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        colors: {
                            qricket: {
                                50: '#f0fdfa',
                                100: '#ccfbf1',
                                200: '#99f6e4',
                                300: '#5eead4',
                                400: '#2dd4bf',
                                500: '#00B388',
                                600: '#009970',
                                700: '#0d9488',
                                800: '#115e59',
                                900: '#134e4a',
                            }
                        },
                        animation: {
                            'fade-in': 'fadeIn 0.3s ease-in-out',
                            'slide-down': 'slideDown 0.3s ease-out',
                            'bounce-in': 'bounceIn 0.6s ease-out',
                            'slide-in-left': 'slideInLeft 0.4s ease-out',
                        },
                        keyframes: {
                            fadeIn: {
                                '0%': { opacity: '0' },
                                '100%': { opacity: '1' },
                            },
                            slideDown: {
                                '0%': { transform: 'translateY(-10px)', opacity: '0' },
                                '100%': { transform: 'translateY(0)', opacity: '1' },
                            },
                            bounceIn: {
                                '0%': { transform: 'scale(0.3)', opacity: '0' },
                                '50%': { transform: 'scale(1.05)' },
                                '70%': { transform: 'scale(0.9)' },
                                '100%': { transform: 'scale(1)', opacity: '1' },
                            },
                            slideInLeft: {
                                '0%': { transform: 'translateX(-20px)', opacity: '0' },
                                '100%': { transform: 'translateX(0)', opacity: '1' },
                            }
                        }
                    }
                }
            }
        </script>
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 transition-colors duration-300 overflow-x-hidden">
        <script>
            // Apply dark mode class ASAP to avoid flash of light theme
            (function(){
                try {
                    const stored = localStorage.getItem('theme');
                    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                    const shouldDark = stored ? (stored === 'dark') : prefersDark;
                    if (shouldDark) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                } catch (e) {
                    // fallback to prefers-color-scheme
                    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300 overflow-x-hidden">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 transition-colors duration-300 relative z-20">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Title Section -->
            @isset($pageTitle)
                <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 transition-colors duration-300">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $pageTitle }}</h1>
                    </div>
                </div>
            @endisset

            <!-- Page Content -->
            <main class="transition-colors duration-300">
                @hasSection('content')
                    @yield('content')
                @else
                    {{ $slot ?? '' }}
                @endif
            </main>
        </div>
        
        <!-- Global Flash Alerts (SweetAlert2) via component -->
        <x-flash-alerts />
        
        <!-- Pusher for real-time notifications -->
        <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
        <script>
            // Initialize Pusher
            const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
                cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
                encrypted: true
            });

            // Listen for user logout notifications
            @auth
            const userId = {{ Auth::id() }};
            const channel = pusher.subscribe('user-session.' + userId);
            
            channel.bind('user.logged.out', function(data) {
                console.log('User logout notification received:', data);
                
                // Show SweetAlert notification
                Swal.fire({
                    title: 'Session Ended',
                    text: data.message,
                    icon: 'warning',
                    showCancelButton: false,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#ef4444',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    customClass: {
                        popup: 'rounded-2xl shadow-2xl',
                        title: 'text-xl font-semibold',
                        htmlContainer: 'text-gray-600',
                        confirmButton: 'bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500'
                    },
                    buttonsStyling: false
                }).then(() => {
                    // Redirect to login page after user acknowledges
                    window.location.href = '{{ route('login') }}';
                });
            });
            @endauth
        </script>
        
        <!-- Include Qricket Feedback Modal -->
        @include('components.qricket-feedback-modal')
        
        @yield('scripts')
    </body>
</html>
