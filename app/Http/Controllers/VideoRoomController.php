<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\VideoRoom;
use App\Models\VideoRoomArchive;
use App\Services\VonageVideoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use OpenTok\ArchiveMode;
use OpenTok\Role;

class VideoRoomController extends Controller
{
    public function __construct(protected VonageVideoService $videoService)
    {
    }

    public function createRoom(Request $request, Consultation $consultation)
    {
        $actor = $this->actor();
        if (!$actor) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        Gate::forUser($actor)->authorize('create', [VideoRoom::class, $consultation]);

        // PAYMENT CHECK: Verify payment before allowing room creation
        if ($consultation->requiresPaymentBeforeStart()) {
            \Log::warning('Video room creation blocked: payment required', [
                'consultation_id' => $consultation->id,
                'consultation_reference' => $consultation->reference,
                'payment_status' => $consultation->payment_status,
                'actor_type' => get_class($actor),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Payment is required before this consultation can proceed. Please complete payment first.',
                'payment_required' => true,
            ], 400);
        }

        $room = DB::transaction(function () use ($consultation, $actor) {
            $existing = VideoRoom::where('active_consultation_id', $consultation->id)
                ->whereIn('status', ['pending', 'active'])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            // Create session with optimized settings for consultations
            // Media Mode: ROUTED (required for archiving, better for multiparty, supports Media Router features)
            // Archive Mode: MANUAL (archives only when explicitly started, not automatic)
            // Note: ROUTED mode is required even for MANUAL archiving per Vonage documentation
            // https://developer.vonage.com/en/video/guides/create-session#the-media-router-and-media-modes
            $sessionResult = $this->videoService->createSession([
                'mediaMode' => 'ROUTED', // Explicitly set ROUTED for archiving support and better reliability
                'archiveMode' => ArchiveMode::MANUAL, // Manual archiving - only archive when explicitly requested
            ]);

            if (!$sessionResult['success']) {
                // Log the error but don't abort - return error response instead
                \Log::error('Failed to create video session for consultation', [
                    'consultation_id' => $consultation->id,
                    'error' => $sessionResult['error'] ?? $sessionResult['message'] ?? 'Unknown error'
                ]);
                throw new \Exception($sessionResult['message'] ?? 'Failed to create video session');
            }

            $room = VideoRoom::create([
                'name' => $request->string('name')->toString() ?: ('Consultation ' . $consultation->id),
                'consultation_id' => $consultation->id,
                'active_consultation_id' => $consultation->id,
                'vonage_session_id' => $sessionResult['session_id'],
                'status' => 'pending',
                'created_by' => $actor->user_id ?? null,
            ]);

            // Update consultation session_status if not already set
            if (!$consultation->session_status) {
                $consultation->update([
                    'session_status' => $consultation->scheduled_at && $consultation->scheduled_at->isFuture() 
                        ? 'scheduled' 
                        : 'waiting'
                ]);
            }

            return $room;
        });

        return response()->json([
            'success' => true,
            'room' => [
                'id' => $room->id,
                'uuid' => $room->uuid,
                'status' => $room->status,
                'consultation_id' => $room->consultation_id,
                'vonage_session_id' => $room->vonage_session_id,
                'started_at' => optional($room->started_at)->toIso8601String(),
                'ended_at' => optional($room->ended_at)->toIso8601String(),
            ],
        ]);
    }

    public function joinRoom(Request $request, Consultation $consultation)
    {
        $actor = $this->actor();
        if (!$actor) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        // PAYMENT CHECK: Verify payment before allowing room join (for patients only)
        // Doctors can join to inform patient about payment requirement
        if ($actor instanceof \App\Models\Patient && $consultation->requiresPaymentBeforeStart()) {
            \Log::warning('Video room join blocked: payment required', [
                'consultation_id' => $consultation->id,
                'consultation_reference' => $consultation->reference,
                'payment_status' => $consultation->payment_status,
                'patient_id' => $actor->id,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Payment is required before this consultation can proceed. Please complete payment first.',
                'payment_required' => true,
                'payment_url' => route('payment.request', ['reference' => $consultation->reference]),
            ], 400);
        }

        $room = VideoRoom::where('active_consultation_id', $consultation->id)
            ->whereIn('status', ['pending', 'active'])
            ->latest()
            ->first();

        if (!$room) {
            // Check if user is a patient - provide more helpful message
            $isPatient = $actor instanceof \App\Models\Patient;
            $message = $isPatient 
                ? 'The video room has not been created yet. Please wait for the doctor to start the session.'
                : 'No active room found. Please create a room first.';
            
            return response()->json([
                'success' => false, 
                'message' => $message,
                'error_code' => 'room_not_found',
                'can_create' => !$isPatient // Only doctors can create rooms
            ], 404);
        }

        Gate::forUser($actor)->authorize('join', $room);

        if ($room->status === 'pending') {
            $room->status = 'active';
        }
        if (!$room->started_at) {
            $room->started_at = now();
        }
        $room->save();

        $tokenResult = $this->videoService->generateToken(
            $room->vonage_session_id,
            $this->roleForActor($actor),
            'participant',
            3600
        );

        if (!$tokenResult['success']) {
            return response()->json([
                'success' => false,
                'message' => $tokenResult['message'] ?? 'Failed to generate token',
            ], 500);
        }

        // Get the correct identifier: Application ID (JWT) or API Key (Legacy)
        $applicationId = $this->videoService->getApplicationId();

        return response()->json([
            'success' => true,
            'applicationId' => $applicationId, // Application ID (JWT) or API Key (Legacy)
            'session_id' => $room->vonage_session_id,
            'token' => $tokenResult['token'],
            'room' => [
                'id' => $room->id,
                'uuid' => $room->uuid,
                'status' => $room->status,
                'consultation_id' => $room->consultation_id,
                'started_at' => optional($room->started_at)->toIso8601String(),
            ],
        ]);
    }

    public function refreshToken(Request $request, Consultation $consultation)
    {
        $actor = $this->actor();
        if (!$actor) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $room = VideoRoom::where('active_consultation_id', $consultation->id)
            ->whereIn('status', ['pending', 'active'])
            ->latest()
            ->first();

        if (!$room) {
            return response()->json(['success' => false, 'message' => 'No active room found'], 404);
        }

        Gate::forUser($actor)->authorize('join', $room);

        $tokenResult = $this->videoService->generateToken(
            $room->vonage_session_id,
            $this->roleForActor($actor),
            'participant',
            3600
        );

        if (!$tokenResult['success']) {
            return response()->json([
                'success' => false,
                'message' => $tokenResult['message'] ?? 'Failed to generate token',
            ], 500);
        }

        // Get the correct identifier: Application ID (JWT) or API Key (Legacy)
        $applicationId = $this->videoService->getApplicationId();

        return response()->json([
            'success' => true,
            'applicationId' => $applicationId, // Application ID (JWT) or API Key (Legacy)
            'session_id' => $room->vonage_session_id,
            'token' => $tokenResult['token'],
            'room_uuid' => $room->uuid,
        ]);
    }

    public function status(Consultation $consultation)
    {
        $actor = $this->actor();
        if (!$actor) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        // Use efficient query - only refresh consultation if we need to update status
        // For status polling, we can use the already-loaded model
        // Only refresh if we detect a status change is needed
        
        $room = VideoRoom::where('consultation_id', $consultation->id)
            ->latest()
            ->first();
        
        // Only refresh consultation if we need to check/update session_status
        // This avoids unnecessary DB queries during frequent status polling
        $needsRefresh = false;
        if (!$room && $consultation->scheduled_at && $consultation->scheduled_at->isPast() && $consultation->session_status === 'scheduled') {
            $needsRefresh = true;
        }
        
        if ($needsRefresh) {
            $consultation->refresh();
        }

        // Map room status to session status for waiting room compatibility
        $sessionStatus = $consultation->session_status;
        
        // If no room exists but consultation has session_status, use that
        if (!$room) {
            // Check if scheduled time has passed
            $scheduledTimePassed = $consultation->scheduled_at && $consultation->scheduled_at->isPast();
            
            // If consultation is scheduled and time hasn't arrived, return scheduled
            if ($consultation->scheduled_at && $consultation->scheduled_at->isFuture()) {
                $sessionStatus = 'scheduled';
            } elseif ($scheduledTimePassed && ($sessionStatus === 'scheduled' || !$sessionStatus)) {
                // If scheduled time has passed, change status to waiting to allow joining
                $sessionStatus = 'waiting';
                // Optionally update the consultation status in database
                if ($consultation->session_status === 'scheduled') {
                    $consultation->update(['session_status' => 'waiting']);
                }
            } elseif (!$sessionStatus) {
                $sessionStatus = 'waiting'; // Default to waiting if no status set
            }
            
            return response()->json([
                'success' => true,
                'session_status' => $sessionStatus,
                'consultation_status' => $consultation->status,
                'room' => null,
            ]);
        }

        Gate::forUser($actor)->authorize('view', $room);

        // Map room status to session status
        // Room statuses: pending, active, ended
        // Session statuses: scheduled, waiting, active, completed, cancelled
        if (!$sessionStatus || $sessionStatus === 'scheduled') {
            // Check if scheduled time has passed
            $scheduledTimePassed = $consultation->scheduled_at && $consultation->scheduled_at->isPast();
            
            switch ($room->status) {
                case 'pending':
                    // If scheduled time has passed, allow joining (waiting status)
                    if ($scheduledTimePassed) {
                        $sessionStatus = 'waiting';
                        // Update consultation status if still scheduled
                        if ($consultation->session_status === 'scheduled') {
                            $consultation->update(['session_status' => 'waiting']);
                        }
                    } else {
                        $sessionStatus = $consultation->scheduled_at && $consultation->scheduled_at->isFuture() 
                            ? 'scheduled' 
                            : 'waiting';
                    }
                    break;
                case 'active':
                    $sessionStatus = 'active';
                    break;
                case 'ended':
                    $sessionStatus = 'completed';
                    break;
                default:
                    $sessionStatus = 'waiting';
            }
        }

        return response()->json([
            'success' => true,
            'session_status' => $sessionStatus,
            'consultation_status' => $consultation->status,
            'room' => [
                'id' => $room->id,
                'uuid' => $room->uuid,
                'status' => $room->status,
                'consultation_id' => $room->consultation_id,
                'active_consultation_id' => $room->active_consultation_id,
                'vonage_session_id' => $room->vonage_session_id,
                'started_at' => optional($room->started_at)->toIso8601String(),
                'ended_at' => optional($room->ended_at)->toIso8601String(),
                'duration' => $room->duration,
                'participant_count' => $room->participant_count,
            ],
        ]);
    }

    public function endRoom(Request $request, Consultation $consultation)
    {
        $actor = $this->actor();
        if (!$actor) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $room = VideoRoom::where('active_consultation_id', $consultation->id)
            ->whereIn('status', ['pending', 'active'])
            ->latest()
            ->first();

        if (!$room) {
            return response()->json(['success' => false, 'message' => 'No active room found'], 404);
        }

        Gate::forUser($actor)->authorize('end', $room);

        $endedAt = now();
        $startedAt = $room->started_at;

        $room->ended_at = $endedAt;
        $room->status = 'ended';
        $room->active_consultation_id = null;
        if ($startedAt) {
            $room->duration = $endedAt->diffInSeconds($startedAt);
        }
        $room->save();

        return response()->json([
            'success' => true,
            'room' => [
                'id' => $room->id,
                'uuid' => $room->uuid,
                'status' => $room->status,
                'ended_at' => optional($room->ended_at)->toIso8601String(),
                'duration' => $room->duration,
            ],
        ]);
    }

    public function startArchive(Request $request, Consultation $consultation)
    {
        $actor = $this->actor();
        if (!$actor) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $room = VideoRoom::where('active_consultation_id', $consultation->id)
            ->whereIn('status', ['pending', 'active'])
            ->latest()
            ->first();

        if (!$room) {
            return response()->json(['success' => false, 'message' => 'No active room found'], 404);
        }

        Gate::forUser($actor)->authorize('startArchive', $room);

        $archiveResult = $this->videoService->startArchive($room->vonage_session_id, [
            'name' => 'video-room-' . $room->uuid,
        ]);

        if (!$archiveResult['success']) {
            return response()->json([
                'success' => false,
                'message' => $archiveResult['message'] ?? 'Failed to start recording',
            ], 500);
        }

        $archive = $archiveResult['archive'];

        $record = VideoRoomArchive::create([
            'video_room_id' => $room->id,
            'vonage_archive_id' => $archive->id,
            'status' => $archive->status ?? null,
            'started_at' => isset($archive->createdAt) ? now()->setTimestamp((int) $archive->createdAt) : now(),
            'metadata' => [
                'name' => $archive->name ?? null,
            ],
        ]);

        return response()->json([
            'success' => true,
            'archive' => [
                'id' => $record->id,
                'vonage_archive_id' => $record->vonage_archive_id,
                'status' => $record->status,
                'started_at' => optional($record->started_at)->toIso8601String(),
            ],
        ]);
    }

    public function stopArchive(Request $request, Consultation $consultation)
    {
        $actor = $this->actor();
        if (!$actor) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $room = VideoRoom::where('consultation_id', $consultation->id)->latest()->first();
        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Room not found'], 404);
        }

        Gate::forUser($actor)->authorize('stopArchive', $room);

        $archiveId = $request->string('archive_id')->toString();
        if (!$archiveId) {
            return response()->json(['success' => false, 'message' => 'archive_id is required'], 422);
        }

        $stopResult = $this->videoService->stopArchive($archiveId);
        if (!$stopResult['success']) {
            return response()->json([
                'success' => false,
                'message' => $stopResult['message'] ?? 'Failed to stop recording',
            ], 500);
        }

        $archive = $stopResult['archive'];

        $record = VideoRoomArchive::where('vonage_archive_id', $archiveId)->first();
        if ($record) {
            $record->status = $archive->status ?? $record->status;
            $record->duration = $archive->duration ?? $record->duration;
            $record->stopped_at = now();
            $record->save();
        }

        return response()->json([
            'success' => true,
            'archive' => [
                'vonage_archive_id' => $archiveId,
                'status' => $archive->status ?? null,
                'duration' => $archive->duration ?? null,
            ],
        ]);
    }

    protected function actor(): mixed
    {
        if (Auth::guard('doctor')->check()) {
            return Auth::guard('doctor')->user();
        }

        if (Auth::guard('patient')->check()) {
            return Auth::guard('patient')->user();
        }

        if (Auth::guard('admin')->check()) {
            return Auth::guard('admin')->user();
        }

        return null;
    }

    protected function roleForActor($actor): string
    {
        if ($actor instanceof \App\Models\Doctor || $actor instanceof \App\Models\AdminUser) {
            return Role::MODERATOR;
        }

        return Role::PUBLISHER;
    }
}
