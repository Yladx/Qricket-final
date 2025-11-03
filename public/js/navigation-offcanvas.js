document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('sidebar-toggle');
    const panel = document.getElementById('sidebar-panel');
    const overlay = document.getElementById('sidebar-overlay');
    const closeTop = document.getElementById('sidebar-close');
    const content = document.getElementById('sidebar-content');

    function openPanel() {
        if (panel && overlay) {
            panel.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        }
    }

    function closePanel() {
        if (panel && overlay) {
            panel.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }
    }

    // Remove conflicting event listener - let Alpine.js handle the toggle
    // if (toggle) toggle.addEventListener('click', function (e) { e.preventDefault(); openPanel(); });
    if (closeTop) closeTop.addEventListener('click', closePanel);
    if (overlay) overlay.addEventListener('click', closePanel);

    // Clone nav links into sidebar (mobile), honoring current side (user vs organizer)
    if (content) {
        content.innerHTML = '';
        const added = new Set();
        const userDesktop = document.getElementById('user-nav-links');
        const orgDesktop = document.getElementById('organizer-nav-links');
        const userMobile = document.getElementById('user-nav-links-mobile');
        const orgMobile = document.getElementById('organizer-nav-links-mobile');
        const isOrganizer = (orgDesktop && !orgDesktop.classList.contains('hidden')) || (orgMobile && !orgMobile.classList.contains('hidden'));

        // Sync the single mobile mode toggle UI with current mode
        (function syncMobileModeToggle() {
            const toggleInline = document.getElementById('mode-toggle-mobile-inline');
            const sliderInline = document.getElementById('mode-toggle-slider-mobile-inline');
            const labelInline = document.getElementById('mode-text-mobile');
            if (toggleInline && sliderInline && labelInline) {
                toggleInline.setAttribute('aria-checked', isOrganizer ? 'true' : 'false');
                // Reset both positions, then apply
                sliderInline.classList.remove('translate-x-1', 'translate-x-6');
                sliderInline.classList.add(isOrganizer ? 'translate-x-6' : 'translate-x-1');
                labelInline.textContent = isOrganizer ? 'Organizer' : 'User';
            }
        })();

        function addLinksFrom(container) {
            if (!container) return;
            Array.from(container.querySelectorAll('a[href]')).forEach(function (a) {
                const href = a.getAttribute('href');
                const text = (a.textContent || '').trim();
                if (!href || !text) return;
                const key = href + '|' + text;
                if (added.has(key)) return; added.add(key);
                const item = document.createElement('a');
                item.href = href;
                item.className = 'block px-3 py-2 rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700';
                item.textContent = text;
                item.addEventListener('click', closePanel);
                content.appendChild(item);
            });
        }

        if (isOrganizer) {
            // Organizer side links (e.g., Dashboard, Events)
            addLinksFrom(orgDesktop);
            addLinksFrom(orgMobile);
        } else {
            // User side links (e.g., Homepage)
            addLinksFrom(userDesktop);
            addLinksFrom(userMobile);
        }

        // Always include common links (Subscription, Help & Support)
        const commonLinks = Array.from(document.querySelectorAll('nav a[href]')).filter(function (a) {
            const href = a.getAttribute('href') || '';
            return /subscription|help/.test(href);
        });
        commonLinks.forEach(function (a) { addLinksFrom({ querySelectorAll: () => [a] }); });

        // Append Profile link after Help & Support if available in page nav
        (function appendProfileLink() {
            const profileCandidate = Array.from(document.querySelectorAll('nav a[href]')).find(function (a) {
                const href = a.getAttribute('href') || '';
                const text = (a.textContent || '').toLowerCase();
                return href.includes('/profile') || text.includes('profile');
            });
            if (profileCandidate) {
                const href = profileCandidate.getAttribute('href');
                const item = document.createElement('a');
                item.href = href;
                item.className = 'block px-3 py-2 rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700';
                item.textContent = profileCandidate.textContent.trim() || 'Profile';
                item.addEventListener('click', closePanel);
                content.appendChild(item);
            }
        })();
    }

    // Ensure clicking the inline toggle updates UI immediately (visual only; action handled inline via onclick)
    (function bindInlineToggle() {
        const toggleInline = document.getElementById('mode-toggle-mobile-inline');
        const sliderInline = document.getElementById('mode-toggle-slider-mobile-inline');
        const labelInline = document.getElementById('mode-text-mobile');
        if (!toggleInline || !sliderInline || !labelInline) return;
        toggleInline.addEventListener('click', function() {
            const on = this.getAttribute('aria-checked') === 'true';
            const next = !on;
            this.setAttribute('aria-checked', next ? 'true' : 'false');
            sliderInline.classList.remove('translate-x-1', 'translate-x-6');
            sliderInline.classList.add(next ? 'translate-x-6' : 'translate-x-1');
            labelInline.textContent = next ? 'Organizer' : 'User';
        });
    })();
});

