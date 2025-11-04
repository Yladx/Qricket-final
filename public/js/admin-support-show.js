/**
 * Admin Support Show - Complete Real-time Reply Handler
 * Handles form submission and real-time updates for admin support ticket replies
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
        // console.log('[Admin Support] DOM loaded, initializing...');
        
        const replyForm = document.getElementById('admin-reply-form');
        const messageTextarea = document.getElementById('admin-message');
        const sendButton = document.getElementById('admin-send-btn');
        const sendText = document.getElementById('admin-send-text');
        const sendSpinner = document.getElementById('admin-send-spinner');
        const messageError = document.getElementById('admin-message-error');
    const repliesList = document.getElementById('admin-replies-list');

        if (!replyForm || !repliesList) {
            // console.error('[Admin Support] Required elements not found:', {
            //     form: !!replyForm,
            //     list: !!repliesList
            // });
            return;
        }

        // console.log('[Admin Support] Form elements found, setting up handlers...');

        // Form submission handler
    replyForm.addEventListener('submit', async function(e) {
        e.preventDefault();
            // console.log('[Admin Support] Form submitted');
        
        const message = messageTextarea.value ? messageTextarea.value.trim() : '';

            // console.log('[Admin Support] Sending reply:', message.substring(0, 50) + '...');
        setLoadingState(true);
        hideError();

        try {
            const formData = new FormData(replyForm);
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                if (!csrfToken) {
                    throw new Error('CSRF token not found');
                }

            const response = await fetch(replyForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

                // console.log('[Admin Support] Response status:', response.status);

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({ message: 'Failed to send reply' }));
                    throw new Error(errorData.message || 'Server error');
                }

            const data = await response.json();
                // console.log('[Admin Support] Reply response:', data);

                if (data.success) {
                    // console.log('[Admin Support] Reply successful, adding to DOM');
                    if (messageTextarea) {
                messageTextarea.value = '';
                    messageTextarea.style.height = 'auto';
                    }
                
                    // Add reply immediately to DOM
                    if (data.reply) {
                    addReplyToDOM(data.reply, true);
                    }
                
                // Show success message
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: data.message || 'Reply sent successfully',
                            timer: 2000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    }
                
                // Scroll to bottom
                scrollToBottom();
            } else {
                    throw new Error(data.message || 'Failed to send reply');
            }
        } catch (error) {
                // console.error('[Admin Support] Error sending reply:', error);
                showError(error.message || 'Network error. Please try again.');
        } finally {
            setLoadingState(false);
        }
    });

        // Helper functions
    function setLoadingState(loading) {
            sendButton.disabled = loading;
        if (loading) {
                sendText.classList.add('hidden');
                sendSpinner.classList.remove('hidden');
        } else {
                sendText.classList.remove('hidden');
                sendSpinner.classList.add('hidden');
        }
    }

    function showError(message) {
            if (messageError) {
        messageError.textContent = message;
        messageError.classList.remove('hidden');
                messageTextarea.classList.add('border-red-500');
            }
    }

    function hideError() {
            if (messageError) {
        messageError.classList.add('hidden');
                messageError.textContent = '';
                messageTextarea.classList.remove('border-red-500');
            }
        }

        function addReplyToDOM(reply, isAdminReply = false) {
            if (!repliesList) return;

            const replyId = reply.id || 'new-' + Date.now();
            
            // Check if reply already exists
            const existing = repliesList.querySelector(`[data-reply-id="${replyId}"]`);
            if (existing) {
                // console.log('[Admin Support] Reply already exists, skipping:', replyId);
                return;
            }

            // console.log('[Admin Support] Adding reply to DOM:', replyId, isAdminReply ? '(admin)' : '(user)');

        const replyElement = document.createElement('div');
            replyElement.className = `flex ${isAdminReply ? 'justify-end' : 'justify-start'}`;
            replyElement.setAttribute('data-reply-id', replyId);
            replyElement.style.opacity = '0';
            replyElement.style.transform = 'translateY(6px)';
            replyElement.style.transition = 'opacity 0.2s ease, transform 0.2s ease';

            const author = escapeHtml(reply.admin_name || reply.author || (isAdminReply ? 'Admin' : 'User'));
            const createdAt = reply.created_at || 'Just now';
            const messageHtml = escapeHtml(reply.message || '');
        
        replyElement.innerHTML = `
                <div class="max-w-[80%]">
                    <div class="flex items-center space-x-2 ${isAdminReply ? 'justify-end flex-row-reverse space-x-reverse' : ''} mb-1">
                        <div class="h-7 w-7 rounded-full ${isAdminReply ? 'bg-qricket-600' : 'bg-gray-300 dark:bg-gray-600'} flex items-center justify-center text-[11px] font-bold text-white">${author.charAt(0)}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400"><span class="font-medium text-gray-700 dark:text-gray-300">${author}</span> • ${createdAt}</div>
                    </div>
                    <div class="rounded-2xl px-4 py-2 text-sm shadow-sm ${isAdminReply ? 'bg-qricket-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'}">${messageHtml}</div>
                </div>
            `;

            repliesList.appendChild(replyElement);
            
            // Animate in
            setTimeout(() => {
                replyElement.style.opacity = '1';
                replyElement.style.transform = 'translateY(0)';
            }, 10);
    }

    function scrollToBottom() {
        if (repliesList) {
            repliesList.scrollTop = repliesList.scrollHeight;
        }
    }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
    }

    // Auto-resize textarea
        if (messageTextarea) {
    messageTextarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
                hideError();
            });
            
            // Auto-resize on load
            messageTextarea.style.height = 'auto';
            messageTextarea.style.height = messageTextarea.scrollHeight + 'px';
        }

        // Initialize real-time updates
        // console.log('[Admin Support] Initializing real-time updates...');
        initRealtimeUpdates();
    });

    // Real-time updates initialization
    function initRealtimeUpdates() {
        // console.log('[Admin Support] initRealtimeUpdates() called');
        
        // Check Pusher availability
        if (typeof Pusher === 'undefined') {
            // console.error('[Admin Support] Pusher library not loaded');
            return;
        }

        // Check Pusher key
        if (!window.PUSHER_APP_KEY || window.PUSHER_APP_KEY === '' || window.PUSHER_APP_KEY === 'undefined') {
            // console.error('[Admin Support] PUSHER_APP_KEY not configured');
            return;
        }

        // Check ticket number
        const ticketNumber = window.TICKET_NUMBER;
        if (!ticketNumber) {
            // console.error('[Admin Support] TICKET_NUMBER not set');
            return;
        }

        // console.log('[Admin Support] All checks passed, initializing Pusher...');
        // console.log('[Admin Support] Ticket number:', ticketNumber);

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const config = {
            cluster: window.PUSHER_APP_CLUSTER || 'mt1',
            forceTLS: true,
            enableTransports: ['ws','wss','xhr_streaming','xhr_polling'],
            disableStats: true,
            authEndpoint: window.PUSHER_AUTH_ENDPOINT || '/admin/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': csrf || ''
                }
            }
        };

        // console.log('[Admin Support] Creating Pusher instance...');
        const pusher = new Pusher(window.PUSHER_APP_KEY, config);
        const repliesList = document.getElementById('admin-replies-list');

        // Connection event handlers
        pusher.connection.bind('connecting', () => {
            // console.log('[Admin Support] 🔌 Pusher connecting...');
        });

        pusher.connection.bind('connected', () => {
            // console.log('[Admin Support] ✅ Pusher connected successfully');
        });

        pusher.connection.bind('disconnected', () => {
            // console.warn('[Admin Support] ⚠️ Pusher disconnected');
        });

        pusher.connection.bind('error', (error) => {
            // console.error('[Admin Support] ❌ Pusher error:', error);
        });

        pusher.connection.bind('state_change', (states) => {
            // console.log('[Admin Support] State changed:', states.previous, '->', states.current);
        });

        // Subscribe to channel
        const channelName = 'private-admin.support.' + ticketNumber;
        // console.log('[Admin Support] Subscribing to channel:', channelName);
        
        const adminChannel = pusher.subscribe(channelName);

        adminChannel.bind('pusher:subscription_succeeded', () => {
            // console.log('[Admin Support] ✅ Successfully subscribed to', channelName);
        });

        adminChannel.bind('pusher:subscription_error', (status) => {
            // console.error('[Admin Support] ❌ Subscription error:', status);
        });

        // Listen for support reply events
        adminChannel.bind('support.reply.added', function(data) {
            // console.log('[Admin Support] ⚡⚡⚡ RECEIVED support.reply.added event! ⚡⚡⚡', data);

            if (!data || !data.ticket_number) {
                // console.log('[Admin Support] Invalid event data');
                return;
            }

            if (data.ticket_number !== ticketNumber) {
                // console.log('[Admin Support] Ticket number mismatch:', data.ticket_number, 'vs', ticketNumber);
                return;
            }

            if (!repliesList) {
                // console.error('[Admin Support] Replies list element not found');
                return;
            }

            // Check for duplicates
            const replyId = data.reply_id;
            const existing = repliesList.querySelector(`[data-reply-id="${replyId}"]`);
            if (existing) {
                // console.log('[Admin Support] Reply already exists, skipping duplicate:', replyId);
                return;
            }

            // Determine if this is an admin or user reply
            const isAdmin = data.is_admin_reply === true;
            
            // if (!isAdmin) {
            //     console.log('[Admin Support] 🎯 USER REPLY RECEIVED! Adding to chat immediately...', replyId);
            // } else {
            //     console.log('[Admin Support] Admin reply received:', replyId);
            // }

            const authorName = isAdmin ? (data.author || 'Admin') : (data.author || 'User');
            const replyElement = document.createElement('div');
            replyElement.className = `flex ${isAdmin ? 'justify-end' : 'justify-start'}`;
            replyElement.setAttribute('data-reply-id', replyId);
            replyElement.style.opacity = '0';
            replyElement.style.transform = 'translateY(6px)';
            replyElement.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
            
            replyElement.innerHTML = `
                <div class="max-w-[80%]">
                    <div class="flex items-center space-x-2 ${isAdmin ? 'justify-end flex-row-reverse space-x-reverse' : ''} mb-1">
                        <div class="h-7 w-7 rounded-full ${isAdmin ? 'bg-qricket-600' : 'bg-gray-300 dark:bg-gray-600'} flex items-center justify-center text-[11px] font-bold text-white">${escapeHtml(authorName).charAt(0)}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400"><span class="font-medium text-gray-700 dark:text-gray-300">${escapeHtml(authorName)}</span> • ${data.created_at || 'Just now'}</div>
                    </div>
                    <div class="rounded-2xl px-4 py-2 text-sm shadow-sm ${isAdmin ? 'bg-qricket-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'}">${escapeHtml(data.message || '')}</div>
                </div>
            `;

            repliesList.appendChild(replyElement);
            
            // Animate in
            setTimeout(() => {
                replyElement.style.opacity = '1';
                replyElement.style.transform = 'translateY(0)';
            }, 10);
            
            scrollToBottom();
            
            // Show browser notification if page is not focused (for user replies only)
            if (!isAdmin && document.hidden) {
                if (Notification.permission === 'granted') {
                    new Notification('New Reply from User', {
                        body: data.message.substring(0, 50) + (data.message.length > 50 ? '...' : ''),
                        icon: '/favicon.ico',
                        tag: 'support-reply-' + replyId
                    });
                } else if (Notification.permission !== 'denied') {
                    // Request permission
                    Notification.requestPermission().then(permission => {
                        if (permission === 'granted') {
                            new Notification('New Reply from User', {
                                body: data.message.substring(0, 50) + (data.message.length > 50 ? '...' : ''),
                                icon: '/favicon.ico',
                                tag: 'support-reply-' + replyId
                            });
                        }
                    });
                }
            }
            
            // console.log('[Admin Support] ✅✅✅ Reply added to DOM successfully! ✅✅✅');
        });

        function scrollToBottom() {
            if (repliesList) {
                repliesList.scrollTop = repliesList.scrollHeight;
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }
})();

