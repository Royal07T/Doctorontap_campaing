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

        $room = DB::transaction(function () use ($consultation, $actor) {
            $existing = VideoRoom::where('active_consultation_id', $consultation->id)
                ->whereIn('status', ['pending', 'active'])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            $sessionResult = $this->videoService->createSession([
                'archiveMode' => ArchiveMode::MANUAL,
            ]);

            if (!$sessionResult['success']) {
                abort(503, $sessionResult['message'] ?? 'Failed to create video session');
            }

            return VideoRoom::create([
                'name' => $request->string('name')->toString() ?: ('Consultation ' . $consultation->id),
                'consultation_id' => $consultation->id,
                'active_consultation_id' => $consultation->id,
                'vonage_session_id' => $sessionResult['session_id'],
                'status' => 'pending',
                'created_by' => $actor->user_id ?? null,
            ]);
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

        $room = VideoRoom::where('active_consultation_id', $consultation->id)
            ->whereIn('status', ['pending', 'active'])
            ->latest()
            ->first();

        if (!$room) {
            return response()->json(['success' => false, 'message' => 'No active room found'], 404);
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

        return response()->json([
            'success' => true,
            'api_key' => config('services.vonage.api_key'),
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

        return response()->json([
            'success' => true,
            'api_key' => config('services.vonage.api_key'),
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

        $room = VideoRoom::where('consultation_id', $consultation->id)
            ->latest()
            ->first();

        if (!$room) {
            return response()->json(['success' => true, 'room' => null]);
        }

        Gate::forUser($actor)->authorize('view', $room);

        return response()->json([
            'success' => true,
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
