// Layout: speakers-grid
(function(){
    function renderSpeakerFields(componentId, index) {
        return `
            <div class="speaker-item border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Name</label>
                        <input type="text" class="speaker-name w-full text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-qricket-500 focus:ring-qricket-500 rounded-md shadow-sm" placeholder="Juan Dela Cruz">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Title/Role</label>
                        <input type="text" class="speaker-title w-full text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-qricket-500 focus:ring-qricket-500 rounded-md shadow-sm" placeholder="CEO, Company Inc.">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3 items-start">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Bio</label>
                        <textarea class="speaker-bio w-full text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-qricket-500 focus:ring-qricket-500 rounded-md shadow-sm" rows="3" placeholder="Short introduction..."></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Photo</label>
                        <div class="relative w-full overflow-hidden rounded-lg border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700" style="padding-bottom:56.25%">
                            <div id="${componentId}-speakers_${index}_image-preview" class="absolute inset-0 hidden">
                                <img class="w-full h-full object-cover" alt="Speaker Photo">
                                <p class="absolute bottom-2 left-2 text-xs bg-black/60 text-white px-2 py-0.5 rounded"></p>
                            </div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <label for="${componentId}-speakers_${index}_image" class="cursor-pointer text-sm text-gray-600 dark:text-gray-300 bg-white/70 dark:bg-gray-800/70 px-3 py-1 rounded shadow">Choose image</label>
                            </div>
                        </div>
                        <input type="file" id="${componentId}-speakers_${index}_image" accept="image/*" class="sr-only" onchange="handleSpeakerImage('${componentId}', ${index}, this)">
                    </div>
                </div>
                <button type="button" onclick="removeSpeakerItem(this)" class="mt-3 text-red-600 hover:text-red-800 text-sm">Remove Speaker</button>
            </div>
        `;
    }

    function createSpeakersGridComponent(componentId) {
        return `
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Section Title</label>
                        <input id="${componentId}-title" type="text" class="w-full text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-qricket-500 focus:ring-qricket-500 rounded-md shadow-sm" placeholder="Event Speakers">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subtitle</label>
                        <input id="${componentId}-subtitle" type="text" class="w-full text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-qricket-500 focus:ring-qricket-500 rounded-md shadow-sm" placeholder="Meet the experts">
                    </div>
                </div>
                <div class="flex items-center justify-between mt-2">
                    <h4 class="text-sm font-medium text-gray-800 dark:text-gray-200">Speakers</h4>
                    <button type="button" onclick="addSpeakerItem('${componentId}')" class="px-3 py-1.5 text-xs rounded bg-qricket-500 text-white hover:bg-qricket-600">Add Speaker</button>
                </div>
                <div id="${componentId}-speakers-container" class="space-y-3"></div>
            </div>
        `;
    }

    // Actions
    function addSpeakerItem(componentId) {
        const container = document.getElementById(`${componentId}-speakers-container`);
        if (!container) return;
        const index = container.querySelectorAll('.speaker-item').length;
        const wrapper = document.createElement('div');
        wrapper.innerHTML = renderSpeakerFields(componentId, index);
        container.appendChild(wrapper.firstElementChild);
    }
    function removeSpeakerItem(button){
        const item = button.closest('.speaker-item');
        if (!item) return;
        const container = item.parentElement;
        item.remove();
        // Reindex remaining to keep image ids aligned
        Array.from(container.querySelectorAll('.speaker-item')).forEach((el, idx) => {
            // reset image input/preview ids
            const file = el.querySelector('input[type="file"]');
            const preview = el.querySelector('[id$="_image-preview"]');
            if (file) file.id = `${componentId}-speakers_${idx}_image`;
            if (preview) preview.id = `${componentId}-speakers_${idx}_image-preview`;
        });
        if (typeof updateLayoutJSON === 'function') updateLayoutJSON();
    }

    function handleSpeakerImage(componentId, index, input){
        const file = input.files && input.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(e){
            const pv = document.getElementById(`${componentId}-speakers_${index}_image-preview`);
            if (pv) {
                const img = pv.querySelector('img');
                const label = pv.querySelector('p');
                if (img) img.src = e.target.result;
                if (label) label.textContent = file.name;
                pv.classList.remove('hidden');
            }
        };
        reader.readAsDataURL(file);
        // Upload to server and store PATH in component data (both flattened and structured)
        if (typeof uploadLayoutImage === 'function') {
            uploadLayoutImage(componentId, input).then(()=>{
                try {
                    const cmp = (window.layoutComponents||[]).find(c=>c.id===componentId);
                    if (!cmp) return;
                    const flatKey = `speakers_${index}_image`;
                    const url = cmp.data && cmp.data[flatKey];
                    const path = (cmp.data && cmp.data[`${flatKey}_path`]) || (url && (url.match(/events\/.*?\.(?:jpg|jpeg|png|webp|gif)/i)||[])[0]);
                    if (!cmp.data.speakers) cmp.data.speakers = [];
                    if (!cmp.data.speakers[index]) cmp.data.speakers[index] = {};
                    if (path) {
                        cmp.data.speakers[index].image = path; // store only path
                        cmp.data.speakers[index].image_path = path;
                    }
                    if (typeof updateLayoutJSON === 'function') updateLayoutJSON();
                } catch (_){ }
            });
        }
    }

    // Export
    window.createSpeakersGridComponent = window.createSpeakersGridComponent || createSpeakersGridComponent;

    // Hydrate speakers grid
    window.hydrateSpeakersGrid = function(componentId, data) {
        try {
            const d = data || {};
            const titleEl = document.getElementById(`${componentId}-title`);
            const subEl = document.getElementById(`${componentId}-subtitle`);
            if (titleEl) titleEl.value = d.title || '';
            if (subEl) subEl.value = d.subtitle || '';
            const container = document.getElementById(`${componentId}-speakers-container`);
            if (!container) return;
            container.innerHTML = '';
            const speakers = Array.isArray(d.speakers) ? d.speakers : [];
            const add = (sp) => {
                const idx = container.querySelectorAll('.speaker-item').length;
                const wrapper = document.createElement('div');
                wrapper.innerHTML = renderSpeakerFields(componentId, idx);
                const node = wrapper.firstElementChild;
                container.appendChild(node);
                node.querySelector('.speaker-name').value = sp.name || '';
                node.querySelector('.speaker-title').value = sp.title || '';
                node.querySelector('.speaker-bio').value = sp.bio || '';
                const rawUrl = sp.image;
                const rawPath = sp.image_path || '';
                if (rawUrl || rawPath) {
                    const preview = document.getElementById(`${componentId}-speakers_${idx}_image-preview`);
                    if (preview) {
                        const img = preview.querySelector('img');
                        const setImg = (u)=>{ if (img) img.src = u; preview.classList.remove('hidden'); };
                        const eventId = window.currentEventId;
                        if (rawPath && eventId) {
                            fetch('/organizer/events/get-fresh-image-url', { method:'POST', headers: { 'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')}, body: JSON.stringify({ path: rawPath, event_id: parseInt(eventId) }) })
                            .then(r=>r.json()).then(j=>{ if (j.success && j.image_url) setImg(j.image_url); else if (rawUrl) setImg(rawUrl); }).catch(()=>{ if (rawUrl) setImg(rawUrl); });
                        } else if (rawUrl) { setImg(rawUrl); }
                    }
                }
            };
            if (speakers.length === 0) add({}); else speakers.forEach(add);
        } catch(e) {}
    };
    window.addSpeakerItem = window.addSpeakerItem || addSpeakerItem;
    window.removeSpeakerItem = window.removeSpeakerItem || removeSpeakerItem;
    window.handleSpeakerImage = window.handleSpeakerImage || handleSpeakerImage;
    window.renderSpeakerFields = window.renderSpeakerFields || renderSpeakerFields;
})();
