<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    /**
     * Get notifications for the authenticated user
     */
    public function index(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userType = $this->getUserType();
        $userId = $user->id;

        $query = Notification::forUser($userType, $userId)
            ->orderBy('created_at', 'desc');

        // Filter by read status
        if ($request->has('read')) {
            if ($request->read == 'true') {
                $query->read();
            } else {
                $query->unread();
            }
        }

        $limit = $request->get('limit', 20);
        $notifications = $query->limit($limit)->get();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => Notification::forUser($userType, $userId)->unread()->count(),
        ]);
    }

    /**
     * Get unread notification count
     */
    public function unreadCount()
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return response()->json(['count' => 0]);
        }

        $userType = $this->getUserType();
        $userId = $user->id;

        // Cache key unique per user type and ID
        $cacheKey = "notifications.unread_count.{$userType}.{$userId}";
        
        // Cache for 45 seconds to reduce database queries
        $count = Cache::remember($cacheKey, 45, function () use ($userType, $userId) {
            return Notification::forUser($userType, $userId)->unread()->count();
        });

        return response()->json(['count' => $count]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userType = $this->getUserType();
        $userId = $user->id;

        $notification = Notification::forUser($userType, $userId)
            ->findOrFail($id);

        $notification->markAsRead();

        // Clear cache when notification is marked as read
        $cacheKey = "notifications.unread_count.{$userType}.{$userId}";
        Cache::forget($cacheKey);

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userType = $this->getUserType();
        $userId = $user->id;

        Notification::forUser($userType, $userId)
            ->unread()
            ->update(['read_at' => now()]);

        // Clear cache when all notifications are marked as read
        $cacheKey = "notifications.unread_count.{$userType}.{$userId}";
        Cache::forget($cacheKey);

        return response()->json(['success' => true]);
    }

    /**
     * Get authenticated user based on guard
     */
    private function getAuthenticatedUser()
    {
        $guards = ['patient', 'doctor', 'admin', 'nurse', 'canvasser'];
        
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return Auth::guard($guard)->user();
            }
        }
        
        return null;
    }

    /**
     * Get user type based on current guard
     */
    private function getUserType(): string
    {
        $guards = ['patient', 'doctor', 'admin', 'nurse', 'canvasser'];
        
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return $guard;
            }
        }
        
        return 'patient'; // default
    }
}
