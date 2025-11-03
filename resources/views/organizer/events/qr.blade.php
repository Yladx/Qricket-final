<x-unified-layout :layout-type="'organizer'" :title="'QR Code Generator - ' . $event->title" :page-title="'QR Code'">
    <x-slot name="header">
        <x-manage-header :event="$event" />
    </x-slot>
    
    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Event Info -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6">
                        <div class="flex items-start space-x-4">
                            @if($event->poster)
                                <img src="{{ asset('storage/' . $event->poster) }}" alt="{{ $event->title }}" class="w-20 h-24 object-cover rounded-lg">
                            @else
                                <div class="w-20 h-24 bg-gray-300 dark:bg-gray-600 rounded-lg flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $event->title }}</h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $event->venue }}, {{ $event->city }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $event->start_date->format('M j, Y g:i A') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- QR Code Options -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Event Page QR -->
                        <div class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-6">
                            <div class="text-center">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Event Page</h3>
                                <div id="event-qr" class="flex justify-center mb-4">
                                    <!-- QR code will be generated here -->
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Direct link to event page</p>
                                <button onclick="downloadQR('event-qr', 'event-page-qr')" class="px-4 py-2 bg-qricket-500 hover:bg-qricket-600 text-white rounded-lg transition-colors">
                                    Download QR
                                </button>
                            </div>
                        </div>

                        <!-- Ticket Purchase QR -->
                        <div class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-6">
                            <div class="text-center">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Buy Tickets</h3>
                                <div id="tickets-qr" class="flex justify-center mb-4">
                                    <!-- QR code will be generated here -->
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Direct link to ticket purchase</p>
                                <button onclick="downloadQR('tickets-qr', 'buy-tickets-qr')" class="px-4 py-2 bg-qricket-500 hover:bg-qricket-600 text-white rounded-lg transition-colors">
                                    Download QR
                                </button>
                            </div>
                        </div>

                        <!-- Share QR -->
                        <div class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-6">
                            <div class="text-center">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Share Event</h3>
                                <div id="share-qr" class="flex justify-center mb-4">
                                    <!-- QR code will be generated here -->
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Share event with others</p>
                                <button onclick="downloadQR('share-qr', 'share-event-qr')" class="px-4 py-2 bg-qricket-500 hover:bg-qricket-600 text-white rounded-lg transition-colors">
                                    Download QR
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Custom QR Generator -->
                    <div class="mt-8 bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Custom QR Code</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="custom-url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Custom URL</label>
                                <input type="url" id="custom-url" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-qricket-500 dark:bg-gray-800 dark:text-white" placeholder="https://example.com">
                                <button onclick="generateCustomQR()" class="mt-2 px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors">
                                    Generate QR
                                </button>
                            </div>
                            <div class="text-center">
                                <div id="custom-qr" class="flex justify-center mb-4">
                                    <!-- Custom QR code will be generated here -->
                                </div>
                                <button onclick="downloadQR('custom-qr', 'custom-qr')" class="px-4 py-2 bg-qricket-500 hover:bg-qricket-600 text-white rounded-lg transition-colors" style="display: none;">
                                    Download QR
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- QR Code Generation Script -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <script>
        // Generate QR codes when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const baseUrl = window.location.origin;
            const eventUrl = `${baseUrl}/events/{{ $event->id }}`;
            const ticketsUrl = `${baseUrl}/events/{{ $event->id }}/tickets`;
            const shareUrl = `${baseUrl}/events/{{ $event->id }}/share`;

            // Generate Event Page QR
            QRCode.toCanvas(document.createElement('canvas'), eventUrl, {
                width: 200,
                margin: 2,
                color: {
                    dark: '#000000',
                    light: '#FFFFFF'
                }
            }, function (error, canvas) {
                if (error) console.error('Event QR Error:', error);
                else {
                    canvas.id = 'event-qr-canvas';
                    document.getElementById('event-qr').appendChild(canvas);
                }
            });

            // Generate Tickets QR
            QRCode.toCanvas(document.createElement('canvas'), ticketsUrl, {
                width: 200,
                margin: 2,
                color: {
                    dark: '#000000',
                    light: '#FFFFFF'
                }
            }, function (error, canvas) {
                if (error) console.error('Tickets QR Error:', error);
                else {
                    canvas.id = 'tickets-qr-canvas';
                    document.getElementById('tickets-qr').appendChild(canvas);
                }
            });

            // Generate Share QR
            QRCode.toCanvas(document.createElement('canvas'), shareUrl, {
                width: 200,
                margin: 2,
                color: {
                    dark: '#000000',
                    light: '#FFFFFF'
                }
            }, function (error, canvas) {
                if (error) console.error('Share QR Error:', error);
                else {
                    canvas.id = 'share-qr-canvas';
                    document.getElementById('share-qr').appendChild(canvas);
                }
            });
        });

        // Download QR Code
        function downloadQR(elementId, filename) {
            const canvas = document.getElementById(elementId + '-canvas');
            if (canvas) {
                const link = document.createElement('a');
                link.download = filename + '.png';
                link.href = canvas.toDataURL();
                link.click();
            }
        }

        // Generate Custom QR
        function generateCustomQR() {
            const url = document.getElementById('custom-url').value;
            if (!url) {
                alert('Please enter a URL');
                return;
            }

            const customQrDiv = document.getElementById('custom-qr');
            customQrDiv.innerHTML = ''; // Clear previous QR

            QRCode.toCanvas(document.createElement('canvas'), url, {
                width: 200,
                margin: 2,
                color: {
                    dark: '#000000',
                    light: '#FFFFFF'
                }
            }, function (error, canvas) {
                if (error) {
                    console.error('Custom QR Error:', error);
                    alert('Error generating QR code');
                } else {
                    canvas.id = 'custom-qr-canvas';
                    customQrDiv.appendChild(canvas);
                    document.querySelector('#custom-qr + button').style.display = 'inline-block';
                }
            });
        }
    </script>
</x-app-layout>
