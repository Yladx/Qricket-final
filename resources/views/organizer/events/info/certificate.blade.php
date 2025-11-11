<x-unified-layout :layout-type="$layoutType" :title="'Event Certificate - ' . $event->title" :page-title="'Certificate Information'">
    <x-slot name="header">
        <x-manage-header :event="$event" />
    </x-slot>
    
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Certificate Information</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">View certificate design and information</p>
                    </div>

                    <!-- Use the same certificate preview component as event preview for consistency -->
                    @include('organizer.events.preview.certificate-preview', [
                        'certificateData' => $certificateData ?? [],
                        'certificateEnabled' => $certificateEnabled ?? ($event->certificate_enabled ?? false),
                        'event' => $event,
                        'fallbackWhen' => $fallbackWhen ?? '',
                        'fallbackGiven' => $fallbackGiven ?? ''
                    ])
                </div>
            </div>
        </div>
    </div>
</x-unified-layout>
