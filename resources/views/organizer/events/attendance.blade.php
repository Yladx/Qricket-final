<x-unified-layout :layout-type="$layoutType" :title="'Attendance Tracking - ' . $event->title" :page-title="'Attendance'">
    <x-slot name="header">
        <x-manage-header :event="$event" />
    </x-slot>
    
    <div class="py-4">
        <div class="max-w-6xl mx-auto sm:px-4 lg:px-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 text-gray-900 dark:text-gray-100">

                    <!-- Header with Attendance Label and Action Buttons -->
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Attendance</h2>
                        </div>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-1 sm:space-y-0 sm:space-x-3">
                            <a href="{{ route($layoutType === 'admin' ? 'admin.event.attendance.export' : 'organizer.events.attendance.export', $event) }}" 
                               class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white rounded-md transition-colors flex items-center text-xs font-medium">
                                <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Export XLS
                            </a>
                            @php($eventEndedTop = optional($event->end_date)->isPast())
                            @if($eventEndedTop && $certificateEnabled)
                            <button type="button" onclick="generateCertificates()" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md transition-colors flex items-center text-xs font-medium">
                                <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Generate Certificates
                            </button>
                            @endif
                        </div>
                    </div>

                    <!-- Attendance Stats -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md p-3">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-6 h-6 bg-blue-500 rounded-md flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-xs font-medium text-blue-600 dark:text-blue-400">Total Tickets</p>
                                    <p class="text-lg font-semibold text-blue-900 dark:text-blue-100" data-stat="totalTickets">{{ $totalTickets ?? 0 }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md p-3">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-6 h-6 bg-green-500 rounded-md flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-xs font-medium text-green-600 dark:text-green-400">Checked In</p>
                                    <p class="text-lg font-semibold text-green-900 dark:text-green-100" data-stat="checkedInTickets">{{ $checkedInTickets ?? 0 }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-md p-3">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-6 h-6 bg-yellow-500 rounded-md flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-xs font-medium text-yellow-600 dark:text-yellow-400">Pending</p>
                                    <p class="text-lg font-semibold text-yellow-900 dark:text-yellow-100" data-stat="pendingTickets">{{ ($totalTickets ?? 0) - ($checkedInTickets ?? 0) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-md p-3">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-6 h-6 bg-purple-500 rounded-md flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-xs font-medium text-purple-600 dark:text-purple-400">Attendance Rate</p>
                                    <p class="text-lg font-semibold text-purple-900 dark:text-purple-100" data-stat="attendanceRate">{{ round($attendanceRate ?? 0, 1) }}%</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Analytics Graphs -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <!-- Peak Attendance Chart -->
                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-md p-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Peak Attendance</h3>
                                <span class="text-xs text-gray-500 dark:text-gray-400">By Date & Time</span>
                            </div>
                            @if(!empty($peakAttendanceData['data']) && count($peakAttendanceData['data']) > 0)
                                <div class="h-64">
                                    <canvas id="peakAttendanceChart"></canvas>
                                </div>
                            @else
                                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                    <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    <p class="text-sm font-medium">No check-in data yet</p>
                                    <p class="text-xs mt-1">Peak attendance will appear after attendees check in</p>
                                </div>
                            @endif
                        </div>

                        <!-- Audience Behavior Roadmap Chart -->
                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-md p-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Audience Behavior Roadmap</h3>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Timeline vs Event Start</span>
                            </div>
                            @if(!empty($audienceBehaviorData['checkin_counts']) && array_sum($audienceBehaviorData['checkin_counts']) > 0)
                                <div class="mb-2">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                                        Event Start: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $audienceBehaviorData['event_start_label'] ?? 'N/A' }}</span>
                                    </p>
                                </div>
                                <div class="h-64">
                                    <canvas id="audienceBehaviorChart"></canvas>
                                </div>
                            @else
                                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                    <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="text-sm font-medium">No check-in data yet</p>
                                    <p class="text-xs mt-1">Behavior roadmap will appear after attendees check in</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Ticket List -->
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-md p-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Ticket List</h3>
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 w-full sm:w-auto">
                                <select id="status-filter" class="px-2 py-1.5 text-sm bg-white text-gray-900 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-qricket-500 dark:bg-gray-700 dark:text-white">
                                    <option value="all">All Status</option>
                                    <option value="checked-in">Checked In</option>
                                    <option value="pending">Pending</option>
                                </select>
                                <select id="ticket-type-filter" class="px-2 py-1.5 text-sm bg-white text-gray-900 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-qricket-500 dark:bg-gray-700 dark:text-white">
                                    <option value="all">All Ticket Types</option>
                                    @foreach($ticketTypes ?? [] as $ticketType)
                                        <option value="{{ $ticketType }}">{{ $ticketType }}</option>
                                    @endforeach
                                </select>
                                <input type="text" id="search-tickets" placeholder="Search tickets..." class="px-2 py-1.5 text-sm bg-white text-gray-900 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-qricket-500 dark:bg-gray-700 dark:text-white">
                            </div>
                        </div>
                        
                        <!-- Desktop Table View -->
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-48">User</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-32">Type</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-20">ID</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-28">Date</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-24">Status</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-28">Check-in</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-24">Certificate</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-20">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="ticket-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($tickets ?? [] as $ticket)
                                        <tr class="ticket-row" data-status="{{ $ticket['checked_in'] ? 'checked-in' : 'pending' }}" data-ticket-type="{{ $ticket['ticket_type'] }}">
                                            <td class="px-3 py-3">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8">
                                                        @if(isset($ticket['user_profile_picture']) && $ticket['user_profile_picture'])
                                                            <img class="h-8 w-8 rounded-full object-cover" src="{{ $ticket['user_profile_picture'] }}" alt="{{ $ticket['user_name'] }}" loading="lazy">
                                                        @else
                                                            <div class="h-8 w-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                                                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                                </svg>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="ml-3 min-w-0">
                                                        <div class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $ticket['user_name'] }}</div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $ticket['user_email'] }}</div>
                                                        <div class="text-xs text-gray-400 truncate">{{ substr($ticket['purchase_id'], -8) }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-3 py-3">
                                                <div class="text-sm text-gray-900 dark:text-white truncate" title="{{ $ticket['ticket_type'] }}">{{ $ticket['ticket_type'] }}</div>
                                            </td>
                                            <td class="px-3 py-3">
                                                <div class="text-sm text-gray-900 dark:text-white">#{{ $ticket['ticket_id'] }}</div>
                                            </td>
                                            <td class="px-3 py-3">
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $ticket['purchase_date'] ? $ticket['purchase_date']->format('M j, Y') : '-' }}
                                                </div>
                                                <div class="text-xs text-gray-400">
                                                    {{ $ticket['purchase_date'] ? $ticket['purchase_date']->format('g:i A') : '' }}
                                                </div>
                                            </td>
                                            <td class="px-3 py-3">
                                                @php($eventEnded = optional($event->end_date)->isPast())
                                                @if($ticket['checked_in'])
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">✓</span>
                                                @else
                                                    @if($eventEnded)
                                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Unattended</span>
                                                @else
                                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Pending</span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td class="px-3 py-3">
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $ticket['checked_in_at'] ? $ticket['checked_in_at']->format('M j') : '-' }}
                                                </div>
                                                <div class="text-xs text-gray-400">
                                                    {{ $ticket['checked_in_at'] ? $ticket['checked_in_at']->format('g:i A') : '' }}
                                                </div>
                                            </td>
                                            <td class="px-3 py-3">
                                                @if(isset($ticket['certificate_given']) && $ticket['certificate_given'])
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">✓ Sent</span>
                                                @elseif($ticket['checked_in'])
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Pending</span>
                                                @else
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">Not Checked In</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-3">
                                                @if($ticket['checked_in'])
                                                    <span class="text-green-600 dark:text-green-400 text-xs">Done</span>
                                                @else
                                                <form method="POST" action="{{ route('organizer.events.attendance.manual-checkin', $event) }}" onsubmit="return confirmCheckIn(this, event);">
                                                    @csrf
                                                    <input type="hidden" name="ticket_id" value="{{ $ticket['id'] }}">
                                                    <button type="submit" class="text-qricket-600 hover:text-qricket-900 dark:text-qricket-400 dark:hover:text-qricket-300 text-xs font-medium" title="Check in this attendee">Check In</button>
                                                </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                                No tickets found for this event.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
    </div>

                        <!-- Mobile Card View -->
                        <div class="md:hidden space-y-4" id="ticket-cards-container">
                            @forelse($tickets ?? [] as $ticket)
                                <div class="ticket-card bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg p-4" data-status="{{ $ticket['checked_in'] ? 'checked-in' : 'pending' }}" data-ticket-type="{{ $ticket['ticket_type'] }}" data-user-name="{{ strtolower($ticket['user_name']) }}" data-user-email="{{ strtolower($ticket['user_email']) }}">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $ticket['user_name'] }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $ticket['user_email'] }}</div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            @if($ticket['checked_in'])
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">✓</span>
                                            @else
                                                @php($eventEnded = optional($event->end_date)->isPast())
                                                @if($eventEnded)
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Unattended</span>
                                                @else
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Pending</span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-4 text-xs text-gray-500 dark:text-gray-400 mb-3">
                                        <div>
                                            <span class="font-medium">Type:</span> {{ $ticket['ticket_type'] }}
                                        </div>
                                        <div>
                                            <span class="font-medium">ID:</span> #{{ $ticket['ticket_id'] }}
                                        </div>
                                        <div>
                                            <span class="font-medium">Date:</span> {{ $ticket['purchase_date'] ? $ticket['purchase_date']->format('M j, Y') : '-' }}
                                        </div>
                                        <div>
                                            <span class="font-medium">Check-in:</span> {{ $ticket['checked_in_at'] ? $ticket['checked_in_at']->format('M j, g:i A') : '-' }}
                                        </div>
                                    </div>
                                    
                                    <div class="flex justify-end">
                                        @if($ticket['checked_in'])
                                            <span class="text-green-600 dark:text-green-400 text-xs">Already checked in</span>
                                        @else
                                            <form method="POST" action="{{ route('organizer.events.attendance.manual-checkin', $event) }}" onsubmit="return confirmCheckIn(this, event);">
                                                @csrf
                                                <input type="hidden" name="ticket_id" value="{{ $ticket['id'] }}">
                                                <button type="submit" class="text-qricket-600 hover:text-qricket-900 dark:text-qricket-400 dark:hover:text-qricket-300 text-xs font-medium">Check In</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                    <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 6v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-6V7a2 2 0 00-2-2H5z"></path>
                                    </svg>
                                    <p class="text-sm font-medium">No tickets found for this event</p>
                                    <p class="text-xs mt-1">Tickets will appear here once they are purchased</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Initialize Peak Attendance Chart
        @if(!empty($peakAttendanceData['data']) && count($peakAttendanceData['data']) > 0)
        document.addEventListener('DOMContentLoaded', function() {
            const peakCtx = document.getElementById('peakAttendanceChart');
            if (peakCtx) {
                new Chart(peakCtx, {
                    type: 'line',
                    data: {
                        labels: @json($peakAttendanceData['labels'] ?? []),
                        datasets: [{
                            label: 'Check-ins',
                            data: @json($peakAttendanceData['data'] ?? []),
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#3b82f6',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    font: {
                                        size: 12
                                    },
                                    color: '#6b7280'
                                }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                titleFont: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 12
                                },
                                callbacks: {
                                    label: function(context) {
                                        return context.parsed.y + ' check-in' + (context.parsed.y !== 1 ? 's' : '');
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    color: '#6b7280',
                                    font: {
                                        size: 11
                                    }
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                },
                                title: {
                                    display: true,
                                    text: 'Number of Check-ins',
                                    color: '#6b7280',
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            x: {
                                ticks: {
                                    color: '#6b7280',
                                    font: {
                                        size: 10
                                    },
                                    maxRotation: 45,
                                    minRotation: 45
                                },
                                grid: {
                                    display: false
                                }
                            }
                        },
                        interaction: {
                            mode: 'nearest',
                            axis: 'x',
                            intersect: false
                        }
                    }
                });
            }
        });
        @endif

        // Initialize Audience Behavior Roadmap Chart
        @if(!empty($audienceBehaviorData['checkin_counts']) && array_sum($audienceBehaviorData['checkin_counts']) > 0)
        document.addEventListener('DOMContentLoaded', function() {
            const behaviorCtx = document.getElementById('audienceBehaviorChart');
            if (behaviorCtx) {
                const labels = @json($audienceBehaviorData['labels'] ?? []);
                const checkinCounts = @json($audienceBehaviorData['checkin_counts'] ?? []);
                
                // Find the index of "Event Start" to mark it on the chart
                const eventStartIndex = labels.indexOf('Event Start');
                
                new Chart(behaviorCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Check-ins',
                            data: checkinCounts,
                            backgroundColor: checkinCounts.map((count, index) => {
                                if (index === eventStartIndex) {
                                    return 'rgba(239, 68, 68, 0.9)'; // Red for event start
                                } else if (index < eventStartIndex) {
                                    return 'rgba(34, 197, 94, 0.7)'; // Green for before event
                                } else {
                                    return 'rgba(251, 191, 36, 0.7)'; // Yellow for after event
                                }
                            }),
                            borderColor: checkinCounts.map((count, index) => {
                                if (index === eventStartIndex) {
                                    return '#ef4444';
                                } else if (index < eventStartIndex) {
                                    return '#22c55e';
                                } else {
                                    return '#fbbf24';
                                }
                            }),
                            borderWidth: 1,
                            borderRadius: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                titleFont: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 12
                                },
                                callbacks: {
                                    title: function(context) {
                                        const index = context[0].dataIndex;
                                        const label = labels[index];
                                        if (label === 'Event Start') {
                                            return 'Event Start Time';
                                        }
                                        return label + ' from event start';
                                    },
                                    label: function(context) {
                                        const count = context.parsed.y;
                                        return count + ' check-in' + (count !== 1 ? 's' : '');
                                    }
                                }
                            },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    color: '#6b7280',
                                    font: {
                                        size: 11
                                    }
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                },
                                title: {
                                    display: true,
                                    text: 'Number of Check-ins',
                                    color: '#6b7280',
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            x: {
                                ticks: {
                                    color: '#6b7280',
                                    font: {
                                        size: 10
                                    },
                                    maxRotation: 45,
                                    minRotation: 45,
                                    callback: function(value, index) {
                                        const label = labels[index];
                                        if (label === 'Event Start') {
                                            return 'START';
                                        }
                                        return label;
                                    }
                                },
                                grid: {
                                    display: false
                                },
                                title: {
                                    display: true,
                                    text: 'Time Relative to Event Start',
                                    color: '#6b7280',
                                    font: {
                                        size: 12
                                    }
                                }
                            }
                        },
                        interaction: {
                            mode: 'index',
                            intersect: false
                        }
                    }
                });
            }
        });
        @endif

        function confirmCheckIn(form, event) {
            event.preventDefault();
            
            Swal.fire({
                title: 'Confirm Check-in',
                text: 'Are you sure you want to check in this attendee?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, check in',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        function generateCertificates() {
            Swal.fire({
                title: 'Generate Certificates',
                text: 'Generate certificates for all checked-in attendees?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, generate',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('{{ route("organizer.events.attendance.certs", $event) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Certificates generated successfully!',
                                showConfirmButton: false,
                                timer: 2000
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Unknown error occurred',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error generating certificates. Please try again.',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    });
                }
            });
        }

        // Filter functionality
        function applyFilters() {
            const statusFilter = document.getElementById('status-filter').value;
            const ticketTypeFilter = document.getElementById('ticket-type-filter').value;
            const searchTerm = document.getElementById('search-tickets').value.toLowerCase();
            
            // Filter desktop table rows
            const rows = document.querySelectorAll('.ticket-row');
            rows.forEach(row => {
                const status = row.dataset.status;
                const ticketType = row.dataset.ticketType;
                const text = row.textContent.toLowerCase();
                
                // Check status filter
                const statusMatch = statusFilter === 'all' || status === statusFilter;
                
                // Check ticket type filter
                const ticketTypeMatch = ticketTypeFilter === 'all' || ticketType === ticketTypeFilter;
                
                // Check search term
                const searchMatch = searchTerm === '' || text.includes(searchTerm);
                
                // Show row only if all filters match
                if (statusMatch && ticketTypeMatch && searchMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Filter mobile card views
            const cards = document.querySelectorAll('.ticket-card');
            cards.forEach(card => {
                const status = card.dataset.status;
                const ticketType = card.dataset.ticketType;
                const userName = card.dataset.userName.toLowerCase();
                const userEmail = card.dataset.userEmail.toLowerCase();
                const text = card.textContent.toLowerCase();
                
                // Check status filter
                const statusMatch = statusFilter === 'all' || status === statusFilter;
                
                // Check ticket type filter
                const ticketTypeMatch = ticketTypeFilter === 'all' || ticketType === ticketTypeFilter;
                
                // Check search term (search in name, email, and all text)
                const searchMatch = searchTerm === '' || 
                                  userName.includes(searchTerm) || 
                                  userEmail.includes(searchTerm) || 
                                  text.includes(searchTerm);
                
                // Show card only if all filters match
                if (statusMatch && ticketTypeMatch && searchMatch) {
                    card.style.display = '';
            } else {
                    card.style.display = 'none';
                }
            });
        }

        // Add event listeners for all filters when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const statusFilter = document.getElementById('status-filter');
            const ticketTypeFilter = document.getElementById('ticket-type-filter');
            const searchTickets = document.getElementById('search-tickets');
            
            if (statusFilter) statusFilter.addEventListener('change', applyFilters);
            if (ticketTypeFilter) ticketTypeFilter.addEventListener('change', applyFilters);
            if (searchTickets) searchTickets.addEventListener('input', applyFilters);
        });

    </script>

    <!-- Floating QR Scanner Button -->
    @if(!$isAdmin && !optional($event->end_date)->isPast())
    <div class="fixed bottom-6 right-6 z-50">
        <a href="{{ route('organizer.events.scanner', $event) }}" 
           class="inline-flex items-center justify-center w-16 h-16 bg-blue-500 hover:bg-blue-600 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105"
           title="QR Scanner">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
        </a>
    </div>
    @endif
</x-unified-layout>