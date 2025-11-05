(function() {
    'use strict';
    
    let pusher = null;
    let isConnected = false;
    let adminPublicChannel = null;
    let adminPrivateChannel = null;

    // Initialize real-time updates
    function initRealtimeUpdates() {
        // Check Pusher availability
        if (typeof Pusher === 'undefined') {
            // console.error('[Admin Sales] ❌ Pusher library not loaded');
            return;
        }

        // Check Pusher key
        if (!window.PUSHER_APP_KEY || window.PUSHER_APP_KEY === '' || window.PUSHER_APP_KEY === 'undefined') {
            // console.error('[Admin Sales] ❌ PUSHER_APP_KEY not configured');
            return;
        }

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const config = {
            cluster: window.PUSHER_APP_CLUSTER || 'mt1',
            forceTLS: true,
            enableTransports: ['ws', 'wss', 'xhr_streaming', 'xhr_polling'],
            disableStats: true,
            authEndpoint: window.PUSHER_AUTH_ENDPOINT || '/admin/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': csrf || ''
                }
            }
        };

        // console.log('[Admin Sales] Creating Pusher instance...');
        pusher = new Pusher(window.PUSHER_APP_KEY, config);

        // Connection event handlers
        pusher.connection.bind('connecting', () => {
            // console.log('[Admin Sales] 🔌 Pusher connecting...');
        });

        pusher.connection.bind('connected', () => {
            // console.log('[Admin Sales] ✅ Pusher connected successfully');
            isConnected = true;
            updateConnectionStatus(true);
        });

        pusher.connection.bind('disconnected', () => {
            // console.warn('[Admin Sales] ⚠️ Pusher disconnected');
            isConnected = false;
            updateConnectionStatus(false);
        });

        pusher.connection.bind('error', (error) => {
            // console.error('[Admin Sales] ❌ Pusher error:', error);
            isConnected = false;
            updateConnectionStatus(false);
        });

        pusher.connection.bind('state_change', (states) => {
            // console.log('[Admin Sales] State changed:', states.previous, '->', states.current);
        });

        // Subscribe to public admin-dashboard channel (for 'all' broadcasts)
        // console.log('[Admin Sales] Subscribing to public channel: admin-dashboard');
        adminPublicChannel = pusher.subscribe('admin-dashboard');

        adminPublicChannel.bind('pusher:subscription_succeeded', () => {
            // console.log('[Admin Sales] ✅ Successfully subscribed to admin-dashboard');
        });

        adminPublicChannel.bind('pusher:subscription_error', (status) => {
            // console.error('[Admin Sales] ❌ Subscription error on admin-dashboard:', status);
        });

        // Listen for dashboard.updated events on public channel
        adminPublicChannel.bind('dashboard.updated', function(eventData) {
            // console.log('[Admin Sales] ⚡⚡⚡ RECEIVED dashboard.updated on public channel! ⚡⚡⚡', eventData);
            handleDashboardUpdate(eventData);
        });

        // Try to subscribe to private channel if admin ID is available
        // This is for admin-specific broadcasts
        const adminId = window.ADMIN_ID;
        if (adminId) {
            const privateChannelName = 'private-admin-dashboard.' + adminId;
            // console.log('[Admin Sales] Subscribing to private channel:', privateChannelName);
            
            adminPrivateChannel = pusher.subscribe(privateChannelName);

            adminPrivateChannel.bind('pusher:subscription_succeeded', () => {
                // console.log('[Admin Sales] ✅ Successfully subscribed to', privateChannelName);
            });

            adminPrivateChannel.bind('pusher:subscription_error', (status) => {
                // console.error('[Admin Sales] ❌ Subscription error on', privateChannelName + ':', status);
            });

            // Listen for dashboard.updated events on private channel
            adminPrivateChannel.bind('dashboard.updated', function(eventData) {
                // console.log('[Admin Sales] ⚡⚡⚡ RECEIVED dashboard.updated on private channel! ⚡⚡⚡', eventData);
                handleDashboardUpdate(eventData);
            });
        }
    }

    // Handle dashboard update event
    function handleDashboardUpdate(eventData) {
        if (!eventData) {
            // console.log('[Admin Sales] Invalid event data');
            return;
        }

        // Extract metrics from the event data structure
        let metrics = null;
        
        // Handle different event data structures
        if (eventData.data && eventData.data.metrics) {
            // New structure: { data: { metrics: {...} }, type: 'sales_update' }
            metrics = eventData.data.metrics;
        } else if (eventData.data && typeof eventData.data === 'object' && eventData.type === 'sales_update') {
            // Alternative structure: { data: {...}, type: 'sales_update' }
            metrics = eventData.data;
        } else if (eventData.metrics) {
            // Legacy structure: { metrics: {...} }
            metrics = eventData.metrics;
        }

        // Only process if type is sales_update
        const eventType = eventData.type || eventData.data?.type;
        if (eventType === 'sales_update' && metrics) {
            // console.log('[Admin Sales] Processing sales update with metrics:', metrics);
            updateAdminSalesReport(metrics);
            } else {
            // console.log('[Admin Sales] Event type not sales_update or no metrics found. Type:', eventType);
        }
    }

    function updateAdminSalesReport(metrics) {
        if (!metrics) {
            // console.log('[Admin Sales] No metrics provided');
            return;
        }

        // console.log('[Admin Sales] Updating sales report with metrics:', metrics);

        // Update total revenue banner
        if (metrics.grandRevenue !== undefined) {
            updateElement('grand-revenue', '₱' + parseFloat(metrics.grandRevenue).toLocaleString('en-US', {minimumFractionDigits: 2}));
        }

        // Update KPI cards
        if (metrics.ticketRevenue !== undefined) {
            updateElement('ticket-revenue', '₱' + parseFloat(metrics.ticketRevenue).toLocaleString('en-US', {minimumFractionDigits: 2}));
            
            // Also update net revenue text below if available
            if (metrics.ticketNetRevenue !== undefined) {
                const ticketCard = document.querySelector('[data-stat="ticket-revenue"]')?.closest('.bg-white');
                if (ticketCard) {
                    const netEl = ticketCard.querySelector('.text-xs.text-gray-500, .text-xs.text-gray-400');
                    if (netEl && netEl.textContent.includes('Net:')) {
                        netEl.textContent = 'Net: ₱' + parseFloat(metrics.ticketNetRevenue).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    }
                }
            }
        }

        if (metrics.ticketOrders !== undefined) {
            updateElement('ticket-orders', metrics.ticketOrders.toLocaleString());
        }

        if (metrics.ticketPending !== undefined) {
            updateElement('ticket-pending', metrics.ticketPending.toLocaleString());
        }

        if (metrics.ticketLimitRevenue !== undefined) {
            updateElement('additional-tickets-revenue', '₱' + parseFloat(metrics.ticketLimitRevenue).toLocaleString('en-US', {minimumFractionDigits: 2}));
            
            // Also update net revenue text below if available
            if (metrics.ticketLimitNetRevenue !== undefined) {
                const addlCard = document.querySelector('[data-stat="additional-tickets-revenue"]')?.closest('.bg-white');
                if (addlCard) {
                    const netEl = addlCard.querySelector('.text-xs.text-teal-600, .text-xs.text-teal-400');
                    if (netEl && netEl.textContent.includes('Net:')) {
                        netEl.textContent = 'Net: ₱' + parseFloat(metrics.ticketLimitNetRevenue).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    }
                }
            }
        }

        if (metrics.additionalTicketsPurchased !== undefined) {
            updateElement('additional-tickets-count', metrics.additionalTicketsPurchased.toLocaleString() + ' tix purchased');
        }

        if (metrics.subRevenue !== undefined) {
            updateElement('subscription-revenue', '₱' + parseFloat(metrics.subRevenue).toLocaleString('en-US', {minimumFractionDigits: 2}));
            
            // Also update net revenue text below if available
            if (metrics.subNetRevenue !== undefined) {
                const subCard = document.querySelector('[data-stat="subscription-revenue"]')?.closest('.bg-white');
                if (subCard) {
                    const netEl = subCard.querySelector('.text-xs.text-teal-600, .text-xs.text-teal-400');
                    if (netEl && netEl.textContent.includes('Net:')) {
                        netEl.textContent = 'Net: ₱' + parseFloat(metrics.subNetRevenue).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    }
                }
            }
        }

        if (metrics.totalSubscriptions !== undefined) {
            updateElement('subscription-count', metrics.totalSubscriptions.toLocaleString() + ' users subscribed');
        }

        // Update top events table from metrics if available
        if (metrics.topEvents && Array.isArray(metrics.topEvents)) {
            // console.log('[Admin Sales] 📊 Updating top events table from metrics:', metrics.topEvents.length, 'events');
            updateTopEventsTable(metrics.topEvents);
        }

        // Always refresh all tables to ensure they're up to date with latest data
        // console.log('[Admin Sales] 🔄 Refreshing all tables and charts...');
        
        // Refresh event revenue table (also updates top events)
        refreshEventRevenueTable();
        
        // Refresh subscription users table
        refreshSubscriptionUsersTable();
        
        // Update Qricket earnings in banner if available
        if (metrics.adminEarningsTotal !== undefined) {
            const banner = document.querySelector('[data-stat="grand-revenue"]')?.closest('.bg-white');
            if (banner) {
                const earningsEl = banner.querySelector('.text-sm');
                if (earningsEl && earningsEl.textContent.includes('Qricket Earnings')) {
                    earningsEl.innerHTML = '<span class="text-blue-600 dark:text-blue-400">Qricket Earnings (5% of ticket net + subs net + addl net)</span>: ₱' + parseFloat(metrics.adminEarningsTotal).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }
            }
        }

        // Refresh charts if functions exist
        if (typeof refreshAdminSalesCharts === 'function') {
            setTimeout(() => refreshAdminSalesCharts(), 500);
        }

        // Show notification (optional - can be commented out if too frequent)
        // showSalesUpdateNotification(metrics);
    }

    function updateElement(selector, newValue) {
        const element = document.querySelector(`[data-stat="${selector}"]`);
        if (element) {
            animateCounter(element, newValue);
        } else {
            // console.warn('[Admin Sales] Element not found for selector:', selector);
        }
    }

    function animateCounter(element, newValue) {
        if (!element) return;
        element.style.transform = 'scale(1.1)';
        element.style.color = '#10b981';
        element.style.transition = 'all 0.3s ease';
        setTimeout(() => {
            element.textContent = newValue;
            element.style.transform = 'scale(1)';
            element.style.color = '';
        }, 150);
    }

    function updateTopEventsTable(topEvents) {
        const tableBody = document.querySelector('#topEventsTable tbody');
        if (!tableBody) return;

        // Clear existing rows
        tableBody.innerHTML = '';

        // Add new rows
        topEvents.slice(0, 8).forEach((event, index) => {
            const row = document.createElement('tr');
            row.className = 'border-t border-gray-200 dark:border-gray-700';
            
            row.innerHTML = `
                <td class="py-2 pr-4">${truncateText(event.title, 20)}</td>
                <td class="py-2 pr-4">${event.orders.toLocaleString()}</td>
                <td class="py-2 pr-4">₱${parseFloat(event.revenue).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
            `;
            
            tableBody.appendChild(row);
        });

        // Add animation to new rows
        tableBody.querySelectorAll('tr').forEach((row, index) => {
            row.style.opacity = '0';
            row.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                row.style.transition = 'all 0.3s ease';
                row.style.opacity = '1';
                row.style.transform = 'translateY(0)';
            }, index * 50);
        });
    }

    function truncateText(text, maxLength) {
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    }

    function showSalesUpdateNotification(metrics) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Sales Data Updated',
                text: 'New sales data has been received and metrics have been updated',
                icon: 'success',
                timer: 2000,
                timerProgressBar: true,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
            });
        }
    }

    function updateConnectionStatus(connected) {
        const indicator = document.getElementById('admin-sales-connection-indicator');
        const status = document.getElementById('admin-sales-connection-status');
        
        if (indicator) {
            if (connected) {
                indicator.className = 'w-2 h-2 bg-green-500 rounded-full animate-pulse';
            } else {
                indicator.className = 'w-2 h-2 bg-red-500 rounded-full';
            }
        }
        
        if (status) {
            if (connected) {
                status.textContent = 'Live Updates Active';
                status.className = 'text-xs text-green-600 dark:text-green-400 ml-2';
            } else {
                status.textContent = 'Live Updates Disconnected';
                status.className = 'text-xs text-red-600 dark:text-red-400 ml-2';
            }
        }
    }

    // Refresh Event Revenue Table
    function refreshEventRevenueTable() {
        // console.log('[Admin Sales] 📊 Refreshing event revenue table...');
        
        const url = new URL('/realtime/metrics', window.location.origin);
        url.searchParams.set('scope', 'admin_sales');
        
        fetch(url.toString(), {
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            // console.log('[Admin Sales] 📥 Full API response received:', data);
            
            // Update top events table if available
            if (data && data.topEvents && Array.isArray(data.topEvents)) {
                // console.log('[Admin Sales] ✅ Top events data received:', data.topEvents.length, 'events');
                updateTopEventsTable(data.topEvents);
            }
            
            // Update event revenue table if available
            if (data && data.allEventsRevenue && Array.isArray(data.allEventsRevenue)) {
                // console.log('[Admin Sales] ✅ Event revenue data received:', data.allEventsRevenue.length, 'events');
                updateEventRevenueTable(data.allEventsRevenue);
            } else {
                // Fallback: trigger full page refresh of the table section
                // console.log('[Admin Sales] ⚠️ Full event revenue data not available, using fallback refresh');
                refreshEventRevenueTableFallback();
            }
        })
        .catch(error => {
            // console.error('[Admin Sales] ❌ Failed to fetch event revenue data:', error);
            refreshEventRevenueTableFallback();
        });
    }

    function updateEventRevenueTable(events) {
        const tableBody = document.querySelector('#eventRevenueTable tbody');
        if (!tableBody) {
            // console.warn('[Admin Sales] ⚠️ Event revenue table tbody not found');
            return;
        }

        // console.log('[Admin Sales] 📝 Updating event revenue table with', events.length, 'events');

        // Clear existing rows
        tableBody.innerHTML = '';

        if (events.length === 0) {
            tableBody.innerHTML = '<tr><td class="py-3 text-gray-500 dark:text-gray-400" colspan="7">No events with revenue found</td></tr>';
            return;
        }

        // Add new rows
        events.forEach((event, index) => {
            const row = document.createElement('tr');
            row.className = 'border-t border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700';
            
            const statusClass = event.status === 'published' 
                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                : event.status === 'draft'
                ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                : event.status === 'cancelled'
                ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
            
            const eventUrl = `/admin/events/approval/${event.event_id}`;
            const totalRevenue = event.total_revenue || event.revenue || 0;
            const netRevenue = event.net_revenue || event.total_revenue || event.revenue || 0;
            const adminEarnings = event.admin_earnings || 0;
            
            row.innerHTML = `
                <td class="py-2 pr-4 font-medium">
                    <a href="${eventUrl}" class="text-teal-600 dark:text-teal-400 hover:text-teal-800 dark:hover:text-teal-300 hover:underline">
                        ${escapeHtml(event.title || event.event_title || 'Untitled Event')}
                    </a>
                </td>
                <td class="py-2 pr-4">${escapeHtml(event.start_date || 'N/A')}</td>
                <td class="py-2 pr-4">
                    <span class="px-2 py-1 text-xs rounded-full ${statusClass}">
                        ${escapeHtml(event.status ? event.status.charAt(0).toUpperCase() + event.status.slice(1) : 'Unknown')}
                    </span>
                </td>
                <td class="py-2 pr-4">${Number(event.orders || 0).toLocaleString()}</td>
                <td class="py-2 pr-4 font-semibold text-gray-900 dark:text-gray-100">₱${parseFloat(totalRevenue).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                <td class="py-2 pr-4 font-semibold text-blue-600 dark:text-blue-400">₱${parseFloat(netRevenue).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                <td class="py-2 pr-4 font-semibold text-teal-600 dark:text-teal-400">₱${parseFloat(adminEarnings).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            `;
            
            tableBody.appendChild(row);

            // Animate in
            row.style.opacity = '0';
            row.style.transform = 'translateY(-5px)';
            setTimeout(() => {
                row.style.transition = 'all 0.3s ease';
                row.style.opacity = '1';
                row.style.transform = 'translateY(0)';
            }, index * 30);
        });

        // console.log('[Admin Sales] ✅ Event revenue table updated successfully');
    }

    function refreshEventRevenueTableFallback() {
        // If API doesn't return full event list, reload the table section via AJAX
        // This is a fallback method
        // console.log('[Admin Sales] 🔄 Using fallback method to refresh event revenue table');
        if (typeof window.refreshSales === 'function') {
            window.refreshSales();
        }
    }

    // Refresh Subscription Users Table
    function refreshSubscriptionUsersTable() {
        // console.log('[Admin Sales] 👥 Refreshing subscription users table...');
        
        const url = new URL('/realtime/metrics', window.location.origin);
        url.searchParams.set('scope', 'admin_sales');
        
        fetch(url.toString(), {
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.subscriptionUsers && Array.isArray(data.subscriptionUsers)) {
                // console.log('[Admin Sales] ✅ Subscription users data received:', data.subscriptionUsers.length, 'users');
                updateSubscriptionUsersTable(data.subscriptionUsers);
            } else {
                // console.log('[Admin Sales] ⚠️ Full subscription users data not available in metrics');
            }
        })
        .catch(error => {
            // console.error('[Admin Sales] ❌ Failed to fetch subscription users data:', error);
        });
    }

    function updateSubscriptionUsersTable(users) {
        const tableBody = document.querySelector('#subscriptionUsersTable tbody');
        if (!tableBody) {
            // console.warn('[Admin Sales] ⚠️ Subscription users table tbody not found');
            return;
        }

        // console.log('[Admin Sales] 📝 Updating subscription users table with', users.length, 'users');

        // Clear existing rows
        tableBody.innerHTML = '';

        if (users.length === 0) {
            tableBody.innerHTML = '<tr><td class="py-3 text-gray-500 dark:text-gray-400" colspan="6">No subscription users found</td></tr>';
            return;
        }

        // Add new rows
        users.forEach((user, index) => {
            const row = document.createElement('tr');
            row.className = 'border-t border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700';
            
            const statusClass = user.status === 'active' 
                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                : user.status === 'cancelled'
                ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                : user.status === 'expired'
                ? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
                : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
            
            const userUrl = `/admin/users/${user.user_id || user.id}`;
            
            row.innerHTML = `
                <td class="py-2 pr-4 font-medium">
                    <a href="${userUrl}" class="text-teal-600 dark:text-teal-400 hover:text-teal-800 dark:hover:text-teal-300 hover:underline">
                        ${escapeHtml(user.name || user.user_name || 'Unknown User')}
                    </a>
                </td>
                <td class="py-2 pr-4">${escapeHtml(user.email || '')}</td>
                <td class="py-2 pr-4">${escapeHtml(user.plan_name || 'Unknown Plan')}</td>
                <td class="py-2 pr-4 font-semibold text-green-600 dark:text-green-400">₱${parseFloat(user.amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                <td class="py-2 pr-4">
                    <span class="px-2 py-1 text-xs rounded-full ${statusClass}">
                        ${escapeHtml(user.status ? user.status.charAt(0).toUpperCase() + user.status.slice(1) : 'Unknown')}
                    </span>
                </td>
                <td class="py-2 pr-4">${escapeHtml(user.created_at || user.subscribed_at || 'N/A')}</td>
            `;
            
            tableBody.appendChild(row);

            // Animate in
            row.style.opacity = '0';
            row.style.transform = 'translateY(-5px)';
            setTimeout(() => {
                row.style.transition = 'all 0.3s ease';
                row.style.opacity = '1';
                row.style.transform = 'translateY(0)';
            }, index * 30);
        });

        // console.log('[Admin Sales] ✅ Subscription users table updated successfully');
    }

    function escapeHtml(text) {
        if (text == null) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRealtimeUpdates);
            } else {
        initRealtimeUpdates();
    }

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (pusher) {
            // console.log('[Admin Sales] Disconnecting Pusher...');
            pusher.disconnect();
        }
    });
})();
