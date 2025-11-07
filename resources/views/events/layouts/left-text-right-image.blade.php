<!-- Left Text Right Image Layout -->
<div class="left-text-right-image-section py-8 md:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-8 lg:gap-12 items-center">
            <!-- Left Content -->
            <div class="order-2 lg:order-1">
                @if(isset($details['title']) && $details['title'])
                <h2 class="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-gray-100 mb-4 md:mb-6">
                    {{ $details['title'] }}
                </h2>
                @endif
                
                @if(isset($details['content']) && $details['content'])
                <div class="prose prose-sm md:prose-lg dark:prose-invert max-w-none">
                    <div class="text-sm md:text-base text-gray-900 dark:text-gray-400 mb-4 md:mb-6 leading-relaxed">
                        {!! $details['content'] !!}
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Right Image -->
            <div class="order-1 lg:order-2">
                <div class="relative">
                    @if(isset($details['image']) && $details['image'])
                        <img src="{{ $details['image'] }}" 
                             alt="{{ $details['image_alt'] ?? 'Event Experience' }}" 
                             width="800"
                             height="600"
                             class="w-full h-64 md:h-80 lg:h-96 object-cover rounded-lg shadow-lg"
                             loading="lazy">
                    @else
                        <div class="w-full h-64 md:h-80 lg:h-96 bg-gray-100 dark:bg-gray-800 rounded-lg shadow-lg flex items-center justify-center border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 transition-colors duration-200">
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
        </div>
    </div>
</div>
