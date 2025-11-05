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
            // console.error('[Admin Dashboard] ❌ Pusher library not loaded');
            return;
        }

        // Check Pusher key
        if (!window.PUSHER_APP_KEY || window.PUSHER_APP_KEY === '' || window.PUSHER_APP_KEY === 'undefined') {
            // console.error('[Admin Dashboard] ❌ PUSHER_APP_KEY not configured');
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

        // console.log('[Admin Dashboard] Creating Pusher instance...', {
        //     pusherKey: window.PUSHER_APP_KEY ? window.PUSHER_APP_KEY.substring(0, 10) + '...' : 'NOT SET',
        //     cluster: window.PUSHER_APP_CLUSTER || 'mt1',
        //     authEndpoint: window.PUSHER_AUTH_ENDPOINT || '/admin/broadcasting/auth',
        //     adminId: window.ADMIN_ID || 'NOT SET',
        //     csrfToken: csrf ? 'SET' : 'NOT SET'
        // });
        pusher = new Pusher(window.PUSHER_APP_KEY, config);

        // Connection event handlers
        pusher.connection.bind('connecting', () => {
            // console.log('[Admin Dashboard] 🔌 Pusher connecting...');
        });

        pusher.connection.bind('connected', () => {
            // console.log('[Admin Dashboard] ✅ Pusher connected successfully');
            isConnected = true;
        });

        pusher.connection.bind('disconnected', () => {
            // console.warn('[Admin Dashboard] ⚠️ Pusher disconnected');
            isConnected = false;
        });

        pusher.connection.bind('error', (error) => {
            // console.error('[Admin Dashboard] ❌ Pusher error:', error);
            isConnected = false;
        });

        // Subscribe to public admin-dashboard channel (for 'all' broadcasts)
        // console.log('[Admin Dashboard] Subscribing to public channel: admin-dashboard');
        adminPublicChannel = pusher.subscribe('admin-dashboard');

        adminPublicChannel.bind('pusher:subscription_succeeded', () => {
            // console.log('[Admin Dashboard] ✅ Successfully subscribed to admin-dashboard');
        });

        adminPublicChannel.bind('pusher:subscription_error', (status) => {
            // console.error('[Admin Dashboard] ❌ Subscription error on admin-dashboard:', status);
        });

        // Listen for dashboard.updated events on public channel
        adminPublicChannel.bind('dashboard.updated', function(eventData) {
            // console.log('[Admin Dashboard] ⚡⚡⚡ RECEIVED dashboard.updated on public channel! ⚡⚡⚡');
            // console.log('[Admin Dashboard] Full event data:', JSON.stringify(eventData, null, 2));
            // console.log('[Admin Dashboard] Event data structure:', {
            //     has_data: !!eventData.data,
            //     has_metrics: !!(eventData.data && eventData.data.metrics),
            //     has_type: !!eventData.type,
            //     type_value: eventData.type,
            //     data_type: eventData.data?.type,
            //     data_keys: eventData.data ? Object.keys(eventData.data) : [],
            //     metrics_keys: eventData.data?.metrics ? Object.keys(eventData.data.metrics) : []
            // });
            handleDashboardUpdate(eventData);
        });

        // Try to subscribe to private channel if admin ID is available
        const adminId = window.ADMIN_ID;
        if (adminId) {
            const privateChannelName = 'private-admin-dashboard.' + adminId;
            // console.log('[Admin Dashboard] Subscribing to private channel:', privateChannelName);
            
            adminPrivateChannel = pusher.subscribe(privateChannelName);

            adminPrivateChannel.bind('pusher:subscription_succeeded', () => {
                // console.log('[Admin Dashboard] ✅ Successfully subscribed to', privateChannelName);
            });

            adminPrivateChannel.bind('pusher:subscription_error', (status) => {
                // console.error('[Admin Dashboard] ❌ Subscription error on', privateChannelName + ':', status);
            });

            // Listen for dashboard.updated events on private channel
            adminPrivateChannel.bind('dashboard.updated', function(eventData) {
                // console.log('[Admin Dashboard] ⚡⚡⚡ RECEIVED dashboard.updated on private channel! ⚡⚡⚡');
                // console.log('[Admin Dashboard] Full event data:', JSON.stringify(eventData, null, 2));
                // console.log('[Admin Dashboard] Event data structure:', {
                //     has_data: !!eventData.data,
                //     has_metrics: !!(eventData.data && eventData.data.metrics),
                //     has_type: !!eventData.type,
                //     type_value: eventData.type,
                //     data_type: eventData.data?.type,
                //     data_keys: eventData.data ? Object.keys(eventData.data) : [],
                //     metrics_keys: eventData.data?.metrics ? Object.keys(eventData.data.metrics) : []
                // });
                handleDashboardUpdate(eventData);
            });
        }
    }

    // Handle dashboard update event
    function handleDashboardUpdate(eventData) {
        if (!eventData) {
            // console.log('[Admin Dashboard] ❌ Invalid event data - eventData is null/undefined');
            return;
        }

        // console.log('[Admin Dashboard] 🔄 Processing dashboard update...');
        // console.log('[Admin Dashboard] Raw eventData:', eventData);

        // Extract metrics from the event data structure
        let metrics = null;
        
        // Handle different event data structures
        if (eventData.data && eventData.data.metrics) {
            // New structure: { data: { metrics: {...} }, type: 'sales_update' }
            // console.log('[Admin Dashboard] ✅ Found metrics in eventData.data.metrics');
            metrics = eventData.data.metrics;
        } else if (eventData.data && typeof eventData.data === 'object' && eventData.type === 'sales_update') {
            // Alternative structure: { data: {...}, type: 'sales_update' }
            // console.log('[Admin Dashboard] ✅ Using eventData.data as metrics (alternative structure)');
            metrics = eventData.data;
        } else if (eventData.metrics) {
            // Legacy structure: { metrics: {...} }
            // console.log('[Admin Dashboard] ✅ Found metrics in eventData.metrics (legacy structure)');
            metrics = eventData.metrics;
        } else {
            // console.log('[Admin Dashboard] ⚠️ No metrics found in eventData');
            // console.log('[Admin Dashboard] Available keys:', Object.keys(eventData));
        }

        // Handle commerce data (for dashboard cards)
        if (eventData.data && eventData.data.commerce) {
            // console.log('[Admin Dashboard] 💼 Found commerce data, updating commerce cards');
            updateCommerceCards(eventData.data.commerce);
        }

        // Only process if type is sales_update
        const eventType = eventData.type || eventData.data?.type;
        // console.log('[Admin Dashboard] Event type check:', {
        //     eventType: eventType,
        //     expectedType: 'sales_update',
        //     typeMatch: eventType === 'sales_update',
        //     hasMetrics: !!metrics
        // });

        if (eventType === 'sales_update' && metrics) {
            // console.log('[Admin Dashboard] ✅ Processing sales update with metrics:', metrics);
            updateAdminDashboardCards(metrics);
        } else {
            // console.log('[Admin Dashboard] ⚠️ Not processing update:', {
            //     reason: !eventType ? 'No event type' : eventType !== 'sales_update' ? 'Wrong event type' : 'No metrics',
            //     eventType: eventType,
            //     hasMetrics: !!metrics
            // });
        }
    }

    function updateAdminDashboardCards(metrics) {
        if (!metrics) {
            // console.log('[Admin Dashboard] ❌ No metrics provided to updateAdminDashboardCards');
            return;
        }

        // console.log('[Admin Dashboard] 📊 Updating dashboard cards with metrics:', metrics);
        // console.log('[Admin Dashboard] Available metric keys:', Object.keys(metrics));

        // Update commerce cards (gross and net revenue)
        const ticketRevenue = metrics.ticketRevenue || 0;
        const subRevenue = metrics.subRevenue || 0;
        const ticketLimitRevenue = metrics.ticketLimitRevenue || 0;
        const ticketNetRevenue = metrics.ticketNetRevenue || 0;
        const subNetRevenue = metrics.subNetRevenue || 0;
        const ticketLimitNetRevenue = metrics.ticketLimitNetRevenue || 0;

        // console.log('[Admin Dashboard] Revenue values:', {
        //     ticketRevenue,
        //     subRevenue,
        //     ticketLimitRevenue,
        //     ticketNetRevenue,
        //     subNetRevenue,
        //     ticketLimitNetRevenue
        // });

        if (ticketRevenue !== 0 || subRevenue !== 0 || ticketLimitRevenue !== 0) {
            const gross = ticketRevenue + subRevenue + ticketLimitRevenue;
            const net = ticketNetRevenue + subNetRevenue + ticketLimitNetRevenue;
            
            // console.log('[Admin Dashboard] 💵 Calculating totals:', { gross, net });
            
            const grossEl = document.querySelector('[data-stat="gross"]');
            if (grossEl) {
                const oldGross = grossEl.textContent;
                grossEl.textContent = '₱' + parseFloat(gross).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                // console.log('[Admin Dashboard] ✏️ Updated gross revenue:', { old: oldGross, new: grossEl.textContent });
                animateCounter(grossEl);
            } else {
                // console.warn('[Admin Dashboard] ❌ Element not found for [data-stat="gross"]');
            }
            
            const netEl = document.querySelector('[data-stat="net"]');
            if (netEl) {
                const oldNet = netEl.textContent;
                netEl.textContent = '₱' + parseFloat(net).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                // console.log('[Admin Dashboard] ✏️ Updated net revenue:', { old: oldNet, new: netEl.textContent });
                animateCounter(netEl);
            } else {
                // console.warn('[Admin Dashboard] ❌ Element not found for [data-stat="net"]');
                // console.warn('[Admin Dashboard] Available data-stat elements:', 
                //     Array.from(document.querySelectorAll('[data-stat]')).map(el => el.getAttribute('data-stat'))
                // );
            }
        } else {
            // console.log('[Admin Dashboard] ⚠️ No revenue values found in metrics');
        }

        // Trigger refresh for other metrics (charts, etc.)
        if (typeof refreshMetrics === 'function') {
            // console.log('[Admin Dashboard] 🔄 Calling refreshMetrics() function');
            setTimeout(() => refreshMetrics(), 500);
        } else {
            // console.warn('[Admin Dashboard] ⚠️ refreshMetrics function not found');
        }
    }

    function updateCommerceCards(commerce) {
        if (!commerce) return;

        // console.log('[Admin Dashboard] Updating commerce cards:', commerce);
        
        Object.entries(commerce).forEach(([key, value]) => {
            const el = document.querySelector(`[data-stat="${key}"]`);
            if (el) {
                el.textContent = (typeof value === 'number') 
                    ? '₱' + parseFloat(value).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) 
                    : value;
                animateCounter(el);
            }
        });
    }

    function animateCounter(element) {
        if (!element) return;
        element.style.transform = 'scale(1.1)';
        element.style.color = '#10b981';
        element.style.transition = 'all 0.3s ease';
        setTimeout(() => {
            element.style.transform = 'scale(1)';
            element.style.color = '';
        }, 150);
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
            // console.log('[Admin Dashboard] Disconnecting Pusher...');
            pusher.disconnect();
        }
    });
})();

