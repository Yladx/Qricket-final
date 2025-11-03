// Shared Photo Layout Utility Functions for dynamic photo/image-based layouts
// (originally from components.js - modularized)

// Render a single photo field with the new drop zone style
function renderPhotoFields(componentId, index, photoData = null) {
    const hasImage = photoData && photoData.url;
    return `
        <div class="photo-item">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Image ${index + 1}</label>
            <div class="relative bg-gray-300 dark:bg-gray-700 rounded-lg border-2 border-dashed border-gray-400 dark:border-gray-600 hover:border-gray-500 dark:hover:border-gray-500 transition-colors duration-200" style="min-height: 200px;">
                <input type="file" id="${componentId}-photo_${index}" accept="image/*" onchange="uploadPhotoHighlight('${componentId}', ${index}, this)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                <div class="absolute inset-0 flex items-center justify-center ${hasImage ? 'hidden' : ''}">
                    <button type="button" class="bg-gray-600 dark:bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-700 dark:hover:bg-gray-400 transition-colors duration-200 font-medium">Choose image</button>
                </div>
                <div id="${componentId}-photo_${index}-preview" class="absolute inset-0 ${hasImage ? '' : 'hidden'}">
                    ${hasImage ? `
                        <img src="${photoData.url}" alt="Preview" class="w-full h-full object-cover rounded-lg">
                    ` : ''}
                </div>
            </div>
            <div class="mt-3 flex justify-end">
                <button type="button" onclick="removePhotoItemFallback('${componentId}', ${index})" class="text-red-600 hover:text-red-800 text-sm font-medium">Remove Photo</button>
            </div>
        </div>
    `;
}

// Update grid classes based on number of photos
function updatePhotoGridClasses(componentId) {
    const container = document.getElementById(`${componentId}-photos-container`);
    if (!container) return;
    const photoItems = container.querySelectorAll('.photo-item');
    const count = photoItems.length;
    container.className = container.className.replace(/grid-cols-\d+/g, '');
    let newClasses;
    if (count === 0 || count === 1) {
        newClasses = 'grid grid-cols-1 gap-4 min-h-[200px]';
    } else if (count === 2) {
        newClasses = 'grid grid-cols-1 md:grid-cols-2 gap-4 min-h-[200px]';
    } else if (count === 3) {
        newClasses = 'grid grid-cols-1 lg:grid-cols-3 gap-4 min-h-[200px]';
    } else {
        newClasses = 'grid grid-cols-1 md:grid-cols-2 gap-4 min-h-[200px]';
    }
    container.className = newClasses;
    // Re-index photo items
    photoItems.forEach((item, index) => {
        const label = item.querySelector('label');
        const input = item.querySelector('input[type="file"]');
        const preview = item.querySelector('[id$="-preview"]');
        const removeBtn = item.querySelector('button[onclick*="removePhotoItemFallback"]');
        if (label) label.textContent = `Image ${index + 1}`;
        if (input) {
            input.id = `${componentId}-photo_${index}`;
            input.onchange = () => uploadPhotoHighlight(componentId, index, input);
        }
        if (preview) preview.id = `${componentId}-photo_${index}-preview`;
        if (removeBtn) removeBtn.onclick = () => removePhotoItemFallback(componentId, index);
    });
}

function addPhotoItemFallback(componentId) {
    const container = document.getElementById(`${componentId}-photos-container`);
    if (!container) return;
    const photoItems = container.querySelectorAll('.photo-item');
    const nextIndex = photoItems.length;
    const photoItemHTML = renderPhotoFields(componentId, nextIndex);
    container.insertAdjacentHTML('beforeend', photoItemHTML);
    updatePhotoGridClasses(componentId);
}

function removePhotoItemFallback(componentId, index) {
    const photoItem = document.querySelector(`#${componentId}-photos-container .photo-item:nth-child(${index + 1})`);
    if (photoItem) {
        photoItem.remove();
        updatePhotoGridClasses(componentId);
    }
}

function uploadPhotoHighlight(componentId, index, input) {
    const file = input.files[0];
    if (!file) return;
    const preview = document.getElementById(`${componentId}-photo_${index}-preview`);
    if (!preview) return;
    preview.innerHTML = '<div class="text-sm text-gray-500">Uploading...</div>';
    const formData = new FormData();
    formData.append('image', file);
    formData.append('component_id', componentId);
    formData.append('index', index);
    const eventId = new URLSearchParams(window.location.search).get('id') || document.querySelector('input[name="id"]')?.value || document.querySelector('meta[name="event-id"]')?.getAttribute('content');
    if (eventId) { formData.append('id', eventId); }
    fetch('/organizer/events/upload-layout-image', {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof updateComponentDetail === 'function') {
                updateComponentDetail(componentId, `photo_${index}`, data.image_url);
                updateComponentDetail(componentId, `photo_${index}_path`, data.image_path);
            }
            preview.innerHTML = `<img src="${data.image_url}" alt="Preview" class="w-full h-full object-cover rounded-lg">`;
            preview.classList.remove('hidden');
            const chooseBtn = preview.parentElement.querySelector('.absolute.inset-0.flex.items-center.justify-center');
            if (chooseBtn) chooseBtn.classList.add('hidden');
        } else {
            preview.innerHTML = '<div class="text-sm text-red-500">Upload failed</div>';
        }
    })
    .catch(() => {
        preview.innerHTML = '<div class="text-sm text-red-500">Upload failed</div>';
    });
}

function loadPhotoWithFreshUrl(componentId, idx, photoUrl, photoPath) {
    const eventId = window.currentEventId;
    if (photoPath && eventId) {
        fetch('/organizer/events/get-fresh-image-url', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ path: photoPath, event_id: parseInt(eventId) })
        })
        .then(response => response.json())
        .then(responseData => {
            if (responseData.success) {
                setPhotoImage(componentId, idx, responseData.image_url);
            } else if (photoUrl) {
                setPhotoImage(componentId, idx, photoUrl);
            }
        })
        .catch(() => { if (photoUrl) setPhotoImage(componentId, idx, photoUrl); });
    } else if (photoUrl) {
        if (!photoUrl.startsWith('http')) {
            if (eventId) {
                const fullPath = photoUrl.includes('events/') ? photoUrl : `events/${eventId}/layoutimages/${photoUrl}`;
                fetch('/organizer/events/get-fresh-image-url', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ path: fullPath, event_id: parseInt(eventId) })
                })
                .then(response => response.json())
                .then(responseData => {
                    if (responseData.success) {
                        setPhotoImage(componentId, idx, responseData.image_url);
                    } else {
                        const r2PublicUrl = `https://pub-0bcfbeb75fa3b4ddc40a682aa1f26ef3.r2.dev/${fullPath}`;
                        setPhotoImage(componentId, idx, r2PublicUrl);
                    }
                })
                .catch(() => {
                    const r2PublicUrl = `https://pub-0bcfbeb75fa3b4ddc40a682aa1f26ef3.r2.dev/${fullPath}`;
                    setPhotoImage(componentId, idx, r2PublicUrl);
                });
            } else {
                const r2PublicUrl = `https://pub-0bcfbeb75fa3b4ddc40a682aa1f26ef3.r2.dev/${photoUrl}`;
                setPhotoImage(componentId, idx, r2PublicUrl);
            }
        } else {
            setPhotoImage(componentId, idx, photoUrl);
        }
    }
}

function setPhotoImage(componentId, idx, imageUrl) {
    setTimeout(() => {
        const previewDiv = document.getElementById(`${componentId}-photo_${idx}-preview`);
        const img = previewDiv?.querySelector('img');
        if (img && imageUrl) {
            img.src = imageUrl;
            img.onload = () => {
                previewDiv.classList.remove('hidden');
                setTimeout(() => { updatePhotoGridClasses(componentId); }, 50);
            };
            img.onerror = () => {};
        }
    }, 100);
}

// Expose core methods globally for DOM event compatibility
window.renderPhotoFields = renderPhotoFields;
window.updatePhotoGridClasses = updatePhotoGridClasses;
window.addPhotoItemFallback = addPhotoItemFallback;
window.removePhotoItemFallback = removePhotoItemFallback;
window.uploadPhotoHighlight = uploadPhotoHighlight;
window.loadPhotoWithFreshUrl = loadPhotoWithFreshUrl;
window.setPhotoImage = setPhotoImage;
