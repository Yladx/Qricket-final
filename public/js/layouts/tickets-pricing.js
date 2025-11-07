// Layout: tickets-pricing
(function(){
    function createTicketsPricingComponent(componentId) {
        return `
            <div class="space-y-4">
                <div>
                    <label for="${componentId}-title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Title</label>
                    <input type="text" id="${componentId}-title" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Choose Your Ticket">
                </div>
                <div>
                    <label for="${componentId}-subtitle" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Subtitle</label>
                    <input type="text" id="${componentId}-subtitle" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Select the ticket that best fits your needs">
                </div>
                <div>
                    <p id="${componentId}-tickets-label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ticket Options</p>
                    <div id="${componentId}-tickets-container" class="space-y-3" role="group" aria-labelledby="${componentId}-tickets-label">
                        <div class="ticket-item border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="${componentId}-ticket-name-0" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Ticket Name</label>
                                    <input id="${componentId}-ticket-name-0" type="text" class="w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="General Admission">
                                </div>
                                <div>
                                    <label for="${componentId}-ticket-price-0" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Price</label>
                                    <input id="${componentId}-ticket-price-0" type="text" class="w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="$25">
                                </div>
                            </div>
                            <div class="mt-3">
                                <label for="${componentId}-ticket-description-0" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Description</label>
                                    <input id="${componentId}-ticket-description-0" type="text" class="w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Perfect for individuals">
                            </div>
                            <div class="mt-3">
                                <label for="${componentId}-ticket-features-0" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Features (one per line)</label>
                                <textarea id="${componentId}-ticket-features-0" class="w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="3" placeholder="Event access&#10;Welcome materials&#10;Networking opportunities"></textarea>
                            </div>
                            <div class="mt-3 flex items-center space-x-4">
                                <div class="flex items-center">
                                    <input type="checkbox" id="${componentId}-ticket-popular-0" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <label for="${componentId}-ticket-popular-0" class="ml-2 text-xs text-gray-600 dark:text-gray-400">Most Popular</label>
                                </div>
                                <div>
                                    <label for="${componentId}-ticket-button-0" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Button Text</label>
                                    <input id="${componentId}-ticket-button-0" type="text" class="w-32 text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Select Ticket">
                                </div>
                            </div>
                            <button type="button" onclick="removeTicketItem(this)" class="mt-2 text-red-600 hover:text-red-800 text-sm" aria-label="Remove ticket option">Remove</button>
                        </div>
                    </div>
                    <button type="button" onclick="addTicketItem('${componentId}')" class="mt-2 text-indigo-600 hover:text-indigo-800 text-sm">+ Add Ticket Option</button>
                </div>
            </div>
        `;
    }

    // Export functions
    window.createTicketsPricingComponent = window.createTicketsPricingComponent || createTicketsPricingComponent;
    
    // Add/Remove Ticket item (moved from components.js)
    window.addTicketItem = window.addTicketItem || function(componentId) {
        const container = document.getElementById(`${componentId}-tickets-container`);
        if (container) {
            const nextIndex = container.querySelectorAll('.ticket-item').length;
            const ticketItem = document.createElement('div');
            ticketItem.className = 'ticket-item border border-gray-200 dark:border-gray-600 rounded-lg p-4';
            ticketItem.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="${componentId}-ticket-name-${nextIndex}" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Ticket Name</label>
                        <input id="${componentId}-ticket-name-${nextIndex}" type="text" class="w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="General Admission">
                    </div>
                    <div>
                        <label for="${componentId}-ticket-price-${nextIndex}" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Price</label>
                        <input id="${componentId}-ticket-price-${nextIndex}" type="text" class="w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="$25">
                    </div>
                </div>
                <div class="mt-3">
                    <label for="${componentId}-ticket-description-${nextIndex}" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Description</label>
                    <input id="${componentId}-ticket-description-${nextIndex}" type="text" class="w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Perfect for individuals">
                </div>
                <div class="mt-3">
                    <label for="${componentId}-ticket-features-${nextIndex}" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Features (one per line)</label>
                    <textarea id="${componentId}-ticket-features-${nextIndex}" class="w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="3" placeholder="Event access&#10;Welcome materials&#10;Networking opportunities"></textarea>
                </div>
                <div class="mt-3 flex items-center space-x-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="${componentId}-ticket-popular-${nextIndex}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="${componentId}-ticket-popular-${nextIndex}" class="ml-2 text-xs text-gray-600 dark:text-gray-400">Most Popular</label>
                    </div>
                    <div>
                        <label for="${componentId}-ticket-button-${nextIndex}" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Button Text</label>
                        <input id="${componentId}-ticket-button-${nextIndex}" type="text" class="w-32 text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Select Ticket">
                    </div>
                </div>
                <button type="button" onclick="removeTicketItem(this)" aria-label="Remove ticket option" class="mt-2 text-red-600 hover:text-red-800 text-sm">Remove</button>
            `;
            container.appendChild(ticketItem);
        }
    };
    window.removeTicketItem = window.removeTicketItem || function(button) {
        button.closest('.ticket-item')?.remove();
    };
    
    // Hydrate Tickets Pricing
    window.hydrateTicketsPricing = function(componentId, data) {
        try {
            const d = data || {};
            const titleEl = document.getElementById(`${componentId}-title`);
            const subEl = document.getElementById(`${componentId}-subtitle`);
            if (titleEl) titleEl.value = d.title || '';
            if (subEl) subEl.value = d.subtitle || '';
            const container = document.getElementById(`${componentId}-tickets-container`);
            if (!container) return;
            container.innerHTML = '';
            const tickets = Array.isArray(d.tickets) ? d.tickets : [];
            const add = (t = {}) => {
                if (typeof window.addTicketItem === 'function') window.addTicketItem(componentId);
                const item = container.lastElementChild;
                if (!item) return;
                const inputs = item.querySelectorAll('input, textarea, input[type="checkbox"]');
                // Map inputs by order as in template
                inputs[0] && (inputs[0].value = t.name || '');
                inputs[1] && (inputs[1].value = t.price || '');
                inputs[2] && (inputs[2].value = t.description || '');
                inputs[3] && (inputs[3].value = (t.features || ''));
                const checkbox = item.querySelector('input[type="checkbox"]');
                if (checkbox) checkbox.checked = !!t.popular;
                const btnText = item.querySelector('input.w-32');
                if (btnText) btnText.value = t.buttonText || '';
            };
            if (tickets.length === 0) add({}); else tickets.forEach(add);
        } catch(e) {}
    };
})();
