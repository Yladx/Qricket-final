// Modal controls for Step 2 layout selection
(function () {
    function openLayoutModal() {
        const modal = document.getElementById('layoutModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
    }

    function closeLayoutModal() {
        const modal = document.getElementById('layoutModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }

    function selectLayout(layoutType) {
        // Delegate to builder in event-step2.js
        if (typeof window.addLayoutComponent === 'function') {
            window.addLayoutComponent(layoutType);
        }
        closeLayoutModal();
    }

    // Expose globally for buttons/hooks in Blade
    window.openLayoutModal = openLayoutModal;
    window.closeLayoutModal = closeLayoutModal;
    window.selectLayout = selectLayout;
})();


