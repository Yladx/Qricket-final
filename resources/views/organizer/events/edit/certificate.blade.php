<x-unified-layout :layout-type="$layoutType" :title="'Edit Event - Certificate'" :page-title="'Edit Certificate'">
    <x-slot name="header">
        <x-manage-header :event="$event" />
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <form id="editCertificateForm" method="POST" action="{{ route('organizer.events.certificate.update', $event) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="skip_certificate" id="skip_certificate_hidden" value="0">
                
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
                        <button type="button" onclick="window.print()" class="px-3 py-1.5 text-xs rounded bg-qricket-500 text-white hover:bg-qricket-600">Print</button>
                        <span class="text-xs text-gray-500">Click to collapse/expand</span>
                    </div>
                </summary>
                <div class="p-4">
                    <div id="cert_preview_wrapper">
                        @include('organizer.events.preview.certificate-template', [
                            'certificateData' => $certificateData, 
                            'event' => $event ?? (object)['id' => $event->id ?? request()->get('id')], 
                            'fallbackWhen' => $fallbackWhen, 
                            'fallbackGiven' => $fallbackGiven
                        ])
                    </div>
                </div>
            </details>

            <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('organizer.events.manage', $event) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-medium text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-qricket-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Manage
                </a>

                <div class="flex items-center space-x-3">
                    <button type="submit" form="editCertificateForm" class="inline-flex items-center px-4 py-2 bg-qricket-500 text-white rounded-md font-medium text-xs hover:bg-qricket-600 focus:outline-none focus:ring-2 focus:ring-qricket-500">
                        Save Certificate
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </button>
                </div>
            </div>
            </form>
        </div>
    </div>

    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const eventId = {{ $eventId ?? 'null' }};
        window.currentEventId = {{ $event->id }};

        function syncPreview(idInput, idPreview){
            const elI = document.getElementById(idInput);
            const elP = document.getElementById(idPreview);
            if (!elI || !elP) return;
            elI.addEventListener('input', () => { elP.textContent = elI.value; });
        }
        // Sync preview for basic fields
        ['org_line1','org_line2','org_school','recipient_name','event_title','event_when','event_given']
            .forEach(k => syncPreview(`${k}_input`, k));
        
        // Sync preview for dynamic signatory fields
        function syncAllSignatories() {
            document.querySelectorAll('input[name^="sig_"][name$="_name"], input[name^="sig_"][name$="_title"]').forEach(input => {
                const fieldName = input.name;
                const previewId = fieldName.replace('_input', '').replace('sig_', 'sig_');
                syncPreview(fieldName, previewId);
            });
        }
        
        // Update preview when signatories change
        function updateSignatoryPreview() {
            // Remove all existing signatory previews
            document.querySelectorAll('[id^="sig_"][id$="_name"], [id^="sig_"][id$="_title"]').forEach(el => {
                el.textContent = '';
            });
            
            // Add current signatories to preview
            const signatoryItems = document.querySelectorAll('.signatory-item');
            signatoryItems.forEach((item, index) => {
                const signatoryNum = index + 1;
                const nameInput = item.querySelector(`input[name="sig_${signatoryNum}_name"]`);
                const titleInput = item.querySelector(`input[name="sig_${signatoryNum}_title"]`);
                
                if (nameInput) {
                    const namePreview = document.getElementById(`sig_${signatoryNum}_name`);
                    if (namePreview) {
                        namePreview.textContent = nameInput.value || `Signatory ${signatoryNum}`;
                    }
                }
                
                if (titleInput) {
                    const titlePreview = document.getElementById(`sig_${signatoryNum}_title`);
                    if (titlePreview) {
                        titlePreview.textContent = titleInput.value || 'Signature';
                    }
                }
            });
        }
        
        // Initial sync
        syncAllSignatories();
        updateSignatoryPreview();
        
        // Re-sync when signatories are added/removed
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    syncAllSignatories();
                    updateSignatoryPreview();
                }
            });
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
        // Autosave helpers (debounced)
        const debounce = (fn, d=600)=>{ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), d); }; };
        const statusEl = document.getElementById('cert_autosave_status');
        async function autosaveCert(){
            try {
                if (statusEl) statusEl.textContent = 'Saving…';
                const payload = collectPayload();
                // Persist JSON to R2 via controller autosave endpoint
                const autosaveUrl = '{{ route("organizer.events.certificate.autosave", [], false) }}' + '?id=' + encodeURIComponent(eventId);
                await postJSON(autosaveUrl, { certificate_data_json: JSON.stringify(payload) });
                if (statusEl) statusEl.textContent = 'Saved';
                refreshPreview(payload);
                setTimeout(()=>{ if (statusEl) statusEl.textContent=''; }, 1200);
            } catch(e) {
                if (statusEl) statusEl.textContent = 'Save failed';
                console.error('Certificate autosave failed', e);
            }
        }
        const autosaveDebounced = debounce(autosaveCert, 700);

        async function postJSON(url, payload){
            const resp = await fetch(url, { method:'POST', headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify(payload) });
            return resp.json();
        }

        window.uploadLogo = async function(side){
            const file = document.getElementById(`logo_${side}_file`).files[0];
            if (!file) return;
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
                    // Show small preview/status near inputs
                    const pv = document.getElementById(`logo_${side}_preview`);
                    const st = document.getElementById(`logo_${side}_status`);
                    if (pv) { pv.src = data.url; pv.classList.remove('hidden'); }
                    if (st) { st.textContent = 'Uploaded'; }
                autosaveDebounced();
            }
        }

        window.uploadSignature = async function(which){
            const file = document.getElementById(`sig_${which}_file`).files[0];
            if (!file) return;
            const fd = new FormData();
            fd.append('id', eventId);
            fd.append('which', which);
            fd.append('image', file);
            const resp = await fetch('/organizer/events/certificate/upload-signature', { method:'POST', headers:{ 'X-CSRF-TOKEN': csrf }, body: fd });
            const res = await resp.json();
            if (res && res.success) {
                const stId = which === 'left' ? 'sig_left_status' : 'sig_right_status';
                const st = document.getElementById(stId);
                if (st) st.textContent = 'Uploaded';
                autosaveDebounced();
            }
        }

        function collectPayload(){
            return {
                org_line1: document.getElementById('org_line1_input')?.value || '',
                org_line2: document.getElementById('org_line2_input')?.value || '',
                org_line3: document.getElementById('org_line3_input')?.value || '',
                org_school: document.getElementById('org_school_input')?.value || '',
                recipient_name: document.getElementById('recipient_name_input')?.value || '',
                // event title/when/given are auto
                sig_left_name: document.getElementById('sig_left_name_input')?.value || '',
                sig_left_title: document.getElementById('sig_left_title_input')?.value || '',
                sig_right_name: document.getElementById('sig_right_name_input')?.value || '',
                sig_right_title: document.getElementById('sig_right_title_input')?.value || '',
                theme_primary: document.getElementById('theme_primary')?.value || '#0b2f6b',
                theme_secondary: document.getElementById('theme_secondary')?.value || '#092557',
            };
        }

        function refreshPreview(payload){
            // Update text fields inline
            const map = {
                org_line1: 'org_line1', org_line2: 'org_line2', org_line3: 'org_line3',
                org_school: 'org_school', recipient_name: 'recipient_name',
                sig_left_name: 'sig_left_name', sig_left_title: 'sig_left_title',
                sig_right_name: 'sig_right_name', sig_right_title: 'sig_right_title',
                event_title: 'event_title'
            };
            Object.keys(map).forEach(k=>{
                const el = document.getElementById(map[k]);
                if (el && payload[k] !== undefined) el.textContent = payload[k];
            });
            // Apply colors
            const primary = payload.theme_primary;
            document.querySelectorAll('#cert_preview_wrapper .text-blue-900, #cert_preview_wrapper .text-violet-900, #cert_preview_wrapper .text-emerald-900')
                .forEach(el=>{ el.style.color = primary; });
            const bands = document.querySelectorAll('#cert_preview_wrapper .band-top, #cert_preview_wrapper .band-bottom');
            bands.forEach(el=>{ el.style.backgroundImage = `linear-gradient(to right, ${payload.theme_primary}, ${payload.theme_secondary})`; });
        }

        // Wire autosave on inputs
        ['org_line1_input','org_line2_input','org_school_input','recipient_name_input','sig_left_name_input','sig_left_title_input','sig_right_name_input','sig_right_title_input','theme_primary','theme_secondary']
            .forEach(id=>{ const el = document.getElementById(id); el && el.addEventListener('input', autosaveDebounced); el && el.addEventListener('change', autosaveDebounced); });

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
                    }
                    if (previewSection) {
                        previewSection.style.display = 'none';
                    }
                } else {
                    // Show form and preview if checkbox is not checked
                    if (formContainer) {
                        formContainer.style.display = 'block';
                    }
                    if (previewSection) {
                        previewSection.style.display = 'block';
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
                
                if (box.checked) {
                    // Hide form and preview when skipping
                    if (formContainer) {
                        formContainer.style.display = 'none';
                    }
                    if (previewSection) {
                        previewSection.style.display = 'none';
                    }
                } else {
                    // Show form and preview when not skipping
                    if (formContainer) {
                        formContainer.style.display = 'block';
                    }
                    if (previewSection) {
                        previewSection.style.display = 'block';
                        previewSection.setAttribute('open', 'open');
                    }
                }
            });
            
            // Update visibility after loading the setting
            loadCertificateSetting().then(() => {
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
                } catch (error) {
                    console.error('Certificate autosave failed:', error);
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
        @media print {
            /* Hide everything except the certificate preview area */
            body * { visibility: hidden !important; }
            #cert_preview_wrapper, #cert_preview_wrapper * { visibility: visible !important; }
            /* Ensure the preview fills the page */
            #cert_preview_wrapper { position: absolute; left: 0; top: 0; right: 0; margin: 0; padding: 0; }
            /* Expand details container and remove summary */
            #cert_section, #cert_section > div { overflow: visible !important; }
            #cert_section > summary { display: none !important; }
        }
    </style>
</x-unified-layout>