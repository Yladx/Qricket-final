// Premium Gallery Mosaic layout - Modular
// (helpers expected via global window - do NOT import)

window.createPremiumGalleryMosaicComponent = function(componentId) {
    return `
        <div class="space-y-3">
            <div id="${componentId}-photos-container" class="grid grid-cols-2 md:grid-cols-3 gap-2"></div>
            <button type="button" onclick="addPhotoItemFallback('${componentId}')" class="px-3 py-1.5 text-xs rounded bg-qricket-500 text-white">Add Photo</button>
        </div>`;
};

window.hydratePremiumGalleryMosaic = function(componentId, data) {
    try {
        const container = document.getElementById(`${componentId}-photos-container`);
        if (!container) return;
        container.innerHTML = '';
        // Support all legacy shapes/keys
        const d = data || {};
        let photos = Array.isArray(d.photos) ? [...d.photos] : [];
        // Backfill for image1/image2 as photos if photos is empty
        if ((!photos || photos.length == 0) && (d.image1 || d.image2)) {
            photos = [];
            if (d.image1) photos.push({
                url: d.image1,
                path: d.image1_path || extractPathFromUrl(d.image1)
            });
            if (d.image2) photos.push({
                url: d.image2,
                path: d.image2_path || extractPathFromUrl(d.image2)
            });
        }
        // Always keep photos as array in memory
        if (typeof updateComponentDetail === 'function') {
            updateComponentDetail(componentId, 'photos', photos);
        }
        if (!photos || photos.length === 0) {
            if (typeof addPhotoItemFallback === 'function') addPhotoItemFallback(componentId);
            return;
        }
        photos.forEach((photo, idx) => {
            // Always propagate only relative paths in memory/hidden fields
            let photoPath = photo.path;
            if (!photoPath && photo.url) photoPath = extractPathFromUrl(photo.url);
            const html = (typeof renderPhotoFields === 'function') ? renderPhotoFields(componentId, idx, { url: photo.url }) : '';
            if (html) container.insertAdjacentHTML('beforeend', html);
            if (photoPath && typeof updateComponentDetail === 'function') {
                updateComponentDetail(componentId, `photo_${idx}`, photoPath);
                updateComponentDetail(componentId, `photo_${idx}_path`, photoPath);
            }
            if (typeof loadPhotoWithFreshUrl === 'function') {
                loadPhotoWithFreshUrl(componentId, idx, photo.url, photoPath);
            }
        });
        if (typeof updatePhotoGridClasses === 'function') {
            [200, 500, 1000].forEach(dly => setTimeout(() => updatePhotoGridClasses(componentId), dly));
        }
    } catch(e) {}
};

function extractPathFromUrl(url) {
    // Try to extract: events/.../filename
    if (!url || typeof url !== 'string') return '';
    const match = url.match(/events\/.*?\.(jpg|jpeg|webp|png|gif)/i);
    return match ? match[0] : url;
}
