@php
    $p = $certificateData['theme_primary'] ?? '#10b981';
    $s = $certificateData['theme_secondary'] ?? '#f59e0b';
    $b = $certificateData['theme_border'] ?? $p ?? '#10b981';
    $gradStyle = "background-image: linear-gradient(to right, {$p}, {$s});";
    // Resolve R2 logo URLs or fallback to local asset route
    $eventId = $event->id ?? (is_object($event) ? ($event->id ?? null) : null) ?? request()->get('id');
    $leftLogoRaw = $certificateData['logo_left_url'] ?? null;
    $rightLogoRaw = $certificateData['logo_right_url'] ?? null;
    $leftLogo = $leftLogoRaw;
    $rightLogo = $rightLogoRaw;
    
    // Process left logo URL - always try to get logo if eventId exists
    if ($eventId) {
        if (!empty($leftLogoRaw)) {
            // Check if URL is an R2 URL (needs conversion to route)
            $isR2Url = (strpos($leftLogoRaw, 'cloudflarestorage.com') !== false) || 
                       (strpos($leftLogoRaw, 'r2.dev') !== false);
            
            if ($isR2Url) {
                // Convert R2 URL to route, then to absolute URL
                $leftLogo = url(route('organizer.events.certificate.asset', ['file' => 'logo_left.png', 'id' => $eventId]));
            } else {
                // Check if it's a relative URL (route) or absolute URL
                $parsed = parse_url($leftLogoRaw);
                if (!isset($parsed['scheme'])) {
                    // It's a relative URL (route), convert to absolute
                    $leftLogo = url($leftLogoRaw);
                } else {
                    // It's already an absolute URL, use as-is
                    $leftLogo = $leftLogoRaw;
                }
            }
        } else {
            // No URL in data, but try route anyway (file might exist in storage)
            $leftLogo = url(route('organizer.events.certificate.asset', ['file' => 'logo_left.png', 'id' => $eventId]));
    }
    }
    
    // Process right logo URL - always try to get logo if eventId exists
    if ($eventId) {
        if (!empty($rightLogoRaw)) {
            // Check if URL is an R2 URL (needs conversion to route)
            $isR2Url = (strpos($rightLogoRaw, 'cloudflarestorage.com') !== false) || 
                       (strpos($rightLogoRaw, 'r2.dev') !== false);
            
            if ($isR2Url) {
                // Convert R2 URL to route, then to absolute URL
                $rightLogo = url(route('organizer.events.certificate.asset', ['file' => 'logo_right.png', 'id' => $eventId]));
            } else {
                // Check if it's a relative URL (route) or absolute URL
                $parsed = parse_url($rightLogoRaw);
                if (!isset($parsed['scheme'])) {
                    // It's a relative URL (route), convert to absolute
                    $rightLogo = url($rightLogoRaw);
                } else {
                    // It's already an absolute URL, use as-is
                    $rightLogo = $rightLogoRaw;
    }
            }
        } else {
            // No URL in data, but try route anyway (file might exist in storage)
            $rightLogo = url(route('organizer.events.certificate.asset', ['file' => 'logo_right.png', 'id' => $eventId]));
        }
    }
    
    // Determine if logos should be shown (if URL exists and eventId exists, show it)
    $hasLeftLogo = !empty($leftLogo) && !empty($eventId);
    $hasRightLogo = !empty($rightLogo) && !empty($eventId);
    
    // Generate absolute URLs for signature assets
    $signatureLeftUrl = null;
    $signatureRightUrl = null;
    $signatureUrls = [];
    if ($eventId) {
        if (!empty($certificateData['signature_left_path']) || !empty($certificateData['sig_left_name'])) {
            $signatureLeftUrl = url(route('organizer.events.certificate.asset', ['file' => 'signature_left.enc', 'id' => $eventId]));
        }
        if (!empty($certificateData['signature_right_path']) || !empty($certificateData['sig_right_name'])) {
            $signatureRightUrl = url(route('organizer.events.certificate.asset', ['file' => 'signature_right.enc', 'id' => $eventId]));
        }
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($certificateData["sig_{$i}_signature"]) || !empty($certificateData["sig_{$i}_name"])) {
                $signatureUrls[$i] = url(route('organizer.events.certificate.asset', ['file' => "signature_{$i}.enc", 'id' => $eventId]));
            }
        }
    }
@endphp

<div class="page max-w-5xl mx-auto bg-white border-8 rounded-lg shadow-lg relative overflow-visible landscape-scale" style="aspect-ratio: 11/8.5; border-color: {{ $b ?? $p ?? '#10b981' }}; padding-top: 1.5rem; padding-bottom: 1.5rem;">
    <div class="band-top absolute inset-x-0 top-0 h-28 border-b-4 border-yellow-400" style="clip-path:polygon(0 0, 100% 0, 100% 60%, 0 100%); {{ $gradStyle ?? 'background-image: linear-gradient(to right, #10b981, #f59e0b);' }}"></div>
    <div class="band-bottom absolute inset-x-0 bottom-0 h-28 border-t-4 border-yellow-400" style="clip-path:polygon(0 40%, 100% 0, 100% 100%, 0 100%); {{ $gradStyle ?? 'background-image: linear-gradient(to right, #10b981, #f59e0b);' }}"></div>

    <div class="px-10 pt-16 pb-8 relative z-10">
            <div class="grid grid-cols-3 items-center">
            <div class="justify-self-start">
                @if($eventId)
                <img id="logo_left_img" src="{{ $hasLeftLogo ? $leftLogo : (url(route('organizer.events.certificate.asset', ['file' => 'logo_left.png', 'id' => $eventId]))) }}" alt="Left Logo" class="w-20 h-20 object-contain certificate-image" onload="this.style.display='block';" onerror="this.style.display='none';" style="{{ $hasLeftLogo ? 'display: block;' : 'display: none;' }} max-width: 80px; max-height: 80px;">
                @endif
            </div>
            <div class="text-center">
                <div id="org_line1" class="text-sm font-semibold uppercase text-gray-700">{{ $certificateData['org_line1'] ?? 'Organization Name' }}</div>
                <div id="org_line2" class="text-sm font-medium text-gray-700">{{ $certificateData['org_line2'] ?? 'Department/Division' }}</div>
                <div class="mt-2">
                    <div id="org_school" class="text-2xl font-bold" style="color: {{ $p ?? '#10b981' }};">{{ trim($certificateData['org_school'] ?? '') !== '' ? $certificateData['org_school'] : 'Event Organizer' }}</div>
                </div>
                <div class="text-sm text-gray-600">Ticket ID: <span id="ticket_id_preview">{{ $certificateData['ticket_id_preview'] ?? '—' }}</span></div>
            </div>
            <div class="justify-self-end">
                @if($eventId)
                <img id="logo_right_img" src="{{ $hasRightLogo ? $rightLogo : (url(route('organizer.events.certificate.asset', ['file' => 'logo_right.png', 'id' => $eventId]))) }}" alt="Right Logo" class="w-20 h-20 object-contain certificate-image" onload="this.style.display='block';" onerror="this.style.display='none';" style="{{ $hasRightLogo ? 'display: block;' : 'display: none;' }} max-width: 80px; max-height: 80px;">
                @endif
            </div>
        </div>

        <div class="mt-6 text-center px-4" style="overflow: visible;">
            <div class="certificate-title" style="font-family:'Great Vibes', cursive; color: {{ $p ?? '#10b981' }}">Certificate of Participation</div>
        </div>

        <div class="mt-4 text-center px-8 md:px-16">
            <p class="text-base" style="color: {{ $p ?? '#10b981' }};">This certificate is proudly presented to</p>
            <div id="recipient_name" class="mt-4 font-serif text-4xl font-bold" style="color: {{ $p ?? '#10b981' }};">{{ $certificateData['recipient_name'] ?? 'Participant Name' }}</div>
            <p class="mt-4 text-base" style="color: {{ $p ?? '#10b981' }};">for his/her active participation in the event</p>
            <p class="mt-3 text-base" id="event_when" style="color: {{ $p ?? '#10b981' }};">{{ trim($certificateData['event_when'] ?? '') !== '' ? $certificateData['event_when'] : ($fallbackWhen ?? 'Event Date') }}</p>
            @if(trim($certificateData['event_given'] ?? '') !== '' || trim($fallbackGiven ?? '') !== '')
                <p class="mt-3 text-base" id="event_given" style="color: {{ $p ?? '#10b981' }};">at {{ trim($certificateData['event_given'] ?? '') !== '' ? $certificateData['event_given'] : ($fallbackGiven ?? '') }}</p>
            @endif
            <p class="mt-4 text-base" style="color: {{ $p ?? '#10b981' }};">and this certificate is given at this time of the event.</p>
        </div>

        <div class="mt-8" id="signatories-container">
            @php
                $signatoryCount = 0;
                $maxSignatories = 4;
                
                // Count existing signatories from the new format
                for ($i = 1; $i <= $maxSignatories; $i++) {
                    if (!empty($certificateData["sig_{$i}_name"]) || !empty($certificateData["sig_{$i}_title"])) {
                        $signatoryCount++;
                    }
                }
                
                // Fallback to old format if no new format data
                if ($signatoryCount == 0) {
                    if (!empty($certificateData['sig_left_name']) || !empty($certificateData['sig_right_name'])) {
                        $signatoryCount = 2; // Use old format
                    } else {
                        $signatoryCount = 1; // Default
                    }
                }
                
                // Determine grid layout based on count
                $gridCols = $signatoryCount <= 2 ? 'grid-cols-1 md:grid-cols-2' : 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4';
                
                // Debug: Log signatory data
                \Log::info('Certificate signatory data', [
                    'signatory_count' => $signatoryCount,
                    'certificate_data_keys' => array_keys($certificateData),
                    'sig_1_name' => $certificateData['sig_1_name'] ?? 'not set',
                    'sig_1_title' => $certificateData['sig_1_title'] ?? 'not set',
                    'sig_left_name' => $certificateData['sig_left_name'] ?? 'not set',
                    'sig_right_name' => $certificateData['sig_right_name'] ?? 'not set'
                ]);
            @endphp
            
            <div class="grid {{ $gridCols ?? 'grid-cols-1 md:grid-cols-2' }} gap-6 px-3 md:px-6">
                @if(($signatoryCount ?? 0) == 0 || (!empty($certificateData['sig_left_name']) && empty($certificateData['sig_1_name'])))
                    {{-- Fallback to old format --}}
                    <div class="text-center">
                        <div class="relative mx-auto w-4/5 h-16">
                            <div class="absolute inset-x-0 bottom-0 h-0.5" style="background-color: {{ $p ?? '#10b981' }};"></div>                                                             
                            @if((!empty($certificateData['signature_left_path']) || !empty($certificateData['sig_left_name'])) && $signatureLeftUrl)
                                <img src="{{ $signatureLeftUrl }}" alt="Left Signature" class="absolute left-1/2 -translate-x-1/2 h-10 object-contain select-none pointer-events-none certificate-image" style="bottom: 2px; display: none;" onerror="this.style.display='none'" onload="this.style.display='block'">                                                                                 
                            @endif
                        </div>
                        <div id="sig_left_name" class="mt-2 font-semibold" style="color: {{ $p ?? '#10b981' }};">{{ $certificateData['sig_left_name'] ?? 'Event Coordinator' }}</div>          
                        <div id="sig_left_title" class="text-sm" style="color: {{ $p ?? '#10b981' }};">{{ $certificateData['sig_left_title'] ?? 'Signature' }}</div>                           
                    </div>
                    <div class="text-center">
                        <div class="relative mx-auto w-4/5 h-16">
                            <div class="absolute inset-x-0 bottom-0 h-0.5" style="background-color: {{ $p ?? '#10b981' }};"></div>                                                             
                            @if((!empty($certificateData['signature_right_path']) || !empty($certificateData['sig_right_name'])) && $signatureRightUrl)
                                <img src="{{ $signatureRightUrl }}" alt="Right Signature" class="absolute left-1/2 -translate-x-1/2 h-10 object-contain select-none pointer-events-none certificate-image" style="bottom: 2px; display: none;" onerror="this.style.display='none'" onload="this.style.display='block'">                                                                               
                            @endif
                        </div>
                        <div id="sig_right_name" class="mt-2 font-semibold sig-line" style="color: {{ $p ?? '#10b981' }};">{{ $certificateData['sig_right_name'] ?? 'Event Director' }}</div>           
                        <div id="sig_right_title" class="text-sm sig-role" style="color: {{ $p ?? '#10b981' }};">{{ $certificateData['sig_right_title'] ?? 'Signature' }}</div>                         
                    </div>
                @else
                    {{-- New dynamic format --}}
                    @for($i = 1; $i <= ($maxSignatories ?? 4); $i++)
                        @if(!empty($certificateData["sig_{$i}_name"]) || !empty($certificateData["sig_{$i}_title"]))
                            <div class="text-center">
                                <div class="relative mx-auto w-4/5 h-16">
                                    <div class="absolute inset-x-0 bottom-0 h-0.5" style="background-color: {{ $p ?? '#10b981' }};"></div>                                                             
                                    @if((!empty($certificateData["sig_{$i}_signature"]) || !empty($certificateData["sig_{$i}_name"])) && isset($signatureUrls[$i]))
                                        <img src="{{ $signatureUrls[$i] }}" alt="Signature {{ $i }}" class="absolute left-1/2 -translate-x-1/2 h-10 object-contain select-none pointer-events-none certificate-image" style="bottom: 2px; display: none;" onerror="this.style.display='none'" onload="this.style.display='block'">                                                                                 
                                    @endif
                                </div>
                                <div id="sig_{{$i}}_name" class="mt-2 font-semibold sig-line" style="color: {{ $p ?? '#10b981' }};">{{ $certificateData["sig_{$i}_name"] ?? 'Signatory ' . $i }}</div>          
                                <div id="sig_{{$i}}_title" class="text-sm sig-role" style="color: {{ $p ?? '#10b981' }};">{{ $certificateData["sig_{$i}_title"] ?? 'Signature' }}</div>                           
                            </div>
                        @endif
                    @endfor
                @endif
            </div>
        </div>

        <div class="mt-6 text-center text-xs text-gray-500">
            This certificate is issued by <span class="font-medium">{{ $certificateData['org_school'] ?? 'Event Organizer' }}</span>. Reproduction or alteration without authorization is prohibited.
        </div>
    </div>
</div>


