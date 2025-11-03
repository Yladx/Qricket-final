// Premium Hero Spotlight layout - Modular
window.createPremiumHeroSpotlightComponent = function(componentId) {
    return `
        <div class="space-y-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Headline</label>
            <input id="${componentId}-headline" type="text" class="w-full text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md" placeholder="Big headline" oninput="updateLayoutJSON()">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subheadline</label>
            <input id="${componentId}-subheadline" type="text" class="w-full text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md" placeholder="Catchy subheadline" oninput="updateLayoutJSON()">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Background Image</label>
                <div class="relative w-full overflow-hidden rounded-lg border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700" style="padding-bottom:56.25%">
                    <div id="${componentId}-background_image-preview" class="absolute inset-0 hidden">
                        <img class="w-full h-full object-cover" alt="Preview">
                        <p class="absolute bottom-2 left-2 text-xs bg-black/60 text-white px-2 py-0.5 rounded"></p>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <label for="${componentId}-background_image" class="cursor-pointer text-sm text-gray-600 dark:text-gray-300 bg-white/70 dark:bg-gray-800/70 px-3 py-1 rounded shadow">Choose image</label>
                    </div>
                </div>
                <input type="file" id="${componentId}-background_image" accept="image/*" class="sr-only" onchange="handlePremiumHeroSpotlightUpload('${componentId}', this)">
            </div>
        </div>`;
};

// Hydrate fields from saved data
window.hydratePremiumHeroSpotlight = function(componentId, data) {
    try {
        const d = data || {};
        const headlineEl = document.getElementById(`${componentId}-headline`);
        const subEl = document.getElementById(`${componentId}-subheadline`);
        if (headlineEl) headlineEl.value = d.headline || '';
        if (subEl) subEl.value = d.subheadline || '';

        const rawUrl = d.background_image || d.backgroundImage || '';
        const rawPath = d.background_image_path || '';
        const preview = document.getElementById(`${componentId}-background_image-preview`);
        if (!preview) return;
        const img = preview.querySelector('img');
        const setImg = (u) => { if (img) img.src = u; preview.classList.remove('hidden'); };

        const eventId = window.currentEventId;
        if (rawPath && eventId) {
            fetch('/organizer/events/get-fresh-image-url', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ path: rawPath, event_id: parseInt(eventId) })
            })
            .then(r => r.json())
            .then(j => { if (j && j.success && j.image_url) setImg(j.image_url); else if (rawUrl) setImg(rawUrl); })
            .catch(() => { if (rawUrl) setImg(rawUrl); });
        } else if (rawUrl) {
            setImg(rawUrl);
        }
    } catch(e) {}
};

// Upload handler: mirrors single-image layouts, saves only path and shows signed URL
window.handlePremiumHeroSpotlightUpload = function(componentId, input) {
    const file = input.files[0];
    if (!file) return;

    const preview = document.getElementById(`${componentId}-background_image-preview`);
    if (!preview) return;
    preview.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">Uploading...</div>';
    preview.classList.remove('hidden');

    const formData = new FormData();
    formData.append('image', file);
    formData.append('component_id', componentId);

    const urlMatch = window.location.pathname.match(/\/events\/(\d+)\//);
    const eventIdFromUrl = urlMatch ? urlMatch[1] : null;
    const eventId = window.currentEventId || eventIdFromUrl || new URLSearchParams(window.location.search).get('id') || document.querySelector('input[name="id"]')?.value || document.querySelector('meta[name="event-id"]')?.getAttribute('content');
    if (eventId) formData.append('id', eventId);

    fetch('/organizer/events/upload-layout-image', {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
    })
    .then(r => r.json())
    .then(data => {
        if (!data || !data.success) {
            preview.innerHTML = '<div class="flex items-center justify-center h-full text-red-500">Upload failed</div>';
            return;
        }
        if (typeof updateComponentDetail === 'function') {
            updateComponentDetail(componentId, 'background_image', data.image_path);
            updateComponentDetail(componentId, 'background_image_path', data.image_path);
        }
        preview.innerHTML = `
            <img src="${data.image_url}" alt="Preview" class="w-full h-full object-cover">
            <p class="absolute bottom-2 left-2 text-xs bg-black/60 text-white px-2 py-0.5 rounded">Background</p>
        `;
        try { if (typeof updateLayoutJSON === 'function') updateLayoutJSON(); } catch(_) {}
    })
    .catch(() => {
        preview.innerHTML = '<div class="flex items-center justify-center h-full text-red-500">Upload failed</div>';
    });
};
