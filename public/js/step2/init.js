document.addEventListener('DOMContentLoaded', function() {
    // Expose globals
    window.addLayoutComponent = window.addLayoutComponent || addLayoutComponent;
    window.removeLayoutComponent = window.removeLayoutComponent || removeLayoutComponent;
    window.moveComponentUp = window.moveComponentUp || moveComponentUp;
    window.moveComponentDown = window.moveComponentDown || moveComponentDown;
    window.openLayoutModal = window.openLayoutModal || openLayoutModal;
    window.closeLayoutModal = window.closeLayoutModal || closeLayoutModal;
    window.selectLayout = window.selectLayout || selectLayout;
    window.updateComponentDetail = window.updateComponentDetail || updateComponentDetail;

    // Prevent duplicate renders causing visible flicker
    window.__layoutRenderKey = window.__layoutRenderKey || null;
    function computeKey(arr){
        try { return JSON.stringify(arr); } catch(_) { return String(Date.now()); }
    }
    function renderLayoutArray(arr) {
        const normalized = arr.map(item => ({
            layout: item.layout || item.type,
            details: item.details || item.data || {}
        }));
        const key = computeKey(normalized);
        if (window.__layoutRenderKey === key) { return; }
        window.__layoutRenderKey = key;
        const container = document.getElementById('layoutComponents');
        if (container) container.innerHTML = '';
        window.layoutComponents = [];
        window.componentCounter = 0;
        normalized.forEach(addLayoutComponentFromData);
        setTimeout(() => { if (typeof updateLayoutJSON === 'function') updateLayoutJSON(); }, 100);
    }

    window.__fetchLayoutInFlight = false;
    async function fetchAndRenderLatestLayout() {
        if (!window.existingLayoutPath) return false;
        if (window.__fetchLayoutInFlight) return false;
        window.__fetchLayoutInFlight = true;
        try {
            const url = (window.__layoutLoadUrlTemplate || '').replace('REPLACE', encodeURIComponent(window.existingLayoutPath));
            if (!url) return false;
            const resp = await fetch(url + `?t=${Date.now()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!resp.ok) return false;
            const data = await resp.json();
            if (Array.isArray(data)) {
                window.existingLayoutData = data;
                renderLayoutArray(data);
                return true;
            }
        } catch(_) { /* noop */ }
        finally { window.__fetchLayoutInFlight = false; }
        return false;
    }

    // Expose for external callers (e.g., pageshow handler in Blade)
    window.fetchAndRenderLatestLayout = fetchAndRenderLatestLayout;
    window.__renderLayoutArray = renderLayoutArray;

    // Provide tickets preview hydration for tickets-pricing stub (no fields needed)
    window.hydrateTicketsPreview = function(componentId){
        const box = document.getElementById(`${componentId}-tickets-preview`);
        if (!box) return;
        try {
            const eventTickets = window.__eventTickets || [];
            if (Array.isArray(eventTickets) && eventTickets.length > 0) {
                box.innerHTML = eventTickets.map(t => `
                    <div class=\"flex items-center justify-between border-b border-gray-200 dark:border-gray-700 py-2\">\n\
                        <div class=\"text-sm font-medium text-gray-800 dark:text-gray-200\">${t.name || 'Ticket'}</div>\n\
                        <div class=\"text-sm text-qricket-600 dark:text-qricket-400\">₱${Number(t.price || 0).toFixed(2)}</div>\n\
                    </div>
                `).join('');
            } else {
                box.innerHTML = `
                    <div class=\"grid grid-cols-1 md:grid-cols-2 gap-3\">\n\
                        <div class=\"p-3 rounded-md bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700\">\n\
                            <div class=\"text-sm font-semibold text-gray-800 dark:text-gray-200\">General Admission</div>\n\
                            <div class=\"text-sm text-qricket-600 dark:text-qricket-400\">₱0.00</div>\n\
                        </div>\n\
                        <div class=\"p-3 rounded-md bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700\">\n\
                            <div class=\"text-sm font-semibold text-gray-800 dark:text-gray-200\">VIP Access</div>\n\
                            <div class=\"text-sm text-qricket-600 dark:text-qricket-400\">₱0.00</div>\n\
                        </div>\n\
                    </div>`;
            }
        } catch(_) {}
    };

    // Load latest from R2 if path is available; otherwise fall back to embedded data
    (async function initLoad(){
        // Try remote first; if it fails, render embedded once
        const loaded = await fetchAndRenderLatestLayout();
        if (!loaded && window.existingLayoutData && Array.isArray(window.existingLayoutData)) {
            try { renderLayoutArray(window.existingLayoutData); } catch(_) {}
        }
    })();

    // Generic listeners to update JSON and autosave
    const debounce = (fn, delay = 500) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn.apply(null, a), delay); }; };
    const persistLayoutDebounced = debounce(() => { try { updateLayoutJSON(); } catch (_) {} }, 500);

    document.addEventListener('input', function(event) {
        const tag = event.target.tagName;
        if (tag === 'TEXTAREA' || tag === 'INPUT' || tag === 'SELECT') persistLayoutDebounced();
    });
    document.addEventListener('change', function(event) {
        const tag = event.target.tagName;
        if (event.target.type === 'file' || tag === 'SELECT' || event.target.type === 'checkbox') persistLayoutDebounced();
    });

    // Footer autosave on input changes (debounced)
    (function(){
        const titleEl = document.getElementById('footer_title');
        const emailEl = document.getElementById('footer_email');
        const descEl = document.getElementById('footer_desc');
        const facebookEl = document.getElementById('footer_facebook');
        const twitterEl = document.getElementById('footer_twitter');
        const instagramEl = document.getElementById('footer_instagram');
        const youtubeEl = document.getElementById('footer_youtube');
        const tiktokEl = document.getElementById('footer_tiktok');
        const statusEl = document.getElementById('save_footer_status');
        
        const footerElements = [titleEl, emailEl, descEl, facebookEl, twitterEl, instagramEl, youtubeEl, tiktokEl];
        if (!footerElements.some(el => el)) return;
        
        const debounce = (fn, d=600)=>{ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), d); }; };
        const doSave = debounce(async () => {
            try {
                if (statusEl) statusEl.textContent = 'Saving…';
                const eventId = (new URLSearchParams(location.search)).get('id') || window.eventId || '';
                const footer = {
                    title: titleEl?.value || '',
                    email: emailEl?.value || '',
                    description: descEl?.value || '',
                    facebook: facebookEl?.value || '',
                    twitter: twitterEl?.value || '',
                    instagram: instagramEl?.value || '',
                    youtube: youtubeEl?.value || '',
                    tiktok: tiktokEl?.value || ''
                };
                const resp = await fetch('/organizer/events/update-session-layout', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                    body: JSON.stringify({ footer_data: footer, id: eventId })
                });
                const data = await resp.json();
                if (statusEl) statusEl.textContent = data && data.success ? 'Saved' : 'Save failed';
                setTimeout(()=>{ if (statusEl) statusEl.textContent=''; }, 1500);
            } catch(_) { if (statusEl) statusEl.textContent = 'Save failed'; }
        }, 700);
        ['input','change'].forEach(evt => {
            footerElements.forEach(el => el && el.addEventListener(evt, doSave));
        });
    })();
});
