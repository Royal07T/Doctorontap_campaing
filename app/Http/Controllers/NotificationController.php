<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get notifications for the authenticated user (Web version)
     */
    public function index(Request $request)
    {
        // Check all possible guards to find authenticated user
        $user = $this->getAuthenticatedUser();
        
        if (!$user) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return redirect()->route('admin.login');
        }

        $userType = $this->getUserType($user);
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

        // Return JSON for both AJAX and regular requests
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

        $userType = $this->getUserType($user);
        $userId = $user->id;

        $count = Notification::forUser($userType, $userId)->unread()->count();

        return response()->json([
            'success' => true,
            'data' => ['count' => $count]
        ]);
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

        $userType = $this->getUserType($user);
        $userId = $user->id;

        $notification = Notification::forUser($userType, $userId)
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
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

        $userType = $this->getUserType($user);
        $userId = $user->id;

        Notification::forUser($userType, $userId)
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Get authenticated user from any guard
     */
    private function getAuthenticatedUser()
    {
        // Check all possible guards in order of likelihood
        $guards = ['admin', 'doctor', 'patient', 'nurse', 'canvasser', 'customer_care', 'care_giver', 'web'];
        
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return Auth::guard($guard)->user();
            }
        }
        
        // Fallback to default guard
        return Auth::user();
    }

    /**
     * Get user type based on model class
     */
    private function getUserType($user): string
    {
        $class = get_class($user);
        $typeMap = [
            \App\Models\Patient::class => 'patient',
            \App\Models\Doctor::class => 'doctor',
            \App\Models\AdminUser::class => 'admin',
            \App\Models\Nurse::class => 'nurse',
            \App\Models\Canvasser::class => 'canvasser',
            \App\Models\CustomerCare::class => 'customer_care',
        ];

        return $typeMap[$class] ?? 'patient';
    }
}

