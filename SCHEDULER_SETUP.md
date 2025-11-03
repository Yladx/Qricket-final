# Laravel Scheduler Setup

## Overview
The event end notification system runs automatically to notify users when events they saved or participated in have ended.

## Automatic Setup (Recommended)

The scheduler runs automatically every minute via the Laravel scheduler. To enable it, add this cron job to your server:

### For Linux/Unix Servers:
```bash
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

### For Windows (Using Task Scheduler):
1. Open Task Scheduler
2. Create Basic Task
3. Trigger: Daily
4. Action: Start a program
5. Program: `php`
6. Arguments: `C:\path\to\your\project\artisan schedule:run`
7. Start in: `C:\path\to\your\project`

### For Shared Hosting (cPanel):
1. Go to Cron Jobs in cPanel
2. Add new cron job with:
   - Minute: `*`
   - Hour: `*`
   - Day of Month: `*`
   - Month: `*`
   - Day of Week: `*`
   - Command: `php /path/to/your/project/artisan schedule:run`

## Manual Command

You can also run the notification processing manually:

```bash
php artisan events:notify-ended
```

## What It Does

The scheduler automatically:
1. **Checks for ended events** that haven't been notified yet
2. **Notifies participants** who purchased tickets
3. **Notifies users** who saved/bookmarked the event
4. **Sends both in-app notifications** and **email notifications**
5. **Prevents duplicate notifications** by checking if the user already received one
6. **Marks events as notified** to avoid processing them again

## Testing

To test if the scheduler is working:

```bash
# Check scheduled tasks
php artisan schedule:list

# Run scheduler manually
php artisan schedule:run

# Test the notification command
php artisan events:notify-ended
```

## Status Check

Check the `end_notified_at` column in the `events` table to see which events have been processed.

## Logs

Monitor the logs for notification processing:
```bash
tail -f storage/logs/laravel.log | grep EventEndNotification
```

