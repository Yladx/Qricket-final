<x-admin-layout>
    <x-slot name="title">Event Details</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Event Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Event Header -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between">
                            <h1 class="text-3xl font-bold">{{ $event->title }}</h1>
                            <div class="flex items-center space-x-2">
                                <span class="px-3 py-1 rounded-full text-sm font-medium
                                    @if($event->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                    @elseif($event->status === 'pending_approval') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                    @elseif($event->status === 'rejected') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $event->status)) }}
                                </span>
                            </div>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">{{ $event->category }}</p>
                    </div>

                    <!-- Event Details Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Main Content -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Description -->
                            <div>
                                <h3 class="text-lg font-semibold mb-3">Description</h3>
                                <div class="prose dark:prose-invert max-w-none">
                                    {!! $event->description !!}
                                </div>
                            </div>

                            <!-- Event Details -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h3 class="text-lg font-semibold mb-3">Event Details</h3>
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <dt class="font-medium text-gray-700 dark:text-gray-300">Start Date</dt>
                                        <dd class="text-gray-900 dark:text-gray-100">{{ $event->start_date->format('M d, Y g:i A') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-700 dark:text-gray-300">End Date</dt>
                                        <dd class="text-gray-900 dark:text-gray-100">{{ $event->end_date->format('M d, Y g:i A') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-700 dark:text-gray-300">Location</dt>
                                        <dd class="text-gray-900 dark:text-gray-100">{{ $event->location }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-700 dark:text-gray-300">Created</dt>
                                        <dd class="text-gray-900 dark:text-gray-100">{{ $event->created_at->format('M d, Y') }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Organizers -->
                            @if($event->organizers->count() > 0)
                            <div>
                                <h3 class="text-lg font-semibold mb-3">Organizers</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @foreach($event->organizers as $organizer)
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-qricket-500 rounded-full flex items-center justify-center">
                                                <span class="text-white font-semibold">
                                                    {{ substr($organizer->user->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <p class="font-medium">{{ $organizer->user->name }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $organizer->user->email }}</p>
                                                <span class="inline-block px-2 py-1 bg-qricket-100 dark:bg-qricket-900 text-qricket-800 dark:text-qricket-200 text-xs rounded-full">
                                                    {{ ucfirst($organizer->role) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Staff -->
                            @if($event->staff->count() > 0)
                            <div>
                                <h3 class="text-lg font-semibold mb-3">Staff</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @foreach($event->staff as $staff)
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                                <span class="text-white font-semibold">
                                                    {{ substr($staff->user->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <p class="font-medium">{{ $staff->user->name }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $staff->user->email }}</p>
                                                <span class="inline-block px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs rounded-full">
                                                    {{ $staff->role }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Sidebar -->
                        <div class="space-y-6">
                            <!-- Approval Actions -->
                            @if($event->status === 'pending_approval')
                            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <h3 class="text-lg font-semibold mb-4">Approval Actions</h3>
                                <form method="POST" action="{{ route('admin.events.approve', $event) }}" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="flex items-center">
                                            <input type="radio" name="approved" value="1" class="mr-2">
                                            <span class="text-green-600 dark:text-green-400 font-medium">Approve Event</span>
                                        </label>
                                    </div>
                                    <div>
                                        <label class="flex items-center">
                                            <input type="radio" name="approved" value="0" class="mr-2">
                                            <span class="text-red-600 dark:text-red-400 font-medium">Reject Event</span>
                                        </label>
                                    </div>
                                    <div>
                                        <label for="rejection_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Rejection Reason (if rejecting)
                                        </label>
                                        <textarea id="rejection_reason" name="rejection_reason" rows="3" 
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-qricket-500 focus:border-qricket-500 dark:bg-gray-700 dark:text-gray-300"
                                            placeholder="Explain why the event is being rejected..."></textarea>
                                    </div>
                                    <button type="submit" class="w-full bg-qricket-500 hover:bg-qricket-600 text-white font-medium py-2 px-4 rounded-md transition-colors duration-200">
                                        Submit Decision
                                    </button>
                                </form>
                            </div>
                            @endif

                            <!-- Event Images -->
                            @if($event->poster || $event->thumbnail)
                            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <h3 class="text-lg font-semibold mb-4">Event Images</h3>
                                <div class="space-y-4">
                                    @if($event->poster)
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Poster</p>
                                        <img src="{{ Storage::url($event->poster) }}" alt="Event Poster" 
                                            class="w-full h-48 object-cover rounded-lg">
                                    </div>
                                    @endif
                                    @if($event->thumbnail)
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Thumbnail</p>
                                        <img src="{{ Storage::url($event->thumbnail) }}" alt="Event Thumbnail" 
                                            class="w-full h-32 object-cover rounded-lg">
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <!-- Tickets -->
                            @if($event->tickets->count() > 0)
                            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <h3 class="text-lg font-semibold mb-4">Tickets</h3>
                                <div class="space-y-3">
                                    @foreach($event->tickets as $ticket)
                                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-3">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="font-medium">{{ $ticket->name }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">${{ number_format($ticket->price, 2) }}</p>
                                            </div>
                                            <span class="px-2 py-1 text-xs rounded-full
                                                @if($ticket->is_active) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                                                {{ $ticket->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                        @if($ticket->quantity)
                                        <div class="mt-2">
                                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                                {{ $ticket->sold_count }} / {{ $ticket->quantity }} sold
                                            </p>
                                            <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2 mt-1">
                                                <div class="bg-qricket-500 h-2 rounded-full" 
                                                     style="width: {{ $ticket->quantity > 0 ? ($ticket->sold_count / $ticket->quantity) * 100 : 0 }}%"></div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Back Button -->
                    <div class="mt-8 flex justify-start">
                        <a href="{{ route('admin.events.approval') }}" 
                           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition-colors duration-200">
                            ← Back to Event Approval
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
