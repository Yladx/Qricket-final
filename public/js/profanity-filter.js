/**
 * Real-time Profanity Filter
 * Automatically removes profanity words as users type
 */
(function() {
    'use strict';

    // Debounce function to limit API calls
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Filter profanity using the API
    async function filterProfanity(text) {
        if (!text || text.trim().length === 0) {
            return text;
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            
            const response = await fetch('/api/profanity/filter', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ text: text }),
            });

            if (!response.ok) {
                const errorText = await response.text();
                console.warn('Profanity filter API error:', response.status, errorText);
                return text; // Return original text if API fails
            }

            const data = await response.json();
            if (data.success && data.filtered !== undefined) {
                // Debug logging (remove in production)
                if (data.filtered !== data.original) {
                    console.log('Profanity filtered:', { original: data.original, filtered: data.filtered });
                }
                return data.filtered;
            }

            return text;
        } catch (error) {
            console.error('Profanity filter error:', error);
            return text; // Return original text on error
        }
    }

    // Track if we're currently filtering to prevent loops
    let isFiltering = false;

    // Process input field
    async function processInputField(input) {
        // Prevent infinite loops
        if (isFiltering) {
            return;
        }

        const originalValue = input.value;
        if (!originalValue || originalValue.trim().length === 0) {
            return;
        }

        const cursorPosition = input.selectionStart;

        try {
            isFiltering = true;

            // Filter the text
            const filteredValue = await filterProfanity(originalValue);

            // Only update if the value changed
            if (filteredValue !== originalValue) {
                // Calculate new cursor position
                // Filter the text before cursor to see how much was removed
                const textBeforeCursor = originalValue.substring(0, cursorPosition);
                const filteredBeforeCursor = await filterProfanity(textBeforeCursor);
                const newCursorPosition = filteredBeforeCursor.length;

                // Update the value
                input.value = filteredValue;

                // Restore cursor position (clamp to valid range)
                const safePosition = Math.min(newCursorPosition, filteredValue.length);
                input.setSelectionRange(safePosition, safePosition);

                // Trigger change event for form validation
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        } catch (error) {
            console.error('Error filtering profanity:', error);
        } finally {
            isFiltering = false;
        }
    }

    // Debounced version - reduced delay for faster response (100ms for near-instant feel)
    const debouncedProcessInput = debounce(processInputField, 100);

    // Initialize profanity filter on an input/textarea
    function initializeProfanityFilter(element) {
        // Skip if already initialized
        if (element.dataset.profanityFilterInitialized === 'true') {
            return;
        }

        // Mark as initialized
        element.dataset.profanityFilterInitialized = 'true';

        // Add input event listener - use 'input' for real-time detection
        element.addEventListener('input', function(e) {
            // Skip if we're currently filtering
            if (isFiltering) {
                return;
            }

            // Process immediately for better real-time feel
            debouncedProcessInput(this);
        }, { passive: true });

        // Also listen to keyup for immediate detection
        element.addEventListener('keyup', function(e) {
            // Skip if we're currently filtering
            if (isFiltering) {
                return;
            }

            // Skip modifier keys
            if (e.ctrlKey || e.metaKey || e.altKey) {
                return;
            }

            // Process immediately
            debouncedProcessInput(this);
        }, { passive: true });

        // Also check on paste
        element.addEventListener('paste', function(e) {
            setTimeout(() => {
                debouncedProcessInput(this);
            }, 10);
        });
    }

    // Initialize on page load
    function initializeAllFields() {
        // Find all text inputs and textareas
        const textInputs = document.querySelectorAll('input[type="text"], input[type="email"], textarea');
        
        console.log('[ProfanityFilter] Initializing on', textInputs.length, 'fields');
        
        textInputs.forEach(input => {
            // Skip if it's a search field or has a specific class to exclude
            if (input.classList.contains('no-profanity-filter') || 
                input.id === 'recipient_search' ||
                input.name === 'search') {
                return;
            }

            initializeProfanityFilter(input);
        });
        
        console.log('[ProfanityFilter] Initialization complete');
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeAllFields);
    } else {
        initializeAllFields();
    }

    // Re-initialize for dynamically added fields
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Element node
                    // Check if the added node is an input/textarea
                    if (node.tagName === 'INPUT' || node.tagName === 'TEXTAREA') {
                        if (!node.classList.contains('no-profanity-filter')) {
                            initializeProfanityFilter(node);
                        }
                    }
                    // Check for inputs/textareas within the added node
                    const inputs = node.querySelectorAll && node.querySelectorAll('input[type="text"], input[type="email"], textarea');
                    if (inputs) {
                        inputs.forEach(input => {
                            if (!input.classList.contains('no-profanity-filter')) {
                                initializeProfanityFilter(input);
                            }
                        });
                    }
                }
            });
        });
    });

    // Start observing when body is available
    function startObserving() {
        if (document.body) {
            observer.observe(document.body, {
                childList: true,
                subtree: true,
            });
        } else {
            // Wait for body to be available
            setTimeout(startObserving, 100);
        }
    }
    
    // Start observing
    if (document.body) {
        startObserving();
    } else {
        document.addEventListener('DOMContentLoaded', startObserving);
    }

    // Export for manual initialization if needed
    window.ProfanityFilter = {
        initialize: initializeProfanityFilter,
        filter: filterProfanity,
    };
})();

