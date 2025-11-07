<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // $middleware->append(\App\Http\Middleware\VerifyRecaptcha::class); // Temporarily disabled for development
        
        // Add session role middleware to web group (after auth)
        $middleware->web(append: \App\Http\Middleware\System\SetSessionRoleMiddleware::class);
        
        // Add session cleanup middleware to web group
        $middleware->web(append: \App\Http\Middleware\System\SessionCleanup::class);
        
        // Add navigation variables middleware to web group
        $middleware->web(append: \App\Http\Middleware\System\SetNavigationVariables::class);
        
        // Add user actions tracking middleware to web group
        $middleware->web(append: \App\Http\Middleware\TrackUserActions::class);
        
        // Add conditional console log middleware for admin debug mode
        $middleware->web(append: \App\Http\Middleware\ConditionalConsoleLog::class);
        
        // Add automatic feedback checker middleware
        $middleware->web(append: \App\Http\Middleware\AutomaticFeedbackChecker::class);
        
        // Add user status check middleware (after auth)
        $middleware->web(append: \App\Http\Middleware\CheckUserStatus::class);
        
        // Add organizer guard middleware only to specific routes (not globally)
        // $middleware->web(append: \App\Http\Middleware\OrganizerGuard::class);
        
        // Register the verified organizer middleware
        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'verified.organizer' => \App\Http\Middleware\Organizer\VerifiedOrganizerMiddleware::class,
            'admin' => \App\Http\Middleware\Admin\AdminMiddleware::class,
            'prevent.user.admin' => \App\Http\Middleware\Admin\PreventUserAdminAccess::class,
            'admin.permission' => \App\Http\Middleware\Admin\RequireAdminPermission::class,
            'admin.session' => \App\Http\Middleware\CheckAdminSession::class,
            'organizer.guard' => \App\Http\Middleware\Organizer\OrganizerGuard::class,
            'event.access' => \App\Http\Middleware\Security\VerifyEventAccess::class,
            'subscription.limits' => \App\Http\Middleware\User\CheckSubscriptionLimits::class,
            'track.event.view' => \App\Http\Middleware\TrackEventView::class,
            'track.event.save' => \App\Http\Middleware\TrackEventSave::class,
            'track.ticket.purchase' => \App\Http\Middleware\TrackTicketPurchase::class,
        ]);
        
        // Configure CSRF middleware to exclude Xendit webhook
        $middleware->validateCsrfTokens(except: [
            'xendit/webhook',
            'webhook/xendit',
            'test-webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();