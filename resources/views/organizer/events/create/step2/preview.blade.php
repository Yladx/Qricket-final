<x-app-layout>
    <x-slot name="title">Preview Event Layout - Step 2</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-4">
                    {{ __('Preview Event Layout') }}
                </h2>
                @include('organizer.events.components.progress-bar', ['currentStep' => 2])
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('organizer.events.create.layout') }}?id={{ $eventId }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Step 2
                </a>
            </div>
        </div>
    </x-slot>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="event-id" content="{{ $eventId }}">

    <div class="py-0 min-h-screen relative z-10 flex flex-col">
        <div class="relative z-10 flex-1">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <!-- Event Header Preview -->
                <div id="event-header-preview" class="mb-8">
                    <x-events.event-header :event="$event" :section="'info'" :hideActions="true" />
                </div>

                <!-- Layout Components Preview - Matching event-info structure -->
                <div class="p-6 relative z-10">
                    <div id="layout-components-display" class="space-y-8 relative z-10">
                        <!-- Loading state -->
                        <div class="text-center py-12">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-qricket-600"></div>
                            <p class="mt-4 text-gray-600 dark:text-gray-400">Loading preview...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Organizer layout rendering (same as admin approval)
        const organizerEventData = @json($event);
        let organizerLayoutData = @json($layoutData);

        function loadOrganizerLayout() {
            let components = organizerLayoutData;
            if (!components) return;
            if (typeof components === 'string') {
                try { components = JSON.parse(components); } catch (e) { components = []; }
            }
            if (!Array.isArray(components) || components.length === 0) {
                document.getElementById('layout-components-display').innerHTML = '<div class="text-sm text-gray-500">No layout configured.</div>';
                return;
            }

            const container = document.getElementById('layout-components-display');

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

                console.log(`Step2 Preview: Loading component ${index + 1}/${components.length}: ${layoutType}`);

                fetch(`/events/layouts/${layoutType}?id={{ $eventId }}`, {
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
                    console.log(`Step2 Preview: Successfully loaded ${layoutType} component`);
                    results[index] = html;
                    loadedComponents++;
                    if (loadedComponents === components.length) {
                        container.innerHTML = results.join('');
                        console.log('Step2 Preview: All layout components rendered successfully (ordered)');
                    }
                })
                .catch(error => {
                    console.error(`Step2 Preview: Failed to load ${layoutType} component:`, error);
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
</x-app-layout>
