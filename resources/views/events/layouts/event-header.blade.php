<!-- Event Header Layout -->
<div class="event-header-section relative overflow-hidden">
    <div class="relative min-h-[400px] md:min-h-[500px]">
        <!-- Hero Background Image -->
        @if(isset($details['hero_image']) || isset($event->thumbnail_url) || isset($event->poster_url))
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" 
                 style="background-image: url('{{ $details['hero_image'] ?? $event->thumbnail_url ?? $event->poster_url ?? asset('images/default-event-header.jpg') }}');">
                <div class="absolute inset-0 bg-black bg-opacity-50"></div>
            </div>
        @else
            <!-- Default gradient background if no image -->
            <div class="absolute inset-0 bg-gradient-to-br from-qricket-500 via-qricket-600 to-qricket-700"></div>
        @endif
        
        <!-- Content Container -->
        <div class="relative z-10 h-full flex items-center">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                <div class="max-w-4xl">
                    <!-- Event Title -->
                    <div class="mb-6">
                        @if(isset($details['title']) && $details['title'])
                            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-2 leading-tight">
                                {{ $details['title'] }}
                            </h1>
                        @else
                            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-2 leading-tight">
                                {{ $event->title }}
                            </h1>
                        @endif
                        
                        <!-- Category Badge -->
                        @if(isset($details['category']) || isset($event->category))
                            <span class="inline-block px-3 py-1 bg-green-500 text-white text-sm font-medium rounded-full">
                                {{ ucfirst($details['category'] ?? $event->category ?? 'Event') }}
                            </span>
                        @endif
                    </div>
                    
                    <!-- Event Details Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <!-- Date & Time -->
                        <div class="flex items-center text-white">
                            <div class="flex-shrink-0 w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-semibold">
                                    @if(isset($event->start_date) && $event->start_date)
                                        {{ \Carbon\Carbon::parse($event->start_date)->format('l, F j, Y') }}
                                    @elseif(isset($details['date']))
                                        {{ $details['date'] }}
                                    @else
                                        Event Date
                                    @endif
                                </div>
                                <div class="text-sm opacity-90">
                                    @if(isset($event->start_date) && $event->start_date)
                                        {{ \Carbon\Carbon::parse($event->start_date)->format('g:i A') }}
                                        @if(isset($event->end_date) && $event->end_date)
                                            - {{ \Carbon\Carbon::parse($event->end_date)->format('g:i A') }}
                                        @endif
                                    @elseif(isset($details['time']))
                                        {{ $details['time'] }}
                                    @else
                                        Event Time
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Location -->
                        <div class="flex items-center text-white">
                            <div class="flex-shrink-0 w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-semibold">
                                    @if(isset($event->location) && $event->location)
                                        {{ $event->location }}
                                    @elseif(isset($details['venue']))
                                        {{ $details['venue'] }}
                                    @else
                                        Event Venue
                                    @endif
                                </div>
                                <div class="text-sm opacity-90">
                                    @if(isset($event->city) && $event->city)
                                        {{ $event->city }}
                                        @if(isset($event->state) && $event->state)
                                            , {{ $event->state }}
                                        @endif
                                        @if(isset($event->country) && $event->country)
                                            , {{ $event->country }}
                                        @endif
                                    @elseif(isset($details['location']))
                                        {{ $details['location'] }}
                                    @else
                                        Location
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <div class="mb-8">
                        <div class="text-lg text-gray-200 leading-relaxed max-w-3xl">
                            @if(isset($details['description']) && $details['description'])
                                {!! $details['description'] !!}
                            @elseif(isset($event->description) && $event->description)
                                {{ Str::limit($event->description, 300) }}
                            @elseif(isset($details['content']) && $details['content'])
                                {!! Str::limit(strip_tags($details['content']), 300) !!}
                            @else
                                Join us for an amazing event you won't want to miss!
                            @endif
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    @if(isset($details['show_buttons']) && $details['show_buttons'] !== false)
                        <div class="flex flex-col sm:flex-row sm:gap-4 gap-3">
                            @if(isset($details['primary_button_text']) || isset($event->ticketTypes) && count($event->ticketTypes) > 0)
                                <button type="button" class="bg-green-500 hover:bg-green-600 text-white px-8 py-3 rounded-lg text-lg font-medium transition-colors duration-200 flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                    </svg>
                                    {{ $details['primary_button_text'] ?? 'Buy Ticket' }}
                                </button>
                            @endif
                            
                            @if($details['secondary_button_text'] ?? true)
                                <button type="button" class="bg-gray-600 hover:bg-gray-700 text-white px-8 py-3 rounded-lg text-lg font-medium transition-colors duration-200 flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                    {{ $details['secondary_button_text'] ?? 'Save Event' }}
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
