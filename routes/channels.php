<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return $user && (int) $user->id === (int) $id;
});

// Notification channels for different user types
// Note: $user parameter may be null, so we check guards directly
Broadcast::channel('notifications.admin.{userId}', function ($user, $userId) {
    if (Auth::guard('admin')->check()) {
        return Auth::guard('admin')->id() == $userId;
    }
    return false;
});

Broadcast::channel('notifications.doctor.{userId}', function ($user, $userId) {
    // Laravel's broadcasting uses the default guard, but we need to check the doctor guard
    // The $user parameter comes from Auth::user() which uses the default guard
    // So we need to check all guards to find the authenticated user
    
    $doctorId = null;
    $isAuthenticated = false;
    
    // First, try to get the doctor ID from the doctor guard
    if (Auth::guard('doctor')->check()) {
        $doctorId = Auth::guard('doctor')->id();
        $isAuthenticated = true;
    }
    // If $user is provided and it's a doctor model, use it
    elseif ($user && is_object($user)) {
        // Check if it's a Doctor model by checking the table name or class
        $userClass = get_class($user);
        if (str_contains($userClass, 'Doctor') || 
            (method_exists($user, 'getTable') && $user->getTable() === 'doctors') ||
            (method_exists($user, 'id') && $user->id == $userId)) {
            $doctorId = $user->id;
            $isAuthenticated = true;
        }
    }
    
    // Log for debugging (remove in production)
    if (app()->environment('local')) {
        \Log::info('Broadcasting auth check - doctor channel', [
            'user_param' => $user ? (method_exists($user, 'id') ? $user->id : get_class($user)) : 'null',
            'doctor_guard_id' => $doctorId,
            'doctor_guard_check' => Auth::guard('doctor')->check(),
            'requested_user_id' => $userId,
            'match' => $isAuthenticated && $doctorId == $userId,
            'session_id' => session()->getId(),
            'all_guards' => [
                'web' => Auth::guard('web')->check(),
                'doctor' => Auth::guard('doctor')->check(),
                'admin' => Auth::guard('admin')->check(),
            ],
        ]);
    }
    
    $result = $isAuthenticated && $doctorId == $userId;
    
    if (!$result && app()->environment('local')) {
        \Log::warning('Broadcasting auth failed for doctor channel', [
            'doctor_id' => $doctorId,
            'requested_id' => $userId,
            'authenticated' => $isAuthenticated,
        ]);
    }
    
    return $result;
});

Broadcast::channel('notifications.patient.{userId}', function ($user, $userId) {
    if (Auth::guard('patient')->check()) {
        return Auth::guard('patient')->id() == $userId;
    }
    return false;
});

Broadcast::channel('notifications.nurse.{userId}', function ($user, $userId) {
    if (Auth::guard('nurse')->check()) {
        return Auth::guard('nurse')->id() == $userId;
    }
    return false;
});

Broadcast::channel('notifications.canvasser.{userId}', function ($user, $userId) {
    if (Auth::guard('canvasser')->check()) {
        return Auth::guard('canvasser')->id() == $userId;
    }
    return false;
});
