// Premium Schedule Timeline layout - Modular
window.createPremiumScheduleTimelineComponent = function(componentId) {
    return `
        <div class="space-y-2">
            <div class="grid grid-cols-5 gap-2 text-xs text-gray-500 dark:text-gray-400">
                <div class="col-span-2">Time:</div>
                <div class="col-span-3">Activity / Description:</div>
            </div>
            <div id="${componentId}-timeline-container" class="space-y-2"></div>
            <button type="button" class="px-3 py-1.5 text-xs rounded bg-qricket-500 text-white" onclick="window.addTimelineRow && addTimelineRow('${componentId}')">Add Timeline Item</button>
        </div>`;
};

window.addTimelineRow = function(componentId, time = '', item = '') {
    const c = document.getElementById(`${componentId}-timeline-container`);
    if (!c) return;
    const row = document.createElement('div');
    row.className = 'grid grid-cols-5 gap-2';
    row.innerHTML = `<input type='text' class='col-span-2 text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded' placeholder='Time (e.g., 10:00)' value='${(time||'').replace(/'/g, "&#39;")}' oninput=\"updateLayoutJSON()\">` +
        `<input type='text' class='col-span-3 text-sm bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded' placeholder='Activity / Description' value='${(item||'').replace(/'/g, "&#39;")}' oninput=\"updateLayoutJSON()\">`;
    c.appendChild(row);
    try { if (typeof updateLayoutJSON === 'function') updateLayoutJSON(); } catch(_) {}
};

window.hydratePremiumScheduleTimeline = function(componentId, data) {
    try {
        const c = document.getElementById(`${componentId}-timeline-container`);
        if (!c) return;
        c.innerHTML = '';
        let items = [];
        if (Array.isArray(data?.timeline)) {
            items = data.timeline;
        } else if (Array.isArray(data)) {
            items = data;
        }
        if (items.length > 0) items.forEach(t => window.addTimelineRow(componentId, t.time, t.item));
        else window.addTimelineRow(componentId);
    } catch(e) { window.addTimelineRow(componentId); }
};
