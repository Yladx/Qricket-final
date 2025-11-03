<x-unified-layout :layout-type="$layoutType" :title="'Layout Information - ' . $event->title" :page-title="'Layout Information'">
    <x-slot name="header">
        <x-manage-header :event="$event" />
    </x-slot>
    
    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Layout Information</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">View event layout and design information</p>
                    </div>

                    @php
                        $hasLayoutData = !empty($layoutData) && is_array($layoutData) && count($layoutData) > 0;
                    @endphp

                    @if($hasLayoutData)
                        <!-- Layout Preview -->
                        <div class="mb-8">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Layout Preview</h4>
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                <!-- Event Header Preview -->
                                <div class="mb-6">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Event Header Preview</h4>
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                        <x-events.event-header :event="$event" :section="'info'" :hideActions="true" />
                                    </div>
                                </div>
                                
                                <!-- Page preview container -->
                                <div id="organizer-layout-container" class="space-y-4 p-6"></div>
                            </div>
                        </div>

                        <!-- Layout Settings -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Design Settings</h4>
                                <div class="space-y-3">
                                    @if(isset($layoutData['primary_color']))
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Primary Color:</span>
                                            <div class="flex items-center space-x-2">
                                                <div class="w-6 h-6 rounded border border-gray-300 dark:border-gray-600" style="background-color: {{ $layoutData['primary_color'] }}"></div>
                                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ $layoutData['primary_color'] }}</span>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if(isset($layoutData['secondary_color']))
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Secondary Color:</span>
                                            <div class="flex items-center space-x-2">
                                                <div class="w-6 h-6 rounded border border-gray-300 dark:border-gray-600" style="background-color: {{ $layoutData['secondary_color'] }}"></div>
                                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ $layoutData['secondary_color'] }}</span>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if(isset($layoutData['accent_color']))
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Accent Color:</span>
                                            <div class="flex items-center space-x-2">
                                                <div class="w-6 h-6 rounded border border-gray-300 dark:border-gray-600" style="background-color: {{ $layoutData['accent_color'] }}"></div>
                                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ $layoutData['accent_color'] }}</span>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if(isset($layoutData['font_family']))
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Font Family:</span>
                                            <span class="text-sm text-gray-900 dark:text-gray-100">{{ $layoutData['font_family'] }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Layout Elements</h4>
                                <div class="space-y-3">
                                    @if(isset($layoutData['show_header']))
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Show Header:</span>
                                            <span class="text-sm text-gray-900 dark:text-gray-100">{{ $layoutData['show_header'] ? 'Yes' : 'No' }}</span>
                                        </div>
                                    @endif
                                    
                                    @if(isset($layoutData['show_footer']))
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Show Footer:</span>
                                            <span class="text-sm text-gray-900 dark:text-gray-100">{{ $layoutData['show_footer'] ? 'Yes' : 'No' }}</span>
                                        </div>
                                    @endif
                                    
                                    @if(isset($layoutData['show_sidebar']))
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Show Sidebar:</span>
                                            <span class="text-sm text-gray-900 dark:text-gray-100">{{ $layoutData['show_sidebar'] ? 'Yes' : 'No' }}</span>
                                        </div>
                                    @endif
                                    
                                    @if(isset($layoutData['layout_type']))
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Layout Type:</span>
                                            <span class="text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($layoutData['layout_type']) }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if(isset($layoutData['custom_css']) && $layoutData['custom_css'])
                            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                                <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Custom CSS</h4>
                                <div class="bg-gray-900 dark:bg-gray-800 rounded-md p-4">
                                    <pre class="text-sm text-gray-300 overflow-x-auto"><code>{{ $layoutData['custom_css'] }}</code></pre>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No layout configured</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This event doesn't have a custom layout configured yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- CSRF Token for AJAX requests -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        // Organizer layout rendering (same as admin approval and step2 preview)
        const organizerEventData = @json($event);
        let organizerLayoutData = @json($layoutData ?? []);

        function loadOrganizerLayout() {
            let components = organizerLayoutData;
            
            // Check if we have valid layout data
            if (!components) {
                console.log('Layout Info: No layout data provided');
                return;
            }
            
            if (typeof components === 'string') {
                try { 
                    components = JSON.parse(components); 
                } catch (e) { 
                    console.error('Layout Info: Failed to parse layout data:', e);
                    components = []; 
                }
            }
            
            if (!Array.isArray(components) || components.length === 0) {
                console.log('Layout Info: Layout data is empty or not an array', components);
                const container = document.getElementById('organizer-layout-container');
                if (container) {
                    container.innerHTML = '<div class="text-sm text-gray-500 dark:text-gray-400">No layout components configured.</div>';
                }
                return;
            }
            
            console.log('Layout Info: Loading ' + components.length + ' layout components');

            const container = document.getElementById('organizer-layout-container');

            // Show loading state
            container.innerHTML = `
                <div class="space-y-4">
                    <div class="animate-pulse">
                        <div class="bg-gray-200 dark:bg-gray-700 rounded-lg h-32 mb-4"></div>
                    </div>
                    <div class="animate-pulse">
                        <div class="bg-gray-200 dark:bg-gray-700 rounded-lg h-24 mb-4"></div>
                            </div>
                        </div>
                    `;

            // Make sure rendering is always in order.
            const results = new Array(components.length);
            let loadedComponents = 0;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            components.forEach((component, index) => {
                const layoutType = component.layout;
                const details = component.details || {};

                console.log(`Layout Info: Loading component ${index + 1}/${components.length}: ${layoutType}`);

                fetch(`/events/layouts/${layoutType}?id={{ $event->id }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ eventData: organizerEventData, details })
                })
                .then(r => { 
                    if (!r.ok) throw new Error('HTTP ' + r.status); 
                    return r.text(); 
                    })
                    .then(html => {
                        console.log(`Layout Info: Successfully loaded ${layoutType} component`);
                    results[index] = html;
                        loadedComponents++;
                    if (loadedComponents === components.length) {
                        container.innerHTML = results.join('');
                        console.log('Layout Info: All layout components rendered successfully (ordered)');
                        }
                    })
                    .catch(error => {
                        console.error(`Layout Info: Failed to load ${layoutType} component:`, error);
                    // Fallback to simple rendering in the correct index
                    results[index] = `
                        <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        ${layoutType.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                    </h3>
                                <span class="px-2 py-1 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 text-xs rounded">
                                    Error Loading
                                </span>
                            </div>
                            <p class="text-red-500 text-sm">Failed to load layout component: ${layoutType}</p>
                            <p class="text-red-500 text-xs">Error: ${error.message}</p>
                                </div>
                            `;
                    loadedComponents++;
                    if (loadedComponents === components.length) {
                        container.innerHTML = results.join('');
                        }
                    });
                });
            }

        // Default to render layout
        document.addEventListener('DOMContentLoaded', () => { 
            loadOrganizerLayout(); 
        });
    </script>
</x-unified-layout>
