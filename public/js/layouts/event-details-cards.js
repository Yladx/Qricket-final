// Layout: event-details-cards
(function(){
    function createEventDetailsCardsComponent(componentId) {
        return `
            <div class="space-y-4">
                <div>
                    <label for="${componentId}-title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Section Title</label>
                    <input type="text" id="${componentId}-title" onchange="updateComponentDetail('${componentId}', 'title', this.value)" class="block w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 dark:bg-gray-700 dark:text-white" placeholder="Enter section title...">
                </div>
                <div>
                    <p id="${componentId}-cards-label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cards</p>
                    <div id="cards_${componentId}" class="space-y-3" role="group" aria-labelledby="${componentId}-cards-label">
                        <div class="card-item border border-gray-200 dark:border-gray-600 rounded-lg p-3">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label for="${componentId}-card-icon-0" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Icon</label>
                                    <input id="${componentId}-card-icon-0" list="icon_list_datalist" type="text" value="calendar" onchange="updateCardDetail('${componentId}', 0, 'icon', this.value)" class="block w-full text-sm rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 dark:bg-gray-700 dark:text-white" placeholder="calendar">
                                </div>
                                <div>
                                    <label for="${componentId}-card-title-0" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Title</label>
                                    <input id="${componentId}-card-title-0" type="text" value="Date" onchange="updateCardDetail('${componentId}', 0, 'title', this.value)" class="block w-full text-sm rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 dark:bg-gray-700 dark:text-white" placeholder="Date">
                                </div>
                                <div>
                                    <label for="${componentId}-card-content-0" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Content</label>
                                    <input id="${componentId}-card-content-0" type="text" value="Event Date" onchange="updateCardDetail('${componentId}', 0, 'content', this.value)" class="block w-full text-sm rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 dark:bg-gray-700 dark:text-white" placeholder="Event Date">
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="addCard('${componentId}')" class="mt-2 px-3 py-1 text-sm bg-qricket-500 text-white rounded hover:bg-qricket-600">Add Card</button>
                </div>
            </div>
        `;
    }

    // Ensure icon datalist exists early and initialize default data in memory
    try {
        if (typeof ensureIconDatalistLoaded === 'function') ensureIconDatalistLoaded();
    } catch (e) {}
    // Export functions
    window.createEventDetailsCardsComponent = window.createEventDetailsCardsComponent || createEventDetailsCardsComponent;
    
    // Local datalist loader to avoid dependency on components.js
    window.ensureIconDatalistLoaded = window.ensureIconDatalistLoaded || function() {
        if (window.__iconListLoaded) return;
        let dl = document.getElementById('icon_list_datalist');
        if (!dl) {
            dl = document.createElement('datalist');
            dl.id = 'icon_list_datalist';
            document.body.appendChild(dl);
        }
        const fallback = ['calendar','clock','map-pin','ticket','user','users','star','phone','mail','globe','location','wallet','camera'];
        fetch('https://cdn.jsdelivr.net/npm/lucide-static@latest/icons.json')
            .then(r => r.json())
            .then(list => {
                const names = Array.isArray(list) ? list : Object.keys(list||{});
                (names.length ? names : fallback).slice(0,200).forEach(n => {
                    const o = document.createElement('option');
                    o.value = n; dl.appendChild(o);
                });
                window.__iconListLoaded = true;
            })
            .catch(() => {
                fallback.forEach(n => { const o = document.createElement('option'); o.value = n; dl.appendChild(o); });
                window.__iconListLoaded = true;
            });
    };
    
    // Add Card (moved from components.js)
    window.addCard = window.addCard || function(componentId) {
        const container = document.getElementById(`cards_${componentId}`);
        if (container) {
            const cardItem = document.createElement('div');
            cardItem.className = 'card-item border border-gray-200 dark:border-gray-600 rounded-lg p-3';
            const nextIndex = container.querySelectorAll('.card-item').length;
            try { ensureIconDatalistLoaded(); } catch (e) {}
            cardItem.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label for="${componentId}-card-icon-${nextIndex}" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Icon</label>
                        <input id="${componentId}-card-icon-${nextIndex}" list="icon_list_datalist" type="text" value="calendar" onchange="updateCardDetail('${componentId}', ${nextIndex}, 'icon', this.value)" class="block w-full text-sm rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 dark:bg-gray-700 dark:text-white" placeholder="calendar">
                    </div>
                    <div>
                        <label for="${componentId}-card-title-${nextIndex}" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Title</label>
                        <input id="${componentId}-card-title-${nextIndex}" type="text" onchange="updateCardDetail('${componentId}', ${nextIndex}, 'title', this.value)" class="block w-full text-sm rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 dark:bg-gray-700 dark:text-white" placeholder="Date">
                    </div>
                    <div>
                        <label for="${componentId}-card-content-${nextIndex}" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Content</label>
                        <input id="${componentId}-card-content-${nextIndex}" type="text" onchange="updateCardDetail('${componentId}', ${nextIndex}, 'content', this.value)" class="block w-full text-sm rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 dark:bg-gray-700 dark:text-white" placeholder="Event Date">
                    </div>
                </div>`;
            container.appendChild(cardItem);
        }
    };
    
    // Hydrate Event Details Cards
    window.hydrateEventDetailsCards = function(componentId, data) {
        try {
            const d = data || {};
            const titleEl = document.getElementById(`${componentId}-title`);
            if (titleEl) titleEl.value = d.title || '';
            const container = document.getElementById(`cards_${componentId}`);
            if (!container) return;
            const cards = Array.isArray(d.cards) ? d.cards : [];
            // Ensure enough card items
            const current = container.querySelectorAll('.card-item').length;
            for (let i = current; i < cards.length; i++) {
                if (typeof window.addCard === 'function') window.addCard(componentId);
            }
            // Fill values
            cards.forEach((c, idx) => {
                const iconInput = container.querySelectorAll('.card-item')[idx]?.querySelector('input[list="icon_list_datalist"]');
                const titleInput = container.querySelectorAll('.card-item')[idx]?.querySelector('input[placeholder="Date"], input');
                const contentInput = container.querySelectorAll('.card-item')[idx]?.querySelector('input[placeholder="Event Date"], input');
                if (iconInput) iconInput.value = c.icon || '';
                if (titleInput) titleInput.value = c.title || '';
                if (contentInput) contentInput.value = c.content || '';
            });
        } catch(e) {}
    };
})();
