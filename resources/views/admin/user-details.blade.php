<x-admin-layout title="User Details - {{ $user->full_name }}" page-title="User Details">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">User Details</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">View detailed information and activity for {{ $user->full_name }}.</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.users') }}" class="inline-flex items-center px-3 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium rounded-md transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Users
                </a>
                
                @if($user->status === \App\Models\User::STATUS_DELETED || $user->deleted_at)
                    <!-- Restore Button -->
                    <form method="POST" action="{{ route('admin.users.restore', $user) }}" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-md transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Restore Account
                        </button>
                    </form>
                @elseif($user->status === \App\Models\User::STATUS_BANNED || $user->banned_at)
                    <!-- Unban Button -->
                    <form method="POST" action="{{ route('admin.users.unban', $user) }}" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-md transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Unban User
                        </button>
                    </form>
                @elseif($user->status === \App\Models\User::STATUS_ACTIVE)
                    <!-- Ban Button -->
                    <button onclick="openBanModal()" type="button" class="inline-flex items-center px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-md transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                        </svg>
                        Ban User
                    </button>
                    
                    <!-- Delete Button -->
                    <form method="POST" action="{{ route('admin.users.delete', $user) }}" class="inline">
                        @csrf
                        <button type="submit" onclick="event.preventDefault(); if (typeof Swal !== 'undefined' && window.Swal) { Swal.fire({ title: 'Delete User?', text: 'Are you sure you want to delete this user? This will schedule their account for deletion.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, delete', cancelButtonText: 'Cancel', confirmButtonColor: '#dc2626', cancelButtonColor: '#6b7280' }).then((result) => { if (result.isConfirmed) { event.target.closest('form').submit(); } }); } else { if (confirm('Are you sure you want to delete this user? This will schedule their account for deletion.')) { event.target.closest('form').submit(); } } return false;" class="inline-flex items-center px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-md transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete Account
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- User Information Card -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <!-- Subscription Badge - Upper Right -->
                        <div class="flex justify-end mb-4">
                            @if($currentSubscription)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-qricket-100 text-qricket-800 dark:bg-qricket-900 dark:text-qricket-200">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ strtoupper($currentSubscription->plan_id) }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                    No subscription
                                </span>
                            @endif
                        </div>

                        <div class="text-center">
                            <!-- Profile Picture -->
                            <div class="mx-auto mb-4">
                                @if($user->profile_picture)
                                    <img class="h-24 w-24 rounded-full object-cover border-4 border-gray-200 dark:border-gray-600 mx-auto" 
                                         src="{{ $user->profile_picture_url }}" 
                                         alt="{{ $user->full_name }}'s profile picture"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    @php
                                        $name = $user->full_name;
                                        $initials = strtoupper(substr($name, 0, 1) . (str_contains($name, ' ') ? substr($name, strpos($name, ' ') + 1, 1) : ''));
                                        $colors = ['bg-qricket-500', 'bg-qricket-600', 'bg-qricket-500', 'bg-qricket-600', 'bg-qricket-500', 'bg-qricket-600', 'bg-qricket-500'];
                                        $colorIndex = crc32($name) % count($colors);
                                        $bgColor = $colors[$colorIndex];
                                    @endphp
                                    <div class="h-24 w-24 rounded-full overflow-hidden {{ $bgColor }} flex items-center justify-center text-white font-medium text-2xl mx-auto" style="display: none;">
                                        {{ $initials }}
                                    </div>
                                @else
                                    @php
                                        $name = $user->full_name;
                                        $initials = strtoupper(substr($name, 0, 1) . (str_contains($name, ' ') ? substr($name, strpos($name, ' ') + 1, 1) : ''));
                                        $colors = ['bg-qricket-500', 'bg-qricket-600', 'bg-qricket-500', 'bg-qricket-600', 'bg-qricket-500', 'bg-qricket-600', 'bg-qricket-500'];
                                        $colorIndex = crc32($name) % count($colors);
                                        $bgColor = $colors[$colorIndex];
                                    @endphp
                                    <div class="h-24 w-24 rounded-full overflow-hidden {{ $bgColor }} flex items-center justify-center text-white font-medium text-2xl mx-auto">
                                        {{ $initials }}
                                    </div>
                                @endif
                            </div>
                            
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ $user->full_name }}</h2>
                            <p class="text-gray-600 dark:text-gray-400 mb-4">{{ $user->email }}</p>
                            
                            <!-- User Status Badges -->
                            <div class="flex flex-wrap justify-center gap-2 mb-4">
                                @if($user->isVerifiedOrganizer())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Verified Organizer
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                        Regular User
                                    </span>
                                @endif
                                
                                @if($user->email_verified_at)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Email Verified
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        Email Not Verified
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <!-- User Details -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">User ID</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $user->id }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $user->first_name }} {{ $user->last_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $user->email }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Joined</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $user->created_at->format('M j, Y \a\t g:i A') }}</dd>
                                </div>
                                @if($userStats['last_login'])
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Login</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $userStats['last_login']->format('M j, Y \a\t g:i A') }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                    
                    <!-- User Statistics Card -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 mt-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Statistics</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-qricket-600 dark:text-qricket-400">{{ $userStats['total_logins'] }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Total Logins</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-qricket-600 dark:text-qricket-400">{{ $userStats['verification_requests'] }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Verification Requests</div>
                            </div>
                            
                            <div class="text-center">
                                <div class="text-2xl font-bold text-qricket-600 dark:text-qricket-400">{{ $user->activityLogs->count() }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Total Activities</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-qricket-600 dark:text-qricket-400">{{ $userStats['created_events'] }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Events Created</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-qricket-600 dark:text-qricket-400">{{ $userStats['participated_events'] }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Events Participated</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-qricket-600 dark:text-qricket-400">{{ $userStats['purchases'] }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Purchases</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Activity Logs and Tabs -->
                <div class="lg:col-span-2">
                    <!-- Compact Stats Header (Created/Participated/Purchases) -->
                    <div class="mb-6">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-6 h-6 bg-blue-500 rounded flex items-center justify-center mr-3">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Event Overview</h3>
                                    </div>
                                    <div class="flex space-x-6">
                                        <div class="text-center">
                                            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $userStats['created_events'] }}</div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">Created</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-lg font-semibold text-blue-600 dark:text-blue-400">{{ $userStats['participated_events'] }}</div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">Participated</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-lg font-semibold text-purple-600 dark:text-purple-400">{{ $userStats['purchases'] }}</div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">Purchases</div>
                                        </div>
                        </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Money-Related Cards Row (Top) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                        <!-- Total Revenue -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Revenue</p>
                                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format($salesStats['totalRevenue'] ?? 0, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Total Spent (changed from This Month) -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Spent</p>
                                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format($salesStats['thisMonthRevenue'] ?? 0, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pending Payments -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Payments</p>
                                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format($salesStats['pendingRevenue'] ?? 0, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Activity & Participation Cards Row (Bottom) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                        <!-- Participated -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-1">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Participated</h4>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">{{ $userStats['participated_events'] }}</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Events where this user was part of the team.</p>
                            </div>
                        </div>
                        
                        <!-- Tickets Bought -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Tickets Bought</p>
                                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($salesStats['totalTicketsSold'] ?? 0) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Purchases -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-1">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Purchases</h4>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">{{ $userStats['purchases'] }}</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total purchase records for this user.</p>
                            </div>
                        </div>
                    </div>
                    
                    
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex space-x-6">
                                <button class="text-sm font-medium user-tab-btn text-qricket-600 dark:text-qricket-400" data-target="panel-activity">Recent Activity</button>
                                <button class="text-sm font-medium user-tab-btn text-gray-600 dark:text-gray-400" data-target="panel-participated">Event Participated</button>
                                <button class="text-sm font-medium user-tab-btn text-gray-600 dark:text-gray-400" data-target="panel-created">Event List</button>
                                <button class="text-sm font-medium user-tab-btn text-gray-600 dark:text-gray-400" data-target="panel-purchases">Purchase History</button>
                                <button class="text-sm font-medium user-tab-btn text-gray-600 dark:text-gray-400" data-target="panel-verification">Organizer Verification</button>
                            </div>
                        </div>
                        <!-- Activity Tab -->
                        <div class="user-tab" id="panel-activity">
                            @include('admin.user-details.partials.activity')
                        </div>
                        <!-- Participated Tab -->
                        <div class="px-6 py-4 user-tab hidden" id="panel-participated">
                            @include('admin.user-details.partials.participated-events')
                        </div>
                        <!-- Created Tab -->
                        <div class="px-6 py-4 user-tab hidden" id="panel-created">
                            @include('admin.user-details.partials.created-events')
                        </div>
                        <!-- Purchases Tab -->
                        <div class="px-6 py-4 user-tab hidden" id="panel-purchases">
                            @include('admin.user-details.partials.purchase-history')
                                        </div>
                        <!-- Organizer Verification Tab -->
                        <div class="px-6 py-4 user-tab hidden" id="panel-verification">
                            @include('admin.user-details.partials.verification')
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function(){
            const buttons = document.querySelectorAll('.user-tab-btn');
            const panels = document.querySelectorAll('.user-tab');
            const overview = document.getElementById('overview-card');
            function setActive(targetId){
                panels.forEach(p => p.classList.add('hidden'));
                const active = document.getElementById(targetId);
                if(active){ active.classList.remove('hidden'); }
                // style buttons
                buttons.forEach(b => {
                    if (b.getAttribute('data-target') === targetId) {
                        b.classList.remove('text-gray-600','dark:text-gray-400');
                        b.classList.add('text-qricket-600','dark:text-qricket-400');
                    } else {
                        b.classList.add('text-gray-600','dark:text-gray-400');
                        b.classList.remove('text-qricket-600','dark:text-qricket-400');
                    }
                });
                // overview always visible
                if (overview) { overview.style.display = ''; }
            }
            buttons.forEach(b => b.addEventListener('click', () => setActive(b.getAttribute('data-target'))));
            // init
            setActive('panel-activity');
        })();
    </script>

    <!-- Ban User Modal -->
    <div id="banModal" class="hidden fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto max-w-md w-full">
            <div class="bg-white dark:bg-gray-800 shadow-xl rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-red-500 to-red-600 rounded-t-xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                            </svg>
                            <h3 class="text-xl font-semibold text-white">Ban User</h3>
                        </div>
                        <button onclick="closeBanModal()" class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-1 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="px-6 py-5">
                    <form id="banForm" method="POST" action="{{ route('admin.users.ban', $user) }}">
                        @csrf
                        <div class="mb-4">
                            <label for="banned_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reason for Ban (Optional)</label>
                            <textarea id="banned_reason" name="banned_reason" rows="4" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" placeholder="Enter reason for banning this user..."></textarea>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">This reason will be visible to the user when they try to log in.</p>
                        </div>
                        <div class="mb-6">
                            <label for="banned_until" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ban Duration (Optional)</label>
                            <input type="datetime-local" id="banned_until" name="banned_until" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" />
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Set an end date/time to automatically lift the ban.</p>
                        </div>
                        
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeBanModal()" class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm hover:shadow-md transition-all">
                                Ban User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openBanModal() {
            document.getElementById('banModal').classList.remove('hidden');
        }

        function closeBanModal() {
            document.getElementById('banModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('banModal').addEventListener('click', function(e) {
            if (e.target === this || e.target.classList.contains('bg-black')) {
                closeBanModal();
            }
        });
    </script>
</x-admin-layout>
