<x-admin-layout title="Payouts - Qricket" page-title="Payouts">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-10 h-10 bg-qricket-100 dark:bg-qricket-900 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-qricket-600 dark:text-qricket-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Payouts</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Manage event payout requests</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Payouts</div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $pending->count() + $approved->count() + $history->count() }}</div>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- Payout Cards Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Pending Review -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-4 py-3 bg-yellow-50 dark:bg-yellow-900/20 border-b border-yellow-200 dark:border-yellow-800">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-yellow-800 dark:text-yellow-200">Pending Review</h3>
                    <span class="px-2 py-1 bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 text-xs font-medium rounded-full">
                        {{ $pending->count() }}
                    </span>
                </div>
            </div>
            <div class="p-4">
                @forelse($pending as $p)
                    <div class="mb-4 p-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 relative overflow-hidden">
                        <!-- Background Thumbnail -->
                        <div class="absolute inset-0 bg-cover bg-center opacity-5" style="background-image: url('{{ $p->event->thumbnail_url ?: asset('images/default-event-bg.jpg') }}');"></div>
                        <div class="relative">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1 min-w-0 mr-3">
                                    <a href="{{ route('admin.event.manage', $p->event) }}" class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:text-qricket-600 dark:hover:text-qricket-400 block truncate" title="{{ $p->event->title }}">
                                        {{ $p->event->title }}
                                    </a>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">{{ $p->organizer->name }}</p>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">₱{{ number_format($p->net_amount, 2) }}</div>
                                    <div class="text-xs text-gray-500">Net Amount</div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-2 mb-2 text-xs">
                                <div class="text-gray-600 dark:text-gray-400">
                                    <span class="font-medium">Gross:</span> ₱{{ number_format($p->total_earnings, 2) }}
                                </div>
                                <div class="text-gray-600 dark:text-gray-400">
                                    <span class="font-medium">Fee:</span> ₱{{ number_format($p->qricket_fee, 2) }}
                                </div>
                            </div>
                            
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                Requested: {{ $p->requested_at->format('M d, Y g:i A') }}
                            </div>
                            
                            <div class="flex space-x-2">
                                <form action="{{ route('admin.payouts.approve', $p) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full px-3 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors">
                                        Approve
                                    </button>
                                </form>
                                <form action="{{ route('admin.payouts.reject', $p) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full px-3 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                                        Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">No pending payouts</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Approved / Processing -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-4 py-3 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-200 dark:border-blue-800">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-blue-800 dark:text-blue-200">Approved / Processing</h3>
                    <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs font-medium rounded-full">
                        {{ $approved->count() }}
                    </span>
                </div>
            </div>
            <div class="p-4">
                @forelse($approved as $p)
                    <div class="mb-4 p-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 relative overflow-hidden">
                        <!-- Background Thumbnail -->
                        <div class="absolute inset-0 bg-cover bg-center opacity-5" style="background-image: url('{{ $p->event->thumbnail_url ?: asset('images/default-event-bg.jpg') }}');"></div>
                        <div class="relative">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1 min-w-0 mr-3">
                                    <a href="{{ route('admin.event.manage', $p->event) }}" class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:text-qricket-600 dark:hover:text-qricket-400 block truncate" title="{{ $p->event->title }}">
                                        {{ $p->event->title }}
                                    </a>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">{{ $p->organizer->name }}</p>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">₱{{ number_format($p->net_amount, 2) }}</div>
                                    <div class="text-xs text-gray-500">Net Amount</div>
                                </div>
                            </div>
                            
                            <div class="mb-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @if($p->status === 'approved') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                    @elseif($p->status === 'released') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $p->status)) }}
                                </span>
                            </div>
                            
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                Requested: {{ $p->requested_at->format('M d, Y g:i A') }}
                            </div>
                            
                            @if($p->status === 'approved')
                                <form action="{{ route('admin.payouts.release', $p) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full px-3 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                                        Release for Collection
                                    </button>
                                </form>
                            @else
                                <div class="text-center py-2">
                                    <div class="text-sm text-blue-600 dark:text-blue-400 font-medium">
                                        @if($p->status === 'released') Ready for Collection
                                        @else Processing...
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">No approved payouts</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- History -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200">History</h3>
                    <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-xs font-medium rounded-full">
                        {{ $history->count() }}
                    </span>
                </div>
            </div>
            <div class="p-4">
                @forelse($history as $p)
                    <div class="mb-4 p-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 relative overflow-hidden">
                        <!-- Background Thumbnail -->
                        <div class="absolute inset-0 bg-cover bg-center opacity-5" style="background-image: url('{{ $p->event->thumbnail_url ?: asset('images/default-event-bg.jpg') }}');"></div>
                        <div class="relative">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1 min-w-0 mr-3">
                                    <a href="{{ route('admin.event.manage', $p->event) }}" class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:text-qricket-600 dark:hover:text-qricket-400 block truncate" title="{{ $p->event->title }}">
                                        {{ $p->event->title }}
                                    </a>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">{{ $p->organizer->name }}</p>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <div class="text-lg font-bold {{ $p->status === 'paid' ? 'text-green-600' : 'text-red-600' }}">
                                        ₱{{ number_format($p->net_amount, 2) }}
                                    </div>
                                    <div class="text-xs text-gray-500">Net Amount</div>
                                </div>
                            </div>
                            
                            <div class="mb-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @if($p->status === 'paid') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                    @elseif($p->status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $p->status)) }}
                                </span>
                            </div>
                            
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                {{ $p->processed_at ? $p->processed_at->diffForHumans() : 'No processing date' }}
                            </div>
                            
                            @if($p->notes)
                                <div class="text-xs text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-800 p-2 rounded border">
                                    <span class="font-medium">Notes:</span> {{ Str::limit($p->notes, 60) }}
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">No payout history</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-admin-layout><!-- Real-time updates for payout management page -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        // Initialize Pusher for real-time updates
        if (typeof Pusher !== 'undefined' && window.PUSHER_APP_KEY) {
            // Pusher Cloud only
            const pusherConfig = {
                cluster: window.PUSHER_APP_CLUSTER || 'mt1',
                forceTLS: true,
                enableTransports: ['ws','wss','xhr_streaming','xhr_polling'],
                disableStats: true,
                authEndpoint: window.PUSHER_AUTH_ENDPOINT || '/broadcasting/auth',
                auth: { headers: { 'X-CSRF-TOKEN': csrf } },
            };

            const pusher = new Pusher(window.PUSHER_APP_KEY, pusherConfig);
            const channel = pusher.subscribe('admin-dashboard');

            channel.bind('admin.dashboard.updated', function(data) {
                console.log('Payout management page update received:', data);
                
                // Handle payout updates
                if (data.type === 'payout_approved' || data.type === 'payout_rejected' || data.type === 'payout_released') {
                    // Refresh the page to show updated data
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                    
                    // Show notification
                    if (typeof Swal !== 'undefined') {
                        let title, text, icon;
                        switch (data.type) {
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
            });

            // Connection status
            pusher.connection.bind('connected', function() {
                console.log('Real-time connection established for payout management page');
            });

            pusher.connection.bind('disconnected', function() {
                console.log('Real-time connection lost for payout management page');
            });
        }
    });
</script>

<!-- Real-time updates for payout management page -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        // Initialize Pusher for real-time updates
        if (typeof Pusher !== 'undefined' && window.PUSHER_APP_KEY) {
            // Pusher Cloud only
            const pusherConfig = {
                cluster: window.PUSHER_APP_CLUSTER || 'mt1',
                forceTLS: true,
                enableTransports: ['ws','wss','xhr_streaming','xhr_polling'],
                disableStats: true,
                authEndpoint: window.PUSHER_AUTH_ENDPOINT || '/broadcasting/auth',
                auth: { headers: { 'X-CSRF-TOKEN': csrf } },
            };

            const pusher = new Pusher(window.PUSHER_APP_KEY, pusherConfig);
            const channel = pusher.subscribe('admin-dashboard');

            channel.bind('admin.dashboard.updated', function(data) {
                console.log('Payout management page update received:', data);
                
                // Handle payout updates
                if (data.type === 'payout_approved' || data.type === 'payout_rejected' || data.type === 'payout_released') {
                    // Refresh the page to show updated data
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                    
                    // Show notification
                    if (typeof Swal !== 'undefined') {
                        let title, text, icon;
                        switch (data.type) {
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
            });

            // Connection status
            pusher.connection.bind('connected', function() {
                console.log('Real-time connection established for payout management page');
            });

            pusher.connection.bind('disconnected', function() {
                console.log('Real-time connection lost for payout management page');
            });
        }
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        // Initialize Pusher for real-time updates
        if (typeof Pusher !== 'undefined' && window.PUSHER_APP_KEY) {
            // Pusher Cloud only
            const pusherConfig = {
                cluster: window.PUSHER_APP_CLUSTER || 'mt1',
                forceTLS: true,
                enableTransports: ['ws','wss','xhr_streaming','xhr_polling'],
                disableStats: true,
                authEndpoint: window.PUSHER_AUTH_ENDPOINT || '/broadcasting/auth',
                auth: { headers: { 'X-CSRF-TOKEN': csrf } },
            };

            const pusher = new Pusher(window.PUSHER_APP_KEY, pusherConfig);
            const channel = pusher.subscribe('admin-dashboard');

            channel.bind('admin.dashboard.updated', function(data) {
                console.log('Payout management page update received:', data);
                
                // Handle payout updates
                if (data.type === 'payout_approved' || data.type === 'payout_rejected' || data.type === 'payout_released') {
                    // Refresh the page to show updated data
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                    
                    // Show notification
                    if (typeof Swal !== 'undefined') {
                        let title, text, icon;
                        switch (data.type) {
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
            });

            // Connection status
            pusher.connection.bind('connected', function() {
                console.log('Real-time connection established for payout management page');
            });

            pusher.connection.bind('disconnected', function() {
                console.log('Real-time connection lost for payout management page');
            });
        }
    });
</script>

<!-- Real-time updates for payout management page -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        // Initialize Pusher for real-time updates
        if (typeof Pusher !== 'undefined' && window.PUSHER_APP_KEY) {
            // Pusher Cloud only
            const pusherConfig = {
                cluster: window.PUSHER_APP_CLUSTER || 'mt1',
                forceTLS: true,
                enableTransports: ['ws','wss','xhr_streaming','xhr_polling'],
                disableStats: true,
                authEndpoint: window.PUSHER_AUTH_ENDPOINT || '/broadcasting/auth',
                auth: { headers: { 'X-CSRF-TOKEN': csrf } },
            };

            const pusher = new Pusher(window.PUSHER_APP_KEY, pusherConfig);
            const channel = pusher.subscribe('admin-dashboard');

            channel.bind('admin.dashboard.updated', function(data) {
                console.log('Payout management page update received:', data);
                
                // Handle payout updates
                if (data.type === 'payout_approved' || data.type === 'payout_rejected' || data.type === 'payout_released') {
                    // Refresh the page to show updated data
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                    
                    // Show notification
                    if (typeof Swal !== 'undefined') {
                        let title, text, icon;
                        switch (data.type) {
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
            });

            // Connection status
            pusher.connection.bind('connected', function() {
                console.log('Real-time connection established for payout management page');
            });

            pusher.connection.bind('disconnected', function() {
                console.log('Real-time connection lost for payout management page');
            });
        }
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        // Initialize Pusher for real-time updates
        if (typeof Pusher !== 'undefined' && window.PUSHER_APP_KEY) {
            // Pusher Cloud only
            const pusherConfig = {
                cluster: window.PUSHER_APP_CLUSTER || 'mt1',
                forceTLS: true,
                enableTransports: ['ws','wss','xhr_streaming','xhr_polling'],
                disableStats: true,
                authEndpoint: window.PUSHER_AUTH_ENDPOINT || '/broadcasting/auth',
                auth: { headers: { 'X-CSRF-TOKEN': csrf } },
            };

            const pusher = new Pusher(window.PUSHER_APP_KEY, pusherConfig);
            const channel = pusher.subscribe('admin-dashboard');

            channel.bind('admin.dashboard.updated', function(data) {
                console.log('Payout management page update received:', data);
                
                // Handle payout updates
                if (data.type === 'payout_approved' || data.type === 'payout_rejected' || data.type === 'payout_released') {
                    // Refresh the page to show updated data
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                    
                    // Show notification
                    if (typeof Swal !== 'undefined') {
                        let title, text, icon;
                        switch (data.type) {
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
            });

            // Connection status
            pusher.connection.bind('connected', function() {
                console.log('Real-time connection established for payout management page');
            });

            pusher.connection.bind('disconnected', function() {
                console.log('Real-time connection lost for payout management page');
            });
        }
    });
</script>

<!-- Real-time updates for payout management page -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        // Initialize Pusher for real-time updates
        if (typeof Pusher !== 'undefined' && window.PUSHER_APP_KEY) {
            // Pusher Cloud only
            const pusherConfig = {
                cluster: window.PUSHER_APP_CLUSTER || 'mt1',
                forceTLS: true,
                enableTransports: ['ws','wss','xhr_streaming','xhr_polling'],
                disableStats: true,
                authEndpoint: window.PUSHER_AUTH_ENDPOINT || '/broadcasting/auth',
                auth: { headers: { 'X-CSRF-TOKEN': csrf } },
            };

            const pusher = new Pusher(window.PUSHER_APP_KEY, pusherConfig);
            const channel = pusher.subscribe('admin-dashboard');

            channel.bind('admin.dashboard.updated', function(data) {
                console.log('Payout management page update received:', data);
                
                // Handle payout updates
                if (data.type === 'payout_approved' || data.type === 'payout_rejected' || data.type === 'payout_released') {
                    // Refresh the page to show updated data
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                    
                    // Show notification
                    if (typeof Swal !== 'undefined') {
                        let title, text, icon;
                        switch (data.type) {
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
            });

            // Connection status
            pusher.connection.bind('connected', function() {
                console.log('Real-time connection established for payout management page');
            });

            pusher.connection.bind('disconnected', function() {
                console.log('Real-time connection lost for payout management page');
            });
        }
    });
</script>

