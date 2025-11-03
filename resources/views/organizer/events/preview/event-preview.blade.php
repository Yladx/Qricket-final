<x-unified-layout :layout-type="$layoutType" :title="'Event Preview - ' . ($eventData['title'] ?? 'Event Preview')" :page-title="'Event Preview'">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $eventData['title'] ?? 'Event Preview' }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Preview your event layout and design</p>
            </div>
        </div>
    </x-slot>

    <!-- CSRF Token for AJAX requests -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Preview Header -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 sm:p-6 mb-6">
                <!-- Mobile Layout: Stacked -->
                <div class="flex flex-col space-y-4 sm:hidden">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Event Preview</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Preview your event layout and design</p>
                    </div>
                    <!-- Mobile Action Buttons -->
                    <div class="flex flex-col space-y-2">
                        <a href="{{ route('organizer.events.create.layout') }}?id={{ $eventId }}" class="w-full inline-flex items-center justify-center px-4 py-3 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-medium text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-400 dark:hover:bg-gray-600 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Back to Edit
                        </a>
                        <button onclick="openOffcanvasNavigation()" class="w-full inline-flex items-center justify-center px-4 py-3 bg-gray-500 dark:bg-gray-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-gray-600 dark:hover:bg-gray-700 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                            Navigation Menu
                        </button>
                    </div>
                </div>

                <!-- Desktop Layout: Side by Side -->
                <div class="hidden sm:flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Event Preview</h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">Preview your event layout and design</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('organizer.events.create.layout') }}?id={{ $eventId }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-medium text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-400 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Back to Edit
                        </a>
                    </div>
                </div>
            </div>

            <!-- Event Preview Container -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <!-- Event Header Preview (rendered via component to match exactly) -->
                @php(
                    $eventObj = (object) [
                        'id' => $eventData['id'] ?? ($eventId ?? null),
                        'title' => $eventData['title'] ?? 'Untitled Event',
                        'thumbnail_url' => $eventData['existing_thumbnail_url'] ?? null,
                        'poster_url' => $eventData['existing_poster_url'] ?? null,
                        'category' => $eventData['category'] ?? 'event',
                        'start_date' => $eventData['start_date'] ?? null,
                        'end_date' => $eventData['end_date'] ?? null,
                        'location' => $eventData['location'] ?? null,
                        'description' => $eventData['description'] ?? '',
                        'status' => $eventData['status'] ?? null,
                        'user_id' => $eventData['user_id'] ?? null,
                    ]
                )
                <x-events.event-header :event="$eventObj" :section="'info'" :previewMode="true" :hideActions="true" :previewPosterUrl="($eventData['existing_poster_url'] ?? null)" :previewThumbnailUrl="($eventData['existing_thumbnail_url'] ?? null)" />

                <!-- Layout Components Preview -->
                <div id="layout-components-preview" class="p-6">
                </div>
            </div>

            <!-- Layout Data Info -->
            <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-500 dark:text-blue-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Preview Information</h3>
                        <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                            This preview shows how your event will look with the current layout and design. 
                            The layout data has been saved to cloud storage and can be retrieved later.
                        </p>
                        <div class="mt-2 text-xs text-blue-600 dark:text-blue-400">
                            <span id="storage-info">Layout saved to cloud storage</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Offcanvas Navigation -->
    <div id="offcanvas-navigation" class="fixed inset-0 z-50 hidden">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity duration-300" onclick="closeOffcanvasNavigation()"></div>
        
        <!-- Navigation Panel -->
        <div class="fixed top-0 right-0 h-full w-80 bg-white dark:bg-gray-800 shadow-xl transform translate-x-full transition-transform duration-300">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Navigation Menu</h2>
                <button onclick="closeOffcanvasNavigation()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Navigation Links -->
            <div class="p-4">
                <nav class="space-y-2">
                    <!-- Current Step -->
                    <div class="pb-4 border-b border-gray-200 dark:border-gray-700 mb-4">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Event Creation Flow</div>
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-qricket-500 rounded-full"></div>
                            <span class="text-sm text-qricket-600 dark:text-qricket-400 font-medium">Step 2: Layout Design</span>
                        </div>
                    </div>
                    
                    <!-- Navigation Steps -->
                    <a href="{{ route('organizer.events.step1') }}" class="flex items-center p-3 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="text-sm font-medium">Step 1: Basic Info</span>
                    </a>
                    
                    <div class="flex items-center p-3 bg-qricket-50 dark:bg-qricket-900/20 border border-qricket-200 dark:border-qricket-800 rounded-lg">
                        <svg class="w-5 h-5 mr-3 text-qricket-600 dark:text-qricket-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                        </svg>
                        <span class="text-sm font-medium text-qricket-700 dark:text-qricket-300">Step 2: Layout Design</span>
                    </div>
                    
                    <div class="flex items-center p-3 text-gray-500 dark:text-gray-400">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <span class="text-sm">Step 3: Ticket Pricing</span>
                        <span class="ml-auto text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">Coming Soon</span>
                    </div>
                    
                    <div class="flex items-center p-3 text-gray-500 dark:text-gray-400">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="text-sm">Step 4: Certificates</span>
                        <span class="ml-auto text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">Coming Soon</span>
                    </div>
                </nav>
                
                <!-- Action Buttons -->
                <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700"></div>
            </div>
        </div>
    </div>

    <script>
        // Load and render the event preview
        document.addEventListener('DOMContentLoaded', function() {
            loadEventPreview();
        });

        function loadEventPreview() {
            const eventData = @json($eventData);
            let layoutData = @json($layoutData);
            
            console.log('Preview: Loading event preview with', Object.keys(eventData).length, 'event fields');
            console.log('Preview: Layout data type:', typeof layoutData, 'Is array:', Array.isArray(layoutData));
            if (layoutData && Array.isArray(layoutData)) {
                console.log('Preview: Layout data has', layoutData.length, 'components');
            }
            
            // Parse layout data if it's a JSON string
            if (typeof layoutData === 'string') {
                try {
                    layoutData = JSON.parse(layoutData);
                    console.log('Preview: Parsed layout data successfully');
                } catch (e) {
                    console.error('Preview: Failed to parse layout data JSON:', e);
                    layoutData = null;
                }
            }
            
            // Store event data globally for layout rendering
            window.eventData = eventData;
            
            // Render the event header
            renderEventHeader(eventData);
            
            // Render layout components
            if (layoutData && Array.isArray(layoutData)) {
                console.log('Preview: Rendering', layoutData.length, 'layout components');
                renderLayoutComponents(layoutData);
            } else {
                console.log('Preview: No layout data to render or layout data is not an array');
                const container = document.getElementById('layout-components-preview');
                if (container) {
                    container.innerHTML = `
                        <div class="text-center py-12 text-gray-500">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No Layout Components</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-4">Add layout components in Step 2 to see them here.</p>
                            <a href="{{ route('organizer.events.create.layout') }}?id={{ $eventId }}" class="inline-flex items-center px-4 py-2 bg-qricket-500 hover:bg-qricket-600 text-white font-medium rounded-lg transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Layout Components
                            </a>
                        </div>
                    `;
                }
            }
            
            // Update storage info
            updateStorageInfo();
        }

        function renderEventHeader(eventData) {
            const headerContainer = document.getElementById('event-header-preview');
            
            console.log('Rendering event header with data:', eventData);
            console.log('Poster URL:', eventData.poster);
            console.log('Thumbnail URL:', eventData.thumbnail);
            
            if (!eventData.title) {
                headerContainer.innerHTML = '<div class="flex items-center justify-center h-64 text-gray-500">No event data available</div>';
                return;
            }

            // Create the event header HTML based on the layout data
            const headerHTML = `
                <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1f2937 0%, #374151 50%, #1f2937 100%); min-height: 300px;">
                    ${eventData.thumbnail ? `
                        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat opacity-40" style="background-image: url('${eventData.thumbnail}'); filter: grayscale(100%) brightness(0.6) contrast(1.3);">
                            <div class="absolute inset-0 bg-gradient-to-br from-black/60 via-gray-800/40 to-black/80"></div>
                        </div>
                    ` : `
                        <div class="absolute inset-0 bg-gradient-to-br from-gray-800 via-gray-900 to-black"></div>
                    `}
                    
                    <div class="relative z-10 p-8">
                        <div class="max-w-4xl mx-auto">
                            <div class="flex flex-col lg:flex-row items-center lg:items-start space-y-6 lg:space-y-0 lg:space-x-8">
                                ${eventData.poster ? `
                                    <div class="flex-shrink-0">
                                        <img src="${eventData.poster}" alt="Event Poster" class="w-48 h-60 object-cover rounded-lg shadow-lg">
                                    </div>
                                ` : `
                                    <div class="flex-shrink-0 w-48 h-60 bg-gray-700 rounded-lg flex items-center justify-center">
                                        <div class="text-center text-gray-400">
                                            <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <p class="text-sm">No Poster</p>
                                        </div>
                                    </div>
                                `}
                                
                                <div class="flex-1 text-white">
                                    <div class="mb-4">
                                        <h1 class="text-3xl lg:text-4xl font-bold mb-2">${eventData.title}</h1>
                                        ${eventData.category ? `
                                            <span class="inline-block px-3 py-1 bg-qricket-500 text-white text-sm font-medium rounded-full">
                                                ${eventData.category}
                                            </span>
                                        ` : ''}
                                    </div>
                                    
                                    <div class="space-y-3">
                                        ${eventData.start_date ? `
                                            <div class="flex items-center space-x-3">
                                                <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                                <span class="text-lg">${new Date(eventData.start_date).toLocaleDateString('en-US', { 
                                                    weekday: 'long', 
                                                    year: 'numeric', 
                                                    month: 'long', 
                                                    day: 'numeric',
                                                    hour: '2-digit',
                                                    minute: '2-digit'
                                                })}</span>
                                            </div>
                                        ` : ''}
                                        
                                        ${eventData.location ? `
                                            <div class="flex items-center space-x-3">
                                                <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    </svg>
                                                </div>
                                                <span class="text-lg">${eventData.location}</span>
                                            </div>
                                        ` : ''}
                                    </div>
                                    
                                    ${eventData.description ? `
                                        <div class="mt-6">
                                            <p class="text-gray-200 leading-relaxed">${eventData.description}</p>
                                        </div>
                                    ` : ''}

                                    <!-- Action Buttons -->
                                    <div class="flex items-center space-x-4 mt-8">
                                        <button type="button" class="px-6 py-3 bg-teal-500 hover:bg-teal-600 text-white font-medium rounded-lg transition-colors duration-200 flex items-center">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                            </svg>
                                            Buy Ticket
                                        </button>
                                        <button type="button" class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200 flex items-center">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                            </svg>
                                            Save Event
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            headerContainer.innerHTML = headerHTML;
        }

        function renderLayoutComponents(components) {
            const container = document.getElementById('layout-components-preview');
            
            if (!components || components.length === 0) {
                container.innerHTML = '<div class="text-center text-gray-500 py-8">No layout components added yet</div>';
                return;
            }

            console.log('Preview: Rendering', components.length, 'layout components');
            
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
            
            // Load each layout component via AJAX, preserving original order
            const results = new Array(components.length);
            let loadedComponents = 0;
            
            components.forEach((component, index) => {
                const layoutType = component.layout;
                const details = component.details || {};
                
                console.log(`Preview: Loading component ${index + 1}/${components.length}: ${layoutType}`);
                console.log('Preview: Component details:', details);
                console.log('Preview: Event data:', window.eventData);
                
                // Make AJAX request to load the layout template
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    console.error('CSRF token not found');
                    return;
                }
                
                console.log(`Preview: Making AJAX request to: /organizer/events/layouts/${layoutType}`);
                
                fetch(`/organizer/events/layouts/${layoutType}?id={{ $eventId }}`, {
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
                    console.log(`Preview: Successfully loaded ${layoutType} component`);
                    results[index] = html;
                    loadedComponents++;
                    if (loadedComponents === components.length) {
                        container.innerHTML = results.join('');
                        console.log('Preview: All layout components rendered successfully (ordered)');
                    }
                })
                .catch(error => {
                    console.error(`Preview: Failed to load ${layoutType} component:`, error);
                    
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
                        container.innerHTML = results.join('');
                    }
                });
            });
        }

        // Offcanvas Navigation Functions
        function openOffcanvasNavigation() {
            const offcanvas = document.getElementById('offcanvas-navigation');
            const panel = offcanvas.querySelector('.fixed.top-0.right-0');
            
            offcanvas.classList.remove('hidden');
            
            // Trigger animation
            setTimeout(() => {
                panel.classList.remove('translate-x-full');
            }, 10);
            
            // Prevent body scroll
            document.body.classList.add('overflow-hidden');
        }

        function closeOffcanvasNavigation() {
            const offcanvas = document.getElementById('offcanvas-navigation');
            const panel = offcanvas.querySelector('.fixed.top-0.right-0');
            
            panel.classList.add('translate-x-full');
            
            // Wait for animation to complete before hiding
            setTimeout(() => {
                offcanvas.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }, 300);
        }

        // Save preview function (placeholder)
        // Removed Save Preview action

        // Close offcanvas on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const offcanvas = document.getElementById('offcanvas-navigation');
                if (!offcanvas.classList.contains('hidden')) {
                    closeOffcanvasNavigation();
                }
            }
        });

        function updateStorageInfo() {
            const storageInfo = document.getElementById('storage-info');
            const timestamp = new Date().toLocaleString();
            storageInfo.textContent = `Layout saved to cloud storage at ${timestamp}`;
        }

        // Removed Save Preview AJAX function
    </script>
</x-unified-layout>


