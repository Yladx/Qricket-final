// Logout Prevention and Confirmation Script

// Wait for SweetAlert to be available
function waitForSweetAlert(callback) {
    if (typeof Swal !== 'undefined') {
        callback();
    } else {
        setTimeout(() => waitForSweetAlert(callback), 100);
    }
}

// Enhanced logout confirmation function
function confirmLogout(formId) {
    // console.log('confirmLogout called with formId:', formId);
    
    // Check if SweetAlert is available
    if (typeof Swal !== 'undefined') {
        // Use SweetAlert for confirmation
        const isDark = document.documentElement.classList.contains('dark') || (localStorage.getItem('darkMode') === 'true');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to logout from your account?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, logout!',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            background: isDark ? '#111827' : '#ffffff',
            color: isDark ? '#e5e7eb' : '#111827',
            customClass: {
                popup: `rounded-2xl shadow-2xl ${isDark ? 'ring-1 ring-gray-700' : 'ring-1 ring-gray-200'}`,
                title: 'text-xl font-semibold',
                htmlContainer: isDark ? 'text-gray-300' : 'text-gray-600',
                confirmButton: (isDark
                    ? 'bg-red-500 hover:bg-red-400 text-white'
                    : 'bg-red-600 hover:bg-red-700 text-white') + ' px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500',
                cancelButton: (isDark
                    ? 'bg-gray-700 hover:bg-gray-600 text-gray-200'
                    : 'bg-gray-100 hover:bg-gray-200 text-gray-700') + ' px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                // console.log('User confirmed logout');
                
                // Clear dark mode preference on logout
                try { 
                    localStorage.removeItem('darkMode'); 
                } catch (e) {
                    // console.warn('Could not clear darkMode from localStorage:', e);
                }
                
                // Submit the logout form
                const form = document.getElementById(formId);
                // console.log('Looking for form with ID:', formId);
                // console.log('Form found:', form);
                
                if (form) {
                    // console.log('Submitting logout form');
                    form.submit();
                } else {
                    // console.error('Logout form not found:', formId);
                    // Fallback: redirect to logout route
                    window.location.href = '/logout';
                }
            } else {
                // console.log('User cancelled logout');
            }
        });
    } else {
        // console.warn('SweetAlert2 not available, using browser confirm');
        // Fallback to browser confirm dialog
        if (confirm('Are you sure you want to logout?')) {
            const form = document.getElementById(formId);
            if (form) {
                form.submit();
            } else {
                window.location.href = '/logout';
            }
        }
    }
}

// Prevent accidental logout on page refresh/close
// Only activate if user is actively trying to logout (not on every page navigation)
// This listener is intentionally minimal to avoid false warnings

// Make function globally available
window.confirmLogout = confirmLogout;

// Debug function to test logout
window.testLogout = function() {
    // console.log('Testing logout functionality...');
    const logoutForms = document.querySelectorAll('form[id*="logout"]');
    // console.log('Found logout forms:', logoutForms.length);
    
    logoutForms.forEach((form, index) => {
        // console.log(`Form ${index + 1}:`, form.id, form.action);
    });
    
    if (logoutForms.length === 0) {
        // console.warn('No logout forms found on this page');
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Check if logout forms exist
    const logoutForms = document.querySelectorAll('form[id*="logout"]');
    
    // if (logoutForms.length === 0) {
    //     console.log('No logout forms found on this page');
    // } else {
    //     console.log(`Found ${logoutForms.length} logout form(s)`);
    // }
    
    // Add click event listeners to logout links/buttons
    document.querySelectorAll('a[href*="logout"], button[onclick*="logout"]').forEach(element => {
        element.addEventListener('click', function(e) {
            // Let the onclick handler take care of it
            // console.log('Logout element clicked:', element);
        });
    });
});

