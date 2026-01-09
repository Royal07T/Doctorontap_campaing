<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Notification channels for different user types
Broadcast::channel('notifications.admin.{userId}', function ($user, $userId) {
    return Auth::guard('admin')->check() && 
           Auth::guard('admin')->id() == $userId;
});

Broadcast::channel('notifications.doctor.{userId}', function ($user, $userId) {
    return Auth::guard('doctor')->check() && 
           Auth::guard('doctor')->id() == $userId;
});

Broadcast::channel('notifications.patient.{userId}', function ($user, $userId) {
    return Auth::guard('patient')->check() && 
           Auth::guard('patient')->id() == $userId;
});

Broadcast::channel('notifications.nurse.{userId}', function ($user, $userId) {
    return Auth::guard('nurse')->check() && 
           Auth::guard('nurse')->id() == $userId;
});

Broadcast::channel('notifications.canvasser.{userId}', function ($user, $userId) {
    return Auth::guard('canvasser')->check() && 
           Auth::guard('canvasser')->id() == $userId;
});
