<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-4">
            {{ __('Create Event - Step 5: Staff Management') }}
        </h2>

        @include('organizer.events.components.progress-bar', ['currentStep' => 5])
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Form Column -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <form method="POST" action="{{ route('organizer.events.staff.store') }}?id={{ $eventId }}" class="space-y-8" data-search-url="{{ route('organizer.events.staff.search') }}" data-autosave-url="{{ route('organizer.events.staff.autosave') }}?id={{ $eventId }}">
                                @csrf
                                @include('organizer.events.forms.staff', ['eventId' => $eventId, 'eventData' => $eventData, 'event' => $event])

                                <!-- Navigation Buttons -->
                                <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                                    <a href="{{ route('organizer.events.create.certificate', ['id' => $eventId]) }}" 
                                       class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-500 focus:bg-gray-400 dark:focus:bg-gray-500 active:bg-gray-500 dark:active:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                        Back to Certificate
                                    </a>

                                    <button type="submit" 
                                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                        Next: Review & Publish
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar Column -->
                <div class="lg:col-span-1">
                    <div class="sticky top-6 space-y-4">
                        <!-- Staff Management Guide Sidebar -->
                        <div class="bg-teal-50 dark:bg-teal-900/20 border border-teal-200 dark:border-teal-800 rounded-lg p-4 shadow-sm">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-teal-600 dark:text-teal-400 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-teal-900 dark:text-teal-100 mb-2">Staff Management Guide</h4>
                                    <div class="text-sm text-teal-800 dark:text-teal-200 space-y-2">
                                        <ul class="list-disc list-inside space-y-1.5 ml-2 text-xs">
                                            <li><strong>Search & Add Staff:</strong> Search for users by name or email to add them as staff members to your event.</li>
                                            <li><strong>Roles:</strong> Assign roles to staff members (e.g., Organizer, Manager, Staff, Scanner) to define their responsibilities.</li>
                                            <li><strong>Permissions:</strong> Grant specific permissions to each staff member:
                                                <ul class="ml-4 mt-1 space-y-0.5 list-circle list-inside text-xs">
                                                    <li><strong>Manage Event:</strong> Edit event information and settings</li>
                                                    <li><strong>Manage Staff:</strong> Add or remove staff members</li>
                                                    <li><strong>Manage Tickets:</strong> Configure ticket types and pricing</li>
                                                    <li><strong>Manage News:</strong> Create and edit event announcements</li>
                                                    <li><strong>Manage Attendance:</strong> Scan tickets and check in attendees</li>
                                                    <li><strong>Manage Sales:</strong> View earnings and financial reports</li>
                                                </ul>
                                            </li>
                                            <li><strong>Welcome Message:</strong> Set a custom message that will be sent to staff members when they're added to the event.</li>
                                            <li><strong>Staff Instructions:</strong> Provide detailed instructions and guidelines that staff members will see in their dashboard.</li>
                                        </ul>
                                        <div class="mt-3 p-2 bg-teal-100 dark:bg-teal-900/30 rounded border border-teal-200 dark:border-teal-700">
                                            <p class="text-xs font-medium text-teal-900 dark:text-teal-100 mb-1">💡 Tip:</p>
                                            <p class="text-xs text-teal-800 dark:text-teal-200">Assign permissions carefully. Staff members will only see and access features based on their assigned permissions. You can always update permissions later.</p>
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

    <!-- Include Step 5 JavaScript -->
    <script src="{{ asset('js/events/step5.js') }}?v={{ time() }}" defer></script>

</x-app-layout>

