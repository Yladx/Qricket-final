<x-app-layout>
    <x-slot name="title">My Events</x-slot>
    <x-slot name="header">
   
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        {{ __('My Events') }}
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Events you organize or are part of the team
                    </p>
                </div>
                
                @if(auth()->user()->isVerifiedOrganizer() || Auth::guard('admin')->check())
                    <a href="{{ route('organizer.events.step1', ['new' => 1]) }}" class="bg-qricket-500 hover:bg-qricket-600 text-white px-6 py-2 rounded-lg transition-colors duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create New Event
                    </a>
                @else
                    <a href="{{ route('organizer.verification') }}" class="bg-qricket-500 hover:bg-qricket-600 text-white px-6 py-2 rounded-lg transition-colors duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Get verified now to create an event
                    </a>
                @endif
            </div>
       
    </x-slot>

    <div class="py-4 px-4 sm:px-6">
        <div class="max-w-7xl mx-auto">

            <!-- Filters and Search (compact) -->
            <div class="mb-3">
                <div class="p-3">
                    <form method="GET" action="{{ route('organizer.events.index') }}" class="flex flex-wrap items-center gap-2 justify-end">
                        <div class="flex flex-wrap items-center gap-3">
                            <select name="status" class="border border-gray-300 dark:border-gray-600 rounded-md px-2 py-1 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-qricket-500 focus:border-qricket-500">
                                <option value="">All Events</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                            <select name="category" class="border border-gray-300 dark:border-gray-600 rounded-md px-2 py-1 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-qricket-500 focus:border-qricket-500">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                        {{ ucfirst($category) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search events..." class="border border-gray-300 dark:border-gray-600 rounded-md pl-8 pr-2 py-1 w-56 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-qricket-500 focus:border-qricket-500">
                            <svg class="absolute left-2 top-1.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button type="submit" class="bg-qricket-500 hover:bg-qricket-600 text-white px-2.5 py-1 rounded-md text-xs transition-colors duration-200">
                                Filter
                            </button>
                            @if(request()->hasAny(['status', 'category', 'search']))
                                <a href="{{ route('organizer.events.index') }}" class="bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-2.5 py-1 rounded-md text-xs transition-colors duration-200">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Events Grid (compact cards) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                @forelse($events as $event)
                    @php
                        // Check if event has ended
                        $eventEnded = false;
                        if ($event->end_date) {
                            $eventEnded = \Carbon\Carbon::parse($event->end_date)->isPast();
                        } elseif ($event->start_date) {
                            $eventEnded = \Carbon\Carbon::parse($event->start_date)->isPast();
                        }
                    @endphp
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-200 {{ $eventEnded ? 'border-2 border-gray-400 dark:border-gray-600' : '' }}" data-event-id="{{ $event->id }}">
                        <div class="relative">
                            @if($event->thumbnail_url)
                                <img src="{{ $event->thumbnail_url }}" alt="{{ $event->title ?: 'Event' }}" class="h-36 w-full object-cover">
                            @else
                                <div class="h-36 bg-gradient-to-r from-qricket-400 to-qricket-600 flex items-center justify-center">
                                    <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                            <div class="absolute top-3 right-3 flex flex-col space-y-1">
                                <!-- Event Status -->
                                @if($event->status === 'draft')
                                    <span class="bg-gray-500 text-white px-2 py-1 rounded-full text-xs font-medium">Draft</span>
                                @elseif($event->status === 'pending_approval')
                                    <span class="bg-yellow-500 text-white px-2 py-1 rounded-full text-xs font-medium">Pending</span>
                                @elseif($event->status === 'published')
                                    <span class="bg-green-500 text-white px-2 py-1 rounded-full text-xs font-medium">Published</span>
                                @elseif($event->status === 'rejected')
                                    <span class="bg-red-500 text-white px-2 py-1 rounded-full text-xs font-medium">Rejected</span>
                                @endif
                            </div>
                        </div>
                        <div class="p-3">
                            @php
                                $isAdmin = Auth::guard('admin')->check();
                                $isCreatorTop = $event->user_id === Auth::id();
                                $isAdminEvent = $event->user_id === null && $isAdmin;
                                $isOrganizerTop = $event->staff->where('user_id', Auth::id())->where('role', 'organizer')->isNotEmpty();
                                $canDeleteTop = $isCreatorTop || $isAdminEvent || $isOrganizerTop;
                            @endphp
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 line-clamp-2 min-h-[3.5rem] leading-tight">{{ $event->title ?: 'Untitled Event' }}</h3>
                                @php
                                    $userRole = null;
                                    $roleBadgeColor = 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200';
                                    $currentUserId = Auth::id();
                                    $staffMember = $event->staff->where('user_id', $currentUserId)->first();
                                    
                                    // Priority: Check staff role first (co-owner takes precedence)
                                    if ($staffMember) {
                                        // User is staff member - prioritize staff role over creator
                                        if ($staffMember->role === 'co-owner') {
                                            $userRole = 'Co-Owner';
                                            $roleBadgeColor = 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200';
                                        } elseif ($staffMember->role === 'manager') {
                                            $userRole = 'Manager';
                                            $roleBadgeColor = 'bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200';
                                        } elseif ($staffMember->role === 'organizer') {
                                            $userRole = 'Organizer';
                                            $roleBadgeColor = 'bg-teal-100 dark:bg-teal-900 text-teal-800 dark:text-teal-200';
                                        } else {
                                            $userRole = ucfirst(str_replace('_', ' ', $staffMember->role));
                                            $roleBadgeColor = 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200';
                                        }
                                    } elseif ($event->user_id === $currentUserId) {
                                        // User is creator but not staff
                                        $userRole = 'Creator';
                                        $roleBadgeColor = 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200';
                                    } elseif ($event->user_id === null && $isAdmin) {
                                        $userRole = 'Admin';
                                        $roleBadgeColor = 'bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200';
                                    }
                                @endphp
                                <div class="flex items-center gap-1.5">
                                @if($userRole)
                                    <span class="{{ $roleBadgeColor }} px-2 py-1 rounded-full text-xs font-medium">
                                        {{ $userRole }}
                                    </span>
                                @endif
                                    @if($canDeleteTop && $event->status === 'draft')
                                        <!-- 3-dot menu for draft events -->
                                        <div class="relative">
                                            <button type="button" class="p-1 rounded-md text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white" data-menu-toggle="event-menu-{{ $event->id }}">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            </button>
                                            <div id="event-menu-{{ $event->id }}" class="hidden absolute right-0 mt-2 w-36 rounded-md shadow-lg bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 z-20">
                                                <button type="button" onclick="hideEventMenu('event-menu-{{ $event->id }}'); deleteEvent({{ $event->id }}, this)" class="w-full text-left px-3 py-2 text-xs text-red-600 dark:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20">Delete</button>
                                            </div>
                                        </div>
                                    @elseif($event->status === 'rejected' && $event->rejection_reason)
                                        <!-- 3-dot menu for rejected events -->
                                        <div class="relative">
                                            <button type="button" class="p-1 rounded-md text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white" data-menu-toggle="rejection-menu-{{ $event->id }}">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            </button>
                                            <div id="rejection-menu-{{ $event->id }}" class="hidden absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 z-20">
                                                <button type="button" onclick="hideEventMenu('rejection-menu-{{ $event->id }}'); showRejectionReason({{ $event->id }}, '{{ addslashes($event->rejection_reason) }}', '{{ addslashes($event->title) }}')" class="w-full text-left px-3 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                                                    View Rejection Reason
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="space-y-1 mb-2.5 text-xs">
                                @if($event->start_date)
                                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                        {{ \Carbon\Carbon::parse($event->start_date)->format('M d, Y g:i A') }}
                                </div>
                                @endif
                                @if($event->location)
                                <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
                                    <div class="flex items-center flex-1 min-w-0">
                                        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                        <span class="truncate">{{ $event->location }}</span>
                                    </div>
                                    @if($event->start_date && \Carbon\Carbon::parse($event->start_date) > now())
                                        <span class="text-xs text-green-600 dark:text-green-400 font-medium flex-shrink-0 ml-2">Upcoming</span>
                                    @elseif($event->start_date)
                                        <span class="text-xs text-gray-500 dark:text-gray-400 font-medium flex-shrink-0 ml-2">Past Event</span>
                                    @endif
                                </div>
                                @endif
                                @if($event->max_participants)
                                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    {{ number_format($event->max_participants) }} max attendees
                                </div>
                                @endif
                            </div>
                            
                            <div class="px-2 pb-2">
                                    @php
                                    $isCreator = $event->user_id === Auth::id();
                                    $isAdminEvent = $event->user_id === null && $isAdmin;
                                    $isOrganizer = $event->staff->where('user_id', Auth::id())->where('role', 'organizer')->isNotEmpty();
                                    $staffMember = $event->staff->where('user_id', Auth::id())->first();
                                        $userRole = $staffMember ? $staffMember->role : null;
                                    $hasAnyAccess = $staffMember ? (
                                        $staffMember->can_manage_event || $staffMember->can_manage_ticket ||
                                        $staffMember->can_manage_attendance || $staffMember->can_manage_news ||
                                        $staffMember->can_manage_sales || $staffMember->can_manage_staff
                                    ) : false;
                                    $canManageEvent = $isCreator || $isAdminEvent || $isOrganizer || $hasAnyAccess;
                                    @endphp
                                    
                                <!-- Action buttons - flexible layout -->
                                <div class="flex flex-wrap gap-2">
                                    @if($isCreator || $isAdminEvent || $isOrganizer)
                                        @if($event->status === 'draft')
                                            <a href="{{ route('organizer.events.step1', ['continue' => $event->id]) }}" class="flex-1 bg-yellow-100 dark:bg-yellow-900 hover:bg-yellow-200 dark:hover:bg-yellow-800 text-yellow-700 dark:text-yellow-300 px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center justify-center">
                                                Continue
                                            </a>
                                        @elseif($event->status === 'pending_approval')
                                            <form method="POST" action="{{ route('organizer.events.update-status', $event) }}" class="flex-1 cancel-request-form">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="draft">
                                                <button type="button" class="w-full bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center justify-center cancel-request-btn">
                                                    Cancel Request
                                                </button>
                                            </form>
                                        @elseif($event->status === 'rejected')
                                            <!-- Rejected event - show buttons based on cooldown -->
                                            @if($event->canResubmit())
                                                <!-- Can resubmit - show both Continue and Resubmit buttons -->
                                                <div class="flex gap-2">
                                                    <a href="{{ route('organizer.events.step1', ['continue' => $event->id]) }}" class="flex-1 bg-yellow-100 dark:bg-yellow-900 hover:bg-yellow-200 dark:hover:bg-yellow-800 text-yellow-700 dark:text-yellow-300 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center justify-center">
                                                        Continue
                                                    </a>
                                                    <form method="POST" action="{{ route('organizer.events.update-status', $event) }}" class="flex-1">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="status" value="pending_approval">
                                                        <button type="button" class="w-full bg-green-100 dark:bg-green-900 hover:bg-green-200 dark:hover:bg-green-800 text-green-700 dark:text-green-300 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center justify-center publish-btn">
                                                            Resubmit
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <!-- Still in cooldown - only show Continue button -->
                                                <a href="{{ route('organizer.events.step1', ['continue' => $event->id]) }}" class="flex-1 bg-yellow-100 dark:bg-yellow-900 hover:bg-yellow-200 dark:hover:bg-yellow-800 text-yellow-700 dark:text-yellow-300 px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center justify-center">
                                                    Continue
                                                </a>
                                            @endif
                                        @elseif(in_array($event->status, ['approved','published','scheduled']))
                                            <a href="{{ route('organizer.events.manage', $event) }}" 
                                               class="flex-1 bg-qricket-100 dark:bg-qricket-900 hover:bg-qricket-200 dark:hover:bg-qricket-800 text-qricket-700 dark:text-qricket-300 px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center justify-center"
                                               onclick="console.log('Manage button clicked', {eventId: {{ $event->id }}, eventTitle: '{{ $event->title }}', eventStatus: '{{ $event->status }}', eventStartDate: '{{ $event->start_date }}', eventEndDate: '{{ $event->end_date }}', manageUrl: '{{ route('organizer.events.manage', $event) }}'}); return true;">
                                                Manage
                                            </a>
                                        @endif
                                    @elseif($hasAnyAccess)
                                        <a href="{{ route('organizer.events.manage', $event) }}" 
                                           class="flex-1 bg-qricket-100 dark:bg-qricket-900 hover:bg-qricket-200 dark:hover:bg-qricket-800 text-qricket-700 dark:text-qricket-300 px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center justify-center"
                                           onclick="console.log('Manage button clicked (hasAnyAccess)', {eventId: {{ $event->id }}, eventTitle: '{{ $event->title }}', eventStatus: '{{ $event->status }}', eventStartDate: '{{ $event->start_date }}', eventEndDate: '{{ $event->end_date }}', manageUrl: '{{ route('organizer.events.manage', $event) }}', hasAnyAccess: true}); return true;">
                                            Manage
                                        </a>
                                    @endif
                                
                                <!-- Preview button with eye icon only on the right -->
                                <a href="{{ route('organizer.events.preview', $event) }}" class="bg-qricket-500 hover:bg-qricket-600 text-white px-3 py-2 rounded-lg transition-colors duration-200 flex items-center justify-center" title="Preview Event">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-gray-400 dark:text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No events found</h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-6">You haven't created any events yet. Get started by creating your first event!</p>
                            @if(auth()->user()->isVerifiedOrganizer() || Auth::guard('admin')->check())
                                <a href="{{ route('organizer.events.step1', ['new' => 1]) }}" class="bg-qricket-500 hover:bg-qricket-600 text-white px-6 py-3 rounded-lg transition-colors duration-200 inline-flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Create Your First Event
                                </a>
                            @else
                                <a href="{{ route('organizer.verification') }}" class="bg-qricket-500 hover:bg-qricket-600 text-white px-6 py-3 rounded-lg transition-colors duration-200 inline-flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Get verified now to create an event
                                </a>
                            @endif
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($events->hasPages())
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="px-6 py-4">
                                {{ $events->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Success/Error Messages -->
    <div id="notification-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <script>
        // simple popover toggle for 3-dot menus
        document.addEventListener('click', function(e){
            const btn = e.target.closest('[data-menu-toggle]');
            if(btn){
                const id = btn.getAttribute('data-menu-toggle');
                const menu = document.getElementById(id);
                if(menu){ menu.classList.toggle('hidden'); }
                // hide others
                document.querySelectorAll('[id^="event-menu-"]').forEach(m=>{ if(m.id!==id){ m.classList.add('hidden'); }});
                return;
            }
            // clicking outside closes all
            if(!e.target.closest('[id^="event-menu-"]')){
                document.querySelectorAll('[id^="event-menu-"]').forEach(m=>m.classList.add('hidden'));
            }
        });

        function hideEventMenu(id){
            const el = document.getElementById(id); if(el){ el.classList.add('hidden'); }
        }
        function deleteEvent(eventId, button) {
            console.log('Delete event called with ID:', eventId);
            
            // Show confirmation dialog using SweetAlert
            if (typeof Swal !== 'undefined' && window.Swal) {
                Swal.fire({
                    title: 'Delete Event?',
                    text: 'Are you sure you want to delete this event? This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        proceedWithDelete(eventId, button);
                    }
                });
            } else {
                // Fallback to confirm if SweetAlert is not available
                if (confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
                    proceedWithDelete(eventId, button);
                }
            }
        }
        
        function proceedWithDelete(eventId, button) {

            // Store the event card element for removal
            const eventCard = document.querySelector(`[data-event-id="${eventId}"]`);
            console.log('Event card found:', eventCard);
            
            if (!eventCard) {
                console.error('Event card not found for event ID:', eventId);
                if (typeof Swal !== 'undefined' && window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Could not find event card to remove.',
                    });
                } else {
                    showNotification('Could not find event card to remove.', 'error');
                }
                return;
            }
            
            // Show loading state
            const originalText = button.textContent;
            button.textContent = 'Deleting...';
            button.disabled = true;
            button.classList.add('opacity-50', 'cursor-not-allowed');

            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                console.error('CSRF token not found');
                if (typeof Swal !== 'undefined' && window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Security Error',
                        text: 'Security token not found. Please refresh the page.',
                    });
                } else {
                    showNotification('Security token not found. Please refresh the page.', 'error');
                }
                return;
            }

            // Send AJAX request
            fetch(`/organizer/events/${eventId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                console.log('Delete response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Delete response data:', data);
                if (data.success) {
                    // Show success notification
                    if (typeof Swal !== 'undefined' && window.Swal) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Event Deleted',
                            text: 'Event deleted successfully!',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        showNotification('Event deleted successfully!', 'success');
                    }
                    
                    // Remove the event card with animation
                    eventCard.style.transition = 'all 0.3s ease-out';
                    eventCard.style.opacity = '0';
                    eventCard.style.transform = 'scale(0.95)';
                    
                    setTimeout(() => {
                        eventCard.remove();
                        
                        // Check if no events remain
                        const eventGrid = document.querySelector('.grid');
                        const remainingEvents = eventGrid.querySelectorAll('[data-event-id]');
                        
                        if (remainingEvents.length === 0) {
                            // Show empty state
                            eventGrid.innerHTML = `
                                <div class="col-span-full">
                                    <div class="text-center py-12">
                                        <svg class="w-16 h-16 text-gray-400 dark:text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No events found</h3>
                                        <p class="text-gray-500 dark:text-gray-400 mb-6">You haven't created any events yet. Get started by creating your first event!</p>
                                        ${document.querySelector('[href*="organizer.events.step1"]').outerHTML}
                            </div>
                        </div>
                            `;
                        }
                    }, 300);
                } else {
                    throw new Error(data.message || 'Failed to delete event');
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                
                // Show error notification
                if (typeof Swal !== 'undefined' && window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Delete Failed',
                        text: 'Failed to delete event. Please try again.',
                    });
                } else {
                    showNotification('Failed to delete event. Please try again.', 'error');
                }
                
                // Restore button state
                button.textContent = originalText;
                button.disabled = false;
                button.classList.remove('opacity-50', 'cursor-not-allowed');
            });
        }

        function showRejectionReason(eventId, rejectionReason, eventTitle) {
            Swal.fire({
                title: 'Rejection Reason',
                html: `
                    <div class="text-left">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                            <strong class="text-gray-900 dark:text-white">Event:</strong> ${eventTitle}
                        </p>
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                            <p class="text-sm text-red-700 dark:text-red-300 whitespace-pre-wrap">${rejectionReason}</p>
                        </div>
                    </div>
                `,
                icon: 'error',
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'I Understand',
                width: '500px'
            });
        }

        function showNotification(message, type = 'info') {
            const container = document.getElementById('notification-container');
            
            const notification = document.createElement('div');
            notification.className = `px-4 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${getNotificationClasses(type)}`;
            
            notification.innerHTML = `
                <div class="flex items-center">
                    <div class="flex-shrink-0 mr-3">
                        ${getNotificationIcon(type)}
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium">${message}</p>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-3 flex-shrink-0 text-current opacity-70 hover:opacity-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                </button>
                </div>
            `;
            
            container.appendChild(notification);
            
            // Trigger animation
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            }, 5000);
        }

        function getNotificationClasses(type) {
            switch (type) {
                case 'success':
                    return 'bg-green-50 dark:bg-green-900 text-green-800 dark:text-green-200 border border-green-200 dark:border-green-700';
                case 'error':
                    return 'bg-red-50 dark:bg-red-900 text-red-800 dark:text-red-200 border border-red-200 dark:border-red-700';
                case 'warning':
                    return 'bg-yellow-50 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 border border-yellow-200 dark:border-yellow-700';
                default:
                    return 'bg-blue-50 dark:bg-blue-900 text-blue-800 dark:text-blue-200 border border-blue-200 dark:border-blue-700';
            }
        }

        function getNotificationIcon(type) {
            switch (type) {
                case 'success':
                    return '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                case 'error':
                    return '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
                case 'warning':
                    return '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>';
                default:
                    return '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
            }
        }

        // Countdown timer for rejected events
        let timerInterval = null;
        
        function updateRejectedEventTimers() {
            const rejectedElements = document.querySelectorAll('[data-rejected-at]');
            
            if (rejectedElements.length === 0) {
                // No rejected events, stop the timer if it's running
                if (timerInterval) {
                    clearInterval(timerInterval);
                    timerInterval = null;
                }
                return;
            }
            
            rejectedElements.forEach(function(element) {
                const rejectedAt = new Date(element.dataset.rejectedAt);
                const now = new Date();
                const cooldownEnd = new Date(rejectedAt.getTime() + (30 * 60 * 1000)); // 30 minutes
                
                if (now < cooldownEnd) {
                    const timeRemaining = cooldownEnd - now;
                    const minutes = Math.floor(timeRemaining / (1000 * 60));
                    const seconds = Math.floor((timeRemaining % (1000 * 60)) / 1000);
                    
                    const timerElement = element.querySelector('.cooldown-timer');
                    if (timerElement) {
                        timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                    }
                } else {
                    // Cooldown expired, just show the "can resubmit" message
                    // Don't auto-reload - user can manually refresh if needed
                    const messageElement = element.querySelector('.text-yellow-700, .text-yellow-300');
                    const canResubmitElement = element.querySelector('.text-green-700, .text-green-300');
                    if (messageElement && canResubmitElement) {
                        messageElement.classList.add('hidden');
                        canResubmitElement.classList.remove('hidden');
                    }
                }
            });
        }

        // Start the timer only if there are rejected events with active cooldowns
        if (document.querySelectorAll('[data-rejected-at]').length > 0) {
            timerInterval = setInterval(updateRejectedEventTimers, 1000);
            updateRejectedEventTimers(); // Initial call
        }
    </script>
</x-app-layout>
<script>
document.addEventListener('DOMContentLoaded', function(){
  // Clear any cached error messages from previous middleware blocks
  const errorAlerts = document.querySelectorAll('.alert-error, .bg-red-100, .text-red-800');
  errorAlerts.forEach(alert => {
    if (alert.textContent.includes('not available for management') || 
        alert.textContent.includes('Event Ended')) {
      console.log('Removing cached error message:', alert.textContent);
      alert.remove();
    }
  });
  
  // Track all navigation attempts
  document.addEventListener('click', function(e) {
    if (e.target.tagName === 'A' && e.target.href) {
      console.log('Navigation attempt:', {
        href: e.target.href,
        text: e.target.textContent.trim(),
        isManageButton: e.target.textContent.trim() === 'Manage',
        eventId: e.target.href.match(/\/events\/(\d+)\/manage/)?.[1]
      });
    }
  });
  
  // Track page visibility changes (to detect redirects)
  document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
      console.log('Page became hidden - possible redirect');
    } else {
      console.log('Page became visible');
    }
  });
  
  document.querySelectorAll('.cancel-request-form').forEach(function(form){
    var btn = form.querySelector('.cancel-request-btn');
    if(!btn) return;
    btn.addEventListener('click', function(e){
      e.preventDefault();
      if (window.showConfirm) {
        showConfirm({
          title: 'Cancel approval request?',
          text: 'This will set the event back to Draft so you can continue editing.',
          confirmButtonText: 'Yes, cancel it',
          cancelButtonText: 'No',
        }).then(function(confirmed){ if(confirmed){ form.submit(); } });
      } else {
        // Fallback to SweetAlert or confirm
        if (typeof Swal !== 'undefined' && window.Swal) {
          Swal.fire({
            title: 'Cancel Approval Request?',
            text: 'This will set the event back to Draft so you can continue editing.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, cancel it',
            cancelButtonText: 'No, keep it',
            confirmButtonColor: '#059669',
            cancelButtonColor: '#6b7280'
          }).then((result) => {
            if (result.isConfirmed) {
              form.submit();
            }
          });
        } else {
          if (confirm('Cancel approval request and set event back to Draft?')) { form.submit(); }
        }
      }
    });
  });
});
</script>
