# Event Creation and Management Database Schema

This document outlines all database tables involved in the event creation and management process.

## Core Tables

### 1. `events` - Main Event Table
**Purpose**: Stores the main event information and metadata.

**Key Columns**:
- `id` - 10-digit unique identifier (non-auto-incrementing, randomized)
- `user_id` - Creator user ID (nullable, foreign key to `users`)
- `subscription_id` - Subscription snapshot when event was created (nullable, foreign key to `subscriptions`)
- `title` - Event title
- `is_free` - Boolean flag if event is free (all tickets forced to price 0)
- `description` - Long-form event description
- `category` - Event category/tag
- `start_date` - Event start datetime
- `end_date` - Event end datetime (also used to force ticket sale_end)
- `location` - Venue/location string
- `location_url` - Optional map/location URL
- `tags` - Comma-separated keywords
- `poster` - R2/local storage path to poster image
- `thumbnail` - R2/local storage path to thumbnail image
- `layout_path` - R2 path to custom layout JSON
- `ticket_settings_path` - R2 path to ticket settings JSON
- `status` - Enum: `draft`, `pending_approval`, `approved`, `rejected`, `scheduled`, `published`
- `scheduled_publish_at` - Optional datetime to auto-publish later
- `certificate_enabled` - Boolean flag for certificate feature
- `feedback_emails_sent_at` - Timestamp when feedback emails were sent
- `end_notified_at` - Timestamp when end-of-event notifications were sent
- `feedback_notified_at` - Timestamp when feedback notifications were sent
- `created_at`, `updated_at` - Timestamps

**Indexes**:
- `status`, `start_date`
- `category`
- `start_date`
- `user_id`
- `user_id`, `status`
- `subscription_id`

---

### 2. `event_tickets` - Ticket Types Configuration
**Purpose**: Stores ticket type configurations (price, quantity, sale dates) for each event.

**Key Columns**:
- `id` - Primary key
- `event_id` - Foreign key to `events` (cascade delete)
- `name` - Ticket type name (e.g., "General Admission", "VIP")
- `description` - Short customer-facing description
- `price` - Decimal(10,2) unit price (0 for free tickets)
- `quantity` - Total inventory for this ticket type (nullable)
- `sold_count` - Number already sold (prevents deletion/downsizing)
- `sale_start` - When this ticket becomes available (nullable)
- `sale_end` - When this ticket stops selling (forced to match event end_date)
- `is_active` - Boolean flag if ticket type is active and available
- `sort_order` - Display order for ticket types
- `created_at`, `updated_at` - Timestamps

**Indexes**:
- `event_id`, `is_active`
- `event_id`, `sort_order`

**Relationships**:
- `belongsTo` Event
- `hasMany` EventTicketInformation (individual ticket instances)

---

### 3. `event_staff` - Event Staff/Organizers
**Purpose**: Manages staff members and their permissions for each event.

**Key Columns**:
- `id` - Primary key
- `event_id` - Foreign key to `events` (cascade delete)
- `user_id` - Foreign key to `users` (cascade delete)
- `role` - Staff role (e.g., "organizer", "staff", "manager")
- `can_manage_event` - Boolean permission
- `can_manage_staff` - Boolean permission
- `can_manage_ticket` - Boolean permission
- `can_manage_news` - Boolean permission
- `can_manage_attendance` - Boolean permission
- `can_manage_sales` - Boolean permission (controls access to earnings/revenue)
- `can_edit_event_info` - Boolean permission
- `created_at`, `updated_at` - Timestamps

**Indexes**:
- `event_id`, `user_id` (unique)
- `event_id`

**Note**: The owner (creator) is automatically added as staff with full permissions.

---

### 4. `event_verification_requests` - Event Approval/Verification
**Purpose**: Tracks event approval/verification workflow (replaces approval columns in events table).

**Key Columns**:
- `id` - Primary key
- `event_id` - Foreign key to `events` (cascade delete)
- `organizer_id` - Foreign key to `users` (set null on delete)
- `status` - Enum: `pending`, `approved`, `rejected`, `cancelled`
- `notes` - Admin review notes
- `reviewed_by` - Admin ID who reviewed (nullable)
- `reviewed_at` - Timestamp when reviewed
- `submitted_at` - Timestamp when submitted for approval
- `created_at`, `updated_at` - Timestamps

**Indexes**:
- `event_id`
- `organizer_id`
- `status`

---

## Ticket Sales and Purchase Tables

### 5. `ticket_purchases` - Purchase Transactions
**Purpose**: Records each ticket purchase transaction.

**Key Columns**:
- `id` - Primary key
- `user_id` - Buyer user ID (foreign key to `users`, cascade delete)
- `event_id` - Foreign key to `events` (cascade delete)
- `ticket_type_id` - Foreign key to `event_tickets` (cascade delete)
- `purchase_id` - Unique identifier for this purchase (string, unique)
- `quantity` - Number of tickets purchased
- `unit_price` - Decimal(10,2) price per ticket
- `total_amount` - Decimal(10,2) total purchase amount
- `transaction_fee` - Decimal(10,2) payment gateway fees
- `vat_amount` - Decimal(10,2) VAT/tax amount
- `net_amount` - Decimal(10,2) net amount after fees (for revenue calculation)
- `total_fees` - Decimal(10,2) sum of all fees
- `currency` - Currency code (default: PHP)
- `status` - Enum: `pending`, `paid`, `failed`, `expired`, `refunded`
- `payment_method` - Payment method used (nullable)
- `xendit_payment_id` - Xendit payment ID (nullable)
- `xendit_invoice_id` - Xendit invoice ID (nullable)
- `payment_details` - JSON payment response from Xendit
- `customer_details` - JSON customer information
- `expires_at` - Payment expiration timestamp (nullable)
- `paid_at` - Payment completion timestamp (nullable)
- `created_at`, `updated_at` - Timestamps

**Indexes**:
- `user_id`, `status`
- `event_id`, `status`
- `status`, `created_at`
- `purchase_id`
- `xendit_payment_id`
- `xendit_invoice_id`

**Relationships**:
- `belongsTo` User (buyer)
- `belongsTo` Event
- `belongsTo` EventTicket (ticket type)
- `hasMany` EventTicketInformation (individual ticket instances)

---

### 6. `event_ticket_information` - Individual Ticket Instances
**Purpose**: Stores individual ticket instances with QR codes (one row per ticket purchased).

**Key Columns**:
- `id` - Primary key
- `ticket_id` - Unique ticket identifier (string, 50 chars, unique)
- `user_id` - Ticket owner user ID (foreign key to `users`, cascade delete)
- `event_id` - Foreign key to `events` (cascade delete)
- `ticket_type_id` - Foreign key to `event_tickets` (cascade delete)
- `ticket_purchase_id` - Foreign key to `ticket_purchases` (cascade delete)
- `shared_at` - Timestamp when ticket was shared (nullable)
- `shared_by_user_id` - User who shared the ticket (nullable, foreign key to `users`)
- `qr_code_data` - Encrypted QR code data (text, nullable)
- `checked_in_at` - Timestamp when ticket was checked in (nullable)
- `certificate_given` - Boolean flag if certificate was issued
- `created_at`, `updated_at` - Timestamps

**Indexes**:
- `user_id`, `event_id`
- `ticket_purchase_id`
- `ticket_type_id`, `user_id`
- `ticket_type_id`, `event_id`
- `shared_by_user_id`, `event_id`
- `shared_at`

**Relationships**:
- `belongsTo` User (owner)
- `belongsTo` Event
- `belongsTo` EventTicket (ticket type)
- `belongsTo` TicketPurchase

**Note**: When a user purchases 3 tickets, 3 rows are created here (one per ticket).

---

## Supporting Tables

### 7. `event_news` - Event News/Announcements
**Purpose**: Stores news posts and announcements for events.

**Key Columns**:
- `id` - Primary key
- `event_id` - Foreign key to `events` (cascade delete)
- `user_id` - Author user ID (foreign key to `users`, cascade delete)
- `title` - News title
- `body` - News content (text)
- `attachments` - JSON array of attachment URLs (nullable)
- `visibility` - Enum: `public`, `ticket_buyers`, `saved_users`
- `published_at` - Timestamp when published (nullable)
- `created_at`, `updated_at` - Timestamps

**Indexes**:
- `event_id`, `published_at`

---

### 8. `event_activity_logs` - Event Activity Tracking
**Purpose**: Logs all activities related to events (views, saves, purchases, etc.).

**Key Columns**:
- `id` - Primary key
- `event_id` - Foreign key to `events` (null on delete)
- `user_id` - User who performed the action (nullable, set null on delete)
- `action` - Action type (string)
- `description` - Action description (string)
- `metadata` - JSON additional data (nullable)
- `ip_address` - User IP address (nullable)
- `user_agent` - User agent string (nullable)
- `created_at`, `updated_at` - Timestamps

**Indexes**:
- `event_id`, `created_at`
- `user_id`, `created_at`

---

### 9. `event_feedback` - Event Feedback/Reviews
**Purpose**: Stores user feedback and ratings for events.

**Key Columns**:
- `id` - Primary key
- `event_id` - Foreign key to `events` (cascade delete)
- `user_id` - User who submitted feedback (foreign key to `users`, cascade delete)
- `responses` - JSON feedback responses
- `rating` - Integer rating (nullable)
- `comments` - Text comments (nullable)
- `submitted_at` - Timestamp when submitted (nullable)
- `created_at`, `updated_at` - Timestamps

**Indexes**:
- `event_id`, `user_id` (unique)

---

### 10. `saved_events` - User Saved Events
**Purpose**: Tracks which events users have saved/bookmarked.

**Key Columns**:
- `id` - Primary key
- `user_id` - Foreign key to `users` (cascade delete)
- `event_id` - Foreign key to `events` (cascade delete)
- `saved_at` - Timestamp when saved (nullable)
- `created_at`, `updated_at` - Timestamps

**Indexes**:
- `user_id`, `event_id` (unique)
- `user_id`

---

## Financial Tables

### 11. `event_payout_requests` - Payout Requests
**Purpose**: Tracks organizer requests for payouts after events end.

**Key Columns**:
- `id` - Primary key
- `event_id` - Foreign key to `events` (cascade delete)
- `organizer_id` - Foreign key to `users` (cascade delete)
- `total_earnings` - Decimal(10,2) total earnings before fees
- `qricket_fee` - Decimal(10,2) platform fee deducted
- `net_amount` - Decimal(10,2) amount to be paid out
- `status` - Enum: `pending`, `approved`, `rejected`, `released`, `paid`
- `notes` - Admin/organizer notes (nullable)
- `requested_at` - Timestamp when requested
- `processed_at` - Timestamp when processed (nullable)
- `created_at`, `updated_at` - Timestamps

**Note**: Only shown for events with earnings > 0 and not free events.

---

### 12. `event_payouts` - Legacy Payout Table (Deprecated)
**Purpose**: Legacy payout table (may still exist but not actively used).

**Key Columns**:
- Similar to `event_payout_requests` but with different structure
- Uses `gross_amount`, `platform_fee`, `net_amount`
- Includes Xendit disbursement fields

---

## Related Tables (Referenced but Not Directly Event Creation)

### 13. `ticket_limit_purchases` - Additional Ticket Limit Purchases
**Purpose**: Tracks purchases of additional ticket limits when organizer exceeds subscription limit.

**Key Columns**:
- `id` - Primary key
- `user_id` - User who purchased
- `event_id` - Foreign key to `events`
- `ticket_limit_pricing_id` - Foreign key to `ticket_limit_pricing`
- `additional_tickets` - Number of additional tickets purchased
- `amount`, `currency` - Purchase amount
- `status` - Payment status
- `payment_method`, `payment_reference` - Payment details
- `xendit_invoice_id` - Xendit invoice ID
- `net_amount`, `transaction_fee`, `vat_amount`, `total_fees` - Fee breakdown
- `paid_at` - Payment timestamp
- `created_at`, `updated_at` - Timestamps

---

### 14. `ticket_limit_pricing` - Additional Ticket Pricing Plans
**Purpose**: Defines pricing for purchasing additional ticket limits.

**Key Columns**:
- `id` - Primary key
- `ticket_quantity` - Number of tickets in this plan
- `type` - Enum: `free`, `paid` (event type)
- `price` - Decimal price for this plan
- `currency` - Currency code
- `is_active` - Boolean flag
- `created_at`, `updated_at` - Timestamps

**Indexes**:
- `ticket_quantity`, `type` (unique composite)

---

## Event Creation Flow

### Step 1: Basic Information
- Creates row in `events` table with `status='draft'`
- Sets `user_id` to creator
- Sets `subscription_id` to current subscription
- Stores basic info: title, description, category, dates, location

### Step 2: Layout/Customization
- Updates `events.layout_path` with R2 path to layout JSON
- Layout JSON stored in R2 storage (not in database)

### Step 3: Ticket Configuration
- Creates rows in `event_tickets` table for each ticket type
- Each ticket type has: name, price, quantity, sale dates
- If event is free (`is_free=true`), all prices forced to 0

### Step 4: Staff Management
- Creates rows in `event_staff` table
- Owner automatically added with full permissions
- Additional staff added with specific permissions

### Step 5: Review & Submit
- Updates `events.status` to `pending_approval`
- Creates row in `event_verification_requests` with `status='pending'`

### Step 6: Approval (Admin)
- Admin reviews and updates `event_verification_requests.status`
- If approved: `events.status` = `approved` or `published`
- If rejected: `events.status` = `rejected`

---

## Ticket Purchase Flow

### When User Purchases Tickets:
1. **ticket_purchases** table:
   - Creates one row per purchase transaction
   - Stores payment details, fees, net amount
   - Status: `pending` → `paid` after payment

2. **event_ticket_information** table:
   - Creates N rows (one per ticket purchased)
   - Each row has unique `ticket_id` and encrypted QR code
   - Links to `ticket_purchases` via `ticket_purchase_id`

3. **event_tickets** table:
   - Updates `sold_count` for the purchased ticket type
   - Used to prevent deleting ticket types with sales

---

## Revenue Calculation Flow

### For Free Events (`is_free = true`):
- All ticket prices are 0
- `expected_revenue` = 0
- `expected_payout` = null (no payout requests shown)

### For Paid Events:
1. **Total Net Amount** = Sum of `net_amount` from `ticket_purchases` (status='paid')
2. **Qricket Fee** = Total Net Amount × 5%
3. **Expected Revenue** = max(0, Total Net Amount - Qricket Fee)
4. **Expected Payout** = Expected Revenue (if event ended and revenue > 0)

---

## Key Relationships Summary

```
events
├── user (creator)
├── subscription (snapshot)
├── event_staff (many)
│   └── user (staff member)
├── event_tickets (many)
│   ├── event_ticket_information (many)
│   └── ticket_purchases (many)
│       └── event_ticket_information (many)
├── event_news (many)
├── event_activity_logs (many)
├── event_feedback (many)
├── saved_events (many)
├── event_verification_requests (many)
└── event_payout_requests (many)
```

---

## Important Notes

1. **Event ID**: Uses 10-digit randomized ID (not auto-increment) for security
2. **Ticket Deletion**: Cannot delete ticket types if `sold_count > 0`
3. **Ticket Quantity**: Cannot set quantity below `sold_count`
4. **Sale End Date**: Always matches `event.end_date` (enforced)
5. **Free Events**: All ticket prices forced to 0 when `is_free = true`
6. **Revenue**: Clamped to 0 (never negative) for free events or zero-earning events
7. **Payout Requests**: Only shown for paid events with earnings > 0
8. **Staff Permissions**: `can_manage_sales` controls access to earnings/revenue data
9. **Layout Data**: Stored in R2 storage, referenced by `layout_path` in events table
10. **Ticket Settings**: Stored in R2 storage (JSON), referenced by `ticket_settings_path`

