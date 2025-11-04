// Step 2 - Form field monitoring for autosave
(function() {
    let formFieldTimer;
    
    // Monitor form field changes for autosave
    function setupFormFieldMonitoring() {
        // Monitor any input, textarea, or select changes
        const form = document.querySelector('form[data-autosave-url]');
        if (!form) return;
        
        // Add event listeners to all form fields
        const formFields = form.querySelectorAll('input, textarea, select');
        formFields.forEach(field => {
            // Skip layout_data_input as it's handled separately
            if (field.id === 'layout_data_input') return;
            
            // Skip file inputs as they're handled in uploader.js
            if (field.type === 'file') return;
            
            field.addEventListener('input', function() {
                queueFormFieldAutosave();
            });
            
            field.addEventListener('change', function() {
                queueFormFieldAutosave();
            });
        });
        
        // Monitor checkbox changes
        const checkboxes = form.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                queueFormFieldAutosave();
            });
        });
    }
    
    // Debounced autosave for form field changes
    function queueFormFieldAutosave() {
        clearTimeout(formFieldTimer);
        formFieldTimer = setTimeout(() => {
            collectAndAutosaveFormData();
        }, 1000); // 1 second delay for form field changes
    }
    
    // Collect all form data and autosave
    function collectAndAutosaveFormData() {
        const form = document.querySelector('form[data-autosave-url]');
        if (!form) return;
        
        const formData = {};
        const inputs = form.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            // Skip layout_data_input as it's handled separately
            if (input.id === 'layout_data_input') return;
            
            // Skip file inputs as they're handled in uploader.js
            if (input.type === 'file') return;
            
            // Skip hidden inputs that are not relevant
            if (input.type === 'hidden' && !input.name) return;
            
            if (input.name) {
                if (input.type === 'checkbox') {
                    formData[input.name] = input.checked;
                } else if (input.value) {
                    formData[input.name] = input.value;
                }
            }
        });
        
        // Only autosave if there are changes
        if (Object.keys(formData).length > 0) {
            // console.log('Autosaving form field changes:', formData);
            if (typeof window.autosaveStep2Files === 'function') {
                autosaveStep2Files(formData);
            }
        }
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupFormFieldMonitoring);
    } else {
        setupFormFieldMonitoring();
    }
    
    // Expose function for manual triggering
    window.formFieldAutosave = collectAndAutosaveFormData;
})();
