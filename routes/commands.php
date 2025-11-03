<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Command Routes
|--------------------------------------------------------------------------
|
| Routes specifically for accessing console commands via HTTP
| These routes allow authorized access to run commands through web interface
| All routes are protected by authentication and authorization
|
*/

// Command execution routes - require authentication
Route::middleware(['auth', 'verified'])->group(function () {
    
    // List all available commands
    Route::get('/commands', function () {
        return response()->json([
            'commands' => [
                'auth' => [
                    'test:admin-login',
                    'test:admin-lockout',
                    'clear:admin-lockout',
                    'debug:session',
                    'check:session-data',
                    'session:lifetime',
                ],
                'testing' => [
                    'test:all-emails',
                    'test:certificate-data',
                    'test:qr-encryption',
                    'test:purchase',
                ],
                'maintenance' => [
                    'events:send-feedback',
                ],
                'xendit' => [
                    'xendit:check-account',
                    'xendit:status',
                    'xendit:invoice-data',
                ],
                'email' => [
                    'email:send-invoice',
                    'email:send-payment',
                ],
            ],
            'status' => 'success'
        ]);
    })->name('commands.index');
    
    // Execute a specific command
    Route::post('/commands/execute/{command}', function (string $command) {
        // Only allow specific commands to be executed via web
        $allowedCommands = [
            'test:admin-login',
            'session:lifetime',
            'test:certificate-data',
        ];
        
        if (!in_array($command, $allowedCommands)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Command not allowed to be executed via web'
            ], 403);
        }
        
        // Execute the command
        $exitCode = Artisan::call($command);
        $output = Artisan::output();
        
        return response()->json([
            'status' => 'success',
            'command' => $command,
            'exit_code' => $exitCode,
            'output' => $output
        ]);
    })->name('commands.execute');
    
});

// Admin-only command routes
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    
    // Admin commands execution
    Route::post('/admin/commands/execute/{command}', function (string $command) {
        $allowedAdminCommands = [
            'test:admin-login',
            'test:admin-lockout',
            'clear:admin-lockout',
            'test:logout-notification',
            'test:user-logout-notification',
            'xendit:check-account',
            'xendit:status',
            'events:send-feedback',
            'session:lifetime',
            'debug:session',
        ];
        
        if (!in_array($command, $allowedAdminCommands)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Command not allowed or insufficient permissions'
            ], 403);
        }
        
        $exitCode = Artisan::call($command);
        $output = Artisan::output();
        
        return response()->json([
            'status' => 'success',
            'command' => $command,
            'exit_code' => $exitCode,
            'output' => $output
        ]);
    })->name('admin.commands.execute');
    
});
