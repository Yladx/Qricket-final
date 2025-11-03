// Layout: left-text-right-image
(function(){
    function createLeftTextRightImageComponent(componentId) {
        return `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                <div class="space-y-3 order-2 md:order-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                    <input id="${componentId}-title" type="text" class="w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-qricket-500 focus:ring-qricket-500" placeholder="Enter title..." onchange="updateComponentDetail('${componentId}', 'title', this.value)">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Text Content</label>
                    <textarea id="${componentId}-content" class="w-full h-40 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-qricket-500 focus:ring-qricket-500 rounded-md shadow-sm" placeholder="Enter text content..."></textarea>
                </div>
                <div class="order-1 md:order-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Image</label>
                    <div class="space-y-2">
                        <div class="relative w-full overflow-hidden rounded-lg border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700" style="padding-bottom:56.25%">
                            <div id="${componentId}-image-preview" class="absolute inset-0 hidden">
                                <img class="w-full h-full object-cover" alt="Preview">
                                <p class="absolute bottom-2 left-2 text-xs bg-black/60 text-white px-2 py-0.5 rounded"></p>
                            </div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <label for="${componentId}-image" class="cursor-pointer text-sm text-gray-600 dark:text-gray-300 bg-white/70 dark:bg-gray-800/70 px-3 py-1 rounded shadow">Choose image</label>
                            </div>
                        </div>
                    </div>
                    <input type="file" id="${componentId}-image" accept="image/*" class="sr-only" onchange="handleLeftTextRightImageUpload('${componentId}', this)">
                </div>
            </div>
        `;
    }
    
    // Show rename field when file is selected
    function showLeftTextRightImageRenameField(componentId, input) {
        const file = input.files[0];
        if (!file) return;
        
        // Show rename container
        const renameContainer = document.getElementById(`${componentId}-image-rename-container`);
        if (renameContainer) {
            renameContainer.classList.remove('hidden');
        }
        
        // Hide placeholder
        const placeholder = document.querySelector(`label[for="${componentId}-image"]`).parentElement;
        if (placeholder) {
            placeholder.classList.add('hidden');
        }
        
        // Show preview with file name
        const preview = document.getElementById(`${componentId}-image-preview`);
        if (preview) {
            preview.innerHTML = `
                <div class="flex items-center justify-center h-full text-gray-500 text-sm">
                    <div class="text-center">
                        <div class="mb-2">📁</div>
                        <div>${file.name}</div>
                        <div class="text-xs mt-1">Ready to upload</div>
                    </div>
                </div>
            `;
            preview.classList.remove('hidden');
        }
    }
    
    // Handle image upload for left-text-right-image
    function handleLeftTextRightImageUpload(componentId, input) {
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
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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
                
                // Show preview using the signed URL for immediate display
                const displayName = data.display_name || 'Image';
                preview.innerHTML = `
                    <img src="${data.image_url}" alt="Preview" class="w-full h-full object-cover" 
                         onload="console.log('Image loaded successfully'); this.parentElement.classList.remove('hidden');"
                         onerror="console.error('Image failed to load:', '${data.image_url}');">
                    <p class="absolute bottom-2 left-2 text-xs bg-black/60 text-white px-2 py-0.5 rounded">${displayName}</p>
                `;
                
                // Hide rename container after successful upload
                const renameContainer = document.getElementById(`${componentId}-image-rename-container`);
                if (renameContainer) {
                    renameContainer.classList.add('hidden');
                }
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
    function hydrateLeftTextRightImage(componentId, data) {
        if (!data) return;
        
        // Try multiple possible image URL keys
        let imageUrl = data.image_url || data.image || data.imageUrl;
        let imagePath = data.image_path;
        
        // Get event ID from global variable set in template
        const eventId = window.currentEventId;
        
        // If we have a path, try to get a fresh signed URL first
        if (imagePath && eventId) {
            console.log('Getting fresh signed URL from path:', imagePath);
            
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
                    console.log('Got fresh signed URL:', responseData.image_url);
                    loadImageWithUrl(componentId, responseData.image_url);
                } else {
                    console.warn('Failed to get fresh URL, falling back to stored URL');
                    if (imageUrl) {
                        loadImageWithUrl(componentId, imageUrl);
                    }
                }
            })
            .catch(error => {
                console.error('Error getting fresh URL:', error);
                if (imageUrl) {
                    loadImageWithUrl(componentId, imageUrl);
                }
            });
        } else if (imageUrl) {
            // Handle relative image paths - use fresh URL API
            console.log('Processing imageUrl:', imageUrl, 'starts with http:', imageUrl.startsWith('http'));
            if (!imageUrl.startsWith('http')) {
                console.log('Image is not HTTP URL, checking eventId:', eventId);
                if (eventId) {
                    // Convert relative path to full R2 path and get fresh signed URL
                    const fullPath = imageUrl.includes('events/') ? imageUrl : `events/${eventId}/layoutimages/${imageUrl}`;
                    console.log('Getting fresh signed URL for relative path:', fullPath);
                    
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
                            console.log('Got fresh signed URL for relative path:', responseData.image_url);
                            loadImageWithUrl(componentId, responseData.image_url);
                        } else {
                            console.warn('Failed to get fresh URL for relative path, falling back to R2 public URL');
                            const r2PublicUrl = `https://pub-0bcfbeb75fa3b4ddc40a682aa1f26ef3.r2.dev/${fullPath}`;
                            loadImageWithUrl(componentId, r2PublicUrl);
                        }
                    })
                    .catch(error => {
                        console.error('Error getting fresh URL for relative path:', error);
                        const r2PublicUrl = `https://pub-0bcfbeb75fa3b4ddc40a682aa1f26ef3.r2.dev/${fullPath}`;
                        loadImageWithUrl(componentId, r2PublicUrl);
                    });
                } else {
                    console.warn('No event ID available, converting relative URL to R2 public URL');
                    const r2PublicUrl = `https://pub-0bcfbeb75fa3b4ddc40a682aa1f26ef3.r2.dev/${imageUrl}`;
                    loadImageWithUrl(componentId, r2PublicUrl);
                }
            } else {
                console.log('Using stored full URL:', imageUrl);
                loadImageWithUrl(componentId, imageUrl);
            }
        } else {
            console.log('No image URL or path found in data. Available keys:', Object.keys(data));
        }
    }
    
    function loadImageWithUrl(componentId, imageUrl) {
        console.log('Loading image with URL:', imageUrl);
        
        // Try multiple times with increasing delays to ensure DOM is ready
        const tryHydrate = (attempt = 0) => {
            const preview = document.getElementById(`${componentId}-image-preview`);
            console.log(`Attempt ${attempt + 1}: Found preview element:`, preview);
            
            if (preview) {
                const img = preview.querySelector('img');
                const label = preview.querySelector('p');
                console.log('Setting image src to:', imageUrl);
                if (img) {
                    img.src = imageUrl;
                    img.onload = () => console.log('Image loaded successfully');
                    img.onerror = () => console.error('Image failed to load:', imageUrl);
                }
                if (label) {
                    label.innerHTML = `
                        <div class="absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded-full text-xs flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Uploaded
                        </div>
                    `;
                }
                preview.classList.remove('hidden');
                console.log('Image preview should now be visible');
            } else if (attempt < 5) {
                // Retry after a delay if element not found
                console.log(`Preview element not found, retrying in ${100 * (attempt + 1)}ms`);
                setTimeout(() => tryHydrate(attempt + 1), 100 * (attempt + 1));
            } else {
                console.error('Preview element not found after 5 attempts for component:', componentId);
            }
        };
        
        // Start with immediate attempt
        tryHydrate();
    }
    
    window.createLeftTextRightImageComponent = window.createLeftTextRightImageComponent || createLeftTextRightImageComponent;
    window.hydrateLeftTextRightImage = window.hydrateLeftTextRightImage || hydrateLeftTextRightImage;
    window.handleLeftTextRightImageUpload = window.handleLeftTextRightImageUpload || handleLeftTextRightImageUpload;
})();

