@props(['event', 'details' => []])

<!-- Organizer Info Layout - Premium Professional Design -->
<div class="organizer-info-section py-8">
    <div class="px-4 sm:px-6 lg:px-8">
        @if(isset($details['title']) && $details['title'])
        <div class="mb-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-gray-100 mb-3 tracking-tight">{{ $details['title'] }}</h2>
            @if(isset($details['subtitle']) && $details['subtitle'])
            <p class="text-lg text-gray-900 dark:text-gray-400">{{ $details['subtitle'] }}</p>
            @endif
        </div>
        @endif
        
        <!-- Premium Card Design -->
        <div class="relative bg-gradient-to-br from-white via-white to-gray-50 dark:from-gray-800 dark:via-gray-850 dark:to-gray-900 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 p-8 shadow-xl backdrop-blur-sm overflow-hidden">
            <!-- Decorative Background Pattern -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-qricket-500/5 to-transparent rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-gradient-to-tr from-blue-500/5 to-transparent rounded-full blur-3xl"></div>
            
            <div class="relative z-10">
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                    <!-- Premium Avatar Design -->
                    <div class="flex-shrink-0 relative group">
                    @if(optional($event)->user_id === null)
                        <!-- Admin-created event -->
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-purple-400 to-purple-600 rounded-full blur-lg opacity-40 animate-pulse"></div>
                                <div class="relative w-20 h-20 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center text-white text-3xl font-bold shadow-2xl ring-4 ring-purple-200/50 dark:ring-purple-900/50">
                            A
                                </div>
                        </div>
                    @else
                        <!-- Regular user-created event -->
                        @if(optional($event->user))
                                <a href="{{ route('organizer.profile', optional($event->user)->id) }}?back={{ urlencode(route('events.show', $event->id)) }}" class="block group-hover:scale-105 transition-transform duration-300">
                                @if(optional($event->user)->profile_picture_url)
                                        <div class="relative">
                                            <div class="absolute inset-0 bg-gradient-to-br from-qricket-400 to-qricket-600 rounded-full blur-lg opacity-40 group-hover:opacity-60 transition-opacity duration-300"></div>
                                            <img class="relative w-20 h-20 rounded-full object-cover border-4 border-white dark:border-gray-700 shadow-2xl ring-4 ring-qricket-100 dark:ring-qricket-900 group-hover:ring-qricket-300 dark:group-hover:ring-qricket-700 transition-all duration-300" 
                                         src="{{ optional($event->user)->profile_picture_url }}" 
                                         alt="{{ optional($event->user)->full_name }}">
                                        </div>
                                @else
                                        <div class="relative">
                                            <div class="absolute inset-0 bg-gradient-to-br from-qricket-400 to-qricket-600 rounded-full blur-lg opacity-40 group-hover:opacity-60 transition-opacity duration-300"></div>
                                            <div class="relative w-20 h-20 bg-gradient-to-br from-qricket-500 to-qricket-600 rounded-full flex items-center justify-center text-white text-3xl font-bold shadow-2xl ring-4 ring-qricket-100 dark:ring-qricket-900 group-hover:ring-qricket-300 dark:group-hover:ring-qricket-700 transition-all duration-300">
                                        {{ strtoupper(substr(optional($event->user)->full_name ?? 'U', 0, 1)) }}
                                            </div>
                                    </div>
                                @endif
                            </a>
                        @endif
                    @endif
                </div>
                
                    <!-- Organizer Details - Enhanced -->
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-3 mb-3">
                            <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
                            @if(optional($event)->user_id === null)
                                    <span class="bg-gradient-to-r from-purple-600 to-purple-800 bg-clip-text text-transparent">Admin Event</span>
                            @else
                                @if(optional($event)->user)
                                    <a href="{{ route('organizer.profile', optional($event->user)->id) }}?back={{ urlencode(route('events.show', $event->id)) }}" 
                                       class="hover:text-qricket-500 dark:hover:text-qricket-400 transition-colors duration-200">
                                        {{ optional($event->user)->full_name }}
                                    </a>
                                @else
                                    {{ optional($event->user)->full_name ?? 'Unknown' }}
                                @endif
                            @endif
                        </h3>
                        @if(optional($event)->user && optional($event->user)->isVerifiedOrganizer())
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-green-500 via-emerald-500 to-green-600 text-white shadow-lg shadow-green-500/30">
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.3 3.3 0 001.07-.545l.075-.333c.001-.015.01-.048.05-.081a.81.81 0 01.59-.241c.6.034 1.15.239 1.593.584.433.344 1.105.976 1.86 1.731.676.677.812.812 1.17.812.36 0 .494-.135 1.17-.812.756-.755 1.427-1.387 1.86-1.731.443-.345.993-.55 1.593-.584a.812.812 0 01.59.241c.04.033.049.066.05.081l.074.333c.047.203.226.399.59.241C16.902 8.608 18 7.2 18 5.5 18 3.179 15.821 1 13.5 1c-1.279 0-2.554.69-3.431 1.594a4.819 4.819 0 00-.138.138C9.558 1.69 8.283 1 7.5 1 5.179 1 3 3.179 3 5.5c0 1.7 1.098 3.108 2.671 3.99.364.158.543-.038.59-.241l.075-.333z" clip-rule="evenodd"></path>
                                </svg>
                                Verified Organizer
                            </span>
                        @endif
                    </div>
                        
                        <!-- Role and Email Section -->
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 text-gray-900 dark:text-gray-300">
                                <svg class="w-4 h-4 text-qricket-500 dark:text-qricket-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-sm font-medium">
                        @if(optional($event)->user_id === null)
                            This event is created and managed by Qricket Admin
                        @else
                            Event Organizer
                        @endif
                                </span>
                            </div>
                            @if(optional($event)->user && optional($event->user)->email)
                            <div class="flex items-center gap-2 text-gray-900 dark:text-gray-400">
                                <svg class="w-4 h-4 text-gray-900 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <a href="mailto:{{ optional($event->user)->email }}" class="text-sm hover:text-qricket-500 dark:hover:text-qricket-400 transition-colors duration-200">
                                    {{ optional($event->user)->email }}
                                </a>
                            </div>
                            @endif
                        </div>
                </div>
            </div>
            
                <!-- Staff Members (Grouped by Role) - Premium Design -->
            @php
                $allStaff = optional($event)->staff ? $event->staff->filter(function($staff) {
                    return $staff->user !== null;
                }) : collect();
                
                // Group staff by role
                $staffByRole = $allStaff->groupBy('role');
                
                // Define role display order and labels
                $roleOrder = ['co-owner', 'manager', 'organizer', 'staff'];
                $roleLabels = [
                    'co-owner' => 'Co-Owners',
                    'manager' => 'Managers',
                    'organizer' => 'Organizers',
                    'staff' => 'Staff Members'
                ];
            @endphp
            @if(method_exists($event, 'staff') && $allStaff->count() > 0)
                <div class="mt-8 pt-8 border-t border-gray-200/80 dark:border-gray-700/80">
                    <div class="flex items-center gap-2 mb-6">
                        <svg class="w-5 h-5 text-qricket-500 dark:text-qricket-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-gray-100">Event Staff</h4>
                    </div>
                    
                    @foreach($roleOrder as $role)
                        @if($staffByRole->has($role) && $staffByRole->get($role)->count() > 0)
                            <div class="mb-6 last:mb-0">
                                <h5 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                    {{ $roleLabels[$role] ?? ucfirst(str_replace('-', ' ', $role)) }}
                                </h5>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($staffByRole->get($role) as $staff)
                        @if($staff->user)
                            <a href="{{ route('organizer.profile', $staff->user->id) }}?back={{ urlencode(route('events.show', $event->id)) }}" 
                                   class="group flex items-center gap-3 bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-850 rounded-xl border border-gray-200/60 dark:border-gray-700/60 px-4 py-3 hover:shadow-lg hover:border-qricket-300 dark:hover:border-qricket-700 transition-all duration-300">
                                    <!-- Staff Avatar -->
                                    <div class="relative flex-shrink-0">
                                @if($staff->user->profile_picture_url)
                                            <div class="relative">
                                                <div class="absolute inset-0 bg-gradient-to-br from-qricket-400 to-qricket-600 rounded-full blur opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>
                                                <img class="relative w-10 h-10 rounded-full object-cover border-2 border-white dark:border-gray-700 shadow-md ring-1 ring-gray-200 dark:ring-gray-600" 
                                         src="{{ $staff->user->profile_picture_url }}" 
                                         alt="{{ $staff->user->full_name }}">
                                            </div>
                                @else
                                            <div class="relative">
                                                <div class="absolute inset-0 bg-gradient-to-br from-qricket-400 to-qricket-600 rounded-full blur opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>
                                                <div class="relative w-10 h-10 bg-gradient-to-br from-qricket-500 to-qricket-600 rounded-full flex items-center justify-center text-white text-sm font-bold shadow-md ring-1 ring-gray-200 dark:ring-gray-600">
                                        {{ strtoupper(substr($staff->user->full_name ?? 'S', 0, 1)) }}
                                                </div>
                                    </div>
                                @endif
                                    </div>
                                    
                                    <!-- Staff Info -->
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white group-hover:text-qricket-500 dark:group-hover:text-qricket-400 transition-colors duration-200 truncate">
                                            {{ $staff->user->full_name }}
                                        </p>
                                                    @if($staff->user->email)
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">
                                                            {{ $staff->user->email }}
                                            </p>
                                    @endif
                                </div>
                                    
                                    <!-- Arrow Icon -->
                                                <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 group-hover:text-qricket-500 dark:group-hover:text-qricket-400 group-hover:translate-x-1 transition-all duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                            </a>
                        @endif
                    @endforeach
                </div>
                            </div>
                        @endif
                    @endforeach
                    
                    <!-- Show any staff with roles not in the predefined order -->
                    @foreach($staffByRole as $role => $staffGroup)
                        @if(!in_array($role, $roleOrder) && $staffGroup->count() > 0)
                            <div class="mb-6 last:mb-0">
                                <h5 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                    {{ ucfirst(str_replace('-', ' ', $role)) }}
                                </h5>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($staffGroup as $staff)
                                        @if($staff->user)
                                            <a href="{{ route('organizer.profile', $staff->user->id) }}?back={{ urlencode(route('events.show', $event->id)) }}" 
                                                   class="group flex items-center gap-3 bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-850 rounded-xl border border-gray-200/60 dark:border-gray-700/60 px-4 py-3 hover:shadow-lg hover:border-qricket-300 dark:hover:border-qricket-700 transition-all duration-300">
                                                <!-- Staff Avatar -->
                                                <div class="relative flex-shrink-0">
                                            @if($staff->user->profile_picture_url)
                                                        <div class="relative">
                                                            <div class="absolute inset-0 bg-gradient-to-br from-qricket-400 to-qricket-600 rounded-full blur opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>
                                                            <img class="relative w-10 h-10 rounded-full object-cover border-2 border-white dark:border-gray-700 shadow-md ring-1 ring-gray-200 dark:ring-gray-600" 
                                                     src="{{ $staff->user->profile_picture_url }}" 
                                                     alt="{{ $staff->user->full_name }}">
                                                        </div>
                                            @else
                                                        <div class="relative">
                                                            <div class="absolute inset-0 bg-gradient-to-br from-qricket-400 to-qricket-600 rounded-full blur opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>
                                                            <div class="relative w-10 h-10 bg-gradient-to-br from-qricket-500 to-qricket-600 rounded-full flex items-center justify-center text-white text-sm font-bold shadow-md ring-1 ring-gray-200 dark:ring-gray-600">
                                                    {{ strtoupper(substr($staff->user->full_name ?? 'S', 0, 1)) }}
                                                            </div>
                                                </div>
                                            @endif
                                                </div>
                                                
                                                <!-- Staff Info -->
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-semibold text-gray-900 dark:text-white group-hover:text-qricket-500 dark:group-hover:text-qricket-400 transition-colors duration-200 truncate">
                                                        {{ $staff->user->full_name }}
                                                    </p>
                                                    @if($staff->user->email)
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">
                                                            {{ $staff->user->email }}
                                                        </p>
                                                    @endif
                                                </div>
                                                
                                                <!-- Arrow Icon -->
                                                <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 group-hover:text-qricket-500 dark:group-hover:text-qricket-400 group-hover:translate-x-1 transition-all duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
            </div>
            @endif
            </div>
        </div>
    </div>
</div>
