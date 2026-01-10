<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return $user && (int) $user->id === (int) $id;
});

// Notification channels for different user types
Broadcast::channel('notifications.admin.{userId}', function ($user, $userId) {
    if (Auth::guard('admin')->check()) {
        return Auth::guard('admin')->id() == $userId;
    }
    return false;
});

Broadcast::channel('notifications.doctor.{userId}', function ($user, $userId) {
    if (Auth::guard('doctor')->check()) {
        return Auth::guard('doctor')->id() == $userId;
    }
    return false;
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

