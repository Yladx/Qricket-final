/* Step 6 - Review & Publish with preview functionality */
document.addEventListener('DOMContentLoaded', function() {
  const previewEventBtn = document.getElementById('preview-event-btn');
  const previewCertificateBtn = document.getElementById('preview-certificate-btn');
  
  // Event preview functionality
  if (previewEventBtn) {
    previewEventBtn.addEventListener('click', function() {
      // Get event ID from URL
      const urlParams = new URLSearchParams(window.location.search);
      const eventId = urlParams.get('id');
      
      if (eventId) {
        // Show inline modal preview instead of new window
        showEventPreviewModal(eventId);
      } else {
        if (typeof Swal !== 'undefined' && window.Swal) {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Event ID not found. Please try again.',
          });
        } else {
          alert('Event ID not found. Please try again.');
        }
      }
    });
  }
  
  // Function to show event preview in modal
  function showEventPreviewModal(eventId) {
    // Create modal overlay
    const modalOverlay = document.createElement('div');
    modalOverlay.className = 'fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4';
    modalOverlay.innerHTML = `
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Event Preview</h3>
          <button type="button" class="close-preview text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </header>
        <div class="preview-content" style="height: 70vh;">
          <div class="flex items-center justify-center h-full">
            <svg class="animate-spin h-8 w-8 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="ml-2 text-gray-600 dark:text-gray-400">Loading preview...</span>
          </div>
        </div>
      </div>
    `;
    
    // Add modal to page
    document.body.appendChild(modalOverlay);
    document.body.style.overflow = 'hidden';
    
    // Load preview content via iframe
    const previewUrl = `/organizer/events/create/layout/preview?id=${eventId}`;
    const previewContent = modalOverlay.querySelector('.preview-content');
    
    setTimeout(() => {
      previewContent.innerHTML = `
        <iframe src="${previewUrl}" 
                class="w-full h-full border-0" 
                frameborder="0"
                title="Event Preview">
        </iframe>
      `;
    }, 500);
    
    // Close modal functionality
    const closeBtn = modalOverlay.querySelector('.close-preview');
    closeBtn.addEventListener('click', closeModal);
    
    modalOverlay.addEventListener('click', function(e) {
      if (e.target === modalOverlay) {
        closeModal();
      }
    });
    
    // Close on escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeModal();
      }
    });
    
    function closeModal() {
      document.body.removeChild(modalOverlay);
      document.body.style.overflow = '';
      // Remove event listener
      document.removeEventListener('keydown', arguments.callee);
    }
  }
  
  // Certificate preview functionality
  if (previewCertificateBtn) {
    console.log('Certificate preview button found, adding event listener');
    previewCertificateBtn.addEventListener('click', function() {
      console.log('Certificate preview button clicked');
      // Get event ID from URL
      const urlParams = new URLSearchParams(window.location.search);
      const eventId = urlParams.get('id');
      
      console.log('Event ID from URL:', eventId);
      
      if (eventId) {
        // Show inline certificate preview modal
        showCertificatePreviewModal(eventId);
      } else {
        if (typeof Swal !== 'undefined' && window.Swal) {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Event ID not found. Please try again.',
          });
        } else {
          alert('Event ID not found. Please try again.');
        }
      }
    });
  } else {
    console.log('Certificate preview button not found');
  }
  
  // Function to show certificate preview in modal
  function showCertificatePreviewModal(eventId) {
    console.log('showCertificatePreviewModal called with eventId:', eventId);
    
    // Create modal overlay
    const modalOverlay = document.createElement('div');
    modalOverlay.className = 'fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4';
    modalOverlay.innerHTML = `
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Certificate Preview</h3>
          <button type="button" class="close-certificate-preview text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <div class="certificate-preview-content" style="height: 65vh;">
          <div class="flex items-center justify-center h-full">
            <svg class="animate-spin h-8 w-8 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="ml-2 text-gray-600 dark:text-gray-400">Loading certificate...</span>
          </div>
        </div>
      </div>
    `;
    
    // Add modal to page
    document.body.appendChild(modalOverlay);
    document.body.style.overflow = 'hidden';
    
    // Load certificate preview content
    const previewUrl = `/organizer/events/create/certificate/preview?id=${eventId}`;
    const previewContent = modalOverlay.querySelector('.certificate-preview-content');
    
    setTimeout(() => {
      previewContent.innerHTML = `
        <iframe src="${previewUrl}" 
                class="w-full h-full border-0" 
                frameborder="0"
                title="Certificate Preview">
        </iframe>
      `;
    }, 500);
    
    // Close modal functionality
    const closeBtn = modalOverlay.querySelector('.close-certificate-preview');
    closeBtn.addEventListener('click', closeCertificateModal);
    
    modalOverlay.addEventListener('click', function(e) {
      if (e.target === modalOverlay) {
        closeCertificateModal();
      }
    });
    
    // Close on escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeCertificateModal();
      }
    });
    
    function closeCertificateModal() {
      document.body.removeChild(modalOverlay);
      document.body.style.overflow = '';
      // Remove event listener
      document.removeEventListener('keydown', arguments.callee);
    }
  }
  
  // Publish option handling
  const publishOptions = document.querySelectorAll('input[name="publish_option"]');
  const publishAtInput = document.querySelector('input[name="publish_at"]');
  const scheduleInputDiv = document.getElementById('schedule-input');
  
  // Only add event listeners if elements exist
  if (publishOptions.length > 0) {
    publishOptions.forEach(option => {
      option.addEventListener('change', function() {
        if (this.value === 'scheduled') {
          if (scheduleInputDiv) {
            scheduleInputDiv.classList.remove('hidden');
          }
          if (publishAtInput) {
            publishAtInput.required = true;
          }
        } else {
          if (scheduleInputDiv) {
            scheduleInputDiv.classList.add('hidden');
          }
          if (publishAtInput) {
            publishAtInput.required = false;
          }
        }
      });
    });
  }
  
  // Initialize publish at input visibility
  const selectedOption = document.querySelector('input[name="publish_option"]:checked');
  if (selectedOption && selectedOption.value === 'scheduled') {
    if (scheduleInputDiv) {
      scheduleInputDiv.classList.remove('hidden');
    }
    if (publishAtInput) {
      publishAtInput.required = true;
    }
  } else {
    if (scheduleInputDiv) {
      scheduleInputDiv.classList.add('hidden');
    }
    if (publishAtInput) {
      publishAtInput.required = false;
    }
  }
  
  // Form submission handling
  const form = document.querySelector('form');
  if (form) {
    form.addEventListener('submit', function(e) {
      const selectedPublishOption = document.querySelector('input[name="publish_option"]:checked');
      
      if (selectedPublishOption) {
        const publishOption = selectedPublishOption.value;
        
        // Show confirmation dialog based on publish option
        let confirmMessage = '';
        switch (publishOption) {
          case 'draft':
            confirmMessage = 'Save this event as a draft?';
            break;
          case 'pending_approval':
            confirmMessage = 'Submit this event for admin approval?';
            break;
          case 'scheduled':
            const publishAt = publishAtInput.value;
            if (!publishAt) {
              e.preventDefault();
              if (typeof Swal !== 'undefined' && window.Swal) {
                Swal.fire({
                  icon: 'warning',
                  title: 'Publish Date Required',
                  text: 'Please select a publish date and time.',
                  confirmButtonColor: '#10b981'
                });
              } else {
                alert('Please select a publish date and time.');
              }
              return;
            }
            confirmMessage = `Schedule this event to be published on ${new Date(publishAt).toLocaleString()}?`;
            break;
          default:
            confirmMessage = 'Publish this event?';
        }
        
        if (!confirm(confirmMessage)) {
          e.preventDefault();
        }
      }
    });
  }
  
  // Add loading state to form submission
  if (form) {
    form.addEventListener('submit', function() {
      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processing...';
      }
    });
  }