<x-app-layout title="Notifications" page-title="Notifications">
    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Notifications</h2>
                    <div class="flex items-center space-x-2">
                        <button id="refresh-notifications" class="text-sm px-3 py-1.5 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200" title="Refresh notifications">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </button>
                    <button id="mark-all-read" class="text-sm px-3 py-1.5 rounded-md bg-qricket-100 dark:bg-qricket-900 text-qricket-700 dark:text-qricket-300 hover:bg-qricket-200 dark:hover:bg-qricket-800">Mark All as Read</button>
                    </div>
                </div>
                <div id="notifications-container" class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($notifications as $n)
                        @php($href = route('notifications.go', $n))
                        <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer notification-item" data-id="{{ $n->id }}" data-read="{{ $n->is_read ? 'true' : 'false' }}" data-url="{{ $href }}">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="flex items-center space-x-2">
                                        @if(!$n->is_read)
                                            <span class="inline-block w-2 h-2 rounded-full bg-red-500 unread-dot"></span>
                                        @endif
                                        <div class="text-sm text-gray-900 dark:text-gray-100 font-medium">{{ $n->title ?? ucfirst($n->type) }}</div>
                                        @if($n->type === 'event' && $n->action === 'news')
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                            </svg>
                                        @elseif($n->type === 'ticket' && in_array($n->action, ['share_request', 'share_accepted', 'share_declined']))
                                            @if($n->action === 'share_request')
                                                <svg class="w-4 h-4 text-qricket-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                                                </svg>
                                            @elseif($n->action === 'share_accepted')
                                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            @elseif($n->action === 'share_declined')
                                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            @endif
                                        @elseif($n->type === 'ticket' && $n->action === 'reply')
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                            </svg>
                                        @elseif($n->type === 'organizer')
                                            @if($n->action === 'accept' || $n->action === 'verified')
                                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            @elseif($n->action === 'reject')
                                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4 text-qricket-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                            @endif
                                        @elseif($n->type === 'event')
                                            @if($n->action === 'accept')
                                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            @elseif($n->action === 'reject')
                                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            @elseif($n->action === 'news')
                                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                                </svg>
                                            @elseif($n->action === 'event_ended')
                                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            @elseif($n->action === 'feedback_request')
                                                <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                                </svg>
                                            @elseif($n->action === 'staff_added')
                                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4 text-qricket-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ $n->message }}</div>
                                    <div class="flex items-center justify-between mt-1">
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $n->created_at->diffForHumans() }}</div>
                                        @if($n->type === 'event' && $n->action === 'news')
                                            <div class="text-xs text-blue-500 dark:text-blue-400 font-medium">Click to view news →</div>
                                        @elseif($n->type === 'ticket' && in_array($n->action, ['share_request', 'share_accepted', 'share_declined']))
                                            <div class="text-xs text-qricket-500 dark:text-qricket-400 font-medium">Click to view tickets →</div>
                                        @elseif($n->type === 'event' && $n->action === 'event_ended')
                                            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Click to view event →</div>
                                        @elseif($n->type === 'event' && $n->action === 'feedback_request')
                                            <div class="text-xs text-yellow-500 dark:text-yellow-400 font-medium">Click to give feedback →</div>
                                        @elseif($n->type === 'event' && $n->action === 'staff_added')
                                            <div class="text-xs text-green-500 dark:text-green-400 font-medium">Click to manage event →</div>
                                        @endif
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $n->is_read ? 'bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' }}">
                                    {{ $n->is_read ? 'Read' : 'New' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">No notifications yet.</div>
                    @endforelse
                </div>
                @if($notifications->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const markAllReadBtn = document.getElementById('mark-all-read');
    const notificationItems = document.querySelectorAll('.notification-item');

    // Handle individual notification clicks
    notificationItems.forEach(item => {
        item.addEventListener('click', function() {
            const notificationId = this.getAttribute('data-id');
            const isRead = this.getAttribute('data-read') === 'true';
            const url = this.getAttribute('data-url');

            if (!isRead) {
                // Mark as read immediately (optimistic update)
                markAsRead(this);
                
                // Send server request
                fetch(`/notifications/${notificationId}/mark-read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        console.error('Failed to mark notification as read:', data.error);
                        // Revert UI changes if server request failed
                        revertMarkAsRead(this);
                    } else {
                        // Dispatch event to update notification badge
                        document.dispatchEvent(new CustomEvent('notification-read'));
                    }
                })
                .catch(error => {
                    console.error('Error marking notification as read:', error);
                    // Revert UI changes if request failed
                    revertMarkAsRead(this);
                });
            }

            // Navigate to the notification URL
            window.location.href = url;
        });
    });

    // Handle mark all as read button
    markAllReadBtn.addEventListener('click', function() {
        const unreadItems = document.querySelectorAll('.notification-item[data-read="false"]');
        
        if (unreadItems.length === 0) {
            return; // No unread notifications
        }

        // Update UI immediately
        unreadItems.forEach(item => {
            markAsRead(item);
        });

        // Send server request
        fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Failed to mark all notifications as read:', data.error);
                // Revert UI changes if server request failed
                unreadItems.forEach(item => {
                    revertMarkAsRead(item);
                });
            } else {
                // Dispatch event to update notification badge
                document.dispatchEvent(new CustomEvent('notification-read'));
            }
        })
        .catch(error => {
            console.error('Error marking all notifications as read:', error);
            // Revert UI changes if request failed
            unreadItems.forEach(item => {
                revertMarkAsRead(item);
            });
        });
    });

    function markAsRead(item) {
        item.setAttribute('data-read', 'true');
        item.classList.add('opacity-60');
        
        // Remove unread dot
        const unreadDot = item.querySelector('.unread-dot');
        if (unreadDot) {
            unreadDot.remove();
        }
        
        // Update status badge
        const statusBadge = item.querySelector('.inline-flex.items-center');
        if (statusBadge) {
            statusBadge.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300';
            statusBadge.textContent = 'Read';
        }
        
        // Immediately update notification badge count
        if (window.notificationBadgeManager) {
            window.notificationBadgeManager.decrementBadge();
        }
    }

    function revertMarkAsRead(item) {
        item.setAttribute('data-read', 'false');
        item.classList.remove('opacity-60');
        
        // Re-add unread dot
        const titleDiv = item.querySelector('.flex.items-center.space-x-2');
        if (titleDiv) {
            titleDiv.insertAdjacentHTML('afterbegin', '<span class="inline-block w-2 h-2 rounded-full bg-red-500 unread-dot"></span>');
        }
        
        // Update status badge
        const statusBadge = item.querySelector('.inline-flex.items-center');
        if (statusBadge) {
            statusBadge.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
            statusBadge.textContent = 'New';
        }
        
        // Revert notification badge count
        if (window.notificationBadgeManager) {
            window.notificationBadgeManager.incrementBadge();
        }
    }
});

// Real-time notification tracking
document.addEventListener('DOMContentLoaded', function() {
    // Check if Pusher is available and user is authenticated
    if (typeof Pusher !== 'undefined' && typeof window.USER_ID !== 'undefined') {
        // Initialize Pusher
        const pusher = new Pusher(window.PUSHER_APP_KEY || 'your-pusher-key', {
            cluster: window.PUSHER_APP_CLUSTER || 'mt1',
            encrypted: true,
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            }
        });

        // Subscribe to user's private channel
        const channel = pusher.subscribe('private-user.' + window.USER_ID);

        // Listen for new notifications
        channel.bind('user.notification', function(data) {
            console.log('🔔 Real-time notification received on notifications page:', data);
            
            // Add new notification to the list
            addNotificationToList(data);
            
            // Update notification badge
            if (window.notificationBadgeManager) {
                window.notificationBadgeManager.incrementBadge();
            }
        });

        // Handle connection events
        pusher.connection.bind('connected', function() {
            console.log('✅ Pusher connected on notifications page');
        });

        pusher.connection.bind('disconnected', function() {
            console.log('❌ Pusher disconnected on notifications page');
        });

        // Clean up on page unload
        window.addEventListener('beforeunload', function() {
            pusher.disconnect();
        });
    }

    // Refresh button functionality
    const refreshBtn = document.getElementById('refresh-notifications');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            refreshNotifications();
        });
    }
});

// Function to add new notification to the list
function addNotificationToList(data) {
    const container = document.getElementById('notifications-container');
    if (!container) return;

    // Create notification HTML
    const notificationHtml = createNotificationHTML(data);
    
    // Add to top of list
    container.insertAdjacentHTML('afterbegin', notificationHtml);
    
    // Re-attach event listeners to the new notification
    const newNotification = container.querySelector('.notification-item');
    if (newNotification) {
        attachNotificationListeners(newNotification);
    }
    
    // Show a subtle animation
    if (newNotification) {
        newNotification.style.opacity = '0';
        newNotification.style.transform = 'translateY(-10px)';
        setTimeout(() => {
            newNotification.style.transition = 'all 0.3s ease';
            newNotification.style.opacity = '1';
            newNotification.style.transform = 'translateY(0)';
        }, 100);
    }
}

// Function to create notification HTML
function createNotificationHTML(data) {
    const title = data.title || 'Notification';
    const message = data.message || 'You have a new notification';
    const timestamp = 'Just now';
    const notificationId = 'new-' + Date.now();
    
    return `
        <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer notification-item" data-id="${notificationId}" data-read="false" data-url="#">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="inline-block w-2 h-2 rounded-full bg-red-500 unread-dot"></span>
                        <div class="text-sm text-gray-900 dark:text-gray-100 font-medium">${title}</div>
                        ${getNotificationIcon(data.type, data.action)}
                    </div>
                    <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">${message}</div>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400">${timestamp}</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">New</span>
                </div>
            </div>
        </div>
    `;
}

// Function to get notification icon based on type and action
function getNotificationIcon(type, action) {
    if (type === 'support') {
        return `<svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
        </svg>`;
    }
    return '';
}

// Function to refresh notifications
async function refreshNotifications() {
    const refreshBtn = document.getElementById('refresh-notifications');
    const originalContent = refreshBtn.innerHTML;
    
    // Show loading state
    refreshBtn.innerHTML = `
        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
    `;
    refreshBtn.disabled = true;
    
    try {
        // Reload the page to get fresh notifications
        window.location.reload();
    } catch (error) {
        console.error('Error refreshing notifications:', error);
        // Restore button state
        refreshBtn.innerHTML = originalContent;
        refreshBtn.disabled = false;
    }
}

// Function to attach event listeners to notification items
function attachNotificationListeners(item) {
    item.addEventListener('click', function() {
        const notificationId = this.getAttribute('data-id');
        const isRead = this.getAttribute('data-read') === 'true';
        const url = this.getAttribute('data-url');

        if (!isRead) {
            // Mark as read immediately (optimistic update)
            markAsRead(this);
            
            // Send server request
            fetch(`/notifications/${notificationId}/mark-read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('Failed to mark notification as read:', data.error);
                    // Revert UI changes if server request failed
                    revertMarkAsRead(this);
                } else {
                    // Dispatch event to update notification badge
                    document.dispatchEvent(new CustomEvent('notification-read'));
                }
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
                // Revert UI changes if request failed
                revertMarkAsRead(this);
            });
        }

        // Navigate to the notification URL
        if (url && url !== '#') {
            window.location.href = url;
        }
    });
}

// Pass user ID to JavaScript
window.USER_ID = {{ Auth::id() }};
</script>