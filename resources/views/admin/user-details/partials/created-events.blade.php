@if($createdEvents->count())
    <div class="overflow-x-auto created-events-scroll">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Event</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Date</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Status</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($createdEvents as $e)
                    <tr>
                        <td class="px-4 py-2 text-gray-900 dark:text-gray-100">{{ $e->title }}</td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ optional($e->start_date)->format('M j, Y') }}</td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ ucfirst($e->status) }}</td>
                        <td class="px-4 py-2 text-right">
                            @if($e->status === 'draft')
                                <a href="{{ route('organizer.events.preview', $e->id) }}" class="text-qricket-600 dark:text-qricket-400 hover:text-qricket-800 dark:hover:text-qricket-300">Preview</a>
                            @else
                                <a href="{{ route('admin.event.manage', $e->id) }}" class="text-qricket-600 dark:text-qricket-400 hover:text-qricket-800 dark:hover:text-qricket-300">Manage</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center py-16">
        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">No Created Events</h3>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">This user hasn't created any events yet.</p>
    </div>
@endif

<style>
/* Custom Scrollbar Styling */
.created-events-scroll {
    scrollbar-width: thin;
    scrollbar-color: #4a5568 #1a202c;
}

.created-events-scroll::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.created-events-scroll::-webkit-scrollbar-track {
    background: #1a202c;
    border-radius: 4px;
}

.created-events-scroll::-webkit-scrollbar-thumb {
    background: #4a5568;
    border-radius: 4px;
}

.created-events-scroll::-webkit-scrollbar-thumb:hover {
    background: #718096;
}

/* Dark mode scrollbar */
.dark .created-events-scroll {
    scrollbar-color: #6b7280 #111827;
}

.dark .created-events-scroll::-webkit-scrollbar-track {
    background: #111827;
}

.dark .created-events-scroll::-webkit-scrollbar-thumb {
    background: #6b7280;
}

.dark .created-events-scroll::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}
</style>

