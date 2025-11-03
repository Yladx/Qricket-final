/**
 * Admin Notifications Real-time Handler
 * 
 * Handles Pusher subscriptions for admin notifications and updates badge counts
 * Works globally across all admin pages
 */

(function() {
    'use strict';

    // Wait for DOM and Pusher config to be ready
    function waitForConfig(callback, maxAttempts = 50, attempt = 0) {
        if (attempt >= maxAttempts) {
            console.warn('[Admin Notifications] Timeout waiting for Pusher config');
            return;
        }

        const pusherAvailable = typeof Pusher !== 'undefined';
        const pusherKey = window.PUSHER_APP_KEY;
        const hasKey = pusherKey && pusherKey !== '' && pusherKey !== 'undefined';

        if (pusherAvailable && hasKey && (document.readyState === 'complete' || document.readyState === 'interactive')) {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', callback);
            } else {
                callback();
            }
        } else {
            setTimeout(() => waitForConfig(callback, maxAttempts, attempt + 1), 100);
        }
    }

    // Update admin notification badge count
    window.updateAdminNotificationBadge = function() {
        fetch('/admin/notifications/data', {
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to fetch notifications');
            return response.json();
        })
        .then(data => {
            if (data.notifications && Array.isArray(data.notifications)) {
                const unreadCount = data.notifications.filter(n => !n.is_read).length;
                
                // Update all badge elements
                const badges = document.querySelectorAll('.notification-badge');
                badges.forEach(badge => {
                    if (unreadCount > 0) {
                        badge.textContent = String(unreadCount > 99 ? '99+' : unreadCount);
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                });
            }
        })
        .catch(error => {
            // Silently fail - badge update is not critical
            console.debug('[Admin Notifications] Badge update failed (non-critical):', error);
        });
    };

    // Initialize Pusher for admin notifications
    function initializeAdminNotifications() {
        // Check if Pusher is available
        if (typeof Pusher === 'undefined') {
            console.warn('[Admin Notifications] Pusher is not loaded. Real-time notifications will not work.');
            return;
        }

        // Check if Pusher config is available
        const pusherKey = window.PUSHER_APP_KEY;
        if (!pusherKey || pusherKey === '' || pusherKey === 'undefined') {
            console.warn('[Admin Notifications] Pusher key not configured.');
            return;
        }

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const cluster = window.PUSHER_APP_CLUSTER || 'mt1';
        const authEndpoint = window.PUSHER_AUTH_ENDPOINT || '/admin/broadcasting/auth';
        
        // Initialize Pusher
        const pusher = new Pusher(pusherKey, {
            cluster: cluster,
            forceTLS: true,
            enableTransports: ['ws', 'wss', 'xhr_streaming', 'xhr_polling'],
            disableStats: true,
            authEndpoint: authEndpoint,
            auth: {
                headers: {
                    'X-CSRF-TOKEN': csrf
                }
            }
        });

        // Subscribe to admin notifications channel
        // Laravel automatically prefixes PrivateChannel with 'private-', so we use 'admin.notifications'
        let channel;
        try {
            channel = pusher.subscribe('private-admin.notifications');
            console.log('[Admin Notifications] ✅ Subscribed to private-admin.notifications channel');
        } catch (e) {
            console.warn('[Admin Notifications] Failed to subscribe:', e?.message || e);
            return;
        }

        // Listen for notification.sent events
        channel.bind('notification.sent', function(data) {
            console.log('[Admin Notifications] 🔔 Notification received:', data);
            
            // Update badge count immediately
            updateAdminNotificationBadge();

            // Create notification data
            const notificationId = data.id || Date.now();
            const notificationTitle = data.title || 'Admin Notification';
            const notificationMessage = data.message || '';
            const notificationType = data.type || 'info';
            const receivedAt = data.received_at || new Date().toISOString();

            // Dispatch custom event for other components (like notifications page)
            window.dispatchEvent(new CustomEvent('admin-notification:received', {
                detail: {
                    id: notificationId,
                    title: notificationTitle,
                    message: notificationMessage,
                    type: notificationType,
                    receivedAt: receivedAt,
                    data: data
                }
            }));

            // Show desktop notification if permission is granted
            if ('Notification' in window && Notification.permission === 'granted') {
                try {
                    new Notification(notificationTitle, {
                        body: notificationMessage,
                        icon: '/favicon.ico',
                        tag: 'admin-notification',
                        requireInteraction: false
                    });
                } catch (e) {
                    console.warn('[Admin Notifications] Failed to show desktop notification:', e);
                }
            }

            // Show toast notification if SweetAlert2 is available
            if (typeof Swal !== 'undefined') {
                const isDark = document.documentElement.classList.contains('dark') || 
                              (localStorage.getItem('darkMode') === 'true') ||
                              window.matchMedia('(prefers-color-scheme: dark)').matches;
                
                Swal.fire({
                    title: notificationTitle,
                    text: notificationMessage,
                    timer: 4000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                    width: '320px',
                    padding: '0.75rem',
                    background: isDark ? '#1f2937' : '#ffffff',
                    color: isDark ? '#e5e7eb' : '#111827',
                    icon: notificationType === 'error' ? 'error' : 
                          notificationType === 'success' ? 'success' : 
                          notificationType === 'warning' ? 'warning' : 'info',
                    customClass: {
                        popup: 'notification-toast',
                        title: 'notification-title',
                        content: 'notification-content'
                    }
                });
            }
        });

        // Handle connection events
        pusher.connection.bind('connected', function() {
            console.log('[Admin Notifications] ✅ Pusher connected');
        });

        pusher.connection.bind('disconnected', function() {
            console.warn('[Admin Notifications] ⚠️ Pusher disconnected');
        });

        pusher.connection.bind('error', function(error) {
            console.warn('[Admin Notifications] ⚠️ Pusher connection error:', error?.type || error);
        });

        channel.bind('pusher:subscription_error', function(status) {
            console.warn('[Admin Notifications] ⚠️ Subscription error:', status);
        });

        channel.bind('pusher:subscription_succeeded', function() {
            console.log('[Admin Notifications] ✅ Successfully subscribed to admin notifications');
        });

        // Store pusher instance globally for cleanup
        window.adminNotificationsPusher = pusher;
    }

    // Initialize when ready
    waitForConfig(function() {
        // Load badge count on page load
        if (typeof updateAdminNotificationBadge === 'function') {
            updateAdminNotificationBadge();
        }

        // Initialize Pusher subscription
        initializeAdminNotifications();
    });

    // Update badge periodically (fallback if Pusher fails)
    setInterval(function() {
        if (typeof updateAdminNotificationBadge === 'function') {
            updateAdminNotificationBadge();
        }
    }, 30000); // Every 30 seconds

    // Clean up Pusher connection on page unload
    window.addEventListener('beforeunload', function() {
        if (window.adminNotificationsPusher) {
            window.adminNotificationsPusher.disconnect();
        }
    });
})();

