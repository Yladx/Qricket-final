<x-unified-layout :layout-type="$layoutType" :title="'Edit Event - Layout'" :page-title="'Edit Layout'">
    <x-slot name="header">
        <x-manage-header :event="$event" />
    </x-slot>

    <div class="py-0">
        <div class="max-w-5xl mx-auto px-4 sm:px-8 lg:px-8">
            <form method="POST" action="{{ route('organizer.events.layout.store') }}?id={{ $event->id }}" class="space-y-8" data-autosave-url="{{ route('organizer.events.layout.autosave') }}?id={{ $event->id }}">
                @csrf
                @include('organizer.events.forms.layout', ['eventData' => $eventData, 'event' => $event, 'footerData' => $footerData ?? []])

                <!-- Navigation Buttons - Mobile Responsive -->
                <div class="pt-0 border-t border-gray-200 dark:border-gray-700">
                    <!-- Mobile Layout: Stacked Buttons -->
                    <div class="flex flex-col space-y-3 md:hidden">
                        <!-- Back Button -->
                        <a href="{{ route('organizer.events.manage', ['event' => $event->id]) }}" class="w-full px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Event
                        </a>
                        
                        <!-- Action Buttons Row -->
                        <div class="flex space-x-3">
                            <button type="submit" class="flex-1 px-4 py-3 text-sm font-medium text-white bg-emerald-500 hover:bg-emerald-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 flex items-center justify-center">
                                Save Changes
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Desktop Layout: Side by Side -->
                    <div class="hidden md:flex items-center justify-between">
                        <a href="{{ route('organizer.events.manage', ['event' => $event->id]) }}" class="px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Event
                        </a>
                        <button type="submit" class="px-6 py-3 text-sm font-medium text-white bg-emerald-500 hover:bg-emerald-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 flex items-center">
                            Save Changes
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Layout Selection Modal -->
    @include('organizer.events.components.layout-modal')

    <!-- Initialize existing layout data -->
    <script>
        // Initialize existing layout data if available
        window.existingLayoutData = [];
        @if(isset($layoutData))
            window.existingLayoutData = @json($layoutData);
        @endif
        window.existingLayoutPath = @json($layoutPath ?? null);
        // Provide current event id for image hydration/upload helpers
        window.currentEventId = {{ $event->id }};

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
</x-unified-layout>
