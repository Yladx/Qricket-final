<x-admin-layout title="Review Event" page-title="Event Review">
    @push('head')
    <meta name="event-id" content="{{ $event->id }}">
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.events.approval.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 hover:text-qricket-600 dark:text-gray-300 dark:hover:text-qricket-400 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Approvals
                </a>
                <div class="h-6 w-px bg-gray-300 dark:bg-gray-600"></div>
                <div>
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $event->title ?? 'Untitled Event' }}</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        by {{ $event->user ? ($event->user->first_name . ' ' . $event->user->last_name) : ($event->user_id === null ? 'Admin' : 'Unknown User') }}
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 rounded-full bg-yellow-500"></div>
                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Pending Review</span>
                </div>
                <div class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200 border border-yellow-200 dark:border-yellow-700">
                    {{ ucwords(str_replace('_',' ', $event->status ?? 'pending_approval')) }}
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Event Overview Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-8">
                <div class="px-6 py-5">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Event Overview</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Review all aspects of this event submission</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-qricket-100 dark:bg-qricket-900/30 text-qricket-700 dark:text-qricket-300">{{ ucfirst($event->category ?? 'event') }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Event Details -->
                        <div class="space-y-3">
                            <div class="flex items-center text-sm">
                                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-gray-900 dark:text-gray-100 font-medium">{{ optional($event->start_date)->format('M j, Y g:i A') ?: 'Date to be announced' }}</span>
                            </div>
                            @if($event->location)
                            <div class="flex items-center text-sm">
                                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="text-gray-900 dark:text-gray-100">{{ $event->location }}</span>
                            </div>
                            @endif
                        </div>

                        <!-- Organizer Info -->
                        <div class="space-y-3">
                            <div class="flex items-center text-sm">
                                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <div class="flex items-center space-x-2">
                                    @php $org = $event->user; @endphp
                                    @if($org && ($org->profile_picture_url ?? false))
                                    <img src="{{ $org->profile_picture_url }}" alt="{{ $org->first_name }}" class="w-5 h-5 rounded-full object-cover">
                                    @else
                                    @php $initial = $org ? strtoupper(substr(trim(($org->first_name ?? '') . ' ' . ($org->last_name ?? '')),0,1)) : ($event->user_id === null ? 'Admin' : 'U'); @endphp
                                    <div class="w-5 h-5 rounded-full bg-qricket-600 text-white flex items-center justify-center text-xs font-semibold">{{ $initial }}</div>
                                    @endif
                                    @if($org)
                                    <a href="{{ route('admin.users.show', $org->id) }}" class="font-medium text-qricket-600 dark:text-qricket-400 hover:underline">
                                        {{ $org->first_name }} {{ $org->last_name }}
                                    </a>
                                    @else
                                    <span class="font-medium text-gray-600 dark:text-gray-400">
                                        {{ $event->user_id === null ? 'Admin Created Event' : 'Unknown User' }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                            @if($org && $org->email)
                            <div class="flex items-center text-sm ml-7">
                                <span class="text-gray-500 dark:text-gray-400">{{ $org->email }}</span>
                            </div>
                            @endif
                        </div>

                        <!-- Submission Info -->
                        <div class="space-y-3">
                            <div class="flex items-center text-sm">
                                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-gray-900 dark:text-gray-100">Submitted {{ optional($submittedAt)->diffForHumans() ?? 'Recently' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Review Sections -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Left: Navigation -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 sticky top-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Review Sections</h3>
                        <nav class="space-y-1">
                            <button type="button" data-step="layout" onclick="showStep('layout'); setActiveStep('layout')"
                                class="step-btn w-full flex items-center justify-start px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 hover:bg-qricket-50 dark:hover:bg-qricket-900/20 hover:text-qricket-700 dark:hover:text-qricket-300">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                                Layout & Design
                            </button>
                            <button type="button" data-step="tickets" onclick="showStep('tickets'); setActiveStep('tickets')"
                                class="step-btn w-full flex items-center justify-start px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 hover:bg-qricket-50 dark:hover:bg-qricket-900/20 hover:text-qricket-700 dark:hover:text-qricket-300">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                </svg>
                                Tickets & Pricing
                            </button>
                            <button type="button" data-step="certificate" onclick="showStep('certificate'); setActiveStep('certificate')"
                                class="step-btn w-full flex items-center justify-start px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 hover:bg-qricket-50 dark:hover:bg-qricket-900/20 hover:text-qricket-700 dark:hover:text-qricket-300">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M9 8h6M5 4h14a2 2 0 012 2v12l-4-2-4 2-4-2-4 2V6a2 2 0 012-2z" />
                                </svg>
                                Certificate
                            </button>
                            <button type="button" data-step="staff" onclick="showStep('staff'); setActiveStep('staff')"
                                class="step-btn w-full flex items-center justify-start px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 hover:bg-qricket-50 dark:hover:bg-qricket-900/20 hover:text-qricket-700 dark:hover:text-qricket-300">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Staff & Team
                            </button>
                            <button type="button" data-step="evaluation" onclick="showStep('evaluation'); setActiveStep('evaluation')"
                                class="step-btn w-full flex items-center justify-start px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 hover:bg-qricket-50 dark:hover:bg-qricket-900/20 hover:text-qricket-700 dark:hover:text-qricket-300">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Final Review
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Right: Step Content -->
                <div class="lg:col-span-3">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                        <div id="step-layout" class="hidden p-6">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Layout & Design</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Review the event layout and visual design</p>
                                </div>
                            </div>

                            <!-- Event Header Preview -->
                            <div class="mb-6">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Event Header Preview</h4>
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                    <x-events.event-header :event="$event" :section="'info'" :hideActions="true" />
                                </div>
                            </div>

                            <!-- Page preview container -->
                            <div id="admin-layout-container" class="space-y-4"></div>

                            <div class="flex justify-end mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <button class="px-4 py-2 bg-qricket-600 hover:bg-qricket-700 text-white text-sm font-medium rounded-lg transition-colors" onclick="showStep('tickets')">
                                    Next: Tickets & Pricing
                                    <svg class="w-4 h-4 ml-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div id="step-tickets" class="hidden p-6">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Tickets & Pricing</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Review ticket types and pricing structure</p>
                                </div>
                            </div>

                            <!-- Ticket Types Preview -->
                            @include('organizer.events.preview.ticket-types-preview', [
                            'ticketTypes' => $ticketTypes,
                            'event' => $event,
                            'ticketSettings' => $ticketSettings
                            ])

                            <!-- Ticket Design Preview -->
                            @if(!empty($ticketTypes))
                            <div class="mt-8">
                                <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-qricket-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                    </svg>
                                    Ticket Design Preview
                                </h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">This is how tickets will appear to attendees</p>

                                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                                    <div class="transform scale-75 origin-top-left" style="width: 133.33%;">
                                        @php
                                        $firstTicket = is_array($ticketTypes) ? $ticketTypes[0] : $ticketTypes->first();
                                        $ticketName = $firstTicket['name'] ?? ($firstTicket->name ?? 'General Admission');
                                        // Use ticket colors from ticketSettings if available, otherwise use defaults
                                        $ticketColorsData = $ticketSettings['ticket_colors'] ?? [];
                                        $ticketColors = [
                                        'primary' => $ticketColorsData['primary'] ?? '#10b981',
                                        'secondary' => $ticketColorsData['secondary'] ?? '#f59e0b',
                                        'accent' => $ticketColorsData['accent'] ?? '#059669'
                                        ];
                                        @endphp

                                        @include('organizer.events.preview.ticket-preview', [
                                        'holderName' => 'Sample Attendee',
                                        'eventTitle' => $event->title ?? 'Sample Event',
                                        'dateTime' => optional($event->start_date)->format('M j, Y \a\t g:i A') ?? 'Date TBA',
                                        'venue' => $event->location ?? 'Venue TBA',
                                        'ticketType' => $ticketName,
                                        'ticketId' => 'TKT-001',
                                        'status' => 'PAID',
                                        'qrText' => 'QRICKET-123456',
                                        'bgImage' => $event->thumbnail_url ?? null,
                                        'primaryColor' => $ticketColors['primary'],
                                        'secondaryColor' => $ticketColors['secondary'],
                                        'accentColor' => $ticketColors['accent']
                                        ])
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="flex justify-between mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <button class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 text-sm font-medium transition-colors" onclick="showStep('layout')">
                                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                    Back
                                </button>
                                <button class="px-4 py-2 bg-qricket-600 hover:bg-qricket-700 text-white text-sm font-medium rounded-lg transition-colors" onclick="showStep('certificate')">
                                    Next: Certificate
                                    <svg class="w-4 h-4 ml-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div id="step-certificate" class="hidden p-6">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Certificate</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Review certificate template and design</p>
                                </div>
                            </div>

                            @php
                            $fallbackWhen = (isset($event) && ($event->start_date ?? null))
                            ? ('held on ' . \Carbon\Carbon::parse($event->start_date)->format('F j, Y \\a\\t g:i A') . (($event->location ?? null) ? ' at ' . $event->location : '') . '.')
                            : '';
                            @endphp
                            @php
                            $fallbackGiven = (isset($event) && ($event->start_date ?? null))
                            ? ('Given this ' . \Carbon\Carbon::parse($event->start_date)->format('jS') . ' day of ' . \Carbon\Carbon::parse($event->start_date)->format('F, Y') . '.')
                            : '';
                            @endphp

                            <!-- Certificate Preview -->
                            @include('organizer.events.preview.certificate-preview', [
                            'certificateData' => $certificateData,
                            'certificateEnabled' => $certificateEnabled,
                            'event' => $event,
                            'fallbackWhen' => $fallbackWhen,
                            'fallbackGiven' => $fallbackGiven
                            ])

                            <div class="flex justify-between mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <button class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 text-sm font-medium transition-colors" onclick="showStep('tickets')">
                                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                    Back
                                </button>
                                <button class="px-4 py-2 bg-qricket-600 hover:bg-qricket-700 text-white text-sm font-medium rounded-lg transition-colors" onclick="showStep('staff')">
                                    Next: Staff & Team
                                    <svg class="w-4 h-4 ml-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div id="step-staff" class="hidden p-6">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Staff & Team</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Review staff members and their roles</p>
                                </div>
                            </div>

                            <!-- Staff Preview -->
                            @include('organizer.events.preview.staff-preview', [
                            'staffData' => $staffData,
                            'event' => $event
                            ])

                            <div class="flex justify-between mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <button class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 text-sm font-medium transition-colors" onclick="showStep('certificate')">
                                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                    Back
                                </button>
                                <button class="px-4 py-2 bg-qricket-600 hover:bg-qricket-700 text-white text-sm font-medium rounded-lg transition-colors" onclick="showStep('evaluation')">
                                    Next: Final Review
                                    <svg class="w-4 h-4 ml-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div id="step-evaluation" class="hidden p-6">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Final Review</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Complete evaluation and make your decision</p>
                                </div>
                            </div>

                            @php
                            $evHasLayout = !empty($layoutData);
                            @endphp
                            @php
                            $evHasTickets = !empty($ticketTypes);
                            @endphp
                            @php
                            $evHasCert = !empty($certificateData);
                            @endphp
                            @php
                            $evHasStaff = !empty($staffData) && !empty($staffData['members']);
                            @endphp

                            <!-- Review Summary -->
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 mb-6">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Review Summary</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <!-- Layout -->
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $evHasLayout ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }} flex items-center justify-center">
                                            <svg class="w-4 h-4 {{ $evHasLayout ? 'text-green-600 dark:text-green-300' : 'text-red-600 dark:text-red-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Layout</div>
                                            <div class="text-xs {{ $evHasLayout ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ $evHasLayout ? 'Complete' : 'Missing' }}</div>
                                        </div>
                                    </div>
                                    <!-- Tickets -->
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $evHasTickets ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }} flex items-center justify-center">
                                            <svg class="w-4 h-4 {{ $evHasTickets ? 'text-green-600 dark:text-green-300' : 'text-red-600 dark:text-red-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Tickets</div>
                                            <div class="text-xs {{ $evHasTickets ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ $evHasTickets ? 'Configured' : 'Missing' }}</div>
                                        </div>
                                    </div>
                                    <!-- Certificate -->
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $evHasCert ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }} flex items-center justify-center">
                                            <svg class="w-4 h-4 {{ $evHasCert ? 'text-green-600 dark:text-green-300' : 'text-red-600 dark:text-red-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M9 8h6M5 4h14a2 2 0 012 2v12l-4-2-4 2-4-2-4 2V6a2 2 0 012-2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Certificate</div>
                                            <div class="text-xs {{ $evHasCert ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ $evHasCert ? 'Provided' : 'Missing' }}</div>
                                        </div>
                                    </div>
                                    <!-- Staff -->
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $evHasStaff ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }} flex items-center justify-center">
                                            <svg class="w-4 h-4 {{ $evHasStaff ? 'text-green-600 dark:text-green-300' : 'text-red-600 dark:text-red-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Staff</div>
                                            <div class="text-xs {{ $evHasStaff ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ $evHasStaff ? 'Assigned' : 'Missing' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex justify-between items-center pt-6 border-t border-gray-200 dark:border-gray-700">
                                <button class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 text-sm font-medium transition-colors" onclick="showStep('staff')">
                                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                    Back
                                </button>

                                <div class="flex space-x-3">
                                    <form method="POST" action="{{ route('admin.events.approval.reject', $event->id) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="rejection_reason" id="rejection_reason_bottom">
                                        <button type="button" onclick="promptReject('rejection_reason_bottom')"
                                            class="inline-flex items-center px-4 py-2 border border-red-300 dark:border-red-600 text-sm font-medium rounded-lg text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Reject Event
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.events.approval.approve', $event->id) }}" class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Approve Event
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden data container for JavaScript -->
    <div id="admin-event-data"
        data-event-id="{{ $event->id ?? null }}"
        data-event="{{ htmlspecialchars(json_encode($event ?? []), ENT_QUOTES, 'UTF-8') }}"
        data-layout="{{ htmlspecialchars(json_encode($layoutData ?? []), ENT_QUOTES, 'UTF-8') }}"
        style="display: none;"></div>

    <script>
        // Set event ID for JavaScript image loading (used by layout templates)
        const dataContainer = document.getElementById('admin-event-data');
        window.currentEventId = dataContainer ? parseInt(dataContainer.getAttribute('data-event-id')) || null : null;

        function promptReject(hiddenId = 'rejection_reason') {
            const reason = prompt('Enter rejection reason (optional):');
            if (reason !== null) {
                document.getElementById(hiddenId).value = reason;
                document.querySelector('form[action$="/reject"]').submit();
            }
        }

        function showStep(step) {
            const ids = ['layout', 'tickets', 'certificate', 'staff', 'evaluation'];
            ids.forEach(id => {
                const el = document.getElementById('step-' + id);
                if (el) {
                    if (id === step) {
                        el.classList.remove('hidden');
                    } else {
                        el.classList.add('hidden');
                    }
                }
            });
        }

        function setActiveStep(step) {
            // Update button styles to show active state
            document.querySelectorAll('.step-btn').forEach(btn => {
                btn.classList.remove('bg-qricket-100', 'dark:bg-qricket-900/30', 'text-qricket-700', 'dark:text-qricket-300');
                btn.classList.add('bg-gray-50', 'dark:bg-gray-700/50', 'text-gray-700', 'dark:text-gray-300');
            });

            const activeBtn = document.querySelector(`[data-step="${step}"]`);
            if (activeBtn) {
                activeBtn.classList.remove('bg-gray-50', 'dark:bg-gray-700/50', 'text-gray-700', 'dark:text-gray-300');
                activeBtn.classList.add('bg-qricket-100', 'dark:bg-qricket-900/30', 'text-qricket-700', 'dark:text-qricket-300');
            }
        }

        // Admin layout rendering (like public event show)
        let adminEventData = {};
        let adminLayoutData = [];

        if (dataContainer) {
            try {
                const eventDataStr = dataContainer.getAttribute('data-event') || '{}';
                adminEventData = JSON.parse(eventDataStr);
            } catch (e) {
                console.error('Admin: Failed to parse event data:', e);
                adminEventData = {};
            }

            try {
                const layoutDataStr = dataContainer.getAttribute('data-layout') || '[]';
                adminLayoutData = JSON.parse(layoutDataStr);
            } catch (e) {
                console.error('Admin: Failed to parse layout data:', e);
                adminLayoutData = [];
            }
        }

        // Set event data globally for layout rendering (like event preview page)
        window.eventData = adminEventData;

        function loadAdminLayout() {
            let components = adminLayoutData;
            console.log('Admin: Raw layout data:', components);
            console.log('Admin: Layout data type:', typeof components);

            if (!components) {
                console.log('Admin: No layout data found');
                document.getElementById('admin-layout-container').innerHTML = '<div class="text-sm text-gray-500">No layout configured.</div>';
                return;
            }

            if (typeof components === 'string') {
                try {
                    if (components.trim() === '') {
                        components = [];
                    } else {
                        components = JSON.parse(components);
                    }
                    console.log('Admin: Parsed layout data from string');
                } catch (e) {
                    console.error('Admin: Failed to parse layout data:', e, 'Raw data:', components);
                    components = [];
                }
            }

            if (!Array.isArray(components)) {
                console.error('Admin: Layout data is not an array:', components);
                document.getElementById('admin-layout-container').innerHTML = '<div class="text-sm text-red-500">Invalid layout data format.</div>';
                return;
            }

            if (components.length === 0) {
                console.log('Admin: Layout data is empty array');
                document.getElementById('admin-layout-container').innerHTML = '<div class="text-sm text-gray-500">No layout configured.</div>';
                return;
            }

            console.log('Admin: Processing', components.length, 'layout components');

            const container = document.getElementById('admin-layout-container');

            // Show loading state
            container.innerHTML = `
                <div class="space-y-4">
                    <div class="animate-pulse">
                        <div class="bg-gray-200 dark:bg-gray-700 rounded-lg h-32 mb-4"></div>
                    </div>
                    <div class="animate-pulse">
                        <div class="bg-gray-200 dark:bg-gray-700 rounded-lg h-24 mb-4"></div>
                    </div>
                </div>
            `;

            // Make sure rendering is always in order.
            const results = new Array(components.length);
            let loadedComponents = 0;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            components.forEach((component, index) => {
                const layoutType = component.layout || component.type;
                const details = component.details || component.data || {};

                if (!layoutType) {
                    console.error('Admin: Component missing layout/type property:', component);
                    results[index] = `
                        <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg mb-4">
                            <div class="text-red-500 text-sm">Invalid component: missing layout/type property</div>
                        </div>
                    `;
                    loadedComponents++;
                    if (loadedComponents === components.length) {
                        container.innerHTML = results.join('');
                    }
                    return;
                }

                console.log(`Admin: Loading component ${index + 1}/${components.length}: ${layoutType}`);

                fetch(`/events/layouts/${layoutType}?id={{ $event->id }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            eventData: adminEventData,
                            details
                        })
                    })
                    .then(r => {
                        if (!r.ok) throw new Error('HTTP ' + r.status);
                        return r.text();
                    })
                    .then(html => {
                        console.log(`Admin: Successfully loaded ${layoutType} component`);
                        results[index] = html;
                        loadedComponents++;
                        if (loadedComponents === components.length) {
                            container.innerHTML = results.join('');
                            console.log('Admin: All layout components rendered successfully (ordered)');
                        }
                    })
                    .catch(error => {
                        console.error(`Admin: Failed to load ${layoutType} component:`, error);
                        // Fallback to simple rendering in the correct index
                        results[index] = `
                        <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    ${layoutType.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                </h3>
                                <span class="px-2 py-1 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 text-xs rounded">
                                    Error Loading
                                </span>
                            </div>
                            <p class="text-red-500 text-sm">Failed to load layout component: ${layoutType}</p>
                            <p class="text-red-500 text-xs">Error: ${error.message}</p>
                        </div>
                    `;
                        loadedComponents++;
                        if (loadedComponents === components.length) {
                            container.innerHTML = results.join('');
                        }
                    });
            });
        }

        // Default to first step and render layout
        document.addEventListener('DOMContentLoaded', () => {
            showStep('layout');
            setActiveStep('layout');
            loadAdminLayout();
        });
    </script>
</x-admin-layout>
