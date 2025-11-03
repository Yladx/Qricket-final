# Xendit Payment Bypass Setup

## Configuration

To enable payment bypass for development/testing, add the following to your `.env` file:

```env
# Xendit Payment Configuration
XENDIT_SECRET_KEY=your_xendit_secret_key_here
XENDIT_PUBLIC_KEY=your_xendit_public_key_here
XENDIT_WEBHOOK_TOKEN=your_webhook_token_here
XENDIT_WEBHOOK_SECRET=your_webhook_secret_here
XENDIT_CURRENCY=PHP
XENDIT_BYPASS_PAYMENT=true  # Set to true for development/testing
```

## Features

### Payment Bypass
- When `XENDIT_BYPASS_PAYMENT=true`, payments are automatically marked as "paid"
- No actual Xendit invoice is created
- Purchase status is immediately set to "paid"
- Sold count is updated automatically
- User is redirected to success page

### Ticket Limits
- Loads `max_tickets_per_order` from ticket settings JSON in R2
- Validates purchase quantity against this limit
- Default limit is 10 if not specified in settings

### Refund Policy
- Loads `refund_policy` from ticket settings JSON in R2
- Stores policy information in purchase record
- Available for future refund processing

## Ticket Settings JSON Structure

The ticket settings should be stored in R2 at the path specified in `event.ticket_settings_path`:

```json
{
  "max_tickets_per_order": 5,
  "refund_policy": {
    "allowed": true,
    "deadline_hours": 24,
    "fee_percentage": 10
  }
}
```

## Usage

1. Set `XENDIT_BYPASS_PAYMENT=true` in your `.env` file
2. Clear config cache: `php artisan config:clear`
3. Purchase tickets - they will be automatically marked as paid
4. Check purchase status in the database or user's purchase history

## Security Note

**Never set `XENDIT_BYPASS_PAYMENT=true` in production!** This is only for development and testing purposes.
