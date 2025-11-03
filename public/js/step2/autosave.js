function autosaveStep2Files(payload) {
    console.log('autosaveStep2Files called with payload:', payload);
    try {
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Get event ID from URL or form
        const eventId = getEventId();
        
        // Try to get autosave URL from form data attribute
        const form = document.querySelector('form[data-autosave-url]');
        let autosaveUrl = `/organizer/events/layout/autosave?id=${eventId}`;
        
        if (form && form.getAttribute('data-autosave-url')) {
            autosaveUrl = form.getAttribute('data-autosave-url');
        }
        
        // Handle payload - if it's FormData (for file uploads), use it directly
        let requestBody;
        if (payload instanceof FormData) {
            requestBody = payload;
            requestBody.append('_token', csrf);
            if (eventId) requestBody.append('id', eventId);
        } else {
            // Handle non-file payload (layout data, etc.)
            const fd = new FormData();
            fd.append('_token', csrf);
            if (eventId) fd.append('id', eventId);
            
            Object.keys(payload).forEach(k => {
                if (payload[k] !== undefined && payload[k] !== null) {
                    fd.append(k, payload[k]);
                }
            });
            requestBody = fd;
        }
        
        console.log('Sending autosave request to:', autosaveUrl);
        fetch(autosaveUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: requestBody
        })
        .then(r => r.ok ? r.json() : null)
        .then(data => {
            console.log('Autosave response:', data);
            // Update header preview with returned URLs immediately
            if (data && data.success) {
                if (data.poster_url) {
                    // Update poster preview in event header
                    const headerPoster = document.getElementById('step2-header-poster');
                    if (headerPoster) {
                        // Check if it's an img element or div element
                        if (headerPoster.tagName === 'IMG') {
                            // For img element, update src directly
                            headerPoster.src = data.poster_url;
                            headerPoster.style.display = 'block';
                        } else {
                            // For div element, use background-image
                            headerPoster.style.backgroundImage = `url(${data.poster_url})`;
                            headerPoster.style.backgroundSize = 'cover';
                            headerPoster.style.backgroundPosition = 'center';
                            headerPoster.style.backgroundRepeat = 'no-repeat';
                            // Hide placeholder content
                            const placeholder = headerPoster.querySelector('div');
                            if (placeholder) placeholder.style.display = 'none';
                        }
                    }
                }
                if (data.thumbnail_url) {
                    // Update thumbnail background in event header
                    const headerBg = document.getElementById('step2-header-bg');
                    if (headerBg) {
                        headerBg.style.backgroundImage = `url(${data.thumbnail_url})`;
                        headerBg.style.filter = 'grayscale(100%) contrast(150%) blur(3px)';
                        headerBg.style.backgroundSize = 'cover';
                        headerBg.style.backgroundPosition = 'center';
                        headerBg.style.backgroundRepeat = 'no-repeat';
                    }
                }
            }
            
            // Show autosave indicator
            showAutosaveIndicator('success');
        })
        .catch(error => {
            console.error('Autosave error:', error);
            showAutosaveIndicator('error');
        });
    } catch (e) {
        console.error('autosaveStep2Files error:', e);
        showAutosaveIndicator('error');
    }
}

// Debounced queue for layout JSON autosave
window.__layoutAutosaveTimer = window.__layoutAutosaveTimer || null;
function queueAutosave(jsonString) {
    try { clearTimeout(window.__layoutAutosaveTimer); } catch (_) {}
    window.__layoutAutosaveTimer = setTimeout(() => {
        try {
            autosaveStep2Files({ layout_data: jsonString });
        } catch (e) {
            console.error('queueAutosave error:', e);
        }
    }, 500);
}

function getEventId() {
    // Try to get event ID from form action URL
    const form = document.querySelector('form[data-autosave-url]');
    if (form) {
        const actionUrl = form.getAttribute('action');
        if (actionUrl) {
            const urlParams = new URLSearchParams(actionUrl.split('?')[1]);
            const eventId = urlParams.get('id');
            if (eventId) return eventId;
        }
    }
    
    // Try to get from autosave URL
    const autosaveUrl = form?.getAttribute('data-autosave-url');
    if (autosaveUrl) {
        const urlParams = new URLSearchParams(autosaveUrl.split('?')[1]);
        const eventId = urlParams.get('id');
        if (eventId) return eventId;
    }
    
    // Try to get from current page URL (for management pages)
    const currentUrl = window.location.href;
    const urlMatch = currentUrl.match(/\/events\/(\d+)/);
    if (urlMatch) {
        return urlMatch[1];
    }
    
    // Try to get from URL path segments
    const pathSegments = window.location.pathname.split('/');
    const eventIndex = pathSegments.indexOf('events');
    if (eventIndex !== -1 && pathSegments[eventIndex + 1]) {
        return pathSegments[eventIndex + 1];
    }
    
    return null;
}

// Function to show autosave indicator
function showAutosaveIndicator(status) {
    // Remove existing indicator
    const existingIndicator = document.getElementById('autosave-indicator');
    if (existingIndicator) {
        existingIndicator.remove();
    }
    
    // Create new indicator
    const indicator = document.createElement('div');
    indicator.id = 'autosave-indicator';
    indicator.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-md text-sm font-medium transition-all duration-300 ${
        status === 'success' 
            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
            : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
    }`;
    indicator.textContent = status === 'success' ? '✓ Auto-saved' : '✗ Save failed';
    
    document.body.appendChild(indicator);
    
    // Remove after 3 seconds
    setTimeout(() => {
        if (indicator && indicator.parentNode) {
            indicator.remove();
        }
    }, 3000);
}
