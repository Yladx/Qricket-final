<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Organizer Verification') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    @if($verification && $verification->isPending())
                        <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                <div>
                                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                        Verification Request Pending
                                    </h3>
                                    <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                                        Your verification request is currently under review. We will notify you once it's processed.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @elseif($verification && $verification->isApproved())
                        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
                                        Verification Approved
                                    </h3>
                                    <p class="text-sm text-green-700 dark:text-green-300 mt-1">
                                        Congratulations! You are now a verified organizer. You can access organizer features.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(!$verification || $verification->isRejected())
                        @if($verification && $verification->isRejected())
                            @php
                                $canResubmit = !$verification->reviewed_at || $verification->reviewed_at->addDay()->isPast();
                                $cooldownEnd = $verification->reviewed_at ? $verification->reviewed_at->addDay() : now();
                                $timeRemaining = $cooldownEnd->isFuture() ? $cooldownEnd->diff(now()) : null;
                                $hoursLeft = $timeRemaining ? $timeRemaining->h : 0;
                                $minutesLeft = $timeRemaining ? $timeRemaining->i : 0;
                                $secondsLeft = $timeRemaining ? $timeRemaining->s : 0;
                                $totalMinutesElapsed = $verification->reviewed_at ? now()->diffInMinutes($verification->reviewed_at) : 0;
                                $progressPercentage = min(($totalMinutesElapsed / 1440) * 100, 100);
                            @endphp
                            
                            @if(!$canResubmit)
                                <div class="mb-6 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-lg p-6">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <svg class="h-6 w-6 text-rose-400 dark:text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <h3 class="text-lg font-semibold text-rose-800 dark:text-rose-200 mb-2">Verification Rejected</h3>
                                            
                                            @if($verification->rejection_reason)
                                                <div class="mb-4 p-3 bg-rose-100 dark:bg-rose-900/30 rounded-lg border border-rose-200 dark:border-rose-700">
                                                    <p class="text-sm text-rose-700 dark:text-rose-300">
                                                        <strong>Reason:</strong> {{ $verification->rejection_reason }}
                                                    </p>
                                                </div>
                                            @endif
                                            
                                            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-rose-200 dark:border-rose-700">
                                                <div class="text-center mb-4">
                                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Time remaining until you can resubmit:</p>
                                                    <div id="countdown-timer" class="text-3xl font-bold text-rose-600 dark:text-rose-400 font-mono">
                                                        {{ sprintf('%02d:%02d:%02d', $hoursLeft, $minutesLeft, $secondsLeft) }}
                                                    </div>
                                                </div>
                                                
                                                <div class="space-y-2">
                                                    <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400">
                                                        <span>Cooldown Progress</span>
                                                        <span id="progress-text">{{ number_format($progressPercentage, 1) }}%</span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                                                        <div id="progress-bar" class="bg-gradient-to-r from-rose-400 to-rose-500 h-3 rounded-full transition-all duration-1000 ease-out" style="width: {{ $progressPercentage }}%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Hide the form during cooldown -->
                                <div class="text-center py-12 bg-gray-50 dark:bg-gray-800/50 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
                                    <div class="mx-auto w-16 h-16 bg-rose-100 dark:bg-rose-900/30 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-rose-500 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m8-6V7a2 2 0 00-2-2H8a2 2 0 00-2 2v4m12 0v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6m12 0H4"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Verification Form Locked</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6 max-w-md mx-auto">
                                        Please wait for the cooldown period to end before submitting a new verification request. The timer above shows exactly when you can resubmit.
                                    </p>
                                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                        <button onclick="location.reload()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-qricket-600 hover:bg-qricket-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-qricket-500 transition-colors duration-200" aria-label="Refresh page">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            Refresh Page
                                        </button>
                                        <button onclick="window.location.href='{{ route('dashboard') }}'" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-qricket-500 transition-colors duration-200" aria-label="Go to dashboard">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                                            </svg>
                                            Go to Dashboard
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="mb-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 text-yellow-400 dark:text-yellow-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                        <div>
                                            <p class="text-sm text-yellow-800 dark:text-yellow-200 font-medium">Previous Verification Rejected</p>
                                            <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                                                You can now resubmit your verification request.
                                            </p>
                                            @if($verification->rejection_reason)
                                                <p class="text-sm text-yellow-600 dark:text-yellow-400 mt-2">
                                                    <strong>Previous Reason:</strong> {{ $verification->rejection_reason }}
                                                </p>
                                            @endif
                                </div>
                            </div>
                        </div>
                            @endif
                    @endif

                        @if(!$verification || !$verification->isRejected() || ($verification->isRejected() && (!$verification->reviewed_at || $verification->reviewed_at->addDay()->isPast())))
                        <form method="POST" action="{{ route('organizer.verification.store') }}" enctype="multipart/form-data" class="space-y-6">
                            @csrf
                            
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                                    Document Verification
                                </h3>
                                
                                <!-- Document Type Selection -->
                                <div class="mb-6">
                                        <label for="document_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Document Type
                                        </label>
                                        <select id="document_type" name="document_type" 
                                                class="mt-1 block w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 sm:text-sm"
                                                required>
                                            <option value="">Select document type</option>
                                            <option value="id_card" {{ old('document_type') == 'id_card' ? 'selected' : '' }}>National ID Card (Front & Back Required)</option>
                                            <option value="passport" {{ old('document_type') == 'passport' ? 'selected' : '' }}>Passport (Front Only)</option>
                                            <option value="business_license" {{ old('document_type') == 'business_license' ? 'selected' : '' }}>Business License (Front & Back Required)</option>
                                            <option value="drivers_license" {{ old('document_type') == 'drivers_license' ? 'selected' : '' }}>Driver's License (Front Only)</option>
                                            <option value="school_id" {{ old('document_type') == 'school_id' ? 'selected' : '' }}>School ID (Front & Back Required)</option>
                                            <option value="birth_certificate" {{ old('document_type') == 'birth_certificate' ? 'selected' : '' }}>Birth Certificate (Front Only)</option>
                                        </select>
                                        <x-input-error class="mt-2" :messages="$errors->get('document_type')" />
                                    </div>
                                    
                                <!-- Document Upload Section -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="document_front" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Upload Document Front Image
                                            </label>
                                            <input type="file" id="document_front" name="document_front" 
                                                   accept=".jpg,.jpeg,.png"
                                                   class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-qricket-50 file:text-qricket-700 hover:file:bg-qricket-100 dark:file:bg-qricket-900 dark:file:text-qricket-300"
                                                   required>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                <strong>Accepted formats:</strong> JPG, PNG only (Max 10MB)
                                            </p>
                                            <x-input-error class="mt-2" :messages="$errors->get('document_front')" />
                                        </div>
                                        
                                        <div id="back-document-section" style="display: none;">
                                            <label for="document_back" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Upload Document Back Image
                                            </label>
                                            <input type="file" id="document_back" name="document_back" 
                                                   accept=".jpg,.jpeg,.png"
                                                   class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-qricket-50 file:text-qricket-700 hover:file:bg-qricket-100 dark:file:bg-qricket-900 dark:file:text-qricket-300">
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                <strong>Required for:</strong> National ID Card, Business License, School ID
                                            </p>
                                            <x-input-error class="mt-2" :messages="$errors->get('document_back')" />
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                                    Personal Information
                                </h3>
                                
                                <div class="space-y-4">
                                    <!-- Full Name -->
                                    <div>
                                        <label for="full_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Full Name <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" id="full_name" name="full_name" 
                                               value="{{ old('full_name') }}"
                                               class="mt-1 block w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 sm:text-sm"
                                               placeholder="Enter your full legal name"
                                               required>
                                        <x-input-error class="mt-2" :messages="$errors->get('full_name')" />
                                    </div>
                                    
                                    <!-- Birthdate and Nationality Row -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="birthdate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Birthdate <span class="text-red-500">*</span>
                                            </label>
                                            <input type="date" id="birthdate" name="birthdate" 
                                                   value="{{ old('birthdate') }}"
                                                   class="mt-1 block w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 sm:text-sm"
                                                   required>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                <strong>Note:</strong> You must be at least 18 years old to become a verified organizer.
                                            </p>
                                            <x-input-error class="mt-2" :messages="$errors->get('birthdate')" />
                                        </div>
                                        
                                        <div>
                                            <label for="nationality" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Nationality <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" id="nationality" name="nationality" 
                                                   value="{{ old('nationality') }}"
                                                   class="mt-1 block w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 sm:text-sm"
                                                   placeholder="e.g., Filipino, American, etc."
                                                   required>
                                            <x-input-error class="mt-2" :messages="$errors->get('nationality')" />
                                        </div>
                                    </div>
                                    
                                    <!-- Address Information -->
                                    <div>
                                        <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Address <span class="text-red-500">*</span>
                                        </label>
                                        <textarea id="address" name="address" rows="2"
                                                  class="mt-1 block w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 sm:text-sm"
                                                  placeholder="Enter your complete address"
                                                  required>{{ old('address') }}</textarea>
                                        <x-input-error class="mt-2" :messages="$errors->get('address')" />
                                    </div>
                                    
                                    <!-- City, State, Postal Code, Country Row -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                        <div>
                                            <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                City <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" id="city" name="city" 
                                                   value="{{ old('city') }}"
                                                   class="mt-1 block w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 sm:text-sm"
                                                   placeholder="City"
                                                   required>
                                            <x-input-error class="mt-2" :messages="$errors->get('city')" />
                                        </div>
                                        
                                        <div>
                                            <label for="state_province" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                State/Province <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" id="state_province" name="state_province" 
                                                   value="{{ old('state_province') }}"
                                                   class="mt-1 block w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 sm:text-sm"
                                                   placeholder="State/Province"
                                                   required>
                                            <x-input-error class="mt-2" :messages="$errors->get('state_province')" />
                                        </div>
                                        
                                        <div>
                                            <label for="postal_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Postal Code <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" id="postal_code" name="postal_code" 
                                                   value="{{ old('postal_code') }}"
                                                   class="mt-1 block w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 sm:text-sm"
                                                   placeholder="Postal Code"
                                                   required>
                                            <x-input-error class="mt-2" :messages="$errors->get('postal_code')" />
                                        </div>
                                        
                                        <div>
                                            <label for="country" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Country <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" id="country" name="country" 
                                                   value="{{ old('country') }}"
                                                   class="mt-1 block w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 sm:text-sm"
                                                   placeholder="Country"
                                                   required>
                                            <x-input-error class="mt-2" :messages="$errors->get('country')" />
                                        </div>
                                    </div>
                                    
                                    <!-- Family Information -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="father_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Father's Name <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" id="father_name" name="father_name" 
                                                   value="{{ old('father_name') }}"
                                                   class="mt-1 block w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 sm:text-sm"
                                                   placeholder="Enter your father's full name"
                                                   required>
                                            <x-input-error class="mt-2" :messages="$errors->get('father_name')" />
                                        </div>
                                        
                                        <div>
                                            <label for="mother_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Mother's Name <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" id="mother_name" name="mother_name" 
                                                   value="{{ old('mother_name') }}"
                                                   class="mt-1 block w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 sm:text-sm"
                                                   placeholder="Enter your mother's full name"
                                                   required>
                                            <x-input-error class="mt-2" :messages="$errors->get('mother_name')" />
                                        </div>
                                    </div>
                                    
                                    <!-- Professional Information -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="occupation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Occupation <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" id="occupation" name="occupation" 
                                                   value="{{ old('occupation') }}"
                                                   class="mt-1 block w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 sm:text-sm"
                                                   placeholder="Your current occupation"
                                                   required>
                                            <x-input-error class="mt-2" :messages="$errors->get('occupation')" />
                                        </div>
                                        
                                        <div>
                                            <label for="employer" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Employer/Company
                                            </label>
                                            <input type="text" id="employer" name="employer" 
                                                   value="{{ old('employer') }}"
                                                   class="mt-1 block w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 sm:text-sm"
                                                   placeholder="Your employer or company name">
                                            <x-input-error class="mt-2" :messages="$errors->get('employer')" />
                                        </div>
                                    </div>
                                    
                                    <!-- Professional Experience -->
                                    <div>
                                        <label for="professional_experience" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Professional Experience
                                        </label>
                                        <textarea id="professional_experience" name="professional_experience" rows="3"
                                                  class="mt-1 block w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 sm:text-sm"
                                                  placeholder="Briefly describe your professional background and experience">{{ old('professional_experience') }}</textarea>
                                        <x-input-error class="mt-2" :messages="$errors->get('professional_experience')" />
                                    </div>
                                    
                                    <!-- Additional Contact Information -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="alternate_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Alternate Email
                                            </label>
                                            <input type="email" id="alternate_email" name="alternate_email" 
                                                   value="{{ old('alternate_email') }}"
                                                   class="mt-1 block w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 sm:text-sm"
                                                   placeholder="Alternative email address">
                                            <x-input-error class="mt-2" :messages="$errors->get('alternate_email')" />
                                        </div>
                                        
                                        <div>
                                            <label for="emergency_contact_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Emergency Contact Name <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" id="emergency_contact_name" name="emergency_contact_name" 
                                                   value="{{ old('emergency_contact_name') }}"
                                                   class="mt-1 block w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 sm:text-sm"
                                                   placeholder="Emergency contact person's name"
                                                   required>
                                            <x-input-error class="mt-2" :messages="$errors->get('emergency_contact_name')" />
                                        </div>
                                    </div>
                                    
                                    <!-- Emergency Contact Details -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="emergency_contact_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Emergency Contact Phone <span class="text-red-500">*</span>
                                            </label>
                                            <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" 
                                                   value="{{ old('emergency_contact_phone') }}"
                                                   class="mt-1 block w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 sm:text-sm"
                                                   placeholder="Emergency contact phone number"
                                                   required>
                                            <x-input-error class="mt-2" :messages="$errors->get('emergency_contact_phone')" />
                                        </div>
                                        
                                        <div>
                                            <label for="emergency_contact_relationship" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Relationship <span class="text-red-500">*</span>
                                            </label>
                                            <select id="emergency_contact_relationship" name="emergency_contact_relationship" 
                                                    class="mt-1 block w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 sm:text-sm"
                                                    required>
                                                <option value="">Select relationship</option>
                                                <option value="spouse" {{ old('emergency_contact_relationship') == 'spouse' ? 'selected' : '' }}>Spouse</option>
                                                <option value="parent" {{ old('emergency_contact_relationship') == 'parent' ? 'selected' : '' }}>Parent</option>
                                                <option value="sibling" {{ old('emergency_contact_relationship') == 'sibling' ? 'selected' : '' }}>Sibling</option>
                                                <option value="child" {{ old('emergency_contact_relationship') == 'child' ? 'selected' : '' }}>Child</option>
                                                <option value="friend" {{ old('emergency_contact_relationship') == 'friend' ? 'selected' : '' }}>Friend</option>
                                                <option value="other" {{ old('emergency_contact_relationship') == 'other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                            <x-input-error class="mt-2" :messages="$errors->get('emergency_contact_relationship')" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                                    Organization Information
                                </h3>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label for="organization_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Organization Name
                                        </label>
                                        <input type="text" id="organization_name" name="organization_name" 
                                               value="{{ old('organization_name') }}"
                                               class="mt-1 block w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 sm:text-sm"
                                               placeholder="Enter your organization name"
                                               required>
                                        <x-input-error class="mt-2" :messages="$errors->get('organization_name')" />
                                    </div>
                                    
                                    <div>
                                        <label for="organization_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Organization Description
                                        </label>
                                        <textarea id="organization_description" name="organization_description" rows="4"
                                                  class="mt-1 block w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 sm:text-sm"
                                                  placeholder="Describe your organization and the types of events you organize"
                                                  required>{{ old('organization_description') }}</textarea>
                                        <x-input-error class="mt-2" :messages="$errors->get('organization_description')" />
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="contact_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Contact Phone
                                            </label>
                                            <input type="tel" id="contact_phone" name="contact_phone" 
                                                   value="{{ old('contact_phone') }}"
                                                   class="mt-1 block w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 sm:text-sm"
                                                   placeholder="+1 (555) 123-4567"
                                                   required>
                                            <x-input-error class="mt-2" :messages="$errors->get('contact_phone')" />
                                        </div>
                                        
                                        <div>
                                            <label for="website" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Website (Optional)
                                            </label>
                                            <input type="url" id="website" name="website" 
                                                   value="{{ old('website') }}"
                                                   class="mt-1 block w-full rounded-md bg-white text-gray-900 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 sm:text-sm"
                                                   placeholder="https://your-website.com">
                                            <x-input-error class="mt-2" :messages="$errors->get('website')" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Terms and Conditions Section -->
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                                    Terms and Conditions
                                </h3>
                                
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 max-h-60 overflow-y-auto border border-gray-200 dark:border-gray-600">
                                    <div id="terms-content" class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">
                                        <div class="animate-pulse">
                                            <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded w-3/4 mb-2"></div>
                                            <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded w-1/2 mb-2"></div>
                                            <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded w-5/6"></div>
                                        </div>
                                        <p class="text-center text-gray-500 dark:text-gray-400 mt-4">Loading terms and conditions...</p>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <label class="flex items-start">
                                        <input type="checkbox" name="terms_accepted" value="1" 
                                               class="mt-1 rounded border-gray-300 dark:border-gray-600 text-qricket-600 shadow-sm focus:border-qricket-500 focus:ring-qricket-500 dark:bg-gray-700 dark:checked:bg-qricket-600"
                                               required>
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                            I have carefully read and understood the <button type="button" id="view-terms-btn" class="text-qricket-600 dark:text-qricket-400 hover:underline font-medium" aria-label="View terms and conditions">Terms and Conditions</button> above. I acknowledge that I am applying to become a verified organizer on the Qricket platform and agree to comply with all terms, responsibilities, and obligations outlined therein. I certify that all information provided is accurate and truthful, and I understand the consequences of providing false information.
                                        </span>
                                    </label>
                                    <x-input-error class="mt-2" :messages="$errors->get('terms_accepted')" />
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-end space-x-4">
                                <a href="{{ route('profile.edit') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-500 focus:bg-gray-400 dark:focus:bg-gray-500 active:bg-gray-500 dark:active:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    Cancel
                                </a>
                                
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 bg-qricket-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-qricket-600 focus:bg-qricket-600 active:bg-qricket-700 focus:outline-none focus:ring-2 focus:ring-qricket-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    Submit Verification Request
                                </button>
                            </div>
                        </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const documentTypeSelect = document.getElementById('document_type');
    const backDocumentSection = document.getElementById('back-document-section');
    const documentBackInput = document.getElementById('document_back');
    
    // Load terms and conditions
    loadTermsAndConditions();
    
    // Handle document type change
    documentTypeSelect.addEventListener('change', function() {
        const selectedType = this.value;
        const requiresBothSides = ['id_card', 'business_license', 'school_id'].includes(selectedType);
        
        if (requiresBothSides) {
            backDocumentSection.style.display = 'block';
            documentBackInput.required = true;
        } else {
            backDocumentSection.style.display = 'none';
            documentBackInput.required = false;
            documentBackInput.value = ''; // Clear the value
        }
    });
    
    // Add age validation for birthdate
    const birthdateInput = document.getElementById('birthdate');
    if (birthdateInput) {
        birthdateInput.addEventListener('change', function() {
            const birthdate = new Date(this.value);
            const today = new Date();
            const age = today.getFullYear() - birthdate.getFullYear();
            const monthDiff = today.getMonth() - birthdate.getMonth();
            
            // Check if birthday hasn't occurred this year
            const actualAge = monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate()) ? age - 1 : age;
            
            if (actualAge < 18) {
                // Show error message
                let errorDiv = document.getElementById('birthdate-age-error');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.id = 'birthdate-age-error';
                    errorDiv.className = 'mt-2 text-sm text-red-600 dark:text-red-400';
                    birthdateInput.parentNode.appendChild(errorDiv);
                }
                errorDiv.textContent = 'You must be at least 18 years old to become a verified organizer.';
                
                // Add error styling
                birthdateInput.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
                birthdateInput.classList.remove('border-gray-300', 'focus:border-qricket-500', 'focus:ring-qricket-500');
            } else {
                // Remove error message and styling
                const errorDiv = document.getElementById('birthdate-age-error');
                if (errorDiv) {
                    errorDiv.remove();
                }
                birthdateInput.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
                birthdateInput.classList.add('border-gray-300', 'focus:border-qricket-500', 'focus:ring-qricket-500');
            }
        });
    }
    
    // Add form submission validation
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const birthdate = document.getElementById('birthdate');
            if (birthdate && birthdate.value) {
                const birthdateValue = new Date(birthdate.value);
                const today = new Date();
                const age = today.getFullYear() - birthdateValue.getFullYear();
                const monthDiff = today.getMonth() - birthdateValue.getMonth();
                
                // Check if birthday hasn't occurred this year
                const actualAge = monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdateValue.getDate()) ? age - 1 : age;
                
                if (actualAge < 18) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Age Requirement Not Met',
                        text: 'You must be at least 18 years old to become a verified organizer.',
                        confirmButtonColor: '#00B388'
                    });
                    return false;
                }
            }
        });
    }
    
    // Load terms and conditions from file
    function loadTermsAndConditions() {
        const termsUrl = "{{ asset('organizer-terms-and-conditions.txt') }}";
        const termsContent = document.getElementById('terms-content');
        
        // Show loading state
        termsContent.innerHTML = `
            <div class="animate-pulse">
                <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded w-3/4 mb-2"></div>
                <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded w-1/2 mb-2"></div>
                <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded w-5/6"></div>
            </div>
            <p class="text-center text-gray-500 dark:text-gray-400 mt-4">Loading terms and conditions...</p>
        `;
        
        fetch(termsUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(data => {
                if (data.trim()) {
                    termsContent.innerHTML = `<div class="prose prose-sm max-w-none">${data.replace(/\n/g, '<br>')}</div>`;
                } else {
                    throw new Error('Terms file is empty');
                }
            })
            .catch(error => {
                console.error('Error loading terms:', error);
                termsContent.innerHTML = `
                    <div class="text-center text-red-600 dark:text-red-400">
                        <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="font-medium">Error loading terms and conditions</p>
                        <p class="text-sm mt-1">Please refresh the page or contact support if the problem persists.</p>
                    </div>
                `;
            });
    }
    
    // Handle view terms button click
    const viewTermsBtn = document.getElementById('view-terms-btn');
    if (viewTermsBtn) {
        viewTermsBtn.addEventListener('click', function() {
        const termsContent = document.getElementById('terms-content');
        termsContent.scrollIntoView({ behavior: 'smooth' });
    });
    }
    
    // Live countdown timer for cooldown period
    const countdownTimer = document.getElementById('countdown-timer');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    
    if (countdownTimer && progressBar && progressText) {
        // Get the cooldown end time from the server
        const cooldownEndTime = new Date('{{ $verification && $verification->reviewed_at ? $verification->reviewed_at->addDay()->toISOString() : now()->toISOString() }}');
        const totalCooldownMs = 24 * 60 * 60 * 1000; // 24 hours in milliseconds
        
        function updateCountdown() {
            const now = new Date();
            const timeRemaining = cooldownEndTime.getTime() - now.getTime();
            
            if (timeRemaining <= 0) {
                // Cooldown period ended, reload page
                countdownTimer.textContent = '00:00:00';
                progressBar.style.width = '100%';
                progressText.textContent = '100.0%';
                setTimeout(() => location.reload(), 1000);
                return;
            }
            
            // Calculate time remaining
            const hours = Math.floor(timeRemaining / (1000 * 60 * 60));
            const minutes = Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((timeRemaining % (1000 * 60)) / 1000);
            
            // Update timer display
            countdownTimer.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            // Calculate progress (time elapsed)
            const timeElapsed = totalCooldownMs - timeRemaining;
            const progress = Math.min((timeElapsed / totalCooldownMs) * 100, 100);
            
            // Update progress bar and text
            progressBar.style.width = `${progress}%`;
            progressText.textContent = `${progress.toFixed(1)}%`;
            
            // Add pulsing effect when time is running low
            if (timeRemaining < 5 * 60 * 1000) { // Less than 5 minutes
                countdownTimer.classList.add('animate-pulse');
            } else {
                countdownTimer.classList.remove('animate-pulse');
            }
        }
        
        // Update countdown every second for smooth animation
        updateCountdown();
        const countdownInterval = setInterval(updateCountdown, 1000);
        
        // Store interval ID for cleanup if needed
        window.countdownInterval = countdownInterval;
    }
});
</script>



