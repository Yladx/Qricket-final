<?php

use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\Payment\SubscriptionController;
use App\Http\Controllers\Payment\XenditWebhookController;
use App\Http\Controllers\User\OrganizerVerificationController;
use App\Http\Controllers\Support\HelpSupportController;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Notifications\NotificationController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Include broadcasting routes
require __DIR__.'/channels.php';

// Include command routes
require __DIR__.'/commands.php';

// Simple database test
Route::get('/test-db', function() {
    try {
        $count = \DB::table('events')->count();
        return "Database connection working. Events count: " . $count;
    } catch (\Exception $e) {
        return "Database error: " . $e->getMessage();
    }
});

Route::get('/', function () {
    $upcomingEvents = \App\Models\Event::whereIn('status', ['published', 'approved'])
        ->where('start_date', '>=', now())
        ->orderBy('start_date', 'asc')
        ->limit(3)
        ->get();
    
    return view('welcome', compact('upcomingEvents'));
})->name('welcome');

// Debug route to check events (remove in production)
Route::get('/debug-events', function() {
    $allEvents = \App\Models\Event::all(['id', 'title', 'status', 'start_date']);
    $publishedEvents = \App\Models\Event::whereIn('status', ['published', 'approved'])->get(['id', 'title', 'status', 'start_date']);
    $featuredEvents = app(\App\Http\Controllers\Events\EventController::class)->getFeaturedEvents(200);
    
    // Also check raw database query
    $rawEvents = \DB::table('events')->select('id', 'title', 'status', 'start_date')->get();
    
    return response()->json([
        'all_events' => $allEvents,
        'published_or_approved_events' => $publishedEvents,
        'featured_events' => $featuredEvents,
        'raw_db_events' => $rawEvents,
        'counts' => [
            'all' => $allEvents->count(),
            'published_or_approved' => $publishedEvents->count(),
            'featured' => $featuredEvents->count(),
            'raw_db' => $rawEvents->count()
        ]
    ]);
});

// Test route without middleware
Route::get('/test-staff', function() {
    return 'Test staff route working! User: ' . (auth()->check() ? auth()->user()->email : 'Not authenticated');
});

// Test organizer route
Route::get('/organizer/test', function() {
    return 'Organizer route working!';
})->middleware('auth');

// Staff Management Routes - Step 5
Route::prefix('organizer')->middleware(['auth', 'organizer.guard'])->group(function () {
    Route::get('/events/create/staff', [\App\Http\Controllers\Organizer\EventManagement\StaffController::class, 'step5'])->name('organizer.events.create.staff');
    Route::post('/events/staff', [\App\Http\Controllers\Organizer\EventManagement\StaffController::class, 'step5Store'])->name('organizer.events.staff.store');
    Route::get('/events/staff/search', [\App\Http\Controllers\Organizer\EventManagement\StaffController::class, 'searchStaff'])->name('organizer.events.staff.search');
});

// Admin Event Approval Routes (use admin guard, not user auth)
Route::prefix('admin')->middleware(['auth:admin', 'admin'])->group(function () {
    // These routes are now handled in routes/admin.php
});

Route::get('/dashboard', function () {
    return redirect()->route('welcome');
})->middleware(['auth', 'verified'])->name('dashboard');

// Public subscription route - guests can view plans
Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
Route::get('/subscription/purchase/{planSlug?}', [SubscriptionController::class, 'purchase'])->name('subscription.purchase');

// Public subscription current route - authenticated users can view their subscription
Route::get('/subscription/current', [SubscriptionController::class, 'current'])->name('subscription.current')->middleware('auth');

// Subscription invoice route
Route::get('/subscription/invoice/{subscription}', [SubscriptionController::class, 'invoice'])->name('subscription.invoice')->middleware('auth');

// Help & Support route - accessible to all users
Route::get('/help', [HelpSupportController::class, 'index'])->name('help.index');
Route::post('/help', [HelpSupportController::class, 'store'])->name('help.store');
Route::get('/tutorials', [HelpSupportController::class, 'tutorials'])->name('tutorials.index');

// Test routes outside auth middleware for debugging
Route::get('/test-search', function() {
    return response()->json(['message' => 'Test route working', 'users' => \App\Models\User::count()]);
});

// Multiple fallback routes for user search
Route::post('/my-tickets/search-users', [\App\Http\Controllers\MyTicketsController::class, 'searchUsers'])->name('my-tickets.search-users');
Route::post('/search-users', [\App\Http\Controllers\MyTicketsController::class, 'searchUsers'])->name('search-users');
Route::get('/search-users', [\App\Http\Controllers\MyTicketsController::class, 'searchUsers'])->name('search-users-get');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/social-media', [ProfileController::class, 'updateSocialMedia'])->name('profile.social-media.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Discount validation route
    Route::post('/discount/validate', [\App\Http\Controllers\DiscountController::class, 'validateDiscount'])->name('discount.validate');

    // My Tickets routes
    Route::get('/my-tickets', [\App\Http\Controllers\MyTicketsController::class, 'index'])->name('my-tickets.index');
    Route::post('/my-tickets/filter', [\App\Http\Controllers\MyTicketsController::class, 'getFilteredTickets'])->name('my-tickets.filter');
    Route::get('/my-tickets/event-tickets', [\App\Http\Controllers\MyTicketsController::class, 'getEventTickets'])->name('my-tickets.event-tickets');
    Route::post('/my-tickets/share', [\App\Http\Controllers\MyTicketsController::class, 'shareTickets'])->name('my-tickets.share');

    // User search API for ticket sharing
    Route::get('/api/users/search', [\App\Http\Controllers\MyTicketsController::class, 'searchUsers'])->name('api.users.search');

    // Ticket sharing routes
    Route::get('/events/{event}/share-tickets', [\App\Http\Controllers\TicketShareController::class, 'show'])->name('ticket-shares.show');
    Route::post('/ticket-shares', [\App\Http\Controllers\TicketShareController::class, 'store'])->name('ticket-shares.store');
    Route::post('/ticket-shares/{share}/accept', [\App\Http\Controllers\TicketShareController::class, 'accept'])->name('ticket-shares.accept');
    Route::post('/ticket-shares/{share}/decline', [\App\Http\Controllers\TicketShareController::class, 'decline'])->name('ticket-shares.decline');
    Route::post('/ticket-shares/{share}/cancel', [\App\Http\Controllers\TicketShareController::class, 'cancel'])->name('ticket-shares.cancel');
    Route::get('/ticket-shares/available-tickets', [\App\Http\Controllers\TicketShareController::class, 'getAvailableTickets'])->name('ticket-shares.available-tickets');

// Subscription routes - require authentication for purchase
    Route::post('/subscription', [SubscriptionController::class, 'create'])->name('subscription.create');
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::post('/subscription/reactivate', [SubscriptionController::class, 'reactivate'])->name('subscription.reactivate');
    Route::get('/subscription/success', [SubscriptionController::class, 'success'])->name('subscription.success');
    Route::get('/subscription/failure', [SubscriptionController::class, 'failure'])->name('subscription.failure');
    Route::post('/subscription/webhook', [SubscriptionController::class, 'webhook'])->name('subscription.webhook');
    
    // Payment status check route (for debugging)
    Route::get('/subscription/{subscription}/check-payment', [SubscriptionController::class, 'checkPaymentStatus'])
        ->name('subscription.check-payment');
    
    // Organizer verification routes
    Route::get('/organizer/verification', [OrganizerVerificationController::class, 'show'])->name('organizer.verification');
    Route::post('/organizer/verification', [OrganizerVerificationController::class, 'store'])->name('organizer.verification.store');
    
    // Refund routes
    Route::post('/refund/request', [\App\Http\Controllers\Payment\RefundController::class, 'store'])->name('refund.request');
    
    // Organizer profile routes (public)
    Route::get('/organizer/{userId}/profile', [\App\Http\Controllers\OrganizerProfileController::class, 'show'])->name('organizer.profile');
    
    // Organizer dashboard - accessible to all authenticated users
    Route::prefix('organizer')->middleware(['organizer.guard'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Organizer\OrganizerDashboardController::class, 'index'])->name('organizer.dashboard');
        Route::get('/dashboard/report', [\App\Http\Controllers\Organizer\OrganizerDashboardController::class, 'generateReport'])->name('organizer.dashboard.report');
        Route::get('/events', [\App\Http\Controllers\Events\EventController::class, 'index'])->name('organizer.events.index');
    });
    
    // Event management routes - accessible to organizers and staff
    Route::prefix('organizer')->middleware(['organizer.guard'])->group(function () {
        // Event management (accessible to organizers and staff)
        Route::get('/events/{event}/activity-log', [\App\Http\Controllers\Events\EventController::class, 'activityLog'])->name('organizer.events.activity-log');
        Route::get('/events/{event}/preview', [\App\Http\Controllers\Organizer\EventManagement\EventEditController::class, 'preview'])->name('organizer.events.preview');
        
        // Ticket limit purchase routes
        Route::get('/events/{event}/ticket-limits', [\App\Http\Controllers\Organizer\TicketLimitController::class, 'show'])->name('organizer.events.ticket-limits');
        Route::post('/events/{event}/ticket-limits/purchase', [\App\Http\Controllers\Organizer\TicketLimitController::class, 'purchase'])->name('organizer.events.ticket-limits.purchase');
        Route::post('/ticket-limits/validate-discount', [\App\Http\Controllers\Organizer\TicketLimitController::class, 'validateDiscount'])->name('organizer.ticket-limits.validate-discount');
        
        // Dashboard routes
        Route::get('/events/{event}/manage', [\App\Http\Controllers\Organizer\EventManagement\EventDashboardController::class, 'index'])
            ->name('organizer.events.manage');
        Route::get('/events/{event}/sales', [\App\Http\Controllers\Organizer\EventManagement\EventDashboardController::class, 'salesReport'])
            ->name('organizer.events.sales');
        Route::get('/events/{event}/sales/export', [\App\Http\Controllers\Organizer\EventManagement\EventDashboardController::class, 'exportSales'])
            ->name('organizer.events.sales.export');
        Route::get('/events/{event}/activity-load-more', [\App\Http\Controllers\Organizer\EventManagement\EventDashboardController::class, 'loadMoreActivity'])
            ->name('organizer.events.activity-load-more');
        
        // Attendance routes
        Route::get('/events/{event}/attendance', [\App\Http\Controllers\Organizer\EventManagement\EventAttendanceController::class, 'attendance'])
            ->middleware('event.access:manage_attendance')
            ->name('organizer.events.attendance');
        Route::get('/events/{event}/attendance/export', [\App\Http\Controllers\Organizer\EventManagement\EventAttendanceController::class, 'exportAttendance'])->name('organizer.events.attendance.export');
        Route::get('/events/{event}/scanner', [\App\Http\Controllers\Organizer\EventManagement\EventAttendanceController::class, 'scanner'])
            ->middleware('event.access:manage_attendance')
            ->name('organizer.events.scanner');
        Route::post('/events/{event}/attendance/qr-scan', [\App\Http\Controllers\Organizer\EventManagement\EventAttendanceController::class, 'processQRScan'])
            ->middleware('event.access:manage_attendance')
            ->name('organizer.events.attendance.qr-scan');
        Route::post('/events/{event}/attendance/manual-checkin', [\App\Http\Controllers\Organizer\EventManagement\EventAttendanceController::class, 'manualCheckIn'])
            ->middleware('event.access:manage_attendance')
            ->name('organizer.events.attendance.manual-checkin');
        Route::get('/events/{event}/attendance/search', [\App\Http\Controllers\Organizer\EventManagement\EventAttendanceController::class, 'searchAttendees'])->name('organizer.events.attendance.search');
        
        // Certificate routes
        Route::post('/events/{event}/attendance/certificates', [\App\Http\Controllers\Organizer\EventManagement\EventCertificateController::class, 'generateCertificates'])->name('organizer.events.attendance.certs');
        
        // Edit routes
        Route::get('/events/{event}/tickets', [\App\Http\Controllers\Organizer\EventManagement\TicketsController::class, 'tickets'])
            ->middleware('event.access:manage_ticket')
            ->name('organizer.events.tickets');
        Route::get('/events/{event}/staff', [\App\Http\Controllers\Organizer\EventManagement\EventEditController::class, 'staff'])
            ->name('organizer.events.staff');
        Route::get('/events/{event}/qr', [\App\Http\Controllers\Organizer\EventManagement\EventEditController::class, 'qrCode'])
            ->name('organizer.events.qr');
        
        // Analytics API endpoints
        Route::get('/events/{event}/api/recent-sales', [\App\Http\Controllers\Organizer\EventManagement\EventAnalyticsController::class, 'getRecentSales'])->name('organizer.events.api.recent-sales');
        Route::get('/events/{event}/api/sales-summary', [\App\Http\Controllers\Organizer\EventManagement\EventAnalyticsController::class, 'getSalesSummary'])->name('organizer.events.api.sales-summary');

        // Event News (accessible to organizers and staff)
        Route::get('/events/{event}/news', [\App\Http\Controllers\Organizer\EventNewsController::class, 'index'])
            ->middleware('event.access:manage_news')
            ->name('organizer.events.news.index');
        // Edit Event quick links
        Route::get('/events/{event}/edit/basic', [\App\Http\Controllers\Organizer\EventManagement\EventEditController::class, 'editBasic'])
            ->name('organizer.events.edit.basic');
        Route::get('/events/{event}/edit/layout', [\App\Http\Controllers\Organizer\EventManagement\EventEditController::class, 'editLayout'])
            ->name('organizer.events.edit.layout');
        Route::get('/events/{event}/edit/certificate', [\App\Http\Controllers\Organizer\EventManagement\EventEditController::class, 'editCertificate'])
            ->name('organizer.events.edit.certificate');
        
        // Info routes (for viewing after event has ended) - use the same methods as edit routes
        Route::get('/events/{event}/info/basic', [\App\Http\Controllers\Organizer\EventManagement\EventEditController::class, 'editBasic'])
            ->name('organizer.events.info.basic');
        Route::get('/events/{event}/info/layout', [\App\Http\Controllers\Organizer\EventManagement\EventEditController::class, 'editLayout'])
            ->name('organizer.events.info.layout');
        Route::get('/events/{event}/info/certificate', [\App\Http\Controllers\Organizer\EventManagement\EventEditController::class, 'editCertificate'])
            ->name('organizer.events.info.certificate');
        Route::get('/events/{event}/info/tickets', [\App\Http\Controllers\Organizer\EventManagement\TicketsController::class, 'tickets'])
            ->name('organizer.events.info.tickets');
        Route::get('/events/{event}/info/staff', [\App\Http\Controllers\Organizer\EventManagement\StaffController::class, 'manage'])
            ->name('organizer.events.info.staff');
        Route::put('/events/{event}/certificate', [\App\Http\Controllers\Organizer\EventManagement\EventEditController::class, 'updateCertificate'])
            ->name('organizer.events.certificate.update');
        Route::post('/events/certificate/autosave', [\App\Http\Controllers\Organizer\EventManagement\EventEditController::class, 'autosaveCertificate'])
            ->name('organizer.events.certificate.autosave');
        Route::get('/events/{event}/news/create', [\App\Http\Controllers\Organizer\EventNewsController::class, 'create'])->name('organizer.events.news.create');
        Route::post('/events/{event}/news', [\App\Http\Controllers\Organizer\EventNewsController::class, 'store'])->name('organizer.events.news.store');
        Route::get('/events/{event}/news/{news}/edit', [\App\Http\Controllers\Organizer\EventNewsController::class, 'edit'])->name('organizer.events.news.edit');
        Route::put('/events/{event}/news/{news}', [\App\Http\Controllers\Organizer\EventNewsController::class, 'update'])->name('organizer.events.news.update');
        
    // Event activity trend data for real-time updates
    Route::get('/events/{event}/activity-trend', [\App\Http\Controllers\Organizer\EventManagement\EventDashboardController::class, 'getActivityTrend'])
        ->middleware('event.access:manage_event')
        ->name('organizer.events.activity-trend');
    
    // Event Feedback Management (accessible to organizers and staff)
    Route::get('/events/{event}/feedback', [\App\Http\Controllers\Events\EventFeedbackController::class, 'manage'])
        ->middleware('event.access:manage_event')
        ->name('organizer.events.feedback');
    
        Route::delete('/events/{event}/news/{news}', [\App\Http\Controllers\Organizer\EventNewsController::class, 'destroy'])->name('organizer.events.news.destroy');
        Route::post('/events/{event}/news/{news}/publish', [\App\Http\Controllers\Organizer\EventNewsController::class, 'publish'])->name('organizer.events.news.publish');
        Route::get('/events/{event}/news/{news}/image/{filename}', [\App\Http\Controllers\Organizer\EventNewsController::class, 'serveImage'])->name('organizer.events.news.image');
        // Update event status (e.g., cancel pending approval back to draft)
        Route::put('/events/{event}/status', [\App\Http\Controllers\Events\EventController::class, 'updateStatus'])->name('organizer.events.update-status');
        
        // Payout routes
        Route::get('/events/{event}/payout', [\App\Http\Controllers\Organizer\PayoutRequestController::class, 'show'])->name('organizer.events.payout');
        Route::get('/events/{event}/payout/calculate', [\App\Http\Controllers\Organizer\PayoutRequestController::class, 'calculatePayout'])->name('organizer.events.payout.calculate');
        Route::post('/events/{event}/payout/request-new', [\App\Http\Controllers\Organizer\PayoutRequestController::class, 'requestPayout'])->name('organizer.events.payout.request-new');
        Route::get('/events/{event}/payout/status', [\App\Http\Controllers\Organizer\PayoutRequestController::class, 'getPayoutStatus'])->name('organizer.events.payout.status');
        Route::post('/events/{event}/payout/collect-new', [\App\Http\Controllers\Organizer\PayoutRequestController::class, 'collectPayout'])->name('organizer.events.payout.collect-new');
        Route::get('/events/{event}/payout/success', [\App\Http\Controllers\Organizer\PayoutRequestController::class, 'payoutSuccess'])->name('organizer.events.payout.success');
        Route::get('/events/{event}/payout/failure', [\App\Http\Controllers\Organizer\PayoutRequestController::class, 'payoutFailure'])->name('organizer.events.payout.failure');
    });
    
    // Event creation routes - use organizer.guard (admins bypass verification)
    Route::prefix('organizer')->middleware('organizer.guard')->group(function () {
        // Event creation routes are now in routes/event.php
        
        // Legacy single-step creation (keeping for compatibility)
        Route::get('/events/create/legacy', [\App\Http\Controllers\Events\EventController::class, 'create'])->name('organizer.events.create.legacy');
        Route::post('/events', [\App\Http\Controllers\Events\EventController::class, 'store'])->name('organizer.events.store');
    });

    // User event browsing routes
    Route::get('/events', [\App\Http\Controllers\Events\EventBrowsingController::class, 'browse'])->name('events.browse');
    Route::get('/events/tag-suggestions', [\App\Http\Controllers\Events\EventBrowsingController::class, 'getTagSuggestions'])->name('events.tag-suggestions');
    
    // Mode switching route
    Route::post('/user/mode-switch', [\App\Http\Controllers\User\UserController::class, 'switchMode'])->name('user.mode-switch');
    
    // Saved events routes (must come before /events/{event} route)
    Route::get('/events/saved', [\App\Http\Controllers\User\SavedEventController::class, 'index'])->name('events.saved');
    Route::post('/events/{event}/save', [\App\Http\Controllers\User\SavedEventController::class, 'store'])
        ->middleware('track.event.save')
        ->name('events.save');
    Route::delete('/events/{event}/unsave', [\App\Http\Controllers\User\SavedEventController::class, 'destroy'])
        ->middleware('track.event.save')
        ->name('events.unsave');
    Route::post('/events/{event}/toggle-save', [\App\Http\Controllers\User\SavedEventController::class, 'toggle'])->name('events.toggle-save');
    
    Route::get('/events/{event}', [\App\Http\Controllers\Events\EventController::class, 'show'])
        ->middleware('track.event.view')
        ->name('events.show');

    // User Support Tickets
    Route::get('/support/my-tickets', [\App\Http\Controllers\Support\SupportTicketController::class, 'myTickets'])->name('support.my');
    Route::get('/support/tickets/{ticketNumber}', [\App\Http\Controllers\Support\SupportTicketController::class, 'myTicketShow'])->name('support.ticket');
    Route::post('/support/tickets/{ticketNumber}/reply', [\App\Http\Controllers\Support\SupportTicketController::class, 'userReply'])->name('support.ticket.reply');

    // Notifications page
    Route::get('/notifications', [\App\Http\Controllers\Notifications\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/go', [\App\Http\Controllers\Notifications\NotificationController::class, 'go'])->name('notifications.go');
    Route::post('/notifications/{id}/mark-read', [\App\Http\Controllers\Notifications\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Notifications\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
});

// API routes for AJAX requests
Route::prefix('api')->group(function () {
    Route::get('/user/verified-organizer', function () {
        // If admin is logged in, grant organizer verification immunity
        if (\Illuminate\Support\Facades\Auth::guard('admin')->check()) {
            return response()->json([
                'is_verified_organizer' => true,
                'reason' => 'admin_immunity'
            ]);
        }

        $user = auth()->user();
        return response()->json([
            'is_verified_organizer' => $user ? $user->isVerifiedOrganizer() : false
        ]);
    });
    
    // Debug route to check current user status
    Route::get('/user/debug', function () {
        $user = auth()->user();
        return response()->json([
            'user_id' => $user->id,
            'full_name' => $user->full_name,
            'organizer' => $user->organizer,
            'verified_organizer' => $user->verified_organizer,
            'is_verified_organizer' => $user->isVerifiedOrganizer(),
            'is_organizer' => $user->isOrganizer()
        ]);
    });
});

// Public-safe endpoint: returns 0 when not authenticated, used by header badge pre-login
Route::prefix('api')->group(function () {
    Route::get('/notifications/unread-count', function () {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['count' => 0]);
        }
        $count = \App\Models\Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
        return response()->json(['count' => $count]);
    });
});

// Email preview routes (only in development)
if (app()->environment('local')) {
    Route::get('/email/preview/invoice', function () {
        $subscription = \App\Models\Subscription::with('user')->first();
        if (!$subscription) {
            return 'No subscription found. Please create a subscription first.';
        }
        
        $plan = [
            'id' => $subscription->plan_id,
            'name' => ucfirst($subscription->plan_id) . ' Plan',
            'price' => $subscription->amount,
        ];
        
        $invoiceUrl = route('subscription.success') . '?test=true';
        
        return view('emails.invoice', [
            'subscription' => $subscription,
            'invoiceUrl' => $invoiceUrl,
            'planDetails' => $plan,
            'user' => $subscription->user,
        ]);
    })->name('email.preview.invoice');

    Route::get('/email/preview/payment-confirmation', function () {
        $subscription = \App\Models\Subscription::with('user')->first();
        if (!$subscription) {
            return 'No subscription found. Please create a subscription first.';
        }
        
        $plan = [
            'id' => $subscription->plan_id,
            'name' => ucfirst($subscription->plan_id) . ' Plan',
            'price' => $subscription->amount,
        ];
        
        return view('emails.payment-confirmation', [
            'subscription' => $subscription,
            'planDetails' => $plan,
            'user' => $subscription->user,
        ]);
    })->name('email.preview.payment-confirmation');
}

// Xendit webhook routes (no auth required) - Single unified webhook
Route::post('/webhook/xendit', [App\Http\Controllers\Payment\XenditWebhookController::class, 'handle'])->name('webhook.xendit');
Route::post('/xendit/webhook', [App\Http\Controllers\Payment\XenditWebhookController::class, 'handle'])->name('xendit.webhook');

// Test webhook endpoint (for debugging)
Route::post('/test-webhook', function(Request $request) {
    return response()->json([
        'status' => 'success',
        'message' => 'Webhook test successful',
        'method' => $request->method(),
        'payload' => $request->all(),
        'headers' => $request->headers->all()
    ]);
})->name('test.webhook');

// Include separate route files for better organization
require __DIR__.'/admin.php';
require __DIR__.'/test.php';
require __DIR__.'/auth.php';
require __DIR__.'/event.php';

// Unified realtime metrics endpoint (web-authenticated)
Route::middleware(['web'])->get('/realtime/metrics', [\App\Http\Controllers\Realtime\RealtimeMetricsController::class, 'metrics'])
    ->name('realtime.metrics');

// Ticket Purchase Routes
Route::middleware('auth')->group(function () {
    Route::get('/events/{event}/buy-tickets', [App\Http\Controllers\Events\TicketPurchaseController::class, 'show'])->name('tickets.purchase.show');
    Route::post('/events/{event}/buy-tickets', [App\Http\Controllers\Events\TicketPurchaseController::class, 'purchase'])
        ->middleware('track.ticket.purchase')
        ->name('tickets.purchase');
    Route::get('/tickets/purchase/success/{purchaseId}', [App\Http\Controllers\Events\TicketPurchaseController::class, 'success'])
        ->middleware('track.ticket.purchase')
        ->name('tickets.purchase.success');
    Route::get('/tickets/purchase/failure/{purchaseId}', [App\Http\Controllers\Events\TicketPurchaseController::class, 'failure'])->name('tickets.purchase.failure');
    Route::get('/tickets/my-purchases', [App\Http\Controllers\Events\TicketPurchaseController::class, 'myPurchases'])->name('tickets.my-purchases');
    Route::get('/tickets/details/{purchaseId}', [App\Http\Controllers\Events\TicketPurchaseController::class, 'details'])->name('tickets.details');
    Route::get('/tickets/details/ticket/{ticketId}', [App\Http\Controllers\Events\TicketPurchaseController::class, 'ticketDetails'])->name('tickets.individual.details');
    Route::get('/ticket/{ticketId}', [App\Http\Controllers\Events\TicketPurchaseController::class, 'showIndividualTicket'])->name('tickets.individual');
    Route::get('/ticket/{ticketId}/qr-code', [App\Http\Controllers\Events\TicketPurchaseController::class, 'generateQRCode'])->name('tickets.qr-code');
});

// Realtime tester endpoints (admin only)
Route::middleware('auth:admin')->group(function(){
    Route::post('/test/realtime/admin', function(\Illuminate\Http\Request $request){
        broadcast(new \App\Events\AdminDashboardUpdated('all', ['source' => 'tester'], 'sales_update'));
        return response()->json(['ok' => true]);
    })->name('test.realtime.admin');

    Route::post('/test/realtime/sale', function(\Illuminate\Http\Request $request){
        $eventId = (int)($request->input('event_id') ?? 0);
        $organizerId = 0;
        broadcast(new \App\Events\SalesReportUpdated($eventId, $organizerId, ['source' => 'tester'], ['note' => 'test broadcast']));
        return response()->json(['ok' => true, 'event_id' => $eventId]);
    })->name('test.realtime.sale');
});


// Public layout rendering route for event show pages
Route::post('/events/layouts/{layout}', [App\Http\Controllers\Events\EventController::class, 'renderLayout'])->name('events.layouts.render');

// Invoice route for tickets
Route::middleware('auth')->get('/tickets/invoice/{purchaseId}/{ticketId}', [App\Http\Controllers\Events\TicketPurchaseController::class, 'viewInvoice'])->name('tickets.invoice');

// Qricket Feedback System
Route::get('/feedback/qricket', [App\Http\Controllers\QricketFeedbackController::class, 'show'])->name('qricket.feedback.show');
Route::post('/feedback/qricket', [App\Http\Controllers\QricketFeedbackController::class, 'store'])->name('qricket.feedback.store');
Route::post('/feedback/qricket/dismiss', [App\Http\Controllers\QricketFeedbackController::class, 'dismissModal'])->name('qricket.feedback.dismiss');
Route::get('/feedback/qricket/stats', [App\Http\Controllers\QricketFeedbackController::class, 'getStats'])->name('qricket.feedback.stats');
Route::get('/feedback/qricket/should-show', [App\Http\Controllers\QricketFeedbackController::class, 'shouldShowModal'])->name('qricket.feedback.should-show');

// Event Feedback System (restored)
Route::middleware('auth')->group(function () {
    Route::get('/events/{event}/feedback', [App\Http\Controllers\Events\EventFeedbackController::class, 'show'])->name('events.feedback.show');
    Route::post('/events/{event}/feedback', [App\Http\Controllers\Events\EventFeedbackController::class, 'submit'])->name('events.feedback.submit');
});

// Public certificate view by ticket id (no auth) and authenticated variants
Route::get('/events/{event}/certificate/public', [App\Http\Controllers\Organizer\EventManagement\EventCertificateController::class, 'publicCertificatePage'])->name('events.certificate.public');
Route::middleware('auth')->group(function () {
    Route::get('/events/{event}/certificate', [App\Http\Controllers\Organizer\EventManagement\EventCertificateController::class, 'userCertificatePage'])->name('events.certificate.view');
    Route::get('/events/{event}/certificate/stream', [App\Http\Controllers\Organizer\EventManagement\EventCertificateController::class, 'userStreamCertificate'])->name('events.certificate.stream');
});

Route::get('/email/preview/invoice', function () {
    $subscription = \App\Models\Subscription::with('user')->first();
    if (!$subscription) {
        return 'No subscription found. Please create a subscription first.';
    }
    
    return view('emails.payment-confirmation', [
            'user' => $subscription->user,
        ]);
    })->name('email.preview.invoice');


// Include separate route files for better organization
require __DIR__.'/admin.php';
require __DIR__.'/test.php';
require __DIR__.'/auth.php';
require __DIR__.'/event.php';

// Additional Ticket Purchase Routes
Route::middleware('auth')->group(function () {
    Route::get('/events/{event}/additional-tickets', [App\Http\Controllers\Organizer\TicketLimitController::class, 'show'])->name('additional-tickets.show');
    Route::post('/events/{event}/additional-tickets', [App\Http\Controllers\Organizer\TicketLimitController::class, 'purchase'])->name('additional-tickets.purchase');
    Route::post('/additional-tickets/validate-discount', [App\Http\Controllers\Organizer\TicketLimitController::class, 'validateDiscount'])->name('additional-tickets.validate-discount');
    Route::get('/additional-tickets/purchase/success/{purchaseId}', [App\Http\Controllers\Organizer\TicketLimitController::class, 'success'])->name('additional-tickets.purchase.success');
    Route::get('/additional-tickets/purchase/failure/{purchaseId}', [App\Http\Controllers\Organizer\TicketLimitController::class, 'failure'])->name('additional-tickets.purchase.failure');
    Route::get('/additional-tickets/my-purchases', [App\Http\Controllers\Organizer\TicketLimitController::class, 'myPurchases'])->name('additional-tickets.my-purchases');
    Route::get('/additional-tickets/invoice/{purchaseId}', [App\Http\Controllers\Organizer\TicketLimitController::class, 'viewInvoice'])->name('additional-tickets.invoice');
    
    // Alias routes for organizer and admin
    Route::get('/additional-tickets/invoice/{purchaseId}', [App\Http\Controllers\Organizer\TicketLimitController::class, 'viewInvoice'])->name('organizer.ticket-limits.view-invoice');
});

// Public layout rendering route for event show pages
Route::post('/events/layouts/{layout}', [App\Http\Controllers\Events\EventController::class, 'renderLayout'])->name('events.layouts.render');

// Invoice route for tickets
Route::middleware('auth')->get('/tickets/invoice/{purchaseId}/{ticketId}', [App\Http\Controllers\Events\TicketPurchaseController::class, 'viewInvoice'])->name('tickets.invoice');

// Ticket Purchase Routes
Route::middleware('auth')->group(function () {
    Route::get('/events/{event}/buy-tickets', [App\Http\Controllers\Events\TicketPurchaseController::class, 'show'])->name('tickets.purchase.show');
    Route::post('/events/{event}/buy-tickets', [App\Http\Controllers\Events\TicketPurchaseController::class, 'purchase'])
        ->middleware('track.ticket.purchase')
        ->name('tickets.purchase');
    Route::get('/tickets/purchase/success/{purchaseId}', [App\Http\Controllers\Events\TicketPurchaseController::class, 'success'])
        ->middleware('track.ticket.purchase')
        ->name('tickets.purchase.success');
    Route::get('/tickets/purchase/failure/{purchaseId}', [App\Http\Controllers\Events\TicketPurchaseController::class, 'failure'])->name('tickets.purchase.failure');
    Route::get('/tickets/details/{purchaseId}', [App\Http\Controllers\Events\TicketPurchaseController::class, 'details'])->name('tickets.details');
    Route::get('/tickets/details/ticket/{ticketId}', [App\Http\Controllers\Events\TicketPurchaseController::class, 'ticketDetails'])->name('tickets.individual.details');
    Route::get('/ticket/{ticketId}', [App\Http\Controllers\Events\TicketPurchaseController::class, 'showIndividualTicket'])->name('tickets.individual');
    Route::get('/ticket/{ticketId}/qr-code', [App\Http\Controllers\Events\TicketPurchaseController::class, 'generateQRCode'])->name('tickets.qr-code');
});

// Redirect old my-purchases route to my-tickets
Route::middleware('auth')->get('/tickets/my-purchases', function() {
    return redirect()->route('my-tickets.index');
})->name('tickets.my-purchases');

// Full page ticket purchase route
Route::middleware('auth')->get('/events/{event}/purchase-ticket', [App\Http\Controllers\Events\TicketPurchaseController::class, 'show'])->name('tickets.purchase.page');

// Test routes (only for development)
Route::get('/test/disbursement', function() {
    try {
        return view('test.disbursement');
    } catch (\Exception $e) {
        return "Error loading test page: " . $e->getMessage();
    }
})->name('test.disbursement');

Route::post('/test/disbursement', [\App\Http\Controllers\TestDisbursementController::class, 'testDisbursement'])->name('test.disbursement.test');
Route::post('/test/api-endpoint', [\App\Http\Controllers\TestDisbursementController::class, 'testApiEndpoint'])->name('test.api-endpoint');

Route::post('/test-notification', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'user_id' => 'required|integer',
        'message' => 'required|string',
        'type' => 'required|string',
        'title' => 'nullable|string'
    ]);
    
    \App\Services\NotificationService::sendUserNotification(
        $request->user_id,
        $request->message,
        $request->type,
        $request->title
    );
    
    return response()->json([
        'success' => true,
        'message' => 'Notification sent successfully'
    ]);
});

// Simple test route to trigger notification
Route::get('/test-notification-simple', function () {
    $userId = auth()->id();
    if (!$userId) {
        return 'User not authenticated';
    }
    
    \App\Services\NotificationService::sendUserNotification(
        $userId,
        'Simple test notification!',
        'success',
        'Test Success'
    );
    
    return 'Test notification sent to user ID: ' . $userId;
})->middleware('auth');

// Debug notification redirect
Route::get('/debug-notification/{id}', function ($id) {
    $notification = \App\Models\Notification::find($id);
    if (!$notification) {
        return response()->json(['error' => 'Notification not found']);
    }
    
    return response()->json([
        'id' => $notification->id,
        'type' => $notification->type,
        'action' => $notification->action,
        'reference_id' => $notification->reference_id,
        'title' => $notification->title,
        'message' => $notification->message,
        'is_read' => $notification->is_read,
        'created_at' => $notification->created_at
    ]);
})->middleware('auth');

// Get unread notification count for authenticated users
Route::middleware('auth')->get('/notifications/unread-count', function () {
    try {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['count' => 0]);
        }
        
        $count = \App\Models\Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
        
        return response()->json(['count' => $count ?? 0]);
    } catch (\Exception $e) {
        \Log::error('Error fetching unread notification count: ' . $e->getMessage());
        return response()->json(['count' => 0]);
    }
});
Route::get('/test/supported-banks', [\App\Http\Controllers\TestDisbursementController::class, 'getSupportedBanks'])->name('test.supported-banks');

// Debug route to check event status
Route::get('/debug-event/{event}', function(App\Models\Event $event) {
    return response()->json([
        'title' => $event->title,
        'status' => $event->status,
        'start_date' => $event->start_date,
        'end_date' => $event->end_date,
        'has_ended' => $event->end_date && now()->gte($event->end_date),
        'is_draft' => $event->status === 'draft',
        'is_rejected' => $event->status === 'rejected',
        'should_be_blocked' => in_array($event->status, ['rejected', 'draft']),
        'can_manage' => !in_array($event->status, ['rejected', 'draft']),
    ]);
})->middleware('web');

// Simple test route
Route::get('/test/simple', function() {
    return "Test route works!";
});

// Test route to verify logout functionality (remove in production)
Route::get('test-logout', function () {
    if (!Auth::check()) {
        return response()->json(['status' => 'error', 'message' => 'No user logged in']);
    }
    
    $user = Auth::user();
    return response()->json([
        'status' => 'success',
        'message' => 'User is logged in',
        'user_id' => $user->id,
        'user_email' => $user->email,
        'session_id' => session()->getId(),
        'logout_route' => route('logout')
    ]);
})->middleware('auth')->name('test.logout');

// Debug route to test logout form (remove in production)
Route::get('debug-logout', function () {
    return view('debug.logout-test');
})->name('debug.logout');
