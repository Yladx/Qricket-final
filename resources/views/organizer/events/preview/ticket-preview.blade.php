@props([
    // Core info
    'holderName' => 'John Doe',
    'eventTitle' => 'ART EXHIBITION',
    'dateTime' => '9/8/2025 at 10:00',
    'venue' => 'Art Gallery, Bonifacio Global City',
    'ticketType' => 'General Admission',
    'ticketId' => 'TKT-2025-4',
    'status' => 'PAID', // PAID | UNPAID | PENDING
    // QR content (display only for preview)
    'qrText' => 'QRICKET-1234567890',
    // Optional background image (event thumbnail)
    'bgImage' => null,
    // Ticket colors
    'primaryColor' => '#10b981',
    'secondaryColor' => '#f59e0b',
    'accentColor' => '#059669',
])

<div class="w-full">
    <!-- Mobile ticket -->
    <div class="md:hidden">
        <div class="rounded-xl overflow-hidden shadow-lg">
            <!-- Top section: Colored gradient with holder info -->
            <div class="p-5 relative ticket-mobile-header" style="background: linear-gradient(to bottom right, {{ $primaryColor }}, {{ $secondaryColor }});">
                @if($bgImage)
                    <div class="absolute inset-0 bg-cover bg-center opacity-20" style="background-image: url('{{ $bgImage }}');"></div>
                    <div class="absolute inset-0 ticket-mobile-overlay" style="background: linear-gradient(to bottom right, {{ $primaryColor }}dd, {{ $secondaryColor }}dd);"></div>
                @endif
                
                <div class="relative z-10">
                    <!-- QR Code -->
                    <div class="flex justify-center mb-5">
                        <div class="bg-white p-4 rounded-lg shadow-md border border-white/30">
                            <div id="qr-code-mobile-preview" class="w-40 h-40 flex items-center justify-center bg-white rounded-lg">
                                <div class="text-gray-500 text-xs">Loading QR Code...</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Holder Name -->
                    <div class="text-center mb-4">
                        <div class="text-xs font-semibold text-gray-800 mb-1">Ticket Holder</div>
                        <div class="text-gray-900 font-bold text-xl">{{ $holderName }}</div>
                    </div>
                    
                    <!-- Date & Venue -->
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center text-gray-800 justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span class="font-medium">{{ $dateTime }}</span>
                        </div>
                        <div class="flex items-center text-gray-800 justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="font-medium">{{ $venue }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom section: Black with event details -->
            <div class="bg-black text-white p-5">
                <div class="text-2xl font-extrabold tracking-tight mb-4">{{ $eventTitle }}</div>

                <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                    <div>
                        <div class="text-gray-400 text-xs uppercase mb-1">Ticket Type</div>
                        <div class="font-semibold text-white">{{ $ticketType }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-gray-400 text-xs uppercase mb-1">Ticket ID</div>
                        <div class="font-semibold text-white">{{ $ticketId }}</div>
                    </div>
                </div>

                <div class="flex justify-center">
                    @php
                        $statusColor = [
                            'PAID' => $accentColor,
                            'PENDING' => '#f59e0b',
                            'UNPAID' => '#dc2626',
                        ][$status] ?? '#6b7280';
                    @endphp
                    <span class="inline-block px-5 py-2 text-xs font-bold rounded-full ticket-status-badge" style="background-color: {{ $statusColor }};">{{ strtoupper($status) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Desktop ticket -->
    <div class="hidden md:block">
        <div class="rounded-xl shadow-lg overflow-hidden">
            <!-- Top section: Colored gradient -->
            <div class="relative ticket-desktop-header" style="background: linear-gradient(to bottom right, {{ $primaryColor }}, {{ $secondaryColor }});">
                @if($bgImage)
                    <div class="absolute inset-0 bg-cover bg-center opacity-20" style="background-image: url('{{ $bgImage }}');"></div>
                    <div class="absolute inset-0 ticket-desktop-overlay" style="background: linear-gradient(to bottom right, {{ $primaryColor }}dd, {{ $secondaryColor }}dd);"></div>
                @endif
                
                <div class="flex items-center p-6 relative z-10">
                    <!-- Left: Holder info -->
                    <div class="flex-1">
                        <div class="text-xs font-semibold text-gray-800 mb-1">Ticket Holder</div>
                        <div class="text-xl font-bold text-gray-900 mb-5">{{ $holderName }}</div>
                        
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center text-gray-800">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="font-medium">{{ $dateTime }}</span>
                            </div>
                            <div class="flex items-center text-gray-800">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span class="font-medium">{{ $venue }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right: QR Code -->
                    <div class="w-56 flex items-center justify-center">
                        <div class="bg-white p-4 rounded-lg shadow-md border border-white/30">
                            <div id="qr-code-desktop-preview" class="w-52 h-52 flex items-center justify-center bg-white rounded-lg">
                                <div class="text-gray-500 text-xs">Loading QR Code...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom section: Black with event details -->
            <div class="bg-black text-white p-6">
                <div class="text-3xl font-extrabold tracking-tight mb-4">{{ $eventTitle }}</div>
                
                <div class="grid grid-cols-3 gap-6 text-sm mb-4">
                    <div>
                        <div class="text-gray-400 text-xs uppercase mb-1">Ticket Type</div>
                        <div class="font-semibold text-white text-base">{{ $ticketType }}</div>
                    </div>
                    <div>
                        <div class="text-gray-400 text-xs uppercase mb-1">Ticket ID</div>
                        <div class="font-semibold text-white text-base">{{ $ticketId }}</div>
                    </div>
                    <div class="text-right">
                        @php
                            $statusColor = [
                                'PAID' => $accentColor,
                                'PENDING' => '#f59e0b',
                                'UNPAID' => '#dc2626',
                            ][$status] ?? '#6b7280';
                        @endphp
                        <span class="inline-block px-5 py-2 text-xs font-bold rounded-full ticket-status-badge" style="background-color: {{ $statusColor }};">{{ strtoupper($status) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Code JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Use QR code data (encrypted) if available, otherwise fallback to ticket ID
        // QR code data contains encrypted ticket information for validation
        const qrData = @json($qrText ?? ($ticketId ?? "TKT-" + Date.now()));
        
        // Function to generate QR code
        function generateQRCode(container, size) {
            if (container && qrData) {
                container.innerHTML = ''; // Clear loading text
                try {
                    new QRCode(container, {
                        text: qrData,
                        width: size,
                        height: size,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.M,
                        margin: 2,
                        useSVG: false
                    });
                } catch (error) {
                    console.error('Failed to generate QR code:', error);
                    container.innerHTML = '<div class="text-red-500 text-xs text-center p-2">QR Code Error</div>';
                }
            }
        }
        
        // Generate QR codes for both mobile and desktop versions
        generateQRCode(document.getElementById('qr-code-mobile-preview'), 160);
        generateQRCode(document.getElementById('qr-code-desktop-preview'), 208);
    });
</script>



