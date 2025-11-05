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
        /* ---------- Screen preview styles ---------- */
        body{background:#f3f4f6;margin:0;padding:40px 20px;font-family:'Figtree',sans-serif}
        .certificate-container{max-width:1200px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 4px 6px -1px rgba(0,0,0,.1);padding:24px 0}
        #cert_preview_wrapper{width:100%}
        /* Keep A4 landscape aspect ratio on screen */
        .page{width:min(100%,1122px);height:auto;aspect-ratio:297/210;overflow:visible}
        .print-button{position:fixed;top:20px;right:20px;z-index:1000;background:#4f46e5;color:#fff;border:none;padding:12px 24px;border-radius:6px;font-weight:500;cursor:pointer;box-shadow:0 4px 6px -1px rgba(0,0,0,.1)}
        .print-button:hover{background:#4338ca}

        /* ---------- Print styles ---------- */
        @media print{
            @page{size:A4 landscape;margin:0}
            html,body{margin:0!important;padding:0!important;width:297mm!important;height:210mm!important;background:#fff!important;-webkit-print-color-adjust:exact;color-adjust:exact}
            .no-print{display:none!important}
            .certificate-container{margin:0!important;padding:0!important;box-shadow:none!important;background:#fff!important}
            #cert_preview_wrapper{width:100%!important;margin:0!important;padding:0!important}
            .page{width:297mm!important;height:210mm!important;margin:0!important;padding:0!important;border:none!important;position:relative!important;overflow:hidden!important;page-break-after:always;page-break-inside:avoid!important}
        }

        /* Prevent text/image overflow and keep typography readable */
        .certificate-title{font-size:clamp(2rem,5vw,4rem);line-height:1.2;font-weight:400;white-space:normal;word-wrap:break-word;overflow-wrap:break-word;hyphens:auto;margin:0 auto;max-width:100%;padding:0 .5rem}
        @media(min-width:768px){.certificate-title{font-size:clamp(3rem,6vw,4.5rem);white-space:nowrap}}
        @media(min-width:1024px){.certificate-title{font-size:4rem}}
        .sig-line{max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-left:auto;margin-right:auto}
        .sig-role{max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-left:auto;margin-right:auto}
    </style>
</head>
<body>
    <button id="printCertificate" class="print-button no-print">Print Certificate</button>
    
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

    <script>
      (function(){
        var btn = document.getElementById('printCertificate');
        function triggerPrint(){
          if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(function(){ setTimeout(function(){ window.print(); }, 60); });
          } else {
            setTimeout(function(){ window.print(); }, 100);
          }
        }
        if (btn){ btn.addEventListener('click', function(){ triggerPrint(); }); }
      })();
    </script>
</body>
</html>
