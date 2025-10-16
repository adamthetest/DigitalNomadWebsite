<?php

namespace App\Http\Controllers;

use App\Events\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Send a notification to all users
     */
    public function broadcastToAll(Request $request)
    {
        $message = $request->input('message', 'Hello from the server!');

        // Broadcast to all users
        broadcast(new UserNotification($message));

        return response()->json([
            'success' => true,
            'message' => 'Notification sent to all users',
        ]);
    }

    /**
     * Send a notification to a specific user
     */
    public function broadcastToUser(Request $request, $userId)
    {
        $message = $request->input('message', 'Personal message from the server!');

        // Broadcast to specific user
        broadcast(new UserNotification($message, $userId));

        return response()->json([
            'success' => true,
            'message' => "Notification sent to user {$userId}",
        ]);
    }

    /**
     * Send a notification to the current user
     */
    public function broadcastToCurrentUser(Request $request)
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        $message = $request->input('message', 'Message for you!');

        // Broadcast to current user
        broadcast(new UserNotification($message, Auth::id()));

        return response()->json([
            'success' => true,
            'message' => 'Notification sent to current user',
        ]);
    }
}
