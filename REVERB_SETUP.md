# Laravel Reverb Setup Guide

This project has been configured to use Laravel Reverb for real-time notifications instead of the previous Socket.io implementation.

## Environment Configuration

Add these variables to your `.env` file:

```env
# Broadcasting Configuration
BROADCAST_DRIVER=reverb

# Laravel Reverb Configuration
REVERB_APP_ID=local
REVERB_APP_KEY=local
REVERB_APP_SECRET=local
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
```

## Starting the Reverb Server

To start the Laravel Reverb server, run:

```bash
php artisan reverb:start
```

The server will start on `http://127.0.0.1:8080` by default.

## Usage

### Sending Notifications

Use the `NotificationService` class to send notifications:

```php
use App\Services\NotificationService;

// Send a general notification to all users
NotificationService::sendGeneralNotification('Welcome to our platform!', 'info', 'Welcome');

// Send a notification to a specific user
NotificationService::sendUserNotification($userId, 'Your subscription has been activated!', 'success', 'Subscription Active');

// Use convenience methods
NotificationService::success('Operation completed successfully!', $userId, 'Success');
NotificationService::error('Something went wrong!', $userId, 'Error');
NotificationService::warning('Please check your input!', $userId, 'Warning');
NotificationService::info('Here is some information!', $userId, 'Information');
```

### Frontend Integration

The notification system is automatically initialized when the page loads. It will:

1. Connect to the Reverb server
2. Subscribe to general notifications channel
3. Subscribe to user-specific private channels (if authenticated)
4. Display notifications with proper styling and animations

### Testing

Visit `/test-notification` (when authenticated) to send a test notification.

## Events

The following events are available:

- `NotificationSent`: For general notifications
- `UserNotification`: For user-specific notifications

## Channels

- `notifications`: Public channel for general notifications
- `private-user.{userId}`: Private channel for user-specific notifications

## Frontend Configuration

The Pusher configuration is automatically set in the layout file with these variables:
- `window.PUSHER_APP_KEY`
- `window.PUSHER_APP_CLUSTER`
- `window.PUSHER_PORT`

## Production Considerations

1. Update the Reverb configuration for production (use HTTPS, proper host, etc.)
2. Remove the test notification route
3. Configure proper authentication for private channels
4. Set up proper error handling and reconnection logic
5. Consider using Redis for scaling across multiple servers


