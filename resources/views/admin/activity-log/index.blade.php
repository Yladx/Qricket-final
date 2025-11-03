<x-admin-layout>
    <x-slot name="title">Activity Log</x-slot>
    <x-slot name="header">
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Admin Activity Log</h2>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Track all your administrative activities and actions.</p>
        </div>
    </x-slot>

    @php
        // Group logs by date
        $groupedLogs = $logs->groupBy(function($log) {
            return $log->created_at->format('Y-m-d');
        });
    @endphp

    <!-- Logs Table -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
        @if($groupedLogs->count() > 0)
            <!-- Global Activity Filter -->
            <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Filter Activities</h4>
                    <div class="flex items-center space-x-4">
                        <!-- Activity Type Filter -->
                        <div class="relative">
                            <button onclick="toggleAdminFilter()" class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                                </svg>
                                Filter Activities
                            </button>
                            <!-- Filter Dropdown -->
                            <div id="admin-filter" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg z-10 hidden border border-gray-200 dark:border-gray-700">
                                <div class="py-1">
                                    @foreach($availableActions as $action)
                                        <label class="flex items-center px-3 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                            <input type="checkbox" class="admin-activity-filter mr-2" value="{{ $action }}" checked>
                                            {{ ucfirst(str_replace('_', ' ', $action)) }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <!-- Collapse All Toggle -->
                        <button onclick="toggleAllAdminGroups()" class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 flex items-center">
                            <svg id="admin-collapse-all-icon" class="w-4 h-4 mr-1 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                            Collapse All
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <div class="space-y-0">
            @forelse($groupedLogs as $date => $items)
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <!-- Date Header -->
                    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ \Carbon\Carbon::parse($date)->format('M j, Y') }}
                            </h4>
                            <!-- Collapse Toggle -->
                            <button onclick="toggleAdminGroup('{{ $date }}')" class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                                <svg id="admin-icon-{{ $date }}" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Activities for this date -->
                    <div id="admin-activities-{{ $date }}" class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($items as $log)
                            @php
                                $color = match(true) {
                                    str_contains($log->action, 'create') => 'green',
                                    str_contains($log->action, 'update') || str_contains($log->action, 'approve') => 'blue',
                                    str_contains($log->action, 'delete') || str_contains($log->action, 'reject') => 'red',
                                    str_contains($log->action, 'login') => 'purple',
                                    default => 'gray'
                                };
                                $bg = "bg-{$color}-100"; 
                                $text = "text-{$color}-700";
                                $icon = match(true) {
                                    str_contains($log->action, 'create') => 'plus',
                                    str_contains($log->action, 'update') || str_contains($log->action, 'approve') => 'check',
                                    str_contains($log->action, 'delete') || str_contains($log->action, 'reject') => 'times',
                                    str_contains($log->action, 'login') => 'sign-in',
                                    default => 'info'
                                };
                            @endphp
                            <div class="px-4 py-3 admin-activity-item" data-action="{{ $log->action }}">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="h-6 w-6 rounded-full {{ $bg }} {{ $text }} flex items-center justify-center">
                                            <i class="fi fi-rr-{{ $icon }} text-xs"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <p class="text-xs font-medium text-gray-900 dark:text-gray-100">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $log->created_at->format('g:i A') }}</p>
                                        </div>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ $log->description ?? '—' }}</p>
                                        @if($log->metadata && count($log->metadata) > 0)
                                            <div class="mt-1">
                                                @foreach($log->metadata as $key => $value)
                                                    <span class="inline-block bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-xs mr-1 mb-1">
                                                        {{ $key }}: {{ is_array($value) ? json_encode($value) : $value }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                        <div class="mt-1 flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                                            <span>IP: {{ $log->ip_address }}</span>
                                            <a href="{{ route('admin.activity-log.show', $log) }}" class="text-qricket-600 dark:text-qricket-400 hover:underline">View Details</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No activity</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">You haven't performed any administrative activities yet.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    @if($logs->hasPages())
        <div class="mt-6">
            {{ $logs->appends(request()->query())->links() }}
        </div>
    @endif
</x-admin-layout>

<script>
    // Admin activity filtering and collapsing functions
    function toggleAdminFilter() {
        const filter = document.getElementById('admin-filter');
        if (filter.classList.contains('hidden')) {
            filter.classList.remove('hidden');
        } else {
            filter.classList.add('hidden');
        }
    }

    function toggleAdminGroup(date) {
        const activities = document.getElementById('admin-activities-' + date);
        const icon = document.getElementById('admin-icon-' + date);
        
        if (activities.classList.contains('hidden')) {
            activities.classList.remove('hidden');
            icon.style.transform = 'rotate(0deg)';
        } else {
            activities.classList.add('hidden');
            icon.style.transform = 'rotate(-90deg)';
        }
    }

    function toggleAllAdminGroups() {
        const allActivities = document.querySelectorAll('[id^="admin-activities-"]');
        const allIcons = document.querySelectorAll('[id^="admin-icon-"]');
        const collapseIcon = document.getElementById('admin-collapse-all-icon');
        
        const isCollapsed = allActivities[0] && allActivities[0].classList.contains('hidden');
        
        allActivities.forEach(activities => {
            if (isCollapsed) {
                activities.classList.remove('hidden');
            } else {
                activities.classList.add('hidden');
            }
        });
        
        allIcons.forEach(icon => {
            if (isCollapsed) {
                icon.style.transform = 'rotate(0deg)';
            } else {
                icon.style.transform = 'rotate(-90deg)';
            }
        });
        
        if (isCollapsed) {
            collapseIcon.style.transform = 'rotate(0deg)';
        } else {
            collapseIcon.style.transform = 'rotate(-90deg)';
        }
    }

    // Admin activity type filtering
    document.addEventListener('DOMContentLoaded', function() {
        const filters = document.querySelectorAll('.admin-activity-filter');
        filters.forEach(filter => {
            filter.addEventListener('change', function() {
                const action = this.value;
                const activities = document.querySelectorAll(`.admin-activity-item[data-action="${action}"]`);
                
                if (this.checked) {
                    activities.forEach(activity => activity.style.display = 'block');
                } else {
                    activities.forEach(activity => activity.style.display = 'none');
                }
            });
        });
    });

    // Close filter dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const adminFilter = document.getElementById('admin-filter');
        if (adminFilter && !adminFilter.contains(event.target) && !event.target.closest('button[onclick*="toggleAdminFilter"]')) {
            adminFilter.classList.add('hidden');
        }
    });
</script>
