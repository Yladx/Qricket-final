<x-admin-layout title="Pending Approvals" page-title="Pending Event Approvals">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Pending Event Approvals ({{ $pendingEvents->total() }})</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Review and manage event submissions awaiting approval.</p>
            </div>
            
            <!-- Status Cards - Right Side, Inline, Smaller -->
            <div class="flex items-center space-x-2">
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-md px-2 py-1 text-center">
                    <div class="text-sm font-bold text-yellow-700 dark:text-yellow-300">{{ $pendingEvents->total() }}</div>
                    <div class="text-[10px] text-yellow-800 dark:text-yellow-400">Pending</div>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <form method="GET" class="flex flex-wrap items-center gap-4">
            <!-- Search -->
            <div class="flex-1 min-w-64">
                <input type="text" 
                       name="q" 
                       value="{{ request('q') }}" 
                       placeholder="Search events..." 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-qricket-500 focus:border-qricket-500">
            </div>
            
            <!-- Organizer Filter -->
            <div class="min-w-48">
                <input type="text" 
                       name="organizer" 
                       value="{{ request('organizer') }}" 
                       placeholder="Filter by organizer..." 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-qricket-500 focus:border-qricket-500">
            </div>
            
            <!-- My Posted Events -->
            <label class="inline-flex items-center">
                <input type="checkbox" 
                       name="my_events" 
                       value="1" 
                       {{ request()->boolean('my_events', false) ? 'checked' : '' }} 
                       class="rounded border-gray-300 text-qricket-600 shadow-sm focus:border-qricket-300 focus:ring focus:ring-qricket-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">My posted events</span>
            </label>
            
            <!-- Submit Button -->
            <button type="submit" 
                    class="px-4 py-2 bg-qricket-600 hover:bg-qricket-700 text-white rounded-md text-sm font-medium transition-colors duration-200">
                Filter
            </button>
            
            <!-- Clear Button -->
            @if(request()->hasAny(['q', 'organizer', 'my_events']))
                <a href="{{ route('admin.events.pending') }}" 
                   class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md text-sm font-medium transition-colors duration-200">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Events Grid -->
    @if($pendingEvents->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($pendingEvents as $event)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm {{ $event->user_id === null ? 'border-2 border-teal-500 dark:border-teal-400' : 'border border-gray-200 dark:border-gray-700' }} overflow-hidden hover:shadow-md transition-shadow duration-200">
                    <!-- Event Image -->
                    @if($event->thumbnail_url || $event->poster_url)
                        <div class="h-48 bg-cover bg-center" style="background-image: url('{{ $event->thumbnail_url ?? $event->poster_url }}')"></div>
                    @else
                        <div class="h-48 bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                    
                    <!-- Event Content -->
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2 line-clamp-2">{{ $event->title ?? 'Untitled Event' }}</h3>
                        
                        <!-- Event Details -->
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                {{ optional($event->start_date)->format('M j, Y g:i A') ?? 'TBD' }}
                            </div>
                            
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                {{ $event->location ?? 'Location TBD' }}
                            </div>
                            
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                                {{ $event->ticketTypes->count() }} ticket type{{ $event->ticketTypes->count() !== 1 ? 's' : '' }}
                            </div>
                        </div>
                        
                        <!-- Organizer Info -->
                        <div class="flex items-center justify-between pt-3 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center space-x-2">
                                @if($event->user?->profile_picture_url)
                                    <img src="{{ $event->user->profile_picture_url }}" alt="{{ $event->user->first_name }}" class="w-5 h-5 rounded-full object-cover">
                                @else
                                    <div class="w-5 h-5 rounded-full bg-qricket-600 text-white flex items-center justify-center text-[10px] font-semibold">
                                        @if($event->user && get_class($event->user) === 'App\Models\Admin')
                                            {{ strtoupper(substr($event->user->username ?? 'Admin', 0, 1)) }}
                                        @elseif($event->user)
                                            {{ strtoupper(substr(trim(($event->user->first_name ?? '') . ' ' . ($event->user->last_name ?? '')), 0, 1)) }}
                                        @else
                                            U
                                        @endif
                                    </div>
                                @endif
                                <span class="text-xs text-gray-600 dark:text-gray-400">
                                    @if($event->user && get_class($event->user) === 'App\Models\Admin')
                                        {{ $event->user->username ?? 'Admin' }}
                                    @elseif($event->user)
                                        {{ ($event->user->first_name ?? '') . ' ' . ($event->user->last_name ?? '') }}
                                    @else
                                        Unknown User
                                    @endif
                                </span>
                            </div>
                            @if($event->status === 'draft')
                                <a href="{{ route('admin.events.approval.show', $event->id) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-gray-600 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">Preview</a>
                            @elseif($event->status === 'pending_approval')
                                <a href="{{ route('admin.events.approval.show', $event->id) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-qricket-600 rounded-md hover:bg-qricket-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-qricket-500">Review</a>
                            @elseif(in_array($event->status, ['approved', 'published']))
                                <a href="{{ route('admin.event.manage', $event->id) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">Manage</a>
                            @else
                                <a href="{{ route('admin.events.approval.show', $event->id) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-qricket-600 rounded-md hover:bg-qricket-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-qricket-500">Review</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        <div class="mt-6">
            {{ $pendingEvents->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No pending approvals</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">All events have been reviewed or there are no events awaiting approval.</p>
        </div>
    @endif
</x-admin-layout>
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
                console.log('Pending events page update received:', data);
                
                // Handle event approval updates
                if (data.type === 'event_approved' || data.type === 'event_rejected') {
                    // Refresh the page to show updated data
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                    
                    // Show notification
                    if (typeof Swal !== 'undefined') {
                        let title, text, icon;
                        switch (data.type) {
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
            });

            // Connection status
            pusher.connection.bind('connected', function() {
                console.log('Real-time connection established for pending events page');
            });

            pusher.connection.bind('disconnected', function() {
                console.log('Real-time connection lost for pending events page');
            });
        }
    });
</script>

