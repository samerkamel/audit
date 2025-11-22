<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get unread notifications count for current user
     */
    public function getUnreadCount()
    {
        $count = auth()->user()->notifications()->unread()->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get latest notifications for dropdown
     */
    public function getLatest()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => auth()->user()->notifications()->unread()->count(),
        ]);
    }

    /**
     * Display all notifications
     */
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark specific notification as read
     */
    public function markAsRead(Notification $notification)
    {
        // Ensure notification belongs to current user
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        auth()->user()
            ->notifications()
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete specific notification
     */
    public function destroy(Notification $notification)
    {
        // Ensure notification belongs to current user
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Notification deleted successfully');
    }

    /**
     * Delete all notifications
     */
    public function destroyAll()
    {
        auth()->user()->notifications()->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'All notifications deleted successfully');
    }

    /**
     * Confirm a notification
     */
    public function confirm(string $notification)
    {
        $notificationRecord = auth()->user()
            ->notifications()
            ->where('id', $notification)
            ->first();

        if (!$notificationRecord) {
            abort(404);
        }

        // Update confirmation status
        $notificationRecord->update([
            'confirmed_at' => now(),
            'confirmed_by' => auth()->id(),
        ]);

        // Also mark as read if not already
        if (!$notificationRecord->read_at) {
            $notificationRecord->markAsRead();
        }

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Notification confirmed successfully']);
        }

        return redirect()->back()->with('success', 'Notification confirmed successfully');
    }

    /**
     * Get notifications requiring confirmation
     */
    public function getRequiringConfirmation()
    {
        $notifications = auth()->user()
            ->notifications()
            ->where('requires_confirmation', true)
            ->whereNull('confirmed_at')
            ->latest()
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'count' => $notifications->count(),
        ]);
    }

    /**
     * Check if a specific notification type requires confirmation
     */
    public static function requiresConfirmation(string $notificationType): bool
    {
        // Define notification types that require confirmation
        $requiresConfirmation = [
            'car_issued',
            'improvement_opportunity_issued',
            'audit_scheduled',
            'external_audit_scheduled',
        ];

        return in_array($notificationType, $requiresConfirmation);
    }
}
