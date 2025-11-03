// Layout: right-text-left-image
(function(){
    function createRightTextLeftImageComponent(componentId) {
        return `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                <div class="order-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Image</label>
                    <div class="relative w-full overflow-hidden rounded-lg border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700" style="padding-bottom:56.25%">
                        <div id="${componentId}-image-preview" class="absolute inset-0 hidden">
                            <img class="w-full h-full object-cover" alt="Preview">
                            <p class="absolute bottom-2 left-2 text-xs bg-black/60 text-white px-2 py-0.5 rounded"></p>
                        </div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <label for="${componentId}-image" class="cursor-pointer text-sm text-gray-600 dark:text-gray-300 bg-white/70 dark:bg-gray-800/70 px-3 py-1 rounded shadow">Choose image</label>
                        </div>
                    </div>
                    <input type="file" id="${componentId}-image" accept="image/*" class="sr-only" onchange="handleRightTextLeftImageUpload('${componentId}', this)">
                    </div>
                <div class="space-y-3 order-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                    <input type="text" id="${componentId}-title" onchange="updateComponentDetail('${componentId}', 'title', this.value)" class="block w-full bg-white text-gray-900 rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 dark:bg-gray-700 dark:text-white" placeholder="Enter title...">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Content</label>
                    <textarea id="${componentId}-content" onchange="updateComponentDetail('${componentId}', 'content', this.value)" class="w-full h-40 bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-qricket-500 focus:ring-qricket-500 rounded-md shadow-sm" placeholder="Enter content..."></textarea>
                </div>
            </div>
        `;
    }
    
    // Handle image upload for right-text-left-image
    function handleRightTextLeftImageUpload(componentId, input) {
        const file = input.files[0];
        if (!file) return;
        
        const preview = document.getElementById(`${componentId}-image-preview`);
        if (!preview) return;
        
        // Show loading state
        preview.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">Uploading...</div>';
        preview.classList.remove('hidden');
        
        // Create form data
        const formData = new FormData();
        formData.append('image', file);
        formData.append('component_id', componentId);
        
        // Get event ID from multiple sources
        // Extract from URL path like /organizer/events/28/edit/layout
        const urlMatch = window.location.pathname.match(/\/events\/(\d+)\//);
        const eventIdFromUrl = urlMatch ? urlMatch[1] : null;
        
        const eventId = window.currentEventId ||
                       eventIdFromUrl ||
                       new URLSearchParams(window.location.search).get('id') || 
                       document.querySelector('input[name="id"]')?.value ||
                       document.querySelector('meta[name="event-id"]')?.getAttribute('content');
        
        console.log('Event ID for upload:', eventId, 'window.currentEventId:', window.currentEventId, 'urlMatch:', urlMatch);
        
        if (eventId) {
            formData.append('id', eventId);
            console.log('Event ID appended to FormData:', eventId);
        } else {
            console.error('Event ID not found! window.currentEventId:', window.currentEventId);
        }
        
        // Upload to server
        fetch('/organizer/events/upload-layout-image', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Upload response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Upload response data:', data);
            if (data.success) {
                // Store only the relative path, not the signed URL (to avoid redundancy and expiration issues)
                updateComponentDetail(componentId, 'image', data.image_path);
                updateComponentDetail(componentId, 'image_path', data.image_path);
                
                console.log(`Updated component ${componentId} with image path:`, data.image_path);
                
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
                console.error('Upload failed:', data.message || 'Unknown error');
                preview.innerHTML = `<div class="flex items-center justify-center h-full text-red-500">Upload failed: ${data.message || 'Unknown error'}</div>`;
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            preview.innerHTML = `<div class="flex items-center justify-center h-full text-red-500">Upload failed: ${error.message || 'Network error'}</div>`;
        });
    }
    
    // Hydration helper: show preview if layout JSON includes image URL
    function hydrateRightTextLeftImage(componentId, data) {
        if (!data) return;
        
        let imageUrl = data.image_url || data.image;
        let imagePath = data.image_path;
        
        // Get event ID from global variable set in template
        const eventId = window.currentEventId;
        
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
                    loadImageWithUrl(componentId, responseData.image_url);
                } else {
                    if (imageUrl) {
                        loadImageWithUrl(componentId, imageUrl);
                    }
                }
            })
            .catch(error => {
                if (imageUrl) {
                    loadImageWithUrl(componentId, imageUrl);
                }
            });
        } else if (imageUrl) {
            // Handle relative image paths - use fresh URL API
            if (!imageUrl.startsWith('http')) {
                if (eventId) {
                    // Convert relative path to full R2 path and get fresh signed URL
                    const fullPath = imageUrl.includes('events/') ? imageUrl : `events/${eventId}/layoutimages/${imageUrl}`;
                    
                    fetch('/organizer/events/get-fresh-image-url', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            path: fullPath,
                            event_id: parseInt(eventId)
                        })
                    })
                    .then(response => response.json())
                    .then(responseData => {
                        if (responseData.success) {
                            loadImageWithUrl(componentId, responseData.image_url);
                        } else {
                            const r2PublicUrl = `https://pub-0bcfbeb75fa3b4ddc40a682aa1f26ef3.r2.dev/${fullPath}`;
                            loadImageWithUrl(componentId, r2PublicUrl);
                        }
                    })
                    .catch(error => {
                        const r2PublicUrl = `https://pub-0bcfbeb75fa3b4ddc40a682aa1f26ef3.r2.dev/${fullPath}`;
                        loadImageWithUrl(componentId, r2PublicUrl);
                    });
                } else {
                    const r2PublicUrl = `https://pub-0bcfbeb75fa3b4ddc40a682aa1f26ef3.r2.dev/${imageUrl}`;
                    loadImageWithUrl(componentId, r2PublicUrl);
                }
            } else {
                loadImageWithUrl(componentId, imageUrl);
            }
        }
    }
    
    function loadImageWithUrl(componentId, imageUrl) {
        setTimeout(() => {
            const preview = document.getElementById(`${componentId}-image-preview`);
            if (preview) {
                const img = preview.querySelector('img');
                const label = preview.querySelector('p');
                if (img) {
                    img.src = imageUrl;
                }
                if (label) label.textContent = 'Image uploaded';
                preview.classList.remove('hidden');
            }
        }, 50);
    }
    
    window.createRightTextLeftImageComponent = window.createRightTextLeftImageComponent || createRightTextLeftImageComponent;
    window.hydrateRightTextLeftImage = window.hydrateRightTextLeftImage || hydrateRightTextLeftImage;
    window.handleRightTextLeftImageUpload = window.handleRightTextLeftImageUpload || handleRightTextLeftImageUpload;
})();










































































