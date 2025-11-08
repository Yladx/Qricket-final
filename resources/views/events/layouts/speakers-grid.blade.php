<!-- Speakers Grid Layout -->
<div class="speakers-grid-section py-8 md:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(isset($details['title']) && $details['title'])
        <div class="text-center mb-8 md:mb-12">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-gray-100 mb-3 md:mb-4">{{ $details['title'] }}</h2>
            @if(isset($details['subtitle']) && $details['subtitle'])
            <p class="text-base md:text-lg text-gray-900 dark:text-gray-400">{{ $details['subtitle'] }}</p>
            @endif
        </div>
        @endif
        
        @php
            // Get speakers array
            $speakers = isset($details['speakers']) && is_array($details['speakers']) ? $details['speakers'] : [];
            $speakerCount = count($speakers);

            // Flexible grid layout with max 3 columns
            // Fixed card size for consistency
            $fixedCardWidth = 'w-full max-w-sm';
            $fixedCardHeight = 'h-full min-h-[450px] sm:min-h-[500px]';
            
            // Determine grid layout based on speaker count
            // Always use max 3 columns for desktop
            if ($speakerCount === 1) {
                $gridClasses = 'grid grid-cols-1 gap-4 md:gap-6 lg:gap-8 justify-items-center';
            } elseif ($speakerCount === 2) {
                $gridClasses = 'grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6 lg:gap-8 justify-items-center';
            } elseif ($speakerCount >= 3) {
                // For 3+ cards, use max 3 columns
                // CSS Grid will automatically handle wrapping:
                // - 3 cards: 3 columns
                // - 4 cards: 3 top, 1 bottom (or 2-2 on smaller screens)
                // - 5 cards: 3 top, 2 bottom (will need special styling for centering)
                // - 6 cards: 3-3 layout
                $gridClasses = 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 lg:gap-8';
            } else {
                $gridClasses = 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 lg:gap-8';
            }
        @endphp

        <div class="{{ $gridClasses }} {{ $speakerCount === 5 ? 'speakers-grid-5' : '' }}">
            @if($speakerCount > 0)
                @foreach($speakers as $index => $speaker)
                <div class="{{ $fixedCardWidth }} {{ $speakerCount === 5 && $index >= 3 ? 'lg:col-start-auto' : '' }} mx-auto">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden {{ $fixedCardHeight }} flex flex-col">
                    @php
                        // Try multiple possible image fields
                        $img = $speaker['image'] ?? ($speaker['image_path'] ?? ($speaker['image_url'] ?? null));
                        // Also check for flattened format like speakers_0_image
                        $flattenedKey = 'speakers_' . $index . '_image';
                        if (!$img && isset($details[$flattenedKey])) {
                            $img = $details[$flattenedKey];
                        }
                        $flattenedPathKey = 'speakers_' . $index . '_image_path';
                        if (!$img && isset($details[$flattenedPathKey])) {
                            $img = $details[$flattenedPathKey];
                        }
                    @endphp
                    @if($img)
                        <img src="{{ $img }}" 
                             alt="{{ $speaker['name'] ?? 'Speaker' }}" 
                             class="w-full h-64 object-cover"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <div class="w-full h-64 bg-gradient-to-br from-blue-500 to-purple-600 hidden"></div>
                    @else
                        <div class="h-64 bg-gradient-to-br from-blue-500 to-purple-600"></div>
                    @endif
                    <div class="p-4 md:p-6 flex-1 flex flex-col">
                        @if(isset($speaker['name']) && $speaker['name'])
                        <h3 class="text-lg md:text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ $speaker['name'] }}</h3>
                        @endif
                        @if(isset($speaker['title']) && $speaker['title'])
                        <p class="text-sm md:text-base text-qricket-600 dark:text-qricket-400 mb-3">{{ $speaker['title'] }}</p>
                        @endif
                        @if(isset($speaker['bio']) && $speaker['bio'])
                        <p class="text-gray-900 dark:text-gray-400 text-xs md:text-sm flex-1">
                            {!! $speaker['bio'] !!}
                        </p>
                        @endif
                    </div>
                </div>
                </div>
                @endforeach
            @else
                <!-- Default speakers if no data provided -->
                <div class="{{ $fixedCardWidth }} mx-auto">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden {{ $fixedCardHeight }} flex flex-col">
                        <div class="h-64 bg-gradient-to-br from-blue-500 to-purple-600 flex-shrink-0"></div>
                        <div class="p-4 md:p-6 flex-1 flex flex-col">
                            <h3 class="text-lg md:text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Add Speakers</h3>
                            <p class="text-sm md:text-base text-qricket-600 dark:text-qricket-400 mb-3">Speaker Title</p>
                            <p class="text-gray-900 dark:text-gray-400 text-xs md:text-sm flex-1">
                                Add speaker information here.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
/* Special styling for 5 cards: center the last 2 cards on desktop */
@media (min-width: 1024px) {
    /* For 5 cards: Position last 2 cards in columns 1 and 3 to center them */
    /* First 3 cards fill columns 1, 2, 3 naturally */
    /* Last 2 cards: position in columns 1 and 3, leaving column 2 empty for visual centering */
    .speakers-grid-5 > div:nth-child(4) {
        grid-column: 1;
    }
    
    .speakers-grid-5 > div:nth-child(5) {
        grid-column: 3;
    }
}

/* Ensure all cards have consistent fixed width and are centered within their grid cells */
.speakers-grid-section [class*="max-w-sm"] {
    width: 100%;
    max-width: 24rem; /* 384px - fixed card width */
    margin-left: auto;
    margin-right: auto;
}
</style>
