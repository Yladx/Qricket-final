<!-- Tickets Pricing Layout -->
<div class="tickets-pricing-section py-16">
    <style>
        /* Hide scrollbar for Chrome, Safari and Opera */
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        /* Hide scrollbar for IE, Edge and Firefox */
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        /* Line clamp utility for description */
        .line-clamp-6 {
            display: -webkit-box;
            -webkit-line-clamp: 6;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(isset($details['title']) && $details['title'])
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">{{ $details['title'] }}</h2>
            @if(isset($details['subtitle']) && $details['subtitle'])
            <p class="text-lg text-gray-900 dark:text-gray-400">{{ $details['subtitle'] }}</p>
            @endif
        </div>
        @endif
        
        @php
            // Build tickets from event when available (for real pages),
            // otherwise rely on provided details or fall back to placeholders.
            $renderTickets = null;
            if (!empty($details['tickets']) && is_array($details['tickets'])) {
                $renderTickets = $details['tickets'];
            } elseif (isset($event) && method_exists($event, 'eventTickets')) {
                $eventTickets = $event->eventTickets()->get();
                if ($eventTickets && $eventTickets->count() > 0) {
                    $renderTickets = $eventTickets->map(function($t){
                        return [
                            'name' => $t->name,
                            'price' => '₱' . number_format($t->price ?? 0, 2, '.', ','),
                            'description' => $t->description ?? null,
                            'features' => [],
                            'button_text' => 'Buy Ticket',
                            'popular' => (bool)($t->is_featured ?? false),
                        ];
                    })->toArray();
                }
            }
            
            // Determine ticket count and grid layout
            $ticketCount = !empty($renderTickets) ? count($renderTickets) : 0;
            
            // Grid classes based on count
            if ($ticketCount === 1) {
                $gridClasses = 'flex justify-center';
                $cardContainerClasses = 'flex';
            } elseif ($ticketCount === 2) {
                $gridClasses = 'grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8';
                $cardContainerClasses = '';
            } elseif ($ticketCount === 3) {
                $gridClasses = 'grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8';
                $cardContainerClasses = '';
            } else {
                // 4 or more: horizontal scroll (no visible scrollbar)
                $gridClasses = 'flex gap-6 lg:gap-8 overflow-x-auto';
                $cardContainerClasses = 'flex-shrink-0';
            }
        @endphp

        <div class="{{ $gridClasses }} {{ $ticketCount >= 4 ? 'scrollbar-hide' : '' }}" style="{{ $ticketCount >= 4 ? 'scrollbar-width: none; -ms-overflow-style: none;' : '' }}">
            @if(!empty($renderTickets))
                @foreach($renderTickets as $index => $ticket)
                @php
                    $tName = $ticket['name'] ?? ('Ticket ' . ($index + 1));
                    $tPriceRaw = $ticket['price'] ?? 0;
                    $tPrice = is_numeric($tPriceRaw) ? ('₱' . number_format($tPriceRaw, 2, '.', ',')) : (string) $tPriceRaw;
                    $tDesc = ($ticket['description'] ?? null);
                    if (!$tDesc || trim($tDesc) === '') {
                        // Consistent default with placeholder
                        $tDesc = $tName === 'VIP Access' ? 'VIP access' : 'Standard access to the event';
                    }
                    $tBtn = $ticket['button_text'] ?? 'Buy Ticket';
                    $tPopular = (bool)($ticket['popular'] ?? false);
                    
                    // Load ticket colors for theme
                    $ticketColors = [
                        'primary' => '#10b981',
                        'secondary' => '#f59e0b',
                        'accent' => '#059669',
                    ];
                    
                    try {
                        $eventDir = "events/{$event->id}";
                        $settingsPath = $eventDir . '/ticket_settings.json';
                        if (\Storage::disk('r2')->exists($settingsPath)) {
                            $json = \Storage::disk('r2')->get($settingsPath);
                            $data = json_decode($json, true);
                            if (is_array($data) && isset($data['ticket_colors']) && is_array($data['ticket_colors'])) {
                                $ticketColors = array_merge($ticketColors, $data['ticket_colors']);
                            }
                        }
                    } catch (\Exception $e) {
                        // Use defaults
                    }
                    
                    $themeColor = $ticketColors['primary'] ?? '#10b981';
                @endphp
                <div class="{{ $cardContainerClasses }} w-full {{ $ticketCount >= 4 ? 'w-80' : '' }}">
                    <div class="bg-gray-700 dark:bg-gray-800 rounded-lg shadow-lg p-6 md:p-8 relative h-full {{ $tPopular ? 'ring-2 ring-opacity-50' : '' }}" style="{{ $tPopular ? "ring-color: {$themeColor};" : '' }} min-h-[400px] flex flex-col">
                    @if($tPopular)
                        <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-10">
                            <span class="text-white px-4 py-1 rounded-full text-sm font-medium" style="background-color: {{ $themeColor }};">Most Popular</span>
                    </div>
                    @endif
                        
                        <div class="flex flex-col h-full">
                            <div class="text-center flex-1 flex flex-col justify-between">
                                <div>
                                    <h3 class="text-xl md:text-2xl font-bold text-white mb-4">{{ $tName }}</h3>
                                    <div class="text-3xl md:text-4xl font-bold mb-2" style="color: {{ $themeColor }};">{{ $tPrice }}</div>
                                    <p class="text-gray-300 dark:text-gray-400 mb-6 text-sm md:text-base leading-relaxed line-clamp-6">{{ $tDesc }}</p>
                                </div>
                                
                                @if(isset($ticket['features']) && is_array($ticket['features']) && count($ticket['features']) > 0)
                                <ul class="text-left space-y-2 mb-6 max-h-48 overflow-y-auto">
                            @foreach($ticket['features'] as $feature)
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                        <span class="text-gray-300 dark:text-gray-400 text-sm">{{ $feature }}</span>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                            </div>
                        
                            <a href="{{ route('tickets.purchase.show', $event->id) }}" class="w-full text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 inline-block text-center hover:opacity-90 mt-auto" style="background-color: {{ $themeColor }};">
                            {{ $tBtn }}
                        </a>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <!-- Default tickets if no data provided - styled to match design -->
                @php
                    $defaultThemeColor = '#10b981';
                    if (isset($event)) {
                        try {
                            $eventDir = "events/{$event->id}";
                            $settingsPath = $eventDir . '/ticket_settings.json';
                            if (\Storage::disk('r2')->exists($settingsPath)) {
                                $json = \Storage::disk('r2')->get($settingsPath);
                                $data = json_decode($json, true);
                                if (is_array($data) && isset($data['ticket_colors']) && is_array($data['ticket_colors'])) {
                                    $defaultThemeColor = $data['ticket_colors']['primary'] ?? '#10b981';
                                }
                            }
                        } catch (\Exception $e) {
                            // Use default
                        }
                    }
                @endphp
                
                <div class="flex justify-center w-full">
                    <div class="bg-gray-700 dark:bg-gray-800 rounded-lg shadow-lg p-6 md:p-8 relative min-h-[400px] flex flex-col w-full max-w-md">
                        <div class="flex flex-col h-full">
                            <div class="text-center flex-1 flex flex-col justify-between">
                                <div>
                                    <h3 class="text-xl md:text-2xl font-bold text-white mb-4">General Admission</h3>
                                    <div class="text-3xl md:text-4xl font-bold mb-2" style="color: {{ $defaultThemeColor }};">₱1,200.00</div>
                                    <p class="text-gray-300 dark:text-gray-400 mb-6 text-sm md:text-base leading-relaxed">Standard access to the event</p>
                    </div>
                </div>
                
                            <a href="{{ route('tickets.purchase.show', $event->id ?? 0) }}" class="w-full text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 inline-block text-center hover:opacity-90 mt-auto" style="background-color: {{ $defaultThemeColor }};">
                                Buy Ticket
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
