# Laravel Scheduler Cron Job Setup

## Step-by-Step Instructions

### 1. In Your Hosting Cron Job Panel

Select **"Custom"** (not PHP)

### 2. Command to Run

Enter this command (replace `qricket` with your project folder name if different):

```
cd /home/username/qricket && /usr/bin/php artisan schedule:run
```

**OR if PHP is in a different location:**

```
cd /home/username/qricket && php artisan schedule:run
```

**Note:** Replace `/home/username/qricket` with your actual project path.

### 3. Set the Schedule (Every Minute)

Select these values from the dropdowns:

- **minute:** `*` (every minute)
- **hour:** `*` (every hour)
- **day:** `*` (every day)
- **month:** `*` (every month)
- **weekDay:** `*` (every weekday)

### 4. Click "Save"

That's it! Your scheduler will now run automatically every minute.

## What It Does

The cron job runs `php artisan schedule:run` which:
- Checks for ended events
- Sends notifications to users
- Updates database with `end_notified_at`

## Testing

After setting up, wait 2-3 minutes, then check your logs:

```bash
tail -f storage/logs/laravel.log
```

You should see entries like:
```
[INFO] EventEndNotificationService: Started
[INFO] EventEndNotificationService: Found ended events
```

## Troubleshooting

If it doesn't work:

1. Make sure the URL is correct for your domain
2. Try both `curl` and `wget` commands
3. Check file permissions on `public/cron.php`
4. Ensure your `.env` has correct database credentials

