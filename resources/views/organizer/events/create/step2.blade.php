<x-app-layout>
    <x-slot name="title">Create Event - Step 2: Design & Layout</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-4">
            {{ __('Create Event - Step 2: Design & Layout') }}
        </h2>

        @include('organizer.events.components.progress-bar', ['currentStep' => 2])
    </x-slot>

   


    <div class="py-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-8 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Form Column -->
                <div class="lg:col-span-2">
                    <form method="POST" action="{{ route('organizer.events.layout.store') }}?id={{ $eventId ?? ($event->id ?? null) }}" class="space-y-8" data-autosave-url="{{ route('organizer.events.layout.autosave') }}?id={{ $eventId ?? ($event->id ?? null) }}">
                        @csrf
                        @include('organizer.events.forms.layout', ['eventData' => $eventData])

                        <!-- Navigation Buttons - Mobile Responsive -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-0 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('organizer.events.step1') }}?id={{ $eventId }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Back to Basic Info
                            </a>
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
                                <a href="{{ route('organizer.events.step2.preview', $eventId ?? ($event->id ?? 0)) }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-3 text-sm font-medium bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Preview
                                </a>
                                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-3 text-sm font-medium text-white bg-qricket-500 hover:bg-qricket-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-qricket-500">
                                    <span class="hidden sm:inline">Next: Ticket Pricing</span>
                                    <span class="sm:hidden">Next</span>
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Sidebar Column -->
                <div class="lg:col-span-1">
                    <div class="sticky top-6 space-y-4">
                        <!-- Design & Layout Guide Sidebar -->
                        <div class="bg-teal-50 dark:bg-teal-900/20 border border-teal-200 dark:border-teal-800 rounded-lg p-4 shadow-sm">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-teal-600 dark:text-teal-400 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-teal-900 dark:text-teal-100 mb-2">Design & Layout Guide</h4>
                                    <div class="text-sm text-teal-800 dark:text-teal-200 space-y-2">
                                        <ul class="list-disc list-inside space-y-1.5 ml-2 text-xs">
                                            <li><strong>Poster:</strong> Upload a high-quality poster image (landscape orientation recommended). This will be displayed as the main visual on your event page.</li>
                                            <li><strong>Thumbnail:</strong> Upload a square thumbnail image. This appears in event listings, cards, and search results.</li>
                                            <li><strong>Layout Components:</strong> Add custom layout sections to showcase event details, schedules, speakers, sponsors, or any additional information. Click the "Add Layout" button to add components.</li>
                                            <li><strong>Footer Settings:</strong> Configure contact information and social media links that appear at the bottom of your event page.</li>
                                        </ul>
                                        <div class="mt-3 p-2 bg-teal-100 dark:bg-teal-900/30 rounded border border-teal-200 dark:border-teal-700">
                                            <p class="text-xs font-medium text-teal-900 dark:text-teal-100 mb-1">💡 Tip:</p>
                                            <p class="text-xs text-teal-800 dark:text-teal-200">Use high-resolution images for best quality. Supported formats: PNG, JPG, WEBP. Maximum file size: 5MB per image.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Layout Selection Modal -->
    @include('organizer.events.components.layout-modal')

    <!-- Initialize existing layout data -->
    <script>
        // Initialize existing layout data if available
        window.existingLayoutData = [];
        @if(isset($eventData['existing_layout']))
            window.existingLayoutData = @json($eventData['existing_layout']);
        @endif
        window.existingLayoutPath = @json($layoutPath ?? null);
          // Provide current event id for image hydration/upload helpers
          window.currentEventId = @json($eventId ?? ($event->id ?? null));
    
        // Provide URL template for JS to fetch latest layout on back/forward
        window.__layoutLoadUrlTemplate = `{{ route('organizer.events.layout.preview.load', ['cloudPath' => 'REPLACE']) }}`;
    </script>

    <!-- Step 2 JavaScript Modules -->
    <script src="{{ asset('js/step2/autosave.js') }}"></script>
    <script src="{{ asset('js/step2/uploader.js') }}"></script>
    <script src="{{ asset('js/step2/components.js') }}"></script>
    <script src="{{ asset('js/step2/init.js') }}"></script>
    <script>
        // Ensure latest layout is loaded when navigating back (BFCache restore)
        // Lightweight loader helpers
        function showLayoutLoader() {
            let el = document.getElementById('layout-soft-loader');
            if (!el) {
                el = document.createElement('div');
                el.id = 'layout-soft-loader';
                el.className = 'fixed top-4 right-4 z-50 flex items-center gap-2 px-3 py-2 rounded-md bg-black/70 text-white text-sm shadow';
                el.innerHTML = '<svg class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg><span>Refreshing layout…</span>';
                document.body.appendChild(el);
            }
            el.style.display = 'flex';
        }
        function hideLayoutLoader() {
            const el = document.getElementById('layout-soft-loader');
            if (el) el.style.display = 'none';
        }

        window.addEventListener('pageshow', function(e) {
            const fromBFCache = e.persisted || (performance && performance.getEntriesByType && performance.getEntriesByType('navigation')[0]?.type === 'back_forward');
            if (fromBFCache && typeof window.fetchAndRenderLatestLayout === 'function') {
                showLayoutLoader();
                window.fetchAndRenderLatestLayout()
                    .finally(() => hideLayoutLoader());
            }
        });

        // Toggle "View more" for description in header preview
        document.addEventListener('click', function(e){
            const btn = e.target.closest('#step2-desc-toggle');
            if (!btn) return;
            const el = document.getElementById('step2-desc');
            if (!el) return;
            const full = @json(($eventData['description'] ?? $event->description ?? ''));
            const short = full.length > 160 ? full.substring(0, 160) + '…' : full;
            if (btn.dataset.expanded === '1') {
                el.textContent = short;
                btn.textContent = 'View more';
                btn.dataset.expanded = '0';
            } else {
                el.textContent = full;
                btn.textContent = 'View less';
                btn.dataset.expanded = '1';
            }
        });
    </script>
    <script src="{{ asset('js/step2/form-monitor.js') }}"></script>
    <script src="{{ asset('js/step2/modal.js') }}"></script>

    

    {{-- Layout Manager Component --}}
    <x-layout-manager />
</x-app-layout>

