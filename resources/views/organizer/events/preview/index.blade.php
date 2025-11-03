<x-unified-layout :layout-type="$layoutType" :title="'Event Preview - ' . $event->title" :page-title="'Event Preview'">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $event->title ?? 'Untitled Event' }}</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Event Preview</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('organizer.events.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back
                </a>
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
                            <p class="text-sm text-gray-600 dark:text-gray-400">Preview all aspects of your event</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-qricket-100 dark:bg-qricket-900/30 text-qricket-700 dark:text-qricket-300">{{ ucfirst($event->category ?? 'event') }}</span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Event Details -->
                        <div class="space-y-3">
                            <div class="flex items-center text-sm">
                                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="text-gray-900 dark:text-gray-100 font-medium">{{ optional($event->start_date)->format('M j, Y g:i A') ?: 'Date to be announced' }}</span>
                            </div>
                            @if($event->location)
                            <div class="flex items-center text-sm">
                                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span class="text-gray-900 dark:text-gray-100">{{ $event->location }}</span>
                            </div>
                            @endif
                        </div>
                        
                        <!-- Organizer Info -->
                        <div class="space-y-3">
                            <div class="flex items-center text-sm">
                                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <div class="flex items-center space-x-2">
                                    @php $org = $event->user; @endphp
                                    @if($org && ($org->profile_picture_url ?? false))
                                        <img src="{{ $org->profile_picture_url }}" alt="{{ $org->first_name }}" class="w-5 h-5 rounded-full object-cover">
                                    @else
                                        @php $initial = $org ? strtoupper(substr(trim(($org->first_name ?? '') . ' ' . ($org->last_name ?? '')),0,1)) : 'U'; @endphp
                                        <div class="w-5 h-5 rounded-full bg-qricket-600 text-white flex items-center justify-center text-xs font-semibold">{{ $initial }}</div>
                                    @endif
                                    <span class="font-medium text-qricket-600 dark:text-qricket-400">
                                        {{ $org?->first_name }} {{ $org?->last_name }}
                                    </span>
                                </div>
                            </div>
                            @if($org && $org->email)
                            <div class="flex items-center text-sm ml-7">
                                <span class="text-gray-500 dark:text-gray-400">{{ $org->email }}</span>
                            </div>
                            @endif
                        </div>
                        
                        <!-- Event Status -->
                        <div class="space-y-3">
                            <div class="flex items-center text-sm">
                                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="text-gray-900 dark:text-gray-100">Status: {{ ucwords(str_replace('_',' ', $event->status ?? 'draft')) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Preview Sections -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Left: Navigation -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 sticky top-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Preview Sections</h3>
                        <nav class="space-y-1">
                            <button type="button" data-step="layout" onclick="showStep('layout'); setActiveStep('layout')"
                                    class="step-btn w-full flex items-center justify-start px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 hover:bg-qricket-50 dark:hover:bg-qricket-900/20 hover:text-qricket-700 dark:hover:text-qricket-300">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                Layout & Design
                            </button>
                            <button type="button" data-step="tickets" onclick="showStep('tickets'); setActiveStep('tickets')"
                                    class="step-btn w-full flex items-center justify-start px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 hover:bg-qricket-50 dark:hover:bg-qricket-900/20 hover:text-qricket-700 dark:hover:text-qricket-300">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                Tickets & Pricing
                            </button>
                            <button type="button" data-step="certificate" onclick="showStep('certificate'); setActiveStep('certificate')"
                                    class="step-btn w-full flex items-center justify-start px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 hover:bg-qricket-50 dark:hover:bg-qricket-900/20 hover:text-qricket-700 dark:hover:text-qricket-300">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M9 8h6M5 4h14a2 2 0 012 2v12l-4-2-4 2-4-2-4 2V6a2 2 0 012-2z"/></svg>
                                Certificate
                            </button>
                            <button type="button" data-step="staff" onclick="showStep('staff'); setActiveStep('staff')"
                                    class="step-btn w-full flex items-center justify-start px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 hover:bg-qricket-50 dark:hover:bg-qricket-900/20 hover:text-qricket-700 dark:hover:text-qricket-300">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Staff & Team
                            </button>
                            <button type="button" data-step="evaluation" onclick="showStep('evaluation'); setActiveStep('evaluation')"
                                    class="step-btn w-full flex items-center justify-start px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 hover:bg-qricket-50 dark:hover:bg-qricket-900/20 hover:text-qricket-700 dark:hover:text-qricket-300">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Summary
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
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Preview the event layout and visual design</p>
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
                            <div id="organizer-layout-container" class="space-y-4"></div>
                            
                            <div class="flex justify-end mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <button class="px-4 py-2 bg-qricket-600 hover:bg-qricket-700 text-white text-sm font-medium rounded-lg transition-colors" onclick="showStep('tickets')">
                                    Next: Tickets & Pricing
                                    <svg class="w-4 h-4 ml-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                        </div>

                        <div id="step-tickets" class="hidden p-6">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Tickets & Pricing</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Preview ticket types and pricing structure</p>
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                        </svg>
                                        Ticket Design Preview
                                    </h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">This is how tickets will appear to attendees</p>
                                    
                                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                                        <div class="transform scale-75 origin-top-left" style="width: 133.33%;">
                                            @php
                                                $firstTicket = is_array($ticketTypes) ? $ticketTypes[0] : $ticketTypes->first();
                                                $ticketName = $firstTicket['name'] ?? ($firstTicket->name ?? 'General Admission');
                                                
                                                // Use actual ticket settings from R2 storage with safe fallbacks
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
                                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    Back
                                </button>
                                <button class="px-4 py-2 bg-qricket-600 hover:bg-qricket-700 text-white text-sm font-medium rounded-lg transition-colors" onclick="showStep('certificate')">
                                    Next: Certificate
                                    <svg class="w-4 h-4 ml-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                        </div>

                        <div id="step-certificate" class="hidden p-6">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Certificate</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Preview certificate template and design</p>
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
                                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    Back
                                </button>
                                <button class="px-4 py-2 bg-qricket-600 hover:bg-qricket-700 text-white text-sm font-medium rounded-lg transition-colors" onclick="showStep('staff')">
                                    Next: Staff & Team
                                    <svg class="w-4 h-4 ml-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                        </div>

                        <div id="step-staff" class="hidden p-6">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Staff & Team</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Preview staff members and their roles</p>
                                </div>
                            </div>
                            
                            <!-- Staff Preview -->
                            @include('organizer.events.preview.staff-preview', [
                                'staffData' => $staffData,
                                'event' => $event
                            ])
                            
                            <div class="flex justify-between mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <button class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 text-sm font-medium transition-colors" onclick="showStep('certificate')">
                                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    Back
                                </button>
                                <button class="px-4 py-2 bg-qricket-600 hover:bg-qricket-700 text-white text-sm font-medium rounded-lg transition-colors" onclick="showStep('evaluation')">
                                    Next: Summary
                                    <svg class="w-4 h-4 ml-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                        </div>

                        <div id="step-evaluation" class="hidden p-6">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Event Summary</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Complete overview of your event</p>
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
                            
                            <!-- Summary Cards -->
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 mb-6">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Event Configuration Summary</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <!-- Layout -->
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $evHasLayout ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }} flex items-center justify-center">
                                            <svg class="w-4 h-4 {{ $evHasLayout ? 'text-green-600 dark:text-green-300' : 'text-red-600 dark:text-red-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Layout</div>
                                            <div class="text-xs {{ $evHasLayout ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ $evHasLayout ? 'Complete' : 'Missing' }}</div>
                                        </div>
                                    </div>
                                    <!-- Tickets -->
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $evHasTickets ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }} flex items-center justify-center">
                                            <svg class="w-4 h-4 {{ $evHasTickets ? 'text-green-600 dark:text-green-300' : 'text-red-600 dark:text-red-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Tickets</div>
                                            <div class="text-xs {{ $evHasTickets ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ $evHasTickets ? 'Configured' : 'Missing' }}</div>
                                        </div>
                                    </div>
                                    <!-- Certificate -->
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $evHasCert ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }} flex items-center justify-center">
                                            <svg class="w-4 h-4 {{ $evHasCert ? 'text-green-600 dark:text-green-300' : 'text-red-600 dark:text-red-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M9 8h6M5 4h14a2 2 0 012 2v12l-4-2-4 2-4-2-4 2V6a2 2 0 012-2z"/></svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Certificate</div>
                                            <div class="text-xs {{ $evHasCert ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ $evHasCert ? 'Provided' : 'Missing' }}</div>
                                        </div>
                                    </div>
                                    <!-- Staff -->
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $evHasStaff ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }} flex items-center justify-center">
                                            <svg class="w-4 h-4 {{ $evHasStaff ? 'text-green-600 dark:text-green-300' : 'text-red-600 dark:text-red-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
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
                                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    Back
                                </button>
                                
                                <div class="flex space-x-3">
                                    @if(in_array($event->status, ['published', 'approved', 'accepted']))
                                        <a href="{{ route('organizer.events.manage', $event) }}" 
                                           class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-qricket-500 transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            Manage Event
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Set event ID for JavaScript image loading (used by layout templates)
        window.currentEventId = {{ $event->id }};
        
        function showStep(step) {
            const ids = ['layout','tickets','certificate','staff','evaluation'];
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

        function setActiveStep(stepName) {
            // Remove active class from all buttons
            document.querySelectorAll('.step-btn').forEach(btn => {
                btn.classList.remove('bg-qricket-50', 'dark:bg-qricket-900/20', 'text-qricket-700', 'dark:text-qricket-300');
                btn.classList.add('bg-gray-50', 'dark:bg-gray-700/50', 'text-gray-700', 'dark:text-gray-300');
            });
            
            // Add active class to selected button
            const activeBtn = document.querySelector(`[data-step="${stepName}"]`);
            if (activeBtn) {
                activeBtn.classList.remove('bg-gray-50', 'dark:bg-gray-700/50', 'text-gray-700', 'dark:text-gray-300');
                activeBtn.classList.add('bg-qricket-50', 'dark:bg-qricket-900/20', 'text-qricket-700', 'dark:text-qricket-300');
            }
        }

        // Organizer layout rendering (same as admin approval)
        const organizerEventData = @json($event);
        let organizerLayoutData = @json($layoutData);

        function loadOrganizerLayout() {
            let components = organizerLayoutData;
            if (!components) return;
            if (typeof components === 'string') {
                try { components = JSON.parse(components); } catch (e) { components = []; }
            }
            if (!Array.isArray(components) || components.length === 0) {
                document.getElementById('organizer-layout-container').innerHTML = '<div class="text-sm text-gray-500">No layout configured.</div>';
                return;
            }

            const container = document.getElementById('organizer-layout-container');

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
                const layoutType = component.layout;
                const details = component.details || {};

                console.log(`Organizer: Loading component ${index + 1}/${components.length}: ${layoutType}`);

                fetch(`/events/layouts/${layoutType}?id={{ $event->id }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ eventData: organizerEventData, details })
                })
                .then(r => { 
                    if (!r.ok) throw new Error('HTTP ' + r.status); 
                    return r.text(); 
                })
                .then(html => { 
                    console.log(`Organizer: Successfully loaded ${layoutType} component`);
                    results[index] = html;
                    loadedComponents++;
                    if (loadedComponents === components.length) {
                        container.innerHTML = results.join('');
                        console.log('Organizer: All layout components rendered successfully (ordered)');
                    }
                })
                .catch(error => {
                    console.error(`Organizer: Failed to load ${layoutType} component:`, error);
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
            loadOrganizerLayout(); 
        });
    </script>
</x-unified-layout>
