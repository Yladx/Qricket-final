<div class="space-y-6">
    <!-- Header Section -->
    <div class="mb-6">
        <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Basic Information</h3>
        <p class="text-gray-600 dark:text-gray-400">Fill out your basic necessary information for the event</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Event Title</label>
            <input type="text" name="title" value="{{ old('title', $eventData['title'] ?? (isset($event) && is_object($event) ? ($event->title ?? '') : '')) }}" class="w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
            <select name="category" class="w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                @php($cats = ['conference','workshop','seminar','meetup','webinar','festival','sports','music','art','technology','business','education','health','charity','other'])
                @foreach($cats as $c)
                    <option value="{{ $c }}" {{ (old('category', $eventData['category'] ?? (isset($event) && is_object($event) ? ($event->category ?? '') : ''))==$c)?'selected':'' }}>{{ ucfirst($c) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Free Event Checkbox (Info moved to sidebar) -->
    @if(isset($isFreeEventLocked) && $isFreeEventLocked === true)
        <!-- Event type is locked - show read-only display instead of checkbox -->
        <div class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 {{ (isset($isFreeEvent) && $isFreeEvent) ? 'text-emerald-600 dark:text-emerald-400' : 'text-blue-600 dark:text-blue-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    This event is set as <strong>{{ (isset($isFreeEvent) && $isFreeEvent) ? 'Free' : 'Paid' }}</strong>
                    @if(isset($eventIsPaidWithSales) && $eventIsPaidWithSales)
                        <span class="text-xs text-gray-500 dark:text-gray-400">(has ticket sales)</span>
                    @endif
                </span>
            </div>
            <span class="text-xs text-amber-600 dark:text-amber-400 font-medium ml-auto">(Cannot be changed)</span>
        </div>
        <!-- Hidden input to ensure is_free value is always submitted -->
        <input type="hidden" name="is_free" value="{{ (isset($isFreeEvent) && $isFreeEvent) ? '1' : '0' }}">
    @else
        <!-- Normal checkbox for event creation or events without restrictions -->
        <div class="flex items-center space-x-3">
            <label class="inline-flex items-center space-x-2">
                <input 
                    type="checkbox" 
                    name="is_free" 
                    value="1" 
                    id="is_free_checkbox"
                    data-locked="{{ isset($isFreeEventLocked) && $isFreeEventLocked ? 'true' : 'false' }}"
                    @if(
                        (old('is_free') !== null && old('is_free') !== '' && (bool)old('is_free')) ||
                        (isset($eventData) && is_array($eventData) && isset($eventData['is_free']) && $eventData['is_free'] !== null && $eventData['is_free'] !== '' && (bool)$eventData['is_free']) ||
                        (isset($event) && is_object($event) && isset($event->is_free) && $event->is_free !== null && (bool)$event->is_free)
                    ) checked @endif
                    class="rounded border-gray-300 text-qricket-600 focus:ring-qricket-500"
                >
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Is your event free?</span>
            </label>
            <span class="text-xs text-gray-500 dark:text-gray-400">(See sidebar for details)</span>
        </div>
    @endif

    <!-- JavaScript to lock checkbox if needed (fallback) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkbox = document.getElementById('is_free_checkbox');
            if (checkbox) {
                const isLocked = checkbox.getAttribute('data-locked') === 'true';
                if (isLocked) {
                    checkbox.disabled = true;
                    checkbox.style.opacity = '0.6';
                    checkbox.style.cursor = 'not-allowed';
                }
            }
        });
    </script>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
        <textarea id="description" name="description" rows="5" draggable="false" class="w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" oninput="this.value = this.value.replace(/\n/g, ' ').replace(/\r/g, '')" ondrop="event.preventDefault(); return false;" ondragover="event.preventDefault(); return false;" ondragstart="event.preventDefault(); return false;">{{ old('description', $eventData['description'] ?? (isset($event) && is_object($event) ? ($event->description ?? '') : '')) }}</textarea>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Note: Line breaks are automatically converted to spaces to prevent formatting issues.</p>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tags <span class="text-red-500">*</span></label>
        <div class="relative">
            <input id="tags" type="text" name="tags" value="{{ old('tags', $eventData['tags'] ?? (isset($event) && is_object($event) ? ($event->tags ?? '') : '')) }}" class="w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" placeholder="music, conference, workshop (separate with commas)" autocomplete="off" required>
            <div id="tag-suggestions" class="absolute z-50 w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg hidden max-h-60 overflow-y-auto"></div>
        </div>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Required: Add at least one tag to help categorize your event (e.g., music, conference, workshop).</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date &amp; Time</label>
            <input type="datetime-local" name="start_date" value="{{ old('start_date', isset($eventData['start_date'])?\Carbon\Carbon::parse($eventData['start_date'])->format('Y-m-d\TH:i'):(isset($event->start_date)?\Carbon\Carbon::parse($event->start_date)->format('Y-m-d\TH:i'):'')) }}" class="w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date &amp; Time</label>
            <input type="datetime-local" name="end_date" value="{{ old('end_date', isset($eventData['end_date'])?\Carbon\Carbon::parse($eventData['end_date'])->format('Y-m-d\TH:i'):(isset($event->end_date)?\Carbon\Carbon::parse($event->end_date)->format('Y-m-d\TH:i'):'')) }}" class="w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
        </div>
    </div>

    <div class="space-y-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Location</label>
            <input id="location" type="text" name="location" value="{{ old('location', $eventData['location'] ?? (isset($event) && is_object($event) ? ($event->location ?? '') : '')) }}" class="w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" placeholder="Enter event location (e.g., Municipal hall San Jose, Batangas, Philippines)">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Enter the event location address.</p>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Location URL (Optional)</label>
            <input id="location_url" type="text" name="location_url" value="{{ old('location_url', $eventData['location_url'] ?? (isset($event) && is_object($event) ? ($event->location_url ?? '') : '')) }}" class="w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" placeholder="https://maps.google.com/... (optional)">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Optional: Add a Google Maps link for directions.</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Clean description field from any existing newlines on page load
    const descriptionField = document.getElementById('description');
    if (descriptionField) {
        // Replace any existing newlines with spaces
        descriptionField.value = descriptionField.value.replace(/\r?\n/g, ' ').replace(/\r/g, '');
        
        // Ensure drag and drop is prevented (additional safety beyond HTML attributes)
        descriptionField.setAttribute('draggable', 'false');
        descriptionField.addEventListener('dragstart', function(e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });
        descriptionField.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });
        descriptionField.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });
        descriptionField.addEventListener('dragenter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });
    }
    
    const tagsInput = document.getElementById('tags');
    const categorySelect = document.querySelector('select[name="category"]');
    const suggestionsDiv = document.getElementById('tag-suggestions');
    
    let tagCategoryMap = {};
    let existingTags = [];
    let currentSuggestions = [];
    let selectedSuggestionIndex = -1;
    
    // Fetch tag-category mappings from database
    async function loadTagCategoryMappings() {
        try {
            const response = await fetch('{{ route("organizer.events.tag-category-mappings") }}', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                tagCategoryMap = await response.json();
            } else {
                console.warn('Failed to load tag-category mappings:', response.status);
                // Fallback to basic mappings if API fails
                tagCategoryMap = {
                    'music': 'music',
                    'concert': 'music',
                    'tech': 'technology',
                    'programming': 'technology',
                    'workshop': 'workshop',
                    'conference': 'conference',
                    'business': 'business',
                    'education': 'education',
                    'sports': 'sports',
                    'art': 'art',
                    'health': 'health',
                    'charity': 'charity'
                };
            }
        } catch (error) {
            console.error('Error loading tag-category mappings:', error);
            // Use fallback mappings
            tagCategoryMap = {
                'music': 'music',
                'concert': 'music',
                'tech': 'technology',
                'programming': 'technology',
                'workshop': 'workshop',
                'conference': 'conference',
                'business': 'business',
                'education': 'education',
                'sports': 'sports',
                'art': 'art',
                'health': 'health',
                'charity': 'charity'
            };
        }
    }
    
    // Fetch existing tags from database
    async function loadExistingTags() {
        try {
            const response = await fetch('{{ route("organizer.events.existing-tags") }}', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                existingTags = await response.json();
            } else {
                console.warn('Failed to load existing tags:', response.status);
                existingTags = [];
            }
        } catch (error) {
            console.error('Error loading existing tags:', error);
            existingTags = [];
        }
    }
    
    // Load data on page load
    async function loadAllData() {
        try {
            await loadTagCategoryMappings();
            await loadExistingTags();
        } catch (error) {
            console.error('❌ Error loading tag data:', error);
        }
    }
    
    loadAllData();
    
    // Get current word being typed
    function getCurrentWord() {
        const cursorPos = tagsInput.selectionStart;
        const text = tagsInput.value;
        const beforeCursor = text.substring(0, cursorPos);
        const lastComma = beforeCursor.lastIndexOf(',');
        return beforeCursor.substring(lastComma + 1).trim();
    }
    
    // Filter suggestions based on current word
    function filterSuggestions(word) {
        if (!word || word.length < 1) return [];
        
        const wordLower = word.toLowerCase();
        const filtered = existingTags.filter(tag => 
            tag.toLowerCase().includes(wordLower) && 
            !isTagAlreadyAdded(tag)
        ).slice(0, 8); // Limit to 8 suggestions
        
        return filtered;
    }
    
    // Check if tag is already added
    function isTagAlreadyAdded(tag) {
        const currentTags = tagsInput.value.split(',').map(t => t.trim().toLowerCase());
        return currentTags.includes(tag.toLowerCase());
    }
    
    // Show suggestions dropdown
    function showSuggestions(suggestions) {
        if (suggestions.length === 0) {
            hideSuggestions();
            return;
        }
        
        currentSuggestions = suggestions;
        selectedSuggestionIndex = -1;
        
        suggestionsDiv.innerHTML = suggestions.map((tag, index) => `
            <div class="suggestion-item px-3 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-600 last:border-b-0" data-index="${index}">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="font-medium">${tag}</span>
                </div>
            </div>
        `).join('');
        
        suggestionsDiv.classList.remove('hidden');
        
        // Add click event listeners
        suggestionsDiv.querySelectorAll('.suggestion-item').forEach((item, index) => {
            item.addEventListener('click', () => selectSuggestion(index));
        });
    }
    
    // Hide suggestions dropdown
    function hideSuggestions() {
        suggestionsDiv.classList.add('hidden');
        currentSuggestions = [];
        selectedSuggestionIndex = -1;
    }
    
    // Select a suggestion
    function selectSuggestion(index) {
        if (index < 0 || index >= currentSuggestions.length) return;
        
        const selectedTag = currentSuggestions[index];
        const cursorPos = tagsInput.selectionStart;
        const text = tagsInput.value;
        const beforeCursor = text.substring(0, cursorPos);
        const afterCursor = text.substring(cursorPos);
        const lastComma = beforeCursor.lastIndexOf(',');
        
        // Replace current word with selected tag
        const beforeWord = beforeCursor.substring(0, lastComma + 1);
        const newText = beforeWord + selectedTag + ', ' + afterCursor;
        
        tagsInput.value = newText;
        
        // Position cursor after the selected tag
        const newCursorPos = beforeWord.length + selectedTag.length + 2;
        tagsInput.setSelectionRange(newCursorPos, newCursorPos);
        
        hideSuggestions();
        tagsInput.focus();
        
        // Trigger category suggestion
        setTimeout(suggestCategory, 100);
    }
    
    // Handle keyboard navigation
    function handleKeyDown(e) {
        if (suggestionsDiv.classList.contains('hidden')) return;
        
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                selectedSuggestionIndex = Math.min(selectedSuggestionIndex + 1, currentSuggestions.length - 1);
                updateSuggestionHighlight();
                break;
            case 'ArrowUp':
                e.preventDefault();
                selectedSuggestionIndex = Math.max(selectedSuggestionIndex - 1, -1);
                updateSuggestionHighlight();
                break;
            case 'Enter':
                e.preventDefault();
                if (selectedSuggestionIndex >= 0) {
                    selectSuggestion(selectedSuggestionIndex);
                }
                break;
            case 'Escape':
                hideSuggestions();
                break;
        }
    }
    
    // Update suggestion highlight
    function updateSuggestionHighlight() {
        suggestionsDiv.querySelectorAll('.suggestion-item').forEach((item, index) => {
            if (index === selectedSuggestionIndex) {
                item.classList.add('bg-blue-100', 'dark:bg-blue-900');
            } else {
                item.classList.remove('bg-blue-100', 'dark:bg-blue-900');
            }
        });
    }
    
    // Function to suggest category based on tags
    function suggestCategory() {
        if (!tagsInput.value.trim() || Object.keys(tagCategoryMap).length === 0) return;
        
        const tags = tagsInput.value.toLowerCase().split(',').map(tag => tag.trim());
        let suggestedCategory = null;
        let maxMatches = 0;
        
        // Count matches for each category
        const categoryMatches = {};
        
        tags.forEach(tag => {
            if (tagCategoryMap[tag]) {
                const category = tagCategoryMap[tag];
                categoryMatches[category] = (categoryMatches[category] || 0) + 1;
                
                if (categoryMatches[category] > maxMatches) {
                    maxMatches = categoryMatches[category];
                    suggestedCategory = category;
                }
            }
        });
        
        // If we found a suggestion and no category is currently selected
        if (suggestedCategory && !categorySelect.value) {
            categorySelect.value = suggestedCategory;
            
            // Add visual feedback
            categorySelect.style.backgroundColor = '#f0f9ff';
            categorySelect.style.borderColor = '#3b82f6';
                        
                        setTimeout(() => {
                categorySelect.style.backgroundColor = '';
                categorySelect.style.borderColor = '';
                        }, 2000);
            
            // Show suggestion message
            showSuggestionMessage(`Suggested category: ${suggestedCategory.charAt(0).toUpperCase() + suggestedCategory.slice(1)}`);
        }
    }
    
    // Function to show suggestion message
    function showSuggestionMessage(message) {
        // Remove existing suggestion message
        const existingMessage = document.querySelector('.category-suggestion');
        if (existingMessage) {
            existingMessage.remove();
        }
        
        // Create new suggestion message
        const suggestionDiv = document.createElement('div');
        suggestionDiv.className = 'category-suggestion mt-2 p-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md text-sm text-blue-700 dark:text-blue-300';
        suggestionDiv.innerHTML = `
            <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                ${message}
            </div>
        `;
        
        // Insert after category select
        categorySelect.parentNode.insertBefore(suggestionDiv, categorySelect.parentNode.children[categorySelect.parentNode.children.length - 1]);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (suggestionDiv.parentNode) {
                suggestionDiv.remove();
            }
        }, 5000);
    }
    
    // Event listeners
    tagsInput.addEventListener('input', function() {
        const currentWord = getCurrentWord();
        const suggestions = filterSuggestions(currentWord);
        
        if (suggestions.length > 0) {
            showSuggestions(suggestions);
        } else {
            hideSuggestions();
        }
        
        // Debounce category suggestion
        clearTimeout(tagsInput.suggestionTimeout);
        tagsInput.suggestionTimeout = setTimeout(suggestCategory, 500);
    });
    
    tagsInput.addEventListener('keydown', handleKeyDown);
    
    tagsInput.addEventListener('blur', function() {
        // Delay hiding to allow clicks on suggestions
        setTimeout(hideSuggestions, 200);
    });
    
    tagsInput.addEventListener('focus', function() {
        const currentWord = getCurrentWord();
        const suggestions = filterSuggestions(currentWord);
        if (suggestions.length > 0) {
            showSuggestions(suggestions);
        }
    });
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!tagsInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
            hideSuggestions();
        }
    });

    // Free Event checkbox SweetAlert confirmation
    const freeEventCheckbox = document.getElementById('is_free_checkbox');
    if (freeEventCheckbox) {
        // Check if checkbox is disabled (locked) - if so, prevent any changes
        if (freeEventCheckbox.disabled) {
            // Prevent any interaction with disabled checkbox
            freeEventCheckbox.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            });
            
            freeEventCheckbox.addEventListener('change', function(e) {
                e.preventDefault();
                e.stopPropagation();
                // Force it to stay checked
                this.checked = true;
                return false;
            });
        } else {
            // Only add confirmation dialog for enabled checkboxes (new events or paid events)
            // Store the previous state before any click
            let previousCheckedState = freeEventCheckbox.checked;
            
            freeEventCheckbox.addEventListener('mousedown', function(e) {
                // Capture state before the click toggles it
                previousCheckedState = this.checked;
            });
            
            freeEventCheckbox.addEventListener('click', function(e) {
                // If checkbox is now checked (meaning it was just checked from unchecked)
                if (this.checked && !previousCheckedState) {
                    e.preventDefault();
                    e.stopPropagation();
                    // Revert the checkbox state immediately
                    this.checked = false;
                    
                    // Show SweetAlert confirmation
                    const showSweetAlert = () => {
                        if (window.Swal && typeof window.Swal.fire === 'function') {
                            window.Swal.fire({
                                title: 'Free Event Confirmation',
                                html: 'You are creating this event as <strong>free</strong>. This means:<br><br>' +
                                      '• All ticket prices will be set to <strong>0</strong> (free)<br>' +
                                      '• You <strong>cannot</strong> set a price for your tickets<br>' +
                                      '• Tickets will be free for all attendees<br><br>' +
                                      '<strong>Note:</strong> Once created as free, this cannot be changed later.',
                                icon: 'info',
                                showCancelButton: true,
                                confirmButtonText: 'Yes, Continue',
                                cancelButtonText: 'No, Cancel',
                                confirmButtonColor: '#059669',
                                cancelButtonColor: '#6b7280',
                                reverseButtons: true
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // User confirmed - check the checkbox
                                    freeEventCheckbox.checked = true;
                                    // Trigger change event manually
                                    freeEventCheckbox.dispatchEvent(new Event('change', { bubbles: true }));
                                } else {
                                    // User cancelled - keep checkbox unchecked
                                    freeEventCheckbox.checked = false;
                                }
                            });
                        } else {
                            // Fallback if SweetAlert is not loaded - use native confirm
                            const confirmed = confirm('You are creating this event as free. This means all ticket prices will be set to 0 and you cannot set a price for your tickets. Once created as free, this cannot be changed later. Do you want to continue?');
                            if (confirmed) {
                                freeEventCheckbox.checked = true;
                                freeEventCheckbox.dispatchEvent(new Event('change', { bubbles: true }));
                            } else {
                                freeEventCheckbox.checked = false;
                            }
                        }
                    };

                    // Check if SweetAlert is loaded, if not wait a bit
                    if (window.Swal && typeof window.Swal.fire === 'function') {
                        showSweetAlert();
                    } else {
                        // Wait for SweetAlert to load (it's loaded in step1.blade.php and edit pages)
                        setTimeout(() => {
                            showSweetAlert();
                        }, 100);
                    }
                }
                // If unchecking (was checked), allow it without SweetAlert - do nothing
            });
        }
    }
});
</script>

