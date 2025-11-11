<div class="space-y-4">
    <p class="text-sm text-gray-600 dark:text-gray-300">Review your event details and choose how to publish the event.</p>

    <!-- Rejection Reason Section (only for rejected events) -->
    @if(isset($eventData['status']) && $eventData['status'] === 'rejected' && $eventData['rejection_reason'])
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                        Event Rejected - Please Review Feedback
                    </h3>
                    <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                        <p class="font-medium mb-2">Rejection Reason:</p>
                        <p class="bg-white dark:bg-gray-800 p-3 rounded border border-red-200 dark:border-red-700">
                            {{ $eventData['rejection_reason'] }}
                        </p>
                    </div>
                    @if(isset($eventData['rejected_at']))
                        <div class="mt-2 text-xs text-red-600 dark:text-red-400">
                            Rejected on: {{ \Carbon\Carbon::parse($eventData['rejected_at'])->format('M d, Y g:i A') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Event Preview Section -->
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Event Preview</h3>
            <button type="button" id="preview-event-btn" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Preview Event
            </button>
        </div>
         <div id="event-preview-content" class="relative">
            @php
                // Create a mock event object for the event-header component
                $mockEvent = (object) [
                    'id' => $eventData['id'] ?? 0,
                    'title' => $eventData['title'] ?? 'Event Title',
                    'description' => $eventData['description'] ?? 'Event Description',
                    'category' => $eventData['category'] ?? 'event',
                    'start_date' => $eventData['start_date'] ?? null,
                    'end_date' => $eventData['end_date'] ?? null,
                    'location' => $eventData['location'] ?? null,
                    'poster_url' => $eventData['existing_poster_url'] ?? null,
                    'thumbnail_url' => $eventData['existing_thumbnail_url'] ?? null,
                    'status' => 'published' // For preview purposes
                ];
            @endphp
            <x-events.event-header 
                :event="$mockEvent" 
                :previewPosterUrl="$eventData['existing_poster_url'] ?? null"
                :previewThumbnailUrl="$eventData['existing_thumbnail_url'] ?? null"
                :previewMode="true"
                :hideActions="true"
            />
         </div>
    </div>

    <div class="space-y-6">
            <!-- Staff Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Staff Members</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ count($eventData['staff_data'] ?? []) }} member(s)</span>
                </div>
                 <div id="review-staff" class="space-y-3">
                     @if(!empty($eventData['staff_data']))
                         @foreach($eventData['staff_data'] as $staff)
                             <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                                 <div class="flex items-start justify-between">
                                     <div class="flex items-center space-x-3">
                                         <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-800 rounded-full flex items-center justify-center">
                                             <span class="text-emerald-600 dark:text-emerald-300 font-semibold text-sm">
                                                 {{ strtoupper(substr($staff['name'] ?? $staff['email'] ?? 'U', 0, 1)) }}
                                             </span>
                                         </div>
                                         <div>
                                             <div class="font-medium text-gray-900 dark:text-gray-100">
                                                 {{ $staff['name'] ?? $staff['email'] ?? 'Unknown' }}
                                             </div>
                                             <div class="text-sm text-gray-500 dark:text-gray-400">{{ $staff['email'] ?? '' }}</div>
                                             <div class="flex flex-wrap gap-1 mt-2">
                                                 @if($staff['can_manage_event'] ?? false)
                                                     <span class="px-2 py-1 text-xs bg-emerald-50 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300 rounded border border-emerald-200 dark:border-emerald-700">Manage Event</span>
                                                 @endif
                                                 @if($staff['can_edit_event_info'] ?? false)
                                                     <span class="px-2 py-1 text-xs bg-emerald-50 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300 rounded border border-emerald-200 dark:border-emerald-700">Edit Info</span>
                                                 @endif
                                                 @if($staff['can_manage_tickets'] ?? false)
                                                     <span class="px-2 py-1 text-xs bg-emerald-50 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300 rounded border border-emerald-200 dark:border-emerald-700">Tickets</span>
                                                 @endif
                                                 @if($staff['can_manage_news'] ?? false)
                                                     <span class="px-2 py-1 text-xs bg-emerald-50 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300 rounded border border-emerald-200 dark:border-emerald-700">News</span>
                                                 @endif
                                                 @if($staff['can_manage_attendance'] ?? false)
                                                     <span class="px-2 py-1 text-xs bg-emerald-50 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300 rounded border border-emerald-200 dark:border-emerald-700">Attendance</span>
                                                 @endif
                                                 @if($staff['can_entry_scan'] ?? false)
                                                     <span class="px-2 py-1 text-xs bg-emerald-50 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300 rounded border border-emerald-200 dark:border-emerald-700">Scan</span>
                                                 @endif
                                             </div>
                                         </div>
                                     </div>
                                     <div class="flex flex-col items-end space-y-2">
                                         <span class="px-3 py-1 text-xs font-medium bg-emerald-100 dark:bg-emerald-800 text-emerald-700 dark:text-emerald-300 rounded-full">
                                             {{ ucfirst($staff['role'] ?? 'staff') }}
                                         </span>
                                     </div>
                                 </div>
                             </div>
                         @endforeach
                     @else
                         <div class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No staff members added yet.</div>
                     @endif
                 </div>
            </div>

            <!-- Tickets Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Ticket Types</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ count($eventData['ticket_types'] ?? []) }} type(s)</span>
                </div>
                <div id="review-tickets" class="space-y-3">
                    @if(!empty($eventData['ticket_types']))
                        @foreach($eventData['ticket_types'] as $ticket)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $ticket['name'] ?? 'Ticket Type' }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $ticket['description'] ?? 'No description' }}</div>
                                    <div class="flex items-center space-x-4 mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        <span>Price: ₱{{ number_format($ticket['price'] ?? 0, 2) }}</span>
                                        <span>Quantity: {{ $ticket['quantity'] ?? 'Unlimited' }}</span>
                                        @if($ticket['early_bird_price'] ?? false)
                                            <span class="text-emerald-600">Early Bird: ₱{{ number_format($ticket['early_bird_price'], 2) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    @if($ticket['is_active'] ?? true)
                                        <span class="px-2 py-1 text-xs bg-emerald-100 dark:bg-emerald-800 text-emerald-700 dark:text-emerald-300 rounded-full">Active</span>
                                    @else
                                        <span class="px-2 py-1 text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full">Inactive</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No ticket types configured yet.</div>
                    @endif
                </div>
            </div>

            <!-- Ticket Settings Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Ticket Settings</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Configuration</span>
                </div>
                <div id="review-ticket-settings" class="space-y-3">
                    @php($settings = $eventData['ticket_settings'] ?? [])
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Max Tickets Per Order</div>
                            <div class="text-lg font-semibold text-qricket-600 dark:text-qricket-400">{{ $settings['max_tickets_per_order'] ?? 10 }}</div>
                        </div>
                        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Max Tickets Per User</div>
                            <div class="text-lg font-semibold text-qricket-600 dark:text-qricket-400">{{ $settings['max_tickets_per_user'] ?? 1 }}</div>
                        </div>
                        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Allow Refunds</div>
                            <div class="text-lg font-semibold {{ ($settings['allow_refunds'] ?? true) ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ ($settings['allow_refunds'] ?? true) ? 'Yes' : 'No' }}
                            </div>
                        </div>
                        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Require Approval</div>
                            <div class="text-lg font-semibold {{ ($settings['require_approval'] ?? false) ? 'text-orange-600 dark:text-orange-400' : 'text-green-600 dark:text-green-400' }}">
                                {{ ($settings['require_approval'] ?? false) ? 'Yes' : 'No' }}
                            </div>
                        </div>
                        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Allow Share Ticket</div>
                            <div class="text-lg font-semibold {{ ($settings['allow_share_ticket'] ?? true) ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ ($settings['allow_share_ticket'] ?? true) ? 'Yes' : 'No' }}
                            </div>
                        </div>
                        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Limit Per User</div>
                            <div class="text-lg font-semibold {{ ($settings['limit_per_user'] ?? false) ? 'text-orange-600 dark:text-orange-400' : 'text-gray-600 dark:text-gray-400' }}">
                                {{ ($settings['limit_per_user'] ?? false) ? 'Yes' : 'No' }}
                            </div>
                        </div>
                    </div>
                    @if(!empty($settings['refund_policy']))
                    <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Refund Policy</div>
                        <div class="text-sm text-gray-600 dark:text-gray-300">{{ $settings['refund_policy'] }}</div>
                    </div>
                    @endif
                    @if(!empty($settings['ticket_colors']))
                    <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Ticket Colors</div>
                        <div class="flex items-center space-x-4">
                            @if(!empty($settings['ticket_colors']['primary']))
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-6 rounded-full border border-gray-300" style="background-color: {{ $settings['ticket_colors']['primary'] }}"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Primary</span>
                            </div>
                            @endif
                            @if(!empty($settings['ticket_colors']['secondary']))
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-6 rounded-full border border-gray-300" style="background-color: {{ $settings['ticket_colors']['secondary'] }}"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Secondary</span>
                            </div>
                            @endif
                            @if(!empty($settings['ticket_colors']['accent']))
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-6 rounded-full border border-gray-300" style="background-color: {{ $settings['ticket_colors']['accent'] }}"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Accent</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Ticket Preview Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Ticket Preview</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Visual Style</span>
                </div>
                <div id="review-ticket-preview" class="space-y-4">
                    @if(!empty($eventData['ticket_types']) && count($eventData['ticket_types']) > 0)
                        @php($firstTicket = $eventData['ticket_types'][0])
                        @php($ticketColors = $eventData['ticket_settings']['ticket_colors'] ?? [])
                        @php($primaryColor = $ticketColors['primary'] ?? '#10b981')
                        @php($secondaryColor = $ticketColors['secondary'] ?? '#f59e0b')
                        @php($accentColor = $ticketColors['accent'] ?? '#059669')
                        
                        
                        @include('organizer.events.preview.ticket-preview', [
                            'holderName' => 'John Doe',
                            'eventTitle' => strtoupper($eventData['title'] ?? 'SAMPLE EVENT'),
                            'dateTime' => $eventData['start_date'] ? \Carbon\Carbon::parse($eventData['start_date'])->format('M j, Y \a\t g:i A') : 'Date TBD',
                            'venue' => $eventData['location'] ?? 'Location TBD',
                            'ticketType' => $firstTicket['name'] ?? 'General Admission',
                            'ticketId' => 'TKT-' . date('Y') . '-' . rand(1000, 9999),
                            'status' => 'PAID',
                            'qrText' => 'QRICKET-' . rand(1000000000, 9999999999),
                            'bgImage' => $eventData['existing_thumbnail_url'] ?? null,
                            'primaryColor' => $primaryColor,
                            'secondaryColor' => $secondaryColor,
                            'accentColor' => $accentColor,
                        ])
                    @else
                        <div class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">No ticket types configured to show preview</div>
                    @endif
                </div>
            </div>

            <!-- Publish Options Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    @if(isset($eventData['status']) && $eventData['status'] === 'rejected')
                        Resubmission Options
                    @else
                        Publish Options
                    @endif
                </h3>
                
                <div class="space-y-3">
                    <label class="flex items-center space-x-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                        <input type="radio" name="publish_option" value="draft" checked class="text-blue-600 focus:ring-blue-500">
                        <div>
                            <div class="font-medium text-gray-900 dark:text-gray-100">Save as Draft</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Save your event as a draft to continue editing later</div>
                        </div>
                    </label>
                    
                    <label class="flex items-center space-x-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                        <input type="radio" name="publish_option" value="pending_approval" class="text-blue-600 focus:ring-blue-500">
                        <div>
                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                @if(isset($eventData['status']) && $eventData['status'] === 'rejected')
                                    Resubmit for Approval
                                @else
                                    Submit for Approval
                                @endif
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                @if(isset($eventData['status']) && $eventData['status'] === 'rejected')
                                    Resubmit your event for admin review after addressing the feedback
                                @else
                                    Submit your event for admin review and approval
                                @endif
                            </div>
                        </div>
                    </label>
                    
                    <label class="flex items-center space-x-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                        <input type="radio" name="publish_option" value="scheduled" class="text-blue-600 focus:ring-blue-500">
                        <div>
                            <div class="font-medium text-gray-900 dark:text-gray-100">Schedule Publication</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Schedule your event to be published at a specific time</div>
                        </div>
                    </label>
                </div>

                <!-- Schedule DateTime Input -->
                <div id="schedule-input" class="mt-4 hidden">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Publish At</label>
                    <input type="datetime-local" name="publish_at" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Certificate Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Certificate Template</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        @if($eventData['certificate_enabled'] ?? true)
                            @if(!empty($eventData['certificate_data']))
                                Configured
                            @else
                                Not configured
                            @endif
                        @else
                            Disabled
                        @endif
                    </span>
                </div>
                <div id="review-certificate" class="space-y-3">
                    @if($eventData['certificate_enabled'] ?? true)
                        @if(!empty($eventData['certificate_data']))
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-16 bg-gray-200 dark:bg-gray-600 rounded border flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-gray-100">Certificate Template</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">PDF template configured</div>
                                </div>
                            </div>
                            
                            <!-- Certificate Preview -->
                            <div class="mt-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                                <div class="p-3 border-b border-gray-200 dark:border-gray-700">
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Certificate Preview</h4>
                                </div>
                                <div class="p-3">
                                    <div class="transform scale-50 origin-top-left" style="width: 200%;">
                                        @include('organizer.events.preview.certificate-template', [
                                            'certificateData' => $eventData['certificate_data'],
                                            'event' => $event,
                                            'fallbackWhen' => $eventData['start_date'] ? \Carbon\Carbon::parse($eventData['start_date'])->format('M j, Y') : 'Date TBD',
                                            'fallbackGiven' => $eventData['end_date'] ? \Carbon\Carbon::parse($eventData['end_date'])->format('M j, Y') : 'Date TBD',
                                        ])
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-sm text-gray-500 dark:text-gray-400">No certificate template configured</div>
                        @endif
                    @else
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-16 bg-gray-100 dark:bg-gray-700 rounded border flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"/>
                                </svg>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">Certificate Disabled</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">No certification for this event</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
    </div>

    <!-- Store event dates for JavaScript validation -->
    <div data-event-start-date="{{ $eventData['start_date'] ?? '' }}" data-event-end-date="{{ $eventData['end_date'] ?? '' }}" style="display: none;"></div>

    <script src="{{ asset('js/events/step6.js') }}?v={{ time() }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Apply ticket colors after page loads
            function applyTicketColors() {
                @if(!empty($eventData['ticket_types']) && count($eventData['ticket_types']) > 0)
                    const primaryColor = '{{ $primaryColor }}';
                    const secondaryColor = '{{ $secondaryColor }}';
                    const accentColor = '{{ $accentColor }}';
                    
                    console.log('Applying ticket colors:', {
                        primary: primaryColor,
                        secondary: secondaryColor,
                        accent: accentColor
                    });
                    
                    // Apply colors to ticket preview elements
                    const mobileHeader = document.querySelector('.ticket-mobile-header');
                    const mobileOverlay = document.querySelector('.ticket-mobile-overlay');
                    const desktopHeader = document.querySelector('.ticket-desktop-header');
                    const desktopOverlay = document.querySelector('.ticket-desktop-overlay');
                    const statusBadges = document.querySelectorAll('.ticket-status-badge');
                    
                    console.log('Found elements:', {
                        mobileHeader: !!mobileHeader,
                        mobileOverlay: !!mobileOverlay,
                        desktopHeader: !!desktopHeader,
                        desktopOverlay: !!desktopOverlay,
                        statusBadges: statusBadges.length
                    });
                    
                    if (mobileHeader) {
                        mobileHeader.style.setProperty('background', `linear-gradient(to right, ${primaryColor}, ${secondaryColor})`, 'important');
                        console.log('Applied mobile header color');
                    }
                    if (mobileOverlay) {
                        mobileOverlay.style.setProperty('background', `linear-gradient(to right, ${primaryColor}cc, ${secondaryColor}cc)`, 'important');
                        console.log('Applied mobile overlay color');
                    }
                    if (desktopHeader) {
                        desktopHeader.style.setProperty('background', `linear-gradient(to right, ${primaryColor}, ${secondaryColor})`, 'important');
                        console.log('Applied desktop header color');
                    }
                    if (desktopOverlay) {
                        desktopOverlay.style.setProperty('background', `linear-gradient(to right, ${primaryColor}60, ${secondaryColor}60)`, 'important');
                        console.log('Applied desktop overlay color');
                    }
                    statusBadges.forEach((badge, index) => {
                        badge.style.setProperty('background-color', accentColor, 'important');
                        console.log(`Applied accent color to badge ${index}`);
                    });
                @endif
            }
            
            // Apply colors immediately and also after a short delay
            applyTicketColors();
            setTimeout(applyTicketColors, 100);
            setTimeout(applyTicketColors, 500);
        });

        // Handle publish options
        const publishOptions = document.querySelectorAll('input[name="publish_option"]');
        const scheduleInput = document.getElementById('schedule-input');
        const publishButtonText = document.getElementById('publish-button-text');

        publishOptions.forEach(option => {
            option.addEventListener('change', function() {
                if (this.value === 'scheduled') {
                    scheduleInput.classList.remove('hidden');
                } else {
                    scheduleInput.classList.add('hidden');
                }

                // Update button text based on selection
                switch(this.value) {
                    case 'draft':
                        publishButtonText.textContent = 'Save as Draft';
                        break;
                    case 'pending_approval':
                        publishButtonText.textContent = 'Submit for Approval';
                        break;
                    case 'scheduled':
                        publishButtonText.textContent = 'Schedule Publication';
                        break;
                }
            });
        });

        // Note: Date validation is handled in step6.js
    </script>
</div>

