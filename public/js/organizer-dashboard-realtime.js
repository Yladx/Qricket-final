(function() {
    'use strict';
    
    let pusher = null;
    let isConnected = false;
    let publicChannel = null;
    let privateChannel = null;
    let revenueChart = null;

    // Initialize real-time updates
    function initRealtimeUpdates() {
        // Check Pusher availability
        if (typeof Pusher === 'undefined') {
            // console.error('[Organizer Dashboard] ❌ Pusher library not loaded');
            return;
        }

        // Check Pusher key
        if (!window.PUSHER_APP_KEY || window.PUSHER_APP_KEY === '' || window.PUSHER_APP_KEY === 'undefined') {
            // console.error('[Organizer Dashboard] ❌ PUSHER_APP_KEY not configured');
            return;
        }

        // Get user ID
        const userId = window.USER_ID;
        if (!userId) {
            // console.error('[Organizer Dashboard] ❌ USER_ID not set');
            return;
        }

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const config = {
            cluster: window.PUSHER_APP_CLUSTER || 'mt1',
            forceTLS: true,
            enableTransports: ['ws', 'wss', 'xhr_streaming', 'xhr_polling'],
            disableStats: true,
            authEndpoint: window.PUSHER_AUTH_ENDPOINT || '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': csrf || ''
                }
            }
        };

        // console.log('[Organizer Dashboard] Creating Pusher instance...', {
            pusherKey: window.PUSHER_APP_KEY ? window.PUSHER_APP_KEY.substring(0, 10) + '...' : 'NOT SET',
            cluster: window.PUSHER_APP_CLUSTER || 'mt1',
            authEndpoint: window.PUSHER_AUTH_ENDPOINT || '/broadcasting/auth',
            userId: userId,
            csrfToken: csrf ? 'SET' : 'NOT SET'
        });
        pusher = new Pusher(window.PUSHER_APP_KEY, config);

        // Connection event handlers
        pusher.connection.bind('connecting', () => {
            // console.log('[Organizer Dashboard] 🔌 Pusher connecting...');
        });

        pusher.connection.bind('connected', () => {
            // console.log('[Organizer Dashboard] ✅ Pusher connected successfully');
            isConnected = true;
        });

        pusher.connection.bind('disconnected', () => {
            // console.warn('[Organizer Dashboard] ⚠️ Pusher disconnected');
            isConnected = false;
        });

        pusher.connection.bind('error', (error) => {
            // console.error('[Organizer Dashboard] ❌ Pusher error:', error);
            isConnected = false;
        });

        // Subscribe to public sales-updates channel
        // console.log('[Organizer Dashboard] Subscribing to public channel: sales-updates');
        publicChannel = pusher.subscribe('sales-updates');

        publicChannel.bind('pusher:subscription_succeeded', () => {
            // console.log('[Organizer Dashboard] ✅ Successfully subscribed to sales-updates');
        });

        publicChannel.bind('pusher:subscription_error', (status) => {
            // console.error('[Organizer Dashboard] ❌ Subscription error on sales-updates:', status);
        });

        // Listen for sales.report.updated events on public channel
        publicChannel.bind('sales.report.updated', function(eventData) {
            // console.log('[Organizer Dashboard] ⚡⚡⚡ RECEIVED sales.report.updated on public channel! ⚡⚡⚡');
            // console.log('[Organizer Dashboard] Full event data:', JSON.stringify(eventData, null, 2));
            // console.log('[Organizer Dashboard] Checking organizer_id match:', {
                received_organizer_id: eventData.organizer_id,
                current_userId: userId,
                match: eventData.organizer_id === userId || eventData.organizer_id === 0
            });
            
            // Only update if it's for this organizer's events
            if (eventData.organizer_id === userId || eventData.organizer_id === 0) {
                console.log('[Organizer Dashboard] ✅ Organizer ID matches, processing update...');
                handleSalesUpdate(eventData);
            } else {
                console.log('[Organizer Dashboard] ❌ Organizer ID does not match, skipping update');
            }
        });

        // Subscribe to private organizer dashboard channel
        const privateChannelName = 'private-organizer-dashboard.' + userId;
        console.log('[Organizer Dashboard] Subscribing to private channel:', privateChannelName);
        
        privateChannel = pusher.subscribe(privateChannelName);

        privateChannel.bind('pusher:subscription_succeeded', () => {
            console.log('[Organizer Dashboard] ✅ Successfully subscribed to', privateChannelName);
        });

        privateChannel.bind('pusher:subscription_error', (status) => {
            console.error('[Organizer Dashboard] ❌ Subscription error on', privateChannelName + ':', status);
        });

        // Listen for sales.report.updated events on private channel
        privateChannel.bind('sales.report.updated', function(eventData) {
            console.log('[Organizer Dashboard] ⚡⚡⚡ RECEIVED sales.report.updated on private channel! ⚡⚡⚡');
            console.log('[Organizer Dashboard] Full event data:', JSON.stringify(eventData, null, 2));
            console.log('[Organizer Dashboard] Processing private channel update...');
            handleSalesUpdate(eventData);
        });

        // Listen for dashboard.stats.updated events
        privateChannel.bind('dashboard.stats.updated', function(eventData) {
            console.log('[Organizer Dashboard] ⚡⚡⚡ RECEIVED dashboard.stats.updated! ⚡⚡⚡', eventData);
            handleDashboardStatsUpdate(eventData);
        });
    }

    // Handle sales update event
    function handleSalesUpdate(eventData) {
        if (!eventData) {
            console.log('[Organizer Dashboard] ❌ Invalid event data - eventData is null/undefined');
            return;
        }

        console.log('[Organizer Dashboard] 🔄 Processing sales update...');
        console.log('[Organizer Dashboard] Event data structure:', {
            has_event_stats: !!eventData.event_stats,
            has_sales_data: !!eventData.sales_data,
            event_id: eventData.event_id,
            organizer_id: eventData.organizer_id,
            event_stats_keys: eventData.event_stats ? Object.keys(eventData.event_stats) : [],
            sales_data_keys: eventData.sales_data ? Object.keys(eventData.sales_data) : []
        });

        // Update metrics from event stats
        if (eventData.event_stats) {
            console.log('[Organizer Dashboard] 📊 Updating metrics from event_stats:', eventData.event_stats);
            updateMetricsFromEventStats(eventData.event_stats);
        } else {
            console.log('[Organizer Dashboard] ⚠️ No event_stats in event data');
        }

        // Update event-specific revenue
        if (eventData.event_id && eventData.sales_data) {
            console.log('[Organizer Dashboard] 💰 Updating event-specific revenue for event:', eventData.event_id);
            updateEventRevenue(eventData.event_id, eventData.sales_data);
        } else {
            console.log('[Organizer Dashboard] ⚠️ Missing event_id or sales_data:', {
                has_event_id: !!eventData.event_id,
                has_sales_data: !!eventData.sales_data
            });
        }

        // Update charts if they exist
        if (eventData.sales_data && typeof window.updateRevenueChart === 'function') {
            setTimeout(() => {
                if (typeof refreshOrganizerMetrics === 'function') {
                    refreshOrganizerMetrics();
                }
            }, 500);
        }
    }

    // Handle dashboard stats update
    function handleDashboardStatsUpdate(eventData) {
        if (!eventData || !eventData.stats) {
            console.log('[Organizer Dashboard] Invalid dashboard stats data');
            return;
        }

        console.log('[Organizer Dashboard] Processing dashboard stats update:', eventData.stats);

        // Update metrics from stats
        if (eventData.stats.totalRevenue !== undefined) {
            updateElement('totalRevenue', '₱' + parseFloat(eventData.stats.totalRevenue).toLocaleString('en-US', {minimumFractionDigits: 2}));
        }

        if (eventData.stats.totalTicketsSold !== undefined) {
            updateElement('totalTicketsSold', eventData.stats.totalTicketsSold.toLocaleString());
        }

        // Update revenue chart if stats include trend data
        if (eventData.stats.revenueTrend && typeof window.updateRevenueChart === 'function') {
            window.updateRevenueChart(eventData.stats.revenueTrend);
        }

        // Refresh full metrics
        if (typeof refreshOrganizerMetrics === 'function') {
            setTimeout(() => refreshOrganizerMetrics(), 500);
        }
    }

    function updateMetricsFromEventStats(eventStats) {
        if (!eventStats) {
            console.log('[Organizer Dashboard] ❌ updateMetricsFromEventStats: eventStats is null/undefined');
            return;
        }

        console.log('[Organizer Dashboard] 📈 Updating metrics from event stats:', eventStats);

        // Update total tickets sold
        if (eventStats.total_tickets_sold !== undefined) {
            const newValue = eventStats.total_tickets_sold.toLocaleString();
            console.log('[Organizer Dashboard] 🎫 Updating totalTicketsSold to:', newValue);
            updateElement('totalTicketsSold', newValue);
        } else {
            console.log('[Organizer Dashboard] ⚠️ total_tickets_sold not in event_stats');
        }

        // Update total revenue
        if (eventStats.total_revenue !== undefined) {
            const newValue = '₱' + parseFloat(eventStats.total_revenue).toLocaleString('en-US', {minimumFractionDigits: 2});
            console.log('[Organizer Dashboard] 💵 Updating totalRevenue to:', newValue);
            updateElement('totalRevenue', newValue);
        } else {
            console.log('[Organizer Dashboard] ⚠️ total_revenue not in event_stats');
        }
    }

    function updateEventRevenue(eventId, salesData) {
        if (!eventId || !salesData) return;

        // Update event-specific revenue in the events list
        if (salesData.total_amount !== undefined) {
            const eventRevenueEl = document.querySelector('[data-stat="revenue-' + eventId + '"]');
            if (eventRevenueEl) {
                eventRevenueEl.textContent = '₱' + parseFloat(salesData.total_amount).toLocaleString('en-US', {minimumFractionDigits: 2});
                animateCounter(eventRevenueEl);
            }
        }

        // Update event-specific tickets sold
        if (salesData.quantity !== undefined) {
            const ticketsEl = document.querySelector('[data-stat="tickets-sold-' + eventId + '"]');
            if (ticketsEl) {
                ticketsEl.textContent = salesData.quantity.toLocaleString() + ' tickets';
                animateCounter(ticketsEl);
            }
        }
    }

    function updateElement(selector, newValue) {
        const element = document.querySelector(`[data-stat="${selector}"]`);
        if (element) {
            const oldValue = element.textContent;
            console.log('[Organizer Dashboard] ✏️ Updating element:', {
                selector: selector,
                oldValue: oldValue,
                newValue: newValue
            });
            animateCounter(element);
            element.textContent = newValue;
            console.log('[Organizer Dashboard] ✅ Element updated successfully');
        } else {
            console.warn('[Organizer Dashboard] ❌ Element not found for selector:', selector);
            console.warn('[Organizer Dashboard] Available data-stat elements:', 
                Array.from(document.querySelectorAll('[data-stat]')).map(el => el.getAttribute('data-stat'))
            );
        }
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
            console.log('[Organizer Dashboard] Disconnecting Pusher...');
            pusher.disconnect();
        }
    });
})();

