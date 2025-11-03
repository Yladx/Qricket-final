<x-app-layout title="Select Certificate - {{ $event->title }}">
    <x-slot name="header">
        <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                <h1 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Select a Certificate</h1>
                <a href="{{ route('events.show', $event) }}" class="text-sm text-qricket-600 dark:text-qricket-400 hover:underline">Back to event</a>
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen flex flex-col">
        <div class="flex-1 py-10">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                    <div class="p-6">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">You have multiple eligible certificates for this event. Choose one to view.</p>
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($tickets as $t)
                            <div class="py-4 flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Ticket: {{ $t->ticket_id }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Checked-in purchase</div>
                                </div>
                                <div class="flex items-center gap-2">
                                        <a href="{{ route('events.certificate.public', $event) }}?ticket={{ $t->ticket_id }}" class="px-3 py-1.5 text-xs bg-indigo-600 text-white rounded hover:bg-indigo-700 transition-colors">View</a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Include Event Footer - Always at bottom -->
        <div class="mt-auto">
            <x-events.event-footer :event="$event" />
        </div>
    </div>
</x-app-layout>


