<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminActivityLog;
use App\Models\User;
use App\Services\UserSessionService;
use App\Events\AdminLoggedOut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdminAuthController extends Controller
{
    /**
     * Show admin login form.
     */
    public function showLogin(Request $request)
    {
        // If already logged in as admin, redirect to dashboard
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        // Clear ALL session flash data to prevent stale messages
        session()->forget(['session_conflict', 'error', 'warning', 'success']);
        
        // Also clear any admin-related session data
        session()->forget(['admin_login_id', 'user_role', 'admin_role', 'admin_id', 'admin_as_user']);

        // Check if there's an existing lockout for the username in the request
        $lockoutData = null;
        if ($request->has('username')) {
            $admin = Admin::where('username', $request->username)->first();
            if ($admin && $admin->locked_until && $admin->locked_until > now()) {
                $remainingMinutes = now()->diffInMinutes($admin->locked_until);
                $remainingSeconds = now()->diffInSeconds($admin->locked_until);
                
                $lockoutData = [
                    'message' => "Account is locked due to multiple failed login attempts. Please try again in {$remainingMinutes} minutes.",
                    'seconds' => $remainingSeconds
                ];
            }
        }

        return view('admin.login', compact('lockoutData'));
    }

    /**
     * Handle admin login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('username', $request->username)->first();

        // Check if admin is locked due to failed attempts
        if ($admin && $admin->locked_until && $admin->locked_until > now()) {
            $remainingMinutes = now()->diffInMinutes($admin->locked_until);
            $remainingSeconds = now()->diffInSeconds($admin->locked_until);
            
            return back()->with('lockout_warning', "Account is locked due to multiple failed login attempts. Please try again in {$remainingMinutes} minutes.")
                        ->with('lockout_seconds', $remainingSeconds)
                        ->withInput($request->only('username'));
        }

        // Check if admin is already logged in elsewhere - but don't block login
        // The actual conflict handling happens during PIN verification
        // This allows the user to proceed to PIN even if another session exists
        if ($admin && $admin->current_session_id) {
            $sessionDriver = config('session.driver');
            $existingSession = null;
            
            if ($sessionDriver === 'database') {
                try {
                    // Check if there's an active session with that ID
                    $existingSession = \Illuminate\Support\Facades\DB::table('sessions')
                        ->where('id', $admin->current_session_id)
                        ->where('role', 'admin')
                        ->where('last_activity', '>', now()->subMinutes(5))
                        ->first();
                } catch (\Exception $e) {
                    \Log::error('Admin login: Failed to check database session: ' . $e->getMessage());
                }
            } else {
                // For file sessions, check if the session file exists and is recent
                $sessionFilesPath = config('session.files', storage_path('framework/sessions'));
                $sessionPath = $sessionFilesPath . '/' . $admin->current_session_id;
                if (file_exists($sessionPath)) {
                    $fileModified = filemtime($sessionPath);
                    if ($fileModified > now()->subMinutes(5)->timestamp) {
                        $existingSession = (object)['id' => $admin->current_session_id];
                    }
                }
            }
            
            // Store info about existing session in session data for later use during PIN verification
            // Don't block - allow them to proceed to PIN verification
            if ($existingSession) {
                // Store flag to show warning on PIN page, but don't block
                session(['has_existing_session' => true, 'existing_session_id' => $admin->current_session_id]);
            } else {
                // Clean up stale session ID silently
                $admin->update(['current_session_id' => null]);
                session()->forget(['has_existing_session', 'existing_session_id']);
            }
        } else {
            session()->forget(['has_existing_session', 'existing_session_id']);
        }

        if ($admin && Hash::check($request->password, $admin->password)) {
            // Reset failed login attempts on successful login
            $admin->update([
                'failed_login_attempts' => 0,
                'locked_until' => null,
            ]);

            // Store admin ID in session for PIN verification
            session(['admin_login_id' => $admin->id]);
            
            // Log login attempt
            $admin->logActivity('login_attempt', [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('admin.pin');
        }

        // Handle failed login attempt
        if ($admin) {
            $admin->increment('failed_login_attempts');
            
            // Lock account after 3 failed attempts
            if ($admin->failed_login_attempts >= 3) {
                $admin->update([
                    'locked_until' => now()->addMinutes(15),
                ]);
                
                // Log account lockout
                $admin->logActivity('account_locked', [
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'failed_attempts' => $admin->failed_login_attempts,
                    'locked_until' => $admin->locked_until,
                ]);
            }
            
            // Log failed login attempt
            $admin->logActivity('login_failed', [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'failed_attempts' => $admin->failed_login_attempts,
            ]);
        }

        return back()->withErrors([
            'username' => 'The provided credentials are incorrect.',
        ])->withInput($request->only('username'));
    }

    /**
     * Show PIN verification form.
     */
    public function showPin()
    {
        if (!session('admin_login_id')) {
            return redirect()->route('admin.login');
        }

        $admin = Admin::find(session('admin_login_id'));
        if (!$admin) {
            session()->forget('admin_login_id');
            return redirect()->route('admin.login');
        }

        return view('admin.pin', compact('admin'));
    }

    /**
     * Verify PIN and complete login.
     */
    public function verifyPin(Request $request)
    {
        $request->validate([
            'pin' => 'required|string|size:6',
        ]);

        $adminId = session('admin_login_id');
        $admin = Admin::find($adminId);

        if (!$admin) {
            return redirect()->route('admin.login')->with('warning', 'Your session expired. Please sign in again.');
        }

        if ($admin->pin === $request->pin) {
            // Check if admin is already logged in from another device BEFORE logging in
            // This is where we should detect the conflict and notify via Pusher
            if ($admin->current_session_id && $admin->current_session_id !== session()->getId()) {
                $sessionDriver = config('session.driver');
                $existingSession = null;
                
                if ($sessionDriver === 'database') {
                    try {
                        $existingSession = \Illuminate\Support\Facades\DB::table('sessions')
                            ->where('id', $admin->current_session_id)
                            ->where('role', 'admin')
                            ->where('last_activity', '>', now()->subMinutes(5))
                            ->first();
                    } catch (\Exception $e) {
                        \Log::error('Admin login: Failed to check database session: ' . $e->getMessage());
                    }
                } else {
                    // For file sessions, check if the session file exists and is recent
                    $sessionFilesPath = config('session.files', storage_path('framework/sessions'));
                    $sessionPath = $sessionFilesPath . '/' . $admin->current_session_id;
                    if (file_exists($sessionPath)) {
                        $fileModified = filemtime($sessionPath);
                        if ($fileModified > now()->subMinutes(5)->timestamp) {
                            $existingSession = (object)['id' => $admin->current_session_id];
                        }
                    }
                }
                
                // If there's an active session from another device, invalidate it and notify via Pusher
                if ($existingSession) {
                    \Log::info('Admin login: Active session detected, logging out other device', [
                        'admin_id' => $admin->id,
                        'existing_session_id' => $admin->current_session_id,
                        'new_session_id' => session()->getId(),
                    ]);
                    
                    // Invalidate the old session immediately (before new login)
                    // This ensures the old session is logged out even if Pusher doesn't reach it
                    $oldSessionId = $admin->current_session_id;
                    
                    // Fire logout event to notify the other device via Pusher
                    // This will show the "logged in from another device" message on the other device
                    // The message indicates that Device A has been logged out because Device B logged in
                    // IMPORTANT: conflicting_session_id must be set so the frontend can identify which session this is for
                    event(new AdminLoggedOut($admin->id, 'session_conflict', [
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'conflicting_session_id' => $oldSessionId, // This is the OLD session being logged out
                        'new_session_id' => session()->getId(), // This is the NEW session logging in
                        'logged_in_from' => $request->ip(),
                    ]));
                    
                    // Clear the old session ID so the old session will be invalidated
                    // The old session will be logged out by the middleware on its next request
                    // or immediately if we can invalidate it here
                }
            }
            
            // Actually log in using the admin guard (this happens AFTER notifying the old session)
            Auth::guard('admin')->login($admin);
            
            // Create/find shadow user for web guard access
            $shadowEmail = 'admin+' . $admin->id . '@admin.local';
            $shadowUser = User::updateOrCreate(
                ['email' => $shadowEmail],
                [
                    'first_name' => 'Admin',
                    'last_name' => $admin->username,
                    'password' => bcrypt(Str::random(24)),
                    'organizer' => false,
                    'email_verified_at' => now(),
                ]
            );
            
            // Ensure email is verified
            if (!$shadowUser->hasVerifiedEmail()) {
                $shadowUser->markEmailAsVerified();
            }
            
            // Login shadow user to web guard
            Auth::guard('web')->login($shadowUser);
            
            // Update current session ID after successful login
            // This replaces the old session ID, effectively invalidating the old session
            // The old device will be logged out on its next request when middleware detects the mismatch
            $newSessionId = session()->getId();
            $admin->update(['current_session_id' => $newSessionId]);
            
            // Log successful dual authentication
            \Log::info('Admin dual authentication successful', [
                'admin_id' => $admin->id,
                'admin_username' => $admin->username,
                'shadow_user_id' => $shadowUser->id,
                'shadow_email' => $shadowEmail,
                'admin_authenticated' => Auth::guard('admin')->check(),
                'user_authenticated' => Auth::guard('web')->check(),
            ]);

            // Log successful login (for audit)
            $admin->logActivity('login', [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            // Set session data
            session(['user_role' => 'admin']);
            session(['admin_role' => $admin->role]);
            session(['admin_id' => $admin->id]);
            session(['admin_as_user' => true]);
            
            // Update current session in sessions table (only if using database session driver)
            $sessionId = session()->getId();
            if ($sessionId) {
                $sessionDriver = config('session.driver');
                if ($sessionDriver === 'database') {
                    try {
                        DB::table('sessions')
                            ->where('id', $sessionId)
                            ->update([
                                'role' => 'admin',
                                'user_id' => $shadowUser->id,
                                'last_activity' => now()->timestamp,
                            ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to update session role: ' . $e->getMessage());
                    }
                }
            }
            
            // Update admin's current session ID (already done above, but ensure it's set)
            if (!$admin->current_session_id || $admin->current_session_id !== $sessionId) {
                $admin->update(['current_session_id' => $sessionId]);
            }
            
            // Clear temporary session data
            session()->forget('admin_login_id');
            
            return redirect()->route('admin.dashboard')
                ->with('success', 'Welcome back to Qrickett Admin.');
        }

        // Log failed PIN attempt
        $admin->logActivity('pin_failed', [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->withErrors([
            'pin' => 'Invalid PIN. Please try again.',
        ]);
    }

    /**
     * Handle admin logout.
     */
    public function logout(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        if ($admin) {
            // Clear session tracking BEFORE logout
            // This prevents the middleware from detecting a false conflict after logout
            $admin->update(['current_session_id' => null]);
            
            // Fire logout event ONLY to notify OTHER active sessions (not current one)
            // This is only needed if there are other sessions, but we fire it anyway
            // The event should not trigger conflict messages for manual logout
            // Other devices listening to Pusher will receive this, but it's a manual logout, not a conflict
            event(new AdminLoggedOut($admin->id, 'manual_logout', [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'logout_type' => 'manual'
            ]));
            
            // Log logout
            $admin->logActivity('logout', [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }
        
        // Update session role BEFORE logout to ensure proper cleanup
        UserSessionService::handleAdminLogout();
        
        // Clear any session conflict messages to prevent stale messages
        $request->session()->forget('session_conflict');
        
        // Logout from both guards
        Auth::guard('admin')->logout();
        Auth::guard('web')->logout();
        
        // Clear all session data
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('admin.login')
            ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Show admin dashboard.
     */
    public function dashboard()
    {
        $admin = Auth::guard('admin')->user();
        
        // Get real-time user counts
        $userCounts = UserSessionService::getCachedCounts();
        
        // Core Statistics
        $stats = [
            'total_users' => User::count(),
            'verified_organizers' => User::whereNotNull('verified_organizer')->count(),
            'pending_verifications' => \App\Models\OrganizerVerification::where('status', 'pending')->count(),
            'recent_activity' => AdminActivityLog::with('admin')->latest()->take(10)->get(),
        ];

        // Event Statistics
        $eventStats = [
            'total_events' => \App\Models\Event::count(),
            'active_events' => \App\Models\Event::where('status', 'active')->count(),
            'pending_approval' => \App\Models\Event::where('status', 'pending_approval')->count(),
            'draft_events' => \App\Models\Event::where('status', 'draft')->count(),
            'scheduled_events' => \App\Models\Event::where('status', 'scheduled')->count(),
            'cancelled_events' => \App\Models\Event::where('status', 'cancelled')->count(),
            'events_this_month' => \App\Models\Event::whereMonth('created_at', now()->month)->count(),
            'events_today' => \App\Models\Event::whereDate('created_at', today())->count(),
        ];

        // User Growth Statistics
        $userGrowthStats = [
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'new_users_this_week' => User::where('created_at', '>=', now()->subWeek())->count(),
            'new_users_this_month' => User::whereMonth('created_at', now()->month)->count(),
            'active_users_today' => User::whereHas('ticketPurchases', function($q) {
                $q->whereDate('created_at', today());
            })->count(),
            'verified_organizers_today' => User::whereNotNull('verified_organizer')->whereDate('verified_organizer', today())->count(),
        ];

        // Calculate admin revenue percentage
        $adminRevenuePercentage = config('app.admin_revenue_percentage', 5.0);
        
        // Sales & Revenue Statistics (Admin's actual revenue)
        $totalTicketRevenue = (float) \App\Models\TicketPurchase::where('status','paid')->sum('total_amount');
        $totalSubscriptionRevenue = (float) \App\Models\Subscription::where('status','active')->sum('amount');
        $adminTicketRevenue = $totalTicketRevenue * ($adminRevenuePercentage / 100);
        // Additional ticket revenue (admin keeps net)
        $additionalTicketRevenueNet = (float) \App\Models\TicketLimitPurchase::where('status','paid')
            ->sum(DB::raw('COALESCE(net_amount, amount)'));
        $additionalTicketPurchases = (int) \App\Models\TicketLimitPurchase::where('status','paid')->count();
        
        $salesStats = [
            'total_ticket_revenue' => $totalTicketRevenue, // Total platform revenue
            'admin_ticket_revenue' => $adminTicketRevenue, // Admin's actual revenue from tickets
            'total_subscription_revenue' => $totalSubscriptionRevenue, // Full subscription revenue (admin keeps all)
            'admin_total_revenue' => $adminTicketRevenue + $totalSubscriptionRevenue + $additionalTicketRevenueNet, // Admin's total actual revenue
            'ticket_orders_paid' => (int) \App\Models\TicketPurchase::where('status','paid')->count(),
            'ticket_orders_pending' => (int) \App\Models\TicketPurchase::where('status','pending')->count(),
            'ticket_orders_failed' => (int) \App\Models\TicketPurchase::where('status','failed')->count(),
            'subscriptions_active' => (int) \App\Models\Subscription::where('status','active')->count(),
            'subscriptions_cancelled' => (int) \App\Models\Subscription::where('status','cancelled')->count(),
            'revenue_today' => (float) \App\Models\TicketPurchase::where('status','paid')->whereDate('paid_at', today())->sum('total_amount') * ($adminRevenuePercentage / 100),
            'revenue_this_week' => (float) \App\Models\TicketPurchase::where('status','paid')->where('paid_at', '>=', now()->subWeek())->sum('total_amount') * ($adminRevenuePercentage / 100),
            'revenue_this_month' => (float) \App\Models\TicketPurchase::where('status','paid')->whereMonth('paid_at', now()->month)->sum('total_amount') * ($adminRevenuePercentage / 100),
            'additional_ticket_revenue' => $additionalTicketRevenueNet,
            'additional_ticket_purchases' => $additionalTicketPurchases,
        ];

        // Refund Statistics
        $refundStats = [
            'total_refund_requests' => \App\Models\RefundRequest::count(),
            'pending_refunds' => \App\Models\RefundRequest::where('status', 'pending')->count(),
            'approved_refunds' => \App\Models\RefundRequest::where('status', 'approved')->count(),
            'processed_refunds' => \App\Models\RefundRequest::where('status', 'processed')->count(),
            'rejected_refunds' => \App\Models\RefundRequest::where('status', 'rejected')->count(),
            'total_refund_amount' => (float) \App\Models\RefundRequest::where('status', 'processed')->sum('amount'),
        ];

        // Support & Verification Statistics
        $supportStats = [
            'open_support_tickets' => \App\Models\SupportTicket::where('status', 'open')->count(),
            'pending_verifications' => \App\Models\OrganizerVerification::where('status', 'pending')->count(),
            'approved_verifications' => \App\Models\OrganizerVerification::where('status', 'approved')->count(),
            'rejected_verifications' => \App\Models\OrganizerVerification::where('status', 'rejected')->count(),
            'support_tickets_today' => \App\Models\SupportTicket::whereDate('created_at', today())->count(),
            'verification_requests_today' => \App\Models\OrganizerVerification::whereDate('created_at', today())->count(),
            'verification_requests_this_week' => \App\Models\OrganizerVerification::where('created_at', '>=', now()->subWeek())->count(),
            'verification_requests_this_month' => \App\Models\OrganizerVerification::whereMonth('created_at', now()->month)->count(),
        ];

        // Qricket Feedback Statistics (with error handling for missing table)
        $feedbackStats = [
            'total_feedback' => 0,
            'recent_feedback' => 0,
            'feedback_today' => 0,
            'average_navigation' => 0,
            'average_information' => 0,
            'average_design' => 0,
            'average_performance' => 0,
            'average_process' => 0,
            'recent_comments' => collect(),
        ];

        try {
            // Check if the qricket_feedback table exists
            if (\Schema::hasTable('qricket_feedback')) {
                $feedbackStats = [
                    'total_feedback' => \App\Models\QricketFeedback::website()->count(),
                    'recent_feedback' => \App\Models\QricketFeedback::website()->recent(30)->count(),
                    'feedback_today' => \App\Models\QricketFeedback::website()->whereDate('created_at', today())->count(),
                    'average_navigation' => round(\App\Models\QricketFeedback::website()->whereNotNull('navigation_ease')->avg('navigation_ease') ?? 0, 1),
                    'average_information' => round(\App\Models\QricketFeedback::website()->whereNotNull('information_found')->avg('information_found') ?? 0, 1),
                    'average_design' => round(\App\Models\QricketFeedback::website()->whereNotNull('design_rating')->avg('design_rating') ?? 0, 1),
                    'average_performance' => round(\App\Models\QricketFeedback::website()->whereNotNull('performance_rating')->avg('performance_rating') ?? 0, 1),
                    'average_process' => round(\App\Models\QricketFeedback::website()->whereNotNull('process_satisfaction')->avg('process_satisfaction') ?? 0, 1),
                    'recent_comments' => \App\Models\QricketFeedback::website()
                        ->whereNotNull('additional_comments')
                        ->where('additional_comments', '!=', '')
                        ->recent(30)
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get(['additional_comments', 'created_at', 'user_id']),
                ];
            } else {
                \Log::info('qricket_feedback table does not exist, using default values');
            }
        } catch (\Exception $e) {
            \Log::warning('Error fetching feedback statistics: ' . $e->getMessage());
            // Keep default values
        }

        // Event Approval Statistics
        // Column-safe computations (handle schemas without approved_at/rejected_at)
        $approvedTodayQuery = \App\Models\Event::where('status', 'active');
        if (Schema::hasColumn('events', 'approved_at')) {
            $approvedTodayQuery->whereDate('approved_at', today());
        } else {
            $approvedTodayQuery->whereDate('updated_at', today());
        }
        $eventsApprovedToday = $approvedTodayQuery->count();

        $rejectedTodayQuery = \App\Models\Event::where('status', 'rejected');
        if (Schema::hasColumn('events', 'rejected_at')) {
            $rejectedTodayQuery->whereDate('rejected_at', today());
        } else {
            $rejectedTodayQuery->whereDate('updated_at', today());
        }
        $eventsRejectedToday = $rejectedTodayQuery->count();

        $totalApprovedQuery = \App\Models\Event::where('status', 'active');
        if (Schema::hasColumn('events', 'approved_at')) {
            $totalApprovedQuery->whereNotNull('approved_at');
        }
        $totalEventsApproved = $totalApprovedQuery->count();

        $totalRejectedQuery = \App\Models\Event::where('status', 'rejected');
        if (Schema::hasColumn('events', 'rejected_at')) {
            $totalRejectedQuery->whereNotNull('rejected_at');
        }
        $totalEventsRejected = $totalRejectedQuery->count();

        $eventApprovalStats = [
            'pending_event_approvals' => \App\Models\Event::where('status', 'pending_approval')->count(),
            'events_submitted_today' => \DB::table('event_verification_requests')->where('status','pending')->whereDate('submitted_at', today())->count(),
            'events_submitted_this_week' => \DB::table('event_verification_requests')->where('status','pending')->where('submitted_at', '>=', now()->subWeek())->count(),
            'events_submitted_this_month' => \DB::table('event_verification_requests')->where('status','pending')->whereMonth('submitted_at', now()->month)->count(),
            'events_approved_today' => $eventsApprovedToday,
            'events_rejected_today' => $eventsRejectedToday,
            'total_events_approved' => $totalEventsApproved,
            'total_events_rejected' => $totalEventsRejected,
        ];

        // Admin Activity Statistics
        $adminStats = [
            'total_admins' => Admin::count(),
            'total_logins' => AdminActivityLog::where('action', 'login_success')->count(),
            'failed_logins' => AdminActivityLog::where('action', 'login_attempt')->where('created_at', '>=', now()->subDays(7))->count(),
            'active_today' => AdminActivityLog::where('action', 'login_success')->whereDate('created_at', today())->count(),
            'admin_actions_today' => AdminActivityLog::whereDate('created_at', today())->count(),
        ];

        $recentLogs = AdminActivityLog::with('admin')->latest()->take(5)->get();

        // Get pending organizer verifications for dashboard
        $pendingVerifications = \App\Models\OrganizerVerification::with('user')
            ->where('status', 'pending')
            ->latest()
            ->get();

        // Legacy variables for backward compatibility
        $ticketRevenue = $salesStats['total_ticket_revenue'];
        $ticketOrders = $salesStats['ticket_orders_paid'];
        $ticketPending = $salesStats['ticket_orders_pending'];
        $subRevenue = $salesStats['total_subscription_revenue'];

        // Trends (last 30 days) - Admin Revenue
        $start = now()->subDays(29)->startOfDay();
        $tp = \App\Models\TicketPurchase::selectRaw('DATE(paid_at) as d, SUM(total_amount) as revenue, COUNT(*) as orders')
            ->where('status','paid')
            ->whereNotNull('paid_at')
            ->where('paid_at','>=',$start)
            ->groupBy('d')->orderBy('d')->get()->keyBy('d');
        $ticketTrendLabels = [];$ticketTrendRevenue=[];$ticketTrendOrders=[];
        for ($i=29;$i>=0;$i--) { 
            $day = now()->subDays($i)->toDateString(); 
            $ticketTrendLabels[]=$day; 
            $ticketTrendRevenue[]=(float)($tp[$day]->revenue ?? 0) * ($adminRevenuePercentage / 100); // Admin's percentage
            $ticketTrendOrders[]=(int)($tp[$day]->orders ?? 0);
        }        

        $sp = \App\Models\Subscription::selectRaw('DATE(updated_at) as d, SUM(amount) as revenue')
            ->where('payment_status','paid')
            ->where('updated_at','>=',$start)
            ->groupBy('d')->orderBy('d')->get()->keyBy('d');
        $subTrendLabels = [];$subTrendRevenue=[];
        for ($i=29;$i>=0;$i--) { 
            $day = now()->subDays($i)->toDateString(); 
            $subTrendLabels[]=$day; 
            $subTrendRevenue[]=(float)($sp[$day]->revenue ?? 0); // Full subscription revenue
        }        

        // Top Earning Events (Admin Revenue)
        $topEarningEvents = \App\Models\Event::with('user')
            ->selectRaw('events.id, events.title, events.user_id, events.created_at, events.updated_at,
                COALESCE(SUM(ticket_purchases.total_amount), 0) as total_revenue,
                COALESCE(SUM(ticket_purchases.total_amount) * ' . ($adminRevenuePercentage / 100) . ', 0) as admin_revenue,
                COALESCE(COUNT(ticket_purchases.id), 0) as tickets_sold')
            ->leftJoin('ticket_purchases', 'events.id', '=', 'ticket_purchases.event_id')
            ->where('ticket_purchases.status', 'paid')
            ->groupBy('events.id', 'events.title', 'events.user_id', 'events.created_at', 'events.updated_at')
            ->orderBy('admin_revenue', 'desc')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'stats', 'admin', 'recentLogs', 'userCounts', 'pendingVerifications',
            'eventStats', 'userGrowthStats', 'salesStats', 'refundStats', 
            'supportStats', 'eventApprovalStats', 'adminStats', 'feedbackStats',
            'ticketRevenue','ticketOrders','ticketPending','subRevenue',
            'ticketTrendLabels','ticketTrendRevenue','ticketTrendOrders',
            'subTrendLabels','subTrendRevenue', 'topEarningEvents'
        ));
    }

    /**
     * Show admin activity logs.
     */
    public function logs()
    {
        $query = AdminActivityLog::with('admin');

        // Apply filters
        if (request('action')) {
            $query->where('action', request('action'));
        }

        if (request('admin_id')) {
            $query->where('admin_id', request('admin_id'));
        }

        if (request('date_from')) {
            $query->whereDate('created_at', '>=', request('date_from'));
        }

        if (request('date_to')) {
            $query->whereDate('created_at', '<=', request('date_to'));
        }

        $logs = $query->latest()->paginate(20);
        $admins = Admin::all();

        return view('admin.logs', compact('logs', 'admins'));
    }

    /**
     * Show user management.
     */
    public function users()
    {
        $currentAdmin = Auth::guard('admin')->user();
        if (!$currentAdmin || (!$currentAdmin->isSuperAdmin() && !optional($currentAdmin->access)->can_manage_users)) {
            abort(403, 'You do not have permission to manage users.');
        }
        $query = User::with(['organizerVerifications' => function($query) {
            $query->latest();
        }]);

        // Online tracking using session data from database
        // Sessions table stores: id, user_id, role (user/admin/guest), last_activity (timestamp)
        // A user is considered "online" if they have an active session with:
        //   - user_id is not null (logged in)
        //   - last_activity is within the last 30 minutes (active)
        // This is updated in real-time via Pusher when user activity changes
        $onlineUserIds = \DB::table('sessions')
            ->where('last_activity', '>', now()->subMinutes(30)->timestamp)
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->unique()
            ->values()
            ->toArray();

        // Apply filters
        if (request('status')) {
            switch (request('status')) {
                case 'active':
                    $query->where('status', User::STATUS_ACTIVE);
                    break;
                case 'deleted':
                    $query->where('status', User::STATUS_DELETED);
                    break;
                case 'banned':
                    $query->where('status', User::STATUS_BANNED);
                    break;
            }
        }

        if (request('organizer_status')) {
            switch (request('organizer_status')) {
                case 'verified':
                    $query->whereNotNull('verified_organizer');
                    break;
                case 'pending':
                    $query->whereHas('organizerVerifications', function($q) {
                        $q->where('status', 'pending');
                    });
                    break;
                case 'regular':
                    $query->where('organizer', false)->whereNull('verified_organizer');
                    break;
            }
        }

        // Search by email
        if (request('email')) {
            $query->where('email', 'like', '%' . request('email') . '%');
        }

        // Search by name
        if (request('name')) {
            $query->where(function($q) {
                $q->where('first_name', 'like', '%' . request('name') . '%')
                  ->orWhere('last_name', 'like', '%' . request('name') . '%')
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . request('name') . '%']);
            });
        }

        // Filter by online status, if provided
        if (request('online_status') === 'online') {
            $query->whereIn('id', $onlineUserIds ?: [-1]);
        } elseif (request('online_status') === 'offline') {
            if (!empty($onlineUserIds)) {
                $query->whereNotIn('id', $onlineUserIds);
            }
        }

        // Apply sorting
        $sortBy = request('sort_by', 'created_at');
        $sortOrder = request('sort_order', 'desc');
        
        // Validate sort column to prevent SQL injection
        $allowedSortColumns = ['created_at', 'first_name', 'last_name', 'email', 'id'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }
        
        // Validate sort order
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }
        
        // Apply sorting
        if ($sortBy === 'first_name' || $sortBy === 'last_name') {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $users = $query->paginate(20);

        $stats = [
            'total_users' => User::where('status', User::STATUS_ACTIVE)->count(),
            'verified_organizers' => User::where('status', User::STATUS_ACTIVE)->whereNotNull('verified_organizer')->count(),
            'pending_organizers' => \App\Models\OrganizerVerification::where('status', 'pending')->count(),
            'regular_users' => User::where('status', User::STATUS_ACTIVE)->where('organizer', false)->whereNull('verified_organizer')->count(),
            'deleted_users' => User::where('status', User::STATUS_DELETED)->count(),
            'banned_users' => User::where('status', User::STATUS_BANNED)->count(),
        ];

        return view('admin.users', compact('users', 'stats', 'onlineUserIds'));
    }

    /**
     * Export users to CSV
     */
    public function exportUsers(Request $request)
    {
        $currentAdmin = Auth::guard('admin')->user();
        if (!$currentAdmin || (!$currentAdmin->isSuperAdmin() && !optional($currentAdmin->access)->can_manage_users)) {
            abort(403, 'You do not have permission to export users.');
        }

        $type = $request->get('type', 'all'); // all, verified, pending
        $exporter = new \App\Exports\UserExport();
        return $exporter->export('csv', $type);
    }

    /**
     * Timeseries data for user activity chart.
     */
    public function userActivityTimeseries(\Illuminate\Http\Request $request)
    {
        $range = $request->query('range', '14d');

        if ($range === 'month') {
            $year = now()->year;
            $rows = \DB::table('activity_logs')
                ->selectRaw('MONTH(created_at) as m, COUNT(*) as c')
                ->whereYear('created_at', $year)
                ->groupBy('m')
                ->orderBy('m')
                ->get();
            $byMonth = collect($rows)->keyBy('m');
            $labels = [];
            $data = [];
            for ($m = 1; $m <= 12; $m++) {
                $labels[] = date('M', mktime(0, 0, 0, $m, 1));
                $data[] = (int)($byMonth[$m]->c ?? 0);
            }
            return response()->json(['labels' => $labels, 'data' => $data, 'range' => 'month']);
        } elseif ($range === 'year') {
            $startYear = now()->year - 4;
            $rows = \DB::table('activity_logs')
                ->selectRaw('YEAR(created_at) as y, COUNT(*) as c')
                ->whereYear('created_at', '>=', $startYear)
                ->groupBy('y')
                ->orderBy('y')
                ->get();
            $byYear = collect($rows)->keyBy('y');
            $labels = [];
            $data = [];
            for ($y = $startYear; $y <= now()->year; $y++) {
                $labels[] = (string)$y;
                $data[] = (int)($byYear[$y]->c ?? 0);
            }
            return response()->json(['labels' => $labels, 'data' => $data, 'range' => 'year']);
        } else {
            $days = 14;
            $rows = \DB::table('activity_logs')
                ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy('d')
                ->orderBy('d')
                ->get();
            $byDate = collect($rows)->keyBy('d');
            $labels = [];
            $data = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i)->toDateString();
                $labels[] = $date;
                $data[] = (int)($byDate[$date]->c ?? 0);
            }
            return response()->json(['labels' => $labels, 'data' => $data, 'range' => '14d']);
        }
    }

    /**
     * Show specific user details and logs.
     */
    public function showUser(User $user)
    {
        $user->load([
            'organizerVerifications',
            'activityLogs',
            'subscriptions',
        ]);
        
        // Get user statistics
        $userStats = [
            'total_logins' => $user->activityLogs()->where('action', 'login')->count(),
            'last_login' => $user->activityLogs()->where('action', 'login')->latest()->first()?->created_at,
            'verification_requests' => $user->organizerVerifications->count(),
            'subscriptions' => $user->subscriptions->count(),
            // New aggregates
            'created_events' => \App\Models\Event::where('user_id', $user->id)->count(),
            'participated_events' => \App\Models\EventStaff::where('user_id', $user->id)->distinct('event_id')->count('event_id'),
            'purchases' => \App\Models\TicketPurchase::where('user_id', $user->id)->count(),
            'created_events_published' => \App\Models\Event::where('user_id', $user->id)->where('status','published')->count(),
            'created_events_draft' => \App\Models\Event::where('user_id', $user->id)->where('status','draft')->count(),
        ];
        
        // Get recent activity logs
        $recentLogs = $user->activityLogs()->latest()->take(20)->get();
        
        // Current subscription (latest)
        $currentSubscription = \App\Models\Subscription::where('user_id', $user->id)
            ->latest('created_at')
            ->first();

        // Lists for tabs
        $createdEvents = \App\Models\Event::where('user_id', $user->id)
            ->orderByDesc('start_date')
            ->get(['id','title','status','start_date']);

        $participatedEvents = \App\Models\Event::whereHas('ticketInformation.ticketPurchase', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderByDesc('start_date')
            ->get(['id','title','status','start_date']);

        $purchases = \App\Models\TicketPurchase::with(['event', 'ticketInformation'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        // Get subscription purchases
        $subscriptions = \App\Models\Subscription::with(['plan'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        // Get additional ticket purchases (TicketLimitPurchase)
        $additionalTickets = \App\Models\TicketLimitPurchase::with(['event', 'ticketLimitPricing'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        // Combine ticket purchases, additional tickets, and subscriptions into a unified purchase history
        $allPurchases = collect();
        
        // Add ticket purchases
        foreach($purchases as $purchase) {
            $allPurchases->push([
                'id' => $purchase->id,
                'type' => 'ticket',
                'invoice_id' => $purchase->purchase_id ?? $purchase->id,
                'title' => $purchase->event->title ?? 'Event',
                'amount' => $purchase->total_amount,
                'gross_amount' => $purchase->total_amount,
                'transaction_fee' => $purchase->transaction_fee ?? 0,
                'vat_amount' => $purchase->vat_amount ?? 0,
                'net_amount' => $purchase->net_amount ?? $purchase->total_amount,
                'payment_method' => $purchase->payment_method ?? '—',
                'status' => $purchase->status ?? $purchase->payment_status,
                'created_at' => $purchase->created_at,
                'route_params' => [
                    'purchaseId' => $purchase->purchase_id ?? $purchase->id,
                    'ticketId' => $purchase->ticketInformation->first()->id ?? ''
                ]
            ]);
        }
        
        // Add subscription purchases
        foreach($subscriptions as $subscription) {
            $allPurchases->push([
                'id' => $subscription->id,
                'type' => 'subscription',
                'invoice_id' => 'SUB_' . $subscription->id,
                'title' => $subscription->plan->name ?? 'Subscription Plan',
                'amount' => $subscription->amount,
                'gross_amount' => $subscription->amount,
                'transaction_fee' => $subscription->transaction_fee ?? 0,
                'vat_amount' => $subscription->vat_amount ?? 0,
                'net_amount' => $subscription->net_amount ?? $subscription->amount,
                'payment_method' => $subscription->payment_method ?? '—',
                'status' => $subscription->payment_status,
                'created_at' => $subscription->created_at,
                'route_params' => [
                    'subscriptionId' => $subscription->id
                ]
            ]);
        }
        
        // Add additional ticket purchases
        foreach($additionalTickets as $additionalTicket) {
            $allPurchases->push([
                'id' => $additionalTicket->id,
                'type' => 'additional_ticket',
                'invoice_id' => 'TLP_' . $additionalTicket->id,
                'title' => $additionalTicket->event->title ?? 'Additional Tickets',
                'amount' => $additionalTicket->amount,
                'gross_amount' => $additionalTicket->amount,
                'transaction_fee' => $additionalTicket->transaction_fee ?? 0,
                'vat_amount' => $additionalTicket->vat_amount ?? 0,
                'net_amount' => $additionalTicket->net_amount ?? $additionalTicket->amount,
                'payment_method' => $additionalTicket->payment_method ?? '—',
                'status' => $additionalTicket->payment_status,
                'created_at' => $additionalTicket->created_at,
                'route_params' => [
                    'purchaseId' => $additionalTicket->id
                ]
            ]);
        }
        
        // Sort combined purchases by date (newest first)
        $allPurchases = $allPurchases->sortByDesc('created_at')->values();

        // Get detailed ticket participation data (exclude tickets that have been shared/given away)
        $ticketParticipation = \App\Models\EventTicketInformation::with(['event', 'ticketPurchase'])
            ->whereHas('ticketPurchase', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('user_id', $user->id) // Only show tickets that still belong to this user
            ->orderByDesc('created_at')
            ->get();

        // Sales stats for this user (similar shape as organizer dashboard)
        $salesStats = [
            'totalRevenue' => 0,
            'totalTicketsSold' => 0,
            'thisMonthRevenue' => 0,
            'pendingRevenue' => 0,
        ];
        try {
            $salesAgg = \App\Models\TicketPurchase::selectRaw('SUM(total_amount) as revenue, COUNT(*) as orders')
                ->where('user_id', $user->id)
                ->where('status', 'paid')
                ->first();
            $salesStats['totalRevenue'] = (float) ($salesAgg->revenue ?? 0);
            $salesStats['totalTicketsSold'] = (int) (\App\Models\EventTicketInformation::whereHas('ticketPurchase', function($q) use ($user){
                $q->where('user_id', $user->id)->where('status','paid');
            })->where('user_id', $user->id)->count()); // Only count tickets that still belong to this user
            $salesStats['thisMonthRevenue'] = (float) (\App\Models\TicketPurchase::where('user_id', $user->id)
                ->where('status','paid')
                ->where('created_at','>=', now()->startOfMonth())
                ->sum('total_amount'));
            $salesStats['pendingRevenue'] = (float) (\App\Models\TicketPurchase::where('user_id', $user->id)
                ->whereIn('status', ['pending','processing'])
                ->sum('total_amount'));
        } catch (\Throwable $e) { /* best effort */ }

        return view('admin.user-details', compact('user', 'userStats', 'recentLogs', 'currentSubscription', 'createdEvents', 'participatedEvents', 'purchases', 'subscriptions', 'allPurchases', 'ticketParticipation', 'salesStats'));
    }

    /**
     * Return the Organizer Verification panel (partial) for the Admin user details page.
     */
    public function userVerificationPanel(User $user)
    {
        return view('admin.user-details.partials.verification', compact('user'));
    }

    /**
     * Admin: Unverify a user as organizer (keeps organizer flag unless explicitly toggled elsewhere).
     */
    public function unverifyOrganizer(User $user)
    {
        $currentAdmin = Auth::guard('admin')->user();
        if (!$currentAdmin || (!$currentAdmin->isSuperAdmin() && !optional($currentAdmin->access)->can_manage_users)) {
            abort(403, 'You do not have permission to manage users.');
        }

        try {
            $user->unverifyAsOrganizer();

            // Optionally mark the latest verification record as revoked
            $latest = \App\Models\OrganizerVerification::where('user_id', $user->id)->latest()->first();
            if ($latest && $latest->status === 'approved') {
                $latest->status = 'revoked';
                $latest->reviewed_by = $currentAdmin->id;
                $latest->reviewed_at = now();
                $latest->save();
            }

            // Log admin activity
            $currentAdmin->logActivity('verification_revoked', [
                'user_id' => $user->id,
                'user_email' => $user->email,
            ], 'Revoked organizer verification');

            // Log user activity
            $user->logActivity('organizer_verification_revoked', 'Your organizer verification was revoked by an administrator.', [
                'admin_id' => $currentAdmin->id,
                'admin_username' => $currentAdmin->username,
            ]);

            if (request()->ajax()) {
                return response()->json(['success' => true]);
            }
            return back()->with('success', 'Organizer verification revoked.');
        } catch (\Throwable $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to revoke verification.');
        }
    }

    /**
     * Delete (soft delete) a user
     */
    public function deleteUser(User $user)
    {
        $currentAdmin = Auth::guard('admin')->user();
        if (!$currentAdmin || (!$currentAdmin->isSuperAdmin() && !optional($currentAdmin->access)->can_manage_users)) {
            abort(403, 'You do not have permission to manage users.');
        }

        // Soft delete the user
        $user->softDelete();

        // Log admin activity
        $currentAdmin->logActivity('user_delete', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_name' => $user->full_name,
        ], 'Deleted user: ' . $user->full_name . ' (' . $user->email . ')');

        // Also log to user's activity log
        $user->logActivity('account_scheduled_for_deletion', [
            'admin_id' => $currentAdmin->id,
            'admin_username' => $currentAdmin->username,
            'deleted_at' => now()->toDateTimeString(),
            'deletion_date' => now()->addDays(30)->toDateTimeString(),
        ], 'Your account has been scheduled for deletion by ' . $currentAdmin->username . '. Account will be permanently deleted in 30 days.');

        // Send account deletion email notification
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)
                ->send(new \App\Mail\AccountDeletionMail($user));
        } catch (\Exception $e) {
            \Log::error('Failed to send account deletion email (admin)', [
                'user_id' => $user->id,
                'admin_id' => $currentAdmin->id,
                'error' => $e->getMessage()
            ]);
        }

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.',
            ]);
        }

        return redirect()->route('admin.users')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Ban a user
     */
    public function banUser(Request $request, User $user)
    {
        $currentAdmin = Auth::guard('admin')->user();
        if (!$currentAdmin || (!$currentAdmin->isSuperAdmin() && !optional($currentAdmin->access)->can_manage_users)) {
            abort(403, 'You do not have permission to manage users.');
        }

        $request->validate([
            'banned_reason' => 'nullable|string|max:1000',
            'banned_until' => 'nullable|date',
        ]);

        // Ban the user (optionally until a date)
        $user->ban($request->banned_reason, $request->banned_until);

        // Log admin activity
        $currentAdmin->logActivity('user_ban', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_name' => $user->full_name,
            'banned_reason' => $request->banned_reason ?? 'No reason provided',
            'banned_until' => $request->banned_until,
        ], 'Banned user: ' . $user->full_name . ' (' . $user->email . ') - Reason: ' . ($request->banned_reason ?? 'No reason provided'));

        // Also log to user's activity log
        $user->logActivity(
            'account_restricted',
            'Your account has been restricted by ' . $currentAdmin->username . ' - Reason: ' . ($request->banned_reason ?? 'No reason provided'),
            [
                'admin_id' => $currentAdmin->id,
                'admin_username' => $currentAdmin->username,
                'banned_reason' => $request->banned_reason ?? 'No reason provided',
                'banned_at' => now()->toDateTimeString(),
                'banned_until' => $request->banned_until,
            ]
        );

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User banned successfully.',
            ]);
        }

        return redirect()->route('admin.users')
            ->with('success', 'User banned successfully.');
    }

    /**
     * Unban a user
     */
    public function unbanUser(User $user)
    {
        $currentAdmin = Auth::guard('admin')->user();
        if (!$currentAdmin || (!$currentAdmin->isSuperAdmin() && !optional($currentAdmin->access)->can_manage_users)) {
            abort(403, 'You do not have permission to manage users.');
        }

        // Unban the user
        $user->unban();

        // Log admin activity
        $currentAdmin->logActivity('user_unban', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_name' => $user->full_name,
        ], 'Unbanned user: ' . $user->full_name . ' (' . $user->email . ')');

        // Also log to user's activity log
        $user->logActivity(
            'account_restriction_lifted',
            'Your account restriction has been lifted by ' . $currentAdmin->username,
            [
                'admin_id' => $currentAdmin->id,
                'admin_username' => $currentAdmin->username,
                'unbanned_at' => now()->toDateTimeString(),
            ]
        );

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User unbanned successfully.',
            ]);
        }

        return redirect()->route('admin.users')
            ->with('success', 'User unbanned successfully.');
    }

    /**
     * Restore a deleted user
     */
    public function restoreUser(User $user)
    {
        $currentAdmin = Auth::guard('admin')->user();
        if (!$currentAdmin || (!$currentAdmin->isSuperAdmin() && !optional($currentAdmin->access)->can_manage_users)) {
            abort(403, 'You do not have permission to manage users.');
        }

        // Restore the user
        $user->restore();

        // Log admin activity
        $currentAdmin->logActivity('user_restore', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_name' => $user->full_name,
        ], 'Restored user: ' . $user->full_name . ' (' . $user->email . ')');

        // Also log to user's activity log
        $user->logActivity('account_deletion_cancelled', [
            'admin_id' => $currentAdmin->id,
            'admin_username' => $currentAdmin->username,
            'restored_at' => now()->toDateTimeString(),
        ], 'Your account deletion has been cancelled and your account has been restored by ' . $currentAdmin->username);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User restored successfully.',
            ]);
        }

        return redirect()->route('admin.users')
            ->with('success', 'User restored successfully.');
    }
}