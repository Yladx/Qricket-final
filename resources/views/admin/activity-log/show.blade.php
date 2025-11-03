<x-admin-layout>
    <x-slot name="title">Activity Log Details</x-slot>
    <x-slot name="header">
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Activity Log Details</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Detailed information about this administrative action.</p>
                </div>
                <a href="{{ route('admin.activity-log.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Activity Log
                </a>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Activity Details -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Activity Information</h3>
                </div>
                
                <div class="p-6">
                    <div class="space-y-6">
                        <!-- Action -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Action</label>
                            @php
                                $color = match(true) {
                                    str_contains($log->action, 'create') => 'green',
                                    str_contains($log->action, 'update') || str_contains($log->action, 'approve') => 'blue',
                                    str_contains($log->action, 'delete') || str_contains($log->action, 'reject') => 'red',
                                    str_contains($log->action, 'login') => 'purple',
                                    default => 'gray'
                                };
                                $bg = "bg-{$color}-100"; 
                                $text = "text-{$color}-700";
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $bg }} {{ $text }}">
                                {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                            </span>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $log->description ?? 'No description provided' }}</p>
                        </div>

                        <!-- Timestamp -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date & Time</label>
                            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $log->created_at->format('F j, Y \a\t g:i A') }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $log->created_at->diffForHumans() }}</p>
                        </div>

                        <!-- Metadata -->
                        @if($log->metadata && count($log->metadata) > 0)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Additional Information</label>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <div class="space-y-2">
                                        @foreach($log->metadata as $key => $value)
                                            <div class="flex">
                                                <span class="font-medium text-gray-700 dark:text-gray-300 w-32">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                <span class="text-gray-900 dark:text-gray-100 flex-1">
                                                    @if(is_array($value))
                                                        <pre class="text-xs bg-white dark:bg-gray-800 p-2 rounded border overflow-x-auto">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                                    @else
                                                        {{ $value }}
                                                    @endif
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Technical Details Sidebar -->
        <div class="space-y-6">
            <!-- Technical Information -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Technical Details</h3>
                </div>
                
                <div class="p-6 space-y-4">
                    <!-- IP Address -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">IP Address</label>
                        <p class="text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $log->ip_address }}</p>
                    </div>

                    <!-- User Agent -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">User Agent</label>
                        <p class="text-xs text-gray-600 dark:text-gray-400 break-all">{{ $log->user_agent }}</p>
                    </div>

                    <!-- Log ID -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Log ID</label>
                        <p class="text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $log->id }}</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Quick Actions</h3>
                </div>
                
                <div class="p-6 space-y-3">
                    <a href="{{ route('admin.activity-log.index') }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-qricket-600 text-white rounded-md hover:bg-qricket-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        View All Activities
                    </a>
                    
                    <button onclick="window.print()" class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print Details
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
