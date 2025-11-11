# Qricket Platform - Controllers and Middleware Documentation

This comprehensive guide documents all controllers and middleware in the Qricket platform. Use it as a reference for understanding the system architecture, component locations, and responsibilities.

---

## Table of Contents
1. [Controllers](#controllers)
   - [Admin Controllers](#admin-controllers)
   - [Organizer Controllers](#organizer-controllers)
   - [User Controllers](#user-controllers)
   - [Event Controllers](#event-controllers)
   - [Auth Controllers](#auth-controllers)
   - [Payment Controllers](#payment-controllers)
   - [Support Controllers](#support-controllers)
   - [API Controllers](#api-controllers)
   - [Other Controllers](#other-controllers)
2. [Middleware](#middleware)
   - [Admin Middleware](#admin-middleware)
   - [Organizer Middleware](#organizer-middleware)
   - [Auth Middleware](#auth-middleware)
   - [Security Middleware](#security-middleware)
   - [Event Middleware](#event-middleware)
   - [System Middleware](#system-middleware)
   - [User Middleware](#user-middleware)

---

## Controllers

### Admin Controllers

#### `app/Http/Controllers/Admin/DashboardController.php`
- **Purpose:** Main admin dashboard with comprehensive statistics and analytics.
- **Key methods:** `index()`, `analytics()`, `getRevenueStats()`, `getMonthlyStats()`
- **Key actions:** Displays revenue statistics (gross, net, fees, VAT), event statistics, user counts, support ticket counts, recent activity, monthly trends.
- **Notable integrations:** XenditFeeCalculator, Event/User/SupportTicket models, comprehensive revenue breakdown.

#### `app/Http/Controllers/Admin/AdminAuthController.php`
- **Purpose:** Handles admin authentication, login, PIN verification, and logout.
- **Key methods:** `showLogin()`, `login()`, `showPin()`, `verifyPin()`, `logout()`, `dashboard()`, `users()`, `showUser()`, `banUser()`, `unbanUser()`, `deleteUser()`, `restoreUser()`, `unverifyOrganizer()`, `exportUsers()`, `userActivityTimeseries()`
- **Key actions:** Dual authentication (username/password + PIN), session conflict detection, account lockout after failed attempts, user management (ban/unban/delete/restore), user statistics, activity logging.
- **Notable integrations:** UserSessionService, AdminLoggedOut event, AdminActivityLog, Pusher for session conflicts, shadow user creation for web guard access.

#### `app/Http/Controllers/Admin/AuthController.php`
- **Purpose:** (Empty placeholder - reserved for future use)

#### `app/Http/Controllers/Admin/DashboardAnalyticsController.php`
- **Purpose:** Provides analytics data and metrics for admin dashboard.
- **Key methods:** `userActivityTimeseries()`, `metrics()`, `triggerUpdate()`
- **Key actions:** Generates timeseries data for user activity charts (daily/monthly/yearly), aggregates traffic metrics (PV, UV, DAU, WAU), commerce metrics (revenue, refunds, conversions), event statistics, triggers dashboard update events.
- **Notable integrations:** AdminDashboardUpdated event, real-time metrics broadcasting.

#### `app/Http/Controllers/Admin/EventApprovalController.php`
- **Purpose:** Handles admin approval/rejection of events.
- **Key methods:** `index()`, `pending()`, `show()`, `approve()`, `reject()`, `reset()`
- **Key actions:** Lists events for approval with filters, shows pending events, displays event details, approves/rejects events, resets event status, creates notifications, sends approval/rejection emails.
- **Notable integrations:** Notification model, approval/rejection emails, activity logging, event filtering and search.

#### `app/Http/Controllers/Admin/FeedbackController.php`
- **Purpose:** Manages Qricket platform feedback from users.
- **Key methods:** `index()`
- **Key actions:** Displays feedback list with pagination, shows feedback statistics (total, today, average ratings for navigation, information, design, performance, process).
- **Notable integrations:** QricketFeedback model, feedback aggregation.

#### `app/Http/Controllers/Admin/LogsController.php`
- **Purpose:** Manages event activity logs and admin activity logs.
- **Key methods:** `index()`, `eventLogs()`, `userLogs()`, `search()`, `export()`, `statistics()`
- **Key actions:** Lists activity logs with filters, shows event-specific logs, shows user-specific logs, searches logs by action/date/user, exports logs to CSV, displays log statistics.
- **Notable integrations:** EventActivityLog, AdminActivityLog models, CSV export functionality.

#### `app/Http/Controllers/Admin/PayoutsController.php`
- **Purpose:** Manages event payout requests from organizers.
- **Key methods:** `index()`, `approve()`, `reject()`, `release()`
- **Key actions:** Lists payout requests (pending, approved, history), approves payout requests, rejects payout requests, releases payouts for collection, creates notifications for organizers, triggers dashboard updates.
- **Notable integrations:** EventPayoutRequest model, NotificationService, AdminDashboardUpdated event, AdminMetricsPing event.

#### `app/Http/Controllers/Admin/ReportsController.php`
- **Purpose:** Generates comprehensive sales and revenue reports.
- **Key methods:** `sales()`, `exportSales()`, `triggerSalesUpdate()`
- **Key actions:** Displays sales statistics (ticket revenue, subscription revenue, additional ticket revenue, admin earnings, refunds), exports sales data to XLSX, provides revenue breakdown by type, triggers real-time sales updates.
- **Notable integrations:** XlsxGenerator service, TicketPurchase, Subscription, TicketLimitPurchase models, AdminDashboardUpdated event.

#### `app/Http/Controllers/Admin/SubscriptionController.php`
- **Purpose:** Manages subscription plans, ticket pricing, and discounts.
- **Key methods:** `index()`, `ticketPricing()`, `discounts()`, `subscriptions()`, `storePricing()`, `updatePricing()`, `destroyPricing()`, `storeDiscount()`, `updateDiscount()`, `destroyDiscount()`, `storePlan()`, `updatePlan()`, `destroyPlan()`, `purchases()`
- **Key actions:** Manages ticket limit pricing plans, discount codes, subscription plans, displays analytics for each section, handles CRUD operations for all plan types.
- **Notable integrations:** TicketLimitPricing, Discount, SubscriptionPlan, TicketLimitPurchase models, AdminActivityLog.

#### `app/Http/Controllers/Admin/AdminNotificationController.php`
- **Purpose:** Handles admin notifications display and management.
- **Key methods:** `index()`, `data()`, `markAsRead()`, `markAllAsRead()`, `generateNotificationTitle()`, `generateNotificationMessage()`
- **Key actions:** Displays admin notifications page, provides notification data via API, marks notifications as read, generates notification titles and messages based on type/action.
- **Notable integrations:** Notification model, admin notification filtering.

#### `app/Http/Controllers/Admin/ActivityLogController.php`
- **Purpose:** Manages admin-specific activity logs.
- **Key methods:** `index()`, `show()`
- **Key actions:** Lists activity logs for current admin with filters (action, date range), shows detailed log entry.
- **Notable integrations:** AdminActivityLog model, admin activity filtering.

---

### Organizer Controllers

#### `app/Http/Controllers/Organizer/OrganizerDashboardController.php`
- **Purpose:** Main organizer dashboard with event statistics and analytics.
- **Key methods:** `index()`
- **Key actions:** Displays organizer's events (owned or staff), revenue statistics, ticket sales, event statistics, activity logs, filters by view (all/owner/staff), calculates revenue with fee breakdown.
- **Notable integrations:** Event, EventStaff, TicketPurchase, TicketLimitPurchase models, XenditFeeCalculator, real-time statistics.

#### `app/Http/Controllers/Organizer/EventManagement/BasicInfoController.php`
- **Purpose:** Handles event creation step 1 (title, schedule, venue, metadata).
- **Key methods:** `step1Store()`, `step1Update()`
- **Key actions:** Validates event basics, enforces profanity filtering, allows assigned staff to manage events alongside owners and admins, handles autosave.
- **Notable integrations:** Autosave support, profanity rule usage, user/staff authorization checks.

#### `app/Http/Controllers/Organizer/EventManagement/TicketsController.php`
- **Purpose:** Manages ticket types for an event (create, update, delete).
- **Key methods:** `store()`, `update()`, `destroy()`
- **Key actions:** Applies subscription limits, enforces max ticket counts, blocks deletion when tickets are already sold, validates ticket availability windows.
- **Notable integrations:** Uses plan limits, additional ticket purchases, validates ticket availability.

#### `app/Http/Controllers/Organizer/EventManagement/ReviewController.php`
- **Purpose:** Submits events for approval and publishes approved events.
- **Key methods:** `submitForApproval()`, `publish()`
- **Key actions:** Moves events into `pending_approval`, logs activity, creates admin notifications, syncs with verification requests.
- **Notable integrations:** Notification model, activity logging, event status management.

#### `app/Http/Controllers/Organizer/EventManagement/LayoutController.php`
- **Purpose:** Manages layout components for event landing pages and uploads layout assets.
- **Key methods:** `store()`, `update()`, `uploadLayoutImage()`
- **Key actions:** Validates layout image uploads, saves assets to R2 storage, updates layout JSON blocks.
- **Notable integrations:** R2 storage, layout JSON management.

#### `app/Http/Controllers/Organizer/EventManagement/CertificateController.php`
- **Purpose:** Handles certificate creation during event setup (step 4).
- **Key methods:** `step4()`, `step4Store()`, `uploadLogo()`, `uploadSignature()`, `autosaveCertificate()`, `serveAsset()`
- **Key actions:** Manages certificate template creation, logo/signature uploads, autosave functionality, asset serving with signed URLs.
- **Note:** Different from `EventCertificateController` which handles editing existing certificates.
- **Notable integrations:** R2 storage, signed URLs, certificate template management.

#### `app/Http/Controllers/Organizer/EventManagement/StaffController.php`
- **Purpose:** Manages event staff assignment and settings (step 5).
- **Key methods:** `step5()`, `step5Store()`, `manage()`, `autosaveStep5()`, `searchStaff()`
- **Key actions:** Handles staff assignment, permission management, staff search, autosave for staff settings.
- **Notable integrations:** Creates Notification records for staff assignments, sends StaffAssignedNotification emails.

#### `app/Http/Controllers/Organizer/EventManagement/EventEditController.php`
- **Purpose:** Provides edit views for existing events (basic info, layout, certificate, tickets, staff, QR code).
- **Key methods:** `preview()`, `editBasic()`, `editLayout()`, `editCertificate()`, `tickets()`, `staff()`, `qrCode()`
- **Key actions:** Renders edit forms for each event section, loads data from R2 storage, handles authorization checks.
- **Notable integrations:** Works with R2 storage for layout/certificate data, enforces staff permissions.

#### `app/Http/Controllers/Organizer/EventManagement/EventDashboardController.php`
- **Purpose:** Provides the organizer dashboard view for an event.
- **Key methods:** `index()`, `getEventActivityTrend()`
- **Key actions:** Aggregates event statistics, guards sections based on permissions/staff membership, aligns activity-log queries with `event_viewed` actions.
- **Notable integrations:** Real-time statistics, activity trend analysis, permission-based UI rendering.

#### `app/Http/Controllers/Organizer/EventManagement/EventAttendanceController.php`
- **Purpose:** Handles attendance tracking, manual/QR check-ins, exports, and certificate generation.
- **Key methods:** `attendance()`, `scanner()`, `processQRScan()`, `manualCheckIn()`, `exportAttendance()`, `generateCertificates()`
- **Key actions:** Processes QR scan payloads, guards against double check-ins, exports XLSX reports, sends certificate delivery emails.
- **Notable integrations:** Uses Notification model for in-app alerts, triggers real-time broadcasting, updates certificate flags, QRCodeEncryptionService.

#### `app/Http/Controllers/Organizer/EventManagement/EventCertificateController.php`
- **Purpose:** Powers certificate design (create/edit, autosave, public preview) for existing events.
- **Key methods:** `edit()`, `update()`, `autosave()`, `publicCertificatePage()`, `uploadLogo()`, `uploadSignature()`
- **Key actions:** Autosaves certificate JSON, manages logo/signature uploads, serves public certificate pages with signed assets.
- **Note:** Different from `CertificateController` which handles step 4 during event creation.
- **Notable integrations:** R2 storage, signed URLs, certificate template management.

#### `app/Http/Controllers/Organizer/EventManagement/EventAnalyticsController.php`
- **Purpose:** Provides analytics and reporting for events.
- **Key methods:** `getRecentSales()`, `getSalesSummary()`
- **Key actions:** Generates sales reports, attendee demographics, performance metrics, exports analytics data, provides real-time sales updates.
- **Notable integrations:** Real-time analytics, data visualization, export functionality, Pusher broadcasting for sales updates.

#### `app/Http/Controllers/Organizer/EventManagement/EventPayoutController.php`
- **Purpose:** Handles payout requests for events.
- **Key methods:** `show()`, `request()`, `collect()`
- **Key actions:** Displays payout information, creates payout requests, processes payouts.
- **Notable integrations:** Event revenue calculation, payout request management.

#### `app/Http/Controllers/Organizer/EventNewsController.php`
- **Purpose:** CRUD for event news posts.
- **Key methods:** `create()`, `store()`, `edit()`, `update()`, `destroy()`, `publish()`
- **Key actions:** Enforces `can_manage_news` access, handles scheduled publishing, creates in-app notifications, sends emails through `EventNewsNotification` (mail only).
- **Notable integrations:** Scheduled publishing, notification system, image attachment handling.

#### `app/Http/Controllers/Organizer/PayoutRequestController.php`
- **Purpose:** Handles organizer payout requests for events.
- **Key methods:** `show()`, `calculatePayout()`, `requestPayout()`, `collectPayout()`
- **Key actions:** Displays payout information, calculates payout amounts (net revenue minus Qricket fees), creates payout requests, processes payouts via Xendit disbursement.
- **Notable integrations:** XenditDisbursementService, XenditPaymentService, EventPayoutRequest model, fee calculations.

#### `app/Http/Controllers/Organizer/RbacController.php`
- **Purpose:** Manages role-based access control for event staff.
- **Key methods:** `index()`, `assign()`, `revoke()`
- **Key actions:** Displays staff list for an event, assigns staff with permissions (manage_event, manage_news, manage_attendance, entry_scan), revokes staff access.
- **Notable integrations:** EventStaff model, permission management.

#### `app/Http/Controllers/Organizer/TicketLimitController.php`
- **Purpose:** Manages additional ticket limit purchases for events.
- **Key methods:** `show()`, `purchase()`, `validateDiscount()`, `success()`, `failure()`, `myPurchases()`, `viewInvoice()`
- **Key actions:** Displays ticket limit purchase page, processes purchases with Xendit, validates discount codes, shows purchase history, displays invoices.
- **Notable integrations:** TicketLimitPricing, TicketLimitPurchase, Discount models, Xendit payment integration, SalesReportUpdated event, DashboardStatsUpdated event.

---

### User Controllers

#### `app/Http/Controllers/User/ProfileController.php`
- **Purpose:** Manages user profile information and settings.
- **Key methods:** `edit()`, `update()`, `destroy()`
- **Key actions:** Displays profile edit form, updates profile information (name, email, profile picture), handles profile picture uploads to R2 storage, deletes user account.
- **Notable integrations:** R2 storage for profile pictures, ProfileUpdateRequest validation.

#### `app/Http/Controllers/User/UserController.php`
- **Purpose:** Handles user mode switching (user/organizer).
- **Key methods:** `switchMode()`
- **Key actions:** Switches user mode between 'user' and 'organizer', stores mode in session, redirects to appropriate dashboard.
- **Notable integrations:** Session management, mode-based routing.

#### `app/Http/Controllers/User/ModeController.php`
- **Purpose:** (Empty placeholder - reserved for future use)

#### `app/Http/Controllers/User/OrganizerVerificationController.php`
- **Purpose:** Handles organizer verification requests from users.
- **Key methods:** `show()`, `store()`, `index()`, `showAdmin()`, `approve()`, `reject()`
- **Key actions:** Displays verification form, processes verification requests with document uploads, admin approval/rejection, updates user as verified organizer.
- **Notable integrations:** OrganizerVerification model, R2 storage for documents, NotificationService, AdminDashboardUpdated event.

#### `app/Http/Controllers/User/SavedEventController.php`
- **Purpose:** Manages user's saved events.
- **Key methods:** `index()`, `toggle()`, `store()`, `destroy()`
- **Key actions:** Lists saved events, toggles save/unsave status, handles mode switching (user/organizer).
- **Notable integrations:** Session-based mode detection, many-to-many relationship with events.

---

### Event Controllers

#### `app/Http/Controllers/Events/EventController.php`
- **Purpose:** Public-facing event retrieval plus organizer-level lifecycle actions.
- **Key methods:** `show()`, `updateStatus()`, `destroy()`, `getRecentlyViewedEventsForUser()`
- **Key actions:** Shows event sections, filters news by `published_at`, updates statuses, deletes events (with storage cleanup).
- **Notable integrations:** Works with `EventActivityLog` for views, Storage facade for logo/news asset refresh.

#### `app/Http/Controllers/Events/EventBrowsingController.php`
- **Purpose:** Public event discovery interface.
- **Key methods:** `browse()`, `getTagSuggestions()`
- **Key actions:** Applies search filters (category, tags, price, date), enforces `published` status by default, controls free/past event toggles.
- **Notable integrations:** Tag suggestion system, filter persistence, pagination.

#### `app/Http/Controllers/Events/TicketPurchaseController.php`
- **Purpose:** Handles ticket selection, purchase, and individual ticket rendering.
- **Key methods:** `show()`, `purchase()`, `details()`, `ticketDetails()`, `generateQRCode()`
- **Key actions:** Validates customer info, enforces max tickets per user/order, integrates with Xendit for paid events, issues QR-enabled tickets for free purchases.
- **Notable integrations:** Xendit payment gateway, QR code generation, email notifications, QRCodeEncryptionService.

#### `app/Http/Controllers/Events/EventFeedbackController.php`
- **Purpose:** Handles event feedback submission and management.
- **Key methods:** `show()`, `submit()`, `sendFeedbackRequest()`
- **Key actions:** Displays feedback form, validates and stores feedback, sends feedback requests to attendees.
- **Notable integrations:** Profanity filtering, feedback request notifications, attendee verification.

#### `app/Http/Controllers/Events/ActivityLogController.php`
- **Purpose:** (Currently empty placeholder)
- **Note:** Reserved for future activity log functionality.

#### `app/Http/Controllers/EventFeedbackController.php`
- **Purpose:** Handles event feedback (alternative implementation).
- **Key methods:** (Similar to Events/EventFeedbackController)
- **Key actions:** Event feedback management.
- **Notable integrations:** EventFeedback model.

---

### Auth Controllers

#### `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- **Purpose:** Handles user authentication (login/logout).
- **Key methods:** `create()`, `store()`, `destroy()`
- **Key actions:** Displays login form, authenticates users, validates email verification, handles session management, logs user activity, updates user counts.
- **Notable integrations:** UserSessionService, email verification checks, activity logging.

#### `app/Http/Controllers/Auth/RegisteredUserController.php`
- **Purpose:** Handles user registration.
- **Key methods:** `create()`, `store()`
- **Key actions:** Displays registration form, validates user data, creates user account, sends email verification, logs registration activity.
- **Notable integrations:** UserSessionService, Registered event, email verification.

#### `app/Http/Controllers/Auth/GoogleController.php`
- **Purpose:** (Empty placeholder - reserved for Google OAuth)
- **Note:** Reserved for future Google authentication integration.

#### `app/Http/Controllers/Auth/PasswordResetLinkController.php`
- **Purpose:** Handles password reset link requests.
- **Key methods:** `create()`, `store()`
- **Key actions:** Displays password reset form, sends password reset emails.
- **Notable integrations:** Password reset email functionality.

#### `app/Http/Controllers/Auth/NewPasswordController.php`
- **Purpose:** Handles password reset (setting new password).
- **Key methods:** `create()`, `store()`
- **Key actions:** Displays new password form, validates reset token, updates user password.
- **Notable integrations:** Password reset token validation.

#### `app/Http/Controllers/Auth/VerifyEmailController.php`
- **Purpose:** Handles email verification.
- **Key methods:** `__invoke()`
- **Key actions:** Verifies user email addresses, marks email as verified.
- **Notable integrations:** Email verification process.

#### `app/Http/Controllers/Auth/EmailVerificationPromptController.php`
- **Purpose:** Displays email verification prompt.
- **Key methods:** `__invoke()`
- **Key actions:** Shows email verification notice page.
- **Notable integrations:** Email verification UI.

#### `app/Http/Controllers/Auth/EmailVerificationNotificationController.php`
- **Purpose:** Sends email verification notifications.
- **Key methods:** `store()`
- **Key actions:** Resends email verification links.
- **Notable integrations:** Email verification email sending.

#### `app/Http/Controllers/Auth/PasswordController.php`
- **Purpose:** Handles password updates.
- **Key methods:** `update()`
- **Key actions:** Updates user password after authentication.
- **Notable integrations:** Password update functionality.

#### `app/Http/Controllers/Auth/ConfirmablePasswordController.php`
- **Purpose:** Handles password confirmation for sensitive actions.
- **Key methods:** `show()`, `store()`
- **Key actions:** Displays password confirmation form, confirms password for sensitive operations.
- **Notable integrations:** Password confirmation for sensitive actions.

---

### Payment Controllers

#### `app/Http/Controllers/Payment/SubscriptionController.php`
- **Purpose:** Handles subscription plan purchases and management.
- **Key methods:** `index()`, `plans()`, `current()`, `subscribe()`, `purchase()`, `handlePayment()`, `success()`, `failure()`
- **Key actions:** Displays subscription plans, processes subscription purchases with Xendit, applies discount codes, handles payment webhooks, sends confirmation emails.
- **Notable integrations:** Xendit payment gateway, SubscriptionPlan, Subscription models, Discount model, PaymentIntentService, PaymentConfirmationMail.

#### `app/Http/Controllers/Payment/RefundController.php`
- **Purpose:** Handles refund requests and processing.
- **Key methods:** `index()`, `store()`, `processRefund()`, `approve()`, `reject()`, `getMaxRefundableFromXendit()`, `mapRefundReasonToXendit()`, `tryAlternativeRefundMethods()`, `processSuccessfulRefund()`, `processManualRefund()`, `createRefundNotification()`
- **Key actions:** Lists refund requests, creates refund requests, processes refunds via Xendit, approves/rejects refunds, handles shared ticket refunds, notifies users, updates ticket statuses.
- **Notable integrations:** Xendit refund API, RefundRequest, TicketPurchase, Subscription, TicketLimitPurchase models, NotificationService, event ticket deletion, sold count updates.

#### `app/Http/Controllers/Payment/XenditWebhookController.php`
- **Purpose:** Handles Xendit webhook events for payments, payouts, and refunds.
- **Key methods:** `handle()`, `handleMoneyInTransaction()`, `handleMoneyOutTransaction()`, `handleInvoiceEvent()`, `handleDisbursementEvent()`, `handleRefundEvent()`, `saveWebhookToStorage()`, `determineEventType()`
- **Key actions:** Processes payment webhooks (SUCCESS, PENDING, VOIDED, REVERSED), handles payout webhooks, processes refund webhooks, updates purchase statuses, calculates fees, sends confirmation emails, triggers dashboard updates.
- **Notable integrations:** Xendit webhook validation, TicketPurchase, Subscription, TicketLimitPurchase, EventPayoutRequest, RefundRequest models, XenditFeeCalculator, PaymentIntentService, email notifications, real-time updates.

---

### Support Controllers

#### `app/Http/Controllers/Support/HelpSupportController.php`
- **Purpose:** Handles help and support page display.
- **Key methods:** `index()`, `show()`
- **Key actions:** Displays help/support page, shows support articles or FAQs.
- **Notable integrations:** Support content management.

#### `app/Http/Controllers/Support/SupportTicketController.php`
- **Purpose:** Manages support tickets from users.
- **Key methods:** `index()`, `create()`, `store()`, `show()`, `reply()`, `close()`
- **Key actions:** Lists support tickets, creates new tickets, displays ticket details, handles ticket replies, closes tickets, creates notifications for replies.
- **Notable integrations:** SupportTicket model, NotificationService, ticket reply notifications.

---

### API Controllers

#### `app/Http/Controllers/Api/EventTicketController.php`
- **Purpose:** API endpoints for ticket operations.
- **Key methods:** `loadTicketSettings()`, `getTicketSettings()`
- **Key actions:** Loads ticket settings from R2 storage, provides ticket configuration API.
- **Notable integrations:** R2 storage, JSON configuration management.

#### `app/Http/Controllers/Api/EventTicketShareController.php`
- **Purpose:** API endpoints for ticket sharing functionality.
- **Key methods:** `share()`, `accept()`, `decline()`, `getReceivedShares()`, `logSharing()`
- **Key actions:** Shares tickets with users, handles share acceptance/decline, retrieves received shares, logs sharing activities, validates ticket ownership.
- **Notable integrations:** Creates Notification records for share requests, ticket ownership validation, real-time share status updates.

#### `app/Http/Controllers/Api/UserController.php`
- **Purpose:** API endpoints for user operations.
- **Key methods:** `search()`
- **Key actions:** Searches users by name or email, returns user list for autocomplete/search functionality.
- **Notable integrations:** User model, user search functionality.

---

### Other Controllers

#### `app/Http/Controllers/MyTicketsController.php`
- **Purpose:** Manages user's ticket collection and sharing.
- **Key methods:** `index()`, `getFilteredTickets()`, `getEventTickets()`, `shareTickets()`, `searchUsers()`, `loadTicketSettings()`
- **Key actions:** Displays user's tickets grouped by event, handles ticket sharing, searches users for sharing, loads ticket settings from R2, filters tickets (hide shared, hide past events).
- **Notable integrations:** EventTicketInformation, TicketShare, EventTicketShare models, R2 storage for ticket settings, NotificationService for share requests.

#### `app/Http/Controllers/TicketShareController.php`
- **Purpose:** Handles ticket sharing interface and operations.
- **Key methods:** `show()`, `store()`, `accept()`, `decline()`, `cancel()`, `getAvailableTickets()`
- **Key actions:** Displays ticket sharing interface, creates share requests, accepts/declines shares, cancels shares, retrieves available tickets for sharing.
- **Notable integrations:** TicketShare, EventTicketInformation models, NotificationService, ticket transfer functionality.

#### `app/Http/Controllers/Notifications/NotificationController.php`
- **Purpose:** Manages user notifications.
- **Key methods:** `index()`, `go()`, `markAsRead()`, `markAllAsRead()`
- **Key actions:** Displays notifications list, handles notification navigation, marks notifications as read.
- **Notable integrations:** Notification model, notification routing.

#### `app/Http/Controllers/ProfanityFilterController.php`
- **Purpose:** Provides real-time profanity filtering API.
- **Key methods:** `filter()`
- **Key actions:** Filters profanity from text input, returns filtered text and profanity detection status.
- **Notable integrations:** ProfanityFilterService, real-time text filtering.

#### `app/Http/Controllers/QricketFeedbackController.php`
- **Purpose:** Handles Qricket platform feedback submission.
- **Key methods:** `show()`, `store()`, `getStats()`, `shouldShowModal()`, `dismissModal()`
- **Key actions:** Displays feedback form, stores feedback, provides feedback statistics, determines if feedback modal should be shown, handles modal dismissal.
- **Notable integrations:** QricketFeedback model, feedback statistics, session-based modal tracking.

#### `app/Http/Controllers/DiscountController.php`
- **Purpose:** Validates discount codes via API.
- **Key methods:** `validateDiscount()`
- **Key actions:** Validates discount codes for subscriptions or tickets, checks expiration and usage limits.
- **Notable integrations:** Discount model, discount validation.

#### `app/Http/Controllers/OrganizerProfileController.php`
- **Purpose:** Displays organizer profile pages.
- **Key methods:** `show()`
- **Key actions:** Shows organizer profile with events (organized/staff), social media links, event filtering (all/upcoming/ended).
- **Notable integrations:** User, Event, SocialMediaLink models, organizer event filtering.

#### `app/Http/Controllers/Realtime/RealtimeMetricsController.php`
- **Purpose:** Provides real-time metrics API for dashboards.
- **Key methods:** `metrics()`, `adminDashboard()`, `adminSales()`, `organizerDashboard()`, `eventSales()`
- **Key actions:** Returns real-time metrics for admin dashboard, admin sales, organizer dashboard, and event sales, calculates revenue, payouts, refunds.
- **Notable integrations:** TicketPurchase, Subscription, TicketLimitPurchase, EventPayoutRequest, RefundRequest models, real-time metrics calculation.

---

## Middleware

### Admin Middleware

#### `app/Http/Middleware/Admin/AdminMiddleware.php`
- **Purpose:** Guards admin area with enhanced session handling.
- **Key actions:** Validates admin authentication, handles session management, detects regular web users crossing into admin space, logs out shadow sessions, ensures only authenticated admins proceed.
- **Usage:** Applied to admin routes.

#### `app/Http/Middleware/Admin/PreventUserAdminAccess.php`
- **Purpose:** Blocks regular web users from accessing admin routes.
- **Key actions:** Forcibly logs out non-admins, invalidates sessions, redirects with error message, detects regular web users accessing admin URLs, ensures clear separation between user and admin sessions.
- **Usage:** Applied to admin routes to prevent user access.

#### `app/Http/Middleware/Admin/RequireAdminPermission.php`
- **Purpose:** Checks if admin has required permissions for specific actions.
- **Key actions:** Validates admin permissions (can_manage_users, can_manage_events, etc.), restricts access based on permission flags.
- **Usage:** Applied to admin routes requiring specific permissions.

#### `app/Http/Middleware/CheckAdminSession.php`
- **Purpose:** Validates and manages admin session state.
- **Key actions:** Checks admin session validity, handles session conflicts, manages session cleanup.
- **Usage:** Applied to admin routes for session validation.

---

### Organizer Middleware

#### `app/Http/Middleware/Organizer/OrganizerGuard.php`
- **Purpose:** Ensures user is an organizer or has organizer access.
- **Key actions:** Validates organizer status, checks if user is verified organizer, redirects non-organizers.
- **Usage:** Applied to organizer routes.

#### `app/Http/Middleware/Organizer/VerifiedOrganizerMiddleware.php`
- **Purpose:** Ensures user is a verified organizer.
- **Key actions:** Checks if user has `verified_organizer` flag set, redirects unverified organizers to verification page.
- **Usage:** Applied to organizer routes requiring verification.

#### `app/Http/Middleware/Organizer/OrganizerSideDetector.php`
- **Purpose:** Detects if user is accessing organizer side of the application.
- **Key actions:** Sets session flags for organizer mode, detects organizer context.
- **Usage:** Applied to organizer routes for mode detection.

#### `app/Http/Middleware/Organizer/CheckOrganizerPermission.php`
- **Purpose:** Checks organizer permissions for event management.
- **Key actions:** Validates organizer has permission to manage specific events, checks event ownership or staff membership.
- **Usage:** Applied to event management routes.

---

### Auth Middleware

#### `app/Http/Middleware/Auth/PreventAuthenticatedUsers.php`
- **Purpose:** Prevents authenticated users from accessing guest-only routes.
- **Key actions:** Redirects authenticated users away from guest routes (e.g., login, register).
- **Usage:** Applied to authentication routes (login, register).

#### `app/Http/Middleware/EnsureEmailIsVerified.php`
- **Purpose:** Ensures user's email is verified before accessing protected routes.
- **Key actions:** Checks email verification status, redirects unverified users to verification page.
- **Usage:** Applied to protected routes requiring email verification.

---

### Security Middleware

#### `app/Http/Middleware/Security/VerifyCsrfToken.php`
- **Purpose:** Validates CSRF tokens for form submissions.
- **Key actions:** Verifies CSRF tokens, excludes specific routes from CSRF protection if needed.
- **Usage:** Applied globally to all POST/PUT/DELETE requests.

#### `app/Http/Middleware/Security/VerifyEventAccess.php`
- **Purpose:** Central gatekeeper for event feature permissions.
- **Key actions:** Checks if user has access to event features (manage_event, manage_news, manage_attendance, etc.), treats any assigned staff as having baseline manage permissions, separates `can_manage_news` from `can_manage_event`.
- **Usage:** Applied to event management routes via `event.access:{ability}` middleware alias.

#### `app/Http/Middleware/Security/VerifyRecaptcha.php`
- **Purpose:** Validates reCAPTCHA tokens for form submissions.
- **Key actions:** Verifies reCAPTCHA tokens, blocks submissions with invalid tokens.
- **Usage:** Applied to forms requiring reCAPTCHA validation.

---

### Event Middleware

#### `app/Http/Middleware/TrackEventView.php`
- **Purpose:** Logs event view activity without duplicate entries.
- **Key actions:** Tracks when users view events, prevents duplicate logs for same user/event/day, uses `EventActivityLog::logEventView()` to merge same-day views, only broadcasts real-time updates when a new view is recorded.
- **Usage:** Applied to event show routes.

#### `app/Http/Middleware/TrackEventSave.php`
- **Purpose:** Tracks event save/unsave actions.
- **Key actions:** Logs when users save or unsave events, creates activity logs, triggers Pusher events for real-time updates.
- **Key integrations:** `EventActivityLog`, `ActivityLog`, Pusher broadcasting.
- **Usage:** Applied to `events.save` and `events.unsave` routes.

#### `app/Http/Middleware/TrackTicketPurchase.php`
- **Purpose:** Tracks ticket purchase activity.
- **Key actions:** Logs ticket purchase events, creates activity logs, tracks purchase metrics.
- **Usage:** Applied to ticket purchase routes.

#### `app/Http/Middleware/CheckEventEnded.php`
- **Purpose:** Prevents editing of ended events (currently disabled).
- **Key actions:** Checks if event has ended and blocks edit/update operations.
- **Status:** Temporarily disabled to fix database connection limit issues.
- **Note:** May be re-enabled in the future once redirect issues are resolved.
- **Usage:** Applied to event edit routes (currently disabled).

#### `app/Http/Middleware/CheckEndedEvents.php`
- **Purpose:** Checks for ended events and processes them automatically.
- **Key actions:** Triggers `EventEndNotificationService::processEndedEvents()` to handle event end notifications, uses caching to prevent duplicate processing, queues processing if queue is configured.
- **Usage:** Applied globally to check for ended events on each request (cached per minute).

#### `app/Http/Middleware/AutomaticFeedbackChecker.php`
- **Purpose:** Checks for ended events and sends automatic feedback requests.
- **Key actions:** Triggers `AutomaticFeedbackService::sendFeedbackForEndedEvents()` to send feedback emails to attendees, uses caching to prevent duplicate processing.
- **Usage:** Applied globally to check for ended events needing feedback requests.

---

### System Middleware

#### `app/Http/Middleware/System/SetSessionRoleMiddleware.php`
- **Purpose:** Sets session role based on user authentication state.
- **Key actions:** Sets session role (user/admin/guest) based on authentication, updates session table with role information.
- **Usage:** Applied globally to set session roles.

#### `app/Http/Middleware/System/SessionCleanup.php`
- **Purpose:** Cleans up expired sessions and orphaned session data.
- **Key actions:** Removes expired sessions, cleans up orphaned session records, maintains session table hygiene.
- **Usage:** Applied globally for session cleanup.

#### `app/Http/Middleware/System/SetNavigationVariables.php`
- **Purpose:** Sets navigation variables for views.
- **Key actions:** Provides navigation data to all views, sets common navigation variables.
- **Usage:** Applied globally to set navigation variables.

#### `app/Http/Middleware/System/LogUserActivity.php`
- **Purpose:** Logs user activity for analytics and auditing.
- **Key actions:** Logs user actions, tracks user activity patterns, creates activity log entries.
- **Usage:** Applied to routes requiring activity logging.

#### `app/Http/Middleware/BaseMiddleware.php`
- **Purpose:** Base middleware class with common functionality.
- **Key actions:** Provides common middleware functionality, base class for other middleware.
- **Usage:** Base class for custom middleware.

#### `app/Http/Middleware/ConditionalConsoleLog.php`
- **Purpose:** Conditionally logs to console based on environment.
- **Key actions:** Logs to console in development, skips logging in production.
- **Usage:** Applied for development debugging.

---

### User Middleware

#### `app/Http/Middleware/User/CheckSubscriptionLimits.php`
- **Purpose:** Checks if user has reached subscription limits.
- **Key actions:** Validates subscription limits (events per month, tickets per event), blocks actions exceeding limits, checks subscription status.
- **Usage:** Applied to event creation and ticket management routes.

#### `app/Http/Middleware/CheckUserStatus.php`
- **Purpose:** Ensures users are not banned or deleted at login.
- **Key actions:** Checks user status, auto-unbans users whose ban period expired, redirects banned/deleted accounts to status page, auto-unban functionality, dedicated status page for banned/deleted users.
- **Usage:** Applied to authenticated routes.

#### `app/Http/Middleware/TrackUserActions.php`
- **Purpose:** Tracks user actions for analytics.
- **Key actions:** Logs user actions, tracks user behavior, creates activity log entries.
- **Usage:** Applied to routes requiring user action tracking.

---

## Summary

### Controllers Summary
- **Admin Controllers:** 12 controllers handling dashboard, authentication, user management, event approval, feedback, logs, payouts, reports, subscriptions, notifications, activity logs
- **Organizer Controllers:** 17 controllers handling dashboard, event management (creation, editing, analytics, attendance, certificates, news, payouts), staff management, ticket limits, RBAC
- **User Controllers:** 5 controllers handling profile, mode switching, organizer verification, saved events
- **Event Controllers:** 6 controllers handling public event browsing, ticket purchase, feedback, activity logs
- **Auth Controllers:** 10 controllers handling authentication, registration, password reset, email verification
- **Payment Controllers:** 3 controllers handling subscriptions, refunds, Xendit webhooks
- **Support Controllers:** 2 controllers handling help/support and support tickets
- **API Controllers:** 3 controllers providing API endpoints for tickets, ticket sharing, user search
- **Other Controllers:** 8 controllers handling tickets, notifications, profanity filtering, feedback, discounts, organizer profiles, real-time metrics

**Total: 66 controllers**

### Middleware Summary
- **Admin Middleware:** 4 middleware (AdminMiddleware, PreventUserAdminAccess, RequireAdminPermission, CheckAdminSession)
- **Organizer Middleware:** 4 middleware (OrganizerGuard, VerifiedOrganizerMiddleware, OrganizerSideDetector, CheckOrganizerPermission)
- **Auth Middleware:** 2 middleware (PreventAuthenticatedUsers, EnsureEmailIsVerified)
- **Security Middleware:** 3 middleware (VerifyCsrfToken, VerifyEventAccess, VerifyRecaptcha)
- **Event Middleware:** 6 middleware (TrackEventView, TrackEventSave, TrackTicketPurchase, CheckEventEnded, CheckEndedEvents, AutomaticFeedbackChecker)
- **System Middleware:** 6 middleware (SetSessionRoleMiddleware, SessionCleanup, SetNavigationVariables, LogUserActivity, BaseMiddleware, ConditionalConsoleLog)
- **User Middleware:** 3 middleware (CheckSubscriptionLimits, CheckUserStatus, TrackUserActions)

**Total: 28 middleware**

---

Keep this README up to date as new controllers and middleware are added or refactored.
