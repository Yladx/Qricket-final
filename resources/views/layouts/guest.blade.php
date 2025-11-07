<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name', 'Qricket') }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'Qricket - Effortless event ticketing and QR-based check-ins. Create, manage, and attend events with ease.' }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
    <meta name="user-id" content="{{ Auth::id() }}">
    @endauth
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <!-- Apply saved theme ASAP to avoid flash -->
    <script>(function(){try{if(localStorage.getItem('darkMode')==='true'){document.documentElement.classList.add('dark');}}catch(e){}})();</script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="preconnect" href="https://cdn.tailwindcss.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preload" href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"></noscript>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com" defer></script>
    <link rel="stylesheet" href="{{ asset('css/qricket-styles.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Pusher.js for Laravel Reverb -->
    <script defer src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    
    <!-- reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <script>
        // Wait for Tailwind to load before configuring it
        function configureTailwind() {
            if (typeof tailwind !== 'undefined') {
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
        
        // Try to configure Tailwind immediately if already loaded
        configureTailwind();
        
        // Also try after DOMContentLoaded and after a delay
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', configureTailwind);
        }
        
        // Fallback: try again after a short delay
        setTimeout(configureTailwind, 100);
        setTimeout(configureTailwind, 500);
    </script>

    <script>
        // Broadcasting configuration for frontend (Pusher Cloud only)
        // Use config() instead of env() to work with cached config
        window.PUSHER_APP_KEY = '{{ config('broadcasting.connections.pusher.key', '') }}';
        window.PUSHER_APP_CLUSTER = '{{ config('broadcasting.connections.pusher.options.cluster', 'mt1') }}';
    </script>

    <!-- Theme manager (respects saved preference, enables toggling if present) -->
    <script src="{{ asset('js/dark-mode.js') }}"></script>

    <!-- Include reusable password toggle script -->
    <script defer src="{{ asset('js/qricket-scripts.js') }}?v={{ time() }}"></script>
    
    <!-- Global Notification Handler -->
    <script defer src="{{ asset('js/notifications.js') }}"></script>
    
    <!-- SweetAlert2 -->
    <script defer src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- SweetAlert Utilities -->
    <script defer src="{{ asset('js/sweetalert-utils.js') }}"></script>
    
    <!-- Profanity Filter (Real-time) -->
    <script defer src="{{ asset('js/profanity-filter.js') }}?v={{ time() }}"></script>
</head>
<body class="h-screen bg-gray-100 dark:bg-gray-900 transition-colors duration-300">
    <!-- Flash Alerts -->
    {{-- <x-flash-alerts /> --}}
    
    <!-- Main Content -->
    <main class="h-full">
        {{ $slot }}
    </main>

    <!-- Scripts -->
    @isset($scripts)
        {{ $scripts }}
    @endisset
</body>
</html>