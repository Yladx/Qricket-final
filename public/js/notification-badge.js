/**
 * Notification Badge Manager
 * 
 * Handles updating the notification badge count and showing/hiding the red dot
 * for unread notifications in the navigation.
 */

class NotificationBadgeManager {
    constructor() {
        this.badges = document.querySelectorAll('.notification-badge');
        this.updateInterval = null;
        this.init();
    }

    init() {
        // Update badge count on page load
        this.updateBadgeCount();
        
        // Set up periodic updates (every 30 seconds)
        this.updateInterval = setInterval(() => {
            this.updateBadgeCount();
        }, 30000);

        // Listen for real-time notifications to update badge
        this.setupRealtimeUpdates();
    }

    async updateBadgeCount() {
        try {
            const response = await fetch('/notifications/unread-count', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.setBadgeCount(data.count);
            }
        } catch (error) {
            console.error('Error fetching notification count:', error);
        }
    }

    setBadgeCount(count) {
        this.badges.forEach(badge => {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count.toString();
                badge.classList.remove('hidden');
                badge.classList.add('inline-flex');
            } else {
                badge.classList.add('hidden');
                badge.classList.remove('inline-flex');
            }
        });
    }

    setupRealtimeUpdates() {
        // Listen for custom events when notifications are received
        document.addEventListener('notification-received', () => {
            this.updateBadgeCount();
        });

        // Listen for custom events when notifications are read
        document.addEventListener('notification-read', () => {
            this.updateBadgeCount();
        });
    }

    // Method to manually increment badge count (for immediate feedback)
    incrementBadge() {
        this.badges.forEach(badge => {
            const currentCount = parseInt(badge.textContent) || 0;
            const newCount = currentCount + 1;
            badge.textContent = newCount > 99 ? '99+' : newCount.toString();
            badge.classList.remove('hidden');
            badge.classList.add('inline-flex');
        });
    }

    // Method to manually decrement badge count
    decrementBadge() {
        this.badges.forEach(badge => {
            const currentCount = parseInt(badge.textContent) || 0;
            const newCount = Math.max(0, currentCount - 1);
            
            if (newCount === 0) {
                badge.classList.add('hidden');
                badge.classList.remove('inline-flex');
            } else {
                badge.textContent = newCount > 99 ? '99+' : newCount.toString();
            }
        });
    }

    // Clean up when page unloads
    destroy() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
        }
    }
}

// Initialize notification badge manager when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if user is authenticated
    if (typeof window.USER_ID !== 'undefined') {
        window.notificationBadgeManager = new NotificationBadgeManager();
    }
});

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    if (window.notificationBadgeManager) {
        window.notificationBadgeManager.destroy();
    }
});
