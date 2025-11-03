@props([
    'ticketTypes' => [],
    'event' => null,
    'ticketSettings' => []
])

@php
    // Define color schemes for different ticket types
    $colorSchemes = [
        'General Admission' => ['primary' => '#10b981', 'secondary' => '#f59e0b', 'accent' => '#059669'],
        'VIP' => ['primary' => '#8b5cf6', 'secondary' => '#f59e0b', 'accent' => '#7c3aed'],
        'Premium' => ['primary' => '#f59e0b', 'secondary' => '#f97316', 'accent' => '#d97706'],
        'Student' => ['primary' => '#06b6d4', 'secondary' => '#0891b2', 'accent' => '#0e7490'],
        'Early Bird' => ['primary' => '#10b981', 'secondary' => '#059669', 'accent' => '#047857'],
        'Group' => ['primary' => '#ef4444', 'secondary' => '#dc2626', 'accent' => '#b91c1c'],
        'Standard' => ['primary' => '#6b7280', 'secondary' => '#4b5563', 'accent' => '#374151'],
    ];
    
    // Convert ticket types to array if needed
    $ticketTypesArr = [];
    if (is_array($ticketTypes)) {
        $ticketTypesArr = $ticketTypes;
    } elseif ($ticketTypes instanceof \Illuminate\Support\Collection) {
        $ticketTypesArr = $ticketTypes->toArray();
    }
@endphp

<div class="space-y-6">
    @if(count($ticketTypesArr) > 0)
        <!-- Ticket Types Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($ticketTypesArr as $index => $ticket)
                @php
                    $ticketName = $ticket['name'] ?? ($ticket->name ?? 'Ticket');
                    $ticketPrice = isset($ticket['price']) ? (float)$ticket['price'] : (isset($ticket->price) ? (float)$ticket->price : 0);
                    $ticketDescription = $ticket['description'] ?? ($ticket->description ?? 'Standard access to the event');
                    
                    // Get color scheme based on ticket name
                    $colors = $colorSchemes[$ticketName] ?? $colorSchemes['Standard'];
                    
                    // Generate sample ticket data
                    $sampleData = [
                        'holderName' => 'Sample Attendee',
                        'eventTitle' => $event->title ?? 'Sample Event',
                        'dateTime' => optional($event->start_date)->format('M j, Y \a\t g:i A') ?? 'Date TBA',
                        'venue' => $event->location ?? 'Venue TBA',
                        'ticketType' => $ticketName,
                        'ticketId' => 'TKT-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                        'status' => 'PAID',
                        'qrText' => 'QRICKET-' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                        'bgImage' => $event->thumbnail_url ?? null,
                        'primaryColor' => $colors['primary'],
                        'secondaryColor' => $colors['secondary'],
                        'accentColor' => $colors['accent'],
                    ];
                @endphp
                
                <!-- Ticket Type Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ $ticketName }}</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $ticketDescription }}</p>
                        </div>
                        <div class="flex items-center space-x-2 ml-4">
                            <span class="px-3 py-1 text-sm font-medium rounded-full" style="background-color: {{ $colors['primary'] }}20; color: {{ $colors['primary'] }};">
                                ₱{{ number_format($ticketPrice, 2, '.', ',') }}
                            </span>
                            <div class="w-3 h-3 rounded-full" style="background-color: {{ $colors['primary'] }};"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Ticket Settings Summary -->
        @if(!empty($ticketSettings))
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Ticket Settings
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    @if(isset($ticketSettings['max_tickets_per_order']))
                        <div class="flex justify-between">
                            <span class="text-blue-700 dark:text-blue-300">Max per order:</span>
                            <span class="font-medium text-blue-900 dark:text-blue-100">{{ $ticketSettings['max_tickets_per_order'] }}</span>
                        </div>
                    @endif
                    @if(isset($ticketSettings['allow_share_ticket']))
                        <div class="flex justify-between">
                            <span class="text-blue-700 dark:text-blue-300">Sharing allowed:</span>
                            <span class="font-medium text-blue-900 dark:text-blue-100">{{ $ticketSettings['allow_share_ticket'] ? 'Yes' : 'No' }}</span>
                        </div>
                    @endif
                    @if(isset($ticketSettings['limit_per_user']) && $ticketSettings['limit_per_user'])
                        <div class="flex justify-between">
                            <span class="text-blue-700 dark:text-blue-300">Max per user:</span>
                            <span class="font-medium text-blue-900 dark:text-blue-100">{{ $ticketSettings['max_tickets_per_user'] ?? 'N/A' }}</span>
                        </div>
                    @endif
                    @if(isset($ticketSettings['refund_policy']))
                        <div class="flex justify-between">
                            <span class="text-blue-700 dark:text-blue-300">Refund policy:</span>
                            <span class="font-medium text-blue-900 dark:text-blue-100">{{ is_string($ticketSettings['refund_policy']) ? $ticketSettings['refund_policy'] : 'See details' }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">No tickets configured</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">This event doesn't have any ticket types set up yet.</p>
        </div>
    @endif
</div>
