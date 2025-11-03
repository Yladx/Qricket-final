// Step 2 - TinyMCE lazy initializer (restored)
(function () {
    function initializeTinyMCEForLayout(componentId) {
        if (typeof tinymce === 'undefined') return;
        const root = document.getElementById(componentId);
        if (!root) return;
        root.querySelectorAll('textarea').forEach((ta) => {
            if (tinymce.get(ta.id)) return;
            tinymce.init({
                selector: `#${ta.id}`,
                menubar: false,
                branding: false,
                height: 220,
                plugins: 'link lists autoresize',
                toolbar: 'bold italic underline | bullist numlist | link removeformat',
                skin: document.documentElement.classList.contains('dark') ? 'oxide-dark' : 'oxide',
                content_css: document.documentElement.classList.contains('dark') ? 'dark' : 'default',
                setup(editor) {
                    editor.on('change keyup blur', () => {
                        const value = editor.getContent();
                        const comp = window.layoutComponents && window.layoutComponents.find(c => c.id === componentId);
                        if (!comp) return;
                        const key = ta.id.replace(`${componentId}-`, '');
                        if (!comp.data) comp.data = {};
                        comp.data[key] = value;
                        if (typeof window.updateLayoutJSONBatched === 'function') window.updateLayoutJSONBatched();
                    });
                }
            });
        });
    }
    window.initializeTinyMCEForLayout = initializeTinyMCEForLayout;
})();


