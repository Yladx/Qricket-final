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
                // Silently fail - return original text if API fails
                return text;
            }

            const data = await response.json();
            if (data.success && data.filtered !== undefined) {
                return data.filtered;
            }

            return text;
        } catch (error) {
            // Silently fail - return original text on error
            return text;
        }
    }

    // Track if we're currently filtering to prevent loops
    let isFiltering = false;
    
    // Track last value to detect space-only changes
    const lastValues = new WeakMap();
    
    // Track pending debounced calls so we can cancel them
    const pendingDebounces = new WeakMap();

    // Process input field
    async function processInputField(input) {
        // Prevent infinite loops
        if (isFiltering) {
            return;
        }

        const originalValue = input.value;
        if (!originalValue || originalValue.trim().length === 0) {
            lastValues.set(input, originalValue);
            return;
        }

        const cursorPosition = input.selectionStart;
        const lastValue = lastValues.get(input) || '';
        
        // Check if cursor is right after a space - if so, skip filtering
        if (cursorPosition > 0) {
            const charBeforeCursor = originalValue[cursorPosition - 1];
            if (charBeforeCursor === ' ') {
                // Cursor is right after a space - user might still be typing
                // Only filter if there's actual text after the space
                const textAfterCursor = originalValue.substring(cursorPosition);
                if (textAfterCursor.trim().length === 0) {
                    lastValues.set(input, originalValue);
                    return;
                }
            }
        }
        
        // Additional check: skip if only spaces were added
        if (originalValue.length > lastValue.length) {
            const addedText = originalValue.slice(lastValue.length);
            if (addedText.trim().length === 0 && addedText.length > 0) {
                lastValues.set(input, originalValue);
                // Cancel any pending debounced calls
                const pending = pendingDebounces.get(input);
                if (pending) {
                    clearTimeout(pending);
                    pendingDebounces.delete(input);
                }
                return;
            }
        }
        
        // Skip if text was deleted (user is editing)
        if (originalValue.length < lastValue.length) {
            lastValues.set(input, originalValue);
            // Cancel any pending debounced calls
            const pending = pendingDebounces.get(input);
            if (pending) {
                clearTimeout(pending);
                pendingDebounces.delete(input);
            }
            return;
        }

        try {
            isFiltering = true;

            // Filter the text
            const filteredValue = await filterProfanity(originalValue);

            // Only update if the value changed
            if (filteredValue !== originalValue) {
                // Calculate new cursor position more accurately
                const textBeforeCursor = originalValue.substring(0, cursorPosition);
                const filteredBeforeCursor = await filterProfanity(textBeforeCursor);
                
                // Calculate how many characters were removed before the cursor
                const charsRemovedBefore = textBeforeCursor.length - filteredBeforeCursor.length;
                const newCursorPosition = cursorPosition - charsRemovedBefore;

                // Update the value
                input.value = filteredValue;

                // Restore cursor position (clamp to valid range)
                const safePosition = Math.max(0, Math.min(newCursorPosition, filteredValue.length));
                input.setSelectionRange(safePosition, safePosition);

                // Trigger input event for form validation (but mark as filtered to avoid loop)
                const inputEvent = new Event('input', { bubbles: true });
                Object.defineProperty(inputEvent, 'filteredByProfanityFilter', { value: true, writable: false });
                input.dispatchEvent(inputEvent);
            }
            
            // Update last value
            lastValues.set(input, input.value);
        } catch (error) {
            // Silently handle errors
        } finally {
            isFiltering = false;
        }
    }

    // Create a debounced function that tracks pending calls per input
    function createDebouncedProcessInput(input) {
        let timeoutId = null;
        
        return function() {
            // Clear any existing timeout for this input
            if (timeoutId) {
                clearTimeout(timeoutId);
            }
            
            // Set new timeout
            timeoutId = setTimeout(() => {
                pendingDebounces.delete(input);
                processInputField(input);
            }, 300);
            
            // Store the timeout ID so we can cancel it
            pendingDebounces.set(input, timeoutId);
        };
    }

    // Initialize profanity filter on an input/textarea
    function initializeProfanityFilter(element) {
        // Skip if already initialized
        if (element.dataset.profanityFilterInitialized === 'true') {
            return;
        }

        // Mark as initialized
        element.dataset.profanityFilterInitialized = 'true';
        
        // Create a debounced function for this specific input element
        const debouncedProcessInput = createDebouncedProcessInput(element);

        // Add input event listener - use 'input' for real-time detection
        element.addEventListener('input', function(e) {
            // Skip if this event was triggered by our filter (to avoid loops)
            if (e.filteredByProfanityFilter) {
                return;
            }
            
            // Skip if we're currently filtering
            if (isFiltering) {
                return;
            }

            // Check if user just typed a space - skip filtering completely for spaces
            const currentValue = this.value;
            const lastValue = lastValues.get(this) || '';
            
            // If the change is just adding a space or spaces, skip filtering entirely
            if (currentValue.length > lastValue.length) {
                const addedText = currentValue.slice(lastValue.length);
                // If only spaces were added, don't filter at all and cancel pending calls
                if (addedText.trim().length === 0 && addedText.length > 0) {
                    lastValues.set(this, currentValue);
                    // Cancel any pending debounced calls for this input
                    const pending = pendingDebounces.get(this);
                    if (pending) {
                        clearTimeout(pending);
                        pendingDebounces.delete(this);
                    }
                    return; // Don't filter when only spaces are added
                }
            }
            
            // If user deleted text, also skip filtering (they're editing, let them finish)
            if (currentValue.length < lastValue.length) {
                lastValues.set(this, currentValue);
                // Cancel any pending debounced calls
                const pending = pendingDebounces.get(this);
                if (pending) {
                    clearTimeout(pending);
                    pendingDebounces.delete(this);
                }
                return;
            }

            // Only filter when user is typing actual characters (not spaces, not deleting)
            debouncedProcessInput();
        }, { passive: true });

        // Also check on paste (with longer delay to allow paste to complete)
        element.addEventListener('paste', function(e) {
            setTimeout(() => {
                if (!isFiltering) {
                    debouncedProcessInput();
                }
            }, 100);
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
