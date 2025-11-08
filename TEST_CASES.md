# Test Cases for Qricket Online Ticketing System

## 1. User Registration and Authentication (Objective 1.1)

| ID | Test Case | Expected Result |
|---|---|---|
| LOG-1 | Register with valid data | User created, verification email sent |
| LOG-2 | Register with existing email | Validation error: "Email already registered" |
| LOG-3 | Register with invalid email/weak password | Validation errors shown |
| LOG-4 | Register without accepting terms | Validation error: "Must accept Terms and Conditions" |
| LOG-5 | Login with verified email + correct password | User logged in, redirected to dashboard |
| LOG-6 | Login with unverified email | Auto-logout, verification email sent |
| LOG-7 | Login with incorrect password/non-existent email | Error message shown |
| LOG-8 | Click email verification link | Email verified, redirected to success page |
| LOG-9 | Resend verification email | Verification email sent, success message shown |
| LOG-10 | Login as deleted/banned user | Account status page shown |
| LOG-11 | Password reset request and completion | Reset email sent, password updated |

## 2. Event Creation and Management (Objective 1.2)

| ID | Test Case | Expected Result |
|---|---|---|
| EVT-1 | Create event with all required fields | Event created in draft status |
| EVT-2 | Create event without required fields | Validation errors shown |
| EVT-3 | Create event with invalid dates (past/end before start) | Validation errors shown |
| EVT-4 | Add ticket type with price/quantity | Ticket type added, visible in list |
| EVT-5 | Add ticket type exceeding subscription limit | Error: "Ticket limit exceeded for your plan" |
| EVT-6 | Create event with profanity | Validation error: "Contains inappropriate language" |
| EVT-7 | Submit event for approval | Status changes to "pending_approval", cannot edit |
| EVT-8 | Edit event in draft/pending status | Draft: changes saved, Pending: error shown |
| EVT-9 | Delete event (with/without sales) | No sales: deleted, With sales: error shown |
| EVT-10 | Add/remove/reorder layout components | Components updated, preview reflects changes |
| EVT-11 | Upload event poster (valid/invalid format) | Valid: uploaded, Invalid: validation error |
| EVT-12 | Preview and publish event | Preview shows components, published event visible to public |

## 3. Ticket Booking and Payment (Objective 1.3)

| ID | Test Case | Expected Result |
|---|---|---|
| TKT-1 | Browse events with filters | Events list displayed with category/date/location filters |
| TKT-2 | View event details | Event info, ticket types, booking button visible |
| TKT-3 | Select ticket type and quantity | Ticket added to cart, price calculated |
| TKT-4 | Select quantity exceeding available/sold-out tickets | Error: "Only X available" or "Sold out" |
| TKT-5 | Add/remove multiple ticket types | All tickets in cart, total calculated correctly |
| TKT-6 | Complete payment (success/failure) | Success: tickets issued, email sent. Failure: error shown |
| TKT-7 | Purchase tickets for free event | Tickets issued immediately without payment |
| TKT-8 | Purchase additional tickets for free event | Additional tickets purchased, limit increased |
| TKT-9 | View/download/share tickets | Tickets listed with QR codes, PDF downloadable, shareable |
| TKT-10 | Purchase tickets for past/ended event | Error: "Event has ended" or "Sales ended" |

## 4. Mobile Responsiveness (Objective 1.4)

| ID | Test Case | Expected Result |
|---|---|---|
| MOB-1 | Access event browsing/purchase on mobile | Events displayed responsively, purchase process works |
| MOB-2 | View tickets/scan QR code on mobile | Ticket list scrollable, QR codes visible and scannable |
| MOB-3 | Access organizer dashboard/create event on mobile | Dashboard responsive, event creation form usable |
| MOB-4 | Check-in attendees/view analytics on mobile | Check-in interface works, analytics readable |

## 5. Certificate Generation (Objective 1.5)

| ID | Test Case | Expected Result |
|---|---|---|
| CRT-1 | Enable/configure certificate | Certificate settings saved, preview available |
| CRT-2 | Add/remove signatories (up to 4) | Signatories added/removed, preview updated |
| CRT-3 | Upload signature image | Signature displayed in certificate preview |
| CRT-4 | Generate certificate for checked-in attendee | Certificate generated, accessible via public URL |
| CRT-5 | View/download certificate | Certificate displayed, PDF downloadable |
| CRT-6 | Generate certificate for non-checked-in attendee | Error: "Must be checked in to receive certificate" |

## 6. QR Code Authentication (Objective 2.1-2.4)

| ID | Test Case | Expected Result |
|---|---|---|
| QR-1 | Generate QR code for ticket | Unique QR code generated, contains encrypted ticket info |
| QR-2 | Scan valid QR code at event entry | Ticket validated, attendee checked in |
| QR-3 | Scan already checked-in/invalid QR code | Error: "Already checked in" or "Invalid ticket" |
| QR-4 | Scan QR code for different event | Error: "Ticket not valid for this event" |
| QR-5 | Manual check-in by ticket number | Attendee checked in, attendance recorded |
| QR-6 | View/export real-time attendance list | Checked-in attendees displayed, exportable as CSV/Excel |
| QR-7 | Filter/search attendance | Attendance filtered by type, searchable |
| QR-8 | View attendance statistics/real-time updates | Statistics displayed, dashboard updates automatically |

## 7. Event Analytics Dashboard (Objective 3.1-3.4)

| ID | Test Case | Expected Result |
|---|---|---|
| ANA-1 | View real-time ticket sales/revenue | Sales data displayed, updates automatically |
| ANA-2 | View sales by type/payment method | Breakdown shown for each type/method |
| ANA-3 | View attendee demographics | Age, gender, location data displayed (if available) |
| ANA-4 | View sales trend/performance metrics | Charts show trends, metrics displayed |
| ANA-5 | View attendance analytics | Check-in rate, peak times displayed |
| ANA-6 | View post-event report | Complete report with all analytics data |
| ANA-7 | Export analytics report (CSV/Excel) | Report exported with all data |
| ANA-8 | Filter analytics by date range | Analytics filtered, only selected range shown |
| ANA-9 | Access analytics as staff/view advanced analytics | Analytics visible based on permissions/plan |

## 8. Notifications and Customer Support (Objective 4.1-4.4)

| ID | Test Case | Expected Result |
|---|---|---|
| NOT-1 | Receive ticket confirmation/reminder emails | Emails sent with ticket details/reminders |
| NOT-2 | Receive event update/cancellation notifications | Notifications sent when event changes/cancels |
| NOT-3 | View/manage in-app notifications | Notifications displayed, mark read/delete works |
| NOT-4 | Receive real-time notifications (Pusher) | Notifications appear instantly without refresh |
| NOT-5 | Create/view/reply to support ticket | Ticket created, status visible, replies sent |
| NOT-6 | Request refund (eligible/ineligible) | Eligible: request created, Ineligible: error shown |
| NOT-7 | Admin processes refund | Refund processed, user notified, ticket invalidated |
| NOT-8 | Submit/view event feedback | Feedback submitted, organizer can view |
| NOT-9 | View detailed feedback (Advanced Analytics) | Detailed analytics visible for eligible plans |
| NOT-10 | Receive news article notification | Notification sent when event news published |

## 9. Additional Features

| ID | Test Case | Expected Result |
|---|---|---|
| EXT-1 | Save/remove/view saved events | Events saved, removed, displayed in saved list |
| EXT-2 | Organizer verification (submit/approve/reject) | Request submitted, admin approves/rejects |
| EXT-3 | Request/process payout | Payout request created, admin processes |
| EXT-4 | View/purchase subscription | Plans displayed, subscription purchased, features activated |
| EXT-5 | Event staff access with permissions | Staff can only access allowed features |
| EXT-6 | Search/filter/sort events | Events searchable, filterable, sortable |
| EXT-7 | Dark mode/session timeout | Theme switches, user logged out after inactivity |

## 10. Security and Performance

| ID | Test Case | Expected Result |
|---|---|---|
| SEC-1 | SQL injection/XSS/CSRF attacks | Input sanitized, scripts escaped, tokens validated |
| SEC-2 | Unauthorized access to routes | Access denied, redirect to login or 403 error |
| SEC-3 | Rate limiting/file upload limits | Too many requests blocked, large files rejected |
| SEC-4 | Concurrent ticket purchases | Race condition handled, no overselling |
| SEC-5 | Payment gateway/database failures | Graceful error handling, system remains stable |
| PERF-1 | Load test: 100-1000 concurrent users | System handles load, response time acceptable |
| PERF-2 | Large event with 10,000+ tickets | Event page loads, browsing works |
| PERF-3 | Real-time updates with 100+ users | Updates delivered promptly, no lag |
