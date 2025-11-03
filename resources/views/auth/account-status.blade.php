<x-guest-layout>
    <x-slot name="title">{{ $type === 'deleted' ? 'Account Deletion' : 'Account Restricted' }} - Qricket</x-slot>
    
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 px-4 py-12">
        <div class="max-w-md w-full">
            @if($type === 'deleted')
                <!-- Account Deletion Card -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden animate-slide-down">
                    <!-- Header with Icon -->
                    <div class="bg-gradient-to-br from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 px-6 py-8 text-center">
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-red-100 dark:bg-red-900/30 mb-4">
                            <svg class="w-10 h-10 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Account Deletion In Process</h1>
                    </div>
                    
                    <!-- Content -->
                    <div class="px-6 py-6 space-y-4">
                        <p class="text-gray-600 dark:text-gray-300 text-center">
                            Your account has been scheduled for deletion. You can cancel this process by entering your email address below.
                        </p>
                        
                        <!-- Info Box -->
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                    Your account will be permanently deleted in 30 days from the deletion date if not cancelled.
                                </p>
                            </div>
                        </div>
                        
                        <!-- Flash Messages -->
                        @if(session('error'))
                            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
                                <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
                            </div>
                        @endif
                        
                        @if(session('success'))
                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3">
                                <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
                            </div>
                        @endif
                        
                        <!-- Cancel Deletion Form -->
                        <form id="cancelDeletionForm" method="POST" action="{{ route('account.cancel-deletion') }}" class="space-y-4">
                            @csrf
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Email Address
                                </label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    required
                                    class="w-full px-4 py-3 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-qricket-500 focus:border-qricket-500 transition-colors"
                                    placeholder="Enter your account email"
                                    value="{{ old('email') }}"
                                >
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                                <button 
                                    type="submit"
                                    id="cancelDeletionBtn"
                                    class="flex-1 bg-qricket-500 hover:bg-qricket-600 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span id="btnText">Cancel Deletion</span>
                                </button>
                                <a 
                                    href="{{ route('login') }}"
                                    class="flex-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold py-3 px-6 rounded-lg transition-colors duration-200 text-center"
                                >
                                    Go to Login
                                </a>
                            </div>
                        </form>
                        
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const form = document.getElementById('cancelDeletionForm');
                            const submitBtn = document.getElementById('cancelDeletionBtn');
                            const btnText = document.getElementById('btnText');
                            
                            if (form) {
                                form.addEventListener('submit', function(e) {
                                    e.preventDefault();
                                    
                                    // Disable button and show loading state
                                    submitBtn.disabled = true;
                                    btnText.textContent = 'Processing...';
                                    
                                    const formData = new FormData(form);
                                    
                                    fetch(form.action, {
                                        method: 'POST',
                                        body: formData,
                                        headers: {
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'Accept': 'application/json'
                                        }
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            // Show success SweetAlert
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Account Deletion Cancelled',
                                                text: data.message || 'Your account deletion has been cancelled successfully. You will be redirected to the login page.',
                                                confirmButtonColor: '#00B388',
                                                confirmButtonText: 'Go to Login',
                                                allowOutsideClick: false,
                                                allowEscapeKey: false,
                                                timer: 3000,
                                                timerProgressBar: true,
                                                didClose: () => {
                                                    window.location.href = '{{ route('login') }}';
                                                }
                                            }).then(() => {
                                                window.location.href = '{{ route('login') }}';
                                            });
                                        } else {
                                            // Show error SweetAlert
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Error',
                                                text: data.message || 'An error occurred while cancelling your account deletion. Please try again.',
                                                confirmButtonColor: '#dc2626',
                                                confirmButtonText: 'OK'
                                            });
                                            
                                            // Re-enable button
                                            submitBtn.disabled = false;
                                            btnText.textContent = 'Cancel Deletion';
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: 'An error occurred. Please try again.',
                                            confirmButtonColor: '#dc2626',
                                            confirmButtonText: 'OK'
                                        });
                                        
                                        // Re-enable button
                                        submitBtn.disabled = false;
                                        btnText.textContent = 'Cancel Deletion';
                                    });
                                });
                            }
                        });
                        </script>
                    </div>
                </div>
                
            @elseif($type === 'banned')
                <!-- Account Banned Card -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden animate-slide-down">
                    <!-- Header with Icon -->
                    <div class="bg-gradient-to-br from-orange-50 to-yellow-50 dark:from-orange-900/20 dark:to-yellow-900/20 px-6 py-8 text-center">
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-orange-100 dark:bg-orange-900/30 mb-4">
                            <svg class="w-10 h-10 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Account Restricted</h1>
                    </div>
                    
                    <!-- Content -->
                    <div class="px-6 py-6 space-y-4">
                        <p class="text-gray-600 dark:text-gray-300 text-center">
                            Your account has been restricted. Please review the information below:
                        </p>
                        
                        @if($user && isset($user['banned_reason']) && $user['banned_reason'])
                            <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-4">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-orange-600 dark:text-orange-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <div class="flex-1">
                                        <strong class="text-sm font-semibold text-orange-900 dark:text-orange-200 block mb-1">Reason for Restriction:</strong>
                                        <p class="text-sm text-orange-800 dark:text-orange-200">{{ $user['banned_reason'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        @if($user && isset($user['banned_at']) && $user['banned_at'])
                            <div class="bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg p-3">
                                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span>Restricted on: <strong class="text-gray-900 dark:text-gray-100">{{ \Carbon\Carbon::parse($user['banned_at'])->format('M d, Y g:i A') }}</strong></span>
                                </div>
                            </div>
                        @endif
                        
                        @if($user && isset($user['banned_until']) && $user['banned_until'])
                            <!-- Countdown Timer -->
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-5 text-center">
                                <div class="flex items-center justify-center mb-3">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-sm font-medium text-blue-800 dark:text-blue-200" id="liftLabel">Time Remaining:</p>
                                </div>
                                <div id="timer" class="text-3xl font-bold text-blue-600 dark:text-blue-400 mb-2 font-mono tracking-wider">Calculating...</div>
                                <p class="text-xs text-blue-700 dark:text-blue-300">Restriction will be lifted automatically</p>
                                <div id="liftBanner" class="hidden mt-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3 text-sm text-green-800 dark:text-green-200">
                                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Restriction ended. Please log in again.
                                </div>
                            </div>
                        @else
                            <!-- Permanent Restriction -->
                            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <strong class="text-sm font-semibold text-red-900 dark:text-red-200 block mb-1">Permanent Restriction</strong>
                                        <p class="text-sm text-red-800 dark:text-red-200">This restriction is permanent. Please contact support if you believe this is an error.</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Flash Messages -->
                        @if(session('error'))
                            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
                                <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
                            </div>
                        @endif
                        
                        @if(session('success'))
                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3">
                                <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
                            </div>
                        @endif
                        
                        <a 
                            href="{{ route('login') }}" 
                            id="primaryAction"
                            class="block w-full bg-qricket-500 hover:bg-qricket-600 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 text-center shadow-md hover:shadow-lg flex items-center justify-center"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            Go to Login
                        </a>
                    </div>
                </div>
                
                @if($user && isset($user['banned_until']) && $user['banned_until'])
                <script>
                    function updateTimer() {
                        const bannedUntil = @json($user['banned_until']);
                        const liftLabel = document.getElementById('liftLabel');
                        if (!bannedUntil) {
                            liftLabel.textContent = 'Permanent restriction (contact support)';
                            document.getElementById('timer').textContent = '—';
                            return;
                        }
                        const end = new Date(bannedUntil);
                        liftLabel.textContent = 'Restriction lift time: ' + end.toLocaleString();

                        let redirected = false;
                        function tick() {
                            const now = new Date();
                            let diff = end - now;
                            if (diff <= 0) {
                                diff = 0;
                                const banner = document.getElementById('liftBanner');
                                if (banner) banner.classList.remove('hidden');
                                const action = document.getElementById('primaryAction');
                                if (action) action.textContent = 'Log in now';
                                if (!redirected) {
                                    redirected = true;
                                    setTimeout(function(){ window.location.href = @json(route('login')); }, 3000);
                                }
                            }
                            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                            document.getElementById('timer').textContent = `${days}d ${hours}h ${minutes}m`;
                        }
                        tick();
                        setInterval(tick, 1000);
                    }
                    updateTimer();
                </script>
                @endif
            @endif
        </div>
    </div>
</x-guest-layout>
