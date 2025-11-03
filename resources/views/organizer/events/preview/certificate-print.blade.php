<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Certificate</title>
    <link href="/build/assets/app-CXbBF2ui.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        html, body { 
            margin: 0 !important; 
            padding: 0 !important; 
            background: #fff !important; 
            width: 100vw !important; 
            height: 100vh !important; 
            overflow: hidden !important;
        }
        /* Ensure ONLY the certificate wrapper exists - hide everything else */
        body > *:not(#cert-print-wrapper) { display: none !important; }
        #cert-print-wrapper {
            display: block !important;
            width: 100vw !important;
            height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
            position: relative !important;
        }
        /* Ensure only ONE .page element exists and is visible */
        #cert-print-wrapper .page {
            display: block !important;
            margin: 0 auto !important;
        }
        /* Hide any duplicate elements */
        #cert-print-wrapper .page ~ .page { display: none !important; }
        /* Print-specific styles */
        @media print {
            @page { size: A4 landscape; margin: 0; }
            html, body { 
                margin: 0 !important; 
                padding: 0 !important; 
                background: #fff !important;
                width: 100% !important;
                height: 100% !important;
            }
            #cert-print-wrapper {
                width: 100% !important;
                height: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            /* Hide any duplicate pages */
            .page ~ .page { display: none !important; }
            /* Ensure only first .page is printed */
            .page:not(:first-of-type) { display: none !important; }
        }
    </style>
</head>
<body>
    <div id="cert-print-wrapper">
        @include('organizer.events.preview.certificate-template', [
            'certificateData' => $certificateData,
            'event' => $event,
            'fallbackWhen' => $fallbackWhen,
            'fallbackGiven' => $fallbackGiven
        ])
    </div>
    <script>
        // Ensure only one certificate exists on page load
        window.addEventListener('DOMContentLoaded', function(){
            const pages = document.querySelectorAll('.page');
            if (pages.length > 1) {
                // Keep only the first one, remove duplicates
                for (let i = 1; i < pages.length; i++) {
                    pages[i].remove();
                }
            }
        });
        window.addEventListener('load', function(){
            // Double-check for duplicates before printing
            const pages = document.querySelectorAll('.page');
            if (pages.length > 1) {
                for (let i = 1; i < pages.length; i++) {
                    pages[i].remove();
                }
            }
            setTimeout(function(){ window.print(); }, 300);
        });
    </script>
</body>
</html>


