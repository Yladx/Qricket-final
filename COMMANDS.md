# Qricket Console Commands Reference

This document lists all available Artisan console commands in the Qricket application.

## 📋 Contents
- [Authentication & Security](#authentication--security)
- [Testing Commands](#testing-commands)
- [Database & Migration](#database--migration)
- [Session Management](#session-management)
- [Email & Communication](#email--communication)
- [Xendit Integration](#xendit-integration)
- [Event Management](#event-management)
- [Subscription Management](#subscription-management)
- [System Maintenance](#system-maintenance)

---

## 🔐 Authentication & Security

### Admin Security

#### `php artisan test:admin-login {username} {password}`
Test admin login functionality
- **Arguments**: 
  - `username` - Admin username
  - `password` - Admin password
- **Purpose**: Test admin authentication flow
- **Use Case**: Debug admin login issues

#### `php artisan test:admin-lockout {username}`
Test admin lockout functionality
- **Arguments**: 
  - `username` - Admin username to test
- **Purpose**: Test account lockout after failed attempts
- **Use Case**: Verify lockout mechanism after 3 failed attempts

#### `php artisan test:admin-independence`
Verify admin authentication works independently
- **Purpose**: Test that admin doesn't need user authentication
- **Use Case**: Verify admin system independence

#### `php artisan clear:admin-lockout {username}`
Clear admin lockout status
- **Arguments**: 
  - `username` - Admin username to unlock
- **Purpose**: Remove lockout from admin account
- **Use Case**: Manually unlock locked admin accounts

---

## 🧪 Testing Commands

### Admin Testing

#### `php artisan test:admin-dual-auth`
Test admin dual authentication (admin + user guards)
- **Purpose**: Verify admin can access both admin and user features
- **Use Case**: Test automatic shadow user creation

#### `php artisan test:admin-login-flow`
Test complete admin login flow
- **Purpose**: Test full admin login process including shadow user creation
- **Use Case**: Debug admin login issues

#### `php artisan test:admin-user-access`
Test admin access to user functionality
- **Purpose**: Verify admin can access user features
- **Use Case**: Test admin functionality without separate login

#### `php artisan test:logout-notification {admin_id} {reason}`
Test admin logout notifications via Pusher
- **Arguments**:
  - `admin_id` - Admin ID to test
  - `reason` - Logout reason (default: session_conflict)
- **Purpose**: Test real-time logout notifications
- **Use Case**: Debug Pusher integration for logout events

### User Testing

#### `php artisan test:user-logout-notification {user_id} {reason}`
Test user logout notifications via Pusher
- **Arguments**:
  - `user_id` - User ID to test
  - `reason` - Logout reason (default: session_conflict)
- **Purpose**: Test real-time user logout notifications
- **Use Case**: Debug Pusher integration for user logout events

### Certificate Testing

#### `php artisan test:certificate-data {event_id}`
Test certificate data loading for an event
- **Arguments**:
  - `event_id` - Event ID to test
- **Purpose**: Check certificate data structure and files
- **Use Case**: Debug certificate generation and loading

### Email Testing

#### `php artisan test:all-emails`
Test all email templates in the system
- **Purpose**: Test all email templates for formatting
- **Use Case**: Verify email templates work correctly

### Payment Testing

#### `php artisan test:xendit-api`
Test Xendit API connectivity and basic functionality
- **Purpose**: Check Xendit API connection
- **Use Case**: Debug Xendit integration issues

#### `php artisan test:purchase {user_id} {ticket_id} {quantity}`
Test ticket purchase flow
- **Arguments**:
  - `user_id` - User ID
  - `ticket_id` - Ticket type ID
  - `quantity` - Quantity to purchase
- **Purpose**: Test ticket purchase functionality
- **Use Case**: Debug payment processing

#### `php artisan test:qr-encryption`
Test QR code encryption and decryption
- **Purpose**: Verify QR code security features
- **Use Case**: Debug QR code issues

---

## 💾 Database & Migration

### Migration Commands

```bash
# Create a new migration
php artisan make:migration create_XXXX_table

# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Rollback all migrations
php artisan migrate:reset

# Rollback and re-run migrations
php artisan migrate:refresh

# Rollback, re-run, and seed
php artisan migrate:fresh --seed

# Check migration status
php artisan migrate:status
```

### Database Commands

```bash
# Show database connection info
php artisan db:show

# Monitor database queries
php artisan db:monitor

# Show database table info
php artisan db:table users

# Wipe database
php artisan db:wipe

# Seed database
php artisan db:seed

# Seed specific seeder
php artisan db:seed --class=UserSeeder
```

---

## 🔄 Session Management

#### `php artisan session:lifetime`
Check and display current session lifetime configuration
- **Purpose**: Show current session configuration
- **Use Case**: Debug session expiry issues

#### `php artisan debug:session`
Debug session data and conflicts
- **Purpose**: Inspect session data
- **Use Case**: Debug session conflict issues

#### `php artisan check:session-data`
Check session data structure
- **Purpose**: Inspect session database records
- **Use Case**: Debug session issues

#### `php artisan cleanup:orphaned-admin-sessions`
Clean up orphaned admin sessions from database
- **Purpose**: Remove invalid admin sessions
- **Use Case**: Maintenance task for session cleanup

---

## 📧 Email & Communication

#### `php artisan email:send-invoice {user_id} {subscription_id}`
Send invoice email to user
- **Arguments**:
  - `user_id` - User ID
  - `subscription_id` - Subscription ID
- **Purpose**: Send invoice email
- **Use Case**: Test invoice email functionality

#### `php artisan email:send-payment {user_id} {subscription_id}`
Send payment confirmation email to user
- **Arguments**:
  - `user_id` - User ID
  - `subscription_id` - Subscription ID
- **Purpose**: Send payment confirmation email
- **Use Case**: Test payment email functionality

#### `php artisan events:send-feedback`
Send feedback emails for recently ended events
- **Purpose**: Send feedback requests to event attendees
- **Use Case**: Automated feedback collection

#### `php artisan debug:logs {--tail=50}`
View debug logs filtered by current admin
- **Options**:
  - `--tail=50` - Number of lines to show (default: 50)
- **Purpose**: View debug logs in terminal
- **Use Case**: Debug system monitoring

---

## 💳 Xendit Integration

#### `php artisan xendit:check-account`
Check Xendit account status and available APIs
- **Purpose**: Verify Xendit connection and features
- **Use Case**: Debug Xendit integration

#### `php artisan xendit:status`
Check Xendit integration status and next steps
- **Purpose**: Show Xendit configuration status
- **Use Case**: Verify Xendit setup

#### `php artisan xendit:invoice-data {invoice_id}`
View invoice data from Xendit
- **Arguments**:
  - `invoice_id` - Xendit invoice ID
- **Purpose**: Fetch and display invoice details
- **Use Case**: Debug invoice issues

---

## 🎟️ Event Management

### Event Testing

#### `php artisan test:disbursement`
Test disbursement functionality with detailed logging
- **Purpose**: Test payout functionality
- **Use Case**: Debug payout issues

#### `php artisan test:disbursement-simple`
Test disbursement in simple format
- **Purpose**: Simplified payout testing
- **Use Case**: Quick payout testing

#### `php artisan test:refund-simple`
Test refund functionality with detailed logging
- **Purpose**: Test refund processing
- **Use Case**: Debug refund issues

---

## 💰 Subscription Management

#### `php artisan subscription:test {user_id} {plan_id}`
Test subscription creation with Xendit API
- **Arguments**:
  - `user_id` - User ID
  - `plan_id` - Subscription plan ID
- **Purpose**: Test subscription flow
- **Use Case**: Debug subscription creation

#### `php artisan fix:pending-subscriptions`
Fix pending subscriptions in the system
- **Purpose**: Clean up stuck subscriptions
- **Use Case**: Maintenance task for subscriptions

---

## 🛠️ System Maintenance

### Cache Management

```bash
# Clear all caches
php artisan cache:clear

# Forget specific cache entry
php artisan cache:forget KEY

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear
```

### Configuration

```bash
# Cache configuration for faster loading
php artisan config:cache

# Clear configuration cache
php artisan config:clear

# Show configuration values
php artisan config:show config.session
```

### Queue Management

```bash
# Run queue worker
php artisan queue:work

# Listen for jobs
php artisan queue:listen

# Retry failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush

# View failed jobs
php artisan queue:failed
```

### Task Scheduling

```bash
# Run scheduled tasks
php artisan schedule:run

# Run specific scheduled command
php artisan schedule:test

# List scheduled tasks
php artisan schedule:list
```

---

## 📚 Laravel Framework Commands

### Application Setup

```bash
# Generate application key
php artisan key:generate

# Link storage
php artisan storage:link

# Publish vendor assets
php artisan vendor:publish
```

### Code Generation

```bash
# Make controller
php artisan make:controller ControllerName

# Make model
php artisan make:model ModelName

# Make migration
php artisan make:migration migration_name

# Make seeder
php artisan make:seeder SeederName

# Make middleware
php artisan make:middleware MiddlewareName
```

---

## 🎯 Quick Reference

### Most Used Commands

| Command | Purpose |
|---------|---------|
| `php artisan session:lifetime` | Check session configuration |
| `php artisan test:admin-login admin admin123` | Test admin login |
| `php artisan test:logout-notification 1` | Test logout notifications |
| `php artisan test:certificate-data 2` | Test certificate data |
| `php artisan xendit:check-account` | Check Xendit status |
| `php artisan cache:clear` | Clear all caches |
| `php artisan migrate` | Run migrations |
| `php artisan db:seed` | Seed database |

---

## 📝 Notes

- All custom commands are located in `app/Console/Commands/`
- Test commands are prefixed with `test:` for easy identification
- Debug commands help troubleshoot specific issues
- Maintenance commands should be run periodically

---

## 🆘 Getting Help

For any command, use the `--help` flag to see detailed information:

```bash
php artisan command-name --help
```

Example:
```bash
php artisan test:admin-login --help
```
