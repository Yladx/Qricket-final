<x-guest-layout title="PIN Verification - Qricket Admin">
    <div class="flex h-screen flex-col md:flex-row">
        <!-- LEFT SECTION - Hidden on mobile, visible on desktop -->
        <div class="hidden md:flex flex-1 flex-col items-center justify-center p-10 text-white" style="background: linear-gradient(135deg, #00B388 50%, #009970 100%);">
            <div class="text-center">
                <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold mb-4">PIN Verification</h1>
                <p class="text-lg opacity-90">Additional security layer</p>
                <div class="mt-8 p-4 bg-white/20 rounded-lg backdrop-blur-sm">
                    <p class="text-sm">Enter your 6-digit PIN to complete login</p>
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
                    <div class="w-16 h-16 bg-qricket-100 dark:bg-qricket-900 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-qricket-600 dark:text-qricket-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl text-qricket-500 dark:text-qricket-400 font-bold">Enter PIN</h1>
                    <p class="text-gray-600 dark:text-gray-300 mt-2">Welcome, {{ $admin->username }}</p>
                </div>

                <!-- Error Messages -->
                @if (session('error'))
                    <div class="mb-4 text-sm text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/20 p-3 rounded-lg border border-red-200 dark:border-red-800">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.pin') }}" class="flex flex-col gap-4">
                    @csrf

                    <div>
                        <label class="text-sm text-gray-700 dark:text-gray-300 mb-1 inline-block transition-colors duration-200" for="pin">6-Digit PIN</label>
                        <input type="password" 
                               name="pin" 
                               id="pin"
                               placeholder="000000" 
                               maxlength="6"
                               pattern="[0-9]{6}"
                               inputmode="numeric"
                               autocomplete="one-time-code"
                               required
                               autofocus
                               class="w-full px-4 py-3 text-center text-2xl tracking-widest border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-qricket-500 dark:focus:ring-qricket-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 transition-colors duration-200 @error('pin') border-red-500 dark:border-red-400 @enderror"> 
                        @error('pin')
                            <p class="text-red-500 dark:text-red-400 text-sm mt-1 transition-colors duration-200">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"  
                            id="pinSubmitBtn"
                            disabled
                            class="bg-qricket-500 hover:bg-qricket-600 dark:bg-qricket-500 dark:hover:bg-qricket-600 text-white font-semibold py-3 rounded-lg shadow hover:shadow-lg transition-all duration-200 transform hover:scale-105 opacity-50 cursor-not-allowed disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100">
                        Verify PIN & Login
                    </button>

                    <div class="text-center mt-4">
                        <a href="{{ route('admin.login') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-qricket-600 dark:hover:text-qricket-400 transition-colors duration-200">
                            ← Back to Login
                        </a>
                    </div>
                </form>

                <!-- PIN Input Helper -->
                <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <p class="text-xs text-gray-600 dark:text-gray-400 text-center">
                        Enter your 6-digit PIN using numbers only
                    </p>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
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

            // Auto-format PIN input
            const pinInput = document.getElementById('pin');
            const submitButton = document.getElementById('pinSubmitBtn');
            
            pinInput.addEventListener('input', function(e) {
                // Only allow numbers
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
                
                // Enable/disable submit button based on PIN length
                if (e.target.value.length === 6) {
                    submitButton.disabled = false;
                    submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                    submitButton.classList.add('hover:bg-qricket-600', 'hover:shadow-lg', 'hover:scale-105');
                } else {
                    submitButton.disabled = true;
                    submitButton.classList.add('opacity-50', 'cursor-not-allowed');
                    submitButton.classList.remove('hover:bg-qricket-600', 'hover:shadow-lg', 'hover:scale-105');
                }
            });
            
            // Initialize button state
            if (pinInput.value.length !== 6) {
                submitButton.disabled = true;
            }
        </script>
    </x-slot>
</x-guest-layout>