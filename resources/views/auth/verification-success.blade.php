<x-guest-layout title="Email Verified - Qrickett">
    <div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 px-4">
        <div class="max-w-md w-full bg-white dark:bg-gray-800 rounded-lg shadow p-8 text-center">
            <svg class="mx-auto w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2l4 -4m6 2a9 9 0 11-18 0a9 9 0 0118 0z" />
            </svg>
            <h1 class="mt-4 text-2xl font-semibold text-gray-900 dark:text-gray-100">Email Verified</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-300">Your email was verified successfully. You can now log in to your account.</p>
            <div class="mt-6">
                <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-qricket-500 hover:bg-qricket-600 text-white rounded-lg">Go to Login</a>
            </div>
        </div>
    </div>
</x-guest-layout>


