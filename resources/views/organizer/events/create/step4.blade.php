<x-app-layout>
    <x-slot name="title">Create Event - Step 4: Certificate Template</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-4">
            {{ __('Create Event - Step 4: Certificate Template') }}
        </h2>

        @include('organizer.events.components.progress-bar', ['currentStep' => 4])
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <form method="POST" action="{{ route('organizer.events.certificate.store') }}?id={{ $eventId }}">
                        @csrf
                <input type="hidden" name="skip_certificate" id="skip_certificate_hidden" value="0">
                <input type="hidden" id="certificate_data_json" name="certificate_data_json" value='@json($certificateData ?? [])'>
                
                <!-- Top: Form -->
                <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="text-sm text-gray-600 dark:text-gray-300">You can set up a certificate now or skip it and add later.</div>
                    <label class="inline-flex items-center space-x-2 text-sm">
                        <input type="checkbox" id="skip_certificate_step4" class="rounded border-gray-300 text-emerald-600">
                        <span class="text-gray-700 dark:text-gray-300">Skip certificate for this event</span>
                    </label>
                </div>
                @php($cd = $certificateData ?? [])
                @php(
                    $fallbackWhen = (isset($event) && ($event->start_date ?? null))
                        ? ('held on ' . \Carbon\Carbon::parse($event->start_date)->format('F j, Y \a\t g:i A') .
                           (($event->location ?? null) ? ' at ' . $event->location : '') . '.')
                        : ''
                )
                @php(
                    $fallbackGiven = (isset($event) && ($event->start_date ?? null))
                        ? ('Given this ' . \Carbon\Carbon::parse($event->start_date)->format('jS') . ' day of '
                           . \Carbon\Carbon::parse($event->start_date)->format('F, Y') . '.')
                        : ''
                )
                <div id="certificate-form-container">
                    @include('organizer.events.forms.certificate', ['certificateData' => $certificateData, 'event' => $event])
                </div>
            </div>
            <!-- Bottom: Live Preview with collapsible toggle -->
            <details id="cert_section" class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden" open>
                <summary class="cursor-pointer select-none px-4 py-3 text-sm font-medium flex items-center justify-between">
                    <span>Certificate Preview</span>
                    <div class="flex items-center gap-3">
            <a href="{{ route('organizer.events.create.certificate.print', ['id' => $eventId]) }}" target="_blank" rel="noopener" class="px-3 py-1.5 text-xs rounded bg-qricket-500 text-white hover:bg-qricket-600">Print A4</a>
                        <span class="text-xs text-gray-500">Click to collapse/expand</span>
                    </div>
                </summary>
                <div class="p-4">
                    <div id="cert_preview_wrapper">
                        @include('organizer.events.preview.certificate-template', [
                            'certificateData' => $certificateData, 
                            'event' => $event ?? (object)['id' => $eventId], 
                            'fallbackWhen' => $fallbackWhen, 
                            'fallbackGiven' => $fallbackGiven
                        ])
                    </div>
                </div>
            </details>

            <style>
                /* Print only the certificate preview */
                @media print {
                    body * { visibility: hidden !important; }
                    #cert_preview_wrapper, #cert_preview_wrapper * { visibility: visible !important; }
                    #cert_preview_wrapper { position: absolute; left: 0; top: 0; width: 100%; }
                }
            </style>

                        <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('organizer.events.create.ticket') }}?id={{ $eventId }}" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-medium text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-qricket-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Back to Tickets
                            </a>

                            <div class="flex items-center space-x-3">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-qricket-500 text-white rounded-md font-medium text-xs hover:bg-qricket-600 focus:outline-none focus:ring-2 focus:ring-qricket-500">
                                Next: Staff
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                </svg>
                            </button>
                            </div>
                        </div>
                    </form>
        </div>
    </div>

    <script>
        // Prevent any inline handlers from executing by blocking their execution immediately
        // This runs as soon as the script tag is parsed, before any inline handlers can fire
        (function preventInlineHandlers() {
            // Override confirm to prevent dialogs on load (temporary, will be restored after initialization)
            const originalConfirm = window.confirm;
            let confirmBlocked = true;
            window.confirm = function(message) {
                if (confirmBlocked && message && message.includes('remove')) {
                    console.warn('Blocked confirm dialog on page load:', message);
                    return false; // Block the confirmation
                }
                return originalConfirm.call(window, message);
            };
            
            // Restore confirm after a short delay (once initialization is complete)
            setTimeout(function() {
                confirmBlocked = false;
                window.confirm = originalConfirm;
            }, 2000); // 2 seconds should be enough for initialization
        })();
        
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const eventId = new URLSearchParams(location.search).get('id');
        const existingCertificateData = @json($certificateData ?? []);

        function syncPreview(idInput, idPreview){
            const elI = document.getElementById(idInput);
            const elP = document.getElementById(idPreview);
            if (!elI || !elP) return;
            elI.addEventListener('input', function() { 
                if (elP) elP.textContent = elI.value; 
            });
        }
        
        // Sync preview for basic fields
        (function() {
            try {
                var fieldsToSync = ['org_line1','org_line2','org_school','recipient_name','event_title','event_when','event_given'];
                fieldsToSync.forEach(function(k) {
                    if (typeof syncPreview === 'function') {
                        syncPreview(k + '_input', k);
                    }
                });
            } catch(e) {
                console.error('Error syncing preview fields:', e);
            }
        })();
        
        // Sync preview for dynamic signatory fields
        function syncAllSignatories() {
            try {
                var signatoryInputs = document.querySelectorAll('input[name^="sig_"][name$="_name"], input[name^="sig_"][name$="_title"]');
                for (var si = 0; si < signatoryInputs.length; si++) {
                    var input = signatoryInputs[si];
                    if (input && input.name) {
                        var fieldName = input.name;
                        var previewId = fieldName.replace('_input', '').replace('sig_', 'sig_');
                        if (typeof syncPreview === 'function') {
                            syncPreview(fieldName, previewId);
                        }
                    }
                }
            } catch(e) {
                console.error('Error in syncAllSignatories:', e);
            }
        }
        
        // Update preview when signatories change
        function updateSignatoryPreview() {
            try {
                // Remove all existing signatory previews
                var previewElements = document.querySelectorAll('[id^="sig_"][id$="_name"], [id^="sig_"][id$="_title"]');
                for (var pe = 0; pe < previewElements.length; pe++) {
                    var el = previewElements[pe];
                    if (el) {
                        el.textContent = '';
                    }
                }
                
                // Add current signatories to preview
                var signatoryItems = document.querySelectorAll('.signatory-item');
                for (var idx = 0; idx < signatoryItems.length; idx++) {
                    var item = signatoryItems[idx];
                    var signatoryNum = idx + 1;
                    var nameInput = item.querySelector('input[name="sig_' + signatoryNum + '_name"]');
                    var titleInput = item.querySelector('input[name="sig_' + signatoryNum + '_title"]');
                    
                    if (nameInput) {
                        var namePreview = document.getElementById('sig_' + signatoryNum + '_name');
                        if (namePreview) {
                            namePreview.textContent = nameInput.value || ('Signatory ' + signatoryNum);
                        }
                    }
                    
                    if (titleInput) {
                        var titlePreview = document.getElementById('sig_' + signatoryNum + '_title');
                        if (titlePreview) {
                            titlePreview.textContent = titleInput.value || 'Signature';
                        }
                    }
                }
            } catch(e) {
                console.error('Error in updateSignatoryPreview:', e);
            }
        }
        
        // Initial sync
        syncAllSignatories();
        updateSignatoryPreview();
        
        // Define autosave functions early so they can be used by addSignatory/removeSignatory
        const debounce = (fn, d=600)=>{ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), d); }; };
        const statusEl = document.getElementById('cert_autosave_status');
        
        // Function to show autosave indicator (like in autosave.js)
        function showAutosaveIndicator(status) {
            // Remove existing indicator
            const existingIndicator = document.getElementById('autosave-indicator');
            if (existingIndicator) {
                existingIndicator.remove();
            }
            
            // Create new indicator
            const indicator = document.createElement('div');
            indicator.id = 'autosave-indicator';
            indicator.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-md text-sm font-medium transition-all duration-300 shadow-lg ${
                status === 'success' 
                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                    : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
            }`;
            indicator.textContent = status === 'success' ? '✓ Auto-saved' : '✗ Save failed';
            
            document.body.appendChild(indicator);
            
            // Remove after 3 seconds
            setTimeout(() => {
                if (indicator && indicator.parentNode) {
                    indicator.remove();
                }
            }, 3000);
        }
        
        // We'll define autosaveCert and autosaveDebounced after collectPayload is defined
        let autosaveDebounced = null;
        
        // Define addSignatory function for step4
        let signatoryCount = document.querySelectorAll('.signatory-item').length || 1;
        
        window.addSignatory = function addSignatory() {
            console.log('addSignatory called from step4, current signatoryCount:', signatoryCount);
            
            if (signatoryCount >= 4) {
                if (typeof Swal !== 'undefined' && window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Maximum Signatories',
                        text: 'Maximum of 4 signatories allowed',
                        confirmButtonColor: '#10b981'
                    });
                } else {
                    alert('Maximum of 4 signatories allowed');
                }
                return;
            }
            
            signatoryCount++;
            console.log('New signatoryCount:', signatoryCount);
            
            const container = document.getElementById('signatory-details-container');
            if (!container) {
                console.error('Container not found: signatory-details-container');
                alert('Error: Could not find signatory container');
                signatoryCount--; // Revert count
                return;
            }
            
            console.log('Container found, creating new signatory...');
            const newSignatory = document.createElement('div');
            newSignatory.className = 'signatory-item border border-gray-200 dark:border-gray-700 rounded-lg p-3';
            newSignatory.setAttribute('data-signatory', signatoryCount);
            
            // Build signatory HTML using string concatenation
            const sigNum = signatoryCount;
            newSignatory.innerHTML = 
                '<div class="flex items-center justify-between mb-2">' +
                    '<h4 class="text-xs font-medium text-gray-700 dark:text-gray-300">Signatory ' + sigNum + '</h4>' +
                    '<button type="button" class="remove-signatory-btn text-red-500 hover:text-red-700 text-xs" data-signatory="' + sigNum + '">' +
                        '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>' +
                        '</svg>' +
                    '</button>' +
                '</div>' +
                '<div class="grid grid-cols-1 md:grid-cols-2 gap-3">' +
                    '<div>' +
                        '<label class="block text-xs text-gray-600 dark:text-gray-300 mb-0.5">Name</label>' +
                        '<input id="sig_' + sigNum + '_name_input" name="sig_' + sigNum + '_name" type="text" maxlength="36" class="w-full text-xs bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md py-1.5 px-2" placeholder="Max 36 characters">' +
                    '</div>' +
                    '<div>' +
                        '<label class="block text-xs text-gray-600 dark:text-gray-300 mb-0.5">Title</label>' +
                        '<input id="sig_' + sigNum + '_title_input" name="sig_' + sigNum + '_title" type="text" maxlength="28" class="w-full text-xs bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md py-1.5 px-2" placeholder="Max 28 characters">' +
                    '</div>' +
                '</div>' +
                '<div class="mt-3">' +
                    '<label class="block text-xs text-gray-600 dark:text-gray-300 mb-0.5">Signature Image</label>' +
                    '<label class="flex items-center justify-between gap-2 border border-dashed border-gray-300 dark:border-gray-700 rounded-md p-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800">' +
                        '<div class="flex items-center gap-2">' +
                            '<div class="w-8 h-8 flex items-center justify-center rounded bg-qricket-50 text-qricket-600 dark:bg-gray-800 dark:text-gray-200">' +
                                '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/></svg>' +
                            '</div>' +
                            '<div>' +
                                '<div class="text-xs font-medium">Signature ' + sigNum + '</div>' +
                                '<div class="text-xs text-gray-500 dark:text-gray-400">PNG/JPG/WebP, up to 2MB</div>' +
                                '<div class="mt-0.5 flex items-center gap-1">' +
                                    '<img id="sig_' + sigNum + '_preview" src="" class="w-5 h-5 rounded object-contain border border-gray-200 dark:border-gray-700 hidden" alt="Signature ' + sigNum + ' preview">' +
                                    '<span id="sig_' + sigNum + '_status" class="text-xs text-gray-500 dark:text-gray-400">No file</span>' +
                                    '<button type="button" id="sig_' + sigNum + '_remove_btn" class="ml-1 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 hidden" title="Remove signature">' +
                                        '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>' +
                                        '</svg>' +
                                    '</button>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                        '<input type="file" id="sig_' + sigNum + '_file" accept="image/*" class="hidden">' +
                        '<span class="px-2 py-0.5 text-xs rounded bg-qricket-500 text-white">Choose</span>' +
                    '</label>' +
                '</div>';
            
            try {
                container.appendChild(newSignatory);
                console.log('Successfully appended signatory', sigNum);
                
                // Attach event listeners after element is added to DOM (prevents onclick from firing on load)
                const fileInput = document.getElementById(`sig_${sigNum}_file`);
                const removeBtn = newSignatory.querySelector('.remove-signatory-btn');
                
                if (fileInput) {
                    // Clone to remove any existing handlers
                    const newFileInput = fileInput.cloneNode(true);
                    fileInput.parentNode.replaceChild(newFileInput, fileInput);
                    newFileInput.addEventListener('change', function(e) {
                        e.stopPropagation();
                        if (typeof window.uploadSignature === 'function') {
                            window.uploadSignature(sigNum);
                        }
                    });
                }
                
                if (removeBtn) {
                    // Remove any onclick attributes and attach event listener
                    removeBtn.removeAttribute('onclick');
                    removeBtn.setAttribute('data-signatory', sigNum);
                    removeBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (typeof window.removeSignatory === 'function') {
                            window.removeSignatory(sigNum);
                        }
                    });
                }
            } catch (e) {
                console.error('Error appending signatory:', e);
                alert('Error adding signatory: ' + e.message);
                signatoryCount--; // Revert count
                return;
            }
            
            // Update add button state
            const addBtn = document.getElementById('add-signatory-btn');
            if (addBtn) {
                if (signatoryCount >= 4) {
                    addBtn.disabled = true;
                    addBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }
            
            // Show remove buttons for all signatories
            var removeBtns = document.querySelectorAll('.remove-signatory-btn');
            for (var rb = 0; rb < removeBtns.length; rb++) {
                var btn = removeBtns[rb];
                if (btn) {
                    btn.classList.remove('hidden');
                }
            }
            
            // Sync with preview
            updateSignatoryPreview();
            
            // Trigger autosave
            if (autosaveDebounced && typeof autosaveDebounced === 'function') {
                autosaveDebounced();
            }
            
            console.log('addSignatory completed successfully');
        };
        
        // Define removeSignatory function
        window.removeSignatory = function removeSignatory(signatoryNum) {
            console.log('removeSignatory called for signatory:', signatoryNum, 'current count:', signatoryCount);
            
            if (signatoryCount <= 1) {
                if (typeof Swal !== 'undefined' && window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Cannot Remove',
                        text: 'At least one signatory is required',
                        confirmButtonColor: '#10b981'
                    });
                } else {
                    alert('At least one signatory is required');
                }
                return;
            }
            
            const signatoryItem = document.querySelector(`[data-signatory="${signatoryNum}"]`);
            if (!signatoryItem) {
                console.warn('Signatory item not found:', signatoryNum);
                return;
            }
            
            signatoryItem.remove();
            signatoryCount--;
            
            // Update add button state
            const addBtn = document.getElementById('add-signatory-btn');
            if (addBtn) {
                if (signatoryCount < 4) {
                    addBtn.disabled = false;
                    addBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }
            
            // Hide remove buttons if only one signatory left
            if (signatoryCount === 1) {
                var hideBtns = document.querySelectorAll('.remove-signatory-btn');
                for (var hb = 0; hb < hideBtns.length; hb++) {
                    var btn = hideBtns[hb];
                    if (btn) {
                        btn.classList.add('hidden');
                    }
                }
            }
            
            // Renumber remaining signatories
            var remainingSignatories = document.querySelectorAll('.signatory-item');
            for (var remIdx = 0; remIdx < remainingSignatories.length; remIdx++) {
                var item = remainingSignatories[remIdx];
                var newNum = remIdx + 1;
                item.setAttribute('data-signatory', newNum);
                
                // Update all input IDs and names
                var nameInput = item.querySelector('input[name^="sig_"][name$="_name"]');
                var titleInput = item.querySelector('input[name^="sig_"][name$="_title"]');
                var fileInput = item.querySelector('input[type="file"][id^="sig_"]');
                var statusEl = item.querySelector('[id^="sig_"][id$="_status"]');
                
                if (nameInput) {
                    nameInput.id = 'sig_' + newNum + '_name_input';
                    nameInput.name = 'sig_' + newNum + '_name';
                }
                if (titleInput) {
                    titleInput.id = 'sig_' + newNum + '_title_input';
                    titleInput.name = 'sig_' + newNum + '_title';
                }
                if (fileInput) {
                    fileInput.id = 'sig_' + newNum + '_file';
                    fileInput.name = 'sig_' + newNum + '_file';
                    // Remove old event listeners and add new one
                    var newFileInput = fileInput.cloneNode(true);
                    fileInput.parentNode.replaceChild(newFileInput, fileInput);
                    (function(sigNum) {
                        newFileInput.addEventListener('change', function() {
                            if (typeof window.uploadSignature === 'function') {
                                window.uploadSignature(sigNum);
                            }
                        });
                    })(newNum);
                }
                if (statusEl) {
                    statusEl.id = 'sig_' + newNum + '_status';
                }
                var previewEl = item.querySelector('[id^="sig_"][id$="_preview"]');
                if (previewEl) {
                    previewEl.id = 'sig_' + newNum + '_preview';
                }
                var sigRemoveBtn = document.getElementById('sig_' + newNum + '_remove_btn') || item.querySelector('[id^="sig_"][id$="_remove_btn"]');
                if (sigRemoveBtn) {
                    sigRemoveBtn.id = 'sig_' + newNum + '_remove_btn';
                    // Remove old event listeners and add new one
                    var newRemoveBtn = sigRemoveBtn.cloneNode(true);
                    sigRemoveBtn.parentNode.replaceChild(newRemoveBtn, sigRemoveBtn);
                    (function(sigNum) {
                        newRemoveBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            if (typeof window.removeSignature === 'function') {
                                window.removeSignature(sigNum);
                            }
                        });
                    })(newNum);
                }
                
                // Update remove button data attribute and event listener
                var removeBtn = item.querySelector('.remove-signatory-btn');
                if (removeBtn) {
                    removeBtn.removeAttribute('onclick');
                    removeBtn.setAttribute('data-signatory', newNum);
                    // Clone and replace to ensure clean event listener
                    var newRemoveBtn = removeBtn.cloneNode(true);
                    removeBtn.parentNode.replaceChild(newRemoveBtn, removeBtn);
                    (function(sigNum) {
                        newRemoveBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            if (typeof window.removeSignatory === 'function') {
                                window.removeSignatory(sigNum);
                            }
                        });
                    })(newNum);
                }
                
                // Update heading
                var heading = item.querySelector('h4');
                if (heading) {
                    heading.textContent = 'Signatory ' + newNum;
                }
            }
            
            // Update preview
            updateSignatoryPreview();
            
            // Trigger autosave
            if (autosaveDebounced && typeof autosaveDebounced === 'function') {
                autosaveDebounced();
            }
            
            console.log('removeSignatory completed successfully');
        };
        
        // Attach event listener to add button
        document.addEventListener('DOMContentLoaded', function() {
            const addBtn = document.getElementById('add-signatory-btn');
            if (addBtn) {
                addBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Add button clicked from step4');
                    if (typeof window.addSignatory === 'function') {
                        window.addSignatory();
                    } else {
                        console.error('window.addSignatory is not a function');
                    }
                });
                console.log('Event listener attached to add button in step4');
            }
        });
        
        // Also try immediately if DOM is ready
        if (document.readyState !== 'loading') {
            const addBtn = document.getElementById('add-signatory-btn');
            if (addBtn) {
                addBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Add button clicked from step4 (immediate)');
                    if (typeof window.addSignatory === 'function') {
                        window.addSignatory();
                    } else {
                        console.error('window.addSignatory is not a function');
                    }
                });
                console.log('Event listener attached to add button in step4 (immediate)');
            }
        }
        
        // Apply colors on load
        (function applyInitialColors(){
            const payload = {
                theme_primary: document.getElementById('theme_primary')?.value || existingCertificateData.theme_primary || '#0b2f6b',
                theme_secondary: document.getElementById('theme_secondary')?.value || existingCertificateData.theme_secondary || '#092557',
                org_line1: document.getElementById('org_line1_input')?.value || existingCertificateData.org_line1 || '',
                org_line2: document.getElementById('org_line2_input')?.value || existingCertificateData.org_line2 || '',
                org_line3: document.getElementById('org_line3_input')?.value || existingCertificateData.org_line3 || '',
                org_school: document.getElementById('org_school_input')?.value || existingCertificateData.org_school || '',
                recipient_name: document.getElementById('recipient_name_input')?.value || existingCertificateData.recipient_name || ''
            };
            refreshPreview(payload);
        })();

        // Build missing signatory rows from existing data
        (function inflateSignatoriesFromExisting(){
            if (!existingCertificateData) return;
            
            // Wait for window.addSignatory to be available
            function tryLoad() {
                if (typeof window.addSignatory !== 'function') {
                    setTimeout(tryLoad, 100);
                    return;
                }
                
                // Get current count from DOM
                const currentSignatories = document.querySelectorAll('.signatory-item');
                const currentCount = currentSignatories.length;
                
                // Calculate signatory_count from data - check for sig_1 through sig_4
                let calculatedSignatoryCount = 1;
                for (let i = 1; i <= 4; i++) {
                    if (existingCertificateData[`sig_${i}_name`] || 
                        existingCertificateData[`sig_${i}_title`] || 
                        existingCertificateData[`sig_${i}_signature`]) {
                        calculatedSignatoryCount = Math.max(calculatedSignatoryCount, i);
                    }
                }
                
                // Use the calculated count or fallback to signatory_count if available
                const savedSignatoryCount = existingCertificateData.signatory_count || calculatedSignatoryCount;
                
                // Also check if we have data for signatories 2-4 even if count isn't set
                let maxSignatoryWithData = savedSignatoryCount;
                for (let i = 2; i <= 4; i++) {
                    if (existingCertificateData[`sig_${i}_name`] || existingCertificateData[`sig_${i}_title`] || existingCertificateData[`sig_${i}_signature`]) {
                        maxSignatoryWithData = Math.max(maxSignatoryWithData, i);
                    }
                }
                
                console.log('Loading signatories:', {
                    currentCount: currentCount,
                    savedCount: savedSignatoryCount,
                    calculatedCount: calculatedSignatoryCount,
                    maxSignatoryWithData: maxSignatoryWithData,
                    existingData: Object.keys(existingCertificateData).filter(k => k.startsWith('sig_'))
                });
                
                // If we need to add more signatories (use maxSignatoryWithData instead of savedSignatoryCount)
                if (maxSignatoryWithData > currentCount) {
                    console.log('Adding', maxSignatoryWithData - currentCount, 'missing signatories');
                    // Add missing signatories synchronously
                    for (let i = currentCount + 1; i <= maxSignatoryWithData && i <= 4; i++) {
                        console.log('Adding signatory', i);
                        window.addSignatory();
                    }
                    
                    // Wait longer for DOM to fully update, then populate all values
                    // Use multiple attempts to ensure DOM is ready
                    setTimeout(() => {
                        console.log('First populate attempt after', 300, 'ms');
                        populateSignatoryValues();
                    }, 300);
                    
                    // Also try again after a longer delay in case first attempt fails
                    setTimeout(() => {
                        console.log('Second populate attempt after', 600, 'ms');
                        populateSignatoryValues();
                    }, 600);
                    
                    // Third attempt as backup
                    setTimeout(() => {
                        console.log('Third populate attempt after', 1000, 'ms');
                        populateSignatoryValues();
                    }, 1000);
                } else {
                    // Just populate existing signatories immediately
                    console.log('No need to add signatories, just populating values');
                    populateSignatoryValues();
                }
            }
            
            function populateSignatoryValues() {
                // Get all signatories after potential additions
                const allSignatories = document.querySelectorAll('.signatory-item');
                
                // Calculate max signatory with data (same logic as above)
                let calculatedSignatoryCount = 1;
                for (let i = 1; i <= 4; i++) {
                    if (existingCertificateData[`sig_${i}_name`] || 
                        existingCertificateData[`sig_${i}_title`] || 
                        existingCertificateData[`sig_${i}_signature`]) {
                        calculatedSignatoryCount = Math.max(calculatedSignatoryCount, i);
                    }
                }
                const savedSignatoryCount = existingCertificateData.signatory_count || calculatedSignatoryCount;
                let maxSignatoryWithData = savedSignatoryCount;
                for (let i = 2; i <= 4; i++) {
                    if (existingCertificateData[`sig_${i}_name`] || existingCertificateData[`sig_${i}_title`] || existingCertificateData[`sig_${i}_signature`]) {
                        maxSignatoryWithData = Math.max(maxSignatoryWithData, i);
                    }
                }
                
                console.log('Populating values for', allSignatories.length, 'signatories. Max signatory with data:', maxSignatoryWithData);
                
                // Populate values for all signatories - use maxSignatoryWithData to ensure we check all
                for (let i = 1; i <= maxSignatoryWithData && i <= 4; i++) {
                    const name = existingCertificateData[`sig_${i}_name`];
                    const title = existingCertificateData[`sig_${i}_title`];
                    const sigPath = existingCertificateData[`sig_${i}_signature`];
                    
                    console.log(`Processing signatory ${i}:`, {
                        name: name,
                        title: title,
                        sigPath: sigPath
                    });
                    
                    // Find the signatory item by data attribute
                    const signatoryItem = document.querySelector(`[data-signatory="${i}"]`);
                    
                    if (!signatoryItem) {
                        console.warn('Signatory item not found for index:', i);
                        continue;
                    }
                    
                    // Try multiple selectors to find inputs - search within the signatory item
                    const nameInput = signatoryItem.querySelector(`input[name="sig_${i}_name"]`) || 
                                     signatoryItem.querySelector(`#sig_${i}_name_input`) ||
                                     document.getElementById(`sig_${i}_name_input`) || 
                                     document.querySelector(`input[name="sig_${i}_name"]`);
                    const titleInput = signatoryItem.querySelector(`input[name="sig_${i}_title"]`) || 
                                      signatoryItem.querySelector(`#sig_${i}_title_input`) ||
                                      document.getElementById(`sig_${i}_title_input`) || 
                                      document.querySelector(`input[name="sig_${i}_title"]`);
                    const statusEl = signatoryItem.querySelector(`#sig_${i}_status`) || 
                                    document.getElementById(`sig_${i}_status`);
                    
                    console.log('Signatory', i, 'inputs found:', {
                        nameInput: !!nameInput,
                        titleInput: !!titleInput,
                        statusEl: !!statusEl,
                        name: name,
                        title: title,
                        sigPath: sigPath
                    });
                    
                    if (nameInput) {
                        if (name !== undefined && name !== null) {
                            nameInput.value = name || '';
                            nameInput.dispatchEvent(new Event('input', { bubbles: true })); // Trigger input event
                            console.log('✓ Populated sig_' + i + '_name:', name || '(empty)');
                        } else {
                            console.log('Skipping sig_' + i + '_name - value is undefined/null');
                        }
                    } else {
                        console.warn('❌ Name input not found for signatory', i);
                    }
                    
                    if (titleInput) {
                        if (title !== undefined && title !== null) {
                            titleInput.value = title || '';
                            titleInput.dispatchEvent(new Event('input', { bubbles: true })); // Trigger input event
                            console.log('✓ Populated sig_' + i + '_title:', title || '(empty)');
                        } else {
                            console.log('Skipping sig_' + i + '_title - value is undefined/null');
                        }
                    } else {
                        console.warn('❌ Title input not found for signatory', i);
                    }
                    
                    if (statusEl && sigPath) {
                        statusEl.textContent = 'Uploaded';
                        statusEl.className = 'text-xs text-green-500 dark:text-green-400';
                        console.log('✓ Set sig_' + i + ' status to Uploaded');
                    } else if (sigPath) {
                        console.warn('Signature path exists but status element not found for signatory', i);
                    }
                    
                    // Show preview image if signature exists
                    const previewEl = signatoryItem.querySelector(`#sig_${i}_preview`) || 
                                     document.getElementById(`sig_${i}_preview`);
                    if (previewEl && sigPath) {
                        // Construct URL from signature path
                        const sigUrl = sigPath.startsWith('http') ? sigPath : `/storage/${sigPath}`;
                        previewEl.src = sigUrl;
                        previewEl.classList.remove('hidden');
                        console.log('Set sig_' + i + ' preview image:', sigUrl);
                        // Show remove button if signature exists
                        showSignatureRemoveButton(i);
                    }
                }
                
                // Update signatory count based on actual DOM elements
                signatoryCount = allSignatories.length;
                
                // Show remove buttons if more than one signatory
                if (allSignatories.length > 1) {
                    document.querySelectorAll('.remove-signatory-btn').forEach(btn => {
                        btn.classList.remove('hidden');
                    });
                }
                
                // Update preview
                updateSignatoryPreview();
                
                // Trigger autosave after populating all values
                if (autosaveDebounced && typeof autosaveDebounced === 'function') {
                    console.log('Triggering autosave after populating signatories');
                    autosaveDebounced();
                }
                
                console.log('Finished populating signatory values');
            }
            
            // Start loading process
            tryLoad();
        })();
        
        // Re-sync when signatories are added/removed
        const observer = new MutationObserver(function(mutations) {
            try {
                for (var m = 0; m < mutations.length; m++) {
                    var mutation = mutations[m];
                    if (mutation && mutation.type === 'childList') {
                        if (typeof syncAllSignatories === 'function') {
                            syncAllSignatories();
                        }
                        if (typeof updateSignatoryPreview === 'function') {
                            updateSignatoryPreview();
                        }
                    }
                }
            } catch(e) {
                console.error('Error in MutationObserver:', e);
            }
        });
        
        const container = document.getElementById('signatory-details-container');
        if (container) {
            observer.observe(container, { childList: true, subtree: true });
        }
        
        // Also listen for input changes on signatory fields
        document.addEventListener('input', function(e) {
            if (e.target.matches('input[name^="sig_"][name$="_name"], input[name^="sig_"][name$="_title"]')) {
                updateSignatoryPreview();
            }
        });
        // Autosave helpers (debounced) - initialize autosaveDebounced now that collectPayload is defined
        async function autosaveCert(){
            try {
                if (statusEl) statusEl.textContent = 'Saving…';
                const payload = collectPayload();
                
                // Update the hidden JSON input to keep it in sync with the cleaned payload
                // This ensures deletions are reflected in the JSON
                const jsonEl = document.getElementById('certificate_data_json');
                if (jsonEl) {
                    jsonEl.value = JSON.stringify(payload);
                }
                
                // Persist JSON to R2 via controller autosave endpoint
                const autosaveUrl = '{{ route("organizer.events.certificate.autosave", [], false) }}' + '?id=' + encodeURIComponent(eventId);
                await postJSON(autosaveUrl, { certificate_data_json: JSON.stringify(payload) });
                if (statusEl) statusEl.textContent = 'Saved';
                refreshPreview(payload);
                showAutosaveIndicator('success');
                setTimeout(()=>{ if (statusEl) statusEl.textContent=''; }, 1200);
            } catch(e) {
                if (statusEl) statusEl.textContent = 'Save failed';
                showAutosaveIndicator('error');
                console.error('Certificate autosave failed', e);
            }
        }
        autosaveDebounced = debounce(autosaveCert, 700);

        async function postJSON(url, payload){
            const resp = await fetch(url, { method:'POST', headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify(payload) });
            return resp.json();
        }

        window.uploadLogo = async function(side){
            const file = document.getElementById(`logo_${side}_file`).files[0];
            if (!file) return;
            
            // Show immediate preview using FileReader
            const previewEl = document.getElementById(`logo_${side}_preview`);
            if (previewEl && file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewEl.src = e.target.result;
                    previewEl.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
            
            const fd = new FormData();
            fd.append('id', eventId);
            fd.append('side', side);
            fd.append('image', file);
            const resp = await fetch('/organizer/events/certificate/upload-logo', { method:'POST', headers:{ 'X-CSRF-TOKEN': csrf }, body: fd });
            const data = await resp.json();
                if (data.success) {
                const img = document.getElementById(`logo_${side}_img`);
                if (img) img.src = data.url;
                const rName = document.getElementById('recipient_name_input');
                if (rName && rName.disabled) {
                    rName.disabled = false;
                    rName.placeholder = 'e.g., JUAN DE LA CRUZ';
                }
                    // Show small preview/status near inputs (update with server URL)
                    const pv = document.getElementById(`logo_${side}_preview`);
                    const st = document.getElementById(`logo_${side}_status`);
                    if (pv) { pv.src = data.url; pv.classList.remove('hidden'); }
                    if (st) { st.textContent = 'Uploaded'; }
                    // Show remove button
                    showLogoRemoveButton(side);
                    
                    // Update certificate preview logo image
                    const previewImg = document.getElementById(`logo_${side}_img`);
                    if (previewImg) {
                        previewImg.src = data.url + '?t=' + Date.now(); // Add timestamp to force reload
                        previewImg.style.display = 'block';
                        previewImg.onload = function() {
                            this.style.display = 'block';
                        };
                        previewImg.onerror = function() {
                            this.style.display = 'none';
                        };
                    }
                    
                    // Persist in hidden JSON for autosave pipeline
                    try {
                        const jsonEl = document.getElementById('certificate_data_json');
                        const current = jsonEl?.value ? JSON.parse(jsonEl.value) : {};
                        current[`logo_${side}_url`] = data.url;
                        if (jsonEl) jsonEl.value = JSON.stringify(current);
                    } catch(_) {}
                    if (autosaveDebounced && typeof autosaveDebounced === 'function') {
                        autosaveDebounced();
                    }
            } else {
                // Hide preview on failure
                if (previewEl) {
                    previewEl.classList.add('hidden');
                }
                hideLogoRemoveButton(side);
            }
        }
        
        function showLogoRemoveButton(side) {
            // Check if remove button exists, if not create it
            let removeBtn = document.getElementById(`logo_${side}_remove_btn`);
            if (!removeBtn) {
                const statusEl = document.getElementById(`logo_${side}_status`);
                if (statusEl && statusEl.parentNode) {
                    removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.id = `logo_${side}_remove_btn`;
                    removeBtn.setAttribute('data-side', side);
                    removeBtn.className = 'ml-1 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300';
                    removeBtn.title = 'Remove logo';
                    removeBtn.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
                    // Attach event listener instead of onclick
                    removeBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (typeof window.removeLogo === 'function') {
                            window.removeLogo(side);
                        }
                    });
                    statusEl.parentNode.appendChild(removeBtn);
                }
            } else {
                // Ensure event listener is attached (in case button was just shown)
                if (removeBtn && !removeBtn.hasAttribute('data-listener-attached')) {
                    removeBtn.removeAttribute('onclick');
                    removeBtn.setAttribute('data-side', side);
                    removeBtn.setAttribute('data-listener-attached', 'true');
                    // Clone to ensure clean event listener
                    const newRemoveBtn = removeBtn.cloneNode(true);
                    removeBtn.parentNode.replaceChild(newRemoveBtn, removeBtn);
                    newRemoveBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (typeof window.removeLogo === 'function') {
                            window.removeLogo(side);
                        }
                    });
                    removeBtn = newRemoveBtn;
                }
            }
            if (removeBtn) {
                removeBtn.classList.remove('hidden');
            }
        }
        
        function hideLogoRemoveButton(side) {
            const removeBtn = document.getElementById(`logo_${side}_remove_btn`);
            if (removeBtn) {
                removeBtn.classList.add('hidden');
            }
        }
        
        window.removeLogo = async function(side) {
            if (!confirm('Are you sure you want to remove this logo?')) {
                return;
            }
            
            // Clear preview
            const previewEl = document.getElementById(`logo_${side}_preview`);
            if (previewEl) {
                previewEl.src = '';
                previewEl.classList.add('hidden');
            }
            
            // Reset status
            const statusEl = document.getElementById(`logo_${side}_status`);
            if (statusEl) {
                statusEl.textContent = 'No file';
                statusEl.className = 'text-xs text-gray-500 dark:text-gray-400';
            }
            
            // Clear file input
            const fileInput = document.getElementById(`logo_${side}_file`);
            if (fileInput) {
                fileInput.value = '';
            }
            
            // Hide remove button
            hideLogoRemoveButton(side);
            
            // Update certificate data to remove logo URL
            try {
                const jsonEl = document.getElementById('certificate_data_json');
                if (jsonEl) {
                    const current = jsonEl.value ? JSON.parse(jsonEl.value) : {};
                    delete current[`logo_${side}_url`];
                    jsonEl.value = JSON.stringify(current);
                }
                
                // Update preview image in certificate preview
                const img = document.getElementById(`logo_${side}_img`);
                if (img) {
                    img.src = '';
                    img.style.display = 'none';
                }
                
                // Trigger autosave
                if (autosaveDebounced && typeof autosaveDebounced === 'function') {
                    autosaveDebounced();
                }
                
                showAutosaveIndicator('success');
            } catch (e) {
                console.error('Failed to remove logo:', e);
                showAutosaveIndicator('error');
            }
        }

            // Dynamic signatory signature upload (1..4)
        window.uploadSignature = async function(signatoryNum){
            const file = document.getElementById(`sig_${signatoryNum}_file`)?.files?.[0];
            if (!file) return;
            
            // Show immediate preview using FileReader
            const previewEl = document.getElementById(`sig_${signatoryNum}_preview`);
            if (previewEl && file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewEl.src = e.target.result;
                    previewEl.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
            
            const fd = new FormData();
            fd.append('event_id', eventId);
            fd.append('signatory_num', String(signatoryNum));
            fd.append('signature', file);
            const resp = await fetch('/organizer/events/certificate/upload-signature', { method:'POST', headers:{ 'X-CSRF-TOKEN': csrf }, body: fd });
            const res = await resp.json();
            const statusEl = document.getElementById(`sig_${signatoryNum}_status`);
            if (res && res.success) {
                if (statusEl) statusEl.textContent = 'Uploaded';
                // Persist path in hidden json so preview + autosave can include it
                try {
                    const jsonEl = document.getElementById('certificate_data_json');
                    const current = jsonEl?.value ? JSON.parse(jsonEl.value) : {};
                    current[`sig_${signatoryNum}_signature`] = res.signature_path || `events/${eventId}/certificate/signature_${signatoryNum}.enc`;
                    if (jsonEl) jsonEl.value = JSON.stringify(current);
                } catch(_) {}
                // Update preview with server URL if provided
                if (res.url && previewEl) {
                    previewEl.src = res.url;
                    previewEl.classList.remove('hidden');
                }
                // Show remove button
                showSignatureRemoveButton(signatoryNum);
                if (autosaveDebounced && typeof autosaveDebounced === 'function') {
                    autosaveDebounced();
                }
                // Update inline preview image if present
                const img = document.querySelector(`#cert_preview_wrapper img[src*="signature_${signatoryNum}.enc"]`);
                if (img) {
                    // Force reload
                    img.src = img.src.split('?')[0] + `?t=${Date.now()}`;
                    img.style.display = 'block';
                }
            } else if (statusEl) {
                statusEl.textContent = 'Upload failed';
                // Hide preview on failure
                if (previewEl) {
                    previewEl.classList.add('hidden');
                }
                hideSignatureRemoveButton(signatoryNum);
            }
        }
        
        function showSignatureRemoveButton(signatoryNum) {
            const removeBtn = document.getElementById(`sig_${signatoryNum}_remove_btn`);
            if (removeBtn) {
                // Ensure event listener is attached and no inline onclick
                if (!removeBtn.hasAttribute('data-listener-attached')) {
                    removeBtn.removeAttribute('onclick');
                    removeBtn.setAttribute('data-listener-attached', 'true');
                    // Clone to ensure clean event listener
                    const newRemoveBtn = removeBtn.cloneNode(true);
                    removeBtn.parentNode.replaceChild(newRemoveBtn, removeBtn);
                    newRemoveBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (typeof window.removeSignature === 'function') {
                            window.removeSignature(signatoryNum);
                        }
                    });
                }
                removeBtn.classList.remove('hidden');
            }
        }
        
        function hideSignatureRemoveButton(signatoryNum) {
            const removeBtn = document.getElementById(`sig_${signatoryNum}_remove_btn`);
            if (removeBtn) {
                removeBtn.classList.add('hidden');
            }
        }
        
        window.removeSignature = async function(signatoryNum) {
            if (!confirm('Are you sure you want to remove this signature?')) {
                return;
            }
            
            // Clear preview
            const previewEl = document.getElementById(`sig_${signatoryNum}_preview`);
            if (previewEl) {
                previewEl.src = '';
                previewEl.classList.add('hidden');
            }
            
            // Reset status
            const statusEl = document.getElementById(`sig_${signatoryNum}_status`);
            if (statusEl) {
                statusEl.textContent = 'No file';
                statusEl.className = 'text-xs text-gray-500 dark:text-gray-400';
            }
            
            // Clear file input
            const fileInput = document.getElementById(`sig_${signatoryNum}_file`);
            if (fileInput) {
                fileInput.value = '';
            }
            
            // Hide remove button
            hideSignatureRemoveButton(signatoryNum);
            
            // Update certificate data to remove signature path
            try {
                const jsonEl = document.getElementById('certificate_data_json');
                if (jsonEl) {
                    const current = jsonEl.value ? JSON.parse(jsonEl.value) : {};
                    delete current[`sig_${signatoryNum}_signature`];
                    jsonEl.value = JSON.stringify(current);
                }
                
                // Trigger autosave
                if (autosaveDebounced && typeof autosaveDebounced === 'function') {
                    autosaveDebounced();
                }
                
                showAutosaveIndicator('success');
            } catch (e) {
                console.error('Failed to remove signature:', e);
                showAutosaveIndicator('error');
            }
        }

        // Initialize remove buttons and file inputs for existing logos and signatures on page load
        // MUST run immediately to prevent inline handlers from executing
        (function initAllRemoveButtons() {
            function attachEventListeners() {
                try {
                    // Attach event listeners for logo remove buttons
                    var logoSides = ['left', 'right'];
                    for (var ls = 0; ls < logoSides.length; ls++) {
                        try {
                            var side = logoSides[ls];
                            var removeBtn = document.getElementById('logo_' + side + '_remove_btn');
                            var fileInput = document.getElementById('logo_' + side + '_file');
                            
                            // Attach event listener for logo remove button
                            if (removeBtn) {
                                // Remove any inline onclick
                                removeBtn.removeAttribute('onclick');
                                // Clone to remove any existing handlers
                                var newRemoveBtn = removeBtn.cloneNode(true);
                                removeBtn.parentNode.replaceChild(newRemoveBtn, removeBtn);
                                (function(btnSide) {
                                    newRemoveBtn.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        if (typeof window.removeLogo === 'function') {
                                            window.removeLogo(btnSide);
                                        }
                                    });
                                })(side);
                            }
                            
                            // Attach event listener for logo file input
                            if (fileInput) {
                                // Remove inline onchange
                                fileInput.removeAttribute('onchange');
                                // Clone to remove any existing handlers
                                var newFileInput = fileInput.cloneNode(true);
                                fileInput.parentNode.replaceChild(newFileInput, fileInput);
                                (function(btnSide) {
                                    newFileInput.addEventListener('change', function(e) {
                                        e.stopPropagation();
                                        if (typeof window.uploadLogo === 'function') {
                                            window.uploadLogo(btnSide);
                                        }
                                    });
                                })(side);
                            }
                            
                            // Show remove button if logo exists
                            var previewEl = document.getElementById('logo_' + side + '_preview');
                            var statusEl = document.getElementById('logo_' + side + '_status');
                            if (previewEl && statusEl && !previewEl.classList.contains('hidden') && (statusEl.textContent === 'Uploaded' || previewEl.src)) {
                                if (typeof showLogoRemoveButton === 'function') {
                                    showLogoRemoveButton(side);
                                }
                            }
                        } catch(e) {
                            console.error('Error initializing logo buttons for', side, ':', e);
                        }
                    }
                    
                    // Attach event listeners for signature remove buttons and file inputs
                    for (let i = 1; i <= 4; i++) {
                        try {
                            const removeBtn = document.getElementById(`sig_${i}_remove_btn`);
                            const fileInput = document.getElementById(`sig_${i}_file`);
                            const signatoryRemoveBtn = document.querySelector(`.remove-signatory-btn[data-signatory="${i}"]`) || 
                                                       document.querySelector(`[data-signatory="${i}"] .remove-signatory-btn`);
                            
                            // Attach event listener for signature remove button (small X button)
                            if (removeBtn) {
                                removeBtn.removeAttribute('onclick');
                                const newRemoveBtn = removeBtn.cloneNode(true);
                                removeBtn.parentNode.replaceChild(newRemoveBtn, removeBtn);
                                newRemoveBtn.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    if (typeof window.removeSignature === 'function') {
                                        window.removeSignature(i);
                                    }
                                });
                            }
                            
                            // Attach event listener for signatory remove button (trash icon)
                            if (signatoryRemoveBtn) {
                                signatoryRemoveBtn.removeAttribute('onclick');
                                const newSignatoryRemoveBtn = signatoryRemoveBtn.cloneNode(true);
                                signatoryRemoveBtn.parentNode.replaceChild(newSignatoryRemoveBtn, signatoryRemoveBtn);
                                newSignatoryRemoveBtn.setAttribute('data-signatory', i);
                                newSignatoryRemoveBtn.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    if (typeof window.removeSignatory === 'function') {
                                        window.removeSignatory(i);
                                    }
                                });
                            }
                            
                            // Attach event listener for signature file input
                            if (fileInput) {
                                fileInput.removeAttribute('onchange');
                                const newFileInput = fileInput.cloneNode(true);
                                fileInput.parentNode.replaceChild(newFileInput, fileInput);
                                newFileInput.addEventListener('change', function(e) {
                                    e.stopPropagation();
                                    if (typeof window.uploadSignature === 'function') {
                                        window.uploadSignature(i);
                                    }
                                });
                            }
                            
                            // Show remove button if signature exists
                            const previewEl = document.getElementById(`sig_${i}_preview`);
                            const statusEl = document.getElementById(`sig_${i}_status`);
                            if (previewEl && statusEl && !previewEl.classList.contains('hidden') && (statusEl.textContent === 'Uploaded' || previewEl.src)) {
                                if (typeof showSignatureRemoveButton === 'function') {
                                    showSignatureRemoveButton(i);
                                }
                            }
                        } catch(e) {
                            console.error('Error initializing signature buttons for', i, ':', e);
                        }
                    }
                    
                    // Also attach event listeners using event delegation for dynamically added elements
                    const container = document.getElementById('signatory-details-container');
                    if (container) {
                        // Use event delegation for remove signatory buttons
                        container.addEventListener('click', function(e) {
                            const removeBtn = e.target.closest('.remove-signatory-btn');
                            if (removeBtn && removeBtn.dataset.signatory) {
                                e.preventDefault();
                                e.stopPropagation();
                                const signatoryNum = parseInt(removeBtn.dataset.signatory);
                                if (typeof window.removeSignatory === 'function') {
                                    window.removeSignatory(signatoryNum);
                                }
                            }
                        });
                    }
                } catch(e) {
                    console.error('Error in attachEventListeners:', e);
                }
            }
            
            // Run immediately if DOM is ready, otherwise wait
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    // Run immediately without setTimeout to prevent inline handlers from firing
                    attachEventListeners();
                });
            } else {
                // DOM is already ready, run immediately
                attachEventListeners();
            }
        })();

        function collectPayload(){
            // First, try to get the most up-to-date data from the hidden JSON input
            let basePayload = {};
            try {
                const jsonEl = document.getElementById('certificate_data_json');
                if (jsonEl && jsonEl.value) {
                    basePayload = JSON.parse(jsonEl.value);
                }
            } catch(e) {
                console.warn('Failed to parse certificate_data_json:', e);
            }
            
            const payload = {
                org_line1: document.getElementById('org_line1_input')?.value || basePayload.org_line1 || '',
                org_line2: document.getElementById('org_line2_input')?.value || basePayload.org_line2 || '',
                org_line3: document.getElementById('org_line3_input')?.value || basePayload.org_line3 || '',
                org_school: document.getElementById('org_school_input')?.value || basePayload.org_school || '',
                recipient_name: document.getElementById('recipient_name_input')?.value || basePayload.recipient_name || '',
                theme_primary: document.getElementById('theme_primary')?.value || basePayload.theme_primary || '#0b2f6b',
                theme_secondary: document.getElementById('theme_secondary')?.value || basePayload.theme_secondary || '#092557',
                theme_border: document.getElementById('theme_border')?.value || basePayload.theme_border || basePayload.theme_primary || '#0b2f6b',
            };
            
            // Copy non-signatory fields from JSON (like logos, event details, etc.)
            var basePayloadKeys = Object.keys(basePayload);
            for (var keyIdx = 0; keyIdx < basePayloadKeys.length; keyIdx++) {
                var key = basePayloadKeys[keyIdx];
                if (key && typeof key === 'string' && !key.startsWith('sig_') && !payload.hasOwnProperty(key)) {
                    payload[key] = basePayload[key];
                }
            }
            
            // Dynamic signatories - ONLY include signatories that exist in DOM
            const items = document.querySelectorAll('.signatory-item');
            payload.signatory_count = items.length;
            
            // First, clear any old signatory data from payload (only keep what exists in DOM)
            for (let i = 1; i <= 10; i++) {
                delete payload['sig_' + i + '_name'];
                delete payload['sig_' + i + '_title'];
                delete payload['sig_' + i + '_signature'];
            }
            
            // Now only add signatories that exist in DOM
            for (var itemIdx = 0; itemIdx < items.length; itemIdx++) {
                var item = items[itemIdx];
                var n = itemIdx + 1;
                var nameInput = item.querySelector('input[name="sig_' + n + '_name"]');
                var titleInput = item.querySelector('input[name="sig_' + n + '_title"]');
                
                payload['sig_' + n + '_name'] = (nameInput && nameInput.value) ? nameInput.value : '';
                payload['sig_' + n + '_title'] = (titleInput && titleInput.value) ? titleInput.value : '';
                
                // Get signature path from JSON if it exists for this signatory
                var statusEl = document.getElementById('sig_' + n + '_status');
                if (statusEl && (statusEl.textContent === 'Uploaded' || (statusEl.textContent && statusEl.textContent.includes('signature')))) {
                    try {
                        var jsonEl = document.getElementById('certificate_data_json');
                        var current = (jsonEl && jsonEl.value) ? JSON.parse(jsonEl.value) : {};
                        if (current['sig_' + n + '_signature']) {
                            payload['sig_' + n + '_signature'] = current['sig_' + n + '_signature'];
                        }
                    } catch(e) {
                        // Ignore parse errors
                    }
                }
            }
            
            return payload;
        }

        function refreshPreview(payload){
            try {
                // Update text fields inline
                const map = {
                    org_line1: 'org_line1', org_line2: 'org_line2', org_line3: 'org_line3',
                    org_school: 'org_school', recipient_name: 'recipient_name', event_title: 'event_title'
                };
                var mapKeys = Object.keys(map);
                for (var i = 0; i < mapKeys.length; i++) {
                    var k = mapKeys[i];
                    var el = document.getElementById(map[k]);
                    if (el && payload[k] !== undefined) {
                        el.textContent = payload[k];
                    }
                }
                
                // Dynamic signatory names/titles
                const count = payload.signatory_count || 0;
                for (let i = 1; i <= count; i++) {
                    var nEl = document.getElementById('sig_' + i + '_name');
                    var tEl = document.getElementById('sig_' + i + '_title');
                    if (nEl && payload['sig_' + i + '_name'] !== undefined) {
                        nEl.textContent = payload['sig_' + i + '_name'] || ('Signatory ' + i);
                    }
                    if (tEl && payload['sig_' + i + '_title'] !== undefined) {
                        tEl.textContent = payload['sig_' + i + '_title'] || 'Signature';
                    }
                }
                
                // Update logo images in preview
                var sides = ['left', 'right'];
                for (var j = 0; j < sides.length; j++) {
                    var side = sides[j];
                    var logoUrl = payload['logo_' + side + '_url'];
                    var previewImg = document.getElementById('logo_' + side + '_img');
                    if (previewImg) {
                        if (logoUrl) {
                            previewImg.src = logoUrl + '?t=' + Date.now();
                            previewImg.style.display = 'block';
                            previewImg.onload = function() {
                                this.style.display = 'block';
                            };
                            previewImg.onerror = function() {
                                this.style.display = 'none';
                            };
                        } else {
                            previewImg.style.display = 'none';
                        }
                    }
                }
                
                // Apply colors
                const primary = payload.theme_primary;
                var colorElements = document.querySelectorAll('#cert_preview_wrapper .text-blue-900, #cert_preview_wrapper .text-violet-900, #cert_preview_wrapper .text-emerald-900');
                if (colorElements) {
                    for (var c = 0; c < colorElements.length; c++) {
                        var el = colorElements[c];
                        if (el && el.style) {
                            el.style.color = primary;
                        }
                    }
                }
                
                var bands = document.querySelectorAll('#cert_preview_wrapper .band-top, #cert_preview_wrapper .band-bottom');
                if (bands) {
                    for (var b = 0; b < bands.length; b++) {
                        var bandEl = bands[b];
                        if (bandEl && bandEl.style) {
                            bandEl.style.backgroundImage = 'linear-gradient(to right, ' + payload.theme_primary + ', ' + payload.theme_secondary + ')';
                        }
                    }
                }
                
                // Apply border color
                const borderColor = payload.theme_border || payload.theme_primary || '#0b2f6b';
                const pageElement = document.querySelector('#cert_preview_wrapper .page');
                if (pageElement) {
                    pageElement.style.borderColor = borderColor;
                }
            } catch(e) {
                console.error('Error in refreshPreview:', e);
            }
        }

        // Wire autosave on inputs
        (function() {
            try {
                var autosaveFieldIds = ['org_line1_input','org_line2_input','org_line3_input','org_school_input','recipient_name_input','theme_primary','theme_secondary','theme_border','theme_primary-hex','theme_secondary-hex','theme_border-hex'];
                autosaveFieldIds.forEach(function(id) {
                    var el = document.getElementById(id);
                    if (el && typeof autosaveDebounced === 'function') {
                        el.addEventListener('input', autosaveDebounced);
                        el.addEventListener('change', autosaveDebounced);
                    }
                });
            } catch(e) {
                console.error('Error wiring autosave inputs:', e);
            }
        })();
        
        // Dynamic signatory inputs also trigger autosave
        document.addEventListener('input', function(e) {
            if (e.target && e.target.matches && e.target.matches('input[name^="sig_"][name$="_name"], input[name^="sig_"][name$="_title"]')) {
                if (typeof autosaveDebounced === 'function') {
                    autosaveDebounced();
                }
            }
        });

        // Skip certificate toggle: persist a tiny flag in R2 settings
        (function() {
            const box = document.getElementById('skip_certificate_step4');
            if (!box) {
                return;
            }
            
            // Initialize visibility on page load
            const formContainer = document.getElementById('certificate-form-container');
            const previewSection = document.getElementById('cert_section');
            const hiddenField = document.getElementById('skip_certificate_hidden');
            
            function updateVisibility() {
                if (hiddenField) {
                    hiddenField.value = box.checked ? '1' : '0';
                }
                
                if (box.checked) {
                    // Hide form and preview if checkbox is checked
                    if (formContainer) {
                        formContainer.style.display = 'none';
                        formContainer.hidden = true;
                    }
                    if (previewSection) {
                        previewSection.style.display = 'none';
                        previewSection.hidden = true;
                        previewSection.removeAttribute('open');
                    }
                } else {
                    // Show form and preview if checkbox is not checked
                    if (formContainer) {
                        formContainer.style.display = 'block';
                        formContainer.hidden = false;
                    }
                    if (previewSection) {
                        previewSection.style.display = 'block';
                        previewSection.hidden = false;
                        previewSection.setAttribute('open', 'open');
                    }
                }
            }
            
            // Load existing flag if present - check database first, then R2 settings
            async function loadCertificateSetting() {
                try {
                    // Try to get from database first
                    const response = await fetch(`/organizer/events/certificate-status?id=${encodeURIComponent(eventId)}`, { 
                        credentials: 'same-origin',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        if (typeof data.certificate_enabled !== 'undefined') {
                            box.checked = !data.certificate_enabled; // If certificate_enabled is false, checkbox should be checked
                            // Update visibility immediately after setting checkbox state
                            updateVisibility();
                            return;
                        }
                    }
                } catch (error) {
                }
                
                // Fallback to R2 settings
                try {
                    const response = await fetch(`/api/events/${encodeURIComponent(eventId)}/settings`, { 
                        credentials: 'same-origin' 
                    });
                    if (response.ok) {
                        const cfg = await response.json();
                        if (cfg && typeof cfg.certificate_enabled !== 'undefined') {
                            box.checked = !cfg.certificate_enabled; // If certificate_enabled is false, checkbox should be checked
                            // Update visibility immediately after setting checkbox state
                            updateVisibility();
                        }
                    }
                } catch (error) {
                }
            }
            
            loadCertificateSetting();

            const persist = async (enabled) => {
                try {
                    // Update both R2 settings and event table
                    await fetch(`/api/events/${encodeURIComponent(eventId)}/settings`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                        body: JSON.stringify({ certificate_enabled: enabled })
                    });
                    
                    // Also update via certificate autosave endpoint
                    const formData = new FormData();
                    formData.append('_token', csrf);
                    formData.append('id', eventId);
                    const certEnabled = enabled ? '1' : '0';
                    const skipCert = enabled ? '0' : '1';
                    formData.append('certificate_enabled', certEnabled);
                    formData.append('skip_certificate', skipCert);
                    
                    console.log('Certificate persist - Sending values:', {
                        enabled: enabled,
                        certificate_enabled: certEnabled,
                        skip_certificate: skipCert
                    });
                    
                    await fetch('/organizer/events/certificate/autosave', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: formData
                    });
                    
                    console.log('Certificate enabled setting persisted:', enabled);
                } catch (error) {
                    console.error('Failed to persist certificate enabled setting:', error);
                }
            };
            box.addEventListener('change', () => {
                const enableCert = !box.checked;
                persist(enableCert);
                
                // Update hidden form field
                const hiddenField = document.getElementById('skip_certificate_hidden');
                if (hiddenField) {
                    hiddenField.value = box.checked ? '1' : '0';
                }
                
                // Hide/show certificate form and preview based on checkbox state
                const formContainer = document.getElementById('certificate-form-container');
                const previewSection = document.getElementById('cert_section');
                
                // Use updateVisibility function for consistency
                updateVisibility();
            });
            
            // Initialize visibility on page load
            updateVisibility();
            
            // Update visibility after loading the setting (if async finishes)
            loadCertificateSetting().then(() => {
                updateVisibility();
            }).catch(() => {
                // If loading fails, still ensure visibility is set correctly
                updateVisibility();
            });
        })();

        // Certificate autosave functionality
        (function() {
            let autosaveTimer = null;
            
            function queueCertificateAutosave() {
                clearTimeout(autosaveTimer);
                autosaveTimer = setTimeout(autosaveCertificate, 1000);
            }
            
            async function autosaveCertificate() {
                try {
                    const formData = new FormData();
                    formData.append('_token', csrf);
                    formData.append('id', eventId);
                    
                    // Collect all form data
                    const inputs = document.querySelectorAll('#certificate-form-container input, #certificate-form-container select, #certificate-form-container textarea');
                    inputs.forEach(input => {
                        if (input.type === 'checkbox') {
                            formData.append(input.name, input.checked ? '1' : '0');
                        } else if (input.type === 'file') {
                            // Skip file inputs for autosave
                        } else if (input.value !== undefined && input.value !== '') {
                            formData.append(input.name, input.value);
                        }
                    });
                    
                    // Also include the skip certificate setting
                    const skipCheckbox = document.getElementById('skip_certificate_step4');
                    if (skipCheckbox) {
                        const certEnabled = skipCheckbox.checked ? '0' : '1';
                        const skipCert = skipCheckbox.checked ? '1' : '0';
                        formData.append('certificate_enabled', certEnabled);
                        formData.append('skip_certificate', skipCert);
                        console.log('Certificate autosave - Sending values:', {
                            checkbox_checked: skipCheckbox.checked,
                            certificate_enabled: certEnabled,
                            skip_certificate: skipCert
                        });
                    }
                    
                    await fetch('/organizer/events/certificate/autosave', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });
                    
                    console.log('Certificate autosaved successfully');
                    showAutosaveIndicator('success');
                } catch (error) {
                    console.error('Certificate autosave failed:', error);
                    showAutosaveIndicator('error');
                }
            }
            
            // Set up autosave listeners
            const certificateContainer = document.getElementById('certificate-form-container');
            if (certificateContainer) {
                const inputs = certificateContainer.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    if (input.type !== 'file') {
                        input.addEventListener('input', queueCertificateAutosave);
                        input.addEventListener('change', queueCertificateAutosave);
                    }
                });
            }
            
            // Also autosave when skip certificate checkbox changes
            const skipCheckbox = document.getElementById('skip_certificate_step4');
            if (skipCheckbox) {
                skipCheckbox.addEventListener('change', queueCertificateAutosave);
            }
        })();
    </script>
    <style>
        /* Printing tweaks for full A4 certificate output */
        @media print {
            @page { size: A4 landscape; margin: 8mm; }
            html, body { height: 100%; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; background: #ffffff !important; }
            /* Keep section expanded and hide summary controls */
            #cert_section[open] > summary { display: none !important; }
            /* Avoid any clipping constraints */
            #cert_section, #cert_section > div { overflow: visible !important; height: auto !important; }
        }
    </style>
</x-app-layout>