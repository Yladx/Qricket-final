document.addEventListener('DOMContentLoaded', function() {
    let pusher = null;
    let updateInterval = null;
    let isConnected = false;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function animateCounter(element, newValue) {
        if (!element) return;
        element.style.transform = 'scale(1.2)';
        element.style.color = '#10b981';
        element.style.transition = 'all 0.3s ease';
        setTimeout(() => {
            element.textContent = newValue;
            element.style.transform = 'scale(1)';
            element.style.color = '';
        }, 150);
    }

    function updateCounters(userCount, guestCount) {
        const loggedUsersEl = document.getElementById('logged-users-count');
        const guestUsersEl = document.getElementById('guest-users-count');
        animateCounter(loggedUsersEl, userCount);
        animateCounter(guestUsersEl, guestCount);
    }

    function fetchCounts() {
        fetch('/admin/dashboard/counts')
            .then(response => response.json())
            .then(data => {
                updateCounters(data.users, data.guests);
            })
            .catch(error => {
                console.error('Failed to fetch user counts:', error);
                if (!isConnected && pusher) {
                    pusher.connect();
                }
            });
    }

    // Metrics panels
    function peso(n){ try { return '₱' + (Number(n||0)).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2}); } catch(e){ return '₱0.00'; } }
    function setText(id, v){ const el = document.getElementById(id); if (el) el.textContent = v; }
    function listInto(id, rows, label){
        const ul = document.getElementById(id);
        if (!ul) return;
        ul.innerHTML = '';
        (rows||[]).forEach(r => {
            const li = document.createElement('li');
            const name = (r.event_id && r.name) ? r.name : (r.ref || 'Direct');
            const count = r.c || r.views || r.tickets || 0;
            li.textContent = `${name} — ${count}`;
            ul.appendChild(li);
        });
    }
    function updateAdminMetrics(metrics) {
        if (!metrics) return;

        // Update traffic metrics
        if (metrics.traffic) {
            setText('m-pv24', (metrics.traffic.pv24h || 0).toLocaleString());
            setText('m-uv24', (metrics.traffic.uv24h || 0).toLocaleString());
            setText('m-dau', (metrics.traffic.dau || 0).toLocaleString());
            setText('m-wau', (metrics.traffic.wau || 0).toLocaleString());
            listInto('m-referrers', metrics.traffic.topReferrers);
        }

        // Update commerce metrics
        if (metrics.commerce) {
            setText('m-gross', peso(metrics.commerce.gross));
            setText('m-net', peso(metrics.commerce.net));
            setText('m-pending', peso(metrics.commerce.pending));
            setText('m-refunds', peso(metrics.commerce.refunds));
            setText('m-failures', (metrics.commerce.failures || 0).toLocaleString());
        }

        // Update conversion metrics
        if (metrics.conversion) {
            setText('m-cvs', ((metrics.conversion.view_save || 0)) + '%');
            setText('m-cso', ((metrics.conversion.save_order || 0)) + '%');
            setText('m-coc', ((metrics.conversion.order_checkin || 0)) + '%');
        }

        // Update event metrics
        if (metrics.events) {
            setText('m-event-stats', `${metrics.events.active || 0} / ${metrics.events.upcoming || 0} / ${metrics.events.pending || 0}`);
            listInto('m-top-selling', metrics.events.topSelling);
            listInto('m-most-viewed', metrics.events.mostViewed);
        }

        // Show notification for significant updates
        showAdminUpdateNotification(metrics);
    }

    function showAdminUpdateNotification(metrics) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Dashboard Updated',
                text: 'New data has been received and metrics have been updated',
                icon: 'info',
                timer: 2000,
                timerProgressBar: true,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
            });
        }
    }

    function handleVerificationUpdate(type) {
        // Refresh verification counts and show notification
        fetchCounts();
        
        if (typeof Swal !== 'undefined') {
            let title, text, icon;
            switch (type) {
                case 'verification_submitted':
                    title = 'New Verification Request';
                    text = 'A new organizer verification request has been submitted';
                    icon = 'info';
                    break;
                case 'verification_approved':
                    title = 'Verification Approved';
                    text = 'An organizer verification request has been approved';
                    icon = 'success';
                    break;
                case 'verification_rejected':
                    title = 'Verification Rejected';
                    text = 'An organizer verification request has been rejected';
                    icon = 'warning';
                    break;
            }
            
            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                timer: 3000,
                timerProgressBar: true,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
            });
        }
    }

    function handleEventApprovalUpdate(type) {
        // Refresh event counts and show notification
        fetchCounts();
        
        if (typeof Swal !== 'undefined') {
            let title, text, icon;
            switch (type) {
                case 'event_approved':
                    title = 'Event Approved';
                    text = 'An event has been approved and published';
                    icon = 'success';
                    break;
                case 'event_rejected':
                    title = 'Event Rejected';
                    text = 'An event has been rejected';
                    icon = 'warning';
                    break;
            }
            
            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                timer: 3000,
                timerProgressBar: true,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
            });
        }
    }

    function handlePayoutUpdate(type) {
        // Refresh payout counts and show notification
        fetchCounts();
        
        if (typeof Swal !== 'undefined') {
            let title, text, icon;
            switch (type) {
                case 'payout_approved':
                    title = 'Payout Approved';
                    text = 'A payout request has been approved';
                    icon = 'success';
                    break;
                case 'payout_rejected':
                    title = 'Payout Rejected';
                    text = 'A payout request has been rejected';
                    icon = 'warning';
                    break;
                case 'payout_released':
                    title = 'Payout Released';
                    text = 'A payout has been released for collection';
                    icon = 'info';
                    break;
            }
            
            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                timer: 3000,
                timerProgressBar: true,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
            });
        }
    }

    function fetchMetrics(){
        fetch('/admin/dashboard/metrics')
            .then(r=>r.json())
            .then(m => {
                updateAdminMetrics(m);
            })
            .catch(console.error);
    }

    // Initialize Pusher for admin dashboard
    if (typeof Pusher !== 'undefined' && window.PUSHER_APP_KEY) {
        const pusherConfig = {
            cluster: window.PUSHER_APP_CLUSTER || 'mt1',
            forceTLS: true,
            enableTransports: ['ws','wss','xhr_streaming','xhr_polling'],
            disableStats: true,
            authEndpoint: window.PUSHER_AUTH_ENDPOINT || '/broadcasting/auth',
            auth: { headers: { 'X-CSRF-TOKEN': csrf } },
        };

        pusher = new Pusher(window.PUSHER_APP_KEY, pusherConfig);

        // Subscribe to admin dashboard channel
        const channel = pusher.subscribe('admin-dashboard');

        channel.bind('user.activity', function(data) {
            if (data && (data.activity === 'count_update' || data.activity === 'session_cleanup')) {
                updateCounters(data.userCount, data.guestCount);
                if (window.userActivityChart) {
                    window.userActivityChart.fetch();
                }
            }
        });

        channel.bind('admin.dashboard.updated', function(data){
            console.log('Admin dashboard update received:', data);
            updateAdminMetrics(data.metrics);
            
            // Handle verification updates
            if (data.type === 'verification_submitted' || data.type === 'verification_approved' || data.type === 'verification_rejected') {
                handleVerificationUpdate(data.type);
            }
            
            // Handle event approval updates
            if (data.type === 'event_approved' || data.type === 'event_rejected') {
                handleEventApprovalUpdate(data.type);
            }
            
            // Handle payout updates
            if (data.type === 'payout_approved' || data.type === 'payout_rejected' || data.type === 'payout_released') {
                handlePayoutUpdate(data.type);
            }
        });

        pusher.connection.bind('connected', function() {
            isConnected = true;
            const indicator = document.getElementById('connection-indicator');
            const status = document.getElementById('connection-status');
            if (indicator && status) {
                indicator.className = 'w-2 h-2 bg-green-500 rounded-full animate-pulse';
                status.textContent = 'Connected';
                status.className = 'text-xs text-green-600 dark:text-green-400 ml-2';
            }
            if (updateInterval) {
                clearInterval(updateInterval);
                updateInterval = null;
            }
        });

        pusher.connection.bind('disconnected', function() {
            isConnected = false;
            const indicator = document.getElementById('connection-indicator');
            const status = document.getElementById('connection-status');
            if (indicator && status) {
                indicator.className = 'w-2 h-2 bg-red-500 rounded-full';
                status.textContent = 'Disconnected';
                status.className = 'text-xs text-red-600 dark:text-red-400 ml-2';
            }
        });
    } else {
        if (typeof Pusher === 'undefined') {
            console.warn('Pusher library not loaded');
        } else if (!window.PUSHER_APP_KEY) {
            console.warn('Pusher app key not configured');
        }
    }

    // Initial fetch and polling fallback
    fetchCounts();
    fetchMetrics();
    updateInterval = setInterval(function(){ fetchCounts(); fetchMetrics(); }, 10000);

    window.addEventListener('beforeunload', function() {
        if (pusher) pusher.disconnect();
        if (updateInterval) clearInterval(updateInterval);
    });

    // Chart handling
    (function initChart(attempt = 0){
        const canvas = document.getElementById('user-activity-chart');
        if (!canvas) return;
        if (typeof Chart === 'undefined') {
            if (attempt < 10) return setTimeout(() => initChart(attempt + 1), 300);
            return;
        }
        const ctx = canvas.getContext('2d');
        let chart;
        let currentRange = '14d';

        function fetchAndRender(range) {
            if (range) currentRange = range;
            document.querySelectorAll('.range-btn').forEach(btn => btn.classList.remove('bg-gray-100', 'dark:bg-gray-700', 'active'));
            const activeBtn = document.querySelector(`.range-btn[data-range="${currentRange}"]`);
            if (activeBtn) activeBtn.classList.add('bg-gray-100', 'dark:bg-gray-700', 'active');
            fetch(`/admin/dashboard/user-activity?range=${encodeURIComponent(currentRange)}`)
                .then(r => r.json())
                .then(({labels, data}) => {
                    if (chart) chart.destroy();
                    chart = new Chart(ctx, {
                        type: 'line',
                        data: { labels, datasets: [{ label: 'Activity Events', data, fill: true, borderColor: '#00B388', backgroundColor: 'rgba(0,179,136,0.15)', tension: 0.35, pointRadius: 2 }] },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            layout: { padding: 8 },
                            scales: {
                                x: { ticks: { color: '#9CA3AF' } },
                                y: { beginAtZero: true, ticks: { stepSize: 1, color: '#9CA3AF' }, grid: { color: 'rgba(156,163,175,0.2)' } }
                            }
                        }
                    });
                })
                .catch(console.error);
        }

        document.querySelectorAll('.range-btn').forEach(btn => {
            btn.addEventListener('click', () => fetchAndRender(btn.getAttribute('data-range')));
        });

        window.userActivityChart = {
            fetch: () => fetchAndRender(),
            setRange: (r) => fetchAndRender(r),
            get range() { return currentRange; }
        };

        fetchAndRender('14d');
    })();
});


