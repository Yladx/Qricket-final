<x-admin-layout title="Verification Details - Qrickett Admin" page-title="Verification Details">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                    Verification Request Details
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Review verification request from {{ $verification->user->first_name }} {{ $verification->user->last_name }}
                </p>
            </div>
            
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.verifications') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-500 focus:bg-gray-400 dark:focus:bg-gray-500 active:bg-gray-500 dark:active:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    Back to List
                </a>
                
                @if($verification->status === 'pending')
                    <form method="POST" action="{{ route('admin.verification.approve', $verification) }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                                onclick="event.preventDefault(); if (typeof Swal !== 'undefined' && window.Swal) { Swal.fire({ title: 'Approve Verification?', text: 'Are you sure you want to approve this verification?', icon: 'question', showCancelButton: true, confirmButtonText: 'Yes, approve', cancelButtonText: 'Cancel', confirmButtonColor: '#059669', cancelButtonColor: '#6b7280' }).then((result) => { if (result.isConfirmed) { event.target.closest('form').submit(); } }); } else { if (confirm('Are you sure you want to approve this verification?')) { event.target.closest('form').submit(); } } return false;">
                            Approve Verification
                        </button>
                    </form>
                    
                    <button type="button" 
                            onclick="showRejectModal()"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        Reject Verification
                    </button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- User Profile & Organization Information (Combined Row) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- User Information -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                User Information
            </h3>
            
            <div class="space-y-4">
                <div class="flex items-center">
                        <a href="{{ route('admin.users.show', $verification->user->id) }}" class="flex items-center hover:opacity-80 transition-opacity">
                    <div class="flex-shrink-0 h-12 w-12">
                        <div class="h-12 w-12 rounded-full bg-qricket-100 dark:bg-qricket-900 flex items-center justify-center">
                            <span class="text-lg font-medium text-qricket-600 dark:text-qricket-400">
                                {{ substr($verification->user->first_name, 0, 1) }}{{ substr($verification->user->last_name, 0, 1) }}
                            </span>
                        </div>
                    </div>
                    <div class="ml-4">
                                <div class="text-lg font-medium text-gray-900 dark:text-gray-100 hover:text-qricket-600 dark:hover:text-qricket-400 transition-colors">
                            {{ $verification->user->first_name }} {{ $verification->user->last_name }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $verification->user->email }}
                        </div>
                    </div>
                        </a>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Registration Date</label>
                        <p class="text-sm text-gray-900 dark:text-gray-100">
                            {{ $verification->user->created_at->format('M d, Y') }}
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Status</label>
                        <p class="text-sm text-gray-900 dark:text-gray-100">
                            @if($verification->user->isVerifiedOrganizer())
                                <span class="text-green-600 dark:text-green-400">Verified Organizer</span>
                            @else
                                <span class="text-yellow-600 dark:text-yellow-400">Pending Verification</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Organization Information -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                Organization Information
            </h3>
            
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Organization Name</label>
                    <p class="text-sm text-gray-900 dark:text-gray-100">
                        {{ $verification->organization_name }}
                    </p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</label>
                    <p class="text-sm text-gray-900 dark:text-gray-100">
                        {{ $verification->organization_description }}
                    </p>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Contact Phone</label>
                        <p class="text-sm text-gray-900 dark:text-gray-100">
                            {{ $verification->contact_phone }}
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Website</label>
                        <p class="text-sm text-gray-900 dark:text-gray-100">
                            @if($verification->website)
                                <a href="{{ $verification->website }}" target="_blank" class="text-qricket-600 hover:text-qricket-900 dark:text-qricket-400 dark:hover:text-qricket-300">
                                    {{ $verification->website }}
                                </a>
                            @else
                                <span class="text-gray-500 dark:text-gray-400">Not provided</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <!-- Personal Information -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                Personal Information
            </h3>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-4">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Full Name</label>
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                {{ $verification->full_name }}
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Birthdate</label>
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                {{ $verification->birthdate ? $verification->birthdate->format('M d, Y') : 'Not provided' }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Nationality</label>
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                {{ $verification->nationality }}
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Country</label>
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                {{ $verification->country }}
                            </p>
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Address</label>
                        <p class="text-sm text-gray-900 dark:text-gray-100">
                            {{ $verification->address }}
                        </p>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">City</label>
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                {{ $verification->city }}
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">State/Province</label>
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                {{ $verification->state_province }}
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Postal Code</label>
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                {{ $verification->postal_code }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Father's Name</label>
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                {{ $verification->father_name }}
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Mother's Name</label>
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                {{ $verification->mother_name }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Occupation</label>
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                {{ $verification->occupation }}
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Employer</label>
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                {{ $verification->employer ?: 'Not provided' }}
                            </p>
                        </div>
                    </div>
                    
                    @if($verification->professional_experience)
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Professional Experience</label>
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                {{ $verification->professional_experience }}
                            </p>
                        </div>
                    @endif
                    
                    @if($verification->alternate_email)
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Alternate Email</label>
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                {{ $verification->alternate_email }}
                            </p>
                        </div>
                    @endif
                    
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Emergency Contact</label>
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                {{ $verification->emergency_contact_name }}
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Emergency Phone</label>
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                {{ $verification->emergency_contact_phone }}
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Relationship</label>
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                {{ ucfirst($verification->emergency_contact_relationship) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Document Information & Verification Status (Combined Row) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Document Information -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                Document Information
            </h3>
            
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Document Type</label>
                    <p class="text-sm text-gray-900 dark:text-gray-100">
                        {{ ucfirst(str_replace('_', ' ', $verification->document_type)) }}
                    </p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Document</label>
                    <div class="mt-2">
                        @if($verification->isPdf())
                            <!-- PDF Viewer -->
                            <div class="space-y-3">
                                <a href="{{ $verification->download_url }}" target="_blank" 
                                   class="inline-flex items-center px-4 py-2 bg-qricket-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-qricket-700 focus:bg-qricket-700 active:bg-qricket-800 focus:outline-none focus:ring-2 focus:ring-qricket-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Download PDF
                                </a>
                                
                                <!-- Embedded PDF Viewer -->
                                <div class="border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
                                    @if($verification->document_url !== '#')
                                        <iframe src="{{ $verification->document_url }}#toolbar=1&navpanes=0&scrollbar=1" 
                                                class="w-full h-96" 
                                                frameborder="0"
                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                            <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                                                <p>Unable to display PDF in browser.</p>
                                                <a href="{{ $verification->download_url }}" target="_blank" 
                                                   class="text-qricket-600 hover:text-qricket-800 dark:text-qricket-400 dark:hover:text-qricket-300">
                                                    Click here to download the PDF
                                                </a>
                                            </div>
                                        </iframe>
                                        <div class="p-4 text-center text-gray-500 dark:text-gray-400" style="display: none;">
                                            <p>Unable to display PDF in browser.</p>
                                            <a href="{{ $verification->download_url }}" target="_blank" 
                                               class="text-qricket-600 hover:text-qricket-800 dark:text-qricket-400 dark:hover:text-qricket-300">
                                                Click here to download the PDF
                                            </a>
                                        </div>
                                    @else
                                        <div class="p-4 text-center text-red-500">
                                            <p>Error: Unable to access document.</p>
                                            <p class="text-sm">Please contact support if this issue persists.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @elseif($verification->isImage())
                            <!-- Image Viewer -->
                            <div class="space-y-4">
                                <!-- Front Document -->
                                @if($verification->document_front_path)
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Front Document</h4>
                                        <div class="space-y-2">
                                            <a href="{{ $verification->getDocumentFrontUrlAttribute() }}" target="_blank" 
                                               class="inline-flex items-center px-3 py-2 bg-qricket-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-qricket-700 focus:bg-qricket-700 active:bg-qricket-800 focus:outline-none focus:ring-2 focus:ring-qricket-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                View Front Full Size
                                            </a>
                                            
                                            <div class="border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
                                                <img src="{{ $verification->getDocumentFrontUrlAttribute() }}" 
                                                     alt="Front Document" 
                                                     class="w-full h-auto max-h-64 object-contain bg-gray-50 dark:bg-gray-800"
                                                     onclick="window.open('{{ $verification->getDocumentFrontUrlAttribute() }}', '_blank')"
                                                     style="cursor: pointer;">
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Back Document -->
                                @if($verification->document_back_path)
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Back Document</h4>
                                        <div class="space-y-2">
                                            <a href="{{ $verification->getDocumentBackUrlAttribute() }}" target="_blank" 
                                               class="inline-flex items-center px-3 py-2 bg-qricket-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-qricket-700 focus:bg-qricket-700 active:bg-qricket-800 focus:outline-none focus:ring-2 focus:ring-qricket-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                View Back Full Size
                                            </a>
                                            
                                            <div class="border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
                                                <img src="{{ $verification->getDocumentBackUrlAttribute() }}" 
                                                     alt="Back Document" 
                                                     class="w-full h-auto max-h-64 object-contain bg-gray-50 dark:bg-gray-800"
                                                     onclick="window.open('{{ $verification->getDocumentBackUrlAttribute() }}', '_blank')"
                                                     style="cursor: pointer;">
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Fallback for old single document format -->
                                @if(!$verification->document_front_path && $verification->document_file_path)
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Document</h4>
                                        <div class="space-y-2">
                                            <a href="{{ $verification->document_url }}" target="_blank" 
                                               class="inline-flex items-center px-3 py-2 bg-qricket-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-qricket-700 focus:bg-qricket-700 active:bg-qricket-800 focus:outline-none focus:ring-2 focus:ring-qricket-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                View Full Size
                                            </a>
                                            
                                            <div class="border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
                                                <img src="{{ $verification->document_url }}" 
                                                     alt="Verification Document" 
                                                     class="w-full h-auto max-h-96 object-contain bg-gray-50 dark:bg-gray-800"
                                                     onclick="window.open('{{ $verification->document_url }}', '_blank')"
                                                     style="cursor: pointer;">
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <!-- Fallback for other file types -->
                            <a href="{{ $verification->document_url }}" target="_blank" 
                               class="inline-flex items-center px-4 py-2 bg-qricket-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-qricket-700 focus:bg-qricket-700 active:bg-qricket-800 focus:outline-none focus:ring-2 focus:ring-qricket-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View Document
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Verification Status -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                Verification Status
            </h3>
            
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Status</label>
                    <div class="mt-1">
                        @if($verification->status === 'pending')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                Pending Review
                            </span>
                        @elseif($verification->status === 'approved')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                Approved
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                Rejected
                            </span>
                        @endif
                    </div>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Submitted</label>
                    <p class="text-sm text-gray-900 dark:text-gray-100">
                        {{ $verification->created_at->format('M d, Y \a\t g:i A') }}
                    </p>
                </div>
                
                @if($verification->reviewed_at)
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Reviewed</label>
                        <p class="text-sm text-gray-900 dark:text-gray-100">
                            {{ $verification->reviewed_at->format('M d, Y \a\t g:i A') }}
                        </p>
                    </div>
                    
                    @if($verification->reviewer)
                        <div>
                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Reviewed By</label>
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                {{ $verification->reviewer->username }}
                            </p>
                        </div>
                    @endif
                @endif
                
                @if($verification->rejection_reason)
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Rejection Reason</label>
                        <p class="text-sm text-gray-900 dark:text-gray-100">
                            {{ $verification->rejection_reason }}
                        </p>
                    </div>
                @endif
            </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Reject Verification Request
                </h3>
                
                <form method="POST" action="{{ route('admin.verification.reject', $verification) }}">
                    @csrf
                    <div class="mb-4">
                        <label for="rejection_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Rejection Reason
                        </label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-qricket-500 focus:border-qricket-500 dark:bg-gray-700 dark:text-gray-300"
                                  placeholder="Please provide a reason for rejection..."
                                  required></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" 
                                onclick="hideRejectModal()"
                                class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-400 dark:hover:bg-gray-500">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Reject Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
            function showRejectModal() {
                document.getElementById('rejectModal').classList.remove('hidden');
            }
            
            function hideRejectModal() {
                document.getElementById('rejectModal').classList.add('hidden');
                document.getElementById('rejection_reason').value = '';
            }
            
            // Close modal when clicking outside
            document.getElementById('rejectModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    hideRejectModal();
                }
            });
        </script>
    </x-slot>
</x-admin-layout>
