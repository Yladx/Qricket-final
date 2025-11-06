@php($cd = $certificateData ?? [])
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 space-y-4">

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-lg font-bold text-gray-900 dark:text-gray-100">Certificate Details</h1>
            <p class="mt-0.5 text-xs text-gray-600 dark:text-gray-400">Set organization, theme, logos, recipient and signatories.</p>
        </div>
    </div>

    <!-- Organization -->
    <div>
        <h3 class="text-xs font-semibold text-gray-800 dark:text-gray-100 mb-1">Organization</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
            <label class="block text-xs text-gray-600 dark:text-gray-300 mb-0.5">Organization Line 1</label>
            <input id="org_line1_input" name="org_line1" type="text" class="w-full text-xs bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md py-1.5 px-2" value="{{ $cd['org_line1'] ?? '' }}">
        </div>
        <div>
            <label class="block text-xs text-gray-600 dark:text-gray-300 mb-0.5">Organization Line 2</label>
            <input id="org_line2_input" name="org_line2" type="text" class="w-full text-xs bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md py-1.5 px-2" value="{{ $cd['org_line2'] ?? '' }}">
        </div>
        <div>
            <label class="block text-xs text-gray-600 dark:text-gray-300 mb-0.5">School/Organization Name</label>
            <input id="org_school_input" name="org_school" type="text" class="w-full text-xs bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md py-1.5 px-2" value="{{ $cd['org_school'] ?? '' }}">
        </div>
        </div>
    </div>

    <!-- Theme & Logos -->
    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 space-y-4">
        <h3 class="text-xs font-semibold text-gray-800 dark:text-gray-100 mb-3">Theme & Logos</h3>
        
        <!-- Certificate Color Customization - Inline -->
        <div>
            <div class="text-xs font-medium text-gray-900 dark:text-gray-100 mb-2">Certificate Color Customization</div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-0.5">Primary Color</label>
                    <div class="flex items-center space-x-1">
                        <input type="color" id="theme_primary" name="theme_primary" class="w-6 h-6 rounded border border-gray-300 dark:border-gray-600 cursor-pointer" value="{{ $cd['theme_primary'] ?? '#0b2f6b' }}">
                        <input type="text" id="theme_primary-hex" class="flex-1 rounded bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 h-6 text-xs" value="{{ $cd['theme_primary'] ?? '#0b2f6b' }}" placeholder="#0b2f6b">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-0.5">Secondary Color</label>
                    <div class="flex items-center space-x-1">
                        <input type="color" id="theme_secondary" name="theme_secondary" class="w-6 h-6 rounded border border-gray-300 dark:border-gray-600 cursor-pointer" value="{{ $cd['theme_secondary'] ?? '#092557' }}">
                        <input type="text" id="theme_secondary-hex" class="flex-1 rounded bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 h-6 text-xs" value="{{ $cd['theme_secondary'] ?? '#092557' }}" placeholder="#092557">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-0.5">Border Color</label>
                    <div class="flex items-center space-x-1">
                        <input type="color" id="theme_border" name="theme_border" class="w-6 h-6 rounded border border-gray-300 dark:border-gray-600 cursor-pointer" value="{{ $cd['theme_border'] ?? '#0b2f6b' }}">
                        <input type="text" id="theme_border-hex" class="flex-1 rounded bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 h-6 text-xs" value="{{ $cd['theme_border'] ?? '#0b2f6b' }}" placeholder="#0b2f6b">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Logos - Next Line -->
        <div>
            <label class="block text-xs text-gray-600 dark:text-gray-300 mb-0.5">Logos</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <label class="flex items-center justify-between gap-2 border border-dashed border-gray-300 dark:border-gray-700 rounded-md p-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 flex items-center justify-center rounded bg-qricket-50 text-qricket-600 dark:bg-gray-800 dark:text-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-medium">Left Logo</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">PNG/JPG/WebP, up to 3MB</div>
                            <div class="mt-0.5 flex items-center gap-1">
                                <img id="logo_left_preview" src="{{ $cd['logo_left_url'] ?? '' }}" class="w-5 h-5 rounded object-contain border border-gray-200 dark:border-gray-700 {{ empty($cd['logo_left_url']) ? 'hidden' : '' }}" alt="Left logo preview">
                                <span id="logo_left_status" class="text-xs text-gray-500 dark:text-gray-400">{{ empty($cd['logo_left_url']) ? 'No file' : 'Uploaded' }}</span>
                            </div>
                        </div>
                    </div>
                    <input type="file" id="logo_left_file" accept="image/*" class="hidden" onchange="uploadLogo('left')">
                    <span class="px-2 py-0.5 text-xs rounded bg-qricket-500 text-white">Choose</span>
                </label>
                <label class="flex items-center justify-between gap-2 border border-dashed border-gray-300 dark:border-gray-700 rounded-md p-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 flex items-center justify-center rounded bg-qricket-50 text-qricket-600 dark:bg-gray-800 dark:text-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-medium">Right Logo</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">PNG/JPG/WebP, up to 3MB</div>
                            <div class="mt-0.5 flex items-center gap-1">
                                <img id="logo_right_preview" src="{{ $cd['logo_right_url'] ?? '' }}" class="w-5 h-5 rounded object-contain border border-gray-200 dark:border-gray-700 {{ empty($cd['logo_right_url']) ? 'hidden' : '' }}" alt="Right logo preview">
                                <span id="logo_right_status" class="text-xs text-gray-500 dark:text-gray-400">{{ empty($cd['logo_right_url']) ? 'No file' : 'Uploaded' }}</span>
                            </div>
                        </div>
                    </div>
                    <input type="file" id="logo_right_file" accept="image/*" class="hidden" onchange="uploadLogo('right')">
                    <span class="px-2 py-0.5 text-xs rounded bg-qricket-500 text-white">Choose</span>
                </label>
            </div>
        </div>
    </div>

    <!-- Recipient -->
    <div>
        <h3 class="text-xs font-semibold text-gray-800 dark:text-gray-100 mb-1">Recipient</h3>
        <div class="grid grid-cols-1">
            <div>
                <label class="block text-xs text-gray-600 dark:text-gray-300 mb-0.5">Recipient Preview Name</label>
                @php($hasLogo = !empty($cd['logo_left_url']) || !empty($cd['logo_right_url']))
                <input id="recipient_name_input" name="recipient_name" @if(!$hasLogo) disabled @endif type="text" class="w-full text-xs bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md py-1.5 px-2" value="{{ $cd['recipient_name'] ?? '' }}" placeholder="Upload a logo first, then enter name">
            </div>
        </div>
    </div>

    <!-- Signatories -->
    <div>
        <h3 class="text-xs font-semibold text-gray-800 dark:text-gray-100 mb-1">Signatories</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Upload signature images for each signatory below</p>
    </div>

    <!-- Signatory Details -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-xs font-semibold text-gray-800 dark:text-gray-100">Signatory Details</h3>
            <button type="button" id="add-signatory-btn" class="px-3 py-1.5 text-xs bg-qricket-500 text-white rounded-md hover:bg-qricket-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" onclick="addSignatory()">
                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Signatory
            </button>
        </div>
        <div id="signatory-details-container" class="space-y-3">
            <!-- Signatory 1 (default) -->
            <div class="signatory-item border border-gray-200 dark:border-gray-700 rounded-lg p-3" data-signatory="1">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-xs font-medium text-gray-700 dark:text-gray-300">Signatory 1</h4>
                    <button type="button" class="remove-signatory-btn text-red-500 hover:text-red-700 text-xs hidden" onclick="removeSignatory(1)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-300 mb-0.5">Name</label>
                        <input id="sig_1_name_input" name="sig_1_name" type="text" maxlength="36" class="w-full text-xs bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md py-1.5 px-2" value="{{ $cd['sig_1_name'] ?? $cd['sig_left_name'] ?? '' }}" placeholder="Max 36 characters">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-300 mb-0.5">Title</label>
                        <input id="sig_1_title_input" name="sig_1_title" type="text" maxlength="28" class="w-full text-xs bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md py-1.5 px-2" value="{{ $cd['sig_1_title'] ?? $cd['sig_left_title'] ?? '' }}" placeholder="Max 28 characters">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="block text-xs text-gray-600 dark:text-gray-300 mb-0.5">Signature Image</label>
            <label class="flex items-center justify-between gap-2 border border-dashed border-gray-300 dark:border-gray-700 rounded-md p-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 flex items-center justify-center rounded bg-qricket-50 text-qricket-600 dark:bg-gray-800 dark:text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                    </div>
                    <div>
                                <div class="text-xs font-medium">Signature 1</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">PNG/JPG/WebP, up to 2MB</div>
                                <div class="mt-0.5"><span id="sig_1_status" class="text-xs text-gray-500 dark:text-gray-400">{{ !empty($cd['sig_1_signature']) ? 'Uploaded' : 'No file' }}</span></div>
                    </div>
                </div>
                        <input type="file" id="sig_1_file" accept="image/*" class="hidden" onchange="uploadSignature(1)">
                <span class="px-2 py-0.5 text-xs rounded bg-qricket-500 text-white">Choose</span>
            </label>
                </div>
            </div>
        </div>
    </div>
    
    <div class="flex items-center justify-end">
        <span id="cert_autosave_status" class="text-xs text-gray-500 dark:text-gray-400"></span>
    </div>
</div>

<script>
let signatoryCount = 1;

// Expose addSignatory globally so it can be called from onclick handlers
window.addSignatory = function addSignatory() {
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
    const container = document.getElementById('signatory-details-container');
    const newSignatory = document.createElement('div');
    newSignatory.className = 'signatory-item border border-gray-200 dark:border-gray-700 rounded-lg p-3';
    newSignatory.setAttribute('data-signatory', signatoryCount);
    
    // Build signatory HTML using string concatenation to avoid template literal issues
    const sigNum = signatoryCount;
    newSignatory.innerHTML = 
        '<div class="flex items-center justify-between mb-2">' +
            '<h4 class="text-xs font-medium text-gray-700 dark:text-gray-300">Signatory ' + sigNum + '</h4>' +
            '<button type="button" class="remove-signatory-btn text-red-500 hover:text-red-700 text-xs" onclick="window.removeSignatory(' + sigNum + ')">' +
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
                        '<div class="mt-0.5"><span id="sig_' + sigNum + '_status" class="text-xs text-gray-500 dark:text-gray-400">No file</span></div>' +
                    '</div>' +
                '</div>' +
                '<input type="file" id="sig_' + sigNum + '_file" accept="image/*" class="hidden" onchange="window.uploadSignature(' + sigNum + ')">' +
                '<span class="px-2 py-0.5 text-xs rounded bg-qricket-500 text-white">Choose</span>' +
            '</label>' +
        '</div>';
    
    container.appendChild(newSignatory);
    
    // Update add button state
    const addBtn = document.getElementById('add-signatory-btn');
    if (signatoryCount >= 4) {
        addBtn.disabled = true;
        addBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
    
    // Show remove buttons for all signatories
    document.querySelectorAll('.remove-signatory-btn').forEach(btn => {
        btn.classList.remove('hidden');
    });
    
    // Sync with preview
    if (typeof window.updateSignatoryPreview === 'function') {
        window.updateSignatoryPreview();
    }
};

// Expose removeSignatory globally
window.removeSignatory = function removeSignatory(signatoryNum) {
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
    
    const certDataEl = document.getElementById('certificate_data_json');
    if (!certDataEl) {
        console.error('Certificate data JSON element not found');
        return;
    }
    
    try {
        // Get current certificate data
        let certData = JSON.parse(certDataEl.value || '{}');
        
        // Remove ALL signature-related fields for this signatory number
        delete certData[`sig_${signatoryNum}_name`];
        delete certData[`sig_${signatoryNum}_title`];
        delete certData[`sig_${signatoryNum}_signature`];
        
        // Also remove any legacy indexed fields (e.g., sig_1_name, sig_2_name, etc.)
        // and any status fields that might exist
        Object.keys(certData).forEach(key => {
            if (key.startsWith(`sig_${signatoryNum}_`)) {
                delete certData[key];
            }
        });
        
        // Update the hidden input with cleaned data
        certDataEl.value = JSON.stringify(certData);
        
        // Remove the DOM element
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
            document.querySelectorAll('.remove-signatory-btn').forEach(btn => {
                btn.classList.add('hidden');
            });
        }
        
        // Renumber remaining signatories (this will rebuild JSON with correct numbering)
        renumberSignatories();
        
        // Get the updated JSON after renumbering
        const updatedCertData = JSON.parse(certDataEl.value || '{}');
        
        // Double-check: Remove any remaining orphaned signatory data (5+ or gaps)
        let cleaned = false;
        for (let i = 1; i <= 10; i++) {
            const hasName = updatedCertData[`sig_${i}_name`];
            const hasTitle = updatedCertData[`sig_${i}_title`];
            const hasSignature = updatedCertData[`sig_${i}_signature`];
            
            // Check if this signatory number exists in DOM
            const existsInDOM = document.querySelector(`[data-signatory="${i}"]`);
            
            if (!existsInDOM && (hasName || hasTitle || hasSignature)) {
                // Orphaned data - remove it
                delete updatedCertData[`sig_${i}_name`];
                delete updatedCertData[`sig_${i}_title`];
                delete updatedCertData[`sig_${i}_signature`];
                cleaned = true;
            }
        }
        
        if (cleaned) {
            certDataEl.value = JSON.stringify(updatedCertData);
        }
        
        // Sync with preview
        if (typeof window.updateSignatoryPreview === 'function') {
            window.updateSignatoryPreview();
        }
        
        // Trigger autosave to persist the deletion
        // Wait a tiny bit to ensure DOM is updated, then trigger autosave
        setTimeout(() => {
            const eventId = document.getElementById('event_id')?.value || 
                           document.querySelector('input[name="id"]')?.value ||
                           new URLSearchParams(window.location.search).get('id');
            
            if (eventId && typeof window.autosaveDebounced === 'function') {
                // Use the existing autosave function which will call collectPayload()
                // This ensures the cleaned JSON is properly read
                window.autosaveDebounced();
                console.log('Signatory deletion triggered autosave');
            } else if (eventId) {
                // Fallback: direct autosave call
                const autosaveUrl = '/organizer/events/certificate/autosave?id=' + encodeURIComponent(eventId);
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                
                fetch(autosaveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        certificate_data_json: certDataEl.value
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Signatory deletion saved successfully');
                    } else {
                        console.error('Failed to save signatory deletion:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error saving signatory deletion:', error);
                });
            } else {
                console.warn('Event ID not found, cannot autosave signatory deletion');
            }
        }, 100);
    } catch (e) {
        console.error('Failed to remove signatory data:', e);
        if (typeof Swal !== 'undefined' && window.Swal) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to remove signatory. Please try again.',
            });
        } else {
            alert('Failed to remove signatory. Please try again.');
        }
    }
}

function renumberSignatories() {
    const signatories = document.querySelectorAll('.signatory-item');
    const certDataEl = document.getElementById('certificate_data_json');
    let certData = {};
    
    // Load existing certificate data if available (to preserve signatures)
    if (certDataEl) {
        try {
            certData = JSON.parse(certDataEl.value || '{}');
        } catch (e) {
            console.error('Failed to parse certificate data:', e);
        }
    }
    
    // Create a map of old signature paths before clearing
    const signatureMap = {};
    for (let i = 1; i <= 4; i++) {
        if (certData[`sig_${i}_signature`]) {
            signatureMap[i] = certData[`sig_${i}_signature`];
        }
    }
    
    // Clear all old signatory data from JSON (will rebuild below)
    for (let i = 1; i <= 4; i++) {
        delete certData[`sig_${i}_name`];
        delete certData[`sig_${i}_title`];
        delete certData[`sig_${i}_signature`];
    }
    
    // Renumber and collect CURRENT values from all signatories
    signatories.forEach((item, index) => {
        const newNum = index + 1;
        item.setAttribute('data-signatory', newNum);
        const title = item.querySelector('h4');
        if (title) {
            title.textContent = `Signatory ${newNum}`;
        }
        
        // Find inputs - try multiple selectors
        const nameInput = item.querySelector('input[name^="sig_"][name$="_name"]') || document.getElementById(`sig_${index + 1}_name_input`);
        const titleInput = item.querySelector('input[name^="sig_"][name$="_title"]`) || document.getElementById(`sig_${index + 1}_title_input`);
        const fileInput = item.querySelector('input[type="file"]');
        const statusEl = item.querySelector('[id^="sig_"][id$="_status"]`) || document.getElementById(`sig_${index + 1}_status`);
        
        // Update input names and IDs
        if (nameInput) {
            const oldName = nameInput.name;
            const oldNum = oldName ? oldName.match(/sig_(\d+)_name/)?.[1] : null;
            nameInput.name = `sig_${newNum}_name`;
            nameInput.id = `sig_${newNum}_name_input`;
            // Use CURRENT value, not old value
            certData[`sig_${newNum}_name`] = nameInput.value || '';
            // Preserve signature path if it exists
            if (oldNum && signatureMap[oldNum]) {
                certData[`sig_${newNum}_signature`] = signatureMap[oldNum];
            } else if (statusEl && (statusEl.textContent === 'Uploaded' || statusEl.textContent.includes('signature'))) {
                // Try to get signature from current data
                const currentData = JSON.parse(certDataEl?.value || '{}');
                if (currentData[`sig_${oldNum || newNum}_signature`]) {
                    certData[`sig_${newNum}_signature`] = currentData[`sig_${oldNum || newNum}_signature`];
                }
            }
        }
        
        if (titleInput) {
            const oldTitle = titleInput.name;
            titleInput.name = `sig_${newNum}_title`;
            titleInput.id = `sig_${newNum}_title_input`;
            // Use CURRENT value, not old value
            certData[`sig_${newNum}_title`] = titleInput.value || '';
        }
        
        if (fileInput) {
            fileInput.id = `sig_${newNum}_file`;
            fileInput.setAttribute('onchange', `uploadSignature(${newNum})`);
        }
        
        if (statusEl) {
            statusEl.id = `sig_${newNum}_status`;
            // Update signature label
            const sigLabel = statusEl.closest('.signatory-item')?.querySelector('.text-xs.font-medium');
            if (sigLabel && sigLabel.textContent.includes('Signature')) {
                sigLabel.textContent = `Signature ${newNum}`;
            }
        }
        
        // Update remove button onclick
        const removeBtn = item.querySelector('.remove-signatory-btn');
        if (removeBtn) {
            removeBtn.setAttribute('onclick', `removeSignatory(${newNum})`);
        }
    });
    
    // Update signatory count
    certData.signatory_count = signatories.length;
    
    // Save updated certificate data back to hidden input
    if (certDataEl) {
        certDataEl.value = JSON.stringify(certData);
    }
    
    console.log('Renumbered signatories:', {
        count: signatories.length,
        data: Object.keys(certData).filter(k => k.startsWith('sig_'))
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Load signatories from JSON first
    const jsonEl = document.getElementById('certificate_data_json');
    if (jsonEl && jsonEl.value) {
        try {
            const certData = JSON.parse(jsonEl.value);
            const savedSignatoryCount = certData.signatory_count || 1;
            
            // Get current count BEFORE adding any new signatories
            let existingSignatories = document.querySelectorAll('.signatory-item');
            const currentCount = existingSignatories.length;
            
            // If we have more signatories saved than currently in DOM, add them
            if (savedSignatoryCount > currentCount) {
                // Add missing signatories
                for (let i = currentCount + 1; i <= savedSignatoryCount && i <= 4; i++) {
                    addSignatory();
                }
                
                // Update signatory count after adding
                signatoryCount = savedSignatoryCount;
            }
            
            // Re-query ALL signatories after adding missing ones
            existingSignatories = document.querySelectorAll('.signatory-item');
            
            // Populate ALL signatories with saved data (including newly added ones)
            existingSignatories.forEach((item, index) => {
                const signatoryNum = index + 1;
                
                // Try multiple selectors to find the inputs
                const nameInput = item.querySelector(`input[name="sig_${signatoryNum}_name"]`) || document.getElementById(`sig_${signatoryNum}_name_input`);
                const titleInput = item.querySelector(`input[name="sig_${signatoryNum}_title"]`) || document.getElementById(`sig_${signatoryNum}_title_input`);
                const statusEl = document.getElementById(`sig_${signatoryNum}_status`);
                
                if (nameInput && certData[`sig_${signatoryNum}_name`]) {
                    nameInput.value = certData[`sig_${signatoryNum}_name`];
                }
                if (titleInput && certData[`sig_${signatoryNum}_title`]) {
                    titleInput.value = certData[`sig_${signatoryNum}_title`];
                }
                if (statusEl && certData[`sig_${signatoryNum}_signature`]) {
                    statusEl.textContent = 'Uploaded';
                    statusEl.className = 'text-xs text-green-500 dark:text-green-400';
                }
            });
            
            console.log('Loaded signatories:', {
                savedCount: savedSignatoryCount,
                loadedCount: existingSignatories.length,
                signatoryData: Object.keys(certData).filter(k => k.startsWith('sig_'))
            });
        } catch (e) {
            console.error('Failed to load signatories from JSON:', e);
        }
    }
    
    // Show remove buttons if more than one signatory exists
    const existingSignatories = document.querySelectorAll('.signatory-item');
    if (existingSignatories.length > 1) {
        document.querySelectorAll('.remove-signatory-btn').forEach(btn => {
            btn.classList.remove('hidden');
        });
    }
    
    // Update signatory count
    signatoryCount = existingSignatories.length;
    
    // Update add button state
    const addBtn = document.getElementById('add-signatory-btn');
    if (signatoryCount >= 4) {
        addBtn.disabled = true;
        addBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
    
    // Load theme colors from JSON and apply to color inputs
    if (jsonEl && jsonEl.value) {
        try {
            const certData = JSON.parse(jsonEl.value);
            const primaryInput = document.getElementById('theme_primary');
            const primaryHex = document.getElementById('theme_primary-hex');
            const secondaryInput = document.getElementById('theme_secondary');
            const secondaryHex = document.getElementById('theme_secondary-hex');
            const borderInput = document.getElementById('theme_border');
            const borderHex = document.getElementById('theme_border-hex');
            
            if (primaryInput && certData.theme_primary) {
                primaryInput.value = certData.theme_primary;
                if (primaryHex) primaryHex.value = certData.theme_primary;
            }
            if (secondaryInput && certData.theme_secondary) {
                secondaryInput.value = certData.theme_secondary;
                if (secondaryHex) secondaryHex.value = certData.theme_secondary;
            }
            if (borderInput && certData.theme_border) {
                borderInput.value = certData.theme_border;
                if (borderHex) borderHex.value = certData.theme_border;
            } else if (borderInput) {
                // Default border color to primary if not set
                const borderColor = certData.theme_primary || '#0b2f6b';
                borderInput.value = borderColor;
                if (borderHex) borderHex.value = borderColor;
            }
        } catch (e) {
            console.error('Failed to load theme colors from JSON:', e);
        }
    }
    
    // Sync color picker with hex input and auto-save
    function syncCertificateColors() {
        const colorInputs = ['theme_primary', 'theme_secondary', 'theme_border'];
        
        colorInputs.forEach(colorId => {
            const colorPicker = document.getElementById(colorId);
            const textInput = document.getElementById(colorId + '-hex');
            
            if (colorPicker && textInput) {
                // Sync color picker -> text input
                colorPicker.addEventListener('input', () => {
                    textInput.value = colorPicker.value;
                    updateColorsInJSON();
                });
                
                // Sync text input -> color picker (with validation)
                textInput.addEventListener('input', () => {
                    const value = textInput.value;
                    if (/^#[0-9A-F]{6}$/i.test(value)) {
                        colorPicker.value = value;
                        updateColorsInJSON();
                    }
                });
            }
        });
    }
    
    function updateColorsInJSON() {
        const jsonEl = document.getElementById('certificate_data_json');
        if (!jsonEl) return;
        
        try {
            const certData = JSON.parse(jsonEl.value || '{}');
            const primaryInput = document.getElementById('theme_primary');
            const secondaryInput = document.getElementById('theme_secondary');
            const borderInput = document.getElementById('theme_border');
            
            if (primaryInput) {
                certData.theme_primary = primaryInput.value;
            }
            if (secondaryInput) {
                certData.theme_secondary = secondaryInput.value;
            }
            if (borderInput) {
                certData.theme_border = borderInput.value;
            }
            
            jsonEl.value = JSON.stringify(certData);
            
            // Apply colors to preview in real-time
            applyColorsToPreview(certData);
            
            // Trigger autosave if available
            if (typeof window.autosaveDebounced === 'function') {
                window.autosaveDebounced();
            } else if (typeof window.queueAutosave === 'function') {
                window.queueAutosave();
            } else if (typeof window.autosaveCertificate === 'function') {
                window.autosaveCertificate();
            }
        } catch (e) {
            console.error('Failed to update colors in JSON:', e);
        }
    }
    
    function applyColorsToPreview(certData) {
        const primary = certData.theme_primary || '#0b2f6b';
        const secondary = certData.theme_secondary || '#092557';
        const border = certData.theme_border || certData.theme_primary || '#0b2f6b';
        
        // Update text colors
        document.querySelectorAll('#cert_preview_wrapper .text-blue-900, #cert_preview_wrapper .text-violet-900, #cert_preview_wrapper .text-emerald-900')
            .forEach(el => { el.style.color = primary; });
        
        // Update gradient bands
        const bands = document.querySelectorAll('#cert_preview_wrapper .band-top, #cert_preview_wrapper .band-bottom');
        bands.forEach(el => { 
            el.style.backgroundImage = `linear-gradient(to right, ${primary}, ${secondary})`; 
        });
        
        // Update border color in real-time
        const pageElement = document.querySelector('#cert_preview_wrapper .page');
        if (pageElement) {
            pageElement.style.borderColor = border;
        }
    }
    
    // Initialize color sync
    syncCertificateColors();
    
    // Add event listeners for signatory input changes
    document.addEventListener('input', function(event) {
        if (event.target.matches('input[name^="sig_"][name$="_name"], input[name^="sig_"][name$="_title"]')) {
            // Update certificate data JSON with current values
            updateSignatoryDataInJSON();
            
            // Update preview if function exists
            if (typeof window.updateSignatoryPreview === 'function') {
                window.updateSignatoryPreview();
            }
            
            // Trigger autosave
            if (typeof window.autosaveDebounced === 'function') {
                window.autosaveDebounced();
            }
        }
    });
    
    // Function to update signatory data in JSON
    function updateSignatoryDataInJSON() {
        const certDataEl = document.getElementById('certificate_data_json');
        if (!certDataEl) return;
        
        try {
            const certData = JSON.parse(certDataEl.value || '{}');
            const signatories = document.querySelectorAll('.signatory-item');
            
            // Clear old signatory data
            for (let i = 1; i <= 4; i++) {
                delete certData[`sig_${i}_name`];
                delete certData[`sig_${i}_title`];
                // Don't delete signatures - preserve them
            }
            
            // Collect current values from all signatories
            signatories.forEach((item, index) => {
                const signatoryNum = index + 1;
                const nameInput = item.querySelector(`input[name="sig_${signatoryNum}_name"]`) || document.getElementById(`sig_${signatoryNum}_name_input`);
                const titleInput = item.querySelector(`input[name="sig_${signatoryNum}_title"]`) || document.getElementById(`sig_${signatoryNum}_title_input`);
                const statusEl = document.getElementById(`sig_${signatoryNum}_status`);
                
                if (nameInput) {
                    certData[`sig_${signatoryNum}_name`] = nameInput.value || '';
                }
                if (titleInput) {
                    certData[`sig_${signatoryNum}_title`] = titleInput.value || '';
                }
                
                // Preserve signature if it exists (don't overwrite)
                if (statusEl && (statusEl.textContent === 'Uploaded' || statusEl.textContent.includes('signature'))) {
                    const currentData = JSON.parse(certDataEl.value || '{}');
                    if (currentData[`sig_${signatoryNum}_signature`]) {
                        certData[`sig_${signatoryNum}_signature`] = currentData[`sig_${signatoryNum}_signature`];
                    }
                }
            });
            
            // Update signatory count
            certData.signatory_count = signatories.length;
            
            // Save back to hidden input
            certDataEl.value = JSON.stringify(certData);
            
            console.log('Updated signatory data:', {
                count: signatories.length,
                signatories: Object.keys(certData).filter(k => k.startsWith('sig_'))
            });
        } catch (e) {
            console.error('Failed to update signatory data in JSON:', e);
        }
    }
    
    // Expose function globally for use by other scripts
    window.updateSignatoryDataInJSON = updateSignatoryDataInJSON;
});

// Signature upload function - expose globally
window.uploadSignature = function uploadSignature(signatoryNum) {
    const fileInput = document.getElementById(`sig_${signatoryNum}_file`);
    const statusElement = document.getElementById(`sig_${signatoryNum}_status`);
    
    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        
        // Validate file type
        const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            if (typeof Swal !== 'undefined' && window.Swal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid File Type',
                    text: 'Please select a valid image file (PNG, JPG, or WebP)',
                    confirmButtonColor: '#10b981'
                });
            } else {
                alert('Please select a valid image file (PNG, JPG, or WebP)');
            }
            return;
        }
        
        // Validate file size (2MB)
        if (file.size > 2 * 1024 * 1024) {
            if (typeof Swal !== 'undefined' && window.Swal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'File Too Large',
                    text: 'File size must be less than 2MB',
                    confirmButtonColor: '#10b981'
                });
            } else {
                alert('File size must be less than 2MB');
            }
            return;
        }
        
        // Update status
        statusElement.textContent = 'Uploading...';
        statusElement.className = 'text-xs text-blue-500 dark:text-blue-400';
        
        // Create FormData for upload
        const formData = new FormData();
        formData.append('signature', file);
        formData.append('signatory_num', signatoryNum);
        formData.append('event_id', document.getElementById('event_id').value);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        // Upload signature
        fetch('/organizer/events/certificate/upload-signature', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                statusElement.textContent = 'Uploaded';
                statusElement.className = 'text-xs text-green-500 dark:text-green-400';
                
                // Update certificate data with signature path
                const certificateData = JSON.parse(document.getElementById('certificate_data_json').value || '{}');
                certificateData[`sig_${signatoryNum}_signature`] = data.signature_path;
                document.getElementById('certificate_data_json').value = JSON.stringify(certificateData);
                
                // Trigger autosave
                if (typeof window.queueAutosave === 'function') {
                    window.queueAutosave();
                }
            } else {
                statusElement.textContent = 'Upload failed';
                statusElement.className = 'text-xs text-red-500 dark:text-red-400';
                if (typeof Swal !== 'undefined' && window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Upload Failed',
                        text: data.message || 'Upload failed',
                    });
                } else {
                    alert(data.message || 'Upload failed');
                }
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            statusElement.textContent = 'Upload failed';
            statusElement.className = 'text-xs text-red-500 dark:text-red-400';
            if (typeof Swal !== 'undefined' && window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'Upload Failed',
                    text: 'Upload failed. Please try again.',
                });
            } else {
                alert('Upload failed. Please try again.');
            }
        });
    }
}
</script>