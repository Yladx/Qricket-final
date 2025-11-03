<!-- Speakers Grid Layout -->
<div class="speakers-grid-section py-16">
    <style>
        /* Hide scrollbar for Chrome, Safari and Opera */
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        /* Hide scrollbar for IE, Edge and Firefox */
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(isset($details['title']) && $details['title'])
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">{{ $details['title'] }}</h2>
            @if(isset($details['subtitle']) && $details['subtitle'])
            <p class="text-lg text-gray-900 dark:text-gray-400">{{ $details['subtitle'] }}</p>
            @endif
        </div>
        @endif
        
        @php
            // Get speakers array
            $speakers = isset($details['speakers']) && is_array($details['speakers']) ? $details['speakers'] : [];
            $speakerCount = count($speakers);
            
            // Grid classes based on count
            if ($speakerCount === 1) {
                $gridClasses = 'flex justify-center';
                $cardContainerClasses = 'flex';
            } elseif ($speakerCount === 2) {
                $gridClasses = 'grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8';
                $cardContainerClasses = '';
            } elseif ($speakerCount === 3) {
                $gridClasses = 'grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8';
                $cardContainerClasses = '';
            } else {
                // 4 or more: horizontal scroll (no visible scrollbar)
                $gridClasses = 'flex gap-6 lg:gap-8 overflow-x-auto';
                $cardContainerClasses = 'flex-shrink-0';
            }
        @endphp
        
        <div class="{{ $gridClasses }} {{ $speakerCount >= 4 ? 'scrollbar-hide' : '' }}" style="{{ $speakerCount >= 4 ? 'scrollbar-width: none; -ms-overflow-style: none;' : '' }}">
            @if($speakerCount > 0)
                @foreach($speakers as $index => $speaker)
                <div class="{{ $cardContainerClasses }} w-full {{ $speakerCount >= 4 ? 'w-80' : '' }}">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden h-full min-h-[400px] flex flex-col">
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
                    <div class="p-6 flex-1 flex flex-col">
                        @if(isset($speaker['name']) && $speaker['name'])
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ $speaker['name'] }}</h3>
                        @endif
                        @if(isset($speaker['title']) && $speaker['title'])
                        <p class="text-qricket-600 dark:text-qricket-400 mb-3">{{ $speaker['title'] }}</p>
                        @endif
                        @if(isset($speaker['bio']) && $speaker['bio'])
                        <p class="text-gray-900 dark:text-gray-400 text-sm flex-1">
                            {!! $speaker['bio'] !!}
                        </p>
                        @endif
                    </div>
                </div>
                </div>
                @endforeach
            @else
                <!-- Default speakers if no data provided -->
                <div class="flex justify-center w-full">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden min-h-[400px] flex flex-col w-full max-w-md">
                        <div class="h-64 bg-gradient-to-br from-blue-500 to-purple-600 flex-shrink-0"></div>
                        <div class="p-6 flex-1 flex flex-col">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Add Speakers</h3>
                            <p class="text-qricket-600 dark:text-qricket-400 mb-3">Speaker Title</p>
                            <p class="text-gray-900 dark:text-gray-400 text-sm flex-1">
                                Add speaker information here.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
