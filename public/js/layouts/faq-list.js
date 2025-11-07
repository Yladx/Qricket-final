// Layout: faq-list
(function(){
    function createFaqListComponent(componentId) {
        return `
            <div class="space-y-4">
                <div>
                    <label for="${componentId}-title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Title</label>
                    <input type="text" id="${componentId}-title" class="w-full bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Frequently Asked Questions">
                </div>
                <div>
                    <label for="${componentId}-subtitle" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Subtitle</label>
                    <input type="text" id="${componentId}-subtitle" class="w-full bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Find answers to common questions">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">FAQ Items</label>
                    <div id="${componentId}-faqs-container" class="space-y-3">
                        <div class="faq-item border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="${componentId}-faq-question-0" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Question</label>
                                    <input id="${componentId}-faq-question-0" type="text" class="faq-question w-full text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="What should I bring to the event?">
                                </div>
                                <div>
                                    <label for="${componentId}-faq-answer-0" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Answer</label>
                                    <textarea id="${componentId}-faq-answer-0" class="faq-answer w-full text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="2" placeholder="Please bring a valid ID and your ticket."></textarea>
                                </div>
                            </div>
                            <button type="button" onclick="removeFaqItem(this)" class="mt-2 text-red-600 hover:text-red-800 text-sm" aria-label="Remove FAQ item">Remove</button>
                        </div>
                    </div>
                    <button type="button" onclick="addFaqItem('${componentId}')" class="mt-2 text-indigo-600 hover:text-indigo-800 text-sm" aria-label="Add FAQ item">+ Add FAQ Item</button>
                </div>
            </div>
        `;
    }

    // Export functions
    window.createFaqListComponent = window.createFaqListComponent || createFaqListComponent;
    
    // Add/Remove FAQ item (moved from components.js)
    window.addFaqItem = window.addFaqItem || function(componentId) {
        const container = document.getElementById(`${componentId}-faqs-container`);
        if (container) {
            const faqItem = document.createElement('div');
            faqItem.className = 'faq-item border border-gray-200 dark:border-gray-600 rounded-lg p-4';
            const nextIndex = container.querySelectorAll('.faq-item').length;
            faqItem.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="${componentId}-faq-question-${nextIndex}" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Question</label>
                        <input id="${componentId}-faq-question-${nextIndex}" type="text" class="faq-question w-full text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="What should I bring to the event?">
                    </div>
                    <div>
                        <label for="${componentId}-faq-answer-${nextIndex}" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Answer</label>
                        <textarea id="${componentId}-faq-answer-${nextIndex}" class="faq-answer w-full text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="2" placeholder="Please bring a valid ID and your ticket."></textarea>
                    </div>
                </div>
                <button type="button" onclick="removeFaqItem(this)" class="mt-2 text-red-600 hover:text-red-800 text-sm" aria-label="Remove FAQ item">Remove</button>`;
            container.appendChild(faqItem);
            try { if (typeof updateLayoutJSON === 'function') updateLayoutJSON(); } catch(_) {}
        }
    };
    window.removeFaqItem = window.removeFaqItem || function(button) {
        button.closest('.faq-item')?.remove();
    };
    
    // Hydrate FAQ List
    window.hydrateFaqListComponent = function(componentId, data) {
        try {
            const d = data || {};
            const titleEl = document.getElementById(`${componentId}-title`);
            const subEl = document.getElementById(`${componentId}-subtitle`);
            if (titleEl) titleEl.value = d.title || '';
            if (subEl) subEl.value = d.subtitle || '';
            const container = document.getElementById(`${componentId}-faqs-container`);
            if (!container) return;
            container.innerHTML = '';
            const items = Array.isArray(d.faqs) ? d.faqs : [];
            const makeItem = (q = '', a = '') => {
                const div = document.createElement('div');
                div.className = 'faq-item border border-gray-200 dark:border-gray-600 rounded-lg p-4';
                const nextIndex = container.querySelectorAll('.faq-item').length;
                div.innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="${componentId}-faq-question-${nextIndex}" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Question</label>
                            <input id="${componentId}-faq-question-${nextIndex}" type="text" class="faq-question w-full text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="What should I bring to the event?" value="${(q||'').replace(/"/g, '&quot;')}">
                        </div>
                        <div>
                            <label for="${componentId}-faq-answer-${nextIndex}" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Answer</label>
                            <textarea id="${componentId}-faq-answer-${nextIndex}" class="faq-answer w-full text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="2" placeholder="Please bring a valid ID and your ticket.">${(a||'').replace(/</g,'&lt;')}</textarea>
                        </div>
                    </div>
                    <button type="button" onclick="removeFaqItem(this)" aria-label="Remove FAQ item" class="mt-2 text-red-600 hover:text-red-800 text-sm">Remove</button>`;
                container.appendChild(div);
            };
            if (items.length === 0) makeItem(); else items.forEach(it => makeItem(it.question, it.answer));
        } catch(e) {}
    };
})();
