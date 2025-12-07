<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource; // Assuming we might want a resource, but for now standard response
// Laravel's native database notifications use `Illuminate\Notifications\DatabaseNotification`.
// We assume valid usage of user->notifications().

use App\Support\ApiResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get list of notifications for the authenticated user.
     */
    public function index(Request $request)
    {
        // Use Laravel's standard notification relationship
        $notifications = $request->user()->notifications()->paginate(20);

        return ApiResponse::success(
            $notifications,
            'Notifications retrieved successfully'
        );
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return ApiResponse::success(
            null,
            'Notification marked as read'
        );
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return ApiResponse::success(
            null,
            'All notifications marked as read'
        );
    }
}
