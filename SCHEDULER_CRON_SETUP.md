# Laravel Scheduler Cron Job Setup

## How to Set Up the Cron Job on Your Hosting Environment

### Step 1: Add Cron Job on Your Hosting Provider

Add this line to your crontab (run `crontab -e` in your hosting panel's terminal):

```bash
* * * * * cd /path/to/qricket && php artisan schedule:run >> /dev/null 2>&1
```

Replace `/path/to/qricket` with your actual project path.

### Step 2: Verify It's Running

After adding the cron job, check your Laravel logs to see if it's working:

```bash
tail -f storage/logs/laravel.log
```

You should see entries like:
- `EventEndNotificationService: Started`
- `EventEndNotificationService: Found ended events`
- etc.

### Step 3: Test Manually First

Before setting up the cron job, test if it works manually:

```bash
php artisan schedule:run
```

This will manually trigger all scheduled tasks.

### Alternative: Test the Event End Service Directly

You can also test the service directly:

```bash
php artisan test:event-end-notifications
```

This will show you exactly what's happening with the event end notifications.

## What Gets Run Every Minute

1. **Event End Notifications**: Checks for ended events and sends notifications
2. **Feedback Requests**: (Disabled in current version)

## Logging

All activity is logged to `storage/logs/laravel.log` with the prefix `EventEndNotificationService:`

Look for these log entries to verify it's working:
- Started
- Found ended events
- Processing event
- Email sent to participant
- Event marked as notified in database
- Completed successfully

## Troubleshooting

### If no logs appear:

1. Check if the cron job is running: `crontab -l`
2. Check file permissions on `storage/logs/`
3. Manually run: `php artisan schedule:run` to see errors

### If emails aren't being sent:

1. Check email configuration in `.env`
2. Check Laravel logs for email errors
3. Verify SMTP settings are correct

## Hosting Providers

### cPanel
- Go to Cron Jobs
- Add: `*/1 * * * * cd /home/username/qricket && php artisan schedule:run`
- Select PHP version from dropdown

### Shared Hosting (SiteGround, etc.)
- Go to Cron Jobs in hosting panel
- Command: `/usr/bin/php /path/to/qricket/artisan schedule:run`
- Frequency: Every Minute

### Cloud Hosting (AWS, DigitalOcean)
- Use systemd or supervisord for better control
- Or use Laravel Forge/Horizon for queue management

