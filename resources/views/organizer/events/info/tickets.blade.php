<x-unified-layout :layout-type="$layoutType" :title="'Ticket Information - ' . $event->title" :page-title="'Ticket Information'">
    <x-slot name="header">
        <x-manage-header :event="$event" />
    </x-slot>
    
    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Ticket Information</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">View ticket types and pricing information</p>
                    </div>

                    @if($ticketTypes && count($ticketTypes) > 0)
                        <!-- Ticket Preview Section -->
                        <div class="mb-8">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Ticket Preview</h4>
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                @include('organizer.events.preview.ticket-types-preview', [
                                    'ticketTypes' => $ticketTypes,
                                    'event' => $event,
                                    'ticketSettings' => $ticketSettings
                                ])
                            </div>
                        </div>

                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No ticket types configured</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This event doesn't have any ticket types set up yet.</p>
                        </div>
                    @endif

                    @if($ticketSettings)
                        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Ticket Settings</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                @if(isset($ticketSettings['max_tickets_per_order']))
                                    <div>
                                        <span class="font-medium text-gray-700 dark:text-gray-300">Max tickets per order:</span>
                                        <span class="text-gray-900 dark:text-gray-100">{{ $ticketSettings['max_tickets_per_order'] }}</span>
                                    </div>
                                @endif
                                @if(isset($ticketSettings['allow_refunds']))
                                    <div>
                                        <span class="font-medium text-gray-700 dark:text-gray-300">Allow refunds:</span>
                                        <span class="text-gray-900 dark:text-gray-100">{{ $ticketSettings['allow_refunds'] ? 'Yes' : 'No' }}</span>
                                    </div>
                                @endif
                                @if(isset($ticketSettings['require_approval']))
                                    <div>
                                        <span class="font-medium text-gray-700 dark:text-gray-300">Require approval:</span>
                                        <span class="text-gray-900 dark:text-gray-100">{{ $ticketSettings['require_approval'] ? 'Yes' : 'No' }}</span>
                                    </div>
                                @endif
                                @if(isset($ticketSettings['allow_share_ticket']))
                                    <div>
                                        <span class="font-medium text-gray-700 dark:text-gray-300">Allow ticket sharing:</span>
                                        <span class="text-gray-900 dark:text-gray-100">{{ $ticketSettings['allow_share_ticket'] ? 'Yes' : 'No' }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-unified-layout>
