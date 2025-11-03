<x-unified-layout :layout-type="$layoutType" :title="'Staff Management - ' . $event->title" :page-title="'Staff'">
    <x-slot name="header">
        <x-manage-header :event="$event" />
    </x-slot>
    
    <div class="py-4">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('organizer.events.staff.store') }}?id={{ $event->id }}" class="space-y-4" data-search-url="{{ route('organizer.events.staff.search') }}" data-autosave-url="{{ route('organizer.events.staff.autosave') }}?id={{ $event->id }}">
                        @csrf
                        @include('organizer.events.forms.staff', ['eventId' => $event->id, 'eventData' => $eventData ?? []])
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/events/step5.js') }}" defer></script>
</x-unified-layout>