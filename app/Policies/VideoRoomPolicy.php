<?php

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\VideoRoom;
use Illuminate\Support\Facades\Log;

class VideoRoomPolicy
{
    public function create($user, Consultation $consultation): bool
    {
        if ($user instanceof AdminUser) {
            $this->logAuthorization('create', $user, $consultation, true, 'admin');
            return true;
        }

        if ($user instanceof Doctor) {
            $authorized = $consultation->doctor_id === $user->id;
            $this->logAuthorization('create', $user, $consultation, $authorized, 'doctor');
            return $authorized;
        }

        $this->logAuthorization('create', $user, $consultation, false, 'unknown');
        return false;
    }

    public function view($user, VideoRoom $room): bool
    {
        return $this->join($user, $room);
    }

    public function join($user, VideoRoom $room): bool
    {
        $consultation = $room->consultation;

        if ($user instanceof AdminUser) {
            $this->logAuthorizationRoom('join', $user, $room, true, 'admin');
            return true;
        }

        if (!$consultation) {
            $this->logAuthorizationRoom('join', $user, $room, false, 'unknown');
            return false;
        }

        if ($user instanceof Doctor) {
            $authorized = $consultation->doctor_id === $user->id;
            $this->logAuthorizationRoom('join', $user, $room, $authorized, 'doctor');
            return $authorized;
        }

        if ($user instanceof Patient) {
            $authorized = ($consultation->patient_id !== null && $consultation->patient_id === $user->id)
                || ($consultation->email !== null && $consultation->email === $user->email);
            $this->logAuthorizationRoom('join', $user, $room, $authorized, 'patient');
            return $authorized;
        }

        $this->logAuthorizationRoom('join', $user, $room, false, 'unknown');
        return false;
    }

    public function end($user, VideoRoom $room): bool
    {
        $consultation = $room->consultation;

        if ($user instanceof AdminUser) {
            $this->logAuthorizationRoom('end', $user, $room, true, 'admin');
            return true;
        }

        if (!$consultation) {
            $this->logAuthorizationRoom('end', $user, $room, false, 'unknown');
            return false;
        }

        if ($user instanceof Doctor) {
            $authorized = $consultation->doctor_id === $user->id;
            $this->logAuthorizationRoom('end', $user, $room, $authorized, 'doctor');
            return $authorized;
        }

        if ($user instanceof Patient) {
            $authorized = ($consultation->patient_id !== null && $consultation->patient_id === $user->id)
                || ($consultation->email !== null && $consultation->email === $user->email);
            $this->logAuthorizationRoom('end', $user, $room, $authorized, 'patient');
            return $authorized;
        }

        $this->logAuthorizationRoom('end', $user, $room, false, 'unknown');
        return false;
    }

    public function startArchive($user, VideoRoom $room): bool
    {
        if ($user instanceof AdminUser) {
            return true;
        }

        $consultation = $room->consultation;
        if (!$consultation) {
            return false;
        }

        if ($user instanceof Doctor) {
            return $consultation->doctor_id === $user->id;
        }

        return false;
    }

    public function stopArchive($user, VideoRoom $room): bool
    {
        return $this->startArchive($user, $room);
    }

    protected function logAuthorization(string $action, $user, Consultation $consultation, bool $authorized, string $userType): void
    {
        $result = $authorized ? 'GRANTED' : 'DENIED';

        Log::channel('audit')->info("Authorization Check: {$result}", [
            'action' => $action,
            'resource' => 'VideoRoom',
            'consultation_id' => $consultation->id,
            'consultation_reference' => $consultation->reference,
            'user_type' => $userType,
            'user_id' => $user->id ?? null,
            'user_email' => $user->email ?? null,
            'authorized' => $authorized,
            'ip_address' => request()->ip(),
            'timestamp' => now()->toIso8601String(),
        ]);

        if (!$authorized) {
            Log::warning('Unauthorized access attempt to video room action', [
                'action' => $action,
                'user_type' => $userType,
                'user_id' => $user->id ?? null,
                'user_email' => $user->email ?? null,
                'consultation_id' => $consultation->id,
                'ip_address' => request()->ip(),
            ]);
        }
    }

    protected function logAuthorizationRoom(string $action, $user, VideoRoom $room, bool $authorized, string $userType): void
    {
        $result = $authorized ? 'GRANTED' : 'DENIED';

        Log::channel('audit')->info("Authorization Check: {$result}", [
            'action' => $action,
            'resource' => 'VideoRoom',
            'video_room_id' => $room->id,
            'video_room_uuid' => $room->uuid,
            'consultation_id' => $room->consultation_id,
            'user_type' => $userType,
            'user_id' => $user->id ?? null,
            'user_email' => $user->email ?? null,
            'authorized' => $authorized,
            'ip_address' => request()->ip(),
            'timestamp' => now()->toIso8601String(),
        ]);

        if (!$authorized) {
            Log::warning('Unauthorized access attempt to video room', [
                'action' => $action,
                'user_type' => $userType,
                'user_id' => $user->id ?? null,
                'user_email' => $user->email ?? null,
                'video_room_id' => $room->id,
                'consultation_id' => $room->consultation_id,
                'ip_address' => request()->ip(),
            ]);
        }
    }
}
