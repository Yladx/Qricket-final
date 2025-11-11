<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Certificate</title>
    <link href="/build/assets/app-CXbBF2ui.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes:wght@400&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        
        /* Screen preview styles - match the preview exactly */
        html, body { 
            margin: 0; 
            padding: 20px; 
            background: #f3f4f6; 
            font-family: 'Figtree', sans-serif;
        }
        
        #cert-print-wrapper {
            display: block;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,.1);
            padding: 24px 0;
        }
        
        /* Ensure only ONE .page element exists and is visible */
        #cert-print-wrapper .page {
            display: block;
            margin: 0 auto;
            width: min(100%, 1122px);
            height: auto;
        }
        
        /* Hide any duplicate elements */
        #cert-print-wrapper .page ~ .page { display: none !important; }
        
        /* Print-specific styles - exact A4 landscape dimensions */
        @media print {
            @page { 
                size: A4 landscape; 
                margin: 0; 
            }
            
            html, body { 
                margin: 0 !important; 
                padding: 0 !important; 
                background: #fff !important;
                width: 297mm !important;
                height: 210mm !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            #cert-print-wrapper {
                width: 297mm !important;
                height: 210mm !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                background: #fff !important;
                border-radius: 0 !important;
            }
            
            /* Certificate page element - fill the entire print area with exact A4 dimensions */
            #cert-print-wrapper .page {
                width: 297mm !important;
                height: 210mm !important;
                max-width: none !important;
                margin: 0 !important;
                padding-top: 1.5rem !important;
                padding-bottom: 1.5rem !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                border-width: 8px !important;
                border-style: solid !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                aspect-ratio: auto !important;
                position: relative !important;
                overflow: visible !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
                background: white !important;
            }
            
            /* Ensure all colors, gradients, and images print correctly */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            /* Ensure certificate title font loads and maintains style */
            .certificate-title {
                font-family: 'Great Vibes', cursive !important;
            }
            
            /* Ensure images are visible and print correctly */
            img.certificate-image {
                max-width: 100% !important;
                height: auto !important;
            }
            
            /* Ensure gradient bands and borders maintain their appearance */
            .band-top,
            .band-bottom {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
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


