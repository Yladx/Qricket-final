<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout Debug Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/logout-prevention.js') }}"></script>
    <script src="{{ asset('js/verify-logout.js') }}"></script>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ccc; }
        button { padding: 10px 20px; margin: 10px; cursor: pointer; }
        .logout-btn { background: #dc2626; color: white; border: none; border-radius: 5px; }
        .test-btn { background: #3b82f6; color: white; border: none; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Logout Debug Test Page</h1>
    
    <div class="test-section">
        <h2>Test 1: Direct Logout Form</h2>
        <form method="POST" action="{{ route('logout') }}" id="logout-form-test">
            @csrf
            <button type="submit" class="logout-btn">Direct Logout (No Confirmation)</button>
        </form>
    </div>
    
    <div class="test-section">
        <h2>Test 2: Logout with Confirmation</h2>
        <form method="POST" action="{{ route('logout') }}" id="logout-form-confirm">
            @csrf
            <button type="button" onclick="confirmLogout('logout-form-confirm')" class="logout-btn">Logout with Confirmation</button>
        </form>
    </div>
    
    <div class="test-section">
        <h2>Test 3: JavaScript Debug Functions</h2>
        <button onclick="testLogout()" class="test-btn">Test Logout Detection</button>
        <button onclick="testSweetAlert()" class="test-btn">Test SweetAlert</button>
        <button onclick="window.location.href='/test-logout'" class="test-btn">Check Login Status</button>
        <button onclick="location.reload()" class="test-btn">Reload Page (Clear Cache)</button>
    </div>
    
    <div class="test-section">
        <h2>Debug Information</h2>
        <div id="debug-info">
            <p><strong>User:</strong> {{ Auth::user()->email ?? 'Not logged in' }}</p>
            <p><strong>Session ID:</strong> {{ session()->getId() }}</p>
            <p><strong>Logout Route:</strong> {{ route('logout') }}</p>
            <p><strong>SweetAlert Available:</strong> <span id="swal-status">Checking...</span></p>
            <p><strong>confirmLogout Function:</strong> <span id="confirm-status">Checking...</span></p>
        </div>
    </div>

    <script>
        // Test SweetAlert availability
        document.getElementById('swal-status').textContent = typeof Swal !== 'undefined' ? 'Yes' : 'No';
        
        // Test confirmLogout function availability
        document.getElementById('confirm-status').textContent = typeof confirmLogout !== 'undefined' ? 'Yes' : 'No';
        
        // Test SweetAlert function
        function testSweetAlert() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'SweetAlert Test',
                    text: 'SweetAlert is working correctly!',
                    icon: 'success'
                });
            } else {
                alert('SweetAlert is not available');
            }
        }
        
        // Override console.log to show in page
        const originalLog = console.log;
        console.log = function(...args) {
            originalLog.apply(console, args);
            const debugDiv = document.getElementById('debug-info');
            const logDiv = document.createElement('div');
            logDiv.style.fontSize = '12px';
            logDiv.style.color = '#666';
            logDiv.textContent = 'Console: ' + args.join(' ');
            debugDiv.appendChild(logDiv);
        };
    </script>
</body>
</html>
