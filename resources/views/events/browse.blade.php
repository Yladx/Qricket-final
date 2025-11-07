<x-app-layout title="Browse Events">
    <x-slot name="header">
        <div class="bg-white dark:bg-gray-800 ">
         
                <div class="flex flex-col gap-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Browse Events</h1>
                    
                    <!-- Basic Filters -->
                    <form method="GET" action="{{ route('events.browse') }}" class="flex flex-row flex-wrap items-center gap-2 sm:gap-3">
                        <!-- Search -->
                        <div class="relative order-1 flex-1 min-w-[160px] sm:min-w-[220px]">
                            <input type="text"
                                   name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Search events..." 
                                   class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-qricket-500 focus:border-qricket-500 dark:bg-gray-900 dark:text-gray-300">
                    </div>
                        
                        <!-- Category -->
                        <div class="order-2 relative">
                            <select name="category" class="w-full px-3 py-2 pr-8 bg-white text-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-qricket-500 focus:border-qricket-500 dark:bg-gray-900 dark:text-gray-300 appearance-none">
                                @php $currentCategory = request('category'); @endphp
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" {{ $currentCategory === $category ? 'selected' : '' }}>
                                        {{ ucfirst($category) }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Sort & Order moved to Advanced Filters -->
                        
                        <!-- Search Button -->
                        <button type="submit" class="order-5 px-4 py-2 bg-qricket-500 hover:bg-qricket-600 text-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                    </form>
                </div>
                
                <!-- Advanced Filters (Collapsible) -->
                <div class="mt-4">
                <details class="group">
                        <summary class="cursor-pointer text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-qricket-600 dark:hover:text-qricket-400 flex items-center gap-2">
                        Advanced Filters
                            <svg class="w-4 h-4 flex-shrink-0 transform group-open:rotate-180 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </summary>
                    
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <form method="GET" action="{{ route('events.browse') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                <!-- Tags Filter -->
                                <div class="relative">
                                    <label for="tags" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tags</label>
                                    <input type="text" 
                                           name="tags" 
                                           id="tags"
                                           value="{{ request('tags') }}" 
                                           placeholder="Enter tags (comma separated)..." 
                                           autocomplete="off"
                                           class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-qricket-500 focus:border-qricket-500 dark:bg-gray-900 dark:text-gray-300">
                                    <div id="tag-suggestions" class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                        <!-- Tag suggestions will be populated here -->
                                    </div>
                                </div>

                                <!-- Min Price -->
                                <div>
                                    <label for="price_min" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Min Price (₱)</label>
                                    <input type="number" 
                                           name="price_min" 
                                           id="price_min"
                                           value="{{ request('price_min') }}" 
                                           placeholder="0" 
                                           min="0"
                                           step="0.01"
                                           class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-qricket-500 focus:border-qricket-500 dark:bg-gray-900 dark:text-gray-300">
                                </div>

                                <!-- Max Price -->
                                <div>
                                    <label for="price_max" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Max Price (₱)</label>
                                    <input type="number" 
                                           name="price_max" 
                                           id="price_max"
                                           value="{{ request('price_max') }}" 
                                           placeholder="∞" 
                                           min="0"
                                           step="0.01"
                                           class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-qricket-500 focus:border-qricket-500 dark:bg-gray-900 dark:text-gray-300">
                                </div>
                        
                                <!-- Date From -->
                        <div>
                                    <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">From Date</label>
                                    <input type="date" 
                                           name="date_from" 
                                           id="date_from"
                                           value="{{ request('date_from') }}" 
                                   class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-qricket-500 focus:border-qricket-500 dark:bg-gray-900 dark:text-gray-300">
                        </div>
                        
                                <!-- Date To -->
                        <div>
                                    <label for="date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">To Date</label>
                                    <input type="date" 
                                           name="date_to" 
                                           id="date_to"
                                           value="{{ request('date_to') }}" 
                                   class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-qricket-500 focus:border-qricket-500 dark:bg-gray-900 dark:text-gray-300">
                        </div>
                        
                                <!-- Sort -->
                        <div>
                            <label for="sort" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sort By</label>
                            <div class="relative">
                                <select name="sort" id="sort" class="w-full px-3 py-2 pr-8 bg-white text-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-qricket-500 focus:border-qricket-500 dark:bg-gray-900 dark:text-gray-300 appearance-none">
                                    <option value="date" {{ request('sort', 'date') == 'date' ? 'selected' : '' }}>Date</option>
                                    <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name</option>
                                    <option value="created" {{ request('sort') == 'created' ? 'selected' : '' }}>Created</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                                <!-- Order -->
                                <div>
                                    <label for="order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Order</label>
                                    <div class="relative">
                                        <select name="order" id="order" class="w-full px-3 py-2 pr-8 bg-white text-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-qricket-500 focus:border-qricket-500 dark:bg-gray-900 dark:text-gray-300 appearance-none">
                                            <option value="asc" {{ request('order', 'asc') == 'asc' ? 'selected' : '' }}>Ascending</option>
                            <option value="desc" {{ request('order') == 'desc' ? 'selected' : '' }}>Descending</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                                <!-- Show Past Events -->
                        <div class="flex items-end">
                            <label class="flex items-center">
                                        <input type="checkbox" 
                                               name="show_past" 
                                               value="1"
                                               {{ request('show_past') ? 'checked' : '' }}
                                               class="h-4 w-4 text-qricket-600 focus:ring-qricket-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Show past events</span>
                            </label>
                        </div>
                        
                                <!-- Show Free Events -->
                        <div class="flex items-end">
                            <label class="flex items-center">
                                        <input type="checkbox" 
                                               name="show_free" 
                                               value="1"
                                               {{ request('show_free') ? 'checked' : '' }}
                                               class="h-4 w-4 text-qricket-600 focus:ring-qricket-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Free events only</span>
                            </label>
                        </div>
                        
                                <!-- Hidden fields to preserve basic filters -->
                                <input type="hidden" name="search" value="{{ request('search') }}">
                        <input type="hidden" name="category" value="{{ request('category') }}">
                        <input type="hidden" name="price_min" value="{{ request('price_min') }}">
                        <input type="hidden" name="price_max" value="{{ request('price_max') }}">
                        <input type="hidden" name="show_free" value="{{ request('show_free') ? '1' : '' }}">
                        
                                <div class="md:col-span-2 lg:col-span-4 flex justify-end">
                                    <a href="{{ route('events.browse') }}" 
                                       class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200 mr-3">
                                Clear Filters
                            </a>
                            <button type="submit" class="px-4 py-2 bg-qricket-500 hover:bg-qricket-600 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                                Apply Filters
                            </button>
                        </div>
                    </form>
                        </div>
                </details>
            </div>

        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @auth
            @if(isset($recentlyViewed) && $recentlyViewed->count())
                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Continue exploring</h2>
                    <div class="overflow-x-auto no-scrollbar">
                        <div class="flex gap-3 pb-4" style="min-width: max-content;">
                            @foreach($recentlyViewed->take(6) as $event)
                                <a href="{{ route('events.show', $event) }}" class="group block bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition flex-shrink-0" style="width: 200px;">
                                <div class="h-24 bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                @if($event->thumbnail_url)
                                        <img src="{{ $event->thumbnail_url }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform" alt="{{ $event->title }}">
                                @endif
                                </div>
                                <div class="p-3">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $event->title }}</h3>
                                        <span class="text-[11px] text-gray-500 dark:text-gray-400">{{ optional($event->start_date)->format('M j') }}</span>
                                </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 truncate">{{ $event->location }}</p>
                            </div>
                            </a>
                    @endforeach
                </div>
                        </div>
            </div>
                        @endif
            @endauth

                        @php
                            $cat = request('category');
                            // Determine title based on whether we're showing upcoming events only
                            if ($cat) {
                                $title = ucfirst($cat) . ' Events';
                            } elseif (isset($isShowingUpcomingOnly) && $isShowingUpcomingOnly) {
                                $title = 'Upcoming Events';
                            } else {
                                $title = 'All Events';
                            }
                        @endphp
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ $title }}</h2>
            <!-- Events Grid -->
            @if($events->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
                    @foreach($events as $event)
                        <a href="{{ route('events.show', $event) }}" class="group block bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transition-all duration-200 hover:shadow-xl hover:-translate-y-0.5">
                            <!-- Event Image -->
                            <div class="relative h-40 sm:h-44 bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                @if($event->thumbnail_url)
                                    <img src="{{ $event->thumbnail_url }}" 
                                         alt="{{ $event->title }}" 
                                         class="w-full h-full object-cover filter grayscale group-hover:grayscale-0 transition duration-300 ease-out">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-b from-black/0 via-black/0 to-black/20"></div>
                                <!-- Category Badge -->
                                <div class="absolute top-3 left-3">
                                    <span class="px-2.5 py-1 text-[11px] font-medium bg-qricket-500 text-white rounded-full shadow-sm">
                                        {{ ucfirst($event->category ?? 'event') }}
                                    </span>
                                </div>
                                <!-- Date Badge -->
                                <div class="absolute top-3 right-3">
                                    <span class="px-2.5 py-1 text-[11px] font-semibold bg-black/60 text-white rounded-full shadow-sm">
                                        {{ optional($event->start_date)->format('M j') }}
                                    </span>
                            </div>
                            
                                <!-- 3-dot Menu -->
                                <div class="absolute top-3 right-3 z-50 hidden">
                                    <button type="button" 
                                            class="flex items-center text-white hover:text-gray-200 focus:outline-none rounded-full p-1 transition-colors duration-200 bg-black bg-opacity-30 hover:bg-opacity-50"
                                            onclick="event.preventDefault(); event.stopPropagation(); toggleEventPopover('event-actions-{{ $event->id }}')">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zm0 4a2 2 0 110-4 2 2 0 010 4zm0 4a2 2 0 110-4 2 2 0 010 4z"></path>
                                        </svg>
                                    </button>

                                    <div id="event-actions-{{ $event->id }}" 
                                         class="hidden absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-700 focus:outline-none z-50"
                                         role="menu" aria-orientation="vertical">
                                        <div class="py-1" role="none">
                                            <button type="button"
                                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600" 
                                                    role="menuitem"
                                                    onclick="event.preventDefault(); event.stopPropagation(); toggleSavedEvent({{ $event->id }}, this);"
                                                    data-event-saved="{{ auth()->check() && $event->savedByUsers->isNotEmpty() ? 'true' : 'false' }}">
                                                <span class="saved-text">{{ auth()->check() && $event->savedByUsers->isNotEmpty() ? 'Unsaved Event' : 'Saved Event' }}</span>
                        </button>
                </div>
            </div>
                                </div>
                            </div>
                            
                            <!-- Event Content -->
                            <div class="p-3 sm:p-4">
                                <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100 mb-1 sm:mb-2 line-clamp-2 min-h-[3rem] flex items-start">
                                    {{ $event->title }}
                                </h3>
                                <!-- Description - always 2 lines for consistency -->
                                <div class="text-xs text-gray-600 dark:text-gray-400 mb-2 line-clamp-2 leading-relaxed min-h-[2.5rem]">
                                    {{ $event->description ? Str::limit($event->description, 120) : '&nbsp;' }}
                                </div>
                                
                                <!-- Event Details -->
                                <div class="space-y-1 mb-3">
                                    @if($event->location)
                                        <div class="flex items-center text-xs text-gray-600 dark:text-gray-400">
                                            <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <span class="truncate">{{ $event->location }}</span>
                                        </div>
                                    @endif
                                    
                                    <div class="flex items-center text-xs text-gray-600 dark:text-gray-400">
                                        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span>{{ optional($event->start_date)->format('M j, Y g:i A') }}</span>
                                    </div>
                                    
                                    @if($event->end_date)
                                    <div class="flex items-center text-xs text-gray-600 dark:text-gray-400">
                                        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                            <span>Ends: {{ optional($event->end_date)->format('M j, Y g:i A') }}</span>
                                    </div>
                    @endif
                                </div>
                                @php
                                    $eventEnded = false;
                                    if ($event->end_date) {
                                        $endDate = $event->end_date instanceof \Illuminate\Support\Carbon
                                            ? $event->end_date
                                            : \Illuminate\Support\Carbon::parse($event->end_date);
                                        $eventEnded = $endDate->isPast();
                                    } elseif ($event->start_date) {
                                        $startDate = $event->start_date instanceof \Illuminate\Support\Carbon
                                            ? $event->start_date
                                            : \Illuminate\Support\Carbon::parse($event->start_date);
                                        $eventEnded = $startDate->isPast();
                                    }

                                    $hasUnlimitedTickets = false;
                                    $availableTickets = 0;

                                    foreach ($event->ticketTypes as $ticketType) {
                                        if (!$ticketType->is_active) {
                                            continue;
                                        }

                                        $saleStart = $ticketType->sale_start instanceof \Illuminate\Support\Carbon
                                            ? $ticketType->sale_start
                                            : ($ticketType->sale_start ? \Illuminate\Support\Carbon::parse($ticketType->sale_start) : null);
                                        if ($saleStart && $saleStart->isFuture()) {
                                            continue;
                                        }

                                        $saleEnd = $ticketType->sale_end instanceof \Illuminate\Support\Carbon
                                            ? $ticketType->sale_end
                                            : ($ticketType->sale_end ? \Illuminate\Support\Carbon::parse($ticketType->sale_end) : null);
                                        if ($saleEnd && $saleEnd->isPast()) {
                                            continue;
                                        }

                                        if (is_null($ticketType->quantity)) {
                                            $hasUnlimitedTickets = true;
                                            break;
                                        }

                                        $remaining = max(0, ($ticketType->quantity ?? 0) - ($ticketType->sold_count ?? 0));

                                        if ($remaining > 0) {
                                            $availableTickets += $remaining;
                                        }
                                    }

                                    $noTicketsAvailable = !$hasUnlimitedTickets && $availableTickets <= 0;
                                @endphp
                                <div class="mt-3 flex items-center justify-between">
                                    <div class="text-xs text-qricket-600 dark:text-qricket-400 font-medium">
                                        @if($eventEnded)
                                            Event ended
                                        @elseif($noTicketsAvailable)
                                            No tickets available
                                        @else
                                            {{ $event->ticket_price_range['display'] }}
                                        @endif
                                    </div>
                                    <span class="inline-flex items-center gap-1.5 text-qricket-600 dark:text-qricket-400 text-xs font-medium group-hover:underline">View details
                                        <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                @if($events->hasPages())
                <div class="mt-8">
                    {{ $events->links() }}
                </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No events found</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        @if(request()->hasAny(['category', 'location', 'date_from', 'date_to']))
                            Try adjusting your filters to see more events.
                        @else
                            There are no events available at the moment. Check back later for new events.
                        @endif
                    </p>
                    @if(request()->hasAny(['category', 'location', 'date_from', 'date_to', 'show_past']))
                        <a href="{{ route('events.browse') }}" 
                           class="inline-flex items-center px-4 py-2 bg-qricket-500 hover:bg-qricket-600 text-white rounded-lg transition-colors duration-200">
                            Clear all filters
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <style>
        /* Hide horizontal scrollbar for cleaner UI */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* Hide native select arrows completely */
        select.appearance-none {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
        
        /* For Firefox */
        select.appearance-none::-ms-expand {
            display: none;
        }
        
        /* For IE */
        select.appearance-none::-ms-value {
            background: transparent;
        }
    </style>

    <script>
        // Toggle popover function
        function toggleEventPopover(popoverId) {
            const popover = document.getElementById(popoverId);
            
            if (!popover) {
                return;
            }
            
            const isHidden = popover.classList.contains('hidden');
            
            // Close all other popovers first
            document.querySelectorAll('[id^="event-actions-"]').forEach(p => {
                if (p.id !== popoverId) {
                    p.classList.add('hidden');
                }
            });
            
            // Toggle current popover
            if (isHidden) {
                popover.classList.remove('hidden');
                } else {
                popover.classList.add('hidden');
            }
        }

        // Toggle saved event function
        function toggleSavedEvent(eventId, buttonElement) {
            // Close popover
            document.querySelectorAll('[id^="event-actions-"]').forEach(p => {
                p.classList.add('hidden');
            });
            
            const textElement = buttonElement.querySelector('.saved-text');
            const isCurrentlySaved = textElement.textContent === 'Saved Event';
            
            // Make AJAX call to toggle saved status
            fetch(`/events/${eventId}/toggle-save`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update button text based on new status
                    if (data.is_saved) {
                        textElement.textContent = 'Unsaved Event';
                        textElement.parentElement.classList.add('text-green-600', 'dark:text-green-400');
                        textElement.parentElement.classList.remove('text-gray-700', 'dark:text-gray-200');
                } else {
                        textElement.textContent = 'Saved Event';
                        textElement.parentElement.classList.remove('text-green-600', 'dark:text-green-400');
                        textElement.parentElement.classList.add('text-gray-700', 'dark:text-gray-200');
                    }
                } else {
                    if (typeof Swal !== 'undefined' && window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to update saved status',
                        });
                    } else {
                        alert('Error: ' + (data.message || 'Failed to update saved status'));
                    }
                }
            })
            .catch(error => {
                if (typeof Swal !== 'undefined' && window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error updating saved status. Please try again.',
                    });
                } else {
                    alert('Error updating saved status. Please try again.');
                }
            });
        }

        // Remove auto-submit on filter change; user must click Apply
        document.addEventListener('DOMContentLoaded', function() {
            // If desired, we only auto-submit category changes; others require clicking Apply
            const categorySelect = document.querySelector('select[name="category"]');
            if (categorySelect && categorySelect.form) {
                categorySelect.addEventListener('change', function() {
                    this.form.submit();
                });
            }

            // Close popovers when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('[onclick*="toggleEventPopover"]') && !event.target.closest('[id^="event-actions-"]')) {
                    document.querySelectorAll('[id^="event-actions-"]').forEach(popover => {
                        popover.classList.add('hidden');
                    });
                }
            });

            // Tag autocomplete functionality
            const tagsInput = document.getElementById('tags');
            const suggestionsDiv = document.getElementById('tag-suggestions');
            let debounceTimer;

            if (tagsInput && suggestionsDiv) {
                tagsInput.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    const query = this.value.trim();
                    
                    if (query.length < 2) {
                        suggestionsDiv.classList.add('hidden');
                        return;
                    }

                    debounceTimer = setTimeout(() => {
                        fetch(`/events/tag-suggestions?q=${encodeURIComponent(query)}`)
                            .then(response => response.json())
                            .then(suggestions => {
                                if (suggestions.length > 0) {
                                    suggestionsDiv.innerHTML = suggestions.map(tag => 
                                        `<div class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm text-gray-700 dark:text-gray-300" onclick="selectTag('${tag}')">${tag}</div>`
                                    ).join('');
                                    suggestionsDiv.classList.remove('hidden');
                                } else {
                                    suggestionsDiv.classList.add('hidden');
                                }
                            })
                            .catch(error => {
                                suggestionsDiv.classList.add('hidden');
                            });
                    }, 300);
                });

                // Hide suggestions when clicking outside
                document.addEventListener('click', function(event) {
                    if (!tagsInput.contains(event.target) && !suggestionsDiv.contains(event.target)) {
                        suggestionsDiv.classList.add('hidden');
                    }
                });

                // Hide suggestions when input loses focus
                tagsInput.addEventListener('blur', function() {
                    setTimeout(() => {
                        suggestionsDiv.classList.add('hidden');
                    }, 200);
                });
            }

            // Function to select a tag
            window.selectTag = function(tag) {
                const tagsInput = document.getElementById('tags');
                const currentTags = tagsInput.value.trim();
                
                if (currentTags === '') {
                    tagsInput.value = tag;
                } else {
                    // Check if tag already exists
                    const existingTags = currentTags.split(',').map(t => t.trim());
                    if (!existingTags.includes(tag)) {
                        tagsInput.value = currentTags + ', ' + tag;
                    }
                }
                
                document.getElementById('tag-suggestions').classList.add('hidden');
                tagsInput.focus();
            };
        });
    </script>

    <!-- Footer -->
    <x-footer />
</x-app-layout>