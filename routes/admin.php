<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\DashboardAnalyticsController;
use App\Http\Controllers\Admin\EventApprovalController;
use App\Http\Controllers\User\OrganizerVerificationController;
// use App\Http\Controllers\AdminManagementController;
use App\Http\Controllers\Support\SupportTicketController;
use Illuminate\Support\Facades\Route;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Broadcasting\BroadcastController;

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware('prevent.user.admin')->group(function () {
    // Public admin routes (no middleware)
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::get('/pin', [AdminAuthController::class, 'showPin'])->name('pin');
    Route::post('/pin', [AdminAuthController::class, 'verifyPin']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    
    // Protected admin routes
    Route::middleware(['admin', 'admin.session'])->group(function () {
        // Admin broadcasting auth is handled via BroadcastServiceProvider with auth:admin
        Route::get('/dashboard', [AdminAuthController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard/counts', function() {
            $result = \App\Services\UserSessionService::getCountsIfChanged();
            Log::info('Admin dashboard counts fetched', $result['counts']);
            return response()->json($result['counts']);
        })->name('dashboard.counts');
        
        // User activity timeseries for charts
        Route::get('/dashboard/user-activity', [DashboardAnalyticsController::class, 'userActivityTimeseries'])->name('dashboard.user_activity');
        // Metrics panels
        Route::get('/dashboard/metrics', [DashboardAnalyticsController::class, 'metrics'])->name('dashboard.metrics');
        
        
        Route::post('/dashboard/force-update', function() {
            $counts = \App\Services\UserSessionService::forceUpdate();
            return response()->json([
                'success' => true,
                'message' => 'Counts force updated',
                'counts' => $counts
            ]);
        })->name('dashboard.force-update');
        
        Route::get('/dashboard/session-details', function() {
            $details = \App\Services\UserSessionService::getSessionDetails();
            return response()->json($details);
        })->name('dashboard.session-details');

        // Payouts
        Route::get('/payouts', [\App\Http\Controllers\Admin\PayoutsController::class, 'index'])->name('payouts.index');
        Route::post('/payouts/{payout}/approve', [\App\Http\Controllers\Admin\PayoutsController::class, 'approve'])->name('payouts.approve');
        Route::post('/payouts/{payout}/reject', [\App\Http\Controllers\Admin\PayoutsController::class, 'reject'])->name('payouts.reject');
        Route::post('/payouts/{payout}/release', [\App\Http\Controllers\Admin\PayoutsController::class, 'release'])->name('payouts.release');

        // Test notification endpoints
        Route::post('/notify/test-broadcast', function() {
            NotificationService::sendGeneralNotification('hi', 'info', 'Broadcast');
            return response()->json(['ok' => true]);
        })->name('notify.test-broadcast');

        Route::post('/notify/test-user', function(\Illuminate\Http\Request $request) {
            $validated = $request->validate([
                'user_id' => 'required|integer|min:1',
                'message' => 'nullable|string|max:255',
                'type' => 'nullable|string|in:info,success,warning,error',
                'title' => 'nullable|string|max:100',
            ]);
            $message = $validated['message'] ?? 'Admin test to user';
            $type = $validated['type'] ?? 'success';
            $title = $validated['title'] ?? 'Direct Message';
            NotificationService::sendUserNotification((int) $validated['user_id'], $message, $type, $title);
            return response()->json(['ok' => true]);
        })->name('notify.test-user');
        Route::get('/users', [AdminAuthController::class, 'users'])->middleware('admin.permission:manage_users')->name('users');
        Route::get('/users/export', [AdminAuthController::class, 'exportUsers'])->middleware('admin.permission:manage_users')->name('users.export');
        Route::get('/users/{user}', [AdminAuthController::class, 'showUser'])->name('users.show');
        Route::post('/users/{user}/delete', [AdminAuthController::class, 'deleteUser'])->middleware('admin.permission:manage_users')->name('users.delete');
        Route::post('/users/{user}/ban', [AdminAuthController::class, 'banUser'])->middleware('admin.permission:manage_users')->name('users.ban');
        Route::post('/users/{user}/unban', [AdminAuthController::class, 'unbanUser'])->middleware('admin.permission:manage_users')->name('users.unban');
        Route::post('/users/{user}/restore', [AdminAuthController::class, 'restoreUser'])->middleware('admin.permission:manage_users')->name('users.restore');
        Route::post('/users/{user}/unverify-organizer', [AdminAuthController::class, 'unverifyOrganizer'])->middleware('admin.permission:manage_users')->name('users.unverify-organizer');
        // Lazy-load organizer verification panel on user details
        Route::get('/users/{user}/verification-panel', [AdminAuthController::class, 'userVerificationPanel'])->name('users.verification.panel');
        Route::get('/logs', [AdminAuthController::class, 'logs'])->name('logs');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        
        // Admin verification management routes
        Route::get('/verifications', [OrganizerVerificationController::class, 'index'])->name('verifications');
        Route::get('/organizer-verifications', [OrganizerVerificationController::class, 'index'])->name('organizer-verifications.index');
        Route::get('/organizer-verifications/{verification}', [OrganizerVerificationController::class, 'show'])->name('organizer-verifications.show');
        Route::post('/organizer-verifications/{verification}/approve', [OrganizerVerificationController::class, 'approve'])->name('organizer-verifications.approve');
        Route::post('/organizer-verifications/{verification}/reject', [OrganizerVerificationController::class, 'reject'])->name('organizer-verifications.reject');
        Route::get('/verifications/{verification}', [OrganizerVerificationController::class, 'showAdmin'])->name('verification.show');
        Route::post('/verifications/{verification}/approve', [OrganizerVerificationController::class, 'approve'])->name('verification.approve');
        Route::post('/verifications/{verification}/reject', [OrganizerVerificationController::class, 'reject'])->name('verification.reject');
        
        // Admin support ticket management routes
        Route::get('/support', [SupportTicketController::class, 'index'])->middleware('admin.permission:manage_support')->name('support');
        Route::get('/support/{ticket}', [SupportTicketController::class, 'show'])->name('support.show');
        Route::post('/support/{ticket}/reply', [SupportTicketController::class, 'reply'])->name('support.reply');
        Route::post('/support/{ticket}/status', [SupportTicketController::class, 'updateStatus'])->name('support.status');

        // Admin activity log routes
        Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
        Route::get('/activity-log/{log}', [ActivityLogController::class, 'show'])->name('activity-log.show');

        // Subscription Management Routes
        Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\SubscriptionController::class, 'index'])->name('index');
            Route::post('/pricing', [\App\Http\Controllers\Admin\SubscriptionController::class, 'storePricing'])->name('pricing.store');
            Route::put('/pricing/{pricing}', [\App\Http\Controllers\Admin\SubscriptionController::class, 'updatePricing'])->name('pricing.update');
            Route::delete('/pricing/{pricing}', [\App\Http\Controllers\Admin\SubscriptionController::class, 'destroyPricing'])->name('pricing.destroy');
            Route::post('/discounts', [\App\Http\Controllers\Admin\SubscriptionController::class, 'storeDiscount'])->name('discounts.store');
            Route::put('/discounts/{discount}', [\App\Http\Controllers\Admin\SubscriptionController::class, 'updateDiscount'])->name('discounts.update');
            Route::delete('/discounts/{discount}', [\App\Http\Controllers\Admin\SubscriptionController::class, 'destroyDiscount'])->name('discounts.destroy');
            Route::get('/purchases', [\App\Http\Controllers\Admin\SubscriptionController::class, 'purchases'])->name('purchases');
            
            // Subscription Plans Routes
            Route::post('/plans', [\App\Http\Controllers\Admin\SubscriptionController::class, 'storePlan'])->name('plans.store');
            Route::put('/plans/{plan}', [\App\Http\Controllers\Admin\SubscriptionController::class, 'updatePlan'])->name('plans.update');
            Route::delete('/plans/{plan}', [\App\Http\Controllers\Admin\SubscriptionController::class, 'destroyPlan'])->name('plans.destroy');
        });

        // Event Approval Management
        Route::prefix('events')->name('events.')->middleware('admin.permission:manage_events')->group(function () {
            Route::get('/approval', [EventApprovalController::class, 'index'])->name('approval.index');
            Route::get('/approval/{event}', [EventApprovalController::class, 'show'])->name('approval.show');
            Route::post('/approval/{event}/approve', [EventApprovalController::class, 'approve'])->name('approval.approve');
            Route::post('/approval/{event}/reject', [EventApprovalController::class, 'reject'])->name('approval.reject');
            Route::post('/approval/{event}/reset', [EventApprovalController::class, 'reset'])->name('approval.reset');
            Route::get('/pending', [EventApprovalController::class, 'pending'])->name('pending');
        });

        // Admin Event Management Routes - Clean URLs for admin access
        Route::prefix('event')->name('event.')->group(function () {
            // Main event management dashboard
            Route::get('/{event}/manage', [\App\Http\Controllers\Organizer\EventManagement\EventDashboardController::class, 'index'])
                ->name('manage');
            Route::get('/{event}/sales', [\App\Http\Controllers\Organizer\EventManagement\EventDashboardController::class, 'salesReport'])
                ->name('sales');
            Route::get('/{event}/sales/export', [\App\Http\Controllers\Organizer\EventManagement\EventDashboardController::class, 'exportSales'])
                ->name('sales.export');
            Route::get('/{event}/activity-load-more', [\App\Http\Controllers\Organizer\EventManagement\EventDashboardController::class, 'loadMoreActivity'])
                ->name('activity-load-more');
            
            // Attendance management
            Route::get('/{event}/attendance', [\App\Http\Controllers\Organizer\EventManagement\EventAttendanceController::class, 'attendance'])
                ->name('attendance');
            Route::get('/{event}/attendance/export', [\App\Http\Controllers\Organizer\EventManagement\EventAttendanceController::class, 'exportAttendance'])
                ->name('attendance.export');
            Route::get('/{event}/scanner', [\App\Http\Controllers\Organizer\EventManagement\EventAttendanceController::class, 'scanner'])
                ->name('scanner');
            Route::post('/{event}/attendance/qr-scan', [\App\Http\Controllers\Organizer\EventManagement\EventAttendanceController::class, 'processQRScan'])
                ->name('attendance.qr-scan');
            Route::post('/{event}/attendance/manual-checkin', [\App\Http\Controllers\Organizer\EventManagement\EventAttendanceController::class, 'manualCheckIn'])
                ->name('attendance.manual-checkin');
            Route::get('/{event}/attendance/search', [\App\Http\Controllers\Organizer\EventManagement\EventAttendanceController::class, 'searchAttendees'])
                ->name('attendance.search');
            
            // Ticket management
            Route::get('/{event}/tickets', [\App\Http\Controllers\Organizer\EventManagement\TicketsController::class, 'tickets'])
                ->name('tickets');
                
            // Admin ticket limits (additional tickets)
            Route::get('/{event}/ticket-limits/view-invoice/{purchaseId}', [\App\Http\Controllers\Organizer\TicketLimitController::class, 'viewInvoice'])
                ->name('ticket-limits.view-invoice');
            
            // Additional tickets purchase
            Route::get('/{event}/ticket-limits', [\App\Http\Controllers\Organizer\TicketLimitController::class, 'show'])
                ->name('ticket-limits');
            Route::post('/{event}/ticket-limits/purchase', [\App\Http\Controllers\Organizer\TicketLimitController::class, 'purchase'])
                ->name('ticket-limits.purchase');
            
            // Staff management
            Route::get('/{event}/staff', [\App\Http\Controllers\Organizer\EventManagement\StaffController::class, 'manage'])
                ->name('staff');
            
            // Payout management
            Route::get('/{event}/payout', [\App\Http\Controllers\Organizer\EventManagement\EventPayoutController::class, 'payout'])
                ->name('payout');
            
            // Event editing
            Route::get('/{event}/edit/basic', [\App\Http\Controllers\Organizer\EventManagement\EventEditController::class, 'editBasic'])
                ->name('edit.basic');
            Route::get('/{event}/edit/layout', [\App\Http\Controllers\Organizer\EventManagement\EventEditController::class, 'editLayout'])
                ->name('edit.layout');
            Route::get('/{event}/edit/certificate', [\App\Http\Controllers\Organizer\EventManagement\EventEditController::class, 'editCertificate'])
                ->name('edit.certificate');
            Route::put('/{event}/edit/certificate', [\App\Http\Controllers\Organizer\EventManagement\EventEditController::class, 'updateCertificate'])
                ->name('edit.certificate.update');
            Route::post('/{event}/edit/certificate/autosave', [\App\Http\Controllers\Organizer\EventManagement\EventEditController::class, 'autosaveCertificate'])
                ->name('edit.certificate.autosave');
            
            // Event info routes (for viewing after event has ended) - use the same methods as edit routes
            Route::get('/{event}/info/basic', [\App\Http\Controllers\Organizer\EventManagement\EventEditController::class, 'editBasic'])
                ->name('info.basic');
            Route::get('/{event}/info/layout', [\App\Http\Controllers\Organizer\EventManagement\EventEditController::class, 'editLayout'])
                ->name('info.layout');
            Route::get('/{event}/info/certificate', [\App\Http\Controllers\Organizer\EventManagement\EventEditController::class, 'editCertificate'])
                ->name('info.certificate');
            
            // QR Code generation
            Route::get('/{event}/qr-code', [\App\Http\Controllers\Organizer\EventManagement\EventEditController::class, 'qrCode'])
                ->name('qr-code');
            
            // Event news management
            Route::get('/{event}/news', [\App\Http\Controllers\Organizer\EventNewsController::class, 'index'])
                ->name('news');
            Route::get('/{event}/news/create', [\App\Http\Controllers\Organizer\EventNewsController::class, 'create'])
                ->name('news.create');
            Route::post('/{event}/news', [\App\Http\Controllers\Organizer\EventNewsController::class, 'store'])
                ->name('news.store');
            Route::get('/{event}/news/{news}/edit', [\App\Http\Controllers\Organizer\EventNewsController::class, 'edit'])
                ->name('news.edit');
            Route::put('/{event}/news/{news}', [\App\Http\Controllers\Organizer\EventNewsController::class, 'update'])
                ->name('news.update');
            Route::delete('/{event}/news/{news}', [\App\Http\Controllers\Organizer\EventNewsController::class, 'destroy'])
                ->name('news.destroy');
            Route::post('/{event}/news/{news}/publish', [\App\Http\Controllers\Organizer\EventNewsController::class, 'publish'])
                ->name('news.publish');
            
            // Event feedback management
            Route::get('/{event}/feedback', [\App\Http\Controllers\Events\EventFeedbackController::class, 'manage'])
                ->name('feedback');
            Route::get('/{event}/news/{news}/image/{filename}', [\App\Http\Controllers\Organizer\EventNewsController::class, 'serveImage'])
                ->name('news.image');
            
            // Activity log
            Route::get('/{event}/activity-log', [\App\Http\Controllers\Events\EventController::class, 'activityLog'])
                ->name('activity-log');
            
            // Update event status
            Route::put('/{event}/status', [\App\Http\Controllers\Events\EventController::class, 'updateStatus'])
                ->name('update-status');
        });

        // Admin Notifications page
        Route::get('/notifications', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'index'])->name('notifications');
        Route::get('/notifications/data', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'data'])->name('notifications.data');
        Route::get('/notifications/{id}/go', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'go'])->name('notifications.go');
        Route::post('/notifications/{id}/mark-read', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        
        // Admin notifications unread count
        Route::get('/notifications/unread-count', function() {
            try {
                $adminId = Auth::guard('admin')->id();
                $count = \App\Models\Notification::where('admin_id', $adminId)
                    ->where('is_read', false)
                    ->count();
                return response()->json(['count' => $count ?? 0]);
            } catch (\Exception $e) {
                \Log::error('Error fetching admin unread notification count: ' . $e->getMessage());
                return response()->json(['count' => 0]);
            }
        })->name('notifications.unread-count');

        // Sales Reports
        Route::get('/reports/sales', [\App\Http\Controllers\Admin\ReportsController::class, 'sales'])
            ->name('reports.sales');
        Route::post('/reports/sales/generate', [\App\Http\Controllers\Admin\ReportsController::class, 'generateSalesReport'])
            ->name('reports.sales.generate');
        Route::post('/reports/sales/trigger-update', [\App\Http\Controllers\Admin\ReportsController::class, 'triggerSalesUpdate'])
            ->name('reports.sales.trigger-update');

        // Refund management
        Route::get('/refunds', [\App\Http\Controllers\Payment\RefundController::class, 'index'])->name('refunds.index');
        Route::post('/refunds/{refundRequest}/approve', [\App\Http\Controllers\Payment\RefundController::class, 'approve'])->name('refunds.approve');
        Route::post('/refunds/{refundRequest}/reject', [\App\Http\Controllers\Payment\RefundController::class, 'reject'])->name('refunds.reject');
        Route::post('/refunds/{refundRequest}/process', [\App\Http\Controllers\Payment\RefundController::class, 'processRefund'])->name('refunds.process');

        // Additional tickets invoice route for admin
        Route::get('/ticket-limits/invoice/{purchaseId}', [\App\Http\Controllers\Organizer\TicketLimitController::class, 'viewInvoice'])->name('ticket-limits.view-invoice');

        // Qricket Feedback (admin view)
        Route::get('/feedback', [\App\Http\Controllers\Admin\FeedbackController::class, 'index'])->name('feedback.index');

        // Manage Admins disabled by request
    });
});
