<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title ?? config('app.name', 'Qrickett')); ?></title>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <?php if(auth()->guard()->check()): ?>
    <meta name="user-id" content="<?php echo e(Auth::id()); ?>">
    <?php endif; ?>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <!-- Apply saved theme ASAP to avoid flash -->
    <script>(function(){try{if(localStorage.getItem('darkMode')==='true'){document.documentElement.classList.add('dark');}}catch(e){}})();</script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Pusher.js for Laravel Reverb -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    
    <!-- reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        qricket: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            200: '#99f6e4',
                            300: '#5eead4',
                            400: '#2dd4bf',
                            500: '#00B388',
                            600: '#009970',
                            700: '#0d9488',
                            800: '#115e59',
                            900: '#134e4a',
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.3s ease-in-out',
                        'slide-down': 'slideDown 0.3s ease-out',
                        'bounce-in': 'bounceIn 0.6s ease-out',
                        'slide-in-left': 'slideInLeft 0.4s ease-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideDown: {
                            '0%': { transform: 'translateY(-10px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                        bounceIn: {
                            '0%': { transform: 'scale(0.3)', opacity: '0' },
                            '50%': { transform: 'scale(1.05)' },
                            '70%': { transform: 'scale(0.9)' },
                            '100%': { transform: 'scale(1)', opacity: '1' },
                        },
                        slideInLeft: {
                            '0%': { transform: 'translateX(-20px)', opacity: '0' },
                            '100%': { transform: 'translateX(0)', opacity: '1' },
                        }
                    }
                }
            }
        }
    </script>

    <script>
        // Broadcasting configuration for frontend (Pusher Cloud vs Reverb)
        window.PUSHER_USE_CLOUD = '<?php echo e(env('BROADCAST_DRIVER', 'pusher')); ?>' === 'pusher';
        window.PUSHER_APP_KEY = window.PUSHER_USE_CLOUD ? '<?php echo e(env('PUSHER_APP_KEY', '')); ?>' : '<?php echo e(env('REVERB_APP_KEY', '')); ?>';
        window.PUSHER_APP_CLUSTER = window.PUSHER_USE_CLOUD ? '<?php echo e(env('PUSHER_APP_CLUSTER', '')); ?>' : '';
        window.PUSHER_PORT = <?php echo e(env('REVERB_PORT', 8080)); ?>; // used only when not cloud
    </script>

    <!-- Theme manager (respects saved preference, enables toggling if present) -->
    <script src="<?php echo e(asset('js/dark-mode.js')); ?>"></script>

    <!-- Include reusable password toggle script -->
    <script src="<?php echo e(asset('js/qricket-scripts.js')); ?>?v=<?php echo e(time()); ?>"></script>
    
    <!-- Global Notification Handler -->
    <script src="<?php echo e(asset('js/notifications.js')); ?>"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- SweetAlert Utilities -->
    <script src="<?php echo e(asset('js/sweetalert-utils.js')); ?>"></script>
</head>
<body class="h-screen bg-gray-100 dark:bg-gray-900 transition-colors duration-300">
    <!-- Flash Alerts -->
    
    
    <!-- Main Content -->
    <main class="h-full">
        <?php echo e($slot); ?>

    </main>

    <!-- Scripts -->
    <?php if(isset($scripts)): ?>
        <?php echo e($scripts); ?>

    <?php endif; ?>
</body>
</html>
<?php /**PATH C:\Users\YLAD\OneDrive\Desktop\Qricket\resources\views/layouts/guest.blade.php ENDPATH**/ ?>