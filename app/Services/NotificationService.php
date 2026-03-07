<?php

namespace App\Services;

use App\Mail\NotificationMail;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Send notification to a single user (database + email).
     */
    public static function send(int $userId, string $type, string $title, string $message, ?string $link = null): Notification
    {
        $notification = Notification::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'link'    => $link,
        ]);

        // Send email notification
        try {
            $user = User::find($userId);
            if ($user && $user->email) {
                Mail::to($user->email)->queue(
                    new NotificationMail($title, $message, $type, $link)
                );
            }
        } catch (\Throwable $e) {
            // Silently fail — email is best-effort, don't break the app
            \Log::warning("Failed to send notification email to user #{$userId}: " . $e->getMessage());
        }

        return $notification;
    }

    /**
     * Send notification to multiple users.
     */
    public static function sendToMany(array $userIds, string $type, string $title, string $message, ?string $link = null): void
    {
        foreach ($userIds as $userId) {
            static::send($userId, $type, $title, $message, $link);
        }
    }

    /**
     * Send notification to all users with a specific role.
     */
    public static function sendToRole(string $role, string $type, string $title, string $message, ?string $link = null): void
    {
        User::where('role', $role)->each(function ($user) use ($type, $title, $message, $link) {
            static::send($user->id, $type, $title, $message, $link);
        });
    }
}
