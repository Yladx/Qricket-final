/**
 * Real-time Notification Handler
 * 
 * Listens for real-time notifications from the backend and displays them to users.
 * Uses SweetAlert2 for consistent UI notifications.
 */

// Wait for DOM to be ready and Pusher config to be available
(function waitForConfig() {
    // Debug logging
    console.log('[Notifications] Checking config...', {
        pusherAvailable: typeof Pusher !== 'undefined',
        pusherKey: window.PUSHER_APP_KEY ? 'SET (' + window.PUSHER_APP_KEY.length + ' chars)' : 'NOT SET',
        readyState: document.readyState
    });
    
    // Check if Pusher is available
    if (typeof Pusher === 'undefined') {
        // If Pusher library not loaded yet, wait a bit longer
        if (document.readyState === 'loading') {
            setTimeout(waitForConfig, 100);
            return;
        }
        console.warn('Pusher is not loaded. Real-time notifications will not work.');
        return;
    }

    // Check if user is authenticated
    if (typeof window.USER_ID === 'undefined') {
        // If DOM not ready, wait
        if (document.readyState === 'loading') {
            setTimeout(waitForConfig, 100);
            return;
        }
        console.log('User not authenticated. Skipping notification setup.');
        return;
    }

    // Check if Pusher config is available - poll until it's ready or timeout
    const pusherKey = window.PUSHER_APP_KEY;
    const hasKey = pusherKey && pusherKey !== '' && pusherKey !== 'undefined';
    
    if (!hasKey) {
        // Check if we've been waiting too long (10 seconds max)
        if (!window.__pusherConfigWaitStart) {
            window.__pusherConfigWaitStart = Date.now();
        }
        
        const waitTime = Date.now() - window.__pusherConfigWaitStart;
        if (waitTime > 10000) {
            // Timeout after 10 seconds - only warn if it's actually missing
            console.warn('Pusher key not configured. Please ensure PUSHER_APP_KEY is set in your .env file and run: php artisan config:clear');
            delete window.__pusherConfigWaitStart;
            return;
        }
        
        // Wait a bit more and check again
        setTimeout(waitForConfig, 50);
        return;
    }
    
    // Config is ready, clean up wait tracker
    delete window.__pusherConfigWaitStart;
    
    // Wait for DOM to be ready before initializing
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeNotifications);
    } else {
        initializeNotifications();
    }
})();

// Initialize notifications - called after config is ready and DOM is loaded
function initializeNotifications() {
        // Check again if Pusher key is configured
        if (!window.PUSHER_APP_KEY || window.PUSHER_APP_KEY === '') {
            return;
        }

        // Initialize Pusher (Pusher Cloud only)
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const config = {
            cluster: window.PUSHER_APP_CLUSTER || 'mt1',
            forceTLS: true,
            enableTransports: ['ws','wss','xhr_streaming','xhr_polling'],
            disableStats: true,
            authEndpoint: window.PUSHER_AUTH_ENDPOINT || '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': csrf
                }
            }
        };

        const pusher = new Pusher(window.PUSHER_APP_KEY, config);

        // Subscribe to user's private channel
        let channel;
        try {
            channel = pusher.subscribe('private-user.' + window.USER_ID);
        } catch (e) {
            console.warn('Pusher subscribe error (suppressed):', e?.message || e);
            return;
        }

        // Listen for user notifications
        channel.bind('user.notification', function(data) {
            console.log('🔔 Received notification:', data);
            
            // Update notification badge count
            if (window.notificationBadgeManager) {
                window.notificationBadgeManager.incrementBadge();
            }
            
            // Dispatch custom event for other components
            document.dispatchEvent(new CustomEvent('notification-received', {
                detail: data
            }));
            
            // Display notification based on type
            const title = data.title || 'Notification';
            const message = data.message || 'You have a new notification';
            
            // Use fallback notification with proper styling
            showFallbackNotification(data.type, title, message);
        });

        // Handle connection events
        pusher.connection.bind('connected', function() {
            console.log('Pusher connected successfully');
        });

        pusher.connection.bind('disconnected', function() {
            console.warn('Pusher disconnected');
        });

        // Debounced, downgraded error logging
        let __lastPusherErr = 0;
        pusher.connection.bind('error', function(error) {
            const now = Date.now();
            if (now - __lastPusherErr > 5000) {
                console.warn('Pusher connection warning:', error?.type || error);
                __lastPusherErr = now;
            }
        });

        channel.bind('pusher:subscription_error', function(status) {
            console.warn('Pusher subscription error:', status);
        });
        
        pusher.connection.bind('unavailable', function() {
            console.warn('Pusher temporarily unavailable; will resume when back online.');
        });

        // Clean up on page unload
        window.addEventListener('beforeunload', function() {
            pusher.disconnect();
        });
    }


// Fallback notification function
function showFallbackNotification(type, title, message) {
    console.log('Using fallback notification:', { type, title, message });
    
    // Try to use SweetAlert if available
    if (typeof Swal !== 'undefined') {
        // Check if dark mode is enabled
        const isDark = document.documentElement.classList.contains('dark') || 
                      (localStorage.getItem('darkMode') === 'true') ||
                      window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        Swal.fire({
            title: title,
            text: message,
            timer: 4000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end',
            width: '320px',
            padding: '0.75rem',
            background: isDark ? '#1f2937' : '#ffffff',
            color: isDark ? '#e5e7eb' : '#111827',
            customClass: {
                popup: 'notification-toast',
                title: 'notification-title',
                content: 'notification-content'
            }
        });
                        } else {
        // Ultimate fallback - use browser notification
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(title, {
                body: message,
                icon: '/favicon.ico'
            });
        } else {
            // Last resort - SweetAlert or alert
            if (typeof Swal !== 'undefined' && window.Swal) {
                Swal.fire({
                    icon: 'info',
                    title: title,
                    text: message,
                    timer: 5000,
                    showConfirmButton: false
                });
            } else {
                alert(`${title}: ${message}`);
            }
        }
    }
}

// Test function to manually trigger a notification
window.testNotification = function() {
    console.log('🧪 Testing notification system...');
    
    // Simulate a notification
    const testData = {
        type: 'info',
        title: 'Support Ticket Reply',
        message: 'An admin has replied to your support ticket #TKT-123456'
    };
    
    // Use the fallback notification system
    showFallbackNotification(testData.type, testData.title, testData.message);
};

// Test function for support ticket notification
window.testSupportNotification = function() {
    console.log('🧪 Testing support notification...');
    
    const testData = {
        type: 'info',
        title: 'Support Ticket Reply',
        message: 'An admin has replied to your support ticket #TKT-68F9992D6B29A'
    };
    
    showFallbackNotification(testData.type, testData.title, testData.message);
};
