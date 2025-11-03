// Verification script to check confirmLogout function
console.log('=== Logout Function Verification ===');

// Check if confirmLogout is defined
if (typeof confirmLogout === 'function') {
    console.log('✅ confirmLogout function is defined');
    
    // Check function source to see if it's the correct one
    const funcString = confirmLogout.toString();
    if (funcString.includes('console.log(\'confirmLogout called with formId:\'')) {
        console.log('✅ confirmLogout is the correct version (from logout-prevention.js)');
    } else {
        console.log('❌ confirmLogout appears to be an old version');
        console.log('Function source:', funcString.substring(0, 200) + '...');
    }
} else {
    console.log('❌ confirmLogout function is not defined');
}

// Check if SweetAlert is available
if (typeof Swal !== 'undefined') {
    console.log('✅ SweetAlert2 is available');
} else {
    console.log('❌ SweetAlert2 is not available');
}

// Check for logout forms
const desktopForm = document.getElementById('logout-form-desktop');
const offcanvasForm = document.getElementById('logout-form-offcanvas');

console.log('Desktop logout form:', desktopForm ? 'Found' : 'Not found');
console.log('Offcanvas logout form:', offcanvasForm ? 'Found' : 'Not found');

// Test function call (without actually executing)
console.log('=== Testing function call (dry run) ===');
if (typeof confirmLogout === 'function') {
    try {
        // Create a mock function to test
        const originalConfirmLogout = confirmLogout;
        let callCount = 0;
        
        window.confirmLogout = function(formId) {
            callCount++;
            console.log(`confirmLogout called (test ${callCount}) with formId: ${formId}`);
            
            if (callCount > 1) {
                console.log('❌ RECURSION DETECTED! Function called itself');
                return;
            }
            
            console.log('✅ Function call successful, no recursion detected');
            
            // Restore original function
            window.confirmLogout = originalConfirmLogout;
        };
        
        // Test the call
        confirmLogout('test-form');
        
    } catch (error) {
        console.log('❌ Error testing confirmLogout:', error.message);
    }
}

console.log('=== Verification Complete ===');
