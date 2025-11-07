<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Qricket Admin' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Pusher.js for Laravel Reverb -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    
    <!-- Dark Mode Management -->
    <script src="{{ asset('js/dark-mode.js') }}"></script>
    
    <!-- Broadcasting configuration MUST be set before notifications.js -->
    <script>
        // Broadcasting configuration for frontend (Pusher Cloud only)
        // This script runs synchronously to ensure config is available before deferred scripts
        // Use config() instead of env() to work with cached config
        window.PUSHER_APP_KEY = '{{ config('broadcasting.connections.pusher.key', '') }}';
        window.PUSHER_APP_CLUSTER = '{{ config('broadcasting.connections.pusher.options.cluster', 'mt1') }}';
        // Admin auth endpoint for private channels
        window.PUSHER_AUTH_ENDPOINT = '/admin/broadcasting/auth';
        
        // Debug: Log config status (remove in production)
        console.log('[Admin Layout] Pusher config set:', {
            hasKey: !!window.PUSHER_APP_KEY && window.PUSHER_APP_KEY !== '',
            keyLength: window.PUSHER_APP_KEY ? window.PUSHER_APP_KEY.length : 0,
            cluster: window.PUSHER_APP_CLUSTER
        });
    </script>
    
    <!-- Global Notification Handler -->
    <script src="{{ asset('js/notifications.js') }}"></script>
    
    <!-- Admin Notifications Handler (real-time Pusher + badge updates) -->
    <script src="{{ asset('js/admin-notifications.js') }}?v={{ time() }}"></script>
    
    <!-- Qricket General Scripts -->
    <script src="{{ asset('js/qricket-scripts.js') }}?v={{ time() }}"></script>
    
    <!-- Logout Prevention Script -->
    <script src="{{ asset('js/logout-prevention.js') }}?v={{ time() }}"></script>
    
    <!-- Profanity Filter (Real-time) -->
    <script src="{{ asset('js/profanity-filter.js') }}?v={{ time() }}"></script>
    
    <script>
        // Configure Tailwind for dark mode
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
    
    @yield('styles')
</head>

<body class="h-screen bg-gray-100 dark:bg-gray-900 transition-colors duration-300 admin">
    <!-- Header (Admin Navigation) -->
    @include('admin.navigation')
 <!-- Page Heading -->
 @isset($header)
                <header class="bg-white dark:bg-gray-800 transition-colors duration-300">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto">
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        

                <!-- Page Content -->
                <div class="transition-colors duration-300">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    @if(isset($scripts))
        {{ $scripts }}
    @endif
    
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

        // Listen for admin logout notifications
        @auth('admin')
        const adminId = '{{ Auth::guard('admin')->id() }}';
        const channel = pusher.subscribe('admin-session.' + adminId);
        
        channel.bind('admin.logged.out', function(data) {
            console.log('Admin logout notification received:', data);
            
            // Get current session ID to compare with the conflicting session
            const currentSessionId = '{{ session()->getId() }}';
            
            // Only show conflict message if reason is 'session_conflict'
            // AND this is the OLD session (conflicting_session_id matches current session)
            // This prevents the NEW device from showing its own logout notification
            if (data.reason === 'session_conflict') {
                // Check if this notification is for THIS session (the old one being logged out)
                // If conflicting_session_id matches current session, this device is being logged out
                const isThisSession = data.metadata && data.metadata.conflicting_session_id === currentSessionId;
                const isThisSessionAlt = data.conflicting_session_id === currentSessionId;
                
                if (isThisSession || isThisSessionAlt) {
                    // This is the OLD session - show logout message
                    // Prevent duplicate notifications by checking if already shown
                    if (window.adminLogoutNotificationShown) {
                        console.log('Admin logout notification already shown, ignoring duplicate');
                        return;
                    }
                    window.adminLogoutNotificationShown = true;
                    
                    Swal.fire({
                        title: 'Logged Out',
                        text: 'You have been logged out because you logged in from another device. This session is no longer valid.',
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
                        // Clear any remaining session data and redirect
                        window.adminLogoutNotificationShown = false;
                        window.location.href = '{{ route('admin.login') }}';
                    });
                } else {
                    // This is the NEW session - ignore the notification
                    // The new device should not see this message
                    console.log('Admin logout notification received but ignored (this is the new session)');
                }
            } else {
                // For manual logout, only redirect if this session matches
                // Check if we should redirect (usually manual logout affects all sessions)
                if (data.metadata && data.metadata.logout_type === 'manual') {
                    // Manual logout from another device - silently redirect
                    window.location.href = '{{ route('admin.login') }}';
                }
            }
        });
        @endauth
    </script>
    
    <!-- Debug Mode Toggle Script -->
    <script>
        // Load debug mode status
        async function loadDebugStatus() {
            try {
                const response = await fetch('/admin/debug/status');
                // Check if response is OK (not 404)
                if (!response.ok) {
                    // Route doesn't exist - fail silently
                    return;
                }
                // Check content type before parsing JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    // Not JSON - probably HTML error page - fail silently
                    return;
                }
                const data = await response.json();
                updateDebugToggle(data.enabled);
            } catch (error) {
                // Silently fail if debug endpoint doesn't exist
                // Only log if it's a real error (not JSON parse error from 404)
                if (error instanceof SyntaxError && error.message.includes('JSON')) {
                    // JSON parse error from HTML 404 page - ignore
                    return;
                }
                // Only log unexpected errors
                if (error.message && !error.message.includes('404') && !error.message.includes('Failed to fetch')) {
                    console.warn('Debug status endpoint not available:', error.message);
                }
            }
        }
        
        // Toggle debug mode
        async function toggleDebugMode() {
            const toggle = document.getElementById('debug-toggle');
            const status = document.getElementById('debug-status');
            const currentState = toggle.classList.contains('bg-qricket-600');
            
            try {
                const response = await fetch('/admin/debug/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ enabled: !currentState })
                });
                
                // Check if response is OK
                if (!response.ok) {
                    // Route doesn't exist - fail silently
                    return;
                }
                
                // Check content type before parsing JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    // Not JSON - probably HTML error page - fail silently
                    return;
                }
                
                const data = await response.json();
                
                if (data.success) {
                    updateDebugToggle(data.enabled);
                    
                    Swal.fire({
                        title: data.enabled ? 'Debug Mode Enabled' : 'Debug Mode Disabled',
                        text: data.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            } catch (error) {
                // Silently fail if debug endpoint doesn't exist
                if (error instanceof SyntaxError && error.message.includes('JSON')) {
                    // JSON parse error from HTML 404 page - ignore silently
                    return;
                }
                if (error.message && (error.message.includes('404') || error.message.includes('Failed to fetch'))) {
                    // Network/404 error - fail silently
                    return;
                }
                // Only log/show unexpected errors
                console.warn('Error toggling debug mode:', error);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Error',
                        text: 'Debug mode toggle not available',
                        icon: 'warning',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            }
        }
        
        // Update toggle visual state
        function updateDebugToggle(enabled) {
            const toggle = document.getElementById('debug-toggle');
            const span = toggle.querySelector('span');
            const status = document.getElementById('debug-status');
            
            if (enabled) {
                toggle.classList.add('bg-qricket-600');
                toggle.classList.remove('bg-gray-200');
                span.classList.add('translate-x-5');
                span.classList.remove('translate-x-0');
                status.textContent = 'Recording all console logs';
            } else {
                toggle.classList.add('bg-gray-200');
                toggle.classList.remove('bg-qricket-600');
                span.classList.add('translate-x-0');
                span.classList.remove('translate-x-5');
                status.textContent = 'Console logs not recorded';
            }
        }
        
        // Load status on page load
        document.addEventListener('DOMContentLoaded', loadDebugStatus);
    </script>
</body>
</html>




