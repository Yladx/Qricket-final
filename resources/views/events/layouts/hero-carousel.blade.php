<!-- Hero Carousel Layout -->
<div class="hero-carousel-section relative overflow-hidden">
    @php
        $slides = [];
        if (isset($details['slides']) && is_array($details['slides'])) {
            $slides = $details['slides'];
        } else {
            // Handle flattened format (slide_title_0, slide_content_0, background_image_0, etc.)
            $index = 0;
            while (isset($details["slide_title_{$index}"]) || isset($details["background_image_{$index}"])) {
                $slides[] = [
                    'title' => $details["slide_title_{$index}"] ?? '',
                    'content' => $details["slide_content_{$index}"] ?? '',
                    'background_image' => $details["background_image_{$index}"] ?? '',
                    'background_image_path' => $details["background_image_{$index}_path"] ?? ''
                ];
                $index++;
            }
        }
        
        // If no slides, create a default one
        if (empty($slides)) {
            $slides = [[
                'title' => $details['title'] ?? $event->title,
                'content' => $details['content'] ?? Str::limit($event->description, 200),
                'background_image' => $details['background_image'] ?? $event->poster_url ?? asset('images/default-hero.jpg')
            ]];
        }
    @endphp
    
    <div class="relative h-96 md:h-[500px] lg:h-[600px]">
        @if(count($slides) > 1)
            <!-- Multiple Slides Carousel -->
            <div id="heroCarousel" class="relative w-full h-full">
                @foreach($slides as $index => $slide)
                <div class="carousel-slide {{ $index === 0 ? 'active opacity-100' : 'opacity-0 pointer-events-none' }} absolute inset-0 transition-opacity duration-500 ease-in-out">
                    <!-- Background Image -->
                    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" 
                         style="background-image: url('{{ $slide['background_image'] ?? asset('images/default-hero.jpg') }}');">
                        <div class="absolute inset-0 bg-black bg-opacity-50"></div>
                    </div>
                    
                    <!-- Content Overlay -->
                    <div class="relative z-10 h-full flex items-center">
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                            <div class="max-w-3xl">
                                @if($slide['title'])
                                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6 leading-tight">
                                    {{ $slide['title'] }}
                                </h1>
                                @endif
                                
                                @if($slide['content'])
                                <div class="text-xl text-gray-200 mb-8 leading-relaxed">
                                    {!! $slide['content'] !!}
                                </div>
                                @endif
                                
                                @if(isset($details['show_event_info']) && $details['show_event_info'])
                                <div class="flex flex-wrap items-center gap-4 mb-6 text-white">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-lg">{{ $event->start_date->format('M j, Y') }}</span>
                                    </div>
                                    
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span class="text-lg">{{ $event->location }}</span>
                                    </div>
                                    
                                    <div class="flex items-center">
                                        <span class="px-3 py-1 bg-qricket-500 text-white text-sm font-medium rounded-full">
                                            {{ $event->category }}
                                        </span>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                
                <!-- Carousel Navigation -->
                <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2 z-30">
                    @foreach($slides as $index => $slide)
                    <button type="button" class="carousel-dot w-3 h-3 rounded-full {{ $index === 0 ? 'bg-white' : 'bg-gray-400' }} transition-colors duration-300 cursor-pointer hover:bg-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                    </button>
                    @endforeach
                </div>
                
                <!-- Previous/Next Buttons -->
                @if(count($slides) > 1)
                <button type="button" id="prevBtn" class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-3 rounded-full hover:bg-opacity-70 transition-all duration-300 z-30 cursor-pointer focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-transparent">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <button type="button" id="nextBtn" class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-3 rounded-full hover:bg-opacity-70 transition-all duration-300 z-30 cursor-pointer focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-transparent">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
                @endif
            </div>
        @else
            <!-- Single Slide -->
            @php $slide = $slides[0]; @endphp
            <div class="relative w-full h-full">
                <!-- Background Image -->
                <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" 
                     style="background-image: url('{{ $slide['background_image'] ?? asset('images/default-hero.jpg') }}');">
                    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
                </div>
                
                <!-- Content Overlay -->
                <div class="relative z-10 h-full flex items-center">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                        <div class="max-w-3xl">
                            @if($slide['title'])
                            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6 leading-tight">
                                {{ $slide['title'] }}
                            </h1>
                            @endif
                            
                            @if($slide['content'])
                            <div class="text-xl text-gray-200 mb-8 leading-relaxed">
                                {!! $slide['content'] !!}
                            </div>
                            @endif
                            
                            @if(isset($details['show_event_info']) && $details['show_event_info'])
                            <div class="flex flex-wrap items-center gap-4 mb-6 text-white">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="text-lg">{{ $event->start_date->format('M j, Y') }}</span>
                                </div>
                                
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span class="text-lg">{{ $event->location }}</span>
                                </div>
                                
                                <div class="flex items-center">
                                    <span class="px-3 py-1 bg-qricket-500 text-white text-sm font-medium rounded-full">
                                        {{ $event->category }}
                                    </span>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@if(count($slides) > 1)
<script>
(function() {
    // Function to initialize carousel - works for both initial load and AJAX-loaded content
    function initHeroCarousel() {
        const root = document.getElementById('heroCarousel');
        if (!root) {
            // If element not found, try again after a short delay (for AJAX-loaded content)
            setTimeout(initHeroCarousel, 100);
            return;
        }

        // Prevent multiple initializations
        if (root.dataset.initialized === 'true') {
            return;
        }
        root.dataset.initialized = 'true';

        let currentSlide = 0;
        const totalSlides = {{ count($slides) }};
        let autoSlideInterval;

        function qsAll(sel) { 
            return Array.from(root.querySelectorAll(sel)); 
        }

        function showSlide(index) {
            if (index < 0 || index >= totalSlides) return;
            
            // Hide all slides and remove active class
            qsAll('.carousel-slide').forEach(slide => {
                slide.classList.remove('active', 'opacity-100');
                slide.classList.add('opacity-0', 'pointer-events-none');
            });
            
            // Show the selected slide
            const slides = qsAll('.carousel-slide');
            if (slides[index]) {
                slides[index].classList.remove('opacity-0', 'pointer-events-none');
                slides[index].classList.add('active', 'opacity-100');
            }
            
            // Update dots
            qsAll('.carousel-dot').forEach((dot, i) => {
                if (i === index) {
                    dot.classList.remove('bg-gray-400');
                    dot.classList.add('bg-white');
                } else {
                    dot.classList.remove('bg-white');
                    dot.classList.add('bg-gray-400');
                }
            });
            
            currentSlide = index;
        }

        function nextSlide() {
            showSlide((currentSlide + 1) % totalSlides);
        }

        function previousSlide() {
            showSlide((currentSlide - 1 + totalSlides) % totalSlides);
        }

        // Initialize first slide
        showSlide(0);

        // Attach event listeners to navigation buttons using event delegation
        // This prevents duplicate listeners and works for dynamically added content
        root.addEventListener('click', function(e) {
            // Handle previous button
            if (e.target.closest('#prevBtn')) {
                e.preventDefault();
                e.stopPropagation();
                previousSlide();
                return;
            }
            
            // Handle next button
            if (e.target.closest('#nextBtn')) {
                e.preventDefault();
                e.stopPropagation();
                nextSlide();
                return;
            }
            
            // Handle dot buttons
            const dot = e.target.closest('.carousel-dot');
            if (dot) {
                e.preventDefault();
                e.stopPropagation();
                const dots = qsAll('.carousel-dot');
                const index = dots.indexOf(dot);
                if (index !== -1) {
                    showSlide(index);
                }
            }
        });

        // Auto-slide functionality
        function startAutoSlide() {
            stopAutoSlide(); // Clear any existing interval
            autoSlideInterval = setInterval(function() {
                nextSlide();
            }, 5000);
        }

        function stopAutoSlide() {
            if (autoSlideInterval) {
                clearInterval(autoSlideInterval);
                autoSlideInterval = null;
            }
        }

        // Start auto-slide
        startAutoSlide();

        // Pause on hover
        root.addEventListener('mouseenter', stopAutoSlide);
        root.addEventListener('mouseleave', startAutoSlide);

        console.log('Hero carousel initialized successfully with', totalSlides, 'slides');
    }

    // Try to initialize immediately (for AJAX-loaded content)
    if (document.readyState === 'loading') {
        // If DOM is still loading, wait for DOMContentLoaded
        document.addEventListener('DOMContentLoaded', initHeroCarousel);
    } else {
        // DOM is already loaded (AJAX content), initialize immediately
        initHeroCarousel();
    }
})();
</script>
@endif
