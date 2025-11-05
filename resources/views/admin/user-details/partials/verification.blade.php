@php
    $verification = \App\Models\OrganizerVerification::where('user_id', $user->id)->latest()->first();
@endphp

@if($verification)
    <div class="space-y-6">
        <!-- Status Overview -->
        <div class="bg-gradient-to-r from-qricket-50 to-teal-50 dark:from-qricket-900/20 dark:to-teal-900/20 border border-qricket-200 dark:border-qricket-700 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Verification Status</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Submitted on {{ $verification->created_at->format('M j, Y') }}</p>
                </div>
                <div class="text-right">
                    @if($verification->status === 'approved')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Approved
                        </span>
                        <form method="POST" action="{{ route('admin.users.unverify-organizer', $user) }}" class="inline-block ml-3">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium bg-red-50 text-red-700 hover:bg-red-100 border border-red-200">
                                Unverify
                            </button>
                        </form>
                    @elseif($verification->status === 'pending')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                            <svg class="w-4 h-4 mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Pending Review
                        </span>
                    @elseif($verification->status === 'rejected')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                            Rejected
                        </span>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Verification Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Business Details -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Business Details</h4>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Business Name</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $verification->business_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Business Type</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $verification->business_type }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Tax ID</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $verification->tax_id ?? 'N/A' }}</dd>
                    </div>
                </dl>
            </div>
            
            <!-- Contact Details -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Contact Details</h4>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Email</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $verification->business_email }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Phone</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $verification->business_phone }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Address</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $verification->business_address }}</dd>
                    </div>
                </dl>
            </div>
        </div>
        
        <!-- Documents -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Verification Documents</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($verification->business_license)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-100">Business License</span>
                            </div>
                            <a href="{{ Storage::disk('r2')->url($verification->business_license) }}" target="_blank" class="text-qricket-600 hover:text-qricket-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                @endif
                
                @if($verification->identity_document)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                </svg>
                                <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-100">Identity Document</span>
                            </div>
                            <a href="{{ Storage::disk('r2')->url($verification->identity_document) }}" target="_blank" class="text-qricket-600 hover:text-qricket-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        @if($verification->admin_notes)
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 mb-2">Admin Notes</h4>
                <p class="text-sm text-yellow-700 dark:text-yellow-300">{{ $verification->admin_notes }}</p>
            </div>
        @endif
    </div>
@else
    <div class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No Verification Request</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This user hasn't submitted an organizer verification request yet.</p>
    </div>
@endif

