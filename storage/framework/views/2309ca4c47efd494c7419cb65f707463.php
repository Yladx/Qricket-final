<?php if (isset($component)) { $__componentOriginal69dc84650370d1d4dc1b42d016d7226b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b = $attributes; } ?>
<?php $component = App\View\Components\GuestLayout::resolve(['title' => 'Login - Qrickett'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('guest-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\GuestLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <!-- Back to Home Link -->
    <div class="fixed top-4 left-4 z-50">
        <a href="<?php echo e(route('welcome')); ?>" class="group inline-flex items-center px-5 py-3 bg-gradient-to-r from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-xl shadow-lg hover:shadow-xl border border-gray-200 dark:border-gray-700 transition-all duration-300 transform hover:scale-105 hover:-translate-y-0.5 text-gray-700 dark:text-gray-300 hover:text-qricket-600 dark:hover:text-qricket-400">
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
                <h1 class="text-4xl text-qricket-500 dark:text-qricket-400 font-bold">Qrickett</h1>

                <h2 class="mt-3 text-qricket-500 dark:text-qricket-400 text-xl font-semibold mb-1">Welcome Back!</h2>

                <p class="text-sm text-gray-600 dark:text-gray-300 mb-4 transition-colors duration-300">
                    Log in to continue where you left off.<br>
                    Don't have an account? 
                    <?php if(Route::has('register')): ?>
                        <a href="<?php echo e(route('register')); ?>" class="text-qricket-600 dark:text-qricket-400 underline hover:text-qricket-700 dark:hover:text-qricket-300 transition-colors duration-200">Create a new account now</a>, take less than a minute.
                    <?php endif; ?>
                </p>

    <!-- Session Status -->
                <?php if(session('status')): ?>
                    <div class="mb-4 text-sm text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/20 p-3 rounded-lg border border-green-200 dark:border-green-800">
                        <?php echo e(session('status')); ?>

                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo e(route('login')); ?>" class="flex flex-col gap-3">
        <?php echo csrf_field(); ?>

        <div>
                        <label class="text-sm text-gray-700 dark:text-gray-300 mb-1 inline-block transition-colors duration-200" for="email">Email</label>
                        <input type="email" 
                               name="email" 
                               id="email"
                               placeholder="Email or User ID" 
                               value="<?php echo e(old('email')); ?>"
                               required
                               autofocus
                               autocomplete="username"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-qricket-500 dark:focus:ring-qricket-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 transition-colors duration-200 <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 dark:border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"> 
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-red-500 dark:text-red-400 text-sm mt-1 transition-colors duration-200"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

                    <div>
                        <label class="text-sm text-gray-700 dark:text-gray-300 mb-1 inline-block transition-colors duration-200" for="password">Password</label>
                        <div class="relative">
                            <input type="password" 
                            name="password"
                                   id="password"
                                   placeholder="Password" 
                                   required
                                   autocomplete="current-password"
                                   onkeypress="return /[^\s]/i.test(event.key)"
                                   class="w-full px-4 py-2 pr-10 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-qricket-500 dark:focus:ring-qricket-400 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 transition-colors duration-200 <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 dark:border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <button type="button" data-password-toggle data-target="#password" aria-label="Show password" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                <svg data-icon-open xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg data-icon-closed xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-2.063-2.063A10.013 10.013 0 0019.542 12C18.268 7.943 14.477 5 10 5a9.958 9.958 0 00-4.243.938L3.707 2.293zM12.454 14.16l-1.421-1.421a3 3 0 01-3.772-3.772L5.43 6.136A7.963 7.963 0 0010 7c3.438 0 6.363 2.065 7.542 5-.371 1.068-1 2.022-1.82 2.86l-3.268-1.7z" clip-rule="evenodd" /></svg>
                            </button>
                        </div>
                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-red-500 dark:text-red-400 text-sm mt-1 transition-colors duration-200"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

                    <!-- Remember Me and Forgot Password -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember_me" 
                                   type="checkbox" 
                                   name="remember"
                                   class="rounded border-gray-300 dark:border-gray-600 text-qricket-600 dark:text-qricket-400 shadow-sm focus:ring-qricket-500 dark:focus:ring-qricket-400 bg-white dark:bg-gray-700">
                            <label for="remember_me" class="ms-2 text-sm text-gray-600 dark:text-gray-400 transition-colors duration-200">
                                <?php echo e(__('Remember me')); ?>

            </label>
                        </div>
                        <?php if(Route::has('password.request')): ?>
                            <a href="<?php echo e(route('password.request')); ?>" class="text-sm text-qricket-600 dark:text-qricket-400 hover:underline transition-colors duration-200">
                                Forgot password?
                            </a>
                        <?php endif; ?>
        </div>

        <!-- reCAPTCHA -->
                    <?php if(config('services.recaptcha.site_key')): ?>
        <div class="mt-4">
                <div class="g-recaptcha" data-sitekey="<?php echo e(config('services.recaptcha.site_key')); ?>"></div>
                            <?php $__errorArgs = ['g-recaptcha-response'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-red-500 dark:text-red-400 text-sm mt-1 transition-colors duration-200"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <!-- CAPTCHA validation message -->
                            <div id="captcha-error-message" class="text-red-500 dark:text-red-400 text-sm mt-1 transition-colors duration-200 hidden">
                                Please complete the CAPTCHA verification to continue.
                            </div>
                        </div>
            <?php endif; ?>

                    <!-- Terms and Conditions -->
                    <div class="flex items-start">
                        <input id="terms_agreement" 
                               type="checkbox" 
                               name="terms_agreement"
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
                        Log In 
                    </button>   
                    
                </form>
            </div>
        </div>
        </div>

    <!-- Terms and Conditions Modal -->
    <div id="terms-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full max-h-[80vh] overflow-hidden">
            <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Terms and Conditions</h3>
                <button id="terms-modal-close" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6 overflow-y-auto max-h-[60vh]">
                <div id="terms-content" class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                    <!-- Terms content will be loaded here -->
                </div>
            </div>
            <div class="flex justify-end p-6 border-t border-gray-200 dark:border-gray-700">
                <button id="terms-modal-ok" class="px-4 py-2 bg-qricket-500 hover:bg-qricket-600 text-white rounded-lg transition-colors duration-200">
                    I Understand
                </button>
            </div>
        </div>
    </div>

    <!-- Privacy Policy Modal -->
    <div id="privacy-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full max-h-[80vh] overflow-hidden">
            <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Privacy Policy</h3>
                <button id="privacy-modal-close" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6 overflow-y-auto max-h-[60vh]">
                <div id="privacy-content" class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                    <!-- Privacy content will be loaded here -->
                </div>
            </div>
            <div class="flex justify-end p-6 border-t border-gray-200 dark:border-gray-700">
                <button id="privacy-modal-ok" class="px-4 py-2 bg-qricket-500 hover:bg-qricket-600 text-white rounded-lg transition-colors duration-200">
                    I Understand
                </button>
            </div>
        </div>
    </div>

     <?php $__env->slot('scripts', null, []); ?> 
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
        <script src="<?php echo e(asset('js/auth-animations.js')); ?>"></script>
        
        <!-- reCAPTCHA Script -->
        <?php if(config('services.recaptcha.site_key')): ?>
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <?php endif; ?>

        <script>
            // Password validation - remove whitespace
            (function(){
                const input = document.getElementById('password');
                
                if(input){
                    // Remove whitespace from password field on input
                    input.addEventListener('input', function(e) {
                        if (e.inputType === 'insertText' || e.inputType === 'insertFromPaste') {
                            this.value = this.value.replace(/\s/g, '');
                        }
                    });
                    
                    // Prevent paste with whitespace in password
                    input.addEventListener('paste', function(e) {
                        e.preventDefault();
                        const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                        const cleanText = pastedText.replace(/\s/g, '');
                        const selectionStart = this.selectionStart;
                        const selectionEnd = this.selectionEnd;
                        this.value = this.value.substring(0, selectionStart) + cleanText + this.value.substring(selectionEnd);
                        this.setSelectionRange(selectionStart + cleanText.length, selectionStart + cleanText.length);
                    });
                }

                // Handle CAPTCHA validation only
                const form = document.querySelector('form[action="<?php echo e(route('login')); ?>"]');
                if(form){
                    form.addEventListener('submit', function(e){
                        const widget = document.querySelector('.g-recaptcha');
                        const captchaErrorMessage = document.getElementById('captcha-error-message');
                        
                        // Check CAPTCHA first
                        if(widget){
                            const resp = document.querySelector('textarea[name="g-recaptcha-response"]');
                            if(!resp || !resp.value){
                                e.preventDefault(); // Prevent form submission
                                
                                // Show error message
                                if(captchaErrorMessage) {
                                    captchaErrorMessage.classList.remove('hidden');
                                }
                                
                                // Scroll to CAPTCHA section
                                widget.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                
                                // Show SweetAlert for CAPTCHA
                                const isDark = document.documentElement.classList.contains('dark');
                                Swal && Swal.fire({
                                    icon: 'warning',
                                    title: 'Complete CAPTCHA',
                                    text: 'Please verify you\'re human to continue.',
                                    confirmButtonText: 'OK',
                                    confirmButtonColor: '#00B388',
                                    customClass: {
                                        popup: isDark ? 'dark:bg-gray-800 dark:text-gray-100' : 'bg-white text-gray-800',
                                        title: isDark ? 'dark:text-gray-100' : 'text-gray-800',
                                        htmlContainer: isDark ? 'dark:text-gray-300' : 'text-gray-600',
                                        confirmButton: 'bg-qricket-500 hover:bg-qricket-600 text-white px-4 py-2 rounded-lg font-medium'
                                    },
                                    width: '320px',
                                    padding: '1rem'
                                });
                                return;
                            } else {
                                // Hide error message if CAPTCHA is completed
                                if(captchaErrorMessage) {
                                    captchaErrorMessage.classList.add('hidden');
                                }
                            }
                        }
                        
                        // Show loading for form submission
                        Swal.fire({
                            title: 'Logging in...',
                            text: 'Please wait while we verify your credentials.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // Let the form submit normally - controller will handle redirects
                    });
                }

                // Hide CAPTCHA error message when CAPTCHA is completed
                const widget = document.querySelector('.g-recaptcha');
                if(widget) {
                    // Listen for CAPTCHA completion
                    const observer = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            if (mutation.type === 'childList') {
                                const resp = document.querySelector('textarea[name="g-recaptcha-response"]');
                                const captchaErrorMessage = document.getElementById('captcha-error-message');
                                
                                if(resp && resp.value && captchaErrorMessage) {
                                    captchaErrorMessage.classList.add('hidden');
                                }
                            }
                        });
                    });
                    
                    observer.observe(widget, { childList: true, subtree: true });
                }

                // Terms and Conditions Modal
                const termsModal = document.getElementById('terms-modal');
                const termsModalTrigger = document.getElementById('terms-modal-trigger');
                const termsModalClose = document.getElementById('terms-modal-close');
                const termsModalOk = document.getElementById('terms-modal-ok');
                const termsContent = document.getElementById('terms-content');

                // Privacy Policy Modal
                const privacyModal = document.getElementById('privacy-modal');
                const privacyModalTrigger = document.getElementById('privacy-modal-trigger');
                const privacyModalClose = document.getElementById('privacy-modal-close');
                const privacyModalOk = document.getElementById('privacy-modal-ok');
                const privacyContent = document.getElementById('privacy-content');

                // Load terms content
                fetch('/terms-and-conditions.txt')
                    .then(response => response.text())
                    .then(data => {
                        termsContent.textContent = data;
                    })
                    .catch(error => {
                        console.error('Error loading terms:', error);
                        termsContent.textContent = 'Error loading terms and conditions. Please try again later.';
                    });

                // Load privacy content
                fetch('/privacy-policy.txt')
                    .then(response => response.text())
                    .then(data => {
                        privacyContent.textContent = data;
                    })
                    .catch(error => {
                        console.error('Error loading privacy policy:', error);
                        privacyContent.textContent = 'Error loading privacy policy. Please try again later.';
                    });

                // Show terms modal
                termsModalTrigger.addEventListener('click', function() {
                    termsModal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                });

                // Show privacy modal
                privacyModalTrigger.addEventListener('click', function() {
                    privacyModal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                });

                // Hide modal functions
                function hideTermsModal() {
                    termsModal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }

                function hidePrivacyModal() {
                    privacyModal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }

                // Close terms modal events
                termsModalClose.addEventListener('click', hideTermsModal);
                termsModalOk.addEventListener('click', hideTermsModal);

                // Close privacy modal events
                privacyModalClose.addEventListener('click', hidePrivacyModal);
                privacyModalOk.addEventListener('click', hidePrivacyModal);

                // Close modal when clicking outside
                termsModal.addEventListener('click', function(e) {
                    if (e.target === termsModal) {
                        hideTermsModal();
                    }
                });

                privacyModal.addEventListener('click', function(e) {
                    if (e.target === privacyModal) {
                        hidePrivacyModal();
                    }
                });

                // Close modal with Escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        if (!termsModal.classList.contains('hidden')) {
                            hideTermsModal();
                        }
                        if (!privacyModal.classList.contains('hidden')) {
                            hidePrivacyModal();
                        }
                    }
                });
            })();
        </script>
     <?php $__env->endSlot(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $attributes = $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $component = $__componentOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php /**PATH C:\Users\YLAD\OneDrive\Desktop\Qricket\resources\views/auth/login.blade.php ENDPATH**/ ?>