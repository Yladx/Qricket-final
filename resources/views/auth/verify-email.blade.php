<x-guest-layout title="Email Verification - Qrickett">
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
            <h1 id="changing-text" class="text-3xl font-bold mb-2 transition-opacity duration-300">Welcome To Qricket</h1>
            <p class="text-lg opacity-85">Your Event. Your Code.</p>
        </div>

        <!-- RIGHT SECTION - Full width on mobile -->
        <div class="flex flex-1 items-center justify-center bg-white dark:bg-gray-800 w-full transition-colors duration-300">
            <div class="w-full max-w-sm text-left p-6">
                <h1 class="text-2xl text-qricket-500 dark:text-qricket-400 font-bold">Qrickett</h1>

                <h2 class="mt-3 text-qricket-500 dark:text-qricket-400 text-xl font-semibold mb-1">Email Verification</h2>

                <div class="mb-4 text-sm text-gray-600 dark:text-gray-300 transition-colors duration-300">
                    {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
                </div>

                @if (session('error'))
                    <div class="mb-4 font-medium text-sm text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/20 p-3 rounded-lg border border-red-200 dark:border-red-800">
                        {{ session('error') }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/20 p-3 rounded-lg border border-green-200 dark:border-green-800">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('status') == 'verification-link-sent')
                    <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/20 p-3 rounded-lg border border-green-200 dark:border-green-800">
                        {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                    </div>
                @endif

                <!-- Resend Section -->
                <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-600">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Didn't receive the email?</p>
                    <form method="POST" action="{{ route('verification.send.guest') }}" class="inline">
                        @csrf
                        <input type="hidden" name="email" value="{{ old('email', session('verification_email')) }}">
                        <button type="submit" class="text-qricket-600 dark:text-qricket-400 hover:text-qricket-700 dark:hover:text-qricket-300 text-sm font-medium underline">
                            Click here to resend verification email
                        </button>
                    </form>
                </div>

                <!-- Back Button -->
                <div class="mt-6">
                    <a href="{{ route('login') }}" class="w-full bg-qricket-500 hover:bg-qricket-600 text-white font-semibold py-2 px-4 rounded-lg text-center block transition-colors duration-200">
                        Back to Login
                    </a>
                </div>
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
        
    </x-slot>
</x-guest-layout>