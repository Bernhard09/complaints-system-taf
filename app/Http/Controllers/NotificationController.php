<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        $unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return view('notifications.inbox', compact('notifications', 'unreadCount'));
    }

    public function markRead(Request $request, Notification $notification)
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->update(['is_read' => true]);

        return back();
    }

    public function delete(Request $request, Notification $notification)
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->delete();

        return back();
    }

    public function clearAll(Request $request)
    {
        Notification::where('user_id', $request->user()->id)->delete();

        return back()->with('success', 'All notifications cleared.');
    }

    public function markAllRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back();
    }

    /**
     * JSON endpoint for AJAX polling.
     * Returns unread count + any notifications newer than ?last_id.
     */
    public function poll(Request $request)
    {
        $user = $request->user();
        $lastId = (int) $request->query('last_id', 0);

        $unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        $newNotifications = Notification::where('user_id', $user->id)
            ->when($lastId, fn ($q) => $q->where('id', '>', $lastId))
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($n) => [
                'id'    => $n->id,
                'type'  => $n->type,
                'title' => $n->title,
                'message' => $n->message,
                'link'  => $n->link,
                'time'  => $n->created_at->diffForHumans(),
            ]);

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $newNotifications,
        ]);
    }
}
