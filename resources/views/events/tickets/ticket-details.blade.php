<x-app-layout title="Ticket Details - {{ $purchase->event->title }}">
    <x-slot name="head">
        <link rel="stylesheet" href="{{ asset('css/ticket-qr-improvements.css') }}">
    </x-slot>
    <x-slot name="header">
        <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Your Tickets</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {{ $purchase->event->title }} - {{ $purchase->ticketType->name ?? 'Unknown Ticket Type' }}
                        </p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('my-tickets.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to My Tickets
                        </a>
                        <a href="{{ route('events.show', $purchase->event) }}" 
                           class="inline-flex items-center px-4 py-2 bg-qricket-500 hover:bg-qricket-600 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            View Event
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Purchase Summary -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
                <div class="flex items-center space-x-4">
                    @if($purchase->event->thumbnail_url)
                        <img src="{{ $purchase->event->thumbnail_url }}" 
                             alt="{{ $purchase->event->title }}" 
                             class="w-20 h-20 object-cover rounded-lg">
                    @else
                        <div class="w-20 h-20 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                    
                    <div class="flex-1">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $purchase->event->title }}</h2>
                        <p class="text-gray-600 dark:text-gray-400">{{ $purchase->ticketType->name ?? 'Unknown Ticket Type' }}</p>
                        <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400 mt-2">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                {{ \Carbon\Carbon::parse($purchase->event->start_date)->format('M d, Y g:i A') }}
                            </div>
                            @if($purchase->event->location)
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                {{ $purchase->event->location }}
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="text-right">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <div>Quantity: <span class="font-medium">{{ $purchase->quantity }}</span></div>
                            <div>Total Paid: <span class="font-medium text-lg text-green-600 dark:text-green-400">₱{{ number_format($purchase->total_amount, 0, ',', '.') }}</span></div>
                        </div>
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Purchase ID: {{ $purchase->purchase_id }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Individual Tickets -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($purchase->ticketInformation as $index => $ticket)
                    <div class="w-full">
                        <!-- Mobile ticket -->
                        <div class="md:hidden">
                            <div class="rounded-xl overflow-hidden shadow-lg">
                                <!-- Dynamic colored header with QR and holder info -->
                                <div class="p-4 relative ticket-mobile-header" style="background: linear-gradient(to right, {{ $primaryColor }}, {{ $secondaryColor }});">
                                    @if($purchase->event->thumbnail_url)
                                        <div class="absolute inset-0 bg-cover bg-center opacity-30" style="background-image: url('{{ $purchase->event->thumbnail_url }}');"></div>
                                        <div class="absolute inset-0 ticket-mobile-overlay" style="background: linear-gradient(to right, {{ $primaryColor }}cc, {{ $secondaryColor }}cc);"></div>
                                    @endif
                                    <div class="text-xs font-semibold text-yellow-200 mb-1">Ticket Holder</div>
                                    <div class="flex items-start justify-between">
                                        <div class="pr-3">
                                            <div class="text-white font-semibold">{{ $purchase->user->full_name ?? $purchase->user->name }}</div>
                                            <div class="mt-2 text-white/90 text-sm flex items-start">
                                                <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                <div>
                                                    <div>Date & Time</div>
                                                    <div class="font-medium">{{ \Carbon\Carbon::parse($purchase->event->start_date)->format('M d, Y \a\t g:i A') }}</div>
                                                </div>
                                            </div>
                                            <div class="mt-3 text-white/80 text-xs uppercase">venue</div>
                                            <div class="text-white text-sm">{{ $purchase->event->location }}</div>
                                        </div>

                                        <!-- QR Code - Larger for mobile scanning -->
                                        <div class="ml-4">
                                            <div class="ticket-qr-container">
                                                <div id="qr-code-{{ $index }}" class="w-40 h-40 flex items-center justify-center bg-white rounded border-2 border-gray-200">
                                                    <div class="text-gray-500 text-xs">Loading QR...</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Black body with event + type/id + status -->
                                <div class="bg-black text-white p-4">
                                    <div class="text-2xl font-extrabold tracking-wide">{{ strtoupper($purchase->event->title) }}</div>

                                    <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <div class="text-gray-400">Ticket Type</div>
                                            <div class="font-semibold">{{ $purchase->ticketType->name ?? 'Unknown Ticket Type' }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-gray-400">Ticket ID</div>
                                            <div class="font-semibold">{{ $ticket->ticket_id }}</div>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <span class="inline-block px-4 py-1 text-xs rounded-full font-semibold text-white ticket-status-badge" style="background-color: {{ $accentColor }};">PAID</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Desktop ticket -->
                        <div class="hidden md:block">
                            <div class="rounded-xl shadow relative overflow-hidden ticket-desktop-header" style="background: linear-gradient(to right, {{ $primaryColor }}60, {{ $secondaryColor }}60);">
                                @if($purchase->event->thumbnail_url)
                                    <div class="absolute inset-0 bg-cover bg-center opacity-25" style="background-image: url('{{ $purchase->event->thumbnail_url }}');"></div>
                                    <div class="absolute inset-0 ticket-desktop-overlay" style="background: linear-gradient(to right, {{ $primaryColor }}60, {{ $secondaryColor }}50);"></div>
                                @endif
                                <div class="flex">
                                    <!-- Left content -->
                                    <div class="flex-1 p-6 relative">
                                        <div class="text-xs font-semibold text-emerald-900">Ticket Holder</div>
                                        <div class="text-lg font-bold text-gray-900">{{ $purchase->user->full_name ?? $purchase->user->name }}</div>

                                        <div class="mt-4 text-gray-900 flex items-start">
                                            <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <div>
                                                <div class="text-sm">Date & Time</div>
                                                <div class="font-semibold">{{ \Carbon\Carbon::parse($purchase->event->start_date)->format('M d, Y \a\t g:i A') }}</div>
                                            </div>
                                        </div>

                                        <div class="mt-4 text-sm">
                                            <div class="uppercase text-gray-700">venue</div>
                                            <div class="font-medium">{{ $purchase->event->location }}</div>
                                        </div>
                                    </div>

                                    <!-- Right side: QR replacing poster image -->
                                    <div class="w-56 p-4 flex items-center justify-center bg-white/70 relative">
                                        <div class="ticket-qr-container">
                                            <div id="qr-code-desktop-{{ $index }}" class="w-48 h-48 flex items-center justify-center bg-white rounded border-2 border-gray-200">
                                                <div class="text-gray-500 text-xs">Loading QR...</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bottom dark strip like a ticket footer -->
                                <div class="bg-black text-white p-5 rounded-b-xl">
                                    <div class="text-2xl font-extrabold tracking-wide mb-3">{{ strtoupper($purchase->event->title) }}</div>
                                    <div class="grid grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <div class="text-gray-400">Ticket Type</div>
                                            <div class="font-semibold">{{ $purchase->ticketType->name ?? 'Unknown Ticket Type' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-gray-400">Ticket ID</div>
                                            <div class="font-semibold">{{ $ticket->ticket_id }}</div>
                                        </div>
                                        <div class="text-right">
                                            <span class="inline-block px-4 py-1 text-xs rounded-full font-semibold text-white ticket-status-badge" style="background-color: {{ $accentColor }};">PAID</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Ticket Actions -->
                        <div class="mt-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                            <div class="flex space-x-2">
                                <button onclick="downloadQRCode({{ $index }})" 
                                        class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Download
                                </button>
                                <button onclick="shareTicket({{ $index }})" 
                                        class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-qricket-500 hover:bg-qricket-600 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                                    </svg>
                                    Share
                                </button>
                                <a href="{{ route('tickets.individual.details', $ticket->id) }}" 
                                   class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Important Notice -->
            <div class="mt-8 bg-blue-50 dark:bg-blue-900 rounded-lg p-6">
                <div class="flex items-start">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">Important Information</h3>
                        <ul class="space-y-1 text-sm text-blue-800 dark:text-blue-200">
                            <li>• Each QR code is unique and can only be used once</li>
                            <li>• Present your QR code at the event entrance for validation</li>
                            <li>• Keep your tickets secure and do not share them publicly</li>
                            <li>• Screenshots of QR codes are acceptable for entry</li>
                            <li>• Contact event organizers if you have any issues</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function downloadQRCode(ticketIndex) {
            const qrImage = document.querySelector(`img[alt="QR Code for Ticket #${ticketIndex + 1}"]`);
            const link = document.createElement('a');
            link.href = qrImage.src;
            link.download = `ticket-${ticketIndex + 1}-{{ $purchase->event->title }}-qr-code.svg`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function shareTicket(ticketIndex) {
            if (navigator.share) {
                navigator.share({
                    title: 'My Event Ticket - {{ $purchase->event->title }}',
                    text: `Ticket #${ticketIndex + 1} for {{ $purchase->event->title }}`,
                    url: window.location.href
                });
            } else {
                // Fallback: copy to clipboard
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Ticket link copied to clipboard!');
                });
            }
        }
    </script>
    <!-- QR Code JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Generate QR codes for each ticket using encrypted ticket_id
            @foreach($purchase->ticketInformation as $index => $ticket)
                // Use encrypted ticket_id for QR code (compact format)
                @php
                    $qrData = $ticket->getEncryptedTicketId();
                @endphp
                
                // Mobile QR code for ticket {{ $index }}
                const mobileContainer{{ $index }} = document.getElementById('qr-code-{{ $index }}');
                if (mobileContainer{{ $index }}) {
                    mobileContainer{{ $index }}.innerHTML = '';
                    try {
                        new QRCode(mobileContainer{{ $index }}, {
                            text: @json($qrData),
                            width: 100,
                            height: 100,
                            colorDark: "#000000",
                            colorLight: "#ffffff",
                            correctLevel: QRCode.CorrectLevel.M,
                            margin: 1,
                            useSVG: false
                        });
                    } catch (error) {
                        console.error('Failed to generate mobile QR code for ticket {{ $index }}:', error);
                        mobileContainer{{ $index }}.innerHTML = '<div class="text-red-500 text-xs text-center p-2">QR Error</div>';
                    }
                }
                
                // Desktop QR code for ticket {{ $index }}
                const desktopContainer{{ $index }} = document.getElementById('qr-code-desktop-{{ $index }}');
                if (desktopContainer{{ $index }}) {
                    desktopContainer{{ $index }}.innerHTML = '';
                    try {
                        new QRCode(desktopContainer{{ $index }}, {
                            text: @json($qrData),
                            width: 192,
                            height: 192,
                            colorDark: "#000000",
                            colorLight: "#ffffff",
                            correctLevel: QRCode.CorrectLevel.M,
                            margin: 1,
                            useSVG: false
                        });
                    } catch (error) {
                        console.error('Failed to generate desktop QR code for ticket {{ $index }}:', error);
                        desktopContainer{{ $index }}.innerHTML = '<div class="text-red-500 text-xs text-center p-2">QR Error</div>';
                    }
                }
            @endforeach
        });
    </script>
</x-app-layout>

