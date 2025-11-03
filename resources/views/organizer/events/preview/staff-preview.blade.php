@props([
    'staffData' => [],
    'event' => null
])

@php
    $hasStaff = !empty($staffData) && !empty($staffData['members']);
    $staffMembers = $hasStaff ? $staffData['members'] : [];
@endphp

<div class="space-y-6">
    @if($hasStaff)
        <!-- Staff Members Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($staffMembers as $index => $staff)
                @php
                    $staffName = $staff['name'] ?? ($staff['email'] ?? 'Member');
                    $staffEmail = $staff['email'] ?? '';
                    $staffRole = $staff['role'] ?? 'staff';
                    
                    // Define role colors
                    $roleColors = [
                        'owner' => ['bg' => 'bg-purple-100 dark:bg-purple-900/30', 'text' => 'text-purple-700 dark:text-purple-300', 'border' => 'border-purple-200 dark:border-purple-700'],
                        'co-owner' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-300', 'border' => 'border-blue-200 dark:border-blue-700'],
                        'manager' => ['bg' => 'bg-green-100 dark:bg-green-900/30', 'text' => 'text-green-700 dark:text-green-300', 'border' => 'border-green-200 dark:border-green-700'],
                        'staff' => ['bg' => 'bg-gray-100 dark:bg-gray-700/50', 'text' => 'text-gray-700 dark:text-gray-300', 'border' => 'border-gray-200 dark:border-gray-600'],
                    ];
                    
                    $roleColor = $roleColors[$staffRole] ?? $roleColors['staff'];
                @endphp
                
                <!-- Staff Member Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-start space-x-3">
                        <!-- Avatar -->
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-qricket-500 flex items-center justify-center text-white font-semibold text-sm">
                                {{ strtoupper(substr($staffName, 0, 1)) }}
                            </div>
                        </div>
                        
                        <!-- Staff Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                    {{ $staffName }}
                                </h4>
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $roleColor['bg'] }} {{ $roleColor['text'] }} {{ $roleColor['border'] }} border">
                                    {{ ucfirst($staffRole) }}
                                </span>
                            </div>
                            
                            @if($staffEmail)
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                    {{ $staffEmail }}
                                </p>
                            @endif
                            
                            <!-- Permissions -->
                            @if(isset($staff['permissions']) && is_array($staff['permissions']))
                                <div class="mt-2">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($staff['permissions'] as $permission => $enabled)
                                            @if($enabled)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                                                    {{ str_replace('can_', '', str_replace('_', ' ', $permission)) }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Staff Settings Summary -->
        @if(isset($staffData['settings']))
            <div class="bg-teal-50 dark:bg-teal-900/20 rounded-lg p-4 border border-teal-200 dark:border-teal-800">
                <h4 class="text-sm font-semibold text-teal-900 dark:text-teal-100 mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Staff Settings
                </h4>
                <div class="space-y-2 text-sm">
                    @if(isset($staffData['settings']['staff_welcome_message']) && !empty($staffData['settings']['staff_welcome_message']))
                        <div>
                            <span class="text-teal-700 dark:text-teal-300 font-medium">Welcome Message:</span>
                            <p class="text-teal-900 dark:text-teal-100 mt-1 text-xs bg-white dark:bg-gray-800 p-2 rounded border">
                                {{ $staffData['settings']['staff_welcome_message'] }}
                            </p>
                        </div>
                    @endif
                    @if(isset($staffData['settings']['staff_instructions']) && !empty($staffData['settings']['staff_instructions']))
                        <div>
                            <span class="text-teal-700 dark:text-teal-300 font-medium">Instructions:</span>
                            <p class="text-teal-900 dark:text-teal-100 mt-1 text-xs bg-white dark:bg-gray-800 p-2 rounded border">
                                {{ $staffData['settings']['staff_instructions'] }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">No staff added</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">This event doesn't have any staff members assigned yet.</p>
        </div>
    @endif
</div>
