<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form id="profile-information-form" method="post" action="{{ route('profile.update') }}" class="mt-4 space-y-4" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <!-- Profile Picture Section -->
        <div>
            <x-input-label for="profile_picture" :value="__('Profile Picture')" />
            <div class="mt-2 flex items-center space-x-4">
                <!-- Clickable Profile Picture -->
                <div class="flex-shrink-0">
                    <label for="profile_picture" class="cursor-pointer group">
                        <div id="current-picture" class="h-28 w-28 rounded-full object-cover border-2 border-gray-300 dark:border-gray-600 overflow-hidden group-hover:border-qricket-500 transition-colors duration-200 relative">
                            @if($user->profile_picture)
                                <img class="h-full w-full object-cover" 
                                     src="{{ $user->profile_picture_url }}" 
                                     alt="{{ $user->first_name }}'s profile picture">
                            @else
                                <div class="h-full w-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                    <svg class="h-12 w-12 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            @endif
                            <!-- Camera Icon Overlay -->
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-200 flex items-center justify-center">
                                <svg class="h-7 w-7 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </label>
                    
                    <!-- Preview Container (hidden by default) -->
                    <div id="preview-container" class="mt-2 hidden">
                        <div class="text-xs text-gray-500 dark:text-gray-400 text-center">
                            Preview
                        </div>
                        <div id="image-preview" class="h-28 w-28 rounded-full object-cover border-2 border-qricket-500 overflow-hidden mt-1">
                            <!-- Preview image will be inserted here -->
                        </div>
                    </div>
                </div>
                
                <!-- Upload Input (Hidden) -->
                <input type="file" 
                       id="profile_picture" 
                       name="profile_picture" 
                       accept=".jpg,.jpeg,.png"
                       class="hidden" disabled>
                
                <!-- Info Text -->
                <div class="flex-1">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Click the profile icon to change your picture
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                        JPG, PNG only (Max 5MB)
                    </p>
                    <x-input-error class="mt-2" :messages="$errors->get('profile_picture')" />
                </div>
            </div>
        </div>

        <!-- Name Fields Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="first_name" :value="__('First Name')" />
                <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="old('first_name', $user->first_name)" required autofocus autocomplete="given-name" disabled />
                <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
            </div>

            <div>
                <x-input-label for="last_name" :value="__('Last Name')" />
                <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name', $user->last_name)" required autocomplete="family-name" disabled />
                <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
            </div>
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" disabled />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <!-- Organizer Status Info -->
        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <p class="text-sm text-blue-800 dark:text-blue-200">
                <strong>Organizer features are now available to all users!</strong><br>
                You can access organizer verification and features without needing to enable a checkbox.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <x-primary-button id="saveProfileBtn" disabled class="opacity-50 cursor-not-allowed">{{ __('Save') }}</x-primary-button>
            <button type="button" id="editProfileBtn" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-qricket-500">
                Edit
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>

    <script>
        (function() {
            'use strict';
            
            // Wait for DOM to be ready
            function initProfileEdit() {
                // Try multiple selectors to find the form - ONLY the profile update form, not social media form
                const form = document.getElementById('profile-information-form') ||
                            document.querySelector('form[action*="profile.update"]:not([action*="social-media"])') || 
                            document.querySelector('form[method="post"][enctype]:not([action*="social-media"])');
                
                if (!form) {
                    console.warn('Profile edit form not found');
                    return;
                }
                
                const fileInput = document.getElementById('profile_picture');
                const currentPicture = document.getElementById('current-picture');
                const previewContainer = document.getElementById('preview-container');
                const preview = document.getElementById('image-preview');
                const editBtn = document.getElementById('editProfileBtn');
                const saveBtn = document.getElementById('saveProfileBtn');
                
                if (!editBtn) {
                    console.warn('Edit button not found');
                    return;
                }
                
                // Only select inputs that are direct children or within this specific form, excluding social media inputs
                const inputs = form.querySelectorAll('input[type="text"]:not([name*="social"]), input[type="email"]:not([name*="social"])');
                const originalImg = currentPicture?.querySelector('img')?.src || null;
                let isEditing = false;
                
                // Store original values
                const originalValues = {};
                inputs.forEach(input => {
                    if (input.id) {
                        originalValues[input.id] = input.value;
                    }
                });

                function setEditing(on) {
                    try {
                        inputs.forEach(i => { 
                            // Never disable social media inputs
                            if (i && !i.name.includes('social') && !i.classList.contains('social-media-input')) {
                                i.disabled = !on; 
                            }
                        });
                        if (fileInput) fileInput.disabled = !on;
                        if (saveBtn) {
                            if (on) {
                                saveBtn.removeAttribute('disabled');
                                saveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                            } else {
                                saveBtn.setAttribute('disabled', 'disabled');
                                saveBtn.classList.add('opacity-50', 'cursor-not-allowed');
                            }
                        }
                        if (editBtn) {
                            editBtn.textContent = on ? 'Cancel' : 'Edit';
                        }
                        isEditing = on;
                    } catch (error) {
                        console.error('Error in setEditing:', error);
                    }
                }
                
                // Initialize as non-editing
                setEditing(false);

                // Edit button click handler
                editBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    try {
                        if (!isEditing) {
                            // Enable editing
                            setEditing(true);
                        } else {
                            // Cancel edits: disable and revert
                            setEditing(false);
                            
                            // Revert form fields to original values
                            inputs.forEach(input => {
                                if (input.id && originalValues[input.id] !== undefined) {
                                    input.value = originalValues[input.id];
                                }
                            });
                            
                            // Revert profile picture if changed
                            if (originalImg && currentPicture) {
                                // Restore original image structure
                                const exist = currentPicture.querySelector('img');
                                if (exist) {
                                    exist.src = originalImg;
                                } else {
                                    // If no image, restore to original structure (with placeholder or image)
                                    const currentSrc = originalImg;
                                    if (currentSrc && currentSrc !== 'null' && currentSrc !== '') {
                                        const img = document.createElement('img');
                                        img.src = currentSrc;
                                        img.className = 'h-full w-full object-cover';
                                        img.alt = 'Profile picture';
                                        currentPicture.appendChild(img);
                                    }
                                }
                                // Ensure camera overlay is present
                                let overlay = currentPicture.querySelector('.absolute.inset-0');
                                if (!overlay) {
                                    overlay = document.createElement('div');
                                    overlay.className = 'absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-200 flex items-center justify-center';
                                    overlay.innerHTML = `
                                        <svg class="h-7 w-7 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    `;
                                    currentPicture.appendChild(overlay);
                                }
                            }
                            if (preview) preview.innerHTML = '';
                            if (previewContainer) previewContainer.classList.add('hidden');
                            if (fileInput) fileInput.value = '';
                        }
                    } catch (error) {
                        console.error('Error handling edit button click:', error);
                    }
                });

                // File input change handler
                if (fileInput) {
                    fileInput.addEventListener('change', function() {
                        const file = this.files && this.files[0];
                        if (!file) return;
                        
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            try {
                                // Update the main current picture with the preview
                                if (currentPicture) {
                                    // Remove any existing placeholder div or img
                                    currentPicture.innerHTML = '';
                                    const img = document.createElement('img');
                                    img.src = e.target.result;
                                    img.className = 'h-full w-full object-cover';
                                    img.alt = 'Profile picture preview';
                                    
                                    // Re-add the camera overlay
                                    const overlay = document.createElement('div');
                                    overlay.className = 'absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-200 flex items-center justify-center';
                                    overlay.innerHTML = `
                                        <svg class="h-7 w-7 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    `;
                                    
                                    currentPicture.appendChild(img);
                                    currentPicture.appendChild(overlay);
                                }
                                
                                // Hide the separate preview container - we're showing preview in main picture
                                if (previewContainer) {
                                    previewContainer.classList.add('hidden');
                                }
                                if (preview) {
                                    preview.innerHTML = '';
                                }
                            } catch (error) {
                                console.error('Error processing file preview:', error);
                            }
                        };
                        reader.readAsDataURL(file);
                    });
                }
            }
            
            // Run when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initProfileEdit);
            } else {
                // DOM already loaded
                initProfileEdit();
            }
        })();
    </script>
</section>