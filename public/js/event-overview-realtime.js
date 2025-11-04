(function() {
    'use strict';
    
    let pusher = null;
    let isConnected = false;
    let publicChannel = null;
    let privateChannel = null;
    let eventChannel = null;
    let eventId = null;
    let userId = null;

    // Initialize real-time updates
    function initRealtimeUpdates() {
        // Check Pusher availability
        if (typeof Pusher === 'undefined') {
            console.error('[Event Overview] ❌ Pusher library not loaded');
            return;
        }

        // Check Pusher key
        if (!window.PUSHER_APP_KEY || window.PUSHER_APP_KEY === '' || window.PUSHER_APP_KEY === 'undefined') {
            console.error('[Event Overview] ❌ PUSHER_APP_KEY not configured');
            return;
        }

        // Get event ID and user ID
        eventId = window.EVENT_ID;
        userId = window.USER_ID;

        if (!eventId) {
            console.error('[Event Overview] ❌ EVENT_ID not set');
            return;
        }

        if (!userId) {
            console.error('[Event Overview] ❌ USER_ID not set');
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

        console.log('[Event Overview] Creating Pusher instance...', {
            eventId: eventId,
            userId: userId,
            pusherKey: window.PUSHER_APP_KEY ? window.PUSHER_APP_KEY.substring(0, 10) + '...' : 'NOT SET',
            cluster: window.PUSHER_APP_CLUSTER || 'mt1',
            authEndpoint: window.PUSHER_AUTH_ENDPOINT || '/broadcasting/auth',
            csrfToken: csrf ? 'SET' : 'NOT SET'
        });
        pusher = new Pusher(window.PUSHER_APP_KEY, config);

        // Connection event handlers
        pusher.connection.bind('connecting', () => {
            console.log('[Event Overview] 🔌 Pusher connecting...');
        });

        pusher.connection.bind('connected', () => {
            console.log('[Event Overview] ✅ Pusher connected successfully');
            isConnected = true;
        });

        pusher.connection.bind('disconnected', () => {
            console.warn('[Event Overview] ⚠️ Pusher disconnected');
            isConnected = false;
        });

        pusher.connection.bind('error', (error) => {
            console.error('[Event Overview] ❌ Pusher error:', error);
            isConnected = false;
        });

        // Subscribe to public sales-updates channel
        console.log('[Event Overview] Subscribing to public channel: sales-updates');
        publicChannel = pusher.subscribe('sales-updates');

        publicChannel.bind('pusher:subscription_succeeded', () => {
            console.log('[Event Overview] ✅ Successfully subscribed to sales-updates');
        });

        publicChannel.bind('pusher:subscription_error', (status) => {
            console.error('[Event Overview] ❌ Subscription error on sales-updates:', status);
        });

        // Listen for sales.report.updated events on public channel
        publicChannel.bind('sales.report.updated', function(eventData) {
            console.log('[Event Overview] ⚡⚡⚡ RECEIVED sales.report.updated on public channel! ⚡⚡⚡');
            console.log('[Event Overview] Full event data:', JSON.stringify(eventData, null, 2));
            console.log('[Event Overview] Checking event_id match:', {
                received_event_id: eventData.event_id,
                current_eventId: eventId,
                match: eventData.event_id == eventId,
                type_match: typeof eventData.event_id === typeof eventId
            });
            
            // Only update if it's for this event
            if (eventData.event_id == eventId) {
                console.log('[Event Overview] ✅ Event ID matches, processing update...');
                handleSalesUpdate(eventData);
            } else {
                console.log('[Event Overview] ❌ Event ID does not match, skipping update');
            }
        });

        // Subscribe to private organizer dashboard channel
        const privateChannelName = 'private-organizer-dashboard.' + userId;
        console.log('[Event Overview] Subscribing to private channel:', privateChannelName);
        
        privateChannel = pusher.subscribe(privateChannelName);

        privateChannel.bind('pusher:subscription_succeeded', () => {
            console.log('[Event Overview] ✅ Successfully subscribed to', privateChannelName);
        });

        privateChannel.bind('pusher:subscription_error', (status) => {
            console.error('[Event Overview] ❌ Subscription error on', privateChannelName + ':', status);
        });

        // Listen for sales.report.updated events on private channel
        privateChannel.bind('sales.report.updated', function(eventData) {
            console.log('[Event Overview] ⚡⚡⚡ RECEIVED sales.report.updated on private channel! ⚡⚡⚡');
            console.log('[Event Overview] Full event data:', JSON.stringify(eventData, null, 2));
            console.log('[Event Overview] Checking event_id match:', {
                received_event_id: eventData.event_id,
                current_eventId: eventId,
                match: eventData.event_id == eventId
            });
            
            // Only update if it's for this event
            if (eventData.event_id == eventId) {
                console.log('[Event Overview] ✅ Event ID matches, processing update...');
                handleSalesUpdate(eventData);
            } else {
                console.log('[Event Overview] ❌ Event ID does not match, skipping update');
            }
        });

        // Subscribe to event-specific channel if available
        const eventChannelName = 'private-event.' + eventId;
        console.log('[Event Overview] Subscribing to event channel:', eventChannelName);
        
        eventChannel = pusher.subscribe(eventChannelName);

        eventChannel.bind('pusher:subscription_succeeded', () => {
            console.log('[Event Overview] ✅ Successfully subscribed to', eventChannelName);
        });

        eventChannel.bind('pusher:subscription_error', (status) => {
            console.error('[Event Overview] ❌ Subscription error on', eventChannelName + ':', status);
        });

        // Listen for event-specific events
        eventChannel.bind('event.stats.updated', function(eventData) {
            console.log('[Event Overview] ⚡⚡⚡ RECEIVED event.stats.updated! ⚡⚡⚡', eventData);
            handleEventStatsUpdate(eventData);
        });
    }

    // Handle sales update event
    function handleSalesUpdate(eventData) {
        if (!eventData) {
            console.log('[Event Overview] ❌ Invalid event data - eventData is null/undefined');
            return;
        }

        console.log('[Event Overview] 🔄 Processing sales update...');
        console.log('[Event Overview] Event data structure:', {
            has_event_stats: !!eventData.event_stats,
            has_sales_data: !!eventData.sales_data,
            event_id: eventData.event_id,
            organizer_id: eventData.organizer_id,
            event_stats_keys: eventData.event_stats ? Object.keys(eventData.event_stats) : [],
            sales_data_keys: eventData.sales_data ? Object.keys(eventData.sales_data) : []
        });

        // Update event metrics
        if (eventData.event_stats) {
            console.log('[Event Overview] 📊 Updating event metrics from event_stats:', eventData.event_stats);
            updateEventMetrics(eventData.event_stats);
        } else {
            console.log('[Event Overview] ⚠️ No event_stats in event data');
        }

        // Update sales data
        if (eventData.sales_data) {
            console.log('[Event Overview] 💰 Updating sales data:', eventData.sales_data);
            updateSalesData(eventData.sales_data);
        } else {
            console.log('[Event Overview] ⚠️ No sales_data in event data');
        }

        // Always refresh charts when sales data is received
        console.log('[Event Overview] 🔄 Refreshing charts and metrics...');
        
        // Refresh sales trend chart - try to get trend from sales summary first
        console.log('[Event Overview] 📈 Refreshing sales trend chart...');
        if (eventId) {
            // Fetch fresh sales summary which includes trend data
            fetch(`/organizer/events/${eventId}/api/sales-summary`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.trend && typeof window.updateSalesTrendChart === 'function') {
                    console.log('[Event Overview] ✅ Sales trend data received, updating chart:', data.trend);
                    // Transform trend data to match chart expectations
                    const chartData = data.trend.map(day => ({
                        date: day.date,
                        display_date: day.display_date || day.date,
                        revenue: day.revenue || day.daily_revenue || 0,
                        tickets_sold: day.tickets_sold || day.sales || day.sales_count || 0
                    }));
                    window.updateSalesTrendChart(chartData);
                } else if (typeof window.fetchSalesTrendData === 'function') {
                    // Fallback to existing function if available
                    console.log('[Event Overview] 📈 Calling fetchSalesTrendData() as fallback');
                    window.fetchSalesTrendData();
                } else {
                    console.warn('[Event Overview] ⚠️ No sales trend data available and fetchSalesTrendData not found');
                }
            })
            .catch(error => {
                console.error('[Event Overview] ❌ Failed to fetch sales trend:', error);
                // Fallback to existing function if available
                if (typeof window.fetchSalesTrendData === 'function') {
                    window.fetchSalesTrendData();
                }
            });
        } else {
            // Fallback to existing function if eventId not available
            if (typeof window.fetchSalesTrendData === 'function') {
                window.fetchSalesTrendData();
            }
        }

        // Refresh activity trend chart
        if (typeof window.fetchActivityTrendData === 'function') {
            console.log('[Event Overview] 📊 Calling fetchActivityTrendData() to refresh activity trend chart');
            setTimeout(() => window.fetchActivityTrendData(), 300);
        }

        // Refresh ticket performance table
        refreshTicketPerformanceTable();

        // Refresh recent sales
        refreshRecentSalesList();

        // Refresh full metrics (event numbers)
        if (typeof refreshEventNumbers === 'function') {
            console.log('[Event Overview] 📊 Calling refreshEventNumbers() to update all event metrics');
            setTimeout(() => refreshEventNumbers(), 600);
        } else {
            console.warn('[Event Overview] ⚠️ refreshEventNumbers function not found');
        }
    }

    // Handle event stats update
    function handleEventStatsUpdate(eventData) {
        if (!eventData) {
            console.log('[Event Overview] Invalid event stats data');
            return;
        }

        console.log('[Event Overview] Processing event stats update:', eventData);

        // Update event metrics from stats
        if (eventData.total_revenue !== undefined) {
            updateElement('event-total-revenue', '₱' + parseFloat(eventData.total_revenue).toLocaleString('en-US', {minimumFractionDigits: 2}));
        }

        if (eventData.total_tickets_sold !== undefined) {
            updateElement('event-tickets-sold', eventData.total_tickets_sold.toLocaleString());
        }

        if (eventData.total_orders !== undefined) {
            updateElement('event-total-orders', eventData.total_orders.toLocaleString());
        }

        if (eventData.expected_revenue !== undefined) {
            const safeValue = Math.max(0, parseFloat(eventData.expected_revenue));
            updateElement('event-expected-revenue', '₱' + safeValue.toLocaleString('en-US', {minimumFractionDigits: 2}));
        }

        // Update charts if data provided
        if (eventData.sales_trend && typeof window.updateSalesTrendChart === 'function') {
            window.updateSalesTrendChart(eventData.sales_trend);
        }

        if (eventData.activity_trend && typeof window.updateActivityTrendChart === 'function') {
            window.updateActivityTrendChart(eventData.activity_trend);
        }
    }

    function updateEventMetrics(eventStats) {
        if (!eventStats) {
            console.log('[Event Overview] ❌ updateEventMetrics: eventStats is null/undefined');
            return;
        }

        console.log('[Event Overview] 📈 Updating event metrics from stats:', eventStats);

        // Update total revenue
        if (eventStats.total_revenue !== undefined) {
            const newValue = '₱' + parseFloat(eventStats.total_revenue).toLocaleString('en-US', {minimumFractionDigits: 2});
            console.log('[Event Overview] 💵 Updating event-total-revenue to:', newValue);
            updateElement('event-total-revenue', newValue);
        } else {
            console.log('[Event Overview] ⚠️ total_revenue not in event_stats');
        }

        // Update tickets sold
        if (eventStats.total_tickets_sold !== undefined) {
            const newValue = eventStats.total_tickets_sold.toLocaleString();
            console.log('[Event Overview] 🎫 Updating event-tickets-sold to:', newValue);
            updateElement('event-tickets-sold', newValue);
        } else {
            console.log('[Event Overview] ⚠️ total_tickets_sold not in event_stats');
        }

        // Update total orders (increment)
        if (eventStats.total_orders !== undefined) {
            const ordersEl = document.querySelector('[data-stat="event-total-orders"]');
            if (ordersEl) {
                const currentOrders = parseInt(ordersEl.textContent.replace(/,/g, '')) || 0;
                const newOrders = eventStats.total_orders;
                if (newOrders > currentOrders) {
                    updateElement('event-total-orders', newOrders.toLocaleString());
                }
            }
        }

        // Update expected revenue
        if (eventStats.expected_revenue !== undefined) {
            const safeValue = Math.max(0, parseFloat(eventStats.expected_revenue));
            updateElement('event-expected-revenue', '₱' + safeValue.toLocaleString('en-US', {minimumFractionDigits: 2}));
        }
    }

    function updateSalesData(salesData) {
        if (!salesData) return;

        // Update ticket monitor if available
        if (salesData.recent_purchases && Array.isArray(salesData.recent_purchases)) {
            updateTicketMonitor(salesData.recent_purchases);
        }
    }

    function updateTicketMonitor(recentPurchases) {
        // Find ticket monitor container
        const monitorContainer = document.querySelector('#ticket-monitor-container, #recent-purchases-container');
        if (!monitorContainer) return;

        console.log('[Event Overview] Updating ticket monitor with', recentPurchases.length, 'purchases');
        
        // Clear existing items (or append new ones based on your UI preference)
        // This is a placeholder - adjust based on your actual ticket monitor structure
        recentPurchases.forEach((purchase) => {
            // Create or update ticket monitor entry
            const existing = monitorContainer.querySelector(`[data-purchase-id="${purchase.id}"]`);
            if (!existing) {
                // Add new purchase entry
                const entry = document.createElement('div');
                entry.setAttribute('data-purchase-id', purchase.id);
                entry.className = 'border-l-4 border-green-500 pl-4 mb-2';
                entry.style.opacity = '0';
                entry.style.transform = 'translateX(-10px)';
                entry.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                
                entry.innerHTML = `
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">${escapeHtml(purchase.buyer_name || 'Guest')}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">${purchase.created_at || 'Just now'}</span>
                    </div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">${purchase.quantity || 1} ticket(s) - ₱${parseFloat(purchase.total_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</div>
                `;
                
                monitorContainer.insertBefore(entry, monitorContainer.firstChild);
                
                // Animate in
                setTimeout(() => {
                    entry.style.opacity = '1';
                    entry.style.transform = 'translateX(0)';
                }, 10);
                
                // Limit to last 10 entries
                const entries = monitorContainer.querySelectorAll('[data-purchase-id]');
                if (entries.length > 10) {
                    entries[entries.length - 1].remove();
                }
            }
        });
    }

    function updateElement(selector, newValue) {
        const element = document.querySelector(`[data-stat="${selector}"]`);
        if (element) {
            const oldValue = element.textContent;
            console.log('[Event Overview] ✏️ Updating element:', {
                selector: selector,
                oldValue: oldValue,
                newValue: newValue
            });
            animateCounter(element);
            element.textContent = newValue;
            console.log('[Event Overview] ✅ Element updated successfully');
        } else {
            console.warn('[Event Overview] ❌ Element not found for selector:', selector);
            console.warn('[Event Overview] Available data-stat elements:', 
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

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Refresh ticket performance table
    function refreshTicketPerformanceTable() {
        if (!eventId) {
            console.warn('[Event Overview] ⚠️ Cannot refresh ticket performance - eventId not set');
            return;
        }

        console.log('[Event Overview] 🎫 Fetching ticket performance data for event:', eventId);
        
        const url = `/organizer/events/${eventId}/api/sales-summary`;
        fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.ticket_performance) {
                console.log('[Event Overview] ✅ Ticket performance data received:', data.ticket_performance);
                updateTicketPerformanceTable(data.ticket_performance);
            } else {
                console.warn('[Event Overview] ⚠️ Invalid ticket performance data structure');
            }
        })
        .catch(error => {
            console.error('[Event Overview] ❌ Failed to fetch ticket performance:', error);
        });
    }

    // Update ticket performance table in DOM
    function updateTicketPerformanceTable(ticketPerformance) {
        const tableBody = document.querySelector('#ticket-performance-table tbody');
        if (!tableBody) {
            console.warn('[Event Overview] ⚠️ Ticket performance table tbody not found');
            return;
        }

        console.log('[Event Overview] 📊 Updating ticket performance table with', ticketPerformance.length, 'tickets');

        // Clear existing rows
        tableBody.innerHTML = '';

        // Add new rows
        ticketPerformance.forEach(ticket => {
            const row = document.createElement('tr');
            const remaining = ticket.remaining !== null && ticket.remaining !== undefined 
                ? Number(ticket.remaining).toLocaleString() 
                : '<span class="text-green-600 dark:text-green-400">Unlimited</span>';
            
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                    ${escapeHtml(ticket.name)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    ₱${Number(ticket.price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    ${Number(ticket.total_sold).toLocaleString()}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    ${remaining}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: ${Math.min(ticket.utilization, 100)}%"></div>
                        </div>
                        <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">
                            ${Number(ticket.utilization).toLocaleString('en-US', {minimumFractionDigits: 1, maximumFractionDigits: 1})}%
                        </span>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    ₱${Number(ticket.total_revenue).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                </td>
            `;
            
            tableBody.appendChild(row);
        });

        console.log('[Event Overview] ✅ Ticket performance table updated successfully');
    }

    // Refresh recent sales list
    function refreshRecentSalesList() {
        if (!eventId) {
            console.warn('[Event Overview] ⚠️ Cannot refresh recent sales - eventId not set');
            return;
        }

        console.log('[Event Overview] 💰 Fetching recent sales data for event:', eventId);
        
        const url = `/organizer/events/${eventId}/api/recent-sales`;
        fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.sales) {
                console.log('[Event Overview] ✅ Recent sales data received:', data.sales.length, 'sales');
                updateRecentSalesList(data.sales);
            } else {
                console.warn('[Event Overview] ⚠️ Invalid recent sales data structure');
            }
        })
        .catch(error => {
            console.error('[Event Overview] ❌ Failed to fetch recent sales:', error);
        });
    }

    // Update recent sales list in DOM
    function updateRecentSalesList(recentSales) {
        const salesList = document.querySelector('#recent-sales-list');
        if (!salesList) {
            console.warn('[Event Overview] ⚠️ Recent sales list container not found');
            return;
        }

        console.log('[Event Overview] 📋 Updating recent sales list with', recentSales.length, 'sales');

        // Clear existing items
        salesList.innerHTML = '';

        if (recentSales.length === 0) {
            salesList.innerHTML = `
                <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <p class="text-sm font-medium">No recent sales</p>
                    <p class="text-xs mt-1">Sales will appear here once customers purchase tickets</p>
                </div>
            `;
            return;
        }

        // Show only first 5 sales
        const salesToShow = recentSales.slice(0, 5);
        
        salesToShow.forEach((sale, index) => {
            const saleItem = document.createElement('div');
            saleItem.className = 'p-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200';
            saleItem.setAttribute('data-sale-id', sale.id || index);
            saleItem.style.opacity = '0';
            saleItem.style.transform = 'translateX(-10px)';
            saleItem.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            
            // Use user_name from API (which is properly formatted)
            const userName = sale.user_name || sale.buyer_name || 'Guest';
            const ticketTypeName = sale.ticket_type?.name || sale.ticketType?.name || 'Ticket';
            const quantity = sale.quantity || sale.tickets || 1;
            const totalAmount = sale.total_amount || sale.amount || 0;
            // Format purchase date - prefer paid_at, then purchased_at, then created_at
            const purchaseDate = sale.paid_at || sale.purchased_at || sale.created_at;
            let paidAt = 'Just now';
            if (purchaseDate) {
                try {
                    const date = new Date(purchaseDate);
                    if (!isNaN(date.getTime())) {
                        const now = new Date();
                        const diffMs = now - date;
                        const diffMins = Math.floor(diffMs / 60000);
                        
                        if (diffMins < 1) {
                            paidAt = 'Just now';
                        } else if (diffMins < 60) {
                            paidAt = `${diffMins} min${diffMins > 1 ? 's' : ''} ago`;
                        } else if (diffMins < 1440) {
                            const hours = Math.floor(diffMins / 60);
                            paidAt = `${hours} hour${hours > 1 ? 's' : ''} ago`;
                        } else {
                            paidAt = date.toLocaleString('en-US', { 
                                month: 'short', 
                                day: 'numeric', 
                                hour: 'numeric', 
                                minute: '2-digit' 
                            });
                        }
                    }
                } catch (e) {
                    paidAt = 'Just now';
                }
            }
            
            saleItem.innerHTML = `
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="flex-shrink-0">
                            <div class="w-6 h-6 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                <svg class="w-3 h-3 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-900 dark:text-gray-100">
                                ${escapeHtml(userName)}
                            </p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                ${quantity}x ${escapeHtml(ticketTypeName)}
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-semibold text-green-600 dark:text-green-400">
                            ₱${Number(totalAmount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            ${escapeHtml(paidAt)}
                        </p>
                    </div>
                </div>
            `;
            
            salesList.appendChild(saleItem);
            
            // Animate in
            setTimeout(() => {
                saleItem.style.opacity = '1';
                saleItem.style.transform = 'translateX(0)';
            }, index * 50);
        });

        console.log('[Event Overview] ✅ Recent sales list updated successfully');
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
            console.log('[Event Overview] Disconnecting Pusher...');
            pusher.disconnect();
        }
    });
})();

