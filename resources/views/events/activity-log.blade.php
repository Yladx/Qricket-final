<x-unified-layout :layout-type="$layoutType" :title="'Activity Log - ' . $event->title" :page-title="'Activity Log'">
    <x-slot name="header">
        <x-manage-header :event="$event" />
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header with Activity Label -->
            <div class="mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Activity Log</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Track all changes and actions for {{ $event->title }}</p>
            </div>
            
            <!-- Activity Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-3">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-6 h-6 bg-blue-500 rounded flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Total Activities</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ number_format($totalActivities) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-3">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-6 h-6 bg-green-500 rounded flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Today</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ number_format($todayActivities) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-3">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-6 h-6 bg-purple-500 rounded flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400">This Week</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ number_format($thisWeekActivities) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="mb-4 flex justify-end">
                <form method="GET" action="{{ route('organizer.events.activity-log', $event) }}" class="flex items-center gap-2">
                    <!-- Action Filter -->
                    <div class="min-w-[100px]">
                        <select name="action" id="action" class="w-full h-8 px-2 text-xs bg-white text-gray-900 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500">
                            <option value="">All Actions</option>
                            @foreach($availableActions as $actionType)
                                <option value="{{ $actionType }}" {{ $action == $actionType ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $actionType)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- User Filter -->
                    <div class="min-w-[100px]">
                        <select name="user" id="user" class="w-full h-8 px-2 text-xs bg-white text-gray-900 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500">
                            <option value="">All Users</option>
                            @foreach($availableUsers as $userOption)
                                <option value="{{ $userOption->id }}" {{ $user == $userOption->id ? 'selected' : '' }}>
                                    {{ $userOption->first_name }} {{ $userOption->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date From -->
                    <div class="min-w-[100px]">
                        <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}" 
                               class="w-full h-8 px-2 text-xs bg-white text-gray-900 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500">
                    </div>

                    <!-- Date To -->
                    <div class="min-w-[100px]">
                        <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}" 
                               class="w-full h-8 px-2 text-xs bg-white text-gray-900 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500">
                    </div>

                    <!-- Per Page -->
                    <div class="min-w-[80px]">
                        <select name="per_page" id="per_page" class="w-full h-8 px-2 text-xs bg-white text-gray-900 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500">
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-1">
                        <button type="submit" class="h-8 bg-qricket-500 hover:bg-qricket-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors">
                            Filter
                        </button>
                        @if(request()->hasAny(['action', 'user', 'date_from', 'date_to', 'per_page']))
                            <a href="{{ route('organizer.events.activity-log', $event) }}" class="h-8 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-3 py-1 rounded text-xs font-medium transition-colors">
                                Clear
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Activity List -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Activity History</h3>
                </div>

                @if($activities->count() > 0)
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($activities as $group)
                            <div class="p-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-200">
                                <!-- Group Header (Collapsible) -->
                                <button type="button" class="w-full flex items-center justify-between" onclick="toggleActivityGroup('{{ $group['date'] }}_{{ $group['user_id'] }}')">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            @if($group['user'] && $group['user']->profile_picture_url)
                                                <img class="w-8 h-8 rounded-full" src="{{ $group['user']->profile_picture_url }}" alt="{{ $group['user_name'] }}">
                                            @else
                                                <div class="w-8 h-8 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                        {{ substr($group['user_name'], 0, 1) }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-left">
                                            <div class="flex items-center space-x-2">
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $group['user_name'] }}</span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">•</span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($group['date'])->format('M j, Y') }}</span>
                                            </div>
                                            <div class="flex items-center space-x-2 mt-1">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                                    {{ $group['count'] }} {{ $group['count'] == 1 ? 'activity' : 'activities' }}
                                                </span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $group['time_ago'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <svg id="icon-{{ $group['date'] }}_{{ $group['user_id'] }}" class="w-5 h-5 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                
                                <!-- Group Details (Collapsible Content) -->
                                <div id="content-{{ $group['date'] }}_{{ $group['user_id'] }}" class="hidden mt-3 ml-11">
                                    <div class="space-y-2">
                                        @foreach($group['activities'] as $activity)
                                            <div class="p-2 bg-gray-50 dark:bg-gray-700 rounded border-l-3 border-{{ $activity->action_badge_color }}-200 dark:border-{{ $activity->action_badge_color }}-700">
                                                <div class="flex items-start justify-between">
                                                    <div class="flex-1">
                                                        <div class="flex items-center space-x-2 mb-1">
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-{{ $activity->action_badge_color }}-100 dark:bg-{{ $activity->action_badge_color }}-900 text-{{ $activity->action_badge_color }}-800 dark:text-{{ $activity->action_badge_color }}-200">
                                                                {{ ucwords(str_replace('_', ' ', $activity->action)) }}
                                                            </span>
                                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                                {{ $activity->created_at->format('g:i A') }}
                                                            </span>
                                                        </div>
                                                        
                                                        <p class="text-xs text-gray-900 dark:text-gray-100 mb-1">
                                                            {{ $activity->description }}
                                                        </p>

                                                        <!-- Metadata -->
                                                        @php
                                                            $metadata = is_string($activity->metadata) ? json_decode($activity->metadata, true) : $activity->metadata;
                                                            $metadataCount = is_array($metadata) ? count($metadata) : 0;
                                                        @endphp
                                                        @if($activity->metadata && $metadataCount > 0)
                                                            <details class="group">
                                                                <summary class="cursor-pointer text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 inline-flex items-center">
                                                                    <svg class="w-3 h-3 mr-1 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                                    </svg>
                                                                    Details ({{ $metadataCount }} items)
                                                                </summary>
                                                                <div class="mt-1 p-2 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-600">
                                                                    @if(is_array($metadata))
                                                                        <div class="space-y-1">
                                                                            @foreach($metadata as $key => $value)
                                                                                <div class="text-xs">
                                                                                    <span class="font-medium text-gray-900 dark:text-gray-100">
                                                                                        {{ ucwords(str_replace('_', ' ', $key)) }}:
                                                                                    </span>
                                                                                    @if(is_array($value) || is_object($value))
                                                                                        <div class="mt-1 bg-gray-50 dark:bg-gray-700 p-1 rounded border border-gray-200 dark:border-gray-600">
                                                                                            <pre class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-wrap overflow-x-auto">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                                                                        </div>
                                                                                    @else
                                                                                        <span class="text-gray-600 dark:text-gray-300 ml-1">{{ $value }}</span>
                                                                                    @endif
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    @else
                                                                        <p class="text-xs text-gray-600 dark:text-gray-300">{{ $activity->metadata }}</p>
                                                                    @endif
                                                                </div>
                                                            </details>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                        {{ $activities->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <p class="text-sm font-medium">No activity found</p>
                        <p class="text-xs mt-1">Try adjusting your filters or check back later</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function toggleActivityGroup(groupKey) {
            const content = document.getElementById('content-' + groupKey);
            const icon = document.getElementById('icon-' + groupKey);
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
            } else {
                content.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        }
    </script>
</x-unified-layout>
