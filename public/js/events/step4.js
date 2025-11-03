// Step 4 - Certificate logic (migrated from inline script)
// Expects window.step4Config to be defined by the Blade view with:
// { eventId, existingUrl, routes: { upload, autosave, remove, stream, preview } }

(function(){
  if (typeof pdfjsLib !== 'undefined' && pdfjsLib.GlobalWorkerOptions) {
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
  }

  let pdfDoc = null;
  let currentScale = 1.0;
  let canvas = null;
  let ctx = null;
  let previewReady = false;

  function cfg(){ return (window.step4Config || {}); }

  document.addEventListener('DOMContentLoaded', function() {
    canvas = document.getElementById('pdf-canvas');
    if (!canvas) return; // Not on step 4
    ctx = canvas.getContext('2d');

    const existingUrl = cfg().existingUrl || '';
    if (existingUrl) { showPreviewSection(); loadPDF(makeStreamUrl()); }

    updateNamePosition();
    updateFontSettings();
    updatePreviewText();
    syncCertificateData();

    canvas.addEventListener('click', function(event) {
      const rect = canvas.getBoundingClientRect();
      const x = event.clientX - rect.left;
      const y = event.clientY - rect.top;
      document.getElementById('name_x_position').value = Math.round(x);
      document.getElementById('name_y_position').value = Math.round(y);
      updateNamePosition();
    });

    const overlay = document.getElementById('pdf-overlay');
    if (overlay) {
      overlay.addEventListener('click', function(event) {
        const rect = overlay.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;
        document.getElementById('name_x_position').value = Math.round(x);
        document.getElementById('name_y_position').value = Math.round(y);
        updateNamePosition();
      });
    }
  });

  function handleCertificateUpload(input) {
    const file = input.files && input.files[0];
    if (!file || file.type !== 'application/pdf') { 
      Swal.fire({
        icon: 'warning',
        title: 'Invalid File',
        text: 'Please select a valid PDF file.',
        showConfirmButton: false,
        timer: 2000
      });
      return; 
    }
    const localUrl = URL.createObjectURL(file);
    showPreviewSection();
    loadPDF(localUrl);
    try { document.getElementById('certificate_filename').textContent = file.name; } catch(_){}

    const body = new FormData();
    body.append('certificate_template', file);
    body.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    body.append('id', cfg().eventId);

    fetch(cfg().routes.upload + '?id=' + encodeURIComponent(cfg().eventId), {
      method: 'POST', body, headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
      if (!data || !data.success) throw new Error(data?.message || 'Upload failed');
      document.getElementById('certificate_path').value = data.certificate_path || data.file_path || '';
      document.getElementById('certificate_url').value = data.certificate_url || '';
      syncCertificateData();
      queueAutosave();
      loadPDF(makeStreamUrl());
    })
    .catch(err => { 
      console.error('Upload error:', err); 
      Swal.fire({
        icon: 'error',
        title: 'Upload Failed',
        text: 'Upload failed. Please try again.',
        showConfirmButton: false,
        timer: 3000
      });
    });
  }

  function showPreviewSection(){
    const up = document.getElementById('upload-section');
    const pv = document.getElementById('preview-section');
    if (up) up.classList.add('hidden');
    if (pv) pv.classList.remove('hidden');
  }
  function showUploadSection(){
    const up = document.getElementById('upload-section');
    const pv = document.getElementById('preview-section');
    if (up) up.classList.remove('hidden');
    if (pv) pv.classList.add('hidden');
  }

  function loadPDF(url){
    previewReady = false;
    const loading = document.getElementById('pdf-loading');
    if (loading) loading.style.display = 'flex';
    const task = (typeof pdfjsLib !== 'undefined') ? pdfjsLib.getDocument({ url, cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/', cMapPacked: true }) : null;
    if (!task){ fallbackRenderPDF(url); return; }
    task.promise.then(function(pdf){
      pdfDoc = pdf;
      return pdf.getPage(1).then(function(page){
        const viewport = page.getViewport({ scale: 1 });
        const container = document.getElementById('pdf-container');
        const scaleX = (container?.clientWidth || viewport.width) / viewport.width;
        const scaleY = (container?.clientHeight || viewport.height) / viewport.height;
        currentScale = Math.min(scaleX, scaleY, 1.0);
        const scaled = page.getViewport({ scale: currentScale });
        canvas.width = scaled.width; canvas.height = scaled.height;
        return page.render({ canvasContext: ctx, viewport: scaled }).promise.then(function(){
          if (loading) loading.style.display = 'none';
          previewReady = true; enableNext();
          const indicator = document.getElementById('position-indicator');
          const textPreview = document.getElementById('text-preview');
          if (indicator) indicator.style.display = 'block';
          if (textPreview) textPreview.style.display = 'block';
          updateNamePosition();
        });
      });
    }).catch(function(err){ console.warn('PDF.js load failed:', err); fallbackRenderPDF(url); });
  }

  function makeStreamUrl(){
    const loc = window.location;
    const base = loc.origin + cfg().routes.stream + '?id=' + encodeURIComponent(cfg().eventId);
    const cb = Date.now().toString();
    return base + '&cb=' + cb; // cache-buster to force latest file
  }

  function fallbackRenderPDF(url){
    try{
      const loader = document.getElementById('pdf-loading');
      const embed = document.getElementById('pdf-embed');
      const overlay = document.getElementById('pdf-overlay');
      const canvasEl = document.getElementById('pdf-canvas');
      if (canvasEl) canvasEl.classList.add('hidden');
      if (embed) embed.classList.remove('hidden');
      if (overlay) overlay.classList.remove('hidden');
      if (embed){
        embed.style.opacity = '0';
        embed.src = url + '#toolbar=0&navpanes=0&scrollbar=0';
        setTimeout(() => { if (loader) loader.style.display = 'none'; embed.style.opacity = '1'; }, 900);
      }
      previewReady = true; enableNext();
      const indicator = document.getElementById('position-indicator');
      const textPreview = document.getElementById('text-preview');
      if (indicator) indicator.style.display = 'block';
      if (textPreview) textPreview.style.display = 'block';
      updateNamePosition();
    } catch(e){ console.error('Fallback embed failed:', e); }
  }

  let autosaveTimer = null;
  function queueAutosave(){
    clearTimeout(autosaveTimer);
    autosaveTimer = setTimeout(() => {
      try {
        const body = new FormData();
        body.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        body.append('id', cfg().eventId);
        syncCertificateData();
        body.append('certificate_data_json', document.getElementById('certificate_data_json')?.value || '{}');
        fetch(cfg().routes.autosave + '?id=' + encodeURIComponent(cfg().eventId), { method: 'POST', body, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      } catch(_){ }
    }, 500);
  }

  function updateNamePosition(){
    const x = parseInt(document.getElementById('name_x_position').value || '100', 10);
    const y = parseInt(document.getElementById('name_y_position').value || '200', 10);
    const indicator = document.getElementById('position-indicator');
    const textPreview = document.getElementById('text-preview');
    if (indicator){ indicator.style.left = x + 'px'; indicator.style.top = y + 'px'; }
    if (textPreview){ textPreview.style.left = x + 'px'; textPreview.style.top = y + 'px'; }
    syncCertificateData();
    queueAutosave();
  }

  function updateFontSettings(){
    const fontFamily = document.getElementById('font_family').value;
    const fontSize = document.getElementById('font_size').value;
    const fontColor = document.getElementById('font_color').value;
    const textPreview = document.getElementById('text-preview');
    if (textPreview){
      textPreview.style.fontFamily = fontFamily;
      textPreview.style.fontSize = fontSize + 'px';
      textPreview.style.color = fontColor;
    }
    syncCertificateData();
    queueAutosave();
  }

  function enableNext(){
    const btn = document.getElementById('next-btn');
    const prev = document.getElementById('preview-btn');
    if (!btn) return;
    if ((document.getElementById('certificate_url').value || previewReady)){
      btn.disabled = false;
      btn.classList.remove('opacity-60', 'cursor-not-allowed', 'bg-indigo-600/60');
      btn.classList.add('bg-indigo-600', 'hover:bg-indigo-700', 'focus:bg-indigo-700');
      if (prev){
        prev.disabled = false;
        prev.classList.remove('opacity-60', 'cursor-not-allowed', 'bg-indigo-600/60');
        prev.classList.add('bg-indigo-600', 'hover:bg-indigo-700', 'focus:bg-indigo-700');
      }
    }
  }

  function openPreview(){
    const url = cfg().routes.preview + '?id=' + encodeURIComponent(cfg().eventId);
    window.open(url, '_blank');
  }

  function updatePreviewText(){
    const text = document.getElementById('preview_text').value || 'John Doe';
    const textPreview = document.getElementById('text-preview');
    if (textPreview) textPreview.textContent = text;
    syncCertificateData();
    queueAutosave();
  }

  function removeCertificate(){
    Swal.fire({
      title: 'Remove Certificate',
      text: 'Are you sure you want to remove the certificate template?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Yes, remove',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        fetch(cfg().routes.remove + '?id=' + encodeURIComponent(cfg().eventId), {
          method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
          if (!data || !data.success) throw new Error(data?.message || 'Remove failed');
          document.getElementById('certificate_path').value = '';
          document.getElementById('certificate_url').value = '';
          syncCertificateData();
          queueAutosave();
          const file = document.getElementById('certificate_file');
          if (file) file.value = '';
          showUploadSection();
          Swal.fire({
            icon: 'success',
            title: 'Removed',
            text: 'Certificate template removed successfully.',
            showConfirmButton: false,
            timer: 2000
          });
        })
        .catch(err => { 
          console.error('Remove error:', err); 
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to remove certificate.',
            showConfirmButton: false,
            timer: 3000
          });
        });
      }
    });
  }

  function syncCertificateData(){
    // Collect all signatory data
    const signatories = {};
    const signatoryItems = document.querySelectorAll('.signatory-item');
    signatoryItems.forEach((item, index) => {
      const signatoryNum = index + 1;
      const nameInput = item.querySelector(`input[name="sig_${signatoryNum}_name"]`);
      const titleInput = item.querySelector(`input[name="sig_${signatoryNum}_title"]`);
      const statusElement = document.getElementById(`sig_${signatoryNum}_status`);
      
      if (nameInput && titleInput) {
        signatories[`sig_${signatoryNum}_name`] = nameInput.value || '';
        signatories[`sig_${signatoryNum}_title`] = titleInput.value || '';
        
        // Include signature path if uploaded
        if (statusElement && statusElement.textContent === 'Uploaded') {
          // Get signature path from certificate data if available
          const currentData = JSON.parse(document.getElementById('certificate_data_json')?.value || '{}');
          signatories[`sig_${signatoryNum}_signature`] = currentData[`sig_${signatoryNum}_signature`] || '';
        }
      }
    });

    // Collect organization data
    const orgData = {
      org_line1: document.getElementById('org_line1_input')?.value || '',
      org_line2: document.getElementById('org_line2_input')?.value || '',
      org_school: document.getElementById('org_school_input')?.value || '',
      recipient_name: document.getElementById('recipient_name_input')?.value || '',
      theme_primary: document.getElementById('theme_primary')?.value || '#0b2f6b',
      theme_secondary: document.getElementById('theme_secondary')?.value || '#092557',
      logo_left_url: document.getElementById('logo_left_preview')?.src || '',
      logo_right_url: document.getElementById('logo_right_preview')?.src || ''
    };

    const data = {
      font_family: document.getElementById('font_family')?.value || 'Inter',
      font_size: parseInt(document.getElementById('font_size')?.value || '24', 10),
      font_color: document.getElementById('font_color')?.value || '#000000',
      name_x_position: (parseInt(document.getElementById('name_x_position')?.value || '100', 10)) / (currentScale || 1),
      name_y_position: (parseInt(document.getElementById('name_y_position')?.value || '200', 10)) / (currentScale || 1),
      preview_text: document.getElementById('preview_text')?.value || 'John Doe',
      certificate_path: document.getElementById('certificate_path')?.value || '',
      certificate_url: document.getElementById('certificate_url')?.value || '',
      signatory_count: signatoryItems.length,
      ...orgData,
      ...signatories
    };
    const target = document.getElementById('certificate_data_json');
    if (target) target.value = JSON.stringify(data);
  }

  (function attachFormSync(){
    try{
      const form = document.querySelector('form[action*="certificate/store"]');
      if (form){ form.addEventListener('submit', function(){ syncCertificateData(); }); }
    } catch(_){ }
  })();

  // Live preview functions for signatories
  function syncAllSignatories() {
    const signatoryItems = document.querySelectorAll('.signatory-item');
    signatoryItems.forEach((item, index) => {
      const signatoryNum = index + 1;
      const nameInput = item.querySelector(`input[name="sig_${signatoryNum}_name"]`);
      const titleInput = item.querySelector(`input[name="sig_${signatoryNum}_title"]`);
      
      if (nameInput && titleInput) {
        // Update preview elements if they exist
        const previewName = document.getElementById(`sig_${signatoryNum}_name`);
        const previewTitle = document.getElementById(`sig_${signatoryNum}_title`);
        
        if (previewName) previewName.textContent = nameInput.value || `Signatory ${signatoryNum}`;
        if (previewTitle) previewTitle.textContent = titleInput.value || 'Signature';
      }
    });
    syncCertificateData();
    queueAutosave();
  }

  function updateSignatoryPreview() {
    syncAllSignatories();
  }

  // Initialize signatory event listeners
  function initializeSignatoryListeners() {
    // Listen for input changes on signatory fields
    document.addEventListener('input', function(event) {
      if (event.target.matches('input[name^="sig_"][name$="_name"], input[name^="sig_"][name$="_title"]')) {
        updateSignatoryPreview();
      }
    });

    // Listen for changes in the signatory container (add/remove)
    const signatoryContainer = document.getElementById('signatory-details-container');
    if (signatoryContainer) {
      const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
          if (mutation.type === 'childList') {
            updateSignatoryPreview();
          }
        });
      });
      observer.observe(signatoryContainer, { childList: true, subtree: true });
    }
  }

  // Initialize on DOM ready
  document.addEventListener('DOMContentLoaded', function() {
    initializeSignatoryListeners();
  });

  // Expose to window for inline handlers
  window.handleCertificateUpload = handleCertificateUpload;
  window.showPreviewSection = showPreviewSection;
  window.showUploadSection = showUploadSection;
  window.loadPDF = loadPDF;
  window.updateNamePosition = updateNamePosition;
  window.updateFontSettings = updateFontSettings;
  window.updatePreviewText = updatePreviewText;
  window.removeCertificate = removeCertificate;
  window.queueAutosave = queueAutosave;
  window.syncCertificateData = syncCertificateData;
  window.openPreview = openPreview;
  window.syncAllSignatories = syncAllSignatories;
  window.updateSignatoryPreview = updateSignatoryPreview;
})();


