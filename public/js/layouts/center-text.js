

function createCenterTextComponent(componentId) {
    return `
        <div class="w-full flex justify-center">
            <div class="w-full md:w-2/3 lg:w-1/2 text-center space-y-4">
            <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Title</label>
                    <input type="text" id="${componentId}-title" onchange="updateComponentDetail('${componentId}', 'title', this.value)" class="mx-auto block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 dark:bg-gray-700 dark:text-white text-center" placeholder="Enter title...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Content</label>
                    <textarea id="${componentId}-content" onchange="updateComponentDetail('${componentId}', 'content', this.value)" class="mx-auto w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-qricket-500 focus:ring-qricket-500 rounded-md shadow-sm text-center" rows="4" placeholder="Enter content..."></textarea>
                </div>
            </div>
        </div>
    `;
}
// Export
window.createCenterTextComponent = window.createCenterTextComponent || createCenterTextComponent;

// Hydrate Center Text fields
window.hydrateCenterText = function(componentId, data) {
    try {
        const d = data || {};
        const titleEl = document.getElementById(`${componentId}-title`);
        const contentEl = document.getElementById(`${componentId}-content`);
        if (titleEl) titleEl.value = d.title || '';
        if (contentEl) contentEl.value = d.content || '';
    } catch(e) {}
};