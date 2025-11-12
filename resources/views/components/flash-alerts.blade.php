@php
$success = session('success') ?? session('status');
$error = session('error');
$warning = session('warning');
$info = session('info');
$sweetalert = session('sweetalert');
@endphp

<!-- Always load SweetAlert functionality -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // console.log('Flash alerts script loaded');

        // Safely get session values
        const success = {
            !!json_encode($success) !!
        };
        const error = {
            !!json_encode($error) !!
        };
        const warning = {
            !!json_encode($warning) !!
        };
        const info = {
            !!json_encode($info) !!
        };

        // console.log('Success:', success);
        // console.log('Error:', error);
        // console.log('Warning:', warning);
        // console.log('Info:', info);
        // console.log('SweetAlert available:', typeof Swal !== 'undefined');

        // Check SweetAlert availability
        // if (typeof Swal !== 'undefined') {
        //     console.log('SweetAlert is available');
        // } else {
        //     console.error('SweetAlert is NOT available!');
        // }

        const isDark = document.documentElement.classList.contains('dark');
        const common = {
            customClass: {
                popup: isDark ? 'dark:bg-gray-800 dark:text-gray-100' : '',
                title: isDark ? 'dark:text-gray-100' : '',
                htmlContainer: isDark ? 'dark:text-gray-300' : '',
                confirmButton: 'bg-qricket-500 hover:bg-qricket-600',
            }
        };

        // Initialize switch states on page load (only if function exists)
        if (typeof initializeModeSwitchStates === 'function') {
            initializeModeSwitchStates();
        }

        // Skip SweetAlert on verification page
        const isVerificationPage = window.location.pathname.includes('email-verification') || window.location.pathname.includes('verify-email');

        // Handle session messages (skip on verification page)
        if ((success || error || warning || info) && !isVerificationPage) {
            if (success) {
                // console.log('Processing success message:', success);
                if (success.includes && success.includes('Registration successful')) {
                    // console.log('Showing registration success SweetAlert');
                    Swal.fire(Object.assign({}, common, {
                        icon: 'success',
                        title: 'Registration Successful!',
                        html: `
                            <div class="text-left">
                                <p class="mb-3">We've sent a verification email to your email address.</p>
                                <p class="text-sm text-gray-600">Please check your inbox and click the verification link to activate your account.</p>
                                <p class="text-sm text-gray-600 mt-2">You must verify your email before you can log in.</p>
                            </div>
                        `,
                        confirmButtonText: 'Got it!',
                        confirmButtonColor: '#00B388',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }));
                } else {
                    Swal.fire(Object.assign({}, common, {
                        icon: 'success',
                        title: 'Success',
                        text: success,
                        timer: 1800,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    }));
                }
            }

            if (error) {
                // console.log('Processing error message:', error);
                if (error.includes && error.includes('verify your email address')) {
                    // console.log('Showing email verification error SweetAlert');
                    Swal.fire(Object.assign({}, common, {
                        icon: 'warning',
                        title: 'Email Verification Required',
                        html: `
                            <div class="text-left">
                                <p class="mb-3">Please verify your email address to continue logging in.</p>
                                <p class="text-sm text-gray-600">We've sent a verification email to your registered email address.</p>
                                <p class="text-sm text-gray-600 mt-2">Click the verification link in your email, then try logging in again.</p>
                                <div class="mt-3 pt-3 border-t border-gray-200">
                                    <p class="text-sm text-gray-500 mb-2">Didn't receive the email?</p>
                                    <button id="resend-verification-btn" class="text-qricket-600 hover:text-qricket-700 text-sm font-medium underline">
                                        Click here to resend verification email
                                    </button>
                                </div>
                            </div>
                        `,
                        confirmButtonText: 'I Understand',
                        confirmButtonColor: '#00B388',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            // Add click handler for resend button
                            document.getElementById('resend-verification-btn').addEventListener('click', function() {
                                // Show loading
                                Swal.fire({
                                    title: 'Sending...',
                                    text: 'Please wait while we send the verification email.',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    showConfirmButton: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });

                                // Send resend request
                                fetch('/email/verification-notification', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                        },
                                        body: JSON.stringify({})
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Email Sent!',
                                            text: 'A new verification email has been sent to your registered email address.',
                                            confirmButtonColor: '#00B388'
                                        });
                                    })
                                    .catch(error => {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: 'Failed to send verification email. Please try again.',
                                            confirmButtonColor: '#00B388'
                                        });
                                    });
                            });
                        }
                    }));
                } else {
                    Swal.fire(Object.assign({}, common, {
                        icon: 'error',
                        title: 'Event Ended',
                        text: error,
                        customClass: {
                            popup: 'rounded-xl shadow-2xl',
                            title: 'text-xl font-semibold',
                            htmlContainer: 'text-gray-600',
                            confirmButton: 'bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium'
                        }
                    }));
                }
            }

            if (warning) {
                Swal.fire(Object.assign({}, common, {
                    icon: 'warning',
                    title: 'Please review',
                    text: warning
                }));
            }

            if (info) {
                Swal.fire(Object.assign({}, common, {
                    icon: 'info',
                    title: 'Information',
                    text: info
                }));
            }
        }

        // Handle SweetAlert session data (e.g., verification required)
        const sweetalert = {
            !!json_encode($sweetalert) !!
        };
        if (sweetalert && !isVerificationPage) {
            // Don't show verification required SweetAlert on edit pages
            // Staff members can edit even without verification
            const isEditPage = window.location.pathname.includes('/edit/basic') ||
                window.location.pathname.includes('/edit/layout') ||
                window.location.pathname.includes('/edit/certificate') ||
                window.location.pathname.includes('/edit/tickets');
            const isVerificationRequired = sweetalert.title &&
                (sweetalert.title.includes('Verification Required') ||
                    sweetalert.title.includes('Verification'));

            // Skip verification alerts on edit pages - staff can edit without verification
            if (isEditPage && isVerificationRequired) {
                // Clear the session flash data by making a request to clear it
                // This prevents the alert from showing on subsequent page loads
                console.log('Skipping verification SweetAlert on edit page - staff can edit without verification');
                return;
            }

            const alertOptions = {
                title: sweetalert.title || 'Notification',
                text: sweetalert.text || '',
                icon: sweetalert.icon || 'info',
                confirmButtonText: sweetalert.confirmButtonText || 'OK',
                confirmButtonColor: sweetalert.confirmButtonColor || '#10b981',
                showCancelButton: sweetalert.showCancelButton || false,
                cancelButtonText: sweetalert.cancelButtonText || 'Cancel',
                ...common
            };

            Swal.fire(alertOptions).then((result) => {
                if (result.isConfirmed && sweetalert.redirect_url) {
                    window.location.href = sweetalert.redirect_url;
                }
            });
        }
    });

    // Global AJAX error handler for verification requirements
    (function() {
        // Intercept fetch requests
        const originalFetch = window.fetch;
        window.fetch = function(...args) {
            return originalFetch.apply(this, args)
                .then(async response => {
                    // Check if response is a 403 with verification requirement
                    if (response.status === 403) {
                        try {
                            const clonedResponse = response.clone();
                            const data = await clonedResponse.json();
                            if (data.requires_verification && data.sweetalert) {
                                // Don't show verification errors on edit pages - staff can edit without verification
                                const isEditPage = window.location.pathname.includes('/edit/basic') ||
                                    window.location.pathname.includes('/edit/layout') ||
                                    window.location.pathname.includes('/edit/certificate') ||
                                    window.location.pathname.includes('/edit/tickets');

                                if (isEditPage) {
                                    // Skip verification errors on edit pages - let the request fail silently
                                    // Staff members can edit even without verification
                                    console.log('Skipping verification error on edit page - staff can edit without verification');
                                    return response; // Return the response without showing SweetAlert
                                }

                                // Show SweetAlert with verification prompt
                                if (typeof Swal !== 'undefined' && window.Swal) {
                                    const alertOptions = {
                                        title: data.sweetalert.title || 'Verification Required',
                                        text: data.sweetalert.text || 'Please verify as organizer to access this functionality.',
                                        icon: data.sweetalert.icon || 'warning',
                                        confirmButtonText: data.sweetalert.confirmButtonText || 'Verify Now',
                                        confirmButtonColor: data.sweetalert.confirmButtonColor || '#10b981',
                                        showCancelButton: data.sweetalert.showCancelButton !== false,
                                        cancelButtonText: data.sweetalert.cancelButtonText || 'Cancel',
                                    };

                                    Swal.fire(alertOptions).then((result) => {
                                        if (result.isConfirmed && data.redirect_url) {
                                            window.location.href = data.redirect_url;
                                        }
                                    });
                                }
                                // Return a rejected promise to prevent further processing
                                throw new Error('Verification required');
                            }
                        } catch (e) {
                            // If JSON parsing fails or it's not a verification error, continue with original response
                            if (e.message === 'Verification required') {
                                throw e;
                            }
                        }
                    }
                    return response;
                });
        };

        // Intercept jQuery AJAX requests (if jQuery is available)
        if (typeof jQuery !== 'undefined' && jQuery.ajaxSetup) {
            jQuery(document).ajaxError(function(event, xhr, settings, thrownError) {
                if (xhr.status === 403) {
                    try {
                        const data = JSON.parse(xhr.responseText);
                        if (data.requires_verification && data.sweetalert) {
                            // Don't show verification errors on edit pages - staff can edit without verification
                            const isEditPage = window.location.pathname.includes('/edit/basic') ||
                                window.location.pathname.includes('/edit/layout') ||
                                window.location.pathname.includes('/edit/certificate') ||
                                window.location.pathname.includes('/edit/tickets');

                            if (isEditPage) {
                                // Skip verification errors on edit pages - let the request fail silently
                                // Staff members can edit even without verification
                                console.log('Skipping verification error on edit page - staff can edit without verification');
                                return; // Don't prevent default, let it fail silently
                            }

                            if (typeof Swal !== 'undefined' && window.Swal) {
                                const alertOptions = {
                                    title: data.sweetalert.title || 'Verification Required',
                                    text: data.sweetalert.text || 'Please verify as organizer to access this functionality.',
                                    icon: data.sweetalert.icon || 'warning',
                                    confirmButtonText: data.sweetalert.confirmButtonText || 'Verify Now',
                                    confirmButtonColor: data.sweetalert.confirmButtonColor || '#10b981',
                                    showCancelButton: data.sweetalert.showCancelButton !== false,
                                    cancelButtonText: data.sweetalert.cancelButtonText || 'Cancel',
                                };

                                Swal.fire(alertOptions).then((result) => {
                                    if (result.isConfirmed && data.redirect_url) {
                                        window.location.href = data.redirect_url;
                                    }
                                });
                            }
                            // Prevent default error handling
                            event.preventDefault();
                            return false;
                        }
                    } catch (e) {
                        // Not JSON or verification error, let default handler process it
                    }
                }
            });
        }
    })();
</script>
