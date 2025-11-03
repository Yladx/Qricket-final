<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $event->title ?? 'Certificate' }} - Certificate of Participation</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes:wght@400&display=swap" rel="stylesheet">
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Screen styles */
        body {
            background: #f3f4f6;
            margin: 0;
            padding: 20px;
            padding-top: 40px;
            font-family: 'Figtree', sans-serif;
        }
        
        .certificate-container {
            max-width: 1200px;
            margin: 0 auto;
            margin-top: 40px;
            margin-bottom: 40px;
            padding: 2rem 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: visible;
        }
        
        #cert_preview_wrapper {
            overflow: visible;
            width: 100%;
        }
        
        /* Ensure the page container doesn't clip content */
        .page {
            overflow: visible !important;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: #4f46e5;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .print-button:hover {
            background: #4338ca;
        }
        
        @media (max-width: 640px) {
            .landscape-scale { 
                transform-origin: top left; 
                transform: scale(0.95); 
            }
        }
        
        /* Print styles */
        @media print {
            @page {
                size: A4 landscape;
                margin: 0;
            }
            
            /* Hide everything except certificate */
            body * {
                visibility: hidden;
            }
            
            /* Show certificate container and all its content */
            .certificate-container,
            .certificate-container *,
            #cert_preview_wrapper,
            #cert_preview_wrapper * {
                visibility: visible !important;
                display: block !important;
            }
            
            /* Reset body and html for print */
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                width: 297mm !important;
                height: 210mm !important;
                background: white !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                overflow: hidden !important;
            }
            
            /* Position certificate container */
            .certificate-container {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 297mm !important;
                height: 210mm !important;
                max-width: 297mm !important;
                max-height: 210mm !important;
                margin: 0 !important;
                padding: 0 !important;
                transform: none !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                background: white !important;
                overflow: hidden !important;
            }
            
            /* Ensure wrapper fits page */
            #cert_preview_wrapper {
                width: 100% !important;
                height: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                display: block !important;
                overflow: visible !important;
            }
            
            /* Ensure page element is full size and scaled correctly */
            .page {
                width: 297mm !important;
                height: 210mm !important;
                max-width: 297mm !important;
                max-height: 210mm !important;
                margin: 0 !important;
                padding: 0 !important;
                padding-top: 0 !important;
                padding-bottom: 0 !important;
                transform: none !important;
                border: none !important;
                box-shadow: none !important;
                background: white !important;
                position: relative !important;
                overflow: visible !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            
            /* Reset landscape scale transform */
            .landscape-scale {
                transform: none !important;
                width: 297mm !important;
                height: 210mm !important;
                max-width: 297mm !important;
                max-height: 210mm !important;
            }
            
            /* Hide print button */
            .print-button,
            .no-print {
                display: none !important;
                visibility: hidden !important;
            }
            
            /* Ensure all text and content fits */
            .page * {
                visibility: visible !important;
                max-width: 100% !important;
            }
            
            /* Prevent text from overflowing (except certificate title) */
            .page p,
            .page div:not(.certificate-title) {
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
            }
            
            /* Certificate template specific print styles */
            .page .px-10 {
                padding-left: 12mm !important;
                padding-right: 12mm !important;
            }
            
            .page .pt-16 {
                padding-top: 10mm !important;
            }
            
            .page .pb-8 {
                padding-bottom: 8mm !important;
            }
            
            /* Ensure Certificate of Participation text fits properly in print */
            .page .certificate-title {
                white-space: nowrap !important;
                word-break: keep-all !important;
                font-size: 3.5rem !important;
                line-height: 1.2 !important;
                overflow: visible !important;
                max-width: 100% !important;
                padding: 0 !important;
            }
            
            /* Adjust recipient name size if needed */
            .page #recipient_name {
                font-size: 2rem !important;
                line-height: 1.3 !important;
            }
            
            /* Adjust text sizes for better fit */
            .page .text-base {
                font-size: 0.9rem !important;
                line-height: 1.5 !important;
            }
            
            /* Ensure bands are visible and positioned correctly */
            .band-top {
                display: block !important;
                visibility: visible !important;
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                height: 28mm !important;
                z-index: 1 !important;
                clip-path: polygon(0 0, 100% 0, 100% 60%, 0 100%) !important;
            }
            
            .band-bottom {
                display: block !important;
                visibility: visible !important;
                position: absolute !important;
                bottom: 0 !important;
                left: 0 !important;
                right: 0 !important;
                height: 28mm !important;
                z-index: 1 !important;
                clip-path: polygon(0 40%, 100% 0, 100% 100%, 0 100%) !important;
            }
            
            /* Remove padding from page in print to allow bands to touch edges */
            .page {
                padding-top: 0 !important;
                padding-bottom: 0 !important;
            }
            
            .landscape-scale { 
                transform: none !important;
                width: 297mm !important;
                height: 210mm !important;
            }
            
            #signatories-container .grid { 
                gap: 1.5rem !important; 
            }
            
            /* Ensure signatories fit */
            #signatories-container {
                margin-top: 1rem !important;
            }
            
            /* Adjust logo sizes if needed */
            .page img {
                max-width: 60px !important;
                max-height: 60px !important;
            }
            
            /* Ensure all content is visible */
            .page > * {
                position: relative !important;
                z-index: 2 !important;
            }
        }
        
        /* Screen-only styles for certificate template */
        @media (max-width: 640px) {
            .landscape-scale { 
                transform-origin: top left; 
                transform: scale(0.95); 
            }
        }
        
        /* Keep certificate on a single sheet */
        .page { 
            page-break-inside: avoid; 
            break-inside: avoid; 
        }
        
        #cert_preview_wrapper { 
            page-break-before: avoid; 
            page-break-after: avoid; 
        }

        /* Certificate title styling - responsive and prevents cutoff */
        .certificate-title {
            font-size: clamp(2rem, 5vw, 4rem);
            line-height: 1.2;
            font-weight: 400;
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
            margin: 0 auto;
            max-width: 100%;
            padding: 0 0.5rem;
        }
        
        /* For larger screens, keep title on one line if possible */
        @media (min-width: 768px) {
            .certificate-title {
                font-size: clamp(3rem, 6vw, 4.5rem);
                white-space: nowrap;
            }
        }
        
        @media (min-width: 1024px) {
            .certificate-title {
                font-size: 4rem;
            }
        }
        
        /* Truncation for signatory lines to prevent overlap */
        .sig-line { 
            max-width: 220px; 
            overflow: hidden; 
            text-overflow: ellipsis; 
            white-space: nowrap; 
            margin-left: auto; 
            margin-right: auto; 
        }
        
        .sig-role { 
            max-width: 220px; 
            overflow: hidden; 
            text-overflow: ellipsis; 
            white-space: nowrap; 
            margin-left: auto; 
            margin-right: auto; 
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">Print Certificate</button>
    
    <div class="certificate-container">
        <div id="cert_preview_wrapper" class="w-full">
            @include('organizer.events.preview.certificate-template', [
                'certificateData' => $certificateData ?? [], 
                'event' => $event, 
                'fallbackWhen' => $fallbackWhen ?? '', 
                'fallbackGiven' => $fallbackGiven ?? ''
            ])
        </div>
    </div>
</body>
</html>
