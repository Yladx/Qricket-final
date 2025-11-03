<x-app-layout title="Saved Events">
    <x-slot name="header">
        <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                        <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Saved Events</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Events you've saved for later
                        </p>
                                        </div>
                        <a href="{{ route('events.browse') }}" 
                       class="inline-flex items-center px-4 py-2 bg-qricket-500 hover:bg-qricket-600 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        Browse More Events
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($savedEvents->count() > 0)
            <!-- Events Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
                    @foreach($savedEvents as $event)
                        <a href="{{ route('events.show', $event) }}" class="group block bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transition-all duration-200 hover:shadow-xl hover:-translate-y-0.5">
                            <!-- Event Image -->
                            <div class="relative h-40 sm:h-44 bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                @if($event->thumbnail_url)
                                    <img src="{{ $event->thumbnail_url }}" 
                                         alt="{{ $event->title }}" 
                                         class="w-full h-full object-cover filter grayscale group-hover:grayscale-0 transition duration-300 ease-out"
                                         loading="lazy"
                                         decoding="async">
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
                                <div class="absolute top-3 right-3 z-50">
                                    <button type="button" 
                                            class="flex items-center text-white hover:text-gray-200 focus:outline-none rounded-full p-1 transition-colors duration-200 bg-black bg-opacity-30 hover:bg-opacity-50"
                                            onclick="event.preventDefault(); event.stopPropagation(); toggleEventPopover('saved-event-actions-{{ $event->id }}')">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zm0 4a2 2 0 110-4 2 2 0 010 4zm0 4a2 2 0 110-4 2 2 0 010 4z"></path>
                                        </svg>
                                    </button>

                                    <div id="saved-event-actions-{{ $event->id }}" 
                                         class="hidden absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-700 focus:outline-none z-50"
                                         role="menu" aria-orientation="vertical">
                                        <div class="py-1" role="none">
                                            <button type="button"
                                                    class="block w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-800" 
                                                    role="menuitem"
                                                    onclick="event.preventDefault(); event.stopPropagation(); removeSavedEvent({{ $event->id }}, this);">
                                                Remove from Saved
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
                                @if($event->description)
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-2 line-clamp-2 leading-relaxed">
                                        {{ Str::limit($event->description, 100) }}
                                    </p>
                                @endif
                                
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
                            </div>
                        </a>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                @if($savedEvents->hasPages())
                <div class="mt-8">
                        {{ $savedEvents->links() }}
                </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H7a2 2 0 01-2-2V8zM5 8v6a2 2 0 002 2h6a2 2 0 002-2V8M5 8a2 2 0 012-2V4a2 2 0 00-2-2H7a2 2 0 00-2 2v2z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No saved events yet</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        Start exploring events and save the ones you're interested in.
                    </p>
                        <a href="{{ route('events.browse') }}" 
                           class="inline-flex items-center px-4 py-2 bg-qricket-500 hover:bg-qricket-600 text-white rounded-lg transition-colors duration-200">
                            Browse Events
                        </a>
                </div>
            @endif
        </div>
    </div>

    <script>
        // Toggle popover function
        function toggleEventPopover(popoverId) {
            const popover = document.getElementById(popoverId);
            
            if (!popover) {
                console.error('Popover not found:', popoverId);
                return;
            }
            
            const isHidden = popover.classList.contains('hidden');
            
            // Close all other popovers first
            document.querySelectorAll('[id^="saved-event-actions-"]').forEach(p => {
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

        // Remove saved event function
        function removeSavedEvent(eventId, buttonElement) {
            // Close popover
            document.querySelectorAll('[id^="saved-event-actions-"]').forEach(p => {
                p.classList.add('hidden');
            });
            
            // Show confirmation dialog using SweetAlert
            const confirmRemove = () => {
                if (typeof Swal !== 'undefined' && window.Swal) {
                    Swal.fire({
                        title: 'Remove from Saved?',
                        text: 'Are you sure you want to remove this event from your saved events?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, remove it',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#059669',
                        cancelButtonColor: '#6b7280'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            proceedWithRemove();
                        }
                    });
                } else {
                    if (confirm('Are you sure you want to remove this event from your saved events?')) {
                        proceedWithRemove();
                    }
                }
            };
            
            const proceedWithRemove = () => {
                // Make AJAX call to remove saved status
                fetch(`/events/${eventId}/toggle-save`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success notification
                        if (typeof Swal !== 'undefined' && window.Swal) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Removed',
                                text: 'Event removed from saved events.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                        
                        // Remove the event card from the page
                        const eventCard = buttonElement.closest('a');
                        eventCard.remove();
                        
                        // Check if there are any events left
                        const remainingEvents = document.querySelectorAll('[id^="saved-event-actions-"]').length;
                        if (remainingEvents === 0) {
                            // Reload the page to show empty state
                            window.location.reload();
                        }
                    } else {
                        if (typeof Swal !== 'undefined' && window.Swal) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Failed to remove event',
                            });
                        } else {
                            alert('Error: ' + (data.message || 'Failed to remove event'));
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (typeof Swal !== 'undefined' && window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error removing event. Please try again.',
                        });
                    } else {
                        alert('Error removing event. Please try again.');
                    }
                });
            };
            
            confirmRemove();
        }

        // Close popovers when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('[onclick*="toggleEventPopover"]') && !event.target.closest('[id^="saved-event-actions-"]')) {
                document.querySelectorAll('[id^="saved-event-actions-"]').forEach(popover => {
                    popover.classList.add('hidden');
                });
            }
        });

    </script>
</x-app-layout>