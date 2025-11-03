<!-- Event Details Cards Layout -->
<div class="event-details-cards-section py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(isset($details['title']) && $details['title'])
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">{{ $details['title'] }}</h2>
            @if(isset($details['subtitle']) && $details['subtitle'])
            <p class="text-lg text-gray-900 dark:text-gray-400">{{ $details['subtitle'] }}</p>
            @endif
        </div>
        @endif
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @if(isset($details['cards']) && is_array($details['cards']))
                @foreach($details['cards'] as $index => $card)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 text-center">
                    @if(isset($card['icon']) && $card['icon'])
                    <div class="w-16 h-16 bg-qricket-100 dark:bg-qricket-900 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="{{ $card['icon'] }} text-2xl text-qricket-600 dark:text-qricket-400"></i>
                    </div>
                    @endif
                    @if(isset($card['title']) && $card['title'])
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ $card['title'] }}</h3>
                    @endif
                    @if(isset($card['content']) && $card['content'])
                    <div class="text-gray-900 dark:text-gray-400">
                        {!! $card['content'] !!}
                    </div>
                    @endif
                </div>
                @endforeach
            @else
                <!-- Default cards if no data provided -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 text-center">
                    <div class="w-16 h-16 bg-qricket-100 dark:bg-qricket-900 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-qricket-600 dark:text-qricket-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Event Details</h3>
                    <p class="text-gray-900 dark:text-gray-400">Add your event details here</p>
                </div>
            @endif
        </div>
    </div>
</div>
