<!-- About Two Images Layout -->
<div class="about-two-images-section py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Title and Content Section -->
        @if(isset($details['title']) && $details['title'])
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-gray-100 mb-6">
                {{ $details['title'] }}
            </h2>
            
            @if(isset($details['content']) && $details['content'])
            <div class="prose prose-lg dark:prose-invert max-w-3xl mx-auto">
                <div class="text-gray-900 dark:text-gray-400 leading-relaxed">
                    {!! $details['content'] !!}
                </div>
            </div>
            @endif
        </div>
        @endif
        
        <!-- Two Images Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- First Image -->
            <div class="relative group">
                @if(isset($details['image1']) && $details['image1'])
                    <img src="{{ $details['image1'] }}" 
                         alt="{{ $details['image1_alt'] ?? 'Event Image 1' }}" 
                         class="w-full h-80 object-cover rounded-lg shadow-lg transition-transform duration-300 group-hover:scale-105">
                @else
                    <div class="w-full h-80 bg-gray-100 dark:bg-gray-800 rounded-lg shadow-lg flex items-center justify-center border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 transition-colors duration-200">
                        <div class="text-center text-gray-900 dark:text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-sm font-medium">Choose image</p>
                        </div>
                    </div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent rounded-lg"></div>
            </div>
            
            <!-- Second Image -->
            <div class="relative group">
                @if(isset($details['image2']) && $details['image2'])
                    <img src="{{ $details['image2'] }}" 
                         alt="{{ $details['image2_alt'] ?? 'Event Image 2' }}" 
                         class="w-full h-80 object-cover rounded-lg shadow-lg transition-transform duration-300 group-hover:scale-105">
                @else
                    <div class="w-full h-80 bg-gray-100 dark:bg-gray-800 rounded-lg shadow-lg flex items-center justify-center border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 transition-colors duration-200">
                        <div class="text-center text-gray-900 dark:text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-sm font-medium">Choose image</p>
                        </div>
                    </div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent rounded-lg"></div>
            </div>
        </div>
        
        <!-- Event Info Section (if enabled) -->
        @if(isset($details['show_event_info']) && $details['show_event_info'])
        <div class="mt-16">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 max-w-md mx-auto">
                <div class="text-center">
                    <div class="text-2xl font-bold text-qricket-600 dark:text-qricket-400">
                        {{ $event->start_date->format('j') }}
                    </div>
                    <div class="text-sm text-gray-900 dark:text-gray-400">
                        {{ $event->start_date->format('M Y') }}
                    </div>
                </div>
                
                <div class="text-center">
                    <div class="text-2xl font-bold text-qricket-600 dark:text-qricket-400">
                        {{ $event->start_date->format('g:i A') }}
                    </div>
                    <div class="text-sm text-gray-900 dark:text-gray-400">
                        Start Time
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
