// Layout: footer-simple
(function(){
    function createFooterSimpleComponent(componentId) {
        return `
            <div class="space-y-4">
                <div>
                    <label for="${componentId}-event_info_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Event Info Title</label>
                    <input type="text" id="${componentId}-event_info_title" class="w-full bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Event Information">
                </div>
                <div>
                    <label for="${componentId}-contact_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Contact Title</label>
                    <input type="text" id="${componentId}-contact_title" class="w-full bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Contact Us">
                </div>
                <div>
                    <label for="${componentId}-social_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Social Title</label>
                    <input type="text" id="${componentId}-social_title" class="w-full bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Follow Us">
                </div>
                <div>
                    <label for="${componentId}-copyright" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Copyright Text</label>
                    <input type="text" id="${componentId}-copyright" class="w-full bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Qricket">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Event Info Items</label>
                    <div id="${componentId}-event_info-container" class="space-y-2">
                        <div class="event-info-item flex space-x-2">
                            <label for="${componentId}-event-info-label-0" class="sr-only">Event Info Label</label>
                            <input id="${componentId}-event-info-label-0" type="text" class="flex-1 text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Label (e.g., Date)">
                            <label for="${componentId}-event-info-value-0" class="sr-only">Event Info Value</label>
                            <input id="${componentId}-event-info-value-0" type="text" class="flex-1 text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Value (e.g., March 15, 2024)">
                            <button type="button" onclick="removeEventInfoItem(this)" aria-label="Remove event info item" class="text-red-600 hover:text-red-800 text-sm">Remove</button>
                        </div>
                    </div>
                    <button type="button" onclick="addEventInfoItem('${componentId}')" class="mt-2 text-indigo-600 hover:text-indigo-800 text-sm">+ Add Event Info</button>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Contact Info Items</label>
                    <div id="${componentId}-contact_info-container" class="space-y-2">
                        <div class="contact-info-item flex space-x-2">
                            <label for="${componentId}-contact-info-label-0" class="sr-only">Contact Info Label</label>
                            <input id="${componentId}-contact-info-label-0" type="text" class="flex-1 text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Label (e.g., Email)">
                            <label for="${componentId}-contact-info-value-0" class="sr-only">Contact Info Value</label>
                            <input id="${componentId}-contact-info-value-0" type="text" class="flex-1 text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Value (e.g., info@example.com)">
                            <button type="button" onclick="removeContactInfoItem(this)" aria-label="Remove contact info item" class="text-red-600 hover:text-red-800 text-sm">Remove</button>
                        </div>
                    </div>
                    <button type="button" onclick="addContactInfoItem('${componentId}')" class="mt-2 text-indigo-600 hover:text-indigo-800 text-sm">+ Add Contact Info</button>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Social Links</label>
                    <div id="${componentId}-social_links-container" class="space-y-2">
                        <div class="social-link-item flex space-x-2">
                            <label for="${componentId}-social-link-url-0" class="sr-only">Social Link URL</label>
                            <input id="${componentId}-social-link-url-0" type="text" class="flex-1 text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="URL (e.g., https://twitter.com)">
                            <label for="${componentId}-social-link-icon-0" class="sr-only">Social Link Icon</label>
                            <input id="${componentId}-social-link-icon-0" type="text" class="w-32 text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Icon (e.g., fab fa-twitter)">
                            <button type="button" onclick="removeSocialLinkItem(this)" aria-label="Remove social link item" class="text-red-600 hover:text-red-800 text-sm">Remove</button>
                        </div>
                    </div>
                    <button type="button" onclick="addSocialLinkItem('${componentId}')" class="mt-2 text-indigo-600 hover:text-indigo-800 text-sm">+ Add Social Link</button>
                </div>
            </div>
        `;
    }

    // Export functions
    window.createFooterSimpleComponent = window.createFooterSimpleComponent || createFooterSimpleComponent;
    
    // Add/Remove row helpers (moved from components.js)
    window.addEventInfoItem = window.addEventInfoItem || function(componentId) {
        const container = document.getElementById(`${componentId}-event_info-container`);
        if (container) {
            const el = document.createElement('div');
            el.className = 'event-info-item flex space-x-2';
            const nextIndex = container.querySelectorAll('.event-info-item').length;
            el.innerHTML = `
                <label for="${componentId}-event-info-label-${nextIndex}" class="sr-only">Event Info Label</label>
                <input id="${componentId}-event-info-label-${nextIndex}" type="text" class="flex-1 text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Label (e.g., Date)">
                <label for="${componentId}-event-info-value-${nextIndex}" class="sr-only">Event Info Value</label>
                <input id="${componentId}-event-info-value-${nextIndex}" type="text" class="flex-1 text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Value (e.g., March 15, 2024)">
                <button type="button" onclick="removeEventInfoItem(this)" aria-label="Remove event info item" class="text-red-600 hover:text-red-800 text-sm">Remove</button>`;
            container.appendChild(el);
        }
    };
    window.removeEventInfoItem = window.removeEventInfoItem || function(button) { button.closest('.event-info-item')?.remove(); };
    window.addContactInfoItem = window.addContactInfoItem || function(componentId) {
        const container = document.getElementById(`${componentId}-contact_info-container`);
        if (container) {
            const el = document.createElement('div');
            el.className = 'contact-info-item flex space-x-2';
            const nextIndex = container.querySelectorAll('.contact-info-item').length;
            el.innerHTML = `
                <label for="${componentId}-contact-info-label-${nextIndex}" class="sr-only">Contact Info Label</label>
                <input id="${componentId}-contact-info-label-${nextIndex}" type="text" class="flex-1 text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Label (e.g., Email)">
                <label for="${componentId}-contact-info-value-${nextIndex}" class="sr-only">Contact Info Value</label>
                <input id="${componentId}-contact-info-value-${nextIndex}" type="text" class="flex-1 text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Value (e.g., info@example.com)">
                <button type="button" onclick="removeContactInfoItem(this)" aria-label="Remove contact info item" class="text-red-600 hover:text-red-800 text-sm">Remove</button>`;
            container.appendChild(el);
        }
    };
    window.removeContactInfoItem = window.removeContactInfoItem || function(button) { button.closest('.contact-info-item')?.remove(); };
    window.addSocialLinkItem = window.addSocialLinkItem || function(componentId) {
        const container = document.getElementById(`${componentId}-social_links-container`);
        if (container) {
            const el = document.createElement('div');
            el.className = 'social-link-item flex space-x-2';
            const nextIndex = container.querySelectorAll('.social-link-item').length;
            el.innerHTML = `
                <label for="${componentId}-social-link-url-${nextIndex}" class="sr-only">Social Link URL</label>
                <input id="${componentId}-social-link-url-${nextIndex}" type="text" class="flex-1 text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="URL (e.g., https://twitter.com)">
                <label for="${componentId}-social-link-icon-${nextIndex}" class="sr-only">Social Link Icon</label>
                <input id="${componentId}-social-link-icon-${nextIndex}" type="text" class="w-32 text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Icon (e.g., fab fa-twitter)">
                <button type="button" onclick="removeSocialLinkItem(this)" aria-label="Remove social link item" class="text-red-600 hover:text-red-800 text-sm">Remove</button>`;
            container.appendChild(el);
        }
    };
    window.removeSocialLinkItem = window.removeSocialLinkItem || function(button) { button.closest('.social-link-item')?.remove(); };
    
    // Hydrate Footer Simple
    window.hydrateFooterSimple = function(componentId, data) {
        try {
            const d = data || {};
            const setVal = (id, val) => { const el = document.getElementById(`${componentId}-${id}`); if (el) el.value = val || ''; };
            setVal('event_info_title', d.event_info_title);
            setVal('contact_title', d.contact_title);
            setVal('social_title', d.social_title);
            setVal('copyright', d.copyright);
            // Lists
            const ei = document.getElementById(`${componentId}-event_info-container`);
            const ci = document.getElementById(`${componentId}-contact_info-container`);
            const sl = document.getElementById(`${componentId}-social_links-container`);
            const buildRows = (container, rows, placeholders) => {
                if (!container) return;
                container.innerHTML = '';
                const isSocial = container.id.indexOf('social') !== -1;
                const isContact = container.id.indexOf('contact') !== -1;
                const addRow = (a = '', b = '', idx = 0) => {
                    const div = document.createElement('div');
                    div.className = isSocial ? 'social-link-item flex space-x-2' : (isContact ? 'contact-info-item flex space-x-2' : 'event-info-item flex space-x-2');
                    const labelId = isSocial ? 'social-link' : (isContact ? 'contact-info' : 'event-info');
                    const label1 = isSocial ? 'Social Link URL' : (isContact ? 'Contact Info Label' : 'Event Info Label');
                    const label2 = isSocial ? 'Social Link Icon' : (isContact ? 'Contact Info Value' : 'Event Info Value');
                    div.innerHTML = `<label for="${componentId}-${labelId}-${idx === 0 ? 'label' : 'url'}-${idx}" class="sr-only">${label1}</label>` +
                                   `<input id="${componentId}-${labelId}-${idx === 0 ? 'label' : 'url'}-${idx}" type="text" class="flex-1 text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="${placeholders[0]}" value="${(a||'').replace(/"/g,'&quot;')}">` +
                                   `<label for="${componentId}-${labelId}-${idx === 0 ? 'value' : 'icon'}-${idx}" class="sr-only">${label2}</label>` +
                                   `<input id="${componentId}-${labelId}-${idx === 0 ? 'value' : 'icon'}-${idx}" type="text" class="${isSocial ? 'w-32' : 'flex-1'} text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="${placeholders[1]}" value="${(b||'').replace(/"/g,'&quot;')}">` +
                                   `<button type="button" onclick="${isSocial ? 'removeSocialLinkItem' : (isContact ? 'removeContactInfoItem' : 'removeEventInfoItem')}(this)" aria-label="Remove ${isSocial ? 'social link' : (isContact ? 'contact info' : 'event info')} item" class="text-red-600 hover:text-red-800 text-sm">Remove</button>`;
                    container.appendChild(div);
                };
                if (!rows || rows.length === 0) addRow('', '', 0); else rows.forEach((r, idx) => addRow(r.label || r.url, r.value || r.icon, idx));
            };
            buildRows(ei, d.event_info, ['Label (e.g., Date)', 'Value (e.g., March 15, 2024)']);
            buildRows(ci, d.contact_info, ['Label (e.g., Email)', 'Value (e.g., info@example.com)']);
            buildRows(sl, d.social_links, ['URL (e.g., https://twitter.com)', 'Icon (e.g., fab fa-twitter)']);
        } catch(e) {}
    };
})();
