<x-guest-layout title="Forgot Password - Qrickett">
    <!-- Back to Home Link -->
    <div class="fixed top-4 left-4 z-50">
        <a href="{{ route('welcome') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 text-gray-700 dark:text-gray-300 hover:text-qricket-600 dark:hover:text-qricket-400">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Home
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

                <h2 class="mt-3 text-qricket-500 dark:text-qricket-400 text-xl font-semibold mb-1">Reset Password</h2>

                <p class="text-gray-600 dark:text-gray-300 mb-4 transition-colors duration-300">
                    Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
                </p>

                <!-- Session Status -->
                @if (session('status'))
                    <div class="mb-4 text-sm text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/20 p-3 rounded-lg border border-green-200 dark:border-green-800">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-3">
                    @csrf

                    <div>
                        <label class="text-sm text-gray-700 dark:text-gray-300 mb-1 inline-block transition-colors duration-200" for="email">Email</label>
                        <input type="email" 
                               name="email" 
                               id="email"
                               placeholder="Enter your email address" 
                               value="{{ old('email') }}"
                               required
                               autofocus
                               autocomplete="email"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-qricket-500 dark:focus:ring-qricket-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 transition-colors duration-200 @error('email') border-red-500 dark:border-red-400 @enderror"> 
                        @error('email')
                            <p class="text-red-500 dark:text-red-400 text-sm mt-1 transition-colors duration-200">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"  
                            class="bg-qricket-500 hover:bg-qricket-600 dark:bg-qricket-500 dark:hover:bg-qricket-600 text-white font-semibold py-2 rounded-lg shadow hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                        Email Password Reset Link 
                    </button>   
                    
                    <div class="flex items-center my-2">
                        <div class="flex-grow border-t border-gray-300 dark:border-gray-600"></div>
                        <span class="mx-2 text-gray-400 dark:text-gray-500">or</span>
                        <div class="flex-grow border-t border-gray-300 dark:border-gray-600"></div>
                    </div>
                    
                    <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 border border-gray-300 dark:border-gray-600 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200 text-gray-700 dark:text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        <span>Back to Login</span>
                    </a>

                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center transition-colors duration-200">
                        Don't have an account? 
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-qricket-600 dark:text-qricket-400 hover:underline transition-colors duration-200">Sign up here</a>
                        @endif
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
    </x-slot>
</x-guest-layout>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qrickett - Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .animate {
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.25s ease-in-out;
        }
    </style>
</head>

<body class="h-screen bg-gray-100">
  <div class="flex h-screen flex-col md:flex-row">

    <!-- LEFT SECTION - Hidden on mobile, visible on desktop -->
    <div class="hidden md:flex flex-1 flex-col items-center justify-center p-10 text-white" style="background: linear-gradient(135deg, #00B388 50%, #009970 100%);">
      <h1 id="changing-text" class="text-3xl font-bold mb-2 transition-opacity duration-300">Welcome To Qricket</h1>
      <p class="text-lg opacity-85">Your Event. Your Code.</p>
    </div>

    <!-- RIGHT SECTION - Full width on mobile -->
    <div class="flex flex-1 items-center justify-center bg-white w-full">
      <div class="w-full max-w-sm text-left p-6">

        <h1 class="text-2xl text-[#00B388] font-bold">Qricket</h1>

        <h2 class="mt-3 text-[#00B388] text-xl font-semibold mb-1">Reset Password</h2>

        <p class="text-gray-600 mb-4">
            Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
        </p>

        <!-- Session Status -->
        @if (session('status'))
            <div class="mb-4 text-sm text-green-600 bg-green-100 p-3 rounded-lg">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-3">
            @csrf

            <div>
                <label class="text-sm text-gray-700 mb-1 inline-block" for="email">Email</label>
                <input type="email" 
                       name="email" 
                       id="email"
                       placeholder="Enter your email address" 
                       value="{{ old('email') }}"
                       required
                       autofocus
                       autocomplete="email"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-600 transition @error('email') border-red-500 @enderror"> 
                @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"  
            class="bg-yellow-400 text-white font-semibold py-2 rounded-lg shadow hover:bg-yellow-500 transition">
                Email Password Reset Link </button>   
            
            <div class="flex items-center my-2">
                <div class="flex-grow border-t border-gray-350"></div>
                <span class="mx-2 text-gray-400">or</span>
                <div class="flex-grow border-t border-gray-350"></div>
            </div>
            
            <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 border border-gray-300 py-2 rounded hover:bg-gray-100 transition text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Back to Login</span>
            </a>

            <p class="text-xs text-gray-500 mt-2 text-center">
                Don't have an account? 
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="text-teal-600 hover:underline">Sign up here</a>
                @endif
            </p>
        </form>
      </div>
    </div>
  </div>

  <!-- External JavaScript for animations -->
  <script src="{{ asset('js/auth-animations.js') }}"></script>
</body>
</html>
