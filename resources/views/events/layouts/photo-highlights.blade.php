<!-- Photo Highlights Layout -->
<div class="photo-highlights-section py-8 md:py-16">
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
            $photos = collect($details['photos'] ?? [])->filter(function($photo) {
                return isset($photo['url']) && !empty($photo['url']);
            });
            $photoCount = $photos->count();
        @endphp
        
        @if($photoCount > 0)
            @if($photoCount == 1)
                <!-- Layout 1: Single large horizontal image -->
                <div class="grid grid-cols-1 gap-4 md:gap-6">
                    @foreach($photos as $photo)
                        <div class="relative group overflow-hidden rounded-lg shadow-lg">
                            <img src="{{ $photo['url'] }}" 
                                 alt="Event Highlight" 
                                 class="w-full h-48 md:h-64 object-cover transition-transform duration-300 group-hover:scale-105">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300"></div>
                        </div>
                    @endforeach
                </div>
            @elseif($photoCount == 2)
                <!-- Layout 2: Two square images side by side -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6">
                    @foreach($photos as $photo)
                        <div class="relative group overflow-hidden rounded-lg shadow-lg">
                            <img src="{{ $photo['url'] }}" 
                                 alt="Event Highlight" 
                                 class="w-full h-48 md:h-64 object-cover transition-transform duration-300 group-hover:scale-105">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300"></div>
                        </div>
                    @endforeach
                </div>
            @elseif($photoCount == 3)
                <!-- Layout 3: Large vertical image on left, two smaller horizontal images on right -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
                    <div class="lg:col-span-1 relative group overflow-hidden rounded-lg shadow-lg">
                        <img src="{{ $photos[0]['url'] }}" 
                             alt="Event Highlight" 
                             class="w-full h-64 md:h-80 object-cover transition-transform duration-300 group-hover:scale-105">
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300"></div>
                    </div>
                    <div class="lg:col-span-2 space-y-4 md:space-y-6">
                        @foreach($photos->skip(1) as $photo)
                            <div class="relative group overflow-hidden rounded-lg shadow-lg">
                                <img src="{{ $photo['url'] }}" 
                                     alt="Event Highlight" 
                                     class="w-full h-32 md:h-40 object-cover transition-transform duration-300 group-hover:scale-105">
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300"></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <!-- Layout 4: 2x2 grid for 4+ images -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6">
                    @foreach($photos->take(4) as $photo)
                        <div class="relative group overflow-hidden rounded-lg shadow-lg">
                            <img src="{{ $photo['url'] }}" 
                                 alt="Event Highlight" 
                                 class="w-full h-48 md:h-64 object-cover transition-transform duration-300 group-hover:scale-105">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300"></div>
                        </div>
                    @endforeach
                </div>
                
                @if($photoCount > 4)
                    <!-- Show remaining images in a secondary grid -->
                    <div class="mt-4 md:mt-6">
                        <h3 class="text-base md:text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3 md:mb-4">Additional Photos</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                            @foreach($photos->skip(4) as $photo)
                                <div class="relative group overflow-hidden rounded-lg shadow-lg">
                                    <img src="{{ $photo['url'] }}" 
                                         alt="Event Highlight" 
                                         class="w-full h-40 md:h-48 object-cover transition-transform duration-300 group-hover:scale-105">
                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300"></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        @else
            <!-- Default placeholder if no photos provided -->
            <div class="grid grid-cols-1 gap-6">
                <div class="relative group overflow-hidden rounded-lg shadow-lg">
                    <div class="w-full h-64 bg-gray-100 dark:bg-gray-800 flex items-center justify-center border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 transition-colors duration-200">
                        <div class="text-center text-gray-900 dark:text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-sm font-medium">Choose image</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
