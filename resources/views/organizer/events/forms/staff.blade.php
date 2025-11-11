<div class="space-y-4">
<!-- Informative Note for Staff Management -->
<div class="bg-indigo-50 dark:bg-indigo-900/20 border-l-4 border-indigo-500 dark:border-indigo-400 rounded-r-lg p-4 mb-6">
    <div class="flex items-start">
        <svg class="w-5 h-5 text-indigo-500 dark:text-indigo-400 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
        </svg>
        <div class="flex-1">
            <h4 class="text-sm font-semibold text-indigo-900 dark:text-indigo-100 mb-2">Staff Management Guide</h4>
            <ul class="text-sm text-indigo-800 dark:text-indigo-200 space-y-1.5 list-disc list-inside">
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
            <p class="text-xs text-indigo-700 dark:text-indigo-300 mt-3 italic">💡 Tip: Assign permissions carefully. Staff members will only see and access features based on their assigned permissions. You can always update permissions later.</p>
        </div>
    </div>
</div>

<div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                  Manage Staff
                </h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Search and add staff as collapsible cards. Set roles and permissions
                </p>
            </div>
   <div class="grid grid-cols-1 gap-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search users</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
                </span>
                <input id="staff-search" type="text" placeholder="Search by name or email..." class="w-full pl-10 pr-10 py-3 bg-white text-gray-900 rounded-lg border-gray-300 dark:bg-gray-600 dark:text-gray-100 dark:border-emerald-500 dark:focus:border-emerald-400 dark:focus:ring-emerald-400 dark:ring-1 transition-colors duration-200" autocomplete="off" />
                <button type="button" id="staff-search-clear" class="absolute inset-y-0 right-0 px-3 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">Clear</button>
                <div id="staff-autocomplete" class="hidden absolute z-20 mt-1 w-full dark:bg-gray-600 dark:border-gray-500 rounded-lg shadow-xl max-h-64 overflow-auto"></div>
            </div>
            <div id="staff-search-results" class="hidden"></div>
        </div>
    </div>

    <div id="staff-cards" class="space-y-3"></div>
    
    <!-- Hidden owner info for JavaScript -->
    <div id="owner-info" data-owner-id="{{ $event->user->id ?? '' }}" data-owner-name="{{ trim(($event->user->first_name ?? '').' '.($event->user->last_name ?? '')) }}" data-owner-email="{{ $event->user->email ?? '' }}" class="hidden"></div>

    <!-- Staff Communication Settings -->
    <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-lg p-3 border border-emerald-200 dark:border-emerald-800">
        <h3 class="text-base font-semibold text-emerald-900 dark:text-emerald-100 mb-2">Staff Communication</h3>
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="staff_welcome_message">Staff Welcome Message</label>
                <textarea id="staff_welcome_message" name="staff_welcome_message" rows="2" 
                          class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-gray-300 dark:focus:border-emerald-400 dark:focus:ring-emerald-400 focus:ring-1 transition-colors duration-200" 
                          placeholder="Welcome message that will be sent to new staff members...">{{ $eventData['staff_settings']['staff_welcome_message'] ?? '' }}</textarea>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">This message will be sent to staff members when they're added to the event.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="staff_instructions">Staff Instructions</label>
                <textarea id="staff_instructions" name="staff_instructions" rows="3" 
                          class="w-full px-3 py-2 bg-white text-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-gray-300 dark:focus:border-emerald-400 dark:focus:ring-emerald-400 focus:ring-1 transition-colors duration-200" 
                          placeholder="Instructions and guidelines for staff members...">{{ $eventData['staff_settings']['staff_instructions'] ?? '' }}</textarea>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Detailed instructions that staff members will see in their dashboard.</p>
            </div>
        </div>
    </div>

    @php(
        $initialStaff = ($event->staff ?? collect())->map(function($s){
            // Check if user exists
            if (!$s->user) {
                return null;
            }
            
            $fullName = trim(($s->user->first_name ?? '') . ' ' . ($s->user->last_name ?? ''));
            return [
                'id' => $s->user_id,
                'name' => $fullName !== '' ? $fullName : ($s->user->email ?? null),
                'email' => $s->user->email ?? null,
                'role' => $s->role,
                'can_manage_event' => (bool)$s->can_manage_event,
                'can_manage_staff' => (bool)$s->can_manage_staff,
                'can_manage_ticket' => (bool)$s->can_manage_ticket,
                'can_manage_news' => (bool)$s->can_manage_news,
                'can_manage_attendance' => (bool)$s->can_manage_attendance,
                'can_manage_sales' => (bool)$s->can_manage_sales,
            ];
        })->filter()->values()->toArray()
    )
    <input type="hidden" id="staff_data_json" name="staff_data_json" value='@json($initialStaff)'>
    <input type="hidden" id="staff_settings_json" name="staff_settings_json" value='@json($eventData['staff_settings'] ?? ['staff_welcome_message' => null, 'staff_instructions' => null])'>

    <script src="{{ asset('js/events/step5.js') }}?v={{ time() }}"></script>
</div>


