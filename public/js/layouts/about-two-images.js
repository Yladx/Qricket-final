// Layout: about-two-images
(function(){
    function createAboutTwoImagesComponent(componentId) {
        return `
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Title</label>
                    <input type="text" id="${componentId}-title" onchange="updateComponentDetail('${componentId}', 'title', this.value)" class="block w-full bg-white text-gray-900 rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 dark:bg-gray-700 dark:text-white" placeholder="Enter section title...">
                </div>
                    <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Content</label>
                    <textarea id="${componentId}-content" onchange="updateComponentDetail('${componentId}', 'content', this.value)" class="w-full bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-qricket-500 focus:ring-qricket-500 rounded-md shadow-sm" rows="4" placeholder="Enter content..."></textarea>
                        </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    ${[1,2].map(i => `
                    <div>
                        <label class=\"block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2\">Image ${i}</label>
                        <div class=\"space-y-2\">
                            <div class=\"relative w-full overflow-hidden rounded-lg border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700\" style=\"padding-bottom:56.25%\">
                                <div id=\"${componentId}-image${i}-preview\" class=\"absolute inset-0 hidden\">
                                    <img class=\"w-full h-full object-cover\" alt=\"Preview\">
                                    <p class=\"absolute bottom-2 left-2 text-xs bg-black/60 text-white px-2 py-0.5 rounded\"></p>
                                </div>
                                <div class=\"absolute inset-0 flex items-center justify-center\" id=\"${componentId}-image${i}-placeholder\">
                                    <label for=\"${componentId}-image${i}\" class=\"cursor-pointer text-sm text-gray-600 dark:text-gray-300 bg-white/70 dark:bg-gray-800/70 px-3 py-1 rounded shadow\">Choose image</label>
                                </div>
                            </div>
                        </div>
                        <input type=\"file\" id=\"${componentId}-image${i}\" accept=\"image/*\" class=\"sr-only\" onchange=\"handleAboutTwoImagesUpload('${componentId}', ${i}, this)\">
                    </div>`).join('')}
                </div>
            </div>
        `;
    }

    // Export functions
    window.createAboutTwoImagesComponent = window.createAboutTwoImagesComponent || createAboutTwoImagesComponent;
    
    // Handle image upload for about-two-images
    function handleAboutTwoImagesUpload(componentId, imageIndex, input) {
        const file = input.files[0];
        if (!file) return;
        
        const preview = document.getElementById(`${componentId}-image${imageIndex}-preview`);
        const placeholder = document.getElementById(`${componentId}-image${imageIndex}-placeholder`);
        
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
                updateComponentDetail(componentId, `image${imageIndex}`, data.image_path);
                updateComponentDetail(componentId, `image${imageIndex}_path`, data.image_path);
                
                // console.log(`Updated component ${componentId} with image${imageIndex} path:`, data.image_path);
                
                // Show preview using the signed URL for immediate display
                const displayName = data.display_name || `Image ${imageIndex}`;
                const fileInput = document.getElementById(`${componentId}-image${imageIndex}`);
                preview.innerHTML = `
                    <img src="${data.image_url}" alt="Preview" class="w-full h-full object-cover" 
                         onload="this.parentElement.classList.remove('hidden');">
                    <p class="absolute bottom-2 left-2 text-xs bg-black/60 text-white px-2 py-0.5 rounded">${displayName}</p>
                `;
                // Make preview clickable to replace image
                preview.style.cursor = 'pointer';
                preview.onclick = () => {
                    if (fileInput) fileInput.click();
                };
                // Clear file input so same file can be uploaded again
                if (fileInput) fileInput.value = '';
            } else {
                preview.innerHTML = '<div class="flex items-center justify-center h-full text-red-500">Upload failed</div>';
                if (placeholder) placeholder.classList.remove('hidden');
                preview.classList.add('hidden');
                // Clear file input on error
                const fileInput = document.getElementById(`${componentId}-image${imageIndex}`);
                if (fileInput) fileInput.value = '';
            }
        })
        .catch(error => {
            // console.error('Upload error:', error);
            preview.innerHTML = '<div class="flex items-center justify-center h-full text-red-500">Upload failed</div>';
            if (placeholder) placeholder.classList.remove('hidden');
            preview.classList.add('hidden');
            // Clear file input on error
            const fileInput = document.getElementById(`${componentId}-image${imageIndex}`);
            if (fileInput) fileInput.value = '';
        });
    }

    // Hydration helper: show preview if layout JSON includes image URLs
    function hydrateAboutTwoImages(componentId, data) {
        // console.log('hydrateAboutTwoImages called with:', { componentId, data });
        // console.log('All available keys in data:', Object.keys(data));
        if (!data) return;
        
        [1,2].forEach(i => {
            // Match the exact field names expected by the Blade template
            let url = data[`image${i}`] || data[`image${i}_url`];
            let path = data[`image${i}_path`];
            // console.log(`Looking for image ${i} data:`, { url, path });
            
        // Get event ID from global variable set in template
        const eventId = window.currentEventId;
        // console.log('Event ID for hydration:', eventId);
            
            // If we have a path, try to get a fresh signed URL first
            if (path && eventId) {
                // console.log(`Getting fresh signed URL for image ${i} from path:`, path);
                
                fetch('/organizer/events/get-fresh-image-url', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        path: path,
                        event_id: parseInt(eventId)
                    })
                })
                .then(response => response.json())
                .then(responseData => {
                    if (responseData.success) {
                        // console.log(`Got fresh signed URL for image ${i}:`, responseData.image_url);
                        loadImageWithUrl(componentId, i, responseData.image_url);
                    } else {
                        // console.warn(`Failed to get fresh URL for image ${i}, falling back to stored URL`);
                        if (url) {
                            loadImageWithUrl(componentId, i, url);
                        }
                    }
                })
                .catch(error => {
                    // console.error(`Error getting fresh URL for image ${i}:`, error);
                    if (url) {
                        loadImageWithUrl(componentId, i, url);
                    }
                });
            } else if (url) {
                // Handle relative image paths - use fresh URL API
                if (!url.startsWith('http')) {
                    if (eventId) {
                        // Convert relative path to full R2 path and get fresh signed URL
                        const fullPath = url.includes('events/') ? url : `events/${eventId}/layoutimages/${url}`;
                        // console.log(`Getting fresh signed URL for image ${i} relative path:`, fullPath);
                        
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
                                // console.log(`Got fresh signed URL for image ${i}:`, responseData.image_url);
                                loadImageWithUrl(componentId, i, responseData.image_url);
                            } else {
                                // console.warn(`Failed to get fresh URL for image ${i}, falling back to R2 public URL`);
                                const r2PublicUrl = `https://pub-0bcfbeb75fa3b4ddc40a682aa1f26ef3.r2.dev/${fullPath}`;
                                loadImageWithUrl(componentId, i, r2PublicUrl);
                            }
                        })
                        .catch(error => {
                            // console.error(`Error getting fresh URL for image ${i}:`, error);
                            const r2PublicUrl = `https://pub-0bcfbeb75fa3b4ddc40a682aa1f26ef3.r2.dev/${fullPath}`;
                            loadImageWithUrl(componentId, i, r2PublicUrl);
                        });
                    } else {
                        // console.warn(`No event ID available for image ${i}, converting relative URL to R2 public URL`);
                        const r2PublicUrl = `https://pub-0bcfbeb75fa3b4ddc40a682aa1f26ef3.r2.dev/${url}`;
                        loadImageWithUrl(componentId, i, r2PublicUrl);
                    }
                } else {
                    // console.log(`Using stored full URL for image ${i}:`, url);
                    loadImageWithUrl(componentId, i, url);
                }
            } else {
                // console.log(`No URL or path found for image ${i}. Available keys:`, Object.keys(data));
            }
        });
    }
    
    function loadImageWithUrl(componentId, imageIndex, url) {
        // console.log(`Attempting to load image ${imageIndex} with URL:`, url);
        
        // Try multiple times with different delays
        [100, 300, 500, 1000, 2000].forEach((delay, attempt) => {
            setTimeout(() => {
                const wrap = document.getElementById(`${componentId}-image${imageIndex}-preview`);
                const img = wrap?.querySelector('img');
                const ph = document.getElementById(`${componentId}-image${imageIndex}-placeholder`);
                
                // console.log(`Hydration attempt ${attempt + 1} (${delay}ms) for image ${imageIndex}:`, { wrap: !!wrap, img: !!img, ph: !!ph, wrapId: `${componentId}-image${imageIndex}-preview`, placeholderId: `${componentId}-image${imageIndex}-placeholder` });
                
                if (wrap) {
                    if (img) {
                        img.src = url;
                        img.onload = () => {
                            // console.log(`Image ${imageIndex} loaded successfully`);
                            wrap.classList.remove('hidden');
                            if (ph) ph.classList.add('hidden');
                        };
                        img.onerror = () => {};
                    } else {
                        // If no img element, create one
                        const fileInput = document.getElementById(`${componentId}-image${imageIndex}`);
                        wrap.innerHTML = `
                            <img src="${url}" alt="Preview" class="w-full h-full object-cover" 
                                 onload="this.parentElement.classList.remove('hidden'); var ph = document.getElementById('${componentId}-image${imageIndex}-placeholder'); if (ph) ph.classList.add('hidden');"
                                 onerror="void 0;">
                            <p class="absolute bottom-2 left-2 text-xs bg-black/60 text-white px-2 py-0.5 rounded">Image ${imageIndex}</p>
                        `;
                        // Make preview clickable to replace image
                        wrap.style.cursor = 'pointer';
                        wrap.onclick = () => {
                            if (fileInput) fileInput.click();
                        };
                        wrap.classList.remove('hidden');
                        if (ph) ph.classList.add('hidden');
                    }
                } else {
                    // console.log(`Preview element not found for image ${imageIndex} on attempt ${attempt + 1}`);
                }
            }, delay);
        });
    }

    // Export functions
    window.hydrateAboutTwoImages = window.hydrateAboutTwoImages || hydrateAboutTwoImages;
    window.handleAboutTwoImagesUpload = window.handleAboutTwoImagesUpload || handleAboutTwoImagesUpload;
})();
