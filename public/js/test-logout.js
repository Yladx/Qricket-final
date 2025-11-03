// Test logout functionality
console.log('Testing logout functionality...');

// Check if SweetAlert is available
if (typeof Swal !== 'undefined') {
    console.log('SweetAlert2 is available');
    
    // Test the confirmLogout function
    window.testLogoutFunction = function() {
        console.log('Testing confirmLogout function...');
        
        // Check if logout forms exist
        const desktopForm = document.getElementById('logout-form-desktop');
        const offcanvasForm = document.getElementById('logout-form-offcanvas');
        
        console.log('Desktop logout form:', desktopForm);
        console.log('Offcanvas logout form:', offcanvasForm);
        
        if (desktopForm) {
            console.log('Testing desktop logout...');
            confirmLogout('logout-form-desktop');
        } else if (offcanvasForm) {
            console.log('Testing offcanvas logout...');
            confirmLogout('logout-form-offcanvas');
        } else {
            console.log('No logout forms found');
        }
    };
    
    // Auto-test after 2 seconds
    setTimeout(() => {
        console.log('Auto-testing logout in 2 seconds...');
        window.testLogoutFunction();
    }, 2000);
    
} else {
    console.log('SweetAlert2 is NOT available');
    
    // Wait for SweetAlert to load
    let attempts = 0;
    const checkSweetAlert = setInterval(() => {
        attempts++;
        console.log(`Checking for SweetAlert2... attempt ${attempts}`);
        
        if (typeof Swal !== 'undefined') {
            console.log('SweetAlert2 loaded after', attempts, 'attempts');
            clearInterval(checkSweetAlert);
            
            // Now test the function
            setTimeout(() => {
                window.testLogoutFunction();
            }, 1000);
        } else if (attempts > 20) {
            console.log('SweetAlert2 failed to load after 20 attempts');
            clearInterval(checkSweetAlert);
        }
    }, 500);
}
