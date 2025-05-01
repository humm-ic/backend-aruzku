<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the notifications.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $isRead = $request->query('is_read');
        
        $query = $user->notifications();
        
        if ($isRead !== null) {
            $query->where('is_read', $isRead);
        }
        
        $notifications = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $notifications,
        ], 200);
    }

    /**
     * Mark a notification as read.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function markAsRead($id, Request $request)
    {
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($id);
        
        $notification->is_read = true;
        $notification->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read',
            'data' => [
                'notification' => $notification,
            ],
        ], 200);
    }

    /**
     * Mark all notifications as read.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $user->notifications()->update(['is_read' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read',
        ], 200);
    }

    /**
     * Remove the specified notification from storage.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($id);
        
        $notification->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification deleted successfully',
        ], 200);
    }
}
