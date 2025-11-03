@php
    $groupedLogs = $recentLogs->groupBy(function($log) {
        return $log->created_at->format('Y-m-d');
    });
@endphp

@if($groupedLogs->count() > 0)
    <!-- Global Activity Filter -->
    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Activity Log</h4>
            <div class="flex items-center space-x-4">
                <!-- Activity Type Filter -->
                <div class="relative">
                    <button onclick="toggleGlobalFilter()" class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                        </svg>
                        Filter Activities
                    </button>
                    <!-- Filter Dropdown -->
                    <div id="global-filter" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg z-10 hidden border border-gray-200 dark:border-gray-700">
                        <div class="py-1">
                            <label class="flex items-center px-3 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <input type="checkbox" class="global-activity-filter mr-2" value="login" checked>
                                Login
                            </label>
                            <label class="flex items-center px-3 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <input type="checkbox" class="global-activity-filter mr-2" value="profile_update" checked>
                                Profile Update
                            </label>
                            <label class="flex items-center px-3 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <input type="checkbox" class="global-activity-filter mr-2" value="organizer_status_changed" checked>
                                Status Change
                            </label>
                        </div>
                    </div>
                </div>
                <!-- Collapse All Toggle -->
                <button onclick="toggleAllDateGroups()" class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 flex items-center">
                    <svg id="collapse-all-icon" class="w-4 h-4 mr-1 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                    Collapse All
                </button>
            </div>
        </div>
    </div>
@endif

@forelse($groupedLogs as $date => $logs)
    <div class="border-b border-gray-200 dark:border-gray-700">
        <!-- Date Header -->
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ \Carbon\Carbon::parse($date)->format('M j, Y') }}
                </h4>
                <!-- Collapse Toggle -->
                <button onclick="toggleDateGroup('{{ $date }}')" class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                    <svg id="icon-{{ $date }}" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Activities for this date -->
        <div id="activities-{{ $date }}" class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($logs as $log)
                <div class="px-4 py-3 activity-item" data-action="{{ $log->action }}">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            @switch($log->action)
                                @case('login')
                                    <div class="h-6 w-6 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                                        <svg class="h-3 w-3 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                        </svg>
                                    </div>
                                    @break
                                @case('profile_update')
                                    <div class="h-6 w-6 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                        <svg class="h-3 w-3 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    @break
                                @case('organizer_status_changed')
                                    <div class="h-6 w-6 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                                        <svg class="h-3 w-3 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    @break
                                @default
                                    <div class="h-6 w-6 rounded-full bg-gray-100 dark:bg-gray-900 flex items-center justify-center">
                                        <svg class="h-3 w-3 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                            @endswitch
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-medium text-gray-900 dark:text-gray-100">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $log->created_at->format('g:i A') }}</p>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ $log->description }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@empty
    <div class="px-6 py-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No activity</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This user hasn't performed any activities yet.</p>
    </div>
@endforelse

<script>
// Activity filtering and collapsing functions
function toggleGlobalFilter() {
    const filter = document.getElementById('global-filter');
    if (filter.classList.contains('hidden')) {
        filter.classList.remove('hidden');
    } else {
        filter.classList.add('hidden');
    }
}

function toggleDateGroup(date) {
    const activities = document.getElementById('activities-' + date);
    const icon = document.getElementById('icon-' + date);
    
    if (activities.classList.contains('hidden')) {
        activities.classList.remove('hidden');
        icon.style.transform = 'rotate(0deg)';
    } else {
        activities.classList.add('hidden');
        icon.style.transform = 'rotate(-90deg)';
    }
}

function toggleAllDateGroups() {
    const allActivities = document.querySelectorAll('[id^="activities-"]');
    const allIcons = document.querySelectorAll('[id^="icon-"]');
    const collapseIcon = document.getElementById('collapse-all-icon');
    
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

// Global activity type filtering
document.addEventListener('DOMContentLoaded', function() {
    const filters = document.querySelectorAll('.global-activity-filter');
    filters.forEach(filter => {
        filter.addEventListener('change', function() {
            const action = this.value;
            const activities = document.querySelectorAll(`.activity-item[data-action="${action}"]`);
            
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
    const globalFilter = document.getElementById('global-filter');
    if (globalFilter && !globalFilter.contains(event.target) && !event.target.closest('button[onclick*="toggleGlobalFilter"]')) {
        globalFilter.classList.add('hidden');
    }
});
</script>

