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

                    @php
                        // Use certificateEnabled from controller, or check from event model
                        $certificateEnabled = isset($certificateEnabled) ? (bool)$certificateEnabled : (bool)($event->certificate_enabled ?? false);
                        
                        // If certificate data is not passed, try to load it (fallback)
                        if (empty($certificateData)) {
                            try {
                                $candidates = [
                                    "events/{$event->id}/certificate/certificate.json",
                                    "events/{$event->id}/certificate_data.json",
                                    "events/{$event->id}/certificate/certificate_data.json",
                                    "events/{$event->id}/certificate.json",
                                ];
                                
                                foreach ($candidates as $certificateDataPath) {
                                    if (\Storage::disk('r2')->exists($certificateDataPath)) {
                                        $certificateDataJson = \Storage::disk('r2')->get($certificateDataPath);
                                        $data = json_decode($certificateDataJson, true) ?? [];
                                        if (!empty($data)) {
                                            $certificateData = $data;
                                            break;
                                        }
                                    }
                                }
                            } catch (\Exception $e) {
                                \Log::error('Failed to load certificate data: ' . $e->getMessage());
                            }
                        }
                        
                        // Determine if we should show the certificate
                        $hasCertificateData = !empty($certificateData);
                        $shouldShowCertificate = $certificateEnabled && $hasCertificateData;
                    @endphp

                    @if($shouldShowCertificate)
                        <div class="mb-8">
                            <!-- Certificate Settings -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Certificate Settings</h4>
                                    <div class="space-y-3">
                                        @if(isset($certificateData['template']))
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Template:</span>
                                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($certificateData['template']) }}</span>
                                            </div>
                                        @endif
                                        
                                        @if(isset($certificateData['orientation']))
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Orientation:</span>
                                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($certificateData['orientation']) }}</span>
                                            </div>
                                        @endif
                                        
                                        @if(isset($certificateData['background_color']))
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Background Color:</span>
                                                <div class="flex items-center space-x-2">
                                                    <div class="w-6 h-6 rounded border border-gray-300 dark:border-gray-600" style="background-color: {{ $certificateData['background_color'] }}"></div>
                                                    <span class="text-sm text-gray-900 dark:text-gray-100">{{ $certificateData['background_color'] }}</span>
                                                </div>
                                            </div>
                                        @endif
                                        
                                        @if(isset($certificateData['border_color']))
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Border Color:</span>
                                                <div class="flex items-center space-x-2">
                                                    <div class="w-6 h-6 rounded border border-gray-300 dark:border-gray-600" style="background-color: {{ $certificateData['border_color'] }}"></div>
                                                    <span class="text-sm text-gray-900 dark:text-gray-100">{{ $certificateData['border_color'] }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div>
                                    <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Certificate Content</h4>
                                    <div class="space-y-3">
                                        @if(isset($certificateData['org_line1']))
                                            <div>
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Organization Line 1:</span>
                                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $certificateData['org_line1'] }}</p>
                                            </div>
                                        @endif
                                        
                                        @if(isset($certificateData['recipient_name']))
                                            <div>
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Recipient Name:</span>
                                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $certificateData['recipient_name'] ?? 'Participant Name' }}</p>
                                            </div>
                                        @endif
                                        
                                        @if(isset($certificateData['event_title']))
                                            <div>
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Event Title:</span>
                                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $certificateData['event_title'] ?? $event->title }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Certificate Preview -->
                            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                                <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Certificate Preview</h4>
                                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                                    <div class="p-4 overflow-x-auto overflow-y-auto max-h-[800px]">
                                        <div class="flex justify-center min-w-full">
                                            @include('organizer.events.preview.certificate-template', [
                                                'certificateData' => $certificateData, 
                                                'event' => $event, 
                                                'fallbackWhen' => $fallbackWhen ?? 'Event Date', 
                                                'fallbackGiven' => $fallbackGiven ?? 'Event Location'
                                            ])
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M9 8h6M5 4h14a2 2 0 012 2v12l-4-2-4 2-4-2-4 2V6a2 2 0 012-2z"></path>
                            </svg>
                            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">
                                @if($certificateEnabled === false)
                                    Certificate Disabled
                                @else
                                    No Certificate Configured
                                @endif
                            </h3>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                @if($certificateEnabled === false)
                                    Certificate generation is disabled for this event. Attendees will not receive certificates.
                                @else
                                    This event doesn't have a certificate template configured yet.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-unified-layout>

