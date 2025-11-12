<x-app-layout title="Ticket Details">
    <x-slot name="head">
        <link rel="stylesheet" href="{{ asset('css/ticket-qr-improvements.css') }}">
    </x-slot>
    <x-slot name="header">
        <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex items-center justify-between">
                <a href="{{ route('my-tickets.index') }}" class="inline-flex items-center text-2xl font-bold text-gray-900 dark:text-gray-100">
                    <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Ticket Details
                </a>

                <!-- Actions: 3-dot popover -->
                <div class="relative" x-data="{ open: false }">
                    <button id="ticket-actions-btn" type="button" class="inline-flex items-center justify-center w-9 h-9 rounded-md bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 focus:outline-none">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </button>
                    <div id="ticket-actions-menu" class="hidden absolute right-0 mt-2 w-44 rounded-md shadow-lg bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 z-10">
                        <a href="{{ route('tickets.invoice', [$ticket->ticketPurchase->purchase_id, $ticket->id]) }}" class="block px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">View Invoice</a>
                        <a href="{{ route('tickets.invoice', [$ticket->ticketPurchase->purchase_id, $ticket->id]) }}?print=1" class="block px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">Print Invoice</a>
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const btn = document.getElementById('ticket-actions-btn');
                const menu = document.getElementById('ticket-actions-menu');
                if (!btn || !menu) return;
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    menu.classList.toggle('hidden');
                });
                document.addEventListener('click', function () {
                    if (!menu.classList.contains('hidden')) menu.classList.add('hidden');
                });
            });
        </script>
    </x-slot>

    @php
        $holderName = auth()->user()?->full_name ?? auth()->user()?->name ?? 'You';
        $eventTitle = $ticket->event->title ?? 'Event';
        $dateTime = optional($ticket->event->start_date)->format('M d, Y g:i A');
        $venue = $ticket->event->location ?? '—';
        $ticketType = $ticket->ticketPurchase->ticketType->name ?? 'Ticket';
        $ticketId = $ticket->ticket_id ?? ($ticket->event_id . '_' . strtoupper(substr(md5($ticket->id), 0, 8)));
        $status = 'PAID';
        $qrUrl = route('tickets.qr-code', $ticket->id);
        $bgImage = $ticket->event->thumbnail_url ?? null;
        
        // Use ticket_id for QR code
        $qrText = $ticket->ticket_id;
        
        // Format date for preview template
        $formattedDateTime = optional($ticket->event->start_date)->format('n/j/Y \\a\\t g:i A') ?? 'Date & Time';
    @endphp

    <div class="py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Use the same ticket preview component design -->
            @include('organizer.events.preview.ticket-preview', [
                'holderName' => $holderName,
                'eventTitle' => $eventTitle,
                'dateTime' => $formattedDateTime,
                'venue' => $venue,
                'ticketType' => $ticketType,
                'ticketId' => $ticketId,
                'status' => $status,
                'qrText' => $qrText, // Use ticket_id for QR code
                'bgImage' => $bgImage,
                'primaryColor' => $primaryColor ?? '#10b981',
                'secondaryColor' => $secondaryColor ?? '#f59e0b',
                'accentColor' => $accentColor ?? '#059669'
            ])

            <div class="mt-6 flex items-center justify-end">
                <a href="{{ route('events.show', $ticket->event) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors duration-200">
                    View Event
                </a>
            </div>
        </div>
    </div>

</x-app-layout>





















































