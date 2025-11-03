<x-guest-layout title="Sign Up - Qrickett">
    <!-- Back to Home Link -->
    <div class="fixed top-4 left-4 z-50">
        <a href="{{ route('welcome') }}" class="group inline-flex items-center px-5 py-3 bg-gradient-to-r from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-xl shadow-lg hover:shadow-xl border border-gray-200 dark:border-gray-700 transition-all duration-300 transform hover:scale-105 hover:-translate-y-0.5 text-gray-700 dark:text-gray-300 hover:text-qricket-600 dark:hover:text-qricket-400">
            <div class="flex items-center justify-center w-6 h-6 mr-3 rounded-full bg-qricket-100 dark:bg-qricket-900 group-hover:bg-qricket-200 dark:group-hover:bg-qricket-800 transition-colors duration-300">
                <svg class="h-4 w-4 text-qricket-600 dark:text-qricket-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </div>
            <span class="font-medium text-sm">Back to Home</span>
        </a>
    </div>

    <!-- Dark Mode Toggle -->
    <div class="fixed top-4 right-4 z-50">
        <button id="dark-mode-toggle" class="p-3 bg-white dark:bg-gray-800 rounded-full shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-110">
            <!-- Sun icon for dark mode -->
            <svg id="sun-icon" class="h-6 w-6 text-gray-600 dark:text-gray-400 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            <!-- Moon icon for light mode -->
            <svg id="moon-icon" class="h-6 w-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
            </svg>
        </button>
    </div>

    <div class="flex h-screen flex-col md:flex-row">
        <!-- LEFT SECTION - Hidden on mobile, visible on desktop -->
        <div class="hidden md:flex flex-1 flex-col items-center justify-center p-10 text-white" style="background: linear-gradient(135deg, #00B388 50%, #009970 100%);">
            <h1 id="changing-text" class="text-3xl font-bold mb-2 transition-opacity duration-300">Welcome To Qrickett</h1>
            <p class="text-lg opacity-85">Your Event. Your Code.</p>
        </div>

        <!-- Right Section - Full width on mobile -->
        <div class="flex-1 flex items-center justify-center bg-white dark:bg-gray-800 w-full transition-colors duration-300">
            <div class="w-full max-w-sm text-left p-6">
                <h2 class="text-2xl font-bold text-qricket-500 dark:text-qricket-400 mb-4">Create Account</h2>
                <p class="text-gray-600 dark:text-gray-300 font-semibold mb-4 transition-colors duration-300">Join thousands of event organizers and attendees!</p>

                <form method="POST" action="{{ route('register') }}" id="registrationForm" class="flex flex-col gap-3">
        @csrf

        <!-- First Name -->
        <div>
                        <label class="text-sm text-gray-700 dark:text-gray-300 mb-1 inline-block transition-colors duration-200" for="first_name">Name</label>
                        <input type="text" 
                               name="first_name" 
                               id="first_name"
                               placeholder="Name" 
                               value="{{ old('first_name') }}"
                               required 
                               autofocus
                               autocomplete="given-name"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-qricket-500 dark:focus:ring-qricket-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 transition-colors duration-200 @error('first_name') border-red-500 dark:border-red-400 @enderror" />
                        @error('first_name')
                            <p class="text-red-500 dark:text-red-400 text-sm mt-1 transition-colors duration-200">{{ $message }}</p>
                        @enderror
        </div>

        <!-- Last Name -->
                    <div>
                        <label class="text-sm text-gray-700 dark:text-gray-300 mb-1 inline-block transition-colors duration-200" for="last_name">Last Name</label>
                        <input type="text" 
                               name="last_name" 
                               id="last_name"
                               placeholder="Last Name" 
                               value="{{ old('last_name') }}"
                               required
                               autocomplete="family-name"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-qricket-500 dark:focus:ring-qricket-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 transition-colors duration-200 @error('last_name') border-red-500 dark:border-red-400 @enderror" />
                        @error('last_name')
                            <p class="text-red-500 dark:text-red-400 text-sm mt-1 transition-colors duration-200">{{ $message }}</p>
                        @enderror
        </div>

        <!-- Email Address -->
                    <div>
                        <label class="text-sm text-gray-700 dark:text-gray-300 mb-1 inline-block transition-colors duration-200" for="email">Email</label>
                        <input type="email" 
                               name="email" 
                               id="email"
                               placeholder="Email" 
                               value="{{ old('email') }}"
                               required
                               autocomplete="username"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-qricket-500 dark:focus:ring-qricket-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 transition-colors duration-200 @error('email') border-red-500 dark:border-red-400 @enderror" />
                        @error('email')
                            <p class="text-red-500 dark:text-red-400 text-sm mt-1 transition-colors duration-200">{{ $message }}</p>
                        @enderror
        </div>

        <!-- Password -->
                    <div>
                        <label class="text-sm text-gray-700 dark:text-gray-300 mb-1 inline-block transition-colors duration-200" for="password">Password</label>
                        <div class="relative">
                            <input type="password" 
                            name="password"
                                   id="password"
                                   placeholder="Password" 
                                   required
                                   autocomplete="new-password"
                                   class="w-full px-4 py-2 pr-10 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-qricket-500 dark:focus:ring-qricket-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 transition-colors duration-200 @error('password') border-red-500 dark:border-red-400 @enderror" />
                            <button type="button" data-password-toggle data-target="#password" aria-label="Show password" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                <svg data-icon-open xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg data-icon-closed xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-2.063-2.063A10.013 10.013 0 0019.542 12C18.268 7.943 14.477 5 10 5a9.958 9.958 0 00-4.243.938L3.707 2.293zM12.454 14.16l-1.421-1.421a3 3 0 01-3.772-3.772L5.43 6.136A7.963 7.963 0 0010 7c3.438 0 6.363 2.065 7.542 5-.371 1.068-1 2.022-1.82 2.86l-3.268-1.7z" clip-rule="evenodd" /></svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-red-500 dark:text-red-400 text-sm mt-1 transition-colors duration-200">{{ $message }}</p>
                        @enderror
        </div>

        <!-- Confirm Password -->
                    <div>
                        <label class="text-sm text-gray-700 dark:text-gray-300 mb-1 inline-block transition-colors duration-200" for="password_confirmation">Confirm Password</label>
                        <div class="relative">
                            <input type="password" 
                                   name="password_confirmation" 
                                   id="password_confirmation"
                                   placeholder="Confirm Password" 
                                   required
                                   autocomplete="new-password"
                                   class="w-full px-4 py-2 pr-10 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-qricket-500 dark:focus:ring-qricket-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 transition-colors duration-200 @error('password_confirmation') border-red-500 dark:border-red-400 @enderror" />
                            <button type="button" data-password-toggle data-target="#password_confirmation" aria-label="Show password" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                <svg data-icon-open xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg data-icon-closed xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-2.063-2.063A10.013 10.013 0 0019.542 12C18.268 7.943 14.477 5 10 5a9.958 9.958 0 00-4.243.938L3.707 2.293zM12.454 14.16l-1.421-1.421a3 3 0 01-3.772-3.772L5.43 6.136A7.963 7.963 0 0010 7c3.438 0 6.363 2.065 7.542 5-.371 1.068-1 2.022-1.82 2.86l-3.268-1.7z" clip-rule="evenodd" /></svg>
                            </button>
                        </div>
                        @error('password_confirmation')
                            <p class="text-red-500 dark:text-red-400 text-sm mt-1 transition-colors duration-200">{{ $message }}</p>
                        @enderror
        </div>

        <!-- reCAPTCHA -->
                    @if(config('services.recaptcha.site_key'))
        <div class="mt-4">
                <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                            @error('g-recaptcha-response')
                                <p class="text-red-500 dark:text-red-400 text-sm mt-1 transition-colors duration-200">{{ $message }}</p>
                            @enderror
                            <!-- CAPTCHA validation message -->
                            <div id="captcha-error-message" class="text-red-500 dark:text-red-400 text-sm mt-1 transition-colors duration-200 hidden">
                                Please complete the CAPTCHA verification to continue.
                            </div>
                        </div>
            @endif

                    <!-- Terms and Conditions -->
                    <div class="flex items-start">
                        <input id="terms_agreement" 
                               type="checkbox" 
                               name="terms"
                               value="1"
                               required
                               class="mt-1 rounded border-gray-300 dark:border-gray-600 text-qricket-600 dark:text-qricket-400 shadow-sm focus:ring-qricket-500 dark:focus:ring-qricket-400 bg-white dark:bg-gray-700">
                        <label for="terms_agreement" class="ms-2 text-sm text-gray-600 dark:text-gray-400 transition-colors duration-200">
                            I agree to the 
                            <button type="button" id="terms-modal-trigger" class="text-qricket-600 dark:text-qricket-400 hover:underline transition-colors duration-200">
                                Terms and Conditions
                            </button>
                            and 
                            <button type="button" id="privacy-modal-trigger" class="text-qricket-600 dark:text-qricket-400 hover:underline transition-colors duration-200">
                                Privacy Policy
                            </button>
                        </label>
                    </div>

                    <button type="submit"
                            class="bg-qricket-500 hover:bg-qricket-600 dark:bg-qricket-500 dark:hover:bg-qricket-600 text-white font-semibold py-2 rounded-lg shadow hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                        Sign Up
                    </button>

                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center mt-2 transition-colors duration-200">
                        Already have an account?
                        <a href="{{ route('login') }}" class="text-qricket-600 dark:text-qricket-400 underline hover:text-qricket-700 dark:hover:text-qricket-300 transition-colors duration-200">Log in here</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <!-- Dark Mode Script -->
        <script>
            // Dark Mode Management
            class DarkModeManager {
                constructor() {
                    this.darkModeToggle = document.getElementById('dark-mode-toggle');
                    this.sunIcon = document.getElementById('sun-icon');
                    this.moonIcon = document.getElementById('moon-icon');
                    this.init();
                }

                init() {
                    this.loadSavedPreference();
                    this.setupEventListeners();
                }

                loadSavedPreference() {
                    const isDarkMode = localStorage.getItem('darkMode') === 'true';
                    this.setDarkMode(isDarkMode);
                }

                setupEventListeners() {
                    if (this.darkModeToggle) {
                        this.darkModeToggle.addEventListener('click', () => {
                            this.toggleDarkMode();
                        });
                    }
                }

                setDarkMode(isDark) {
                    if (isDark) {
                        document.documentElement.classList.add('dark');
                        this.showSunIcon();
                    } else {
                        document.documentElement.classList.remove('dark');
                        this.showMoonIcon();
                    }
                    localStorage.setItem('darkMode', isDark.toString());
                }

                toggleDarkMode() {
                    const isCurrentlyDark = document.documentElement.classList.contains('dark');
                    this.setDarkMode(!isCurrentlyDark);
                }

                showSunIcon() {
                    if (this.sunIcon) this.sunIcon.classList.remove('hidden');
                    if (this.moonIcon) this.moonIcon.classList.add('hidden');
                }

                showMoonIcon() {
                    if (this.sunIcon) this.sunIcon.classList.add('hidden');
                    if (this.moonIcon) this.moonIcon.classList.remove('hidden');
                }
            }

            // Initialize dark mode when DOM is loaded
            document.addEventListener('DOMContentLoaded', function() {
                new DarkModeManager();
            });
        </script>

        <!-- External JavaScript for animations -->
        <script src="{{ asset('js/auth-animations.js') }}"></script>
        
        <!-- reCAPTCHA Script -->
        @if(config('services.recaptcha.site_key'))
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        @endif

        <script>
            (function(){
                // Password toggle handled globally by qricket-scripts.js

                // Remove whitespace from password fields
                const passwordInput = document.getElementById('password');
                const passwordConfirmInput = document.getElementById('password_confirmation');
                
                if (passwordInput) {
                    passwordInput.addEventListener('input', function(e) {
                        if (e.inputType === 'insertText' || e.inputType === 'insertFromPaste') {
                            this.value = this.value.replace(/\s/g, '');
                        }
                    });
                    
                    passwordInput.addEventListener('paste', function(e) {
                        e.preventDefault();
                        const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                        const cleanText = pastedText.replace(/\s/g, '');
                        const selectionStart = this.selectionStart;
                        const selectionEnd = this.selectionEnd;
                        this.value = this.value.substring(0, selectionStart) + cleanText + this.value.substring(selectionEnd);
                        this.setSelectionRange(selectionStart + cleanText.length, selectionStart + cleanText.length);
                    });
                }
                
                if (passwordConfirmInput) {
                    passwordConfirmInput.addEventListener('input', function(e) {
                        if (e.inputType === 'insertText' || e.inputType === 'insertFromPaste') {
                            this.value = this.value.replace(/\s/g, '');
                        }
                    });
                    
                    passwordConfirmInput.addEventListener('paste', function(e) {
                        e.preventDefault();
                        const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                        const cleanText = pastedText.replace(/\s/g, '');
                        const selectionStart = this.selectionStart;
                        const selectionEnd = this.selectionEnd;
                        this.value = this.value.substring(0, selectionStart) + cleanText + this.value.substring(selectionEnd);
                        this.setSelectionRange(selectionStart + cleanText.length, selectionStart + cleanText.length);
                    });
                }

                // Handle form submission with AJAX
                document.getElementById('registrationForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const form = this;
                    const submitButton = form.querySelector('button[type="submit"]');
                    const originalText = submitButton.textContent;
                    
                    // Disable submit button and show loading state
                    submitButton.disabled = true;
                    submitButton.textContent = 'Creating Account...';
                    submitButton.classList.add('opacity-75', 'cursor-not-allowed');
                    
                    // Prepare form data
                    const formData = new FormData(form);
                    
                    // Add AJAX header
                    formData.append('X-Requested-With', 'XMLHttpRequest');
                    
                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(data => {
                                throw new Error(data.message || 'Registration failed');
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Show success SweetAlert
                            Swal.fire({
                                icon: 'success',
                                title: 'Registration Successful!',
                                text: data.message,
                                confirmButtonText: 'Go to Login',
                                confirmButtonColor: '#00B388',
                                allowOutsideClick: false,
                                allowEscapeKey: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = data.redirect_url;
                                }
                            });
                        } else {
                            throw new Error(data.message || 'Registration failed');
                        }
                    })
                    .catch(error => {
                        console.error('Registration error:', error);
                        
                        // Show error SweetAlert
                        Swal.fire({
                            icon: 'error',
                            title: 'Registration Failed',
                            text: error.message || 'An error occurred during registration. Please try again.',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#ef4444'
                        });
                    })
                    .finally(() => {
                        // Re-enable submit button
                        submitButton.disabled = false;
                        submitButton.textContent = originalText;
                        submitButton.classList.remove('opacity-75', 'cursor-not-allowed');
                    });
                });

                // Load Terms/Privacy in modal
                window.openDoc = function(type){
                    const map = { terms: '{{ asset('terms-and-conditions.txt') }}', privacy: '{{ asset('privacy-policy.txt') }}' };
                    const url = map[type];
                    if(!url){ return; }
                    fetch(url).then(r=>r.text()).then(text=>{
                        Swal && Swal.fire({
                            title: type === 'terms' ? 'Terms and Conditions' : 'Privacy Policy',
                            html: `<div style="text-align:left;max-height:50vh;overflow:auto" class="text-sm">${text.replaceAll('\n','<br>')}</div>`,
                            width: 800,
                            confirmButtonColor: '#00B388'
                        });
                    });
                }

                // Add event listeners for terms and privacy modals
                document.addEventListener('DOMContentLoaded', function() {
                    const termsTrigger = document.getElementById('terms-modal-trigger');
                    const privacyTrigger = document.getElementById('privacy-modal-trigger');
                    
                    if (termsTrigger) {
                        termsTrigger.addEventListener('click', () => openDoc('terms'));
                    }
                    
                    if (privacyTrigger) {
                        privacyTrigger.addEventListener('click', () => openDoc('privacy'));
                    }
                });
            })();
        </script>
    </x-slot>
</x-guest-layout>