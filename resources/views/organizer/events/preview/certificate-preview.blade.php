@props([
    'certificateData' => [],
    'certificateEnabled' => false,
    'event' => null,
    'fallbackWhen' => '',
    'fallbackGiven' => ''
])

@php
    // Only show certificate when the feature is explicitly enabled
    $hasCertificate = ($certificateEnabled === true);
    // If enabled but no data, build a sensible default so a template renders
    if ($hasCertificate && empty($certificateData)) {
        $certificateData = [
            'title' => 'Certificate of Participation',
            'subtitle' => $event?->title,
            'recipient_name' => 'Sample Recipient',
            'description' => 'In recognition of participation in the event.',
            'when' => $fallbackWhen,
            'given' => $fallbackGiven,
            'theme_primary' => '#10b981',
            'theme_secondary' => '#f59e0b',
            'theme_border' => '#10b981',
            'signatories' => [],
        ];
    }
@endphp

<div class="space-y-6">
    @if($hasCertificate)
        <!-- Certificate Preview -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Certificate Header -->
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M9 8h6M5 4h14a2 2 0 012 2v12l-4-2-4 2-4-2-4 2V6a2 2 0 012-2z"/>
                    </svg>
                    Certificate Preview
                </h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">This is how the certificate will appear to attendees</p>
            </div>
            
            <!-- Certificate Content -->
            <div class="p-6">
                <div class="transform scale-75 origin-top-left" style="width: 133.33%;">
                    @include('organizer.events.preview.certificate-template', [
                        'certificateData' => $certificateData,
                        'event' => $event,
                        'fallbackWhen' => $fallbackWhen,
                        'fallbackGiven' => $fallbackGiven,
                    ])
                </div>
            </div>
        </div>
        
     
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M9 8h6M5 4h14a2 2 0 012 2v12l-4-2-4 2-4-2-4 2V6a2 2 0 012-2z"/>
            </svg>
            @if($certificateEnabled)
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">No certificate provided</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Certificate is enabled but no template has been configured.</p>
            @else
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">Certificate disabled</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Certificate feature is not enabled for this event.</p>
            @endif
        </div>
    @endif
</div>
