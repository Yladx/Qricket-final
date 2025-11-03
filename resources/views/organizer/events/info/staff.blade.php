<x-unified-layout :layout-type="$layoutType" :title="'Staff Information - ' . $event->title" :page-title="'Staff Information'">
    <x-slot name="header">
        <x-manage-header :event="$event" />
    </x-slot>
    
    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Staff Information</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">View event staff members and their roles</p>
                    </div>

                    @if($event->staff && $event->staff->count() > 0)
                        <!-- Staff Preview Section -->
                        <div class="mb-8">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Staff Preview</h4>
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                @php
                                    $staffData = [
                                        'members' => $event->staff->map(function($staff) {
                                            return [
                                                'name' => $staff->user->first_name . ' ' . $staff->user->last_name,
                                                'email' => $staff->user->email,
                                                'role' => $staff->role,
                                                'permissions' => [
                                                    'can_manage_event' => $staff->can_manage_event,
                                                    'can_manage_ticket' => $staff->can_manage_ticket,
                                                    'can_manage_attendance' => $staff->can_manage_attendance,
                                                    'can_manage_news' => $staff->can_manage_news,
                                                    'can_manage_sales' => $staff->can_manage_sales,
                                                    'can_manage_staff' => $staff->can_manage_staff,
                                                ]
                                            ];
                                        })->toArray()
                                    ];
                                @endphp
                                @include('organizer.events.preview.staff-preview', [
                                    'staffData' => $staffData,
                                    'event' => $event
                                ])
                            </div>
                        </div>

                        <!-- Staff Details Section -->
                        <div class="space-y-4">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Staff Details</h4>
                            @foreach($event->staff as $staff)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-qricket-100 dark:bg-qricket-900 rounded-full flex items-center justify-center">
                                                <span class="text-qricket-600 dark:text-qricket-400 font-semibold text-sm">
                                                    {{ substr($staff->user->first_name, 0, 1) }}{{ substr($staff->user->last_name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ $staff->user->first_name }} {{ $staff->user->last_name }}
                                                </h4>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $staff->user->email }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($staff->role === 'organizer') bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200
                                                @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 @endif">
                                                {{ ucfirst($staff->role) }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    @if($staff->role !== 'organizer')
                                        <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                                                <div class="flex items-center">
                                                    <span class="font-medium text-gray-700 dark:text-gray-300 mr-2">Manage Event:</span>
                                                    <span class="text-gray-900 dark:text-gray-100">{{ $staff->can_manage_event ? 'Yes' : 'No' }}</span>
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="font-medium text-gray-700 dark:text-gray-300 mr-2">Manage Tickets:</span>
                                                    <span class="text-gray-900 dark:text-gray-100">{{ $staff->can_manage_ticket ? 'Yes' : 'No' }}</span>
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="font-medium text-gray-700 dark:text-gray-300 mr-2">Manage Attendance:</span>
                                                    <span class="text-gray-900 dark:text-gray-100">{{ $staff->can_manage_attendance ? 'Yes' : 'No' }}</span>
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="font-medium text-gray-700 dark:text-gray-300 mr-2">Manage News:</span>
                                                    <span class="text-gray-900 dark:text-gray-100">{{ $staff->can_manage_news ? 'Yes' : 'No' }}</span>
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="font-medium text-gray-700 dark:text-gray-300 mr-2">Manage Sales:</span>
                                                    <span class="text-gray-900 dark:text-gray-100">{{ $staff->can_manage_sales ? 'Yes' : 'No' }}</span>
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="font-medium text-gray-700 dark:text-gray-300 mr-2">Manage Staff:</span>
                                                    <span class="text-gray-900 dark:text-gray-100">{{ $staff->can_manage_staff ? 'Yes' : 'No' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No staff members</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This event doesn't have any additional staff members.</p>
                        </div>
                    @endif

                    <!-- Event Owner -->
                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Event Owner</h4>
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                    <span class="text-green-600 dark:text-green-400 font-semibold text-sm">
                                        {{ substr($event->user->first_name, 0, 1) }}{{ substr($event->user->last_name, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $event->user->first_name }} {{ $event->user->last_name }}
                                    </h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $event->user->email }}</p>
                                </div>
                                <div class="ml-auto">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        Owner
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-unified-layout>
