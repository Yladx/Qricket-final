<x-guest-layout title="Welcome to Qricket - Your Event Management Platform" page-title="Welcome to Qricket">
    
<div class="flex h-screen flex-col md:flex-row">
        <!-- LEFT SECTION - Hidden on mobile, visible on desktop -->
        <div class="hidden md:flex flex-1 flex-col items-center justify-center p-10 text-white" style="background: linear-gradient(135deg, #00B388 50%, #009970 100%);">
            <div class="text-center">
                <h1 class="text-4xl font-bold mb-4">Admin Portal</h1>
                <p class="text-xl opacity-90">Qricket Management System</p>
                <div class="mt-8 p-4 bg-white/20 rounded-lg backdrop-blur-sm">
                    <p class="text-sm">Secure access to administrative functions</p>
                </div>
            </div>
        </div>

        <!-- RIGHT SECTION - Full width on mobile -->
        <div class="flex flex-1 items-center justify-center bg-white dark:bg-gray-800 w-full transition-colors duration-300">
            <div class="w-full max-w-sm text-left p-6">
                <!-- Dark Mode Toggle -->
                <div class="flex justify-end mb-4">
                    <button id="darkModeToggle" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200" aria-label="Toggle dark mode">
                        <svg id="sunIcon" class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <svg id="moonIcon" class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </button>
                </div>

                <div class="text-center mb-8">
                    <h1 class="text-3xl text-qricket-500 dark:text-qricket-400 font-bold">Admin Login</h1>
                    <p class="text-gray-600 dark:text-gray-300 mt-2">Enter your credentials to continue</p>
                </div>

                <!-- Error Messages -->
                @if (session('error'))
                    <div class="mb-4 text-sm text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/20 p-3 rounded-lg border border-red-200 dark:border-red-800">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ session('error') }}
                        </div>
                    </div>
                @endif

                <!-- Lockout Warning -->
                @if (session('lockout_warning') || isset($lockoutData))
                    <div class="mb-4 text-sm text-orange-600 dark:text-orange-400 bg-orange-100 dark:bg-orange-900/20 p-3 rounded-lg border border-orange-200 dark:border-orange-800" data-lockout-warning>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            {{ session('lockout_warning') ?? $lockoutData['message'] ?? '' }}
                        </div>
                    </div>
                @endif

                <!-- Session Conflict Warning - TEMPORARILY DISABLED FOR DEBUGGING -->
                {{-- @if (session('session_conflict'))
                    <div class="mb-4 text-sm text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/20 p-3 rounded-lg border border-blue-200 dark:border-blue-800">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ session('session_conflict') }}
                        </div>
                    </div>
                @endif --}}

                <form method="POST" action="{{ route('admin.login') }}" class="flex flex-col gap-4" id="loginForm">
                    @csrf

                    <div>
                        <label class="text-sm text-gray-700 dark:text-gray-300 mb-1 inline-block transition-colors duration-200" for="username">Username</label>
                        <input type="text" 
                               name="username" 
                               id="username"
                               placeholder="Enter username" 
                               value="{{ old('username') }}"
                               required
                               autofocus
                               class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-qricket-500 dark:focus:ring-qricket-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 transition-colors duration-200 @error('username') border-red-500 dark:border-red-400 @enderror"> 
                        @error('username')
                            <p class="text-red-500 dark:text-red-400 text-sm mt-1 transition-colors duration-200">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm text-gray-700 dark:text-gray-300 mb-1 inline-block transition-colors duration-200" for="password">Password</label>
                        <div class="relative">
                            <input type="password" 
                                   name="password" 
                                   id="password"
                                   placeholder="Enter password" 
                                   required
                                   class="w-full px-4 py-3 pr-10 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-qricket-500 dark:focus:ring-qricket-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 transition-colors duration-200 @error('password') border-red-500 dark:border-red-400 @enderror">
                            <button type="button" data-password-toggle data-target="#password" aria-label="Show password" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                <svg data-icon-open xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg data-icon-closed xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-2.063-2.063A10.013 10.013 0 0019.542 12C18.268 7.943 14.477 5 10 5a9.958 9.958 0 00-4.243.938L3.707 2.293zM12.454 14.16l-1.421-1.421a3 3 0 01-3.772-3.772L5.43 6.136A7.963 7.963 0 0010 7c3.438 0 6.363 2.065 7.542 5-.371 1.068-1 2.022-1.82 2.86l-3.268-1.7z" clip-rule="evenodd" /></svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-red-500 dark:text-red-400 text-sm mt-1 transition-colors duration-200">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" id="loginButton"
                            class="bg-qricket-500 hover:bg-qricket-600 dark:bg-qricket-500 dark:hover:bg-qricket-600 text-white font-semibold py-3 rounded-lg shadow hover:shadow-lg transition-all duration-200 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:hover:shadow-none">
                        <span id="buttonText">Continue to PIN Verification</span>
                        <span id="countdownText" class="hidden"></span>
                    </button>

                    <div class="text-center mt-4">
                        <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-qricket-600 dark:hover:text-qricket-400 transition-colors duration-200">
                            ← Back to User Portal
                        </a>
                    </div>

                    <!-- Security Information -->
                    <div class="mt-6 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            <div class="flex items-center mb-2">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                <span class="font-medium">Security Features</span>
                            </div>
                            <ul class="space-y-1 text-xs">
                                <li>• Account locks after 3 failed attempts (15 minutes)</li>
                                <li>• Only one active session per admin</li>
                                <li>• All login attempts are logged</li>
                            </ul>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Admin lockout timer functionality
        function initLockoutTimer() {
            console.log('Initializing lockout timer...');
            
            const lockoutWarning = document.querySelector('[data-lockout-warning]');
            const loginButton = document.getElementById('loginButton');
            const buttonText = document.getElementById('buttonText');
            const countdownText = document.getElementById('countdownText');
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');

            // Get lockout seconds from session data or lockout data
            const lockoutSeconds = @json(session('lockout_seconds', 0)) || @json($lockoutData['seconds'] ?? 0);
            
            console.log('Lockout warning element:', lockoutWarning);
            console.log('Lockout seconds:', lockoutSeconds);
            console.log('Login button:', loginButton);

            if (lockoutWarning && lockoutSeconds > 0) {
                console.log('Lockout detected, disabling form...');
                let remainingSeconds = lockoutSeconds;
                
                // Disable form immediately
                disableLoginForm();
                
                // Start countdown
                const countdownInterval = setInterval(function() {
                    remainingSeconds--;
                    
                    if (remainingSeconds <= 0) {
                        // Lockout expired
                        clearInterval(countdownInterval);
                        enableLoginForm();
                        hideLockoutWarning();
                    } else {
                        // Update countdown display
                        const minutes = Math.floor(remainingSeconds / 60);
                        const seconds = remainingSeconds % 60;
                        
                        updateCountdownDisplay(minutes, seconds);
                    }
                }, 1000);
                
                // Initial countdown display
                const initialMinutes = Math.floor(remainingSeconds / 60);
                const initialSeconds = remainingSeconds % 60;
                updateCountdownDisplay(initialMinutes, initialSeconds);
            } else {
                console.log('No lockout detected');
            }

            function disableLoginForm() {
                console.log('Disabling login form...');
                if (loginButton) loginButton.disabled = true;
                if (usernameInput) usernameInput.disabled = true;
                if (passwordInput) passwordInput.disabled = true;
                if (buttonText) buttonText.classList.add('hidden');
                if (countdownText) countdownText.classList.remove('hidden');
                if (loginButton) {
                    loginButton.classList.add('bg-gray-400', 'cursor-not-allowed');
                    loginButton.classList.remove('bg-qricket-500', 'hover:bg-qricket-600');
                }
            }

            function enableLoginForm() {
                console.log('Enabling login form...');
                if (loginButton) loginButton.disabled = false;
                if (usernameInput) usernameInput.disabled = false;
                if (passwordInput) passwordInput.disabled = false;
                if (buttonText) buttonText.classList.remove('hidden');
                if (countdownText) countdownText.classList.add('hidden');
                if (loginButton) {
                    loginButton.classList.remove('bg-gray-400', 'cursor-not-allowed');
                    loginButton.classList.add('bg-qricket-500', 'hover:bg-qricket-600');
                }
            }

            function updateCountdownDisplay(minutes, seconds) {
                const timeString = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                if (countdownText) countdownText.textContent = `Account locked - Try again in ${timeString}`;
            }

            function hideLockoutWarning() {
                const lockoutWarning = document.querySelector('[data-lockout-warning]');
                if (lockoutWarning) {
                    lockoutWarning.style.display = 'none';
                }
            }
        }

        // Try to run immediately, then also on DOMContentLoaded as backup
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initLockoutTimer);
        } else {
            initLockoutTimer();
        }
        
        // Also try after a short delay to ensure elements are rendered
        setTimeout(initLockoutTimer, 100);
    </script>

    <x-slot name="scripts">
        <script>
            // Password validation - remove whitespace
            document.addEventListener('DOMContentLoaded', function() {
                const passwordInput = document.getElementById('password');
                
                if (passwordInput) {
                    // Remove whitespace from password field on input
                    passwordInput.addEventListener('input', function(e) {
                        if (e.inputType === 'insertText' || e.inputType === 'insertFromPaste') {
                            this.value = this.value.replace(/\s/g, '');
                        }
                    });
                    
                    // Prevent paste with whitespace in password
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
            });
            
            // Dark mode toggle functionality
            document.addEventListener('DOMContentLoaded', function() {
                const darkModeToggle = document.getElementById('darkModeToggle');
                const html = document.documentElement;
                
                // Check for saved theme preference or default to 'light'
                const currentTheme = localStorage.getItem('theme') || 'light';
                
                // Apply the current theme
                if (currentTheme === 'dark') {
                    html.classList.add('dark');
                } else {
                    html.classList.remove('dark');
                }
                
                // Toggle dark mode
                darkModeToggle.addEventListener('click', function() {
                    if (html.classList.contains('dark')) {
                        html.classList.remove('dark');
                        localStorage.setItem('theme', 'light');
                    } else {
                        html.classList.add('dark');
                        localStorage.setItem('theme', 'dark');
                    }
                });
            });
        </script>
    </x-slot>
</x-guest-layout>