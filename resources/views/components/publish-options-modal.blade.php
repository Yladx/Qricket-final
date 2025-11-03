@props(['eventId'])

<!-- Publish Options Modal -->
<div id="publish-options-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Publish Options</h3>
                <button type="button" id="close-publish-modal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="space-y-4">
                <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
                    <label class="flex items-center space-x-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                        <input type="radio" name="publish_option" value="draft" checked class="text-blue-600 focus:ring-blue-500">
                        <div>
                            <div class="font-medium">Save as Draft</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Save your event as a draft to continue editing later</div>
                        </div>
                    </label>
                    
                    <label class="flex items-center space-x-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                        <input type="radio" name="publish_option" value="pending_approval" class="text-blue-600 focus:ring-blue-500">
                        <div>
                            <div class="font-medium">Submit for Approval</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Submit your event for admin review and approval</div>
                        </div>
                    </label>
                    
                    <label class="flex items-center space-x-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                        <input type="radio" name="publish_option" value="scheduled" class="text-blue-600 focus:ring-blue-500">
                        <div>
                            <div class="font-medium">Schedule Publication</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Schedule your event to be published at a specific time</div>
                        </div>
                    </label>
                </div>

                <!-- Schedule DateTime Input -->
                <div id="schedule-input" class="mt-4 hidden">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Publish At</label>
                    <input type="datetime-local" name="publish_at" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end space-x-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" id="cancel-publish" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-400 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors">
                    Cancel
                </button>
                <button type="button" id="confirm-publish" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                    <span id="publish-button-text">Create Event</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('publish-options-modal');
    const closeBtn = document.getElementById('close-publish-modal');
    const cancelBtn = document.getElementById('cancel-publish');
    const confirmBtn = document.getElementById('confirm-publish');
    const publishOptions = document.querySelectorAll('input[name="publish_option"]');
    const scheduleInput = document.getElementById('schedule-input');
    const publishButtonText = document.getElementById('publish-button-text');
    const originalSubmitBtn = document.querySelector('button[type="submit"]');

    // Show modal when Create Event button is clicked
    const createEventBtn = document.getElementById('create-event-btn');
    if (createEventBtn) {
        createEventBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Create Event button clicked, showing modal');
            modal.classList.remove('hidden');
        });
    } else {
        console.error('Create Event button not found');
    }

    // Hide modal
    function hideModal() {
        modal.classList.add('hidden');
    }

    closeBtn.addEventListener('click', hideModal);
    cancelBtn.addEventListener('click', hideModal);

    // Handle publish option changes
    publishOptions.forEach(option => {
        option.addEventListener('change', function() {
            if (this.value === 'scheduled') {
                scheduleInput.classList.remove('hidden');
            } else {
                scheduleInput.classList.add('hidden');
            }

            // Update button text based on selection
            switch(this.value) {
                case 'draft':
                    publishButtonText.textContent = 'Save as Draft';
                    break;
                case 'pending_approval':
                    publishButtonText.textContent = 'Submit for Approval';
                    break;
                case 'scheduled':
                    publishButtonText.textContent = 'Schedule Publication';
                    break;
            }
        });
    });

    // Handle confirm publish
    confirmBtn.addEventListener('click', function() {
        // Get selected option
        const selectedOption = document.querySelector('input[name="publish_option"]:checked');
        const publishAt = document.querySelector('input[name="publish_at"]').value;

        // Validate scheduled option
        if (selectedOption.value === 'scheduled' && !publishAt) {
            if (typeof Swal !== 'undefined' && window.Swal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Publish Date Required',
                    text: 'Please select a publish date and time for scheduled publication.',
                    confirmButtonColor: '#10b981'
                });
            } else {
                alert('Please select a publish date and time for scheduled publication.');
            }
            return;
        }

        // Submit the form
        const form = document.querySelector('form');
        if (form) {
            form.submit();
        }
    });

    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            hideModal();
        }
    });
});
</script>
