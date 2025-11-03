// Layout: hero-carousel
(function(){
    function createHeroCarouselComponent(componentId) {
        return `
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Slides</label>
                    <div id="${componentId}-slides-container" class="space-y-4">
                        ${renderHeroSlideFields(componentId, 0)}
                </div>
                    <button type="button" onclick="addHeroSlide('${componentId}')" class="mt-2 px-3 py-1 text-sm bg-qricket-500 text-white rounded hover:bg-qricket-600">Add Slide</button>
                </div>
            </div>
        `;
    }

    function renderHeroSlideFields(componentId, index) {
        return `
            <div class="slide-item border border-gray-200 dark:border-gray-600 rounded-lg p-3" data-index="${index}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-start">
                <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Title</label>
                        <input type="text" id="${componentId}-slide_title_${index}" class="block w-full text-sm bg-white text-gray-900 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500" placeholder="Enter slide title...">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mt-3 mb-1">Content</label>
                        <textarea id="${componentId}-slide_content_${index}" class="w-full text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-qricket-500 focus:ring-qricket-500 rounded-md shadow-sm" rows="3" placeholder="Enter slide content..."></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Background Image</label>
                        <div class="relative w-full overflow-hidden rounded-lg border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700" style="padding-bottom:56.25%">
                            <div id="${componentId}-background_image_${index}-preview" class="absolute inset-0 hidden">
                                <img class="w-full h-full object-cover" alt="Preview">
                                <p class="absolute bottom-2 left-2 text-xs bg-black/60 text-white px-2 py-0.5 rounded"></p>
            </div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <label for="${componentId}-background_image_${index}" class="cursor-pointer text-xs text-gray-600 dark:text-gray-300 bg-white/70 dark:bg-gray-800/70 px-2 py-1 rounded shadow">
                                    Choose image
                                </label>
                            </div>
                        </div>
                        <input type="file" id="${componentId}-background_image_${index}" accept="image/*" class="sr-only" onchange="handleHeroCarouselUpload('${componentId}', ${index}, this)">
                    </div>
                </div>
                <button type="button" onclick="removeHeroSlide(this)" class="mt-2 text-red-600 hover:text-red-800 text-sm">Remove Slide</button>
            </div>
        `;
    }

    function addHeroSlide(componentId) {
        const container = document.getElementById(`${componentId}-slides-container`);
        const nextIndex = container.querySelectorAll('.slide-item').length;
        const wrapper = document.createElement('div');
        wrapper.innerHTML = renderHeroSlideFields(componentId, nextIndex);
        container.appendChild(wrapper.firstElementChild);
    }

    function removeHeroSlide(button) {
        button.closest('.slide-item').remove();
    }

    // Handle image upload for hero carousel
    function handleHeroCarouselUpload(componentId, slideIndex, input) {
        const file = input.files[0];
        if (!file) return;
        
        const preview = document.getElementById(`${componentId}-background_image_${slideIndex}-preview`);
        const placeholder = document.querySelector(`label[for="${componentId}-background_image_${slideIndex}"]`)?.parentElement;
        
        if (!preview) return;
        
        // Show loading state
        preview.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">Uploading...</div>';
        preview.classList.remove('hidden');
        if (placeholder) placeholder.classList.add('hidden');
        
        // Create form data
        const formData = new FormData();
        formData.append('image', file);
        formData.append('component_id', componentId);
        
        // Get event ID from URL or form
        // Extract from URL path like /organizer/events/28/edit/layout
        const urlMatch = window.location.pathname.match(/\/events\/(\d+)\//);
        const eventIdFromUrl = urlMatch ? urlMatch[1] : null;
        
        const eventId = window.currentEventId ||
                       eventIdFromUrl ||
                       new URLSearchParams(window.location.search).get('id') || 
                       document.querySelector('input[name="id"]')?.value ||
                       document.querySelector('meta[name="event-id"]')?.getAttribute('content');
        
        if (eventId) {
            formData.append('id', eventId);
            console.log('Event ID appended:', eventId);
        } else {
            console.error('Event ID not found!');
        }
        
        // Upload to server
        fetch('/organizer/events/upload-layout-image', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Store only the relative path, not the signed URL (to avoid redundancy and expiration issues)
                updateComponentDetail(componentId, `background_image_${slideIndex}`, data.image_path);
                updateComponentDetail(componentId, `background_image_${slideIndex}_path`, data.image_path);
                
                // console.log(`Updated component ${componentId} with background_image_${slideIndex} path:`, data.image_path);
                
                // Show preview
                preview.innerHTML = `
                    <img src="${data.image_url}" alt="Preview" class="w-full h-full object-cover">
                    <div class="absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded-full text-xs flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Uploaded
                    </div>
                `;
            } else {
                preview.innerHTML = '<div class="flex items-center justify-center h-full text-red-500">Upload failed</div>';
                if (placeholder) placeholder.classList.remove('hidden');
            }
        })
        .catch(error => {
            // console.error('Upload error:', error);
            preview.innerHTML = '<div class="flex items-center justify-center h-full text-red-500">Upload failed</div>';
            if (placeholder) placeholder.classList.remove('hidden');
        });
    }

    // Hydration helper: show preview if layout JSON includes slide data
    function hydrateHeroCarousel(componentId, data) {
        if (!data) return;
        
        // Get event ID from global variable set in template
        const eventId = window.currentEventId;
        
        // Handle slides array format
        if (Array.isArray(data.slides)) {
            data.slides.forEach((slide, index) => {
                if (slide.background_image || slide.background_image_path) {
                    loadCarouselImage(componentId, index, slide.background_image, slide.background_image_path, eventId);
                }
            });
        } else {
            // Handle flattened format (background_image_0, background_image_1, etc.)
            let index = 0;
            while (data[`background_image_${index}`] || data[`background_image_${index}_path`]) {
                const imageUrl = data[`background_image_${index}`];
                const imagePath = data[`background_image_${index}_path`];
                loadCarouselImage(componentId, index, imageUrl, imagePath, eventId);
                index++;
            }
        }
    }
    
    function loadCarouselImage(componentId, index, imageUrl, imagePath, eventId) {
        // If we have a path, try to get a fresh signed URL first
        if (imagePath && eventId) {
            fetch('/organizer/events/get-fresh-image-url', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    path: imagePath,
                    event_id: parseInt(eventId)
                })
            })
            .then(response => response.json())
            .then(responseData => {
                if (responseData.success) {
                    setCarouselImage(componentId, index, responseData.image_url);
                } else {
                    if (imageUrl) {
                        setCarouselImage(componentId, index, imageUrl);
                    }
                }
            })
            .catch(error => {
                if (imageUrl) {
                    setCarouselImage(componentId, index, imageUrl);
                }
            });
        } else if (imageUrl) {
            // Handle relative image paths (convert to full URL)
            if (!imageUrl.startsWith('http')) {
                if (eventId) {
                    imageUrl = `/storage/events/${eventId}/layoutimages/${imageUrl}`;
                }
            }
            setCarouselImage(componentId, index, imageUrl);
        }
    }
    
    function setCarouselImage(componentId, index, imageUrl) {
        setTimeout(() => {
            const preview = document.getElementById(`${componentId}-background_image_${index}-preview`);
            const placeholder = document.querySelector(`label[for="${componentId}-background_image_${index}"]`)?.parentElement;
            
            if (preview) {
                const img = preview.querySelector('img');
                if (img) {
                    img.src = imageUrl;
                }
                preview.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');
            }
        }, 100 * (index + 1));
    }

    // Export functions
    window.createHeroCarouselComponent = window.createHeroCarouselComponent || createHeroCarouselComponent;
    window.renderHeroSlideFields = window.renderHeroSlideFields || renderHeroSlideFields;
    window.addHeroSlide = window.addHeroSlide || addHeroSlide;
    window.removeHeroSlide = window.removeHeroSlide || removeHeroSlide;
    window.handleHeroCarouselUpload = window.handleHeroCarouselUpload || handleHeroCarouselUpload;
    window.hydrateHeroCarousel = window.hydrateHeroCarousel || hydrateHeroCarousel;
})();