(function() {
    'use strict';
    
    let pusher = null;
    let isConnected = false;
    let publicChannel = null;
    let privateChannel = null;
    let eventChannel = null;

    // Get event ID and user ID from page
    const eventId = document.querySelector('[data-event-id]')?.getAttribute('data-event-id');
    const userId = document.querySelector('[data-user-id]')?.getAttribute('data-user-id');
    const isAdmin = window.location.pathname.includes('/admin/');

    if (!eventId) {
        console.error('[Sales Report] ❌ Event ID not found');
        return;
    }

    // Initialize real-time updates
    function initRealtimeUpdates() {
        // Check Pusher availability
        if (typeof Pusher === 'undefined') {
            console.error('[Sales Report] ❌ Pusher library not loaded');
            return;
        }

        // Check Pusher key
        if (!window.PUSHER_APP_KEY || window.PUSHER_APP_KEY === '' || window.PUSHER_APP_KEY === 'undefined') {
            console.error('[Sales Report] ❌ PUSHER_APP_KEY not configured');
            return;
        }

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const authEndpoint = isAdmin 
            ? (window.PUSHER_AUTH_ENDPOINT || '/admin/broadcasting/auth')
            : (window.PUSHER_AUTH_ENDPOINT || '/broadcasting/auth');

        const config = {
            cluster: window.PUSHER_APP_CLUSTER || 'mt1',
            forceTLS: true,
            enableTransports: ['ws', 'wss', 'xhr_streaming', 'xhr_polling'],
            disableStats: true,
            authEndpoint: authEndpoint,
            auth: {
                headers: {
                    'X-CSRF-TOKEN': csrf || ''
                }
            }
        };

        console.log('[Sales Report] Creating Pusher instance...', {
            pusherKey: window.PUSHER_APP_KEY ? window.PUSHER_APP_KEY.substring(0, 10) + '...' : 'NOT SET',
            cluster: window.PUSHER_APP_CLUSTER || 'mt1',
            authEndpoint: authEndpoint,
            eventId: eventId,
            userId: userId,
            isAdmin: isAdmin,
            csrfToken: csrf ? 'SET' : 'NOT SET'
        });
        
        pusher = new Pusher(window.PUSHER_APP_KEY, config);

        // Connection event handlers
        pusher.connection.bind('connecting', () => {
            console.log('[Sales Report] 🔌 Pusher connecting...');
        });

        pusher.connection.bind('connected', () => {
            console.log('[Sales Report] ✅ Pusher connected successfully');
            isConnected = true;
            updateConnectionStatus(true);
        });

        pusher.connection.bind('disconnected', () => {
            console.warn('[Sales Report] ⚠️ Pusher disconnected');
            isConnected = false;
            updateConnectionStatus(false);
        });

        pusher.connection.bind('error', (error) => {
            console.error('[Sales Report] ❌ Pusher error:', error);
            isConnected = false;
            updateConnectionStatus(false);
        });

        // Subscribe to public sales-updates channel
        console.log('[Sales Report] Subscribing to public channel: sales-updates');
        publicChannel = pusher.subscribe('sales-updates');

        publicChannel.bind('pusher:subscription_succeeded', () => {
            console.log('[Sales Report] ✅ Successfully subscribed to sales-updates');
        });

        publicChannel.bind('pusher:subscription_error', (status) => {
            console.error('[Sales Report] ❌ Subscription error on sales-updates:', status);
        });

        // Listen for sales.report.updated events
        publicChannel.bind('sales.report.updated', function(eventData) {
            console.log('[Sales Report] ⚡⚡⚡ RECEIVED sales.report.updated! ⚡⚡⚡');
            console.log('[Sales Report] Full event data:', JSON.stringify(eventData, null, 2));
            
            // Only process if it's for this event
            if (eventData.event_id && String(eventData.event_id) !== String(eventId)) {
                console.log('[Sales Report] ⏭️ Skipping update - different event ID:', eventData.event_id, 'vs', eventId);
                return;
            }

            handleSalesUpdate(eventData);
        });

        // Subscribe to event-specific channel
        const eventChannelName = `event.${eventId}`;
        console.log('[Sales Report] Subscribing to event channel:', eventChannelName);
        eventChannel = pusher.subscribe(eventChannelName);

        eventChannel.bind('pusher:subscription_succeeded', () => {
            console.log('[Sales Report] ✅ Successfully subscribed to', eventChannelName);
        });

        eventChannel.bind('pusher:subscription_error', (status) => {
            console.error('[Sales Report] ❌ Subscription error on', eventChannelName + ':', status);
        });

        // Listen for event.stats.updated events
        eventChannel.bind('event.stats.updated', function(eventData) {
            console.log('[Sales Report] ⚡⚡⚡ RECEIVED event.stats.updated! ⚡⚡⚡');
            console.log('[Sales Report] Event stats data:', JSON.stringify(eventData, null, 2));
            handleEventStatsUpdate(eventData);
        });

        // Subscribe to private channel if user ID is available
        if (userId) {
            const privateChannelName = isAdmin 
                ? `private-admin-dashboard.${userId}` 
                : `private-organizer-dashboard.${userId}`;
            console.log('[Sales Report] Subscribing to private channel:', privateChannelName);
            
            privateChannel = pusher.subscribe(privateChannelName);

            privateChannel.bind('pusher:subscription_succeeded', () => {
                console.log('[Sales Report] ✅ Successfully subscribed to', privateChannelName);
            });

            privateChannel.bind('pusher:subscription_error', (status) => {
                console.error('[Sales Report] ❌ Subscription error on', privateChannelName + ':', status);
            });

            // Listen for dashboard updates on private channel
            privateChannel.bind('dashboard.stats.updated', function(eventData) {
                console.log('[Sales Report] ⚡⚡⚡ RECEIVED dashboard.stats.updated on private channel! ⚡⚡⚡');
                handleDashboardUpdate(eventData);
            });
        }
    }

    // Handle sales update event
    function handleSalesUpdate(eventData) {
        if (!eventData) {
            console.log('[Sales Report] ❌ Invalid event data');
            return;
        }

        console.log('[Sales Report] 🔄 Processing sales update...');
        console.log('[Sales Report] Event data structure:', {
            has_event_stats: !!eventData.event_stats,
            has_sales_data: !!eventData.sales_data,
            event_id: eventData.event_id,
            organizer_id: eventData.organizer_id
        });

        // Update totals if event_stats is available
        if (eventData.event_stats) {
            updateSalesTotals(eventData.event_stats);
        }

        // Refresh the sales table if new sale is detected
        if (eventData.sales_data) {
            console.log('[Sales Report] 💰 New sale detected, refreshing table...');
            refreshSalesTable();
        }

        // Always refresh totals from API to ensure accuracy
        refreshSalesTotals();
    }

    // Handle event stats update
    function handleEventStatsUpdate(eventData) {
        if (!eventData || !eventData.stats) {
            console.log('[Sales Report] ❌ Invalid event stats data');
            return;
        }

        console.log('[Sales Report] 📊 Updating from event stats:', eventData.stats);
        updateSalesTotals(eventData.stats);
    }

    // Handle dashboard update
    function handleDashboardUpdate(eventData) {
        if (!eventData || !eventData.stats) {
            return;
        }

        console.log('[Sales Report] 📊 Dashboard stats update received');
        // Refresh totals to get latest data
        refreshSalesTotals();
    }

    // Update sales totals in the UI
    function updateSalesTotals(stats) {
        console.log('[Sales Report] 📊 Updating sales totals with stats:', stats);

        if (stats.total_revenue !== undefined) {
            updateElement('total-revenue', '₱' + parseFloat(stats.total_revenue).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        }

        // Note: Net amount, fees, and expected earnings are calculated on the server
        // We'll refresh the full totals via API to get accurate values
    }

    // Refresh sales totals from API
    function refreshSalesTotals() {
        console.log('[Sales Report] 🔄 Refreshing sales totals from API...');
        
        // Use the current page URL with a refresh parameter
        const url = new URL(window.location.href);
        url.searchParams.set('_refresh', Date.now());

        // Make a lightweight request to get updated totals
        fetch(url.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            },
            credentials: 'same-origin'
        })
        .then(response => response.text())
        .then(html => {
            // Parse the HTML to extract totals
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Extract totals from the updated page
            const totalRevenueEl = doc.querySelector('[data-stat="total-revenue"]');
            const totalOrdersEl = doc.querySelector('[data-stat="total-orders"]');
            
            if (totalRevenueEl) {
                const newTotal = totalRevenueEl.textContent.trim();
                updateElement('total-revenue', newTotal);
            }

            if (totalOrdersEl) {
                const newOrders = totalOrdersEl.textContent.trim();
                updateElement('total-orders', newOrders);
            }

            // Extract other totals from the summary text
            const summaryText = doc.querySelector('.text-sm.text-gray-600')?.textContent || '';
            const netMatch = summaryText.match(/Net Amount:\s*₱([\d,]+\.\d{2})/);
            const feesMatch = summaryText.match(/Total Fees:\s*₱([\d,]+\.\d{2})/);
            const earningsMatch = summaryText.match(/Expected Earnings:\s*₱([\d,]+\.\d{2})/);

            if (netMatch) {
                const netEl = document.querySelector('[data-stat="net-amount"]');
                if (netEl) netEl.textContent = '₱' + netMatch[1];
            }

            if (feesMatch) {
                const feesEl = document.querySelector('[data-stat="total-fees"]');
                if (feesEl) feesEl.textContent = '₱' + feesMatch[1];
            }

            if (earningsMatch) {
                const earningsEl = document.querySelector('[data-stat="expected-earnings"]');
                if (earningsEl) earningsEl.textContent = '₱' + earningsMatch[1];
            }

            console.log('[Sales Report] ✅ Sales totals refreshed');
        })
        .catch(error => {
            console.error('[Sales Report] ❌ Failed to refresh sales totals:', error);
        });
    }

    // Refresh sales table (add new row at top if new sale)
    function refreshSalesTable() {
        console.log('[Sales Report] 🔄 Refreshing sales table...');
        
        // Reload the page after a short delay to show new sales
        // This ensures we get the latest filtered and sorted data
        setTimeout(() => {
            // Only reload if we're not currently filtering/searching
            const hasFilters = window.location.search && !window.location.search.includes('_refresh');
            if (!hasFilters) {
                console.log('[Sales Report] 🔄 Reloading page to show new sales...');
                window.location.reload();
            } else {
                console.log('[Sales Report] ⏭️ Skipping reload - filters active');
            }
        }, 2000); // Wait 2 seconds before reload to allow for batch updates
    }

    // Update a single element by data-stat attribute
    function updateElement(selector, newValue) {
        const element = document.querySelector(`[data-stat="${selector}"]`);
        if (element) {
            const oldValue = element.textContent;
            console.log('[Sales Report] ✏️ Updating element:', {
                selector: selector,
                oldValue: oldValue.trim(),
                newValue: newValue
            });
            
            // Animate the update
            element.style.transform = 'scale(1.1)';
            element.style.color = '#10b981';
            element.style.transition = 'all 0.3s ease';
            
            setTimeout(() => {
                element.textContent = newValue;
                element.style.transform = 'scale(1)';
                element.style.color = '';
                console.log('[Sales Report] ✅ Element updated successfully');
            }, 150);
        } else {
            console.warn('[Sales Report] ❌ Element not found for selector:', selector);
            console.warn('[Sales Report] Available data-stat elements:', 
                Array.from(document.querySelectorAll('[data-stat]')).map(el => el.getAttribute('data-stat'))
            );
        }
    }

    // Update connection status indicator
    function updateConnectionStatus(connected) {
        const indicator = document.getElementById('sales-connection-indicator');
        
        if (indicator) {
            if (connected) {
                indicator.className = 'w-2 h-2 bg-green-500 rounded-full animate-pulse';
            } else {
                indicator.className = 'w-2 h-2 bg-red-500 rounded-full';
            }
        }
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
            console.log('[Sales Report] Disconnecting Pusher...');
            pusher.disconnect();
        }
    });
})();

