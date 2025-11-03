<!-- Qricket Feedback Modal -->
<div id="qricketFeedbackModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Rate Qricket Website</h3>
                    <button onclick="closeFeedbackModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Help us improve your experience</p>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
                <div class="space-y-4">
                    <!-- Quick Rating -->
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">How would you rate your overall experience?</p>
                        <div class="flex space-x-1">
                            <button type="button" class="star-btn text-2xl text-gray-300 hover:text-yellow-400 transition-colors" data-rating="1">★</button>
                            <button type="button" class="star-btn text-2xl text-gray-300 hover:text-yellow-400 transition-colors" data-rating="2">★</button>
                            <button type="button" class="star-btn text-2xl text-gray-300 hover:text-yellow-400 transition-colors" data-rating="3">★</button>
                            <button type="button" class="star-btn text-2xl text-gray-300 hover:text-yellow-400 transition-colors" data-rating="4">★</button>
                            <button type="button" class="star-btn text-2xl text-gray-300 hover:text-yellow-400 transition-colors" data-rating="5">★</button>
                        </div>
                    </div>

                    <!-- Quick Comment -->
                    <div>
                        <label for="quickComment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Any quick feedback? (Optional)</label>
                        <textarea id="quickComment" rows="3" class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-qricket-500 focus:border-qricket-500 dark:bg-gray-700 dark:text-white" placeholder="Share your thoughts..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex justify-between">
                <button onclick="dismissFeedbackModal()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md transition-colors">
                    Maybe Later
                </button>
                <div class="space-x-2">
                    <button onclick="openFullFeedback()" class="px-4 py-2 text-sm font-medium text-qricket-600 dark:text-qricket-400 hover:text-qricket-700 dark:hover:text-qricket-300 transition-colors">
                        Give Detailed Feedback
                    </button>
                    <button onclick="submitQuickFeedback()" class="px-4 py-2 text-sm font-medium text-white bg-qricket-500 hover:bg-qricket-600 rounded-md transition-colors">
                        Submit Rating
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let selectedRating = 0;

// Check if feedback modal should be shown
function checkFeedbackModal() {
    // Don't show if already dismissed today
    const dismissedDate = localStorage.getItem('qricket_feedback_dismissed_date');
    if (dismissedDate === new Date().toDateString()) {
        return;
    }

    // Don't show if already gave feedback today
    const feedbackDate = localStorage.getItem('qricket_feedback_given_date');
    if (feedbackDate === new Date().toDateString()) {
        return;
    }

    // Check if user has performed trigger actions
    fetch('/feedback/qricket/should-show', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.should_show) {
            setTimeout(() => {
                document.getElementById('qricketFeedbackModal').classList.remove('hidden');
            }, 2000); // Show after 2 seconds
        }
    })
    .catch(error => {
        console.log('Feedback modal check failed:', error);
    });
}

// Star rating functionality
document.addEventListener('DOMContentLoaded', function() {
    const starButtons = document.querySelectorAll('.star-btn');
    
    starButtons.forEach((button, index) => {
        button.addEventListener('click', function() {
            selectedRating = index + 1;
            
            // Update visual state
            starButtons.forEach((btn, i) => {
                if (i < selectedRating) {
                    btn.classList.add('text-yellow-400');
                    btn.classList.remove('text-gray-300');
                } else {
                    btn.classList.remove('text-yellow-400');
                    btn.classList.add('text-gray-300');
                }
            });
        });

        button.addEventListener('mouseenter', function() {
            const rating = index + 1;
            starButtons.forEach((btn, i) => {
                if (i < rating) {
                    btn.classList.add('text-yellow-400');
                    btn.classList.remove('text-gray-300');
                }
            });
        });
    });

    // Reset stars on mouse leave
    document.querySelector('.flex.space-x-1').addEventListener('mouseleave', function() {
        if (selectedRating === 0) {
            starButtons.forEach(btn => {
                btn.classList.remove('text-yellow-400');
                btn.classList.add('text-gray-300');
            });
        }
    });

    // Check if modal should be shown
    checkFeedbackModal();
});

function closeFeedbackModal() {
    document.getElementById('qricketFeedbackModal').classList.add('hidden');
}

function dismissFeedbackModal() {
    localStorage.setItem('qricket_feedback_dismissed_date', new Date().toDateString());
    closeFeedbackModal();
}

function openFullFeedback() {
    window.open('/feedback/qricket', '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
    closeFeedbackModal();
}

function submitQuickFeedback() {
    if (selectedRating === 0) {
        if (typeof Swal !== 'undefined' && window.Swal) {
            Swal.fire({
                icon: 'warning',
                title: 'Select Rating',
                text: 'Please select a rating before submitting.',
                confirmButtonColor: '#10b981'
            });
        } else {
            alert('Please select a rating');
        }
        return;
    }

    const comment = document.getElementById('quickComment').value;
    
    const formData = new FormData();
    formData.append('process_satisfaction', selectedRating);
    if (comment.trim()) {
        formData.append('additional_comments', comment);
    }

    fetch('/feedback/qricket', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            localStorage.setItem('qricket_feedback_given_date', new Date().toDateString());
            closeFeedbackModal();
            
            // Show success message
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Thank You!',
                    text: 'Your feedback has been submitted.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        } else {
            if (typeof Swal !== 'undefined' && window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed',
                    text: 'Failed to submit feedback. Please try again.',
                });
            } else {
                alert('Failed to submit feedback. Please try again.');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof Swal !== 'undefined' && window.Swal) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to submit feedback. Please try again.',
            });
        } else {
            alert('Failed to submit feedback. Please try again.');
        }
    });
}
</script>
