@props(['event', 'section'])
@php
    $isAdmin = \Illuminate\Support\Facades\Auth::guard('admin')->check();
    $webUser = \Illuminate\Support\Facades\Auth::guard('web')->user();
    $isSaved = false;
    if ($webUser && method_exists($webUser, 'savedEvents')) {
        try {
            $isSaved = $webUser->savedEvents()->where('event_id', $event->id)->exists();
        } catch (\Throwable $t) {
            $isSaved = false;
        }
    }
@endphp

<!-- Event Card Preview -->
<div class="relative bg-gray-900 rounded-lg overflow-hidden mb-8" style="min-height: 220px;">
    <!-- Thumbnail as Background -->
    @if($event->thumbnail_url)
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" 
             style="background-image: url('{{ $event->thumbnail_url }}');"></div>
    @else
        <div class="absolute inset-0 bg-gray-900"></div>
    @endif
    
    <!-- Dark Overlay for Text Readability -->
    <div class="absolute inset-0 bg-black bg-opacity-30"></div>
    
    <!-- Content -->
    <div class="relative z-10 flex flex-col sm:flex-row">
        <!-- Poster on Left Side -->
        <div class="w-full sm:w-48 lg:w-56 h-56 sm:h-72 flex-shrink-0 p-4 sm:p-6 lg:p-8">
            @if($event->poster_url)
                <div class="w-full h-full bg-black rounded-lg bg-cover bg-center bg-no-repeat shadow-lg" 
                     style="background-image: url('{{ $event->poster_url }}');"></div>
            @else
                <div class="w-full h-full bg-black rounded-lg flex items-center justify-center shadow-lg">
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
        <div class="flex-1 p-4 sm:p-6 lg:p-8 text-white">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between mb-4 sm:mb-6">
                <div class="mb-3 sm:mb-0">
                    <h4 class="mb-2" style="font-size: 2.5rem; font-weight: 900; color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.8); line-height: 1.2;">{{ $event->title }}</h4>
                  
                </div>
                <span class="bg-green-400 text-white px-4 py-2 rounded-full text-sm font-medium self-start">
                    {{ ucfirst($event->category) }}
                </span>
            </div>
            
            <div class="space-y-2 sm:space-y-3 text-xs sm:text-sm text-gray-200 mb-6">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    {{ $event->start_date ? \Carbon\Carbon::parse($event->start_date)->format('M d, Y g:i A') : ($event->date ? \Carbon\Carbon::parse($event->date)->format('M d, Y g:i A') : 'Date to be announced') }}
                </div>
                @if($event->location)
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    {{ $event->location }}
                </div>
                @endif
                @if($event->description)
                <div class="mt-2">
                    <div class="font-medium mb-1">About this event:</div>
                    <div class="leading-relaxed">
                        <span id="description-short">{{ Str::limit($event->description, 120) }}</span>
                        @if(strlen($event->description) > 120)
                            <span id="description-full" class="hidden">{{ $event->description }}</span>
                            <button onclick="toggleDescription()" id="view-more-btn" class="text-blue-300 hover:text-blue-200 underline ml-1">View more</button>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            
            <div class="flex flex-col sm:flex-row gap-2 relative">
                @php($eventEnded = optional($event->end_date)->isPast())
                @php($webUser = $webUser ?? Auth::user())
                @php($hasCertificate = $webUser ? \App\Models\TicketInformation::where('event_id', $event->id)->where('user_id', $webUser->id)->whereNotNull('checked_in_at')->exists() : false)
                @if(!$isAdmin && !$eventEnded)
                <a href="{{ route('tickets.purchase.show', $event) }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-xs sm:text-sm flex items-center justify-center font-medium transition-colors duration-200">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                    Buy Ticket
                </a>
                @elseif(!$isAdmin && $eventEnded && $hasCertificate)
                @php
                    $ticketId = \App\Models\TicketInformation::where('event_id', $event->id)
                        ->where('user_id', optional($webUser)->id)
                        ->whereNotNull('checked_in_at')
                        ->orderBy('checked_in_at', 'asc')
                        ->value('id');
                    $publicCertUrl = $ticketId ? route('events.certificate.public', $event) . '?ticket=' . $ticketId : route('events.certificate.view', $event);
                @endphp
                <a href="{{ $publicCertUrl }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-xs sm:text-sm flex items-center justify-center font-medium transition-colors duration-200">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    View Certificate
                </a>
                @elseif(!$webUser)
                <a href="{{ route('login') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-xs sm:text-sm flex items-center justify-center font-medium transition-colors duration-200">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                    Login to Buy Ticket
                </a>
                @endif
                @if(!$isAdmin)
                <button onclick="toggleSaveEvent({{ $event->id }})" 
                        class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-xs sm:text-sm flex items-center justify-center font-medium transition-colors duration-200 save-event-btn"
                        data-event-id="{{ $event->id }}" 
                        data-saved="{{ $isSaved ? 'true' : 'false' }}">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="save-text">{{ $isSaved ? 'Saved' : 'Save Event' }}</span>
                </button>
                @endif
                <button onclick="copyEventLink()" 
                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-xs sm:text-sm flex items-center justify-center font-medium transition-colors duration-200">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    Share
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Navigation Buttons -->
<div class="flex w-full mb-8 bg-transparent">
    <a href="{{ route('events.show', $event) }}" 
       class="flex-1 px-6 py-3 text-white font-medium transition-colors duration-200 flex items-center justify-center {{ $section !== 'news' ? 'border-b-4 border-qricket-500' : 'border-b-4 border-transparent hover:border-gray-400' }}">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Event Info
    </a>
    <a href="{{ route('events.show', ['event' => $event, 'section' => 'news']) }}" 
       class="flex-1 px-6 py-3 text-white font-medium transition-colors duration-200 flex items-center justify-center {{ $section === 'news' ? 'border-b-4 border-qricket-500' : 'border-b-4 border-transparent hover:border-gray-400' }}">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
        </svg>
        Event News
    </a>
</div>

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
</script>
