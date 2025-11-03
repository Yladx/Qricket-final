// Premium Testimonials Slider layout - Modular
window.createPremiumTestimonialsSliderComponent = function(componentId) {
    return `
        <div id="${componentId}-testimonials" class="space-y-2"></div>
        <button type="button" class="mt-2 px-3 py-1.5 text-xs rounded bg-qricket-500 text-white" onclick="window.addTestimonialRow && addTestimonialRow('${componentId}')">Add Testimonial</button>
    `;
};

window.addTestimonialRow = function(componentId, quote = '', author = '') {
    const c = document.getElementById(`${componentId}-testimonials`);
    if (!c) return;
    const row = document.createElement('div');
    row.className = 'grid grid-cols-5 gap-2';
    row.innerHTML = `<input type='text' class='col-span-4 text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded' placeholder='Quote text' value='${(quote||'').replace(/'/g,"&#39;")}' oninput=\"updateLayoutJSON()\">` +
        `<input type='text' class='col-span-1 text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded' placeholder='Author' value='${(author||'').replace(/'/g,"&#39;")}' oninput=\"updateLayoutJSON()\">`;
    c.appendChild(row);
    try { if (typeof updateLayoutJSON === 'function') updateLayoutJSON(); } catch(_) {}
};

window.hydratePremiumTestimonialsSlider = function(componentId, data) {
    try {
        const c = document.getElementById(`${componentId}-testimonials`);
        if (!c) return;
        c.innerHTML = '';
        let items = [];
        if (Array.isArray(data?.testimonials)) {
            items = data.testimonials;
        } else if (typeof data === 'object' && Array.isArray(data)) {
            items = data;
        }
        if (items.length > 0) items.forEach(t => window.addTestimonialRow(componentId, t.quote, t.author));
        else window.addTestimonialRow(componentId);
    } catch(e) { window.addTestimonialRow(componentId); }
};
