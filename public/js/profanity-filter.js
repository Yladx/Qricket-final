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
            const response = await fetch('/api/profanity/filter', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ text: text }),
            });

            if (!response.ok) {
                console.warn('Profanity filter API error:', response.status);
                return text; // Return original text if API fails
            }

            const data = await response.json();
            if (data.success && data.filtered !== undefined) {
                return data.filtered;
            }

            return text;
        } catch (error) {
            console.warn('Profanity filter error:', error);
            return text; // Return original text on error
        }
    }

    // Process input field
    async function processInputField(input) {
        const originalValue = input.value;
        const cursorPosition = input.selectionStart;
        const textBeforeCursor = originalValue.substring(0, cursorPosition);
        const textAfterCursor = originalValue.substring(cursorPosition);

        // Filter the text
        const filteredValue = await filterProfanity(originalValue);

        // Only update if the value changed
        if (filteredValue !== originalValue) {
            // Calculate new cursor position based on filtered text
            // Try to maintain cursor position relative to the text before it
            const filteredBeforeCursor = await filterProfanity(textBeforeCursor);
            const newCursorPosition = filteredBeforeCursor.length;

            // Update the value
            input.value = filteredValue;

            // Restore cursor position (clamp to valid range)
            const safePosition = Math.min(newCursorPosition, filteredValue.length);
            input.setSelectionRange(safePosition, safePosition);

            // Trigger input event to notify other listeners (but mark it as non-trusted to avoid loop)
            const event = new Event('input', { bubbles: true });
            Object.defineProperty(event, 'isTrusted', { value: false });
            input.dispatchEvent(event);
        }
    }

    // Debounced version of processInputField
    const debouncedProcessInput = debounce(processInputField, 300);

    // Initialize profanity filter on an input/textarea
    function initializeProfanityFilter(element) {
        // Skip if already initialized
        if (element.dataset.profanityFilterInitialized === 'true') {
            return;
        }

        // Mark as initialized
        element.dataset.profanityFilterInitialized = 'true';

        // Add input event listener
        element.addEventListener('input', function(e) {
            // Skip if the change was made by our filter (to avoid infinite loop)
            if (e.isTrusted === false) {
                return;
            }

            debouncedProcessInput(this);
        });

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
        
        textInputs.forEach(input => {
            // Skip if it's a search field or has a specific class to exclude
            if (input.classList.contains('no-profanity-filter') || 
                input.id === 'recipient_search' ||
                input.name === 'search') {
                return;
            }

            initializeProfanityFilter(input);
        });
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

    // Start observing
    observer.observe(document.body, {
        childList: true,
        subtree: true,
    });

    // Export for manual initialization if needed
    window.ProfanityFilter = {
        initialize: initializeProfanityFilter,
        filter: filterProfanity,
    };
})();

