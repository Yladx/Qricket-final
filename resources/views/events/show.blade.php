<x-app-layout>
    <x-slot name="title">{{ $event->title }} - Event Details</x-slot>
    
    <!-- CSRF Token for AJAX requests -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <x-slot name="header">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-20">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ $backUrl ?? route('events.browse') }}" class="text-gray-500 hover:text-qricket-500 dark:text-gray-400 dark:hover:text-qricket-400 transition-colors duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $event->title }} 
                    </h1> 
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-center space-x-3">
                </div>
            </div>
        </div>
    </x-slot>

    <!-- Fixed Background with Thumbnail - Grayscale (behind everything) -->
    <div class="fixed inset-0 z-0 pointer-events-none" style="top: 0; left: 0; right: 0; bottom: 0;">
        @if($event->thumbnail_url)
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat"
                 style="background-image: url('{{ $event->thumbnail_url }}'); filter: grayscale(100%) contrast(1.2) brightness(0.9);"></div>
        @endif
        <!-- Dark Mode Overlay - Strong dark overlay for dark mode -->
        <div class="absolute inset-0 bg-gradient-to-br from-black/75 via-gray-900/70 to-black/80 dark:block hidden"></div>
        <!-- Light Mode Overlay - Increased white overlay for better visibility of black and white details -->
        <div class="absolute inset-0 bg-gradient-to-br from-white/75 via-white/70 to-white/80 dark:hidden"></div>
        <!-- Additional contrast overlay - increased for more whiteness in light mode -->
        <div class="absolute inset-0 bg-white/30 dark:bg-black/30"></div>
        <!-- Blur effect - increased blur for light mode to make background more abstract -->
        <div class="absolute inset-0 backdrop-blur-2xl dark:backdrop-blur-lg"></div>
    </div>
    
    <div class="py-0 min-h-screen relative z-10 flex flex-col">
        <!-- Content with proper z-index to appear above background -->
        <div class="relative z-10 flex-1 flex flex-col">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 flex-1 flex flex-col">
            
            <!-- Event Header Component -->
            <x-events.event-header :event="$event" :section="$section" />

            <div class="{{ $section === 'news' ? 'w-full' : 'w-full' }} flex-1 flex flex-col">
                <!-- Main Content -->
                <div class="{{ $section === 'news' ? 'w-full' : 'w-full' }} space-y-6 flex-1">
                    <!-- Event Info Section -->
                    <div id="event-info-section">
                        <x-events.event-info :section="$section" :event="$event" />
                    </div>

                    <!-- Dynamic Layout Components Render Target -->
                    <div id="layout-components-display" class="space-y-8"></div>

                    <!-- Event News Section -->
                    <div id="event-news-section" style="display: {{ $section === 'news' ? 'block' : 'none' }};" class="mt-6 pb-12">
                        <x-events.news :news="$news" :event="$event" />
                    </div>
                </div>

            </div>
        </div>
        
        <!-- Include Event Footer - At bottom of content -->
        <div class="relative z-20 w-full mt-auto">
            <x-events.event-footer :event="$event" :fixed="false" />
        </div>
        </div>


    @php
        $eventHasEnded = $event->end_date && now()->gte($event->end_date);
        $canSeeFeedback = false;
        $hasAlreadySubmittedFeedback = false;
        
        if ($eventHasEnded && auth()->check()) {
            try {
                // Check if user has a paid ticket
                $hasPaidTicket = \App\Models\TicketInformation::where('event_id', $event->id)
                    ->where('user_id', auth()->id())
                    ->whereHas('ticketPurchase', function($q){ $q->where('status','paid'); })
                    ->exists();
                
                if ($hasPaidTicket) {
                    // Check if user has already submitted feedback
                    $hasAlreadySubmittedFeedback = \App\Models\EventFeedback::where('event_id', $event->id)
                        ->where('user_id', auth()->id())
                        ->exists();
                    
                    // Can see feedback button only if they haven't submitted yet
                    $canSeeFeedback = !$hasAlreadySubmittedFeedback;
                }
            } catch (\Throwable $e) { 
                $canSeeFeedback = false; 
                $hasAlreadySubmittedFeedback = false;
            }
        }
    @endphp

    @if($eventHasEnded && $canSeeFeedback)
        <a href="{{ route('events.feedback.show', $event) }}"
           class="fixed bottom-6 right-6 z-50 inline-flex items-center px-4 py-2 rounded-full shadow-lg border border-gray-200 dark:border-gray-700 bg-white/90 dark:bg-gray-800/90 backdrop-blur hover:bg-white dark:hover:bg-gray-800 text-qricket-600 dark:text-qricket-400 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h6m-6 8l-4-4V6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H7z" />
            </svg>
            Give Feedback
        </a>
    @endif


    <script>
        // Load and render the event layout
        document.addEventListener('DOMContentLoaded', function() {
            // Use the section from the controller
            const section = @json($section);
            
            if (section === 'news') {
                showNewsSection();
            } else {
                // Default to event info (layout)
                showEventInfoSection();
            }
            
            // Load layout data regardless (for when user switches to event info)
            loadEventLayout();
        });

        function showEventInfoSection() {
            document.getElementById('event-info-section').style.display = 'block';
            document.getElementById('event-news-section').style.display = 'none';
        }

        function showNewsSection() {
            document.getElementById('event-info-section').style.display = 'none';
            document.getElementById('event-news-section').style.display = 'block';
        }

        function loadEventLayout() {
            const eventData = @json($event);
            let layoutData = @json($layoutData);
            
            console.log('Event Show: Loading event layout with', Object.keys(eventData).length, 'event fields');
            console.log('Event Show: Event data keys:', Object.keys(eventData));
            console.log('Event Show: Layout data type:', typeof layoutData, 'Is array:', Array.isArray(layoutData));
            console.log('Event Show: Layout data value:', layoutData);
            if (layoutData && Array.isArray(layoutData)) {
                console.log('Event Show: Layout data has', layoutData.length, 'components');
            }
            
            // Parse layout data if it's a JSON string
            if (typeof layoutData === 'string') {
                try {
                    layoutData = JSON.parse(layoutData);
                    console.log('Event Show: Parsed layout data successfully');
                } catch (e) {
                    console.error('Event Show: Failed to parse layout data JSON:', e);
                    layoutData = null;
                }
            }
            
            // Store event data globally for layout rendering
            window.eventData = eventData;
            
            // Render layout components
            if (layoutData && Array.isArray(layoutData)) {
                console.log('Event Show: Rendering', layoutData.length, 'layout components');
                renderLayoutComponents(layoutData);
            } else {
                console.log('Event Show: No layout data to render or layout data is not an array');
                const container = document.getElementById('layout-components-display');
                if (container) {
                    renderHtmlWithScripts(container, `
                        <div class="text-center py-12 text-gray-500">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No Layout Components</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-4">This event doesn't have custom layout components.</p>
                            <div class="prose dark:prose-invert max-w-none">
                                <p class="text-gray-600 dark:text-gray-300 text-lg leading-relaxed">${eventData.description || 'No description available.'}</p>
                            </div>
                        </div>
                    `);
                }
            }
        }

        // Safely inject HTML and execute any inline/external script tags
        function renderHtmlWithScripts(targetElement, htmlString) {
            if (!targetElement) return;
            const wrapper = document.createElement('div');
            wrapper.innerHTML = htmlString;
            const scriptNodes = Array.from(wrapper.querySelectorAll('script'));
            scriptNodes.forEach(node => node.parentNode.removeChild(node));
            targetElement.innerHTML = '';
            while (wrapper.firstChild) {
                targetElement.appendChild(wrapper.firstChild);
            }
            // execute scripts after content is in the DOM
            scriptNodes.forEach(oldScript => {
                const newScript = document.createElement('script');
                if (oldScript.src) {
                    newScript.src = oldScript.src;
                } else {
                    newScript.textContent = oldScript.textContent;
                }
                // copy attributes like type
                if (oldScript.type) newScript.type = oldScript.type;
                document.body.appendChild(newScript);
            });
        }

        function renderLayoutComponents(components) {
            const container = document.getElementById('layout-components-display');
            if (!components || components.length === 0) {
                renderHtmlWithScripts(container, '<div class="text-center text-gray-500 py-8">No layout components added yet</div>');
                return;
            }

            console.log('Event Show: Rendering', components.length, 'layout components');

            // Show subtle loading state
            container.innerHTML = `
                <div class="space-y-8">
                    <div class="animate-pulse">
                        <div class="bg-gray-200 dark:bg-gray-700 rounded-lg h-64 mb-6"></div>
                    </div>
                    <div class="animate-pulse">
                        <div class="bg-gray-200 dark:bg-gray-700 rounded-lg h-32 mb-6"></div>
                    </div>
                    <div class="animate-pulse">
                        <div class="bg-gray-200 dark:bg-gray-700 rounded-lg h-48 mb-6"></div>
                    </div>
                </div>
            `;

            const results = new Array(components.length);
            let loadedComponents = 0;

            components.forEach((component, index) => {
                const layoutType = component.layout;
                const details = component.details || {};

                console.log(`Event Show: Loading component ${index + 1}/${components.length}: ${layoutType}`);
                console.log('Event Show: Component details:', details);
                console.log('Event Show: Event data:', window.eventData);

                // Make AJAX request to load the layout template
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    console.error('CSRF token not found');
                    return;
                }

                console.log(`Event Show: Making AJAX request to: /events/layouts/${layoutType}`);

                fetch(`/events/layouts/${layoutType}?id={{ $event->id }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken.getAttribute('content')
                    },
                    body: JSON.stringify({
                        eventData: window.eventData,
                        details: details
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    console.log(`Event Show: Successfully loaded ${layoutType} component`);
                    results[index] = html;
                    loadedComponents++;
                    if (loadedComponents === components.length) {
                        renderHtmlWithScripts(container, results.join(''));
                        console.log('Event Show: All layout components rendered successfully (ordered)');
                    }
                })
                .catch(error => {
                    console.error(`Event Show: Failed to load ${layoutType} component:`, error);
                    // Fallback to simple rendering in the correct index
                    results[index] = `
                        <div class="mb-8 p-6 border border-gray-200 dark:border-gray-600 rounded-lg">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    ${layoutType.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                </h3>
                                <span class="px-2 py-1 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 text-xs rounded">
                                    Error Loading
                                </span>
                            </div>
                            <div class="prose dark:prose-invert max-w-none">
                                <p class="text-red-500">Failed to load layout component: ${layoutType}</p>
                                <p class="text-red-500">Error: ${error.message}</p>
                                <pre class="text-xs bg-gray-100 dark:bg-gray-700 p-2 rounded">${JSON.stringify(details, null, 2)}</pre>
                            </div>
                        </div>
                    `;
                    loadedComponents++;
                    if (loadedComponents === components.length) {
                        renderHtmlWithScripts(container, results.join(''));
                        console.log('Event Show: All layout components rendered successfully (ordered)');
                    }
                });
            });
        }

        function toggleSaveEvent(eventId) {
            const btn = document.querySelector(`[data-event-id="${eventId}"]`);
            const saveText = btn.querySelector('.save-text');
            const originalText = saveText.textContent;
            const originalContent = btn.innerHTML;
            
            // Show loading state
            saveText.textContent = 'Saving...';
            btn.disabled = true;

            fetch(`/events/${eventId}/toggle-save`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                // Check if we need to show switch mode alert
                if (data.action === 'switch_mode') {
                    Swal.fire({
                        title: data.title,
                        text: data.text,
                        icon: 'info',
                        showCancelButton: data.showCancelButton,
                        confirmButtonText: data.confirmButtonText,
                        cancelButtonText: data.cancelButtonText,
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#6b7280'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Switch to user mode via AJAX
                            fetch('{{ route("user.mode-switch") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({
                                    mode: 'user'
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    window.location.href = data.redirect_url;
                                }
                            })
                            .catch(error => {
                                console.error('Error switching mode:', error);
                                // Fallback: direct redirect
                                window.location.href = '{{ route("dashboard") }}';
                            });
                        }
                    });
                    
                    // Restore button state
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                    return;
                }
                
                if (data.success && data.is_saved) {
                    // Event was saved
                    saveText.textContent = 'Saved';
                    btn.setAttribute('data-saved', 'true');
                    
                    // Change button style for saved state
                    if (btn.classList.contains('bg-teal-600')) {
                        btn.classList.remove('bg-teal-600', 'hover:bg-teal-700');
                        btn.classList.add('bg-green-600', 'hover:bg-green-700');
                    } else {
                        btn.classList.remove('border-gray-300', 'dark:border-gray-600', 'text-gray-700', 'dark:text-gray-300', 'hover:bg-gray-50', 'dark:hover:bg-gray-700');
                        btn.classList.add('border-green-300', 'dark:border-green-600', 'text-green-700', 'dark:text-green-300', 'hover:bg-green-50', 'dark:hover:bg-green-900');
                    }
                } else if (data.success && !data.is_saved) {
                    // Event was unsaved
                    saveText.textContent = originalText.includes('Event') ? 'Save Event' : 'Save';
                    btn.setAttribute('data-saved', 'false');
                    
                    // Change button style for unsaved state
                    if (btn.classList.contains('bg-green-600')) {
                        btn.classList.remove('bg-green-600', 'hover:bg-green-700');
                        btn.classList.add('bg-teal-600', 'hover:bg-teal-700');
                    } else {
                        btn.classList.remove('border-green-300', 'dark:border-green-600', 'text-green-700', 'dark:text-green-300', 'hover:bg-green-50', 'dark:hover:bg-green-900');
                        btn.classList.add('border-gray-300', 'dark:border-gray-600', 'text-gray-700', 'dark:text-gray-300', 'hover:bg-gray-50', 'dark:hover:bg-gray-700');
                    }
                } else {
                    // Handle error response
                    console.error('Error:', data.message);
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                    if (typeof Swal !== 'undefined' && window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'An error occurred. Please try again.',
                        });
                    } else {
                        alert(data.message || 'An error occurred. Please try again.');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                btn.innerHTML = originalContent;
                btn.disabled = false;
                if (typeof Swal !== 'undefined' && window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred. Please try again.',
                    });
                } else {
                    alert('An error occurred. Please try again.');
                }
            })
            .finally(() => {
                btn.disabled = false;
            });
        }
    </script>
</x-app-layout>


