<x-unified-layout :layout-type="$layoutType" :title="'News Management - ' . $event->title" :page-title="'News Management'">
    <x-slot name="header">
        <x-manage-header :event="$event" />
    </x-slot>
    
    <div class="py-4">
        <div class="max-w-4xl mx-auto px-4 sm:px-4 lg:px-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-lg font-bold text-gray-900 dark:text-white">Event News</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Manage news and announcements for {{ $event->title }}</p>
                </div>
                @if(!$isAdmin)
                <div class="flex items-center">
                    <a href="{{ route('organizer.events.news.create', $event) }}" class="inline-flex items-center px-3 py-1.5 bg-qricket-600 hover:bg-qricket-700 text-white rounded-md shadow-sm transition text-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        New Announcement
                    </a>
                </div>
                @endif
            </div>

            <!-- News List (latest first) -->
            @if($news->count() > 0)
                <x-events.news :news="$news" :event="$event" :can-manage="true" />
                <div class="mt-4">{{ $news->links() }}</div>
            @else
                <div class="text-center py-8">
                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                    </svg>
                    <h3 class="text-base font-medium text-gray-900 dark:text-white mb-2">No news articles yet</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Start by creating your first news article to keep your attendees informed.</p>
                    @if(!$isAdmin)
                    <a href="{{ route('organizer.events.news.create', $event) }}" class="inline-flex items-center px-3 py-1.5 bg-qricket-600 hover:bg-qricket-700 text-white rounded-md transition text-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        New Announcement
                    </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-menu]');
            document.querySelectorAll('[id^="menu-"]').forEach(m => m.classList.add('hidden'));
            if (btn) {
                const id = btn.getAttribute('data-menu');
                const menu = document.getElementById(id);
                if (menu) menu.classList.toggle('hidden');
            }
        });
    </script>

</x-unified-layout>
