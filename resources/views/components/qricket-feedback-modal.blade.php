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

            <!-- Modal Footer -->
            <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex justify-between">
                <button onclick="dismissFeedbackModal()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md transition-colors">
                    Maybe Later
                </button>
                <button onclick="openFullFeedback()" class="px-4 py-2 text-sm font-medium text-qricket-600 dark:text-qricket-400 hover:text-qricket-700 dark:hover:text-qricket-300 transition-colors">
                    Give Detailed Feedback
                </button>
            </div>
        </div>
    </div>
</div>

<script>
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

document.addEventListener('DOMContentLoaded', function() {
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
</script>
