<x-app-layout title="{{ $organizer->full_name }} - Organizer Profile" page-title="{{ $organizer->full_name }}">
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Back Button -->
            <div class="mb-4">
                <button type="button" onclick="goBack()" 
                   class="inline-flex items-center text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-qricket-500 dark:hover:text-qricket-400 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back
                </button>
            </div>

            <!-- Compact Organizer Header -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        <!-- Profile Picture -->
                        <div class="flex-shrink-0">
                            @if($organizer->profile_picture)
                                <img src="{{ $organizer->profile_picture_url }}" 
                                     alt="{{ $organizer->full_name }}" 
                                     class="h-16 w-16 rounded-full object-cover border-2 border-gray-300 dark:border-gray-600">
                            @else
                                <div class="h-16 w-16 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center border-2 border-gray-300 dark:border-gray-600">
                                    <span class="text-2xl font-bold text-gray-600 dark:text-gray-400">
                                        {{ strtoupper(substr($organizer->first_name, 0, 1) . (str_contains($organizer->full_name, ' ') ? substr($organizer->full_name, strpos($organizer->full_name, ' ') + 1, 1) : '')) }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Organizer Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                                    {{ $organizer->full_name }}
                                </h1>
                                @if($organizer->isVerifiedOrganizer())
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Verified Organizer
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                Event Organizer
                            </p>

                            <!-- Email Link -->
                            @if($organizer->email)
                                <div class="mb-2">
                                    <a href="mailto:{{ $organizer->email }}?subject={{ urlencode('Question about your events') }}" 
                                       class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium transition-colors duration-200"
                                       style="background-color: #dc354520; color: #dc3545; border: 1px solid #dc354540;"
                                       onmouseover="this.style.backgroundColor='#dc3545'; this.style.color='white';"
                                       onmouseout="this.style.backgroundColor='#dc354520'; this.style.color='#dc3545';">
                                        <svg class="w-3 h-3 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        <span>{{ $organizer->email }}</span>
                                        <svg class="w-3 h-3 ml-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                    </a>
                                </div>
                            @endif

                            <!-- Social Media Links -->
                            @if($socialLinks->count() > 0)
                                <div class="flex flex-wrap gap-2">
                                    @foreach($socialLinks as $link)
                                        @if($link->full_url)
                                            <a href="{{ $link->full_url }}" 
                                               target="_blank" 
                                               rel="noopener noreferrer"
                                               class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium transition-colors duration-200"
                                               style="background-color: {{ $link->platform_color }}20; color: {{ $link->platform_color }}; border: 1px solid {{ $link->platform_color }}40;"
                                               onmouseover="this.style.backgroundColor='{{ $link->platform_color }}'; this.style.color='white';"
                                               onmouseout="this.style.backgroundColor='{{ $link->platform_color }}20'; this.style.color='{{ $link->platform_color }}';">
                                                <span>{{ $link->platform_name }}</span>
                                                <svg class="w-3 h-3 ml-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                </svg>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Event Type Tabs -->
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <!-- Event Type Tabs -->
                <div class="flex gap-2 border-b border-gray-200 dark:border-gray-700">
                    <a href="{{ route('organizer.profile', [$organizer->id, 'type' => 'organized', 'filter' => $filter]) }}" 
                       class="px-4 py-2 text-sm font-medium {{ $type === 'organized' ? 'text-qricket-600 dark:text-qricket-400 border-b-2 border-qricket-600 dark:border-qricket-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }} transition-colors">
                        Events Organized ({{ $organizedCount }})
                    </a>
                    <a href="{{ route('organizer.profile', [$organizer->id, 'type' => 'staff', 'filter' => $filter]) }}" 
                       class="px-4 py-2 text-sm font-medium {{ $type === 'staff' ? 'text-qricket-600 dark:text-qricket-400 border-b-2 border-qricket-600 dark:border-qricket-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }} transition-colors">
                        Part Of ({{ $staffCount }})
                    </a>
                    <a href="{{ route('organizer.profile', [$organizer->id, 'type' => 'all', 'filter' => $filter]) }}" 
                       class="px-4 py-2 text-sm font-medium {{ $type === 'all' ? 'text-qricket-600 dark:text-qricket-400 border-b-2 border-qricket-600 dark:border-qricket-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }} transition-colors">
                        All Events
                    </a>
                </div>

                <!-- Date Filter Buttons -->
                <div class="flex gap-2">
                    <a href="{{ route('organizer.profile', [$organizer->id, 'type' => $type, 'filter' => 'all']) }}" 
                       class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors {{ $filter === 'all' ? 'bg-qricket-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                        All
                    </a>
                    <a href="{{ route('organizer.profile', [$organizer->id, 'type' => $type, 'filter' => 'upcoming']) }}" 
                       class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors {{ $filter === 'upcoming' ? 'bg-qricket-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                        Upcoming
                    </a>
                    <a href="{{ route('organizer.profile', [$organizer->id, 'type' => $type, 'filter' => 'ended']) }}" 
                       class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors {{ $filter === 'ended' ? 'bg-qricket-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                        Ended
                    </a>
                </div>
            </div>

            <!-- Events Grid (Same design as browse page) -->
            @if($events->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
                    @foreach($events as $event)
                        @php
                            $staffRole = null;
                            if ($type === 'staff' || $type === 'all') {
                                $staffRole = \App\Models\EventStaff::where('event_id', $event->id)
                                    ->where('user_id', $organizer->id)
                                    ->first();
                            }
                        @endphp
                        <a href="{{ route('events.show', $event) }}" class="group block bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transition-all duration-200 hover:shadow-xl hover:-translate-y-0.5">
                            <!-- Event Image -->
                            <div class="relative h-40 sm:h-44 bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                @if($event->thumbnail_url)
                                    <img src="{{ $event->thumbnail_url }}"
                                         alt="{{ $event->title }}"
                                         class="w-full h-full object-cover filter grayscale group-hover:grayscale-0 transition duration-300 ease-out">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-b from-black/0 via-black/0 to-black/20"></div>
                                
                                <!-- Category Badge -->
                                <div class="absolute top-3 left-3">
                                    <span class="px-2.5 py-1 text-[11px] font-medium bg-qricket-500 text-white rounded-full shadow-sm">
                                        {{ ucfirst($event->category ?? 'event') }}
                                    </span>
                                </div>
                                
                                <!-- Date Badge -->
                                <div class="absolute top-3 right-3">
                                    <span class="px-2.5 py-1 text-[11px] font-semibold bg-black/60 text-white rounded-full shadow-sm">
                                        {{ optional($event->start_date)->format('M j') }}
                                    </span>
                                </div>

                                <!-- Role Badge (if staff) -->
                                @if($staffRole)
                                    <div class="absolute bottom-3 left-3">
                                        <span class="px-2.5 py-1 text-[11px] font-medium bg-qricket-600 text-white rounded-full shadow-sm">
                                            {{ ucfirst($staffRole->role) }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <!-- Event Content -->
                            <div class="p-3 sm:p-4">
                                <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100 mb-1 sm:mb-2 line-clamp-2 min-h-[3rem] flex items-start">
                                    {{ $event->title }}
                                </h3>
                                
                                <!-- Description -->
                                <div class="text-xs text-gray-600 dark:text-gray-400 mb-2 line-clamp-2 leading-relaxed min-h-[2.5rem]">
                                    {{ $event->description ? \Illuminate\Support\Str::limit($event->description, 120) : '&nbsp;' }}
                                </div>

                                <!-- Event Details -->
                                <div class="space-y-1">
                                    @if($event->location)
                                        <div class="flex items-center text-xs text-gray-600 dark:text-gray-400">
                                            <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <span class="truncate">{{ $event->location }}</span>
                                        </div>
                                    @endif
                                    <div class="flex items-center text-xs text-gray-600 dark:text-gray-400">
                                        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span>{{ optional($event->start_date)->format('M d, Y \a\t g:i A') }}</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $events->appends(request()->query())->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Events Found</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            @if($filter === 'upcoming')
                                No upcoming events found.
                            @elseif($filter === 'ended')
                                No ended events found.
                            @else
                                This organizer hasn't published any events yet.
                            @endif
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

<script>
// Smart back navigation for organizer profile
function goBack() {
    const urlParams = new URLSearchParams(window.location.search);
    const currentPath = window.location.pathname;
    const referrer = document.referrer;
    
    // Store current page in sessionStorage before navigating away (for future use)
    const previousUrl = sessionStorage.getItem('organizer_profile_back_url');
    
    // Priority 1: Check URL parameter (most reliable)
    const backParam = urlParams.get('back');
    if (backParam) {
        try {
            const decodedUrl = decodeURIComponent(backParam);
            // Validate it's a valid internal URL
            if (decodedUrl.startsWith('/') || decodedUrl.includes(window.location.hostname)) {
                window.location.href = decodedUrl;
                return;
            }
        } catch (e) {
            console.warn('Invalid back parameter:', backParam);
        }
    }
    
    // Priority 2: Check sessionStorage (stored when navigating to this page)
    if (previousUrl && previousUrl !== currentPath) {
        window.location.href = previousUrl;
        return;
    }
    
    // Priority 3: Check if coming from events browse or specific routes
    if (referrer) {
        const referrerUrl = new URL(referrer);
        const currentUrl = new URL(window.location.href);
        
        // Only use referrer if it's from the same origin
        if (referrerUrl.origin === currentUrl.origin) {
            const referrerPath = referrerUrl.pathname;
            
            // If referrer is events browse or event detail, go there
            if (referrerPath.includes('/events') && !referrerPath.includes('/organizer/') && !referrerPath.includes('/create')) {
                window.location.href = referrerPath + referrerUrl.search;
                return;
            }
            
            // If referrer is dashboard, go there
            if (referrerPath.includes('/dashboard')) {
                window.location.href = referrerPath;
                return;
            }
            
            // If referrer is another organizer profile (probably a filter change), go to events browse
            if (referrerPath.includes('/organizer/') && referrerPath.includes('/profile')) {
                window.location.href = '{{ route("events.browse") }}';
                return;
            }
            
            // If referrer is valid and different from current page, use it
            if (referrerPath !== currentPath) {
                window.location.href = referrerPath + referrerUrl.search;
                return;
            }
        }
    }
    
    // Priority 4: Use browser history if available
    if (window.history.length > 1) {
        window.history.back();
    } else {
        // Default fallback: events browse
        window.location.href = '{{ route("events.browse") }}';
    }
}

// Store referrer when page loads (for next time)
document.addEventListener('DOMContentLoaded', function() {
    const referrer = document.referrer;
    const currentUrl = new URL(window.location.href);
    
    if (referrer) {
        try {
            const referrerUrl = new URL(referrer);
            // Only store if referrer is from same origin and not the same page
            if (referrerUrl.origin === currentUrl.origin && referrerUrl.pathname !== currentUrl.pathname) {
                // Store the full referrer URL for back navigation
                sessionStorage.setItem('organizer_profile_back_url', referrerUrl.pathname + referrerUrl.search);
            }
        } catch (e) {
            console.warn('Could not parse referrer:', referrer);
        }
    }
});
</script>
