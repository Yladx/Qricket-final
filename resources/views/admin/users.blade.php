<x-admin-layout title="Manage Users - Qrickett Admin" page-title="Manage Users">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">User Management</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">View, manage, and monitor all user accounts in the system.</p>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="openUserReportModal()" class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-qricket-600 hover:bg-qricket-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-qricket-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Generate Report
                </button>
                <div class="text-right">
                    <div class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_users'] }}</div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Total Users</div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-3">
                
                <!-- Verification Management -->
                <div class="space-y-1.5 mb-3">
                    <h4 class="text-[10px] font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wide">Verification Management</h4>
                    
                    <a href="{{ route('admin.verifications') }}" class="flex items-center justify-between w-full px-2 py-1.5 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors duration-200">
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mr-1.5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Pending Verifications
                        </div>
                        <span class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-300 text-[10px] px-1.5 py-0.5 rounded-full">{{ $stats['pending_organizers'] }}</span>
                    </a>
                    
                    <a href="{{ route('admin.verifications') }}?status=approved" class="flex items-center w-full px-2 py-1.5 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors duration-200">
                        <svg class="w-3 h-3 mr-1.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Approved Verifications
                    </a>
                    
                    <a href="{{ route('admin.verifications') }}?status=rejected" class="flex items-center w-full px-2 py-1.5 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors duration-200">
                        <svg class="w-3 h-3 mr-1.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Rejected Verifications
                    </a>
                    
                    <div class="pt-1.5 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('admin.verifications') }}" class="flex items-center justify-center w-full px-2 py-1.5 text-xs font-medium text-white bg-qricket-500 hover:bg-qricket-600 rounded-md transition-colors duration-200">
                            <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Verify Organizer
                        </a>
                    </div>
                </div>

                <!-- User Statistics -->
                <div class="space-y-1.5">
                    <h4 class="text-[10px] font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wide">User Statistics</h4>
                    
                    <div class="space-y-1">
                        <div class="flex items-center justify-between text-xs"><span class="text-gray-600 dark:text-gray-400">Total Users</span><span class="font-medium text-gray-900 dark:text-gray-100">{{ $stats['total_users'] }}</span></div>
                        
                        <div class="flex items-center justify-between text-xs"><span class="text-gray-600 dark:text-gray-400">Verified Organizers</span><span class="font-medium text-green-600 dark:text-green-400">{{ $stats['verified_organizers'] }}</span></div>
                        
                        <div class="flex items-center justify-between text-xs"><span class="text-gray-600 dark:text-gray-400">Pending Organizers</span><span class="font-medium text-yellow-600 dark:text-yellow-400">{{ $stats['pending_organizers'] }}</span></div>
                        
                        <div class="flex items-center justify-between text-xs"><span class="text-gray-600 dark:text-gray-400">Regular Users</span><span class="font-medium text-gray-600 dark:text-gray-400">{{ $stats['regular_users'] }}</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-3 space-y-3">
            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-3">
                <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Filter Users</h3>
                <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <div>
                        <label for="organizer_status" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Organizer Status</label>
                        <select name="organizer_status" id="organizer_status" class="w-full px-2.5 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-qricket-500 dark:focus:ring-qricket-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                            <option value="">All Users</option>
                            <option value="verified" {{ request('organizer_status') === 'verified' ? 'selected' : '' }}>Verified Organizers</option>
                            <option value="pending" {{ request('organizer_status') === 'pending' ? 'selected' : '' }}>Pending Organizers</option>
                            <option value="regular" {{ request('organizer_status') === 'regular' ? 'selected' : '' }}>Regular Users</option>
                        </select>
                    </div>
                    <div>
                        <label for="email" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email</label>
                        <input type="email" name="email" id="email" value="{{ request('email') }}" placeholder="Search by email..." class="w-full px-2.5 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-qricket-500 dark:focus:ring-qricket-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                    </div>
                    <div>
                        <label for="name" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Name</label>
                        <input type="text" name="name" id="name" value="{{ request('name') }}" placeholder="Search by name..." class="w-full px-2.5 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-qricket-500 dark:focus:ring-qricket-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                    </div>
                    <div>
                        <label for="online_status" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Online Status</label>
                        <select name="online_status" id="online_status" class="w-full px-2.5 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-qricket-500 dark:focus:ring-qricket-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                            <option value="" {{ request('online_status')==='' ? 'selected' : '' }}>All</option>
                            <option value="online" {{ request('online_status')==='online' ? 'selected' : '' }}>Online</option>
                            <option value="offline" {{ request('online_status')==='offline' ? 'selected' : '' }}>Offline</option>
                        </select>
                    </div>
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="flex-1 bg-qricket-500 hover:bg-qricket-600 text-white px-3 py-2 rounded-md font-medium transition-colors duration-200 text-sm">Filter</button>
                        <a href="{{ route('admin.users') }}" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-3 py-2 rounded-md font-medium transition-colors duration-200 text-center text-sm">Clear</a>
                    </div>
                </form>
            </div>

            <!-- Active Filters -->
            @if(request()->hasAny(['organizer_status', 'email', 'name']))
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-blue-600 dark:text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            <span class="text-sm font-medium text-blue-800 dark:text-blue-200">Active Filters:</span>
                        </div>
                        <a href="{{ route('admin.users') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200">
                            Clear All
                        </a>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @if(request('organizer_status'))
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ ucfirst(request('organizer_status')) }} Organizers
                            </span>
                        @endif
                        @if(request('email'))
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                Email: {{ request('email') }}
                            </span>
                        @endif
                        @if(request('name'))
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                Name: {{ request('name') }}
                            </span>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Users Table -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Users</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 sortable" data-column="first_name">
                                    User <span class="sort-indicator">↕</span>
                                </th>
                                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 sortable" data-column="email">
                                    Email <span class="sort-indicator">↕</span>
                                </th>
                                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Organizer Status</th>
                                <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 sortable" data-column="created_at">
                                    Joined <span class="sort-indicator">↕</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($users as $user)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <a href="{{ route('admin.users.show', $user) }}" class="flex items-center hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md p-1.5 -m-1.5 transition-colors duration-200 group">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                @if($user->profile_picture_url)
                                                    <img src="{{ $user->profile_picture_url }}" 
                                                         alt="{{ $user->full_name }}" 
                                                         class="h-8 w-8 rounded-full object-cover group-hover:ring-2 group-hover:ring-qricket-500 transition-all duration-200"
                                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                @endif
                                                <div class="h-8 w-8 rounded-full bg-qricket-100 dark:bg-qricket-900 flex items-center justify-center group-hover:bg-qricket-200 dark:group-hover:bg-qricket-800 transition-colors duration-200 {{ $user->profile_picture_url ? 'hidden' : '' }}">
                                                    <span class="text-xs font-medium text-qricket-600 dark:text-qricket-400">
                                                        {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-xs font-medium text-gray-900 dark:text-gray-100 group-hover:text-qricket-600 dark:group-hover:text-qricket-400 transition-colors duration-200">{{ $user->full_name }}</div>
                                                <div class="text-[10px] text-gray-500 dark:text-gray-400">ID: {{ $user->id }}</div>
                                            </div>
                                            <div class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </div>
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-900 dark:text-gray-100">{{ $user->email }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-xs">
                                        @php $isOnline = in_array($user->id, $onlineUserIds ?? []); @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $isOnline ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300' }} online-pill" data-user-id="{{ $user->id }}">
                                            <span class="inline-block w-1.5 h-1.5 rounded-full mr-1.5 {{ $isOnline ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                            {{ $isOnline ? 'Online' : 'Offline' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-xs">
                                        @if($user->verified_organizer)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                Verified Organizer
                                            </span>
                                        @elseif($user->organizer)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                Pending Verification
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300">
                                                Regular User
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">{{ $user->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                                        No users found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Enhanced Pagination -->
                @if($users->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="text-xs text-gray-700 dark:text-gray-300">
                                Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} users
                            </div>
                            <div class="flex items-center space-x-2">
                                {{ $users->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
<script>
// Table sorting functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize sorting indicators based on current sort
    const currentSortBy = '{{ request("sort_by", "created_at") }}';
    const currentSortOrder = '{{ request("sort_order", "desc") }}';
    
    document.querySelectorAll('.sortable').forEach(header => {
        const column = header.dataset.column;
        const indicator = header.querySelector('.sort-indicator');
        
        if (column === currentSortBy) {
            indicator.textContent = currentSortOrder === 'asc' ? '↑' : '↓';
            header.classList.add('text-qricket-600', 'dark:text-qricket-400');
        }
    });
    
    // Add click handlers for sorting
    document.querySelectorAll('.sortable').forEach(header => {
        header.addEventListener('click', function() {
            const column = this.dataset.column;
            const currentSort = '{{ request("sort_by", "created_at") }}';
            const currentOrder = '{{ request("sort_order", "desc") }}';
            
            // Determine new sort order
            let newOrder = 'asc';
            if (column === currentSort && currentOrder === 'asc') {
                newOrder = 'desc';
            }
            
            // Build new URL with sorting parameters
            const url = new URL(window.location);
            url.searchParams.set('sort_by', column);
            url.searchParams.set('sort_order', newOrder);
            
            // Navigate to new URL
            window.location.href = url.toString();
        });
    });
});
</script>
<script>
/**
 * Real-time Online User Tracking via Sessions
 * 
 * How it works:
 * 1. Sessions are stored in the 'sessions' database table
 * 2. Each session has: id, user_id, role (user/admin/guest), last_activity (timestamp)
 * 3. UserSessionService tracks activity and updates sessions automatically
 * 4. A user is "online" if they have a session with:
 *    - user_id is not null (logged in)
 *    - last_activity is within the last 30 minutes (active)
 * 5. Real-time updates come via Pusher when sessions change
 * 6. The /admin/dashboard/session-details endpoint provides current session data
 */
document.addEventListener('DOMContentLoaded', function() {
    try {
        if (typeof Pusher === 'undefined' || !window.PUSHER_APP_KEY) return;
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        // Pusher Cloud only
        const config = {
            cluster: window.PUSHER_APP_CLUSTER || 'mt1',
            forceTLS: true,
            enableTransports: ['ws','wss','xhr_streaming','xhr_polling'],
            disableStats: true,
            authEndpoint: window.PUSHER_AUTH_ENDPOINT || '/broadcasting/auth',
            auth: { headers: { 'X-CSRF-TOKEN': csrf } },
        };
        const p = new Pusher(window.PUSHER_APP_KEY, config);
        const channel = p.subscribe('admin-dashboard');
        channel.bind('user.activity', function(data) {
            if (!data || (data.activity !== 'count_update' && data.activity !== 'session_cleanup')) return;
            // Refetch online user ids from sessions table to update pills without full page reload
            fetch('/admin/dashboard/session-details')
                .then(r => r.json())
                .then(details => {
                    const sessions = details.sessions || {};
                    // Build a Set of user_ids that are online
                    const onlineIds = new Set();
                    if (sessions.user) {
                        sessions.user.forEach(s => { if (s.user_id) onlineIds.add(s.user_id); });
                    }
                    document.querySelectorAll('.online-pill').forEach(el => {
                        const uid = parseInt(el.getAttribute('data-user-id'));
                        const isOnline = onlineIds.has(uid);
                        el.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium online-pill ' + (isOnline ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300');
                        const dot = el.querySelector('span');
                        if (dot) { dot.className = 'inline-block w-2 h-2 rounded-full mr-2 ' + (isOnline ? 'bg-green-500' : 'bg-gray-400'); }
                        el.lastChild.nodeValue = (isOnline ? 'Online' : 'Offline');
                    });
                })
                .catch(() => {});
        });
    } catch (e) {}
});
</script>

<!-- User Report Modal -->
<div id="userReportModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-0 border-0 w-full max-w-lg shadow-2xl rounded-xl bg-white dark:bg-gray-800">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-qricket-500 to-emerald-600 rounded-t-xl">
            <div>
                <h3 class="text-xl font-bold text-white">Generate User Report</h3>
                <p class="text-sm text-qricket-100 mt-1">Export user data to CSV</p>
            </div>
            <button onclick="closeUserReportModal()" class="p-2 text-white hover:bg-white hover:bg-opacity-20 rounded-lg transition-colors duration-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="p-6">
            <form id="userReportForm" onsubmit="generateUserReport(event)" class="space-y-6">
                <!-- Report Type -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">📊 Report Type</label>
                    <div class="grid grid-cols-1 gap-3">
                        <label class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                            <input type="radio" name="reportType" value="all" class="h-4 w-4 text-qricket-600 focus:ring-qricket-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700" checked>
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">All Users</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Export complete user list</div>
                            </div>
                        </label>
                        <label class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                            <input type="radio" name="reportType" value="verified" class="h-4 w-4 text-qricket-600 focus:ring-qricket-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Verified Organizers Only</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Export verified organizers</div>
                            </div>
                        </label>
                        <label class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                            <input type="radio" name="reportType" value="pending" class="h-4 w-4 text-qricket-600 focus:ring-qricket-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Pending Verifications</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Export pending organizer requests</div>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Format -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">📄 Export Format</label>
                    <div class="flex items-center p-3 border-2 border-qricket-500 dark:border-qricket-400 rounded-lg bg-qricket-50 dark:bg-qricket-900/20">
                        <input type="radio" name="format" value="xlsx" class="h-4 w-4 text-qricket-600 focus:ring-qricket-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700" checked>
                        <div class="ml-3">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Excel (.xlsx)</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Colored, formatted Excel file</div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" onclick="closeUserReportModal()" class="px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors duration-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-3 text-sm font-medium text-white bg-gradient-to-r from-qricket-600 to-emerald-600 hover:from-qricket-700 hover:to-emerald-700 rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export Users
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openUserReportModal() {
    document.getElementById('userReportModal').classList.remove('hidden');
}

function closeUserReportModal() {
    document.getElementById('userReportModal').classList.add('hidden');
}

function generateUserReport(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const reportType = formData.get('reportType');
    const format = formData.get('format');
    
    // Show loading state
    const submitButton = event.target.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Generating...';
    submitButton.disabled = true;

    // Build export URL based on report type
    let exportUrl = '{{ route("admin.users.export") }}';
    if (reportType !== 'all') {
        exportUrl += `?type=${reportType}`;
    }

    // Redirect to export URL
    window.location.href = exportUrl;

    // Close modal after a brief delay
    setTimeout(() => {
        closeUserReportModal();
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    }, 500);
}
</script>
