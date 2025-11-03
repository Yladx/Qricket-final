/**
 * SweetAlert Utility Functions
 * 
 * Universal notification system for consistent user feedback across the application.
 * All functions are available globally and can be called from anywhere.
 * 
 * USAGE EXAMPLES:
 * 
 * // Success notifications
 * showSuccess('Success!', 'Operation completed successfully.');
 * toastSuccess('Data saved successfully!');
 * 
 * // Error notifications
 * showError('Error!', 'Something went wrong. Please try again.');
 * toastError('Failed to save data!');
 * 
 * // Warning notifications
 * showWarning('Warning!', 'Please check your input.');
 * toastWarning('Invalid input detected!');
 * 
 * // Info notifications
 * showInfo('Information', 'Here is some helpful information.');
 * toastInfo('New feature available!');
 * 
 * // Loading states
 * showLoading('Processing...', 'Please wait...');
 * hideLoading(); // Call this when done
 * 
 * // Confirmations
 * showConfirm('Are you sure?', 'This action cannot be undone.')
 *   .then((result) => {
 *     if (result.isConfirmed) {
 *       // User confirmed
 *     }
 *   });
 * 
 * // Custom options
 * showSuccess('Custom Title', 'Custom message', {
 *   autoClose: false,  // Don't auto-close
 *   position: 'top',   // Position
 *   toast: true        // Show as toast
 * });
 */
export function confirmLogout(formId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to logout from your account?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, logout!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Logging out...',
                text: 'Please wait while we log you out.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Submit the form
            document.getElementById(formId).submit();
        }
    });
}

export function confirmAccountDeletion() {
    Swal.fire({
        title: 'Delete Account?',
        text: "This action cannot be undone. All your data will be permanently deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete my account!',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        input: 'password',
        inputLabel: 'Enter your password to confirm',
        inputPlaceholder: 'Your password',
        inputValidator: (value) => {
            if (!value) {
                return 'You need to enter your password!';
            }
        },
        showLoaderOnConfirm: true,
        preConfirm: (password) => {
            // Set the password in the hidden form
            document.getElementById('delete-password').value = password;
            
            // Show loading state
            return new Promise((resolve) => {
                setTimeout(() => {
                    resolve();
                }, 1000);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            // Show final confirmation
            Swal.fire({
                title: 'Final Confirmation',
                text: 'Are you absolutely sure you want to delete your account? This action is irreversible!',
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, I understand. Delete it!',
                cancelButtonText: 'No, keep my account',
                reverseButtons: true
            }).then((finalResult) => {
                if (finalResult.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: 'Deleting Account...',
                        text: 'Please wait while we delete your account.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Submit the form
                    document.getElementById('delete-account-form').submit();
                }
            });
        }
    });
}

export function confirmSubscription(planId, planName, planPrice) {
    Swal.fire({
        title: 'Confirm Subscription',
        html: `
            <div class="text-left">
                <p class="mb-4">You are about to subscribe to:</p>
                <div class="bg-gray-50 p-4 rounded-lg mb-4">
                    <h3 class="font-semibold text-lg text-gray-900">${planName}</h3>
                    <p class="text-2xl font-bold text-indigo-600">₱${planPrice.toLocaleString()}</p>
                    <p class="text-sm text-gray-500">per month</p>
                </div>
                <p class="text-sm text-gray-600">You will be redirected to the payment gateway to complete your subscription.</p>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Proceed to Payment',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        width: '500px'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Processing...',
                text: 'Preparing your subscription...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Submit the form
            document.getElementById(`subscription-form-${planId}`).submit();
        }
    });
}

// Universal Success Notification
export function showSuccess(title = 'Success!', message = 'Operation completed successfully.', options = {}) {
    const defaultOptions = {
        title: title,
        text: message,
        icon: 'success',
        confirmButtonColor: '#10b981',
        confirmButtonText: 'OK',
        timer: options.autoClose !== false ? 3000 : null,
        timerProgressBar: true,
        showConfirmButton: options.autoClose === false,
        toast: options.toast || false,
        position: options.position || 'center'
    };

    return Swal.fire({ ...defaultOptions, ...options });
}

// Universal Error Notification
export function showError(title = 'Error!', message = 'Something went wrong. Please try again.', options = {}) {
    const defaultOptions = {
        title: title,
        text: message,
        icon: 'error',
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'OK',
        showConfirmButton: true,
        toast: options.toast || false,
        position: options.position || 'center'
    };

    return Swal.fire({ ...defaultOptions, ...options });
}

// Universal Warning Notification
export function showWarning(title = 'Warning!', message = 'Please check your input and try again.', options = {}) {
    const defaultOptions = {
        title: title,
        text: message,
        icon: 'warning',
        confirmButtonColor: '#f59e0b',
        confirmButtonText: 'OK',
        showConfirmButton: true,
        toast: options.toast || false,
        position: options.position || 'center'
    };

    return Swal.fire({ ...defaultOptions, ...options });
}

// Universal Info Notification
export function showInfo(title = 'Information', message = 'Here is some information for you.', options = {}) {
    const defaultOptions = {
        title: title,
        text: message,
        icon: 'info',
        confirmButtonColor: '#3b82f6',
        confirmButtonText: 'OK',
        timer: options.autoClose !== false ? 4000 : null,
        timerProgressBar: true,
        showConfirmButton: options.autoClose === false,
        toast: options.toast || false,
        position: options.position || 'center'
    };

    return Swal.fire({ ...defaultOptions, ...options });
}

// Loading State
export function showLoading(title = 'Loading...', message = 'Please wait while we process your request.', options = {}) {
    const defaultOptions = {
        title: title,
        text: message,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    };

    return Swal.fire({ ...defaultOptions, ...options });
}

// Close Loading
export function hideLoading() {
    Swal.close();
}

// Toast Success (for quick notifications)
export function toastSuccess(message = 'Success!', options = {}) {
    return showSuccess('', message, { 
        toast: true, 
        position: 'top-end',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
        ...options 
    });
}

// Toast Error (for quick notifications)
export function toastError(message = 'Error occurred!', options = {}) {
    return showError('', message, { 
        toast: true, 
        position: 'top-end',
        timer: 5000,
        timerProgressBar: true,
        showConfirmButton: false,
        ...options 
    });
}

// Toast Warning (for quick notifications)
export function toastWarning(message = 'Warning!', options = {}) {
    return showWarning('', message, { 
        toast: true, 
        position: 'top-end',
        timer: 4000,
        timerProgressBar: true,
        showConfirmButton: false,
        ...options 
    });
}

// Toast Info (for quick notifications)
export function toastInfo(message = 'Information', options = {}) {
    return showInfo('', message, { 
        toast: true, 
        position: 'top-end',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
        ...options 
    });
}

// Universal Confirmation Dialog
export function showConfirm(title = 'Are you sure?', message = 'This action cannot be undone.', options = {}) {
    const defaultOptions = {
        title: title,
        text: message,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, proceed!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    };

    return Swal.fire({ ...defaultOptions, ...options });
}

// Make functions available globally
window.confirmLogout = confirmLogout;
window.confirmAccountDeletion = confirmAccountDeletion;
window.confirmSubscription = confirmSubscription;

// Universal notification functions
window.showSuccess = showSuccess;
window.showError = showError;
window.showWarning = showWarning;
window.showInfo = showInfo;
window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.showConfirm = showConfirm;

// Toast functions
window.toastSuccess = toastSuccess;
window.toastError = toastError;
window.toastWarning = toastWarning;
window.toastInfo = toastInfo; 