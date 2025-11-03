<x-app-layout title="Your Certificate - {{ $event->title }}">
    <x-slot name="header">
        <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                <h1 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Certificate of Participation</h1>
                <a href="{{ route('events.show', $event) }}" class="text-sm text-qricket-600 dark:text-qricket-400 hover:underline">Back to event</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-3 no-print">
                <div class="text-base font-medium text-gray-900 dark:text-gray-100">{{ $event->title }}</div>
                <div class="flex items-center gap-2">
                    <button onclick="window.print()" class="px-3 py-2 text-sm bg-indigo-600 text-white rounded">Print</button>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <div class="p-4">
                    <div id="cert_preview_wrapper" class="w-full bg-gray-100 dark:bg-gray-900 rounded overflow-hidden">
                        @include('organizer.events.preview.certificate-template', ['certificateData' => $certificateData ?? [], 'event' => $event, 'fallbackWhen' => $fallbackWhen ?? '', 'fallbackGiven' => $fallbackGiven ?? ''])
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        /* Ensure images load properly when printing */
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            #cert_preview_wrapper { box-shadow: none !important; }
            
            /* Force images to print with absolute URLs */
            .certificate-image {
                max-width: 100% !important;
                height: auto !important;
                page-break-inside: avoid !important;
            }
            
            /* Ensure all images load */
            img {
                max-width: 100% !important;
                height: auto !important;
            }
        }
        
        /* Preload images before printing */
        .certificate-image {
            display: block;
        }
    </style>
    <script>
        // Ensure images are loaded before printing
        window.addEventListener('beforeprint', function() {
            const images = document.querySelectorAll('.certificate-image');
            const imagePromises = Array.from(images).map(img => {
                if (!img.complete) {
                    return new Promise((resolve, reject) => {
                        img.onload = resolve;
                        img.onerror = resolve; // Continue even if image fails
                        // Set a timeout to continue after 2 seconds
                        setTimeout(resolve, 2000);
                    });
                }
                return Promise.resolve();
            });
            return Promise.all(imagePromises);
        });
    </script>

    <!-- Include Event Footer -->
    <x-events.event-footer :event="$event" />
</x-app-layout>










