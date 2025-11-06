@php
    $user = auth()->user();
    // System/user logs (if relation exists)
    $systemLogs = method_exists($user, 'activityLogs') ? $user->activityLogs()->latest()->get() : collect();
    // Event logs performed by this user
    $eventLogs = \App\Models\EventActivityLog::where('user_id', $user->id)
        ->with('event')
        ->latest()
        ->get();
    // Normalize and merge into a single feed
    // Account/Login history (login/logout/profile/password/subscription/payment)
    $accountLogs = method_exists($user, 'activityLogs')
        ? $user->activityLogs()
            ->whereIn('action', ['login','logout','profile_update','password_change','subscription_created','payment_made'])
            ->latest()->get()
        : collect();
    $accountLogsByDate = $accountLogs->groupBy(fn($l) => $l->created_at->format('Y-m-d'));

    // Recently viewed events (latest distinct views)
    $recentViews = \App\Models\EventActivityLog::where('user_id', $user->id)
        ->where('action', 'event_viewed')
        ->with('event')
        ->latest()
        ->limit(50)
        ->get()
        ->unique('event_id')
        ->take(20);

    // Event history grouped by Event then Date (detailed)
    $eventHistory = \App\Models\EventActivityLog::where('user_id', $user->id)
        ->with('event')
        ->latest()
        ->get();
    $eventHistoryByEvent = $eventHistory->groupBy('event_id')->map(function($logs){
        return $logs->groupBy(fn($l) => $l->created_at->format('Y-m-d'));
    });

    $feed = $systemLogs->map(function($a){
        return [
            '_type' => 'system',
            'action' => $a->action,
            'description' => $a->description,
            'ip' => $a->ip_address ?? null,
            'created_at' => $a->created_at,
            'icon' => 'circle',
            'color' => 'gray',
            'meta' => null,
            'link' => null,
        ];
    })->merge(
        $eventLogs->map(function($e){
            return [
                '_type' => 'event',
                'action' => $e->action,
                'description' => $e->description,
                'ip' => $e->ip_address ?? null,
                'created_at' => $e->created_at,
                'icon' => $e->action_icon ?? 'info',
                'color' => $e->action_badge_color ?? 'gray',
                'meta' => $e->metadata,
                'link' => $e->event ? route('events.show', $e->event) : null,
            ];
        })
    )->sortByDesc('created_at');
    
    // Group by date
    $groupedFeed = $feed->groupBy(function($item) {
        return $item['created_at']->format('Y-m-d');
    });

    // Simple icon map for actions
    $iconFor = function($action) {
        return match($action) {
            'login','user_login' => 'log-in',
            'logout','user_logout' => 'log-out',
            'profile_update' => 'user',
            'password_change' => 'lock',
            'subscription_created' => 'badge-check',
            'payment_made' => 'credit-card',
            'event_viewed','view event' => 'eye',
            'purchase_created','purchase ticket','ticket_purchased' => 'shopping-cart',
            'certificate_generated','certificate_sent','certificate_viewed','certificate_edited','certificate edited' => 'file-text',
            default => 'dot-circle'
        };
    };
@endphp

<section>
    <header class="mb-4">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Activity Log</h3>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Your account and event activities in one place.</p>
    </header>

    <!-- Tabs: Login History | Recently Viewed | Event History | All -->
    <div class="mb-4 flex items-center space-x-2">
        <button type="button" class="px-3 py-1.5 text-xs rounded border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300" onclick="profileShowTab('tab-login')">Login History</button>
        <button type="button" class="px-3 py-1.5 text-xs rounded border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300" onclick="profileShowTab('tab-recent')">Recently Viewed</button>
        <button type="button" class="px-3 py-1.5 text-xs rounded border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300" onclick="profileShowTab('tab-event-history')">Event History</button>
        <button type="button" class="px-3 py-1.5 text-xs rounded bg-qricket-500 text-white" onclick="profileShowTab('tab-all')">All</button>
    </div>

    <!-- Login/Account History Tab (grouped by date, collapsible) -->
    <div id="tab-login" class="hidden">
        @forelse($accountLogsByDate as $date => $logs)
            <div class="rounded border border-gray-200 dark:border-gray-700 mb-3">
                <button type="button" class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-900 text-sm font-medium text-left text-gray-900 dark:text-gray-100 flex items-center justify-between" onclick="profileToggle('account-{{ $date }}')">
                    <span>{{ \Carbon\Carbon::parse($date)->format('M j, Y') }}</span>
                    <svg data-toggle-icon="account-{{ $date }}" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div id="account-{{ $date }}" class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($logs as $log)
                        <div class="px-4 py-2">
                            <button type="button" class="w-full flex items-center justify-between text-left" onclick="profileToggle('account-{{ $date }}-{{ $loop->index }}')">
                                <div class="flex items-center space-x-2 text-sm text-gray-700 dark:text-gray-300">
                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300"><i class="fi fi-rr-{{ $iconFor($log->action) }} text-xs"></i></span>
                                    <span>{{ ucfirst(str_replace('_',' ',$log->action)) }}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $log->created_at->format('g:i A') }}</span>
                                    <svg data-toggle-icon="account-{{ $date }}-{{ $loop->index }}" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </button>
                            <div id="account-{{ $date }}-{{ $loop->index }}" class="mt-2 hidden text-xs text-gray-600 dark:text-gray-400">
                                @if($log->description)
                                    <div class="mb-1"><span class="font-medium">Details:</span> {{ $log->description }}</div>
                                @endif
                                @if(!empty($log->ip_address))
                                    <div class="mb-1"><span class="font-medium">IP:</span> {{ $log->ip_address }}</div>
                                @endif
                                @if(!empty($log->user_agent))
                                    <div class="break-all"><span class="font-medium">Device:</span> {{ $log->user_agent }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="px-4 py-6 text-sm text-gray-500 dark:text-gray-400">No account history yet.</div>
        @endforelse
    </div>

    <!-- Recently Viewed Tab -->
    <div id="tab-recent" class="hidden">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($recentViews as $view)
                @php $ev = $view->event; @endphp
                @if($ev)
                <a href="{{ route('events.show', $ev) }}" class="block p-3 rounded border border-gray-200 dark:border-gray-700 hover:border-qricket-500">
                    <div class="flex items-center space-x-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300"><i class="fi fi-rr-eye text-xs"></i></span>
                        <span>{{ $ev->title }}</span>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Viewed {{ $view->created_at->diffForHumans() }}</div>
                </a>
                @endif
            @empty
                <div class="col-span-full text-sm text-gray-500 dark:text-gray-400">No recent views.</div>
            @endforelse
        </div>
    </div>

    <!-- Event History grouped by Event then Date Tab (all collapsible) -->
    <div id="tab-event-history" class="hidden">
        @forelse($eventHistoryByEvent as $eventId => $dates)
            @php $firstLog = optional($dates->first())->first(); $evTitle = $firstLog?->event?->title; @endphp
            <div class="rounded border border-gray-200 dark:border-gray-700 mb-4">
                <button type="button" class="w-full px-4 py-2 bg-gray-100 dark:bg-gray-800 text-sm font-semibold text-left text-gray-900 dark:text-gray-100 flex items-center justify-between" onclick="profileToggle('ev-{{ $eventId ?? 'none' }}')">
                    <span class="flex items-center space-x-2"><span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300"><i class="fi fi-rr-calendar text-xs"></i></span><span>{{ $evTitle ?? 'Unknown Event' }}</span></span>
                    <svg data-toggle-icon="ev-{{ $eventId ?? 'none' }}" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div id="ev-{{ $eventId ?? 'none' }}">
                    @foreach($dates as $date => $logs)
                        <div class="border-t border-gray-200 dark:border-gray-700">
                            <button type="button" class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-900 text-xs font-medium text-left text-gray-700 dark:text-gray-300 flex items-center justify-between" onclick="profileToggle('ev-{{ $eventId ?? 'none' }}-{{ $date }}')">
                                <span>{{ \Carbon\Carbon::parse($date)->format('M j, Y') }}</span>
                                <svg data-toggle-icon="ev-{{ $eventId ?? 'none' }}-{{ $date }}" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div id="ev-{{ $eventId ?? 'none' }}-{{ $date }}" class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($logs as $log)
                                    <div class="px-4 py-2">
                                        <button type="button" class="w-full flex items-center justify-between text-left" onclick="profileToggle('ev-{{ $eventId ?? 'none' }}-{{ $date }}-{{ $loop->index }}')">
                                            <div class="flex items-center space-x-2 text-sm text-gray-700 dark:text-gray-300">
                                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300"><i class="fi fi-rr-{{ $iconFor($log->action) }} text-xs"></i></span>
                                                <span>{{ ucfirst(str_replace('_',' ',$log->action)) }}</span>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $log->created_at->format('g:i A') }}</span>
                                                <svg data-toggle-icon="ev-{{ $eventId ?? 'none' }}-{{ $date }}-{{ $loop->index }}" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            </div>
                                        </button>
                                        <div id="ev-{{ $eventId ?? 'none' }}-{{ $date }}-{{ $loop->index }}" class="mt-2 hidden text-xs text-gray-600 dark:text-gray-400">
                                            @if($log->description)
                                                <div class="mb-1"><span class="font-medium">Details:</span> {{ $log->description }}</div>
                                            @endif
                                            @if(!empty($log->ip_address))
                                                <div class="mb-1"><span class="font-medium">IP:</span> {{ $log->ip_address }}</div>
                                            @endif
                                            @if(!empty($log->user_agent))
                                                <div class="break-all"><span class="font-medium">Device:</span> {{ $log->user_agent }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-sm text-gray-500 dark:text-gray-400">No event activity yet.</div>
        @endforelse
    </div>

    @if($groupedFeed->count() > 0)
        <!-- Global Activity Filter -->
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 rounded-t-lg mb-4">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Filter Activities</h4>
                <div class="flex items-center space-x-4">
                    <!-- Activity Type Filter -->
                    <div class="relative">
                        <button onclick="toggleProfileFilter()" class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                            </svg>
                            Filter Activities
                        </button>
                        <!-- Filter Dropdown -->
                        <div id="profile-filter" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg z-10 hidden border border-gray-200 dark:border-gray-700">
                            <div class="py-1">
                                <label class="flex items-center px-3 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <input type="checkbox" class="profile-activity-filter mr-2" value="login" checked>
                                    Login
                                </label>
                                <label class="flex items-center px-3 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <input type="checkbox" class="profile-activity-filter mr-2" value="profile_update" checked>
                                    Profile Update
                                </label>
                                <label class="flex items-center px-3 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <input type="checkbox" class="profile-activity-filter mr-2" value="password_change" checked>
                                    Password Change
                                </label>
                                <label class="flex items-center px-3 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <input type="checkbox" class="profile-activity-filter mr-2" value="subscription_created" checked>
                                    Subscription
                                </label>
                                <label class="flex items-center px-3 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <input type="checkbox" class="profile-activity-filter mr-2" value="payment_made" checked>
                                    Payment
                                </label>
                            </div>
                        </div>
                    </div>
                    <!-- Collapse All Toggle -->
                    <button onclick="toggleAllProfileGroups()" class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 flex items-center">
                        <svg id="profile-collapse-all-icon" class="w-4 h-4 mr-1 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                        Collapse All
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div id="tab-all" class="space-y-0">
        @forelse($groupedFeed as $date => $items)
            <div class="border-b border-gray-200 dark:border-gray-700">
                <!-- Date Header -->
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ \Carbon\Carbon::parse($date)->format('M j, Y') }}
                        </h4>
                        <!-- Collapse Toggle -->
                        <button onclick="toggleProfileGroup('{{ $date }}')" class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            <svg id="profile-icon-{{ $date }}" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Activities for this date -->
                <div id="profile-activities-{{ $date }}" class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($items as $item)
                        @php
                            $color = $item['color'] ?? 'gray';
                            $bg = "bg-{$color}-100"; $text = "text-{$color}-700";
                        @endphp
                        <div class="px-4 py-3 profile-activity-item" data-action="{{ $item['action'] }}">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="h-6 w-6 rounded-full {{ $bg }} {{ $text }} flex items-center justify-center">
                                        <i class="fi fi-rr-{{ $item['icon'] ?? 'info' }} text-xs"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <p class="text-xs font-medium text-gray-900 dark:text-gray-100">{{ ucfirst(str_replace('_', ' ', $item['action'])) }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item['created_at']->format('g:i A') }}</p>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ $item['description'] ?? '—' }}</p>
                    @if(!empty($item['link']))
                                        <div class="mt-1">
                            <a href="{{ $item['link'] }}" class="text-xs text-qricket-600 dark:text-qricket-400 hover:underline">View Event</a>
                        </div>
                    @endif
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
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">You haven't performed any activities yet.</p>
            </div>
        @endforelse
    </div>
</section>

<script>
function profileShowTab(id) {
    ['tab-login','tab-recent','tab-event-history','tab-all'].forEach(t => {
        const el = document.getElementById(t);
        if (!el) return;
        if (t === id) el.classList.remove('hidden'); else el.classList.add('hidden');
    });
}
</script>

<script>
function profileToggle(id){
    var el = document.getElementById(id);
    if(!el) return;
    var isHidden = el.classList.contains('hidden');
    if(isHidden) el.classList.remove('hidden'); else el.classList.add('hidden');
    var icon = document.querySelector('[data-toggle-icon="'+id+'"]');
    if(icon){ icon.style.transform = isHidden ? 'rotate(0deg)' : 'rotate(-90deg)'; }
}
</script>

<script>
    // Profile activity filtering and collapsing functions
    function toggleProfileFilter() {
        const filter = document.getElementById('profile-filter');
        if (filter.classList.contains('hidden')) {
            filter.classList.remove('hidden');
        } else {
            filter.classList.add('hidden');
        }
    }

    function toggleProfileGroup(date) {
        const activities = document.getElementById('profile-activities-' + date);
        const icon = document.getElementById('profile-icon-' + date);
        
        if (activities.classList.contains('hidden')) {
            activities.classList.remove('hidden');
            icon.style.transform = 'rotate(0deg)';
        } else {
            activities.classList.add('hidden');
            icon.style.transform = 'rotate(-90deg)';
        }
    }

    function toggleAllProfileGroups() {
        const allActivities = document.querySelectorAll('[id^="profile-activities-"]');
        const allIcons = document.querySelectorAll('[id^="profile-icon-"]');
        const collapseIcon = document.getElementById('profile-collapse-all-icon');
        
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

    // Profile activity type filtering
    document.addEventListener('DOMContentLoaded', function() {
        const filters = document.querySelectorAll('.profile-activity-filter');
        filters.forEach(filter => {
            filter.addEventListener('change', function() {
                const action = this.value;
                const activities = document.querySelectorAll(`.profile-activity-item[data-action="${action}"]`);
                
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
        const profileFilter = document.getElementById('profile-filter');
        if (profileFilter && !profileFilter.contains(event.target) && !event.target.closest('button[onclick*="toggleProfileFilter"]')) {
            profileFilter.classList.add('hidden');
        }
});
</script> 