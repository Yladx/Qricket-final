/**
 * User Support Ticket - Complete Real-time Chat Handler
 * Handles form submission and real-time updates for user support ticket replies
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
        // console.log('[User Support] DOM loaded, initializing...');
        
    const replyForm = document.getElementById('user-reply-form');
    const messageInput = document.getElementById('user-message');
    const sendButton = document.getElementById('user-send-btn');
    const repliesList = document.getElementById('replies-list');

        if (!replyForm || !repliesList) {
            // console.error('[User Support] Required elements not found:', {
            //     form: !!replyForm,
            //     list: !!repliesList
            // });
            return;
        }

        // console.log('[User Support] Form elements found, setting up handlers...');
        // console.log('[User Support] User ID:', window.USER_ID);
        // console.log('[User Support] Ticket Number:', window.TICKET_NUMBER);

        // Form submission handler
    replyForm.addEventListener('submit', async function(e) {
        e.preventDefault();
            // console.log('[User Support] Form submitted');

        const message = messageInput.value.trim();
            if (!message) {
                // console.log('[User Support] Empty message');
                return;
            }

            // console.log('[User Support] Sending reply:', message.substring(0, 50) + '...');
            setLoadingState(true);

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

                // console.log('[User Support] Response status:', response.status);

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: 'Failed to send reply' }));
                    throw new Error(errorData.message || 'Server error');
            }
            
            const data = await response.json();
                // console.log('[User Support] Reply response:', data);

                if (data.success && data.reply) {
                    // console.log('[User Support] Reply successful, adding to DOM');
                    messageInput.value = '';
                    messageInput.style.height = 'auto';
                    
                    // Add reply immediately to DOM
                    addReplyToDOM(data.reply, true);
                    
                    // Scroll to bottom after a brief delay
                    setTimeout(() => scrollToBottom(), 50);
                } else {
                    throw new Error(data.message || 'Failed to send reply');
                }
            } catch (error) {
                // console.error('[User Support] Error sending reply:', error);
                if (typeof Swal !== 'undefined' && window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Network error. Please try again.',
                    });
                } else {
                    alert(error.message || 'Network error. Please try again.');
                }
            } finally {
                setLoadingState(false);
            }
        });

        // Helper functions
        function setLoadingState(loading) {
            sendButton.disabled = loading;
            const spinner = document.getElementById('user-send-spinner');
            const icon = document.getElementById('user-send-icon');
            if (loading) {
                if (spinner) spinner.classList.remove('hidden');
                if (icon) icon.classList.add('hidden');
            } else {
                if (spinner) spinner.classList.add('hidden');
                if (icon) icon.classList.remove('hidden');
            }
        }

        function addReplyToDOM(reply, isUserReply = false) {
        if (!repliesList) return;

            const replyId = reply.id || 'new-' + Date.now();
            
            // Check if reply already exists
            const existing = repliesList.querySelector(`[data-reply-id="${replyId}"]`);
            if (existing) {
                // console.log('[User Support] Reply already exists, skipping:', replyId);
                return;
            }

            // console.log('[User Support] Adding reply to DOM:', replyId, isUserReply ? '(user)' : '(admin)');

            const bubbleClass = isUserReply 
                ? 'bg-gradient-to-r from-teal-600 to-teal-500 text-white shadow-md'
                : 'bg-white dark:bg-gray-700 border border-teal-100 dark:border-teal-900 shadow-sm';
            const containerClass = `flex flex-col ${isUserReply ? 'items-end self-end' : 'items-start'} max-w-[80%]`;
            const textClass = isUserReply ? 'text-white' : 'text-gray-800 dark:text-gray-200';
            const timeClass = isUserReply ? 'text-white/80 text-right' : 'text-gray-500 dark:text-gray-400';
            const authorLabel = isUserReply ? 'You' : 'Qricket.support';

        const replyElement = document.createElement('div');
        replyElement.className = containerClass;
            replyElement.setAttribute('data-reply-id', replyId);
        
        replyElement.innerHTML = `
            <div class="text-xs text-gray-600 dark:text-gray-400 mb-1 px-1">${authorLabel}</div>
            <div class="${bubbleClass} rounded-xl p-3">
                <div class="text-sm ${textClass}">${escapeHtml(reply.message || '')}</div>
                <div class="text-xs ${timeClass} mt-1">${reply.created_at || 'Just now'}</div>
            </div>
        `;

        repliesList.appendChild(replyElement);
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
        if (messageInput) {
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });
        }

        // Initialize real-time updates
        // console.log('[User Support] Initializing real-time updates...');
        initRealtimeUpdates();
    });

    // Real-time updates initialization
    function initRealtimeUpdates() {
        // console.log('[User Support] initRealtimeUpdates() called');
        
        // Check Pusher availability
        if (typeof Pusher === 'undefined') {
            // console.error('[User Support] ❌ Pusher library not loaded');
            return;
        }

        // Check Pusher key
        if (!window.PUSHER_APP_KEY || window.PUSHER_APP_KEY === '' || window.PUSHER_APP_KEY === 'undefined') {
            // console.error('[User Support] ❌ PUSHER_APP_KEY not configured');
            return;
        }

        // Check user ID and ticket number
        const userId = window.USER_ID;
        const ticketNumber = window.TICKET_NUMBER;

        if (!userId) {
            // console.error('[User Support] ❌ USER_ID not set');
            return;
        }

        if (!ticketNumber) {
            // console.error('[User Support] ❌ TICKET_NUMBER not set');
            return;
        }

        // console.log('[User Support] ✅ All checks passed, initializing Pusher...');
        // console.log('[User Support] User ID:', userId);
        // console.log('[User Support] Ticket Number:', ticketNumber);

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const config = {
            cluster: window.PUSHER_APP_CLUSTER || 'mt1',
            forceTLS: true,
            enableTransports: ['ws','wss','xhr_streaming','xhr_polling'],
            disableStats: true,
            authEndpoint: window.PUSHER_AUTH_ENDPOINT || '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': csrf || ''
                }
            }
        };

        // console.log('[User Support] Creating Pusher instance...');
        const pusher = new Pusher(window.PUSHER_APP_KEY, config);
        const repliesList = document.getElementById('replies-list');

        // Connection event handlers
        pusher.connection.bind('connecting', () => {
            // console.log('[User Support] 🔌 Pusher connecting...');
        });

        pusher.connection.bind('connected', () => {
            console.log('[User Support] ✅ Pusher connected successfully');
        });

        pusher.connection.bind('disconnected', () => {
            // console.warn('[User Support] ⚠️ Pusher disconnected');
        });

        pusher.connection.bind('error', (error) => {
            // console.error('[User Support] ❌ Pusher error:', error);
        });

        pusher.connection.bind('state_change', (states) => {
            // console.log('[User Support] State changed:', states.previous, '->', states.current);
        });

        // Subscribe to user channel
        const channelName = 'private-user.' + userId;
        // console.log('[User Support] Subscribing to channel:', channelName);
        
        const userChannel = pusher.subscribe(channelName);

        userChannel.bind('pusher:subscription_succeeded', () => {
            // console.log('[User Support] ✅ Successfully subscribed to', channelName);
        });

        userChannel.bind('pusher:subscription_error', (status) => {
            // console.error('[User Support] ❌ Subscription error:', status);
        });

        // Listen for support reply events
        userChannel.bind('support.reply.added', function(data) {
            // console.log('[User Support] ⚡⚡⚡ RECEIVED support.reply.added event! ⚡⚡⚡', data);

            if (!data || !data.ticket_number) {
                // console.log('[User Support] Invalid event data');
                return;
            }

            if (data.ticket_number !== ticketNumber) {
                // console.log('[User Support] Ticket number mismatch:', data.ticket_number, 'vs', ticketNumber);
                return;
            }

            if (!repliesList) {
                // console.error('[User Support] Replies list element not found');
                return;
            }

            // Check for duplicates
            const replyId = data.reply_id;
            const existing = repliesList.querySelector(`[data-reply-id="${replyId}"]`);
            if (existing) {
                // console.log('[User Support] Reply already exists, skipping duplicate:', replyId);
                return;
            }

            // Determine if this is an admin or user reply
            const isAdmin = data.is_admin_reply === true;
            
            // if (isAdmin) {
            //     console.log('[User Support] 🎯 ADMIN REPLY RECEIVED! Adding to chat immediately...', replyId);
            // } else {
            //     console.log('[User Support] User reply received:', replyId);
            // }

            const isUserReply = !isAdmin;
            const bubbleClass = isUserReply 
                ? 'bg-gradient-to-r from-teal-600 to-teal-500 text-white shadow-md'
                : 'bg-white dark:bg-gray-700 border border-teal-100 dark:border-teal-900 shadow-sm';
            const containerClass = `flex flex-col ${isUserReply ? 'items-end self-end' : 'items-start'} max-w-[80%]`;
            const textClass = isUserReply ? 'text-white' : 'text-gray-800 dark:text-gray-200';
            const timeClass = isUserReply ? 'text-white/80 text-right' : 'text-gray-500 dark:text-gray-400';
            const authorLabel = isUserReply ? 'You' : 'Qricket.support';

            // Create reply element with animation
            const replyElement = document.createElement('div');
            replyElement.className = containerClass;
            replyElement.setAttribute('data-reply-id', replyId);
            replyElement.style.opacity = '0';
            replyElement.style.transform = 'translateY(-10px)';
            replyElement.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            
            replyElement.innerHTML = `
                <div class="text-xs text-gray-600 dark:text-gray-400 mb-1 px-1">${authorLabel}</div>
                <div class="${bubbleClass} rounded-xl p-3">
                    <div class="text-sm ${textClass}">${escapeHtml(data.message || '')}</div>
                    <div class="text-xs ${timeClass} mt-1">${data.created_at || 'Just now'}</div>
                </div>
            `;

            repliesList.appendChild(replyElement);
            
            // Animate in
            setTimeout(() => {
                replyElement.style.opacity = '1';
                replyElement.style.transform = 'translateY(0)';
            }, 10);
            
            // Scroll to bottom after animation
            setTimeout(() => scrollToBottom(), 50);
            
            // Show browser notification if page is not focused
            if (isAdmin && document.hidden) {
                if (Notification.permission === 'granted') {
                    new Notification('New Reply from Support', {
                        body: data.message.substring(0, 50) + (data.message.length > 50 ? '...' : ''),
                        icon: '/favicon.ico',
                        tag: 'support-reply-' + replyId
                    });
                }
            }
            
            // console.log('[User Support] ✅✅✅ Reply added to DOM successfully! ✅✅✅');
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

