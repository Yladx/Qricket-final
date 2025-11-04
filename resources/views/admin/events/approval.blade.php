<x-admin-layout title="Event Approvals" page-title="Event Approval Management">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Event Approvals ({{ $pendingEvents->total() }})</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Review and manage event submissions from organizers.</p>
            </div>
            
            <!-- Status Cards - Right Side, Inline, Smaller -->
            <div class="flex items-center space-x-2">
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-md px-2 py-1 text-center">
                    <div class="text-sm font-bold text-yellow-700 dark:text-yellow-300">{{ $allEvents->where('status','pending_approval')->count() }}</div>
                    <div class="text-[10px] text-yellow-800 dark:text-yellow-400">Pending</div>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md px-2 py-1 text-center">
                    <div class="text-sm font-bold text-green-700 dark:text-green-300">{{ $allEvents->where('status','published')->count() }}</div>
                    <div class="text-[10px] text-green-800 dark:text-green-400">Published</div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-700 rounded-md px-2 py-1 text-center">
                    <div class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ $allEvents->where('status','draft')->count() }}</div>
                    <div class="text-[10px] text-gray-700 dark:text-gray-400">Drafts</div>
                </div>
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md px-2 py-1 text-center">
                    <div class="text-sm font-bold text-red-700 dark:text-red-300">{{ $allEvents->where('status','rejected')->count() }}</div>
                    <div class="text-[10px] text-red-800 dark:text-red-400">Rejected</div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 px-4 sm:px-6 lg:px-8">
            <!-- Left: All Events with compact Filters (no container card) -->
            <div class="lg:col-span-8">
                <!-- Compact header row -->
                <div class="flex items-center justify-between mb-3">
                    <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">All Events</h2>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Filter and search through all event submissions</p>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $allEvents->total() }} total events</div>
                </div>

                <!-- Compact Filters -->
                <form method="GET" action="{{ route('admin.events.approval.index') }}" class="mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                        <!-- Search Input -->
                        <div class="lg:col-span-2">
                            <label class="sr-only">Search Events</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by event title..." class="block w-full pl-8 pr-2 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:ring-qricket-500 focus:border-qricket-500">
                            </div>
                        </div>
                        <!-- Organizer Filter -->
                            <div>
                            <label class="sr-only">Organizer</label>
                            <input list="organizers" name="organizer" value="{{ request('organizer') }}" placeholder="Organizer..." class="block w-full px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100 focus:ring-qricket-500 focus:border-qricket-500">
                                <datalist id="organizers">
                                    @isset($organizerList)
                                        @foreach($organizerList as $u)
                                            <option value="{{ $u->first_name }} {{ $u->last_name }}">{{ $u->email }}</option>
                                        @endforeach
                                    @endisset
                                </datalist>
                            </div>
                        <!-- Status Filter -->
                        <div>
                            <label class="sr-only">Status</label>
                            <select name="status" class="block w-full px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100 focus:ring-qricket-500 focus:border-qricket-500">
                                <option value="all" {{ request('status')=='all' ? 'selected' : '' }}>All Statuses</option>
                                <option value="pending_approval" {{ request('status')=='pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                                <option value="published" {{ request('status')=='published' ? 'selected' : '' }}>Published</option>
                                <option value="draft" {{ request('status')=='draft' ? 'selected' : '' }}>Draft</option>
                                <option value="rejected" {{ request('status')=='rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                            </div>
                    </div>
                    <!-- Options row -->
                    <div class="flex items-center justify-between mt-2">
                        <div class="flex items-center space-x-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="hide_drafts" value="1" {{ request()->boolean('hide_drafts', false) ? 'checked' : '' }} class="rounded border-gray-300 text-qricket-600 shadow-sm focus:border-qricket-300 focus:ring focus:ring-qricket-200 focus:ring-opacity-50">
                                <span class="ml-2 text-xs text-gray-700 dark:text-gray-300">Hide drafts</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="my_events" value="1" {{ request()->boolean('my_events', false) ? 'checked' : '' }} class="rounded border-gray-300 text-qricket-600 shadow-sm focus:border-qricket-300 focus:ring focus:ring-qricket-200 focus:ring-opacity-50">
                                <span class="ml-2 text-xs text-gray-700 dark:text-gray-300">My posted events</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="free_events" value="1" {{ request()->boolean('free_events', false) ? 'checked' : '' }} class="rounded border-gray-300 text-qricket-600 shadow-sm focus:border-qricket-300 focus:ring focus:ring-qricket-200 focus:ring-opacity-50">
                                <span class="ml-2 text-xs text-gray-700 dark:text-gray-300">Free events</span>
                            </label>
                        </div>
                        <div class="space-x-2">
                            <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-qricket-600 hover:bg-qricket-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-qricket-500">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"/></svg>
                                Apply
                            </button>
                            @if(request()->hasAny(['q', 'organizer', 'status', 'hide_drafts', 'my_events', 'free_events']))
                            <a href="{{ route('admin.events.approval.index') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-xs font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">Clear</a>
                            @endif
                    </div>
                </div>
                </form>

                <!-- Events Grid (no outer card) -->
                    @isset($allEvents)
                        @if($allEvents->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                                @foreach($allEvents as $event)
                                <div class="group bg-white dark:bg-gray-800 rounded-lg shadow-sm {{ $event->user_id === null ? 'border-2 border-teal-500 dark:border-teal-400' : 'border border-gray-200 dark:border-gray-700' }} overflow-hidden hover:shadow-md transition-all duration-300">
                                    <!-- Event Image -->
                                    <div class="bg-gray-200 dark:bg-gray-700 relative overflow-hidden" style="padding-top:56.25%">
                                            @if($event->thumbnail_url)
                                            <img src="{{ $event->thumbnail_url }}" alt="{{ $event->title }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                            @else
                                            <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        @endif
                                        <div class="absolute top-2 left-2">
                                            @php($statusColors = ['pending_approval'=>'bg-yellow-500','published'=>'bg-green-500','draft'=>'bg-gray-500','rejected'=>'bg-red-500'])
                                            <span class="px-2 py-0.5 text-[10px] font-semibold text-white rounded-full {{ $statusColors[$event->status] ?? 'bg-gray-500' }}">{{ ucwords(str_replace('_',' ',$event->status)) }}</span>
                                        </div>
                                        </div>
                                        <div class="p-4">
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 line-clamp-2 mb-2 min-h-[3rem]">{{ $event->title ?? 'Untitled Event' }}</h3>
                                            @if($event->description)
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-3 line-clamp-2 min-h-[2.5rem]">{{ Str::limit($event->description, 100) }}</p>
                                            @else
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-3 line-clamp-2 min-h-[2.5rem]">&nbsp;</p>
                                            @endif
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
                        @else
                        <div class="text-center py-10">
                            <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No events found</h3>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Try adjusting your search criteria.</p>
                        </div>
                        @endif
                    @endisset

                <!-- Pagination -->
                @isset($allEvents)
                    @if($allEvents->hasPages())
                        <div class="mt-4">{{ $allEvents->links() }}</div>
                    @endif
                @endisset
            </div>

            <!-- Right: Pending Approvals (keep card) -->
            <div class="lg:col-span-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Pending Approvals</h2>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">Events awaiting review</p>
                            </div>
                            <div class="flex items-center justify-center w-6 h-6 bg-yellow-100 dark:bg-yellow-900 rounded-full">
                                <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        </div>
                        <!-- Sorting Options -->
                        <div class="mt-3">
                            <div class="flex items-center space-x-2">
                                <label class="text-xs text-gray-600 dark:text-gray-400">Sort by:</label>
                                <select id="pending-sort-select" class="text-xs border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-qricket-500 focus:border-qricket-500">
                                    <option value="submitted_desc" {{ request('pending_sort', 'submitted_desc') == 'submitted_desc' ? 'selected' : '' }}>Submitted Date (Newest)</option>
                                    <option value="submitted_asc" {{ request('pending_sort') == 'submitted_asc' ? 'selected' : '' }}>Submitted Date (Oldest)</option>
                                    <option value="name_asc" {{ request('pending_sort') == 'name_asc' ? 'selected' : '' }}>Event Name (A-Z)</option>
                                    <option value="name_desc" {{ request('pending_sort') == 'name_desc' ? 'selected' : '' }}>Event Name (Z-A)</option>
                                    <option value="start_date_asc" {{ request('pending_sort') == 'start_date_asc' ? 'selected' : '' }}>Event Date (Earliest)</option>
                                    <option value="start_date_desc" {{ request('pending_sort') == 'start_date_desc' ? 'selected' : '' }}>Event Date (Latest)</option>
                                </select>
                                <div id="sort-loading" class="hidden">
                                    <svg class="animate-spin h-3 w-3 text-qricket-600" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="pending-events-container" class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($pendingEventsSidebar as $event)
                            <div class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200">
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        @if($event->thumbnail_url)
                                            <img src="{{ $event->thumbnail_url }}" alt="{{ $event->title }}" class="w-10 h-10 rounded-md object-cover">
                                        @else
                                            <div class="w-10 h-10 rounded-md bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $event->title ?? 'Untitled Event' }}</h3>
                                        <div class="flex items-center space-x-2 mt-0.5">
                                            @if($event->user?->profile_picture_url)
                                                <img src="{{ $event->user->profile_picture_url }}" alt="{{ $event->user->first_name }}" class="w-4 h-4 rounded-full object-cover">
                                            @else
                                                <div class="w-4 h-4 rounded-full bg-qricket-600 text-white flex items-center justify-center text-[8px] font-semibold">
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
                                                    Unknown
                                                @endif
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Submitted {{ optional($event->evr_submitted_at)->diffForHumans() ?? 'N/A' }}</div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        @if($event->status === 'draft')
                                            <a href="{{ route('admin.events.approval.show', $event->id) }}" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-white bg-gray-600 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">Preview</a>
                                        @elseif($event->status === 'pending_approval')
                                            <a href="{{ route('admin.events.approval.show', $event->id) }}" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-white bg-qricket-600 rounded-md hover:bg-qricket-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-qricket-500">Review</a>
                                        @elseif(in_array($event->status, ['approved', 'published']))
                                            <a href="{{ route('admin.event.manage', $event->id) }}" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">Manage</a>
                                        @else
                                            <a href="{{ route('admin.events.approval.show', $event->id) }}" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-white bg-qricket-600 rounded-md hover:bg-qricket-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-qricket-500">Review</a>
                                        @endif
                                    </div>
                                </div>
                        </div>
                    @empty
                            <div class="px-4 py-8 text-center">
                                <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No pending approvals</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">All caught up! No events are waiting for review.</p>
                            </div>
                    @endforelse
                    </div>
                    @if($pendingEventsSidebar->count() > 0)
                        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-center">
                            <a href="{{ route('admin.events.pending') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-qricket-600 dark:text-qricket-400 hover:text-qricket-800 dark:hover:text-qricket-300 border border-qricket-300 dark:border-qricket-600 rounded-md hover:bg-qricket-50 dark:hover:bg-qricket-900/20 transition-colors duration-200">
                                View More
                                <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sortSelect = document.getElementById('pending-sort-select');
            const loadingSpinner = document.getElementById('sort-loading');
            const eventsContainer = document.getElementById('pending-events-container');

            if (sortSelect) {
                sortSelect.addEventListener('change', function() {
                    const sortValue = this.value;
                    
                    // Show loading spinner
                    loadingSpinner.classList.remove('hidden');
                    sortSelect.disabled = true;
                    
                    // Prepare URL with current parameters
                    const url = new URL(window.location.href);
                    url.searchParams.set('pending_sort', sortValue);
                    
                    // Make AJAX request
                    fetch(url.toString(), {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.text();
                    })
                    .then(html => {
                        // Parse the response HTML to extract the pending events section
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newEventsContainer = doc.getElementById('pending-events-container');
                        
                        if (newEventsContainer) {
                            // Update the events container
                            eventsContainer.innerHTML = newEventsContainer.innerHTML;
                            
                            // Update URL without page reload
                            window.history.pushState({}, '', url.toString());
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Show error message or revert selection
                        alert('Failed to load events. Please try again.');
                        // Optionally revert to previous selection
                    })
                    .finally(() => {
                        // Hide loading spinner
                        loadingSpinner.classList.add('hidden');
                        sortSelect.disabled = false;
                    });
                });
            }
        });
    </script>

    <!-- Real-time updates for event approval page -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            // Initialize Pusher for real-time updates
            if (typeof Pusher !== 'undefined' && window.PUSHER_APP_KEY) {
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
                    console.log('Event approval page update received:', data);
                    
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
                    console.log('Real-time connection established for event approval page');
                });

                pusher.connection.bind('disconnected', function() {
                    console.log('Real-time connection lost for event approval page');
                });
            }
        });
    </script>
</x-admin-layout>