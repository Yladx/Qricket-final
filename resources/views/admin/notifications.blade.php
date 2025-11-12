<x-admin-layout>
    <x-slot name="title">Notifications - Qrickett Admin</x-slot>
    <x-slot name="page-title">Admin Notifications</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Admin Notifications</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Manage and view system notifications for administrators.</p>
            </div>
            <div class="flex items-center space-x-4">
                <button id="request-permission" class="text-sm px-3 py-2 rounded-md bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-800">Enable Desktop Notifications</button>
                <button id="mark-all-read" class="text-sm px-3 py-2 rounded-md bg-qricket-100 dark:bg-qricket-900 text-qricket-700 dark:text-qricket-300 hover:bg-qricket-200 dark:hover:bg-qricket-800">Mark All as Read</button>
            </div>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <ul id="admin-notifications-list" class="divide-y divide-gray-200 dark:divide-gray-700">
            <li class="text-sm text-gray-500 dark:text-gray-400 py-3">Waiting for notifications...</li>
        </ul>
    </div>

    <x-slot name="scripts">
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const list = document.getElementById('admin-notifications-list');
            const markAllReadBtn = document.getElementById('mark-all-read');
            const requestPermissionBtn = document.getElementById('request-permission');

            // Load existing notifications on page load
            loadExistingNotifications();

            // Request desktop notification permission
            requestPermissionBtn.addEventListener('click', function() {
                if ('Notification' in window) {
                    Notification.requestPermission().then(function(permission) {
                        if (permission === 'granted') {
                            requestPermissionBtn.textContent = 'Desktop Notifications Enabled';
                            requestPermissionBtn.classList.remove('bg-blue-100', 'dark:bg-blue-900', 'text-blue-700', 'dark:text-blue-300');
                            requestPermissionBtn.classList.add('bg-green-100', 'dark:bg-green-900', 'text-green-700', 'dark:text-green-300');
                        } else {
                            alert('Desktop notifications are blocked. Please enable them in your browser settings.');
                        }
                    });
                } else {
                    alert('This browser does not support desktop notifications.');
                }
            });

            function loadExistingNotifications() {
                console.log('Loading existing notifications...');
                console.log('List element:', list);
                
                // Check if list element exists
                if (!list) {
                    console.error('List element not found!');
                    return;
                }
                
                // Fetch existing admin notifications from the database
                fetch('/admin/notifications/data', { 
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                })
                    .then(response => {
                        console.log('Response status:', response.status);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Received data:', data);
                        if (data.notifications && data.notifications.length > 0) {
                            list.innerHTML = '';
                            data.notifications.forEach(notification => {
                                console.log('Adding notification:', notification);
                                prependItem(
                                    notification.title,
                                    notification.message,
                                    notification.type || 'info',
                                    notification.created_at,
                                    notification.is_read,
                                    notification.id,
                                    notification.action || null,
                                    notification.reference_id || null,
                                    notification.data || null
                                );
                            });
                            updateUnreadCount();
                        } else {
                            list.innerHTML = '<li class="text-sm text-gray-500 dark:text-gray-400 py-3">No notifications yet.</li>';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading notifications:', error);
                        list.innerHTML = '<li class="text-sm text-gray-500 dark:text-gray-400 py-3">Error loading notifications: ' + error.message + '</li>';
                    });
            }

            function prependItem(title, message, type, timestamp, isRead = false, notificationId = null, action = null, referenceId = null, notificationData = null) {
                console.log('prependItem called with:', { title, message, type, timestamp, isRead, notificationId, action, referenceId, notificationData });
                
                // Check if list element exists
                if (!list) {
                    console.error('List element not found in prependItem!');
                    return;
                }
                
                // Generate route URL based on notification type and action
                const notificationUrl = generateNotificationUrl(type, action, referenceId, notificationId, notificationData);
                const isClickable = notificationUrl !== null;
                const cursorClass = isClickable ? 'cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50' : '';
                
                const li = document.createElement('li');
                li.className = `py-3 ${cursorClass}`;
                li.setAttribute('data-read', isRead);
                li.setAttribute('data-id', notificationId || Date.now());
                if (isClickable) {
                    li.setAttribute('data-url', notificationUrl);
                }
                const timeAgo = timestamp ? new Date(timestamp).toLocaleString() : 'Just now';
                const readClass = isRead ? 'opacity-60' : '';
                const unreadDot = !isRead ? '<div class="w-2 h-2 bg-red-500 rounded-full mt-1"></div>' : '';
                const clickHint = isClickable ? '<div class="text-xs text-blue-500 dark:text-blue-400 mt-1 font-medium">Click to view →</div>' : '';
                
                li.innerHTML = `
                    <div class="flex items-start justify-between ${readClass}">
                        <div class="flex-1">
                            <div class="flex items-start gap-2">
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">${title || 'Notification'}</div>
                                    <div class="text-sm text-gray-700 dark:text-gray-300">${message || ''}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">${timeAgo}</div>
                                    ${clickHint}
                                </div>
                                ${unreadDot}
                            </div>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full ${type === 'success' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : type === 'error' ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' : type === 'warning' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300' : type === 'refund' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300' : type === 'payout' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'}">${type || 'info'}</span>
                    </div>
                `;
                
                // Add click handler
                if (isClickable) {
                    li.addEventListener('click', function() {
                        markAsRead(li);
                        // Navigate to the notification URL
                        window.location.href = notificationUrl;
                    });
                } else {
                    // Just mark as read if not clickable
                    li.addEventListener('click', function() {
                        markAsRead(li);
                    });
                }
                
                // Safely check and remove existing placeholder messages
                if (list.firstElementChild) {
                    const firstChildText = list.firstElementChild.textContent || '';
                    if (firstChildText.includes('Waiting') || firstChildText.includes('No notifications') || firstChildText.includes('Error loading')) {
                        list.firstElementChild.remove();
                    }
                }
                
                list.prepend(li);
                console.log('Notification added to list');
            }

            function generateNotificationUrl(type, action, referenceId, notificationId, data = null) {
                if (!notificationId) return null;

                const normalizedType = (type || '').toString().toLowerCase();
                const normalizedAction = (action || '').toString().toLowerCase();
                const info = data || {};
                const fallbackUrl = `/admin/notifications/${notificationId}/go`;

                const hasRefundFlag = () => {
                    return !!(info && (info.refund_request_id || info.type === 'refund' || ['refund_requested', 'refund_completed', 'refund_rejected'].includes(normalizedAction)));
                };

                if (['payout'].includes(normalizedType)) {
                    return '/admin/payouts';
                }

                if (['refund'].includes(normalizedType) || hasRefundFlag()) {
                    return '/admin/refunds';
                }

                if (normalizedType === 'organizer') {
                    if (referenceId) {
                        return `/admin/verifications/${referenceId}`;
                    }
                    return '/admin/verifications';
                }

                if (normalizedType === 'support' || normalizedType === 'ticket') {
                    if (hasRefundFlag()) {
                        return '/admin/refunds';
                    }
                    if (referenceId) {
                        return `/admin/support/${referenceId}`;
                    }
                    return '/admin/support';
                }

                if (normalizedType === 'event') {
                    if (hasRefundFlag()) {
                        return '/admin/refunds';
                    }

                    if (['payout_processed'].includes(normalizedAction) || info.payout_request_id) {
                        return '/admin/payouts';
                    }

                    if (referenceId) {
                        if (['accept', 'reject', 'pending', 'published', 'approval_request', 'submitted'].includes(normalizedAction)) {
                            return `/admin/events/approval/${referenceId}`;
                        }
                        return `/admin/event/${referenceId}/manage`;
                    }

                    return '/admin/events/approval';
                }

                if (normalizedType === 'subscription') {
                    return '/admin/subscriptions';
                }

                if (normalizedType === 'system') {
                    return '/admin/dashboard';
                }

                return fallbackUrl;
            }

            function markAsRead(li) {
                const notificationId = li.getAttribute('data-id');
                if (!notificationId || notificationId === 'null') {
                    return;
                }
                
                // Update UI immediately
                li.setAttribute('data-read', 'true');
                li.classList.add('opacity-60');
                const unreadDot = li.querySelector('.bg-red-500');
                if (unreadDot) {
                    unreadDot.remove();
                }
                
                // Update counter
                updateUnreadCount();
                
                // Send server request to persist the change
                if (notificationId) {
                    fetch(`/admin/notifications/${notificationId}/mark-read`, {
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
                            li.setAttribute('data-read', 'false');
                            li.classList.remove('opacity-60');
                            // Re-add the unread dot
                            const statusDiv = li.querySelector('.flex.items-start.gap-2');
                            if (statusDiv) {
                                statusDiv.insertAdjacentHTML('beforeend', '<div class="w-2 h-2 bg-red-500 rounded-full mt-1"></div>');
                            }
                            updateUnreadCount();
                        }
                    })
                    .catch(error => {
                        console.error('Error marking notification as read:', error);
                        // Revert UI changes if request failed
                        li.setAttribute('data-read', 'false');
                        li.classList.remove('opacity-60');
                        const statusDiv = li.querySelector('.flex.items-start.gap-2');
                        if (statusDiv) {
                            statusDiv.insertAdjacentHTML('beforeend', '<div class="w-2 h-2 bg-red-500 rounded-full mt-1"></div>');
                        }
                        updateUnreadCount();
                    });
                }
            }

            function markAllAsRead() {
                // Update UI immediately
                const unreadItems = list.querySelectorAll('li[data-read="false"]');
                unreadItems.forEach(item => {
                    item.setAttribute('data-read', 'true');
                    item.classList.add('opacity-60');
                    const unreadDot = item.querySelector('.bg-red-500');
                    if (unreadDot) {
                        unreadDot.remove();
                    }
                });
                
                // Update counter
                updateUnreadCount();
                
                // Send server request to persist all changes
                fetch('/admin/notifications/mark-all-read', {
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
                        const readItems = list.querySelectorAll('li[data-read="true"]');
                        readItems.forEach(item => {
                            item.setAttribute('data-read', 'false');
                            item.classList.remove('opacity-60');
                            const statusDiv = item.querySelector('.flex.items-start.gap-2');
                            if (statusDiv) {
                                statusDiv.insertAdjacentHTML('beforeend', '<div class="w-2 h-2 bg-red-500 rounded-full mt-1"></div>');
                            }
                        });
                        updateUnreadCount();
                    }
                })
                .catch(error => {
                    console.error('Error marking all notifications as read:', error);
                    // Revert UI changes if request failed
                    const readItems = list.querySelectorAll('li[data-read="true"]');
                    readItems.forEach(item => {
                        item.setAttribute('data-read', 'false');
                        item.classList.remove('opacity-60');
                        const statusDiv = item.querySelector('.flex.items-start.gap-2');
                        if (statusDiv) {
                            statusDiv.insertAdjacentHTML('beforeend', '<div class="w-2 h-2 bg-red-500 rounded-full mt-1"></div>');
                        }
                    });
                    updateUnreadCount();
                });
            }

            function updateUnreadCount() {
                const unreadCount = list.querySelectorAll('li[data-read="false"]').length;
                // Update badges on the page
                const badges = Array.from(document.querySelectorAll('.notification-badge'));
                badges.forEach(badge => {
                    if (unreadCount > 0) {
                        badge.textContent = String(unreadCount > 99 ? '99+' : unreadCount);
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                });
            }

            function showDesktopNotification(title, message) {
                if ('Notification' in window && Notification.permission === 'granted') {
                    new Notification(title, {
                        body: message,
                        icon: '/favicon.ico',
                        tag: 'admin-notification'
                    });
                }
            }

            // Listen for admin-specific notifications
            window.addEventListener('notification:received', (e) => {
                const { title, message, options, type, receivedAt, id, action, reference_id } = (e.detail || {});
                prependItem(
                    title,
                    message,
                    (options && options.icon) || type || 'info',
                    receivedAt,
                    false,
                    id,
                    action,
                    reference_id,
                    e.detail?.data || null
                );
                updateUnreadCount();
                
                // Show desktop notification
                showDesktopNotification(title || 'Admin Notification', message || '');
            });

            markAllReadBtn.addEventListener('click', function() {
                markAllAsRead();
            });

            // Listen for admin notification events from global handler
            window.addEventListener('admin-notification:received', function(e) {
                const { title, message, type, receivedAt, id, action, reference_id, data } = (e.detail || {});
                
                // Add to list if we're on the notifications page
                if (typeof prependItem === 'function') {
                    prependItem(title, message, type || 'info', receivedAt, false, id, action, reference_id, data || null);
                    updateUnreadCount();
                }
            });

            // Also listen for legacy event name (backwards compatibility)
            window.addEventListener('notification:received', function(e) {
                const { title, message, options, type, receivedAt, id, action, reference_id, data } = (e.detail || {});
                
                if (typeof prependItem === 'function') {
                    prependItem(
                        title,
                        message,
                        (options && options.icon) || type || 'info',
                        receivedAt,
                        false,
                        id,
                        action,
                        reference_id,
                        data || null
                    );
                    updateUnreadCount();
                }
            });
        });
        </script>
    </x-slot>
</x-admin-layout>


