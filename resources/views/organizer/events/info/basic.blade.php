<x-unified-layout :layout-type="$layoutType" :title="'Event Information - ' . $event->title" :page-title="'Basic Information'">
    <x-slot name="header">
        <x-manage-header :event="$event" />
    </x-slot>
    
    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Event Information</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">View event details and information</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Event Details -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Event Title</label>
                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-md border border-gray-200 dark:border-gray-600">
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ $event->title }}</p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-md border border-gray-200 dark:border-gray-600">
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($event->category) }}</p>
                                </div>
                            </div>

                            @if($event->tags)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tags</label>
                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-md border border-gray-200 dark:border-gray-600">
                                    @php
                                        $tagsArray = is_array($event->tags_array) ? $event->tags_array : explode(',', $event->tags);
                                        $tagsArray = array_filter(array_map('trim', $tagsArray));
                                    @endphp
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($tagsArray as $tag)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-qricket-100 text-qricket-800 dark:bg-qricket-900 dark:text-qricket-200">
                                                {{ $tag }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date & Time</label>
                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-md border border-gray-200 dark:border-gray-600">
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ \Carbon\Carbon::parse($event->start_date)->format('M j, Y \a\t g:i A') }}</p>
                                </div>
                            </div>

                            @if($event->end_date)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date & Time</label>
                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-md border border-gray-200 dark:border-gray-600">
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ \Carbon\Carbon::parse($event->end_date)->format('M j, Y \a\t g:i A') }}</p>
                                </div>
                            </div>
                            @endif

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Location</label>
                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-md border border-gray-200 dark:border-gray-600">
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ $event->location }}</p>
                                    @if($event->location_url)
                                        <a href="{{ $event->location_url }}" target="_blank" class="text-qricket-600 dark:text-qricket-400 hover:text-qricket-800 dark:hover:text-qricket-300 text-xs mt-1 inline-block">View on Map</a>
                                    @endif
                                </div>
                            </div>

                            @if($event->max_participants)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Maximum Participants</label>
                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-md border border-gray-200 dark:border-gray-600">
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ number_format($event->max_participants) }}</p>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Event Description & Thumbnail -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-md border border-gray-200 dark:border-gray-600 min-h-[120px]">
                                    <p class="text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $event->description }}</p>
                                </div>
                            </div>

                            @if($event->thumbnail_url)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Event Thumbnail</label>
                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-md border border-gray-200 dark:border-gray-600">
                                    <img src="{{ $event->thumbnail_url }}" alt="Event Thumbnail" class="w-full h-32 object-cover rounded-md">
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Event Status -->
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Event Status</label>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    @if($event->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                    @elseif($event->status === 'pending_approval') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                    @elseif($event->status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                    @elseif($event->status === 'draft') bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                                    @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $event->status)) }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                Created: {{ $event->created_at->format('M j, Y') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-unified-layout>
