<?php

namespace App\Services;

use App\Events\NotificationSent;
use App\Events\UserNotification as UserNotificationEvent;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public static function sendGeneralNotification(string $message, string $type = 'info', ?string $title = null): void
    {
        Log::info('Broadcasting general notification', [
            'type' => $type,
            'title' => $title,
            'message' => $message,
        ]);
        event(new NotificationSent($message, $type, $title));
    }

    public static function sendUserNotification(int $userId, string $message, string $type = 'info', ?string $title = null): void
    {
        Log::info('Broadcasting user notification', [
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
        ]);
        
        try {
            event(new UserNotificationEvent($userId, $message, $type, $title));
            Log::info('UserNotification event fired successfully', [
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fire UserNotification event', [
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public static function success(string $message, ?int $userId = null, ?string $title = null): void
    {
        if ($userId) {
            self::sendUserNotification($userId, $message, 'success', $title);
        } else {
            self::sendGeneralNotification($message, 'success', $title);
        }
    }

    public static function error(string $message, ?int $userId = null, ?string $title = null): void
    {
        if ($userId) {
            self::sendUserNotification($userId, $message, 'error', $title);
        } else {
            self::sendGeneralNotification($message, 'error', $title);
        }
    }

    public static function warning(string $message, ?int $userId = null, ?string $title = null): void
    {
        if ($userId) {
            self::sendUserNotification($userId, $message, 'warning', $title);
        } else {
            self::sendGeneralNotification($message, 'warning', $title);
        }
    }

    public static function info(string $message, ?int $userId = null, ?string $title = null): void
    {
        if ($userId) {
            self::sendUserNotification($userId, $message, 'info', $title);
        } else {
            self::sendGeneralNotification($message, 'info', $title);
        }
    }
}




