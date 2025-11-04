// Step 2 - Uploader and preview helpers
(function () {
    function previewPoster(input) {
        const file = input.files && input.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            // Live preview in header poster image
            const headerPoster = document.getElementById('step2-header-poster');
            if (headerPoster) {
                // Check if it's an img element or div element
                if (headerPoster.tagName === 'IMG') {
                    // For img element, update src directly
                    headerPoster.src = e.target.result;
                    headerPoster.style.display = 'block';
                } else {
                    // For div element, use background-image
                    headerPoster.style.backgroundImage = `url(${e.target.result})`;
                    headerPoster.style.backgroundSize = 'cover';
                    headerPoster.style.backgroundPosition = 'center';
                    headerPoster.style.backgroundRepeat = 'no-repeat';
                    
                    // Hide the placeholder content if it exists
                    const placeholder = headerPoster.querySelector('div');
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                }
            }
        };
        reader.readAsDataURL(file);
        
        // Autosave poster upload
        if (typeof window.autosaveStep2Files === 'function') {
            // Create FormData for file upload
            const formData = new FormData();
            formData.append('poster', file);
            autosaveStep2Files(formData);
        }
    }

    function previewThumbnail(input) {
        const file = input.files && input.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            // Live preview in header background (thumbnail with grayscale)
            const headerBg = document.getElementById('step2-header-bg');
            if (headerBg) {
                headerBg.style.backgroundImage = `url(${e.target.result})`;
                headerBg.style.filter = 'grayscale(100%) contrast(150%) blur(3px)';
                headerBg.style.backgroundSize = 'cover';
                headerBg.style.backgroundPosition = 'center';
                headerBg.style.backgroundRepeat = 'no-repeat';
            }
        };
        reader.readAsDataURL(file);
        
        // Autosave thumbnail upload
        if (typeof window.autosaveStep2Files === 'function') {
            // Create FormData for file upload
            const formData = new FormData();
            formData.append('thumbnail', file);
            autosaveStep2Files(formData);
        }
    }

    function previewLayoutImage(componentId, input) {
        const file = input.files && input.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            const preview = document.getElementById(`${componentId}-image-preview`) || document.getElementById(`${componentId}-background_image_0-preview`) || document.getElementById(`${componentId}-gallery-preview`);
            if (preview) {
                const img = preview.querySelector('img');
                const filename = preview.querySelector('p');
                if (img) img.src = e.target.result;
                if (filename) filename.textContent = file.name;
                preview.classList.remove('hidden');
            }
        };
        reader.readAsDataURL(file);
        uploadLayoutImage(componentId, input).then(() => {
            if (typeof window.updateLayoutJSON === 'function') window.updateLayoutJSON();
        });
    }

    function uploadLayoutImage(componentId, input) {
        const file = input.files && input.files[0];
        if (!file) return Promise.resolve();

        const formData = new FormData();
        formData.append('image', file);
        formData.append('component_id', componentId);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        // Get event ID from multiple sources
        const eventId = window.currentEventId || 
                       new URLSearchParams(window.location.search).get('id') ||
                       document.querySelector('input[name="id"]')?.value;
        if (eventId) formData.append('id', eventId);

        const preview = document.getElementById(`${componentId}-image-preview`) || document.getElementById(`${componentId}-background_image_0-preview`) || document.getElementById(`${componentId}-gallery-preview`);
        if (preview) {
            const filename = preview.querySelector('p');
            if (filename) filename.textContent = 'Uploading...';
        }

        return fetch('/organizer/events/upload-layout-image', { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Upload failed');
                const component = window.layoutComponents && window.layoutComponents.find(c => c.id === componentId);
                if (component) {
                    const fieldName = input.id.replace(`${componentId}-`, '');
                    if (!component.data) component.data = {};
                    // Store both url and path for server-side reference
                    component.data[fieldName] = data.image_url;
                    if (data.image_path) {
                        component.data[`${fieldName}_path`] = data.image_path;
                    }
                }
                if (preview) {
                    const img = preview.querySelector('img');
                    const filename = preview.querySelector('p');
                    if (img) img.src = data.image_url;
                    if (filename) filename.textContent = 'Uploaded: ' + file.name;
                }
                // Update layout JSON immediately after upload to persist new URL/path
                if (typeof window.updateLayoutJSON === 'function') window.updateLayoutJSON();
            })
            .catch(err => { 
                // console.error('Image upload error:', err); 
                if (typeof Swal !== 'undefined' && window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Upload Failed',
                        text: 'Image upload failed. Please try again.',
                    });
                } else {
                    alert('Image upload failed. Please try again.');
                }
            });
    }

    window.previewPoster = previewPoster;
    window.previewThumbnail = previewThumbnail;
    window.previewLayoutImage = previewLayoutImage;
    window.uploadLayoutImage = uploadLayoutImage;
})();


