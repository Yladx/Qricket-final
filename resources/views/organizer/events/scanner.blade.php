<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>QR Scanner - {{ $event->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #000;
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        .scanner-frame {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            height: 300px;
            border: 3px solid #10b981;
            border-radius: 16px;
            background: transparent;
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.75),
                        0 0 20px rgba(16, 185, 129, 0.3),
                        inset 0 0 20px rgba(16, 185, 129, 0.1);
        }
        
        .scanner-line {
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(16, 185, 129, 0.5) 20%,
                #10b981 50%,
                rgba(16, 185, 129, 0.5) 80%,
                transparent 100%);
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.8);
            animation: scan 2s linear infinite;
        }
        
        @keyframes scan {
            0% { transform: translateY(-150px); opacity: 1; }
            50% { opacity: 0.8; }
            100% { transform: translateY(150px); opacity: 1; }
        }
        
        .corner {
            position: absolute;
            width: 24px;
            height: 24px;
            border: 3px solid #10b981;
        }
        
        .corner::before {
            content: '';
            position: absolute;
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 2px;
        }
        
        .corner.top-left {
            top: -3px;
            left: -3px;
            border-right: none;
            border-bottom: none;
        }
        
        .corner.top-left::before {
            top: -2px;
            left: -2px;
        }
        
        .corner.top-right {
            top: -3px;
            right: -3px;
            border-left: none;
            border-bottom: none;
        }
        
        .corner.top-right::before {
            top: -2px;
            right: -2px;
        }
        
        .corner.bottom-left {
            bottom: -3px;
            left: -3px;
            border-right: none;
            border-top: none;
        }
        
        .corner.bottom-left::before {
            bottom: -2px;
            left: -2px;
        }
        
        .corner.bottom-right {
            bottom: -3px;
            right: -3px;
            border-left: none;
            border-top: none;
        }
        
        .corner.bottom-right::before {
            bottom: -2px;
            right: -2px;
        }
        
        .status-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.95) 0%, rgba(5, 150, 105, 0.95) 100%);
            backdrop-filter: blur(10px);
            color: white;
            padding: 14px 20px;
            text-align: center;
            font-size: 15px;
            font-weight: 600;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            letter-spacing: 0.3px;
        }
        
        .controls {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.95) 0%, rgba(0, 0, 0, 0.85) 100%);
            backdrop-filter: blur(10px);
            padding: 24px 20px;
            z-index: 1000;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .controls-section {
            margin-bottom: 16px;
        }
        
        .controls-section:last-child {
            margin-bottom: 0;
        }
        
        .controls-label {
            text-align: center;
            color: rgba(255, 255, 255, 0.9);
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 12px;
            letter-spacing: 0.5px;
        }

        .info-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            margin-left: 6px;
            border-radius: 9999px;
            border: 1px solid rgba(255,255,255,0.35);
            color: rgba(255,255,255,0.85);
            background: rgba(255,255,255,0.06);
            cursor: pointer;
            font-size: 12px;
            line-height: 1;
            transition: all .2s ease;
        }
        .info-btn:hover {
            background: rgba(255,255,255,0.12);
            border-color: rgba(255,255,255,0.5);
        }
        
        .manual-input {
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 14px 18px;
            border-radius: 12px;
            width: 100%;
            font-size: 16px;
            text-align: center;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .manual-input:focus {
            outline: none;
            border-color: #10b981;
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
        }
        
        .manual-input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            letter-spacing: 0.3px;
        }
        
        .btn:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn:disabled {
            background: rgba(107, 114, 128, 0.5);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
            opacity: 0.6;
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        .btn-group {
            display: flex;
            gap: 12px;
            align-items: stretch;
        }
        
        .result-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.95);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .result-content {
            background: white;
            padding: 36px 32px;
            border-radius: 20px;
            text-align: center;
            max-width: 420px;
            width: 90%;
            margin: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.3s ease;
        }
        
        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .success-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 36px;
            color: white;
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
        }
        
        .error-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 36px;
            color: white;
            box-shadow: 0 8px 24px rgba(239, 68, 68, 0.4);
        }
        
        .result-content h3 {
            font-size: 22px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 12px;
        }
        
        .result-details {
            color: #6b7280;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        
        .result-details p {
            margin-bottom: 8px;
        }
        
        .result-details strong {
            color: #374151;
            font-weight: 600;
        }
        
        .hidden {
            display: none !important;
        }
        
        .flash {
            animation: flash 0.5s ease-in-out;
        }
        
        @keyframes flash {
            0%, 100% { 
                opacity: 1; 
                transform: scale(1);
            }
            50% { 
                opacity: 0.7; 
                transform: scale(1.1);
            }
        }
        
        .video-container {
            position: relative;
            width: 100%;
            height: 100vh;
            overflow: hidden;
        }
        
        #video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(1.05) contrast(1.1);
        }
        
        .scan-guide {
            position: absolute;
            bottom: 200px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            font-weight: 500;
            background: rgba(0, 0, 0, 0.5);
            padding: 8px 16px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            pointer-events: none;
            z-index: 999;
        }
        
        @media (max-width: 640px) {
            .scanner-frame {
                width: 260px;
                height: 260px;
            }
            
            @keyframes scan {
                0% { transform: translateY(-130px); }
                100% { transform: translateY(130px); }
            }
            
            .controls {
                padding: 20px 16px;
            }
            
            .btn {
                padding: 12px 20px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body class="h-full">
    <!-- Status Bar -->
    <div class="status-bar" id="status">
        Ready to scan QR codes
    </div>

    <!-- Video Container -->
    <div class="video-container">
        <video id="video" autoplay muted playsinline></video>
        
        <!-- Scanner Frame -->
        <div class="scanner-frame">
            <div class="scanner-line"></div>
            <div class="corner top-left"></div>
            <div class="corner top-right"></div>
            <div class="corner bottom-left"></div>
            <div class="corner bottom-right"></div>
        </div>
        
        <!-- Scan Guide -->
        <div class="scan-guide">
            Position QR code within the frame
        </div>
    </div>

    <!-- Controls -->
    <div class="controls">
        <div class="controls-section">
            <div class="btn-group">
                <button id="start-btn" class="btn flex-1" onclick="startScanner()" aria-label="Start scanner">
                    <span style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        Start Scanner
                    </span>
                </button>
                <button id="stop-btn" class="btn btn-secondary flex-1 hidden" onclick="stopScanner()" aria-label="Stop scanner">
                    <span style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"></path>
                        </svg>
                        Stop
                    </span>
                </button>
                <button class="btn btn-secondary" onclick="goBack()" style="min-width: 60px;" aria-label="Go back">
                    <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <div class="controls-section">
            <div class="controls-label">
                Or enter ticket ID manually
                <button type="button" class="info-btn" onclick="showFormatInfo()" aria-label="Accepted formats">i</button>
            </div>
            <div class="btn-group">
                <input type="text" id="manual-input" placeholder="Enter ticket ID (e.g., 36_ABC12345 or ABC12345)" class="manual-input flex-1">
                <button class="btn" onclick="processManualInput()" style="min-width: 120px;">
                    Check In
                </button>
            </div>
        </div>
    </div>

    <!-- Result Modal -->
    <div id="result-modal" class="result-modal hidden">
        <div class="result-content">
                <div id="success-result" class="hidden">
                    <div class="success-icon">✓</div>
                    <h3>Check-in Successful!</h3>
                    <div id="success-details" class="result-details"></div>
                    <button class="btn" onclick="closeResultModal()" style="width: 100%;">
                        Continue Scanning
                    </button>
                </div>
                
                <div id="error-result" class="hidden">
                    <div class="error-icon">✗</div>
                    <h3>Check-in Failed</h3>
                    <div id="error-details" class="result-details"></div>
                    <button class="btn" onclick="closeResultModal()" style="width: 100%; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                        Try Again
                    </button>
                </div>
        </div>
    </div>

    <!-- QR Code Scanner Library -->
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
    <!-- SweetAlert2 for info modal -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        let video = null;
        let canvas = null;
        let context = null;
        let scanner = null;
        let currentStream = null;
        let isScanning = false;
        let isProcessing = false;

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            video = document.getElementById('video');
            canvas = document.createElement('canvas');
            context = canvas.getContext('2d');
            
            updateStatus('Ready to scan QR codes');
            
            // Auto-start scanner on mobile devices
            if (isMobileDevice()) {
                setTimeout(startScanner, 500);
            }
        });

        function goBack() {
            stopScanner();
            window.history.back();
        }

        function startScanner() {
            if (isScanning || isProcessing) return;
            
            updateStatus('Starting camera...');
            
            const constraints = {
                video: { 
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                    facingMode: 'environment'
                }
            };

            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia(constraints)
                .then(function(stream) {
                    setupCamera(stream);
                })
                .catch(function(err) {
                    console.error('Camera error:', err);
                    updateStatus('Camera access denied. Use manual input.');
                    document.getElementById('manual-input').focus();
                });
            } else {
                updateStatus('Camera not supported. Use manual input.');
                document.getElementById('manual-input').focus();
            }
        }

        function setupCamera(stream) {
            currentStream = stream;
            video.srcObject = stream;
            
            video.onloadedmetadata = function() {
                video.play();
                isScanning = true;
                updateStatus('Scanning for QR codes...');
                
                document.getElementById('start-btn').classList.add('hidden');
                document.getElementById('stop-btn').classList.remove('hidden');
                
                startScanning();
            };
        }

        function startScanning() {
            if (!isScanning) return;
            
            scanQRCode();
            requestAnimationFrame(startScanning);
        }

        function scanQRCode() {
            if (!isScanning || isProcessing || video.readyState !== video.HAVE_ENOUGH_DATA) return;
            
            canvas.height = video.videoHeight;
            canvas.width = video.videoWidth;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height);
            
            if (code && code.data) {
                updateStatus('QR code detected!');
                processQRCode(code.data);
            }
        }

        function normalizeQrData(raw) {
            try {
                if (!raw) return '';
                let s = ('' + raw).trim();
                // If JSON with ticket_id
                if (s.startsWith('{') && s.endsWith('}')) {
                    const obj = JSON.parse(s);
                    if (obj && obj.ticket_id) return String(obj.ticket_id).trim();
                }
                // If URL, try to pick ticket_id from query or last path segment
                try {
                    const url = new URL(s);
                    const qid = url.searchParams.get('ticket_id') || url.searchParams.get('id');
                    if (qid) return String(qid).trim();
                    const last = url.pathname.split('/').filter(Boolean).pop();
                    if (last && /[A-Za-z0-9_\-]+/.test(last)) return last;
                } catch (_) { /* not a URL */ }
                // Strip known prefixes like QRICKET-
                s = s.replace(/^QRICKET[-_:]/i, '').trim();
                return s;
            } catch (_) {
                return ('' + raw).trim();
            }
        }

        function processQRCode(qrData) {
            if (isProcessing) return;
            isProcessing = true;
            
            stopScanner();
            updateStatus('Processing...');
            
            const normalized = normalizeQrData(qrData);
            console.log('QR Code detected:', qrData, '=> normalized:', normalized);
            
            // Send the raw QR code data to the server
            fetch(`{{ route('organizer.events.attendance.qr-scan', $event) }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    qr_data: normalized
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess(data);
                    setTimeout(() => {
                        closeResultModal();
                        startScanner();
                    }, 2000);
                } else {
                    showError(data.message);
                    setTimeout(() => {
                        closeResultModal();
                        startScanner();
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Network error - Please try again');
                setTimeout(() => {
                    closeResultModal();
                    startScanner();
                }, 2000);
            })
            .finally(() => {
                isProcessing = false;
            });
        }

        function processManualInput() {
            const input = document.getElementById('manual-input').value.trim();
            
            if (!input) {
                showError('Please enter a ticket ID or QR code data');
                return;
            }
            
            updateStatus('Processing manual input...');
            
            // Send the raw input to the server
            fetch(`{{ route('organizer.events.attendance.qr-scan', $event) }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    qr_data: normalizeQrData(input)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess(data);
                    setTimeout(() => {
                        closeResultModal();
                        startScanner();
                    }, 2000);
                } else {
                    showError(data.message);
                    setTimeout(() => {
                        closeResultModal();
                        startScanner();
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Network error - Please try again');
                setTimeout(() => {
                    closeResultModal();
                    startScanner();
                }, 2000);
            })
            .finally(() => {
                isProcessing = false;
            });
            
            document.getElementById('manual-input').value = '';
        }

        function showSuccess(data) {
            document.getElementById('result-modal').classList.remove('hidden');
            document.getElementById('success-result').classList.remove('hidden');
            document.getElementById('error-result').classList.add('hidden');
            
            const details = document.getElementById('success-details');
            if (data.data) {
                details.innerHTML = `
                    <p><strong>Ticket:</strong> ${data.data.ticket_id || 'N/A'}</p>
                    <p><strong>User:</strong> ${data.data.user_name || 'N/A'}</p>
                    <p><strong>Email:</strong> ${data.data.user_email || 'N/A'}</p>
                    <p><strong>Time:</strong> ${data.data.check_in_time || new Date().toLocaleString()}</p>
                `;
            } else {
                details.innerHTML = '<p>Ticket checked in successfully!</p>';
            }
            
            // Flash effect
            document.querySelector('.success-icon').classList.add('flash');
        }

        function showError(message) {
            document.getElementById('result-modal').classList.remove('hidden');
            document.getElementById('error-result').classList.remove('hidden');
            document.getElementById('success-result').classList.add('hidden');
            
            document.getElementById('error-details').innerHTML = `<p>${message}</p>`;
            
            // Flash effect
            document.querySelector('.error-icon').classList.add('flash');
        }

        function closeResultModal() {
            document.getElementById('result-modal').classList.add('hidden');
            document.getElementById('success-result').classList.add('hidden');
            document.getElementById('error-result').classList.add('hidden');
            
            // Remove flash effect
            document.querySelector('.success-icon').classList.remove('flash');
            document.querySelector('.error-icon').classList.remove('flash');
        }

        function stopScanner() {
            isScanning = false;
            
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
                currentStream = null;
            }
            
            video.srcObject = null;
            updateStatus('Scanner stopped');
            
            document.getElementById('start-btn').classList.remove('hidden');
            document.getElementById('stop-btn').classList.add('hidden');
        }

        function updateStatus(message) {
            document.getElementById('status').textContent = message;
        }

        function isMobileDevice() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        }

        function showFormatInfo() {
            Swal.fire({
                title: 'Accepted Formats',
                html: `
                    <div style="text-align:left">
                        <p style="margin:0 0 8px 0;color:#6b7280">Enter either of the following:</p>
                        <ul style="margin:0;padding-left:18px;color:#374151">
                            <li><strong>Full ID</strong>: <code style="background:#f3f4f6;padding:2px 6px;border-radius:4px">36_A1B2C3D4</code></li>
                            <li style="margin-top:6px"><strong>Unique Code</strong>: <code style="background:#f3f4f6;padding:2px 6px;border-radius:4px">A1B2C3D4</code></li>
                        </ul>
                        <p style="margin-top:10px;color:#6b7280;font-size:12px">Find your ticket ID on the ticket or purchase confirmation.</p>
                    </div>
                `,
                icon: 'info',
                confirmButtonText: 'Got it',
                confirmButtonColor: '#10b981',
                width: 420
            });
        }

        // Handle Enter key in manual input
        document.getElementById('manual-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                processManualInput();
            }
        });

        // Handle page visibility change
        document.addEventListener('visibilitychange', function() {
            if (document.hidden && isScanning) {
                stopScanner();
            }
        });
    </script>
</body>
</html>