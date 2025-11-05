@props(['event', 'section', 'previewPosterUrl' => null, 'previewThumbnailUrl' => null, 'previewMode' => false, 'hideActions' => false, 'containerClass' => ''])
@php
    $isAdmin = \Illuminate\Support\Facades\Auth::guard('admin')->check();
    $webUser = \Illuminate\Support\Facades\Auth::guard('web')->user();
    $isSaved = false;
    if ($webUser && method_exists($webUser, 'savedEvents')) {
        try {
            $isSaved = $webUser->savedEvents()->where('event_id', optional($event)->id)->exists();
        } catch (\Throwable $t) {
            $isSaved = false;
        }
    }
    
    // Allow preview overrides for poster/thumbnail during creation/editing
    $computedThumbnailUrl = $previewThumbnailUrl ?: (optional($event)->thumbnail_url ?? null);
    $computedPosterUrl = $previewPosterUrl ?: (optional($event)->poster_url ?? null);
    
    // Check if event is published (safe for preview/stdClass)
    $isEventPublished = (optional($event)->status ?? null) === 'published';
    
    // Check if user is the event organizer or staff (guard for preview)
    $isEventOrganizer = $webUser && ((optional($event)->user_id ?? null) === $webUser->id);
    $isEventStaff = false;
    if (!$previewMode && $webUser && is_object($event) && method_exists($event, 'staff')) {
        try { $isEventStaff = $event->staff()->where('user_id', $webUser->id)->exists(); } catch (\Throwable $t) { $isEventStaff = false; }
    }
    // Allow organizers and staff to purchase even if not yet published
    $canBuyTickets = $isEventPublished || $isEventOrganizer || $isEventStaff || $isAdmin;
    
    // Debug: Log the values for troubleshooting
    // \Log::info('Event Header Debug', [
    //     'isEventPublished' => $isEventPublished,
    //     'isEventOrganizer' => $isEventOrganizer,
    //     'isEventStaff' => $isEventStaff,
    //     'isAdmin' => $isAdmin,
    //     'canBuyTickets' => $canBuyTickets,
    //     'eventEnded' => optional($event->end_date)->isPast(),
    //     'user_id' => $webUser?->id,
    //     'event_user_id' => $event->user_id ?? null,
    //     'event_status' => $event->status ?? null,
    //     'event_end_date' => $event->end_date ?? null
    // ]);
@endphp

<!-- Event Card Preview -->
<div class="relative bg-gray-900 rounded-none overflow-hidden mb-8 {{ $previewMode ? 'p-0' : 'p-6' }} {{ $containerClass }}" style="min-height: 220px;">
    <!-- Thumbnail as Background -->
    @if($computedThumbnailUrl)
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" @if($previewMode) id="step2-header-bg" @endif
             style="background-image: url('{{ $computedThumbnailUrl }}'); filter: grayscale(100%) contrast(150%) blur(3px);"></div>
    @else
        <div class="absolute inset-0 bg-gray-900" @if($previewMode) id="step2-header-bg" @endif></div>
    @endif
    
    <!-- Dark Overlay for Text Readability -->
    <div class="absolute inset-0 bg-black bg-opacity-60 backdrop-blur-sm"></div>
    
    <!-- Content -->
    <div class="relative z-10 flex flex-col sm:flex-row {{ $previewMode ? 'px-0' : '' }}">
        <!-- Poster on Left Side -->
        <div class="w-48 h-64 mx-auto sm:mx-0 sm:w-44 lg:w-48 flex-shrink-0 {{ $previewMode ? 'p-0' : 'p-2 sm:p-3 lg:p-4' }} flex items-center">
            @if($computedPosterUrl)
                <img src="{{ $computedPosterUrl }}" alt="{{ optional($event)->title }} poster" class="w-full h-full object-cover {{ $previewMode ? 'rounded-none' : 'rounded-lg' }} shadow-lg" @if($previewMode) id="step2-header-poster" @endif>
            @else
                <div class="w-full h-full bg-black rounded-lg flex items-center justify-center shadow-lg" @if($previewMode) id="step2-header-poster" @endif>
                    <div class="flex flex-col items-center justify-center text-white text-center p-3">
                        <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span class="text-sm font-medium">Poster</span>
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Event Details -->
        <div class="flex-1 {{ $previewMode ? 'p-4' : 'p-2 sm:p-3 lg:p-4' }} text-white">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between mb-4 sm:mb-6">
                <div class="mb-3 sm:mb-0">
                    <h4 class="mb-1" style="font-size: 2rem; font-weight: 900; color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.8); line-height: 1.2;">{{ optional($event)->title }}</h4>
                </div>
                <span class="bg-green-400 text-white px-3 py-1 rounded-full text-xs font-medium self-start">
                    {{ ucfirst(optional($event)->category) }}
                </span>
            </div>
            
            <!-- Tags Section -->
            @if(optional($event)->tags)
                @php
                    $tagsArray = is_array(optional($event)->tags_array) ? optional($event)->tags_array : explode(',', optional($event)->tags);
                    $tagsArray = array_filter(array_map('trim', $tagsArray));
                    
                    // Random colors for tags
                    $colors = [
                        ['bg-red-500/20', 'text-red-300', 'border-red-400/30', 'hover:bg-red-500/30'],
                        ['bg-blue-500/20', 'text-blue-300', 'border-blue-400/30', 'hover:bg-blue-500/30'],
                        ['bg-green-500/20', 'text-green-300', 'border-green-400/30', 'hover:bg-green-500/30'],
                        ['bg-yellow-500/20', 'text-yellow-300', 'border-yellow-400/30', 'hover:bg-yellow-500/30'],
                        ['bg-purple-500/20', 'text-purple-300', 'border-purple-400/30', 'hover:bg-purple-500/30'],
                        ['bg-pink-500/20', 'text-pink-300', 'border-pink-400/30', 'hover:bg-pink-500/30'],
                        ['bg-teal-500/20', 'text-teal-300', 'border-teal-400/30', 'hover:bg-teal-500/30'],
                        ['bg-indigo-500/20', 'text-indigo-300', 'border-indigo-400/30', 'hover:bg-indigo-500/30'],
                    ];
                @endphp
                @if(count($tagsArray) > 0)
                    <div class="mb-3">
                        <div class="flex items-center flex-wrap gap-1.5">
                            @foreach($tagsArray as $index => $tag)
                                @php
                                    $colorIndex = $index % count($colors);
                                    $tagColors = $colors[$colorIndex];
                                @endphp
                                <a href="{{ route('events.browse', ['tag' => trim($tag)]) }}" 
                                   class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $tagColors[0] }} {{ $tagColors[1] }} border {{ $tagColors[2] }} hover:{{ $tagColors[3] }} transition-all duration-200 hover:scale-105">
                                    <svg class="w-2.5 h-2.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                    {{ trim($tag) }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
            
            @php
                // Calculate average rating for ended events
                $averageRating = null;
                $ratingCount = 0;
                if (optional($event)->end_date && optional($event)->end_date->isPast()) {
                    try {
                        $ratings = \App\Models\EventFeedback::where('event_id', optional($event)->id)
                            ->whereNotNull('rating')
                            ->get();
                        $ratingCount = $ratings->count();
                        if ($ratingCount > 0) {
                            $averageRating = round($ratings->avg('rating'), 1);
                        }
                    } catch (\Throwable $t) {
                        $averageRating = null;
                    }
                }
            @endphp
            
            @if($averageRating !== null && $ratingCount > 0)
                <div class="mb-3 flex items-center">
                    <div class="flex items-center">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= floor($averageRating))
                                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @elseif($i - 0.5 <= $averageRating)
                                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <defs>
                                        <linearGradient id="half{{ $i }}">
                                            <stop offset="50%" stop-color="currentColor"/>
                                            <stop offset="50%" stop-color="transparent" stop-opacity="1"/>
                                        </linearGradient>
                                    </defs>
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" fill="url(#half{{ $i }})"/>
                                </svg>
                            @else
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endif
                        @endfor
                        <span class="ml-2 text-white font-semibold">{{ number_format($averageRating, 1) }}</span>
                        <span class="ml-1 text-xs text-gray-300">({{ $ratingCount }} {{ Str::plural('rating', $ratingCount) }})</span>
                    </div>
                </div>
            @endif
            
            <div class="space-y-1 sm:space-y-2 text-xs sm:text-sm mb-4">
                <div class="text-white font-medium flex items-center">
                    <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    {{ optional($event)->start_date ? \Carbon\Carbon::parse(optional($event)->start_date)->format('M d, Y g:i A') : (optional($event)->date ? \Carbon\Carbon::parse(optional($event)->date)->format('M d, Y g:i A') : 'Date to be announced') }}
                </div>
                @if(optional($event)->location)
                <div class="text-white font-medium flex items-center">
                    <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    {{ optional($event)->location }}
                </div>
                @endif
                @if(optional($event)->description)
                <div class="mt-2">
                    <div class="leading-relaxed text-white">
                        <span id="description-short">{{ Str::limit(optional($event)->description, 120) }}</span>
                        @if(strlen(optional($event)->description ?? '') > 120)
                            <span id="description-full" class="hidden">{{ optional($event)->description }}</span>
                            <button onclick="toggleDescription()" id="view-more-btn" class="text-blue-300 hover:text-blue-200 underline ml-1">View more</button>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            
            @unless($hideActions)
            <div class="flex flex-col sm:flex-row gap-2 relative">
                @php
                    $eventEnded = optional(optional($event)->end_date)->isPast();
                    $webUser = $webUser ?? Auth::user();
                    $hasCertificate = false;
                    $hasSubmittedFeedback = false;
                    
                    if ($webUser) {
                        $hasCertificate = \App\Models\EventTicketInformation::where('event_id', optional($event)->id)
                            ->where('user_id', $webUser->id)
                            ->whereNotNull('checked_in_at')
                            ->where('certificate_given', true)
                            ->whereHas('ticketPurchase', function($q) {
                                $q->where('status', 'paid');
                            })
                            ->exists();
                            
                        // Check if user has already submitted feedback for this event
                        $hasSubmittedFeedback = \App\Models\EventFeedback::where('event_id', optional($event)->id)
                            ->where('user_id', $webUser->id)
                            ->exists();
                    }
                @endphp
                
                
                @if($eventEnded)
                <!-- Event has ended - show certificate dropdown if user has certificates -->
                @if($webUser && $hasCertificate)
                    @php
                        // Use the userCertificatePage route which handles multiple ticket selection automatically
                        $certUrl = route('events.certificate.view', $event);
                    @endphp
                    <a href="{{ $certUrl }}" target="_blank" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-lg text-xs flex items-center justify-center font-medium shadow-sm transition-colors duration-200">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        View Certificate
                    </a>
                @else
                    <div class="bg-gray-500 text-white px-3 py-1.5 rounded-lg text-xs flex items-center justify-center font-medium shadow-sm">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Event Ended
                    </div>
                    
                    <!-- Feedback Request Button for Event Attendees (when no certificate) -->
                    @php
                        $canGiveFeedback = $webUser && \App\Models\EventTicketInformation::where('event_id', optional($event)->id)
                            ->where('user_id', $webUser->id)
                            ->whereHas('ticketPurchase', function($q) {
                                $q->where('status', 'paid');
                            })
                            ->exists();
                    @endphp
                    
                    @if($canGiveFeedback)
                        <!-- Feedback button removed as requested -->
                    @endif
                @endif
                @elseif($isEventOrganizer || $isEventStaff || $isAdmin)
                <!-- For organizers, staff, and admins (when event hasn't ended), always allow buying tickets -->
                <a href="{{ route('tickets.purchase.show', $event) }}" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-lg text-xs flex items-center justify-center font-medium shadow-sm transition-colors duration-200">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                    Buy Ticket
                </a>
                @elseif($isEventPublished)
                <!-- Show for regular users only if event is published and not ended -->
                <a href="{{ route('tickets.purchase.show', $event) }}" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-lg text-xs flex items-center justify-center font-medium shadow-sm transition-colors duration-200">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                    Buy Ticket
                </a>
                @elseif(!$isEventPublished)
                <div class="bg-gray-500 text-white px-3 py-1.5 rounded-lg text-xs flex items-center justify-center font-medium shadow-sm">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    Event Not Published
                </div>
                @elseif(false)
                @elseif(!$isAdmin && $eventEnded && $hasCertificate)
                @php
                    // Use the userCertificatePage route which handles multiple ticket selection automatically
                    $publicCertUrl = route('events.certificate.view', $event);
                @endphp
                <a href="{{ $publicCertUrl }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-xs flex items-center justify-center font-medium shadow-sm transition-colors duration-200">
                    <i class="fa-solid fa-award mr-1"></i>
                    View Certificate
                </a>
                @elseif(!$webUser)
                <a href="{{ route('login') }}" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-lg text-xs flex items-center justify-center font-medium shadow-sm transition-colors duration-200">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                    Login to Buy Ticket
                </a>
                @endif
                @if(!$isAdmin)
                <button onclick="toggleSaveEvent({{ optional($event)->id }})" 
                        class="bg-teal-600 hover:bg-teal-700 text-white px-3 py-1.5 rounded-lg text-xs flex items-center justify-center font-medium shadow-sm transition-colors duration-200 save-event-btn"
                        data-event-id="{{ optional($event)->id }}" 
                        data-saved="{{ $isSaved ? 'true' : 'false' }}">
                    <i class="fa-solid fa-heart mr-1"></i>
                    <span class="save-text">{{ $isSaved ? 'Saved' : 'Save' }}</span>
                </button>
                @endif
                <button onclick="copyEventLink()" 
                        class="bg-purple-600 hover:bg-purple-700 text-white px-2.5 py-1.5 rounded-lg text-xs flex items-center justify-center font-medium shadow-sm transition-colors duration-200">
                    <i class="fa-solid fa-link text-sm"></i>
                </button>
            </div>
            @endunless
        </div>
    </div>
</div>

<!-- Navigation Buttons -->
@unless($hideActions)
<div class="flex w-full mb-8 bg-gray-900/30 backdrop-blur-sm rounded-xl p-1 border border-gray-700/30 shadow-lg">
    <a href="{{ route('events.show', $event) }}" 
       class="group flex-1 px-6 py-4 text-white font-medium transition-all duration-300 flex items-center justify-center rounded-lg {{ $section !== 'news' ? 'bg-qricket-500/15 border border-qricket-500/40 shadow-md shadow-qricket-500/20' : 'hover:bg-white/5 hover:border-white/10 border border-transparent' }}">
        <svg class="w-5 h-5 mr-2 transition-all duration-300 {{ $section !== 'news' ? 'text-qricket-400 scale-110' : 'text-gray-300 group-hover:text-white group-hover:scale-105' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span class="transition-all duration-300 {{ $section !== 'news' ? 'text-qricket-300 font-semibold' : 'text-gray-200 group-hover:text-white' }}">Event Info</span>
    </a>
    <a href="{{ route('events.show', ['event' => $event, 'section' => 'news']) }}" 
       class="group flex-1 px-6 py-4 text-white font-medium transition-all duration-300 flex items-center justify-center rounded-lg {{ $section === 'news' ? 'bg-qricket-500/15 border border-qricket-500/40 shadow-md shadow-qricket-500/20' : 'hover:bg-white/5 hover:border-white/10 border border-transparent' }}">
        <svg class="w-5 h-5 mr-2 transition-all duration-300 {{ $section === 'news' ? 'text-qricket-400 scale-110' : 'text-gray-300 group-hover:text-white group-hover:scale-105' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
        </svg>
        <span class="transition-all duration-300 {{ $section === 'news' ? 'text-qricket-300 font-semibold' : 'text-gray-200 group-hover:text-white' }}">Event News</span>
    </a>
</div>
@endunless

<script>
function toggleDescription() {
    const shortDesc = document.getElementById('description-short');
    const fullDesc = document.getElementById('description-full');
    const viewMoreBtn = document.getElementById('view-more-btn');
    
    if (fullDesc.classList.contains('hidden')) {
        // Show full description
        shortDesc.classList.add('hidden');
        fullDesc.classList.remove('hidden');
        viewMoreBtn.textContent = 'View less';
    } else {
        // Show short description
        shortDesc.classList.remove('hidden');
        fullDesc.classList.add('hidden');
        viewMoreBtn.textContent = 'View more';
    }
}

function copyEventLink() {
    const eventUrl = window.location.href;
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(eventUrl).then(() => {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Link copied!',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
                width: '200px'
            });
        });
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = eventUrl;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Link copied!',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true,
            width: '200px'
        });
    }
}

// Certificate dropdown functionality
function toggleCertificateDropdown() {
    const dropdown = document.getElementById('certificateDropdown');
    if (dropdown) {
        dropdown.classList.toggle('hidden');
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('#certificateDropdown') && !event.target.closest('button[onclick="toggleCertificateDropdown()"]')) {
                dropdown.classList.add('hidden');
            }
        });
    }
}
</script>
