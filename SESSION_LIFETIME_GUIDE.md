# Session Lifetime Configuration Guide

## Current Configuration

According to `config/session.php`:
- **Session Lifetime**: 120 minutes (2 hours)
- **Expire on Close**: No (session persists after browser closes)

## How to Change Session Lifetime

### Method 1: Environment Variable (Recommended)

Add to your `.env` file:

```env
# Set session lifetime in minutes (e.g., 30 minutes)
SESSION_LIFETIME=30

# Set session to expire when browser closes
SESSION_EXPIRE_ON_CLOSE=true

# Optional: Session driver configuration
SESSION_DRIVER=database
```

### Method 2: Direct Configuration File

Edit `config/session.php`:

```php
'lifetime' => 30, // Change from 120 to 30 minutes
'expire_on_close' => true, // Change from false to true
```

## Session Lifetime Examples

### Short Session (15 minutes)
```env
SESSION_LIFETIME=15
SESSION_EXPIRE_ON_CLOSE=false
```

### Medium Session (30 minutes)
```env
SESSION_LIFETIME=30
SESSION_EXPIRE_ON_CLOSE=false
```

### Long Session (8 hours / 480 minutes)
```env
SESSION_LIFETIME=480
SESSION_EXPIRE_ON_CLOSE=false
```

### Session Expires on Browser Close
```env
SESSION_LIFETIME=120
SESSION_EXPIRE_ON_CLOSE=true
```

## Check Current Session Status

To check if a session is expired in your code:

```php
// Check if session exists and is not expired
if (session()->has('key')) {
    // Session is active
}

// Check session age
$sessionAge = session('last_activity', now())->diffInMinutes(now());
```

## Admin Session Tracking

For admin sessions, check the `sessions` table:

```sql
SELECT 
    id,
    user_id,
    role,
    last_activity,
    FROM_UNIXTIME(last_activity) as last_activity_time,
    TIMESTAMPDIFF(MINUTE, FROM_UNIXTIME(last_activity), NOW()) as minutes_since_activity
FROM sessions
WHERE role = 'admin'
ORDER BY last_activity DESC;
```

## Clear Expired Sessions

To manually clear expired sessions:

```php
// In your controller or middleware
$lifetime = config('session.lifetime', 120);
$expired = DB::table('sessions')
    ->where('last_activity', '<', now()->subMinutes($lifetime)->timestamp)
    ->delete();
```

## Testing Session Lifetime

Test session expiration:

```bash
# Create a test route
Route::get('/test-session', function() {
    session(['test' => 'value']);
    return [
        'lifetime' => config('session.lifetime'),
        'expire_on_close' => config('session.expire_on_close'),
        'session_id' => session()->getId(),
    ];
});
```

## Important Notes

1. **After making changes**: 
   - Clear config cache: `php artisan config:clear`
   - Restart your server if using queue workers

2. **Database Sessions**:
   - Sessions are stored in the `sessions` table
   - Sessions are cleaned up automatically (2% chance on each request)
   - You can manually clean: `php artisan session:gc`

3. **User Experience**:
   - Shorter lifetimes = more security, less convenience
   - Longer lifetimes = less security, more convenience
   - Balance based on your application needs
