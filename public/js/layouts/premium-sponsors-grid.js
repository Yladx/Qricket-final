// Premium Sponsors Grid layout - Modular
window.createPremiumSponsorsGridComponent = function(componentId) {
    return `
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Section Title</label>
                <input id="${componentId}-title" type="text" class="w-full text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md" placeholder="Sponsors" oninput="updateLayoutJSON()">
            </div>
            <div id='${componentId}-sponsors' class='grid grid-cols-2 md:grid-cols-4 gap-2'></div>
            <button type='button' class='mt-1 px-3 py-1.5 text-xs rounded bg-qricket-500 text-white' onclick="window.addSponsorBox && addSponsorBox('${componentId}')">Add Sponsor</button>
        </div>
    `;
};

window.addSponsorBox = function(componentId, sponsor = {}) {
    const container = document.getElementById(`${componentId}-sponsors`);
    if (!container) return;
    const idx = container.querySelectorAll('.sponsor-item').length;
    const logoPath = sponsor.logo || '';
    const div = document.createElement('div');
    div.className = 'sponsor-item border border-dashed border-gray-300 dark:border-gray-700 rounded h-32 flex flex-col items-center justify-center p-2';
    div.innerHTML = `
        <div class="w-full flex flex-col items-center justify-center">
            <div class="relative w-full overflow-hidden rounded-lg border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700" style="height: 56px;">
                <div id="${componentId}-sponsor_${idx}_logo-preview" class="absolute inset-0 ${logoPath ? '' : 'hidden'} flex items-center justify-center" style="cursor: pointer;">
                    ${logoPath ? `<img src="" alt="Logo Preview" class="w-full h-full object-contain">` : ''}
                </div>
                <div id="${componentId}-sponsor_${idx}_logo-placeholder" class="absolute inset-0 flex items-center justify-center ${logoPath ? 'hidden' : ''}">
                    <label for="${componentId}-sponsor_${idx}_logo" class="cursor-pointer text-xs text-gray-600 dark:text-gray-300 bg-white/70 dark:bg-gray-800/70 px-2 py-1 rounded shadow">Choose logo</label>
                </div>
            </div>
            <input type="file" id="${componentId}-sponsor_${idx}_logo" accept="image/*" class="sr-only" onchange="window.handleSponsorLogoUpload && handleSponsorLogoUpload('${componentId}', ${idx}, this)">
        </div>
        <button type="button" onclick="window.removeSponsorBox && removeSponsorBox(this)" class="mt-2 text-xs text-red-600 hover:text-red-800">Remove</button>
    `;
    container.appendChild(div);
    
    // Make preview clickable to replace image
    setTimeout(() => {
        const preview = document.getElementById(`${componentId}-sponsor_${idx}_logo-preview`);
        const fileInput = document.getElementById(`${componentId}-sponsor_${idx}_logo`);
        if (preview && fileInput) {
            preview.onclick = () => {
                if (fileInput) fileInput.click();
            };
        }
        
        // If path, hydrate preview
        if (logoPath) {
            if (typeof window.handleSponsorLogoHydration === 'function') {
                window.handleSponsorLogoHydration(componentId, idx, logoPath);
            }
        }
    }, 0);
    
    try { if (typeof updateLayoutJSON === 'function') updateLayoutJSON(); } catch(_) {}
};
window.removeSponsorBox = function(btn) {
    btn.closest('.sponsor-item')?.remove();
    if (typeof updateLayoutJSON === 'function') updateLayoutJSON();
};

window.handleSponsorLogoUpload = function(componentId, idx, input){
    const file = input.files && input.files[0];
    if (!file) return;
    const preview = document.getElementById(`${componentId}-sponsor_${idx}_logo-preview`);
    if (!preview) return;
    preview.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">Uploading...</div>';
    preview.classList.remove('hidden');
    const formData = new FormData();
    formData.append('image', file);
    formData.append('component_id', componentId);
    // Find event id as in other uploaders
    const urlMatch = window.location.pathname.match(/\/events\/(\d+)\//);
    const eventIdFromUrl = urlMatch ? urlMatch[1] : null;
    const eventId = window.currentEventId || eventIdFromUrl || new URLSearchParams(window.location.search).get('id') || document.querySelector('input[name="id"]')?.value || document.querySelector('meta[name="event-id"]')?.getAttribute('content');
    if (eventId) formData.append('id', eventId);
    fetch('/organizer/events/upload-layout-image', {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
    }).then(r => r.json()).then(data => {
        if (!data || !data.success) {
            preview.innerHTML = '<div class="flex items-center justify-center h-full text-red-500">Upload failed</div>';
            return;
        }
        // Store only path
        if (typeof updateComponentDetail === 'function') {
            updateComponentDetail(componentId, `sponsor_${idx}_logo`, data.image_path);
        }
        const placeholder = document.getElementById(`${componentId}-sponsor_${idx}_logo-placeholder`);
        if (placeholder) placeholder.classList.add('hidden');
        preview.innerHTML = `<img src="${data.image_url}" alt="Logo Preview" class="w-full h-full object-contain">`;
        preview.classList.remove('hidden');
        preview.style.cursor = 'pointer';
        const fileInput = document.getElementById(`${componentId}-sponsor_${idx}_logo`);
        if (fileInput) {
            preview.onclick = () => { if (fileInput) fileInput.click(); };
        }
        // Clear file input so same file can be uploaded again
        if (fileInput) fileInput.value = '';
        try { if (typeof updateLayoutJSON === 'function') updateLayoutJSON(); } catch(_) {}
    }).catch(() => {
        preview.innerHTML = '<div class="flex items-center justify-center h-full text-red-500">Upload failed</div>';
    });
};
window.handleSponsorLogoHydration = function(componentId, idx, path) {
    // hydrate preview with fresh signed URL for given path
    if (!path) return;
    const preview = document.getElementById(`${componentId}-sponsor_${idx}_logo-preview`);
    if (!preview) return;
    const img = preview.querySelector('img');
    const eventId = window.currentEventId;
    if (eventId && path) {
        fetch('/organizer/events/get-fresh-image-url', {
            method:'POST',
            headers: { 'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')},
            body: JSON.stringify({ path: path, event_id: parseInt(eventId) })
        })
        .then(r=>r.json()).then(j=>{ 
            if (j.success && j.image_url && img) { 
                img.src = j.image_url; 
                preview.classList.remove('hidden');
                preview.style.cursor = 'pointer';
                const fileInput = document.getElementById(`${componentId}-sponsor_${idx}_logo`);
                if (fileInput) {
                    preview.onclick = () => { if (fileInput) fileInput.click(); };
                }
            } 
        });
    }
};
window.hydratePremiumSponsorsGrid = function(componentId, data) {
    try {
        const container = document.getElementById(`${componentId}-sponsors`);
        if (!container) return;
        container.innerHTML = '';
        let sponsors = [];
        if (Array.isArray(data?.sponsors)) sponsors = data.sponsors;
        else if (Array.isArray(data)) sponsors = data;
        if (sponsors.length > 0) sponsors.forEach(s => window.addSponsorBox(componentId, s));
        else window.addSponsorBox(componentId);
    } catch(e) { window.addSponsorBox(componentId); }
};
