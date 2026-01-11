<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class VonageVoiceWebhookController extends Controller
{
    /**
     * Handle answer webhook - called when call is answered
     * POST /vonage/webhook/voice/answer
     */
    public function handleAnswer(Request $request)
    {
        Log::info('Vonage Voice answer webhook received', [
            'data' => $request->all()
        ]);

        $data = $request->all();
        $callUuid = $data['uuid'] ?? null;
        $from = $data['from'] ?? null;
        $to = $data['to'] ?? null;

        // Store call answer event
        try {
            DB::table('voice_call_logs')->insert([
                'call_uuid' => $callUuid,
                'event_type' => 'answered',
                'from' => $from,
                'to' => $to,
                'raw_data' => json_encode($data),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('Vonage Voice call answered logged', [
                'call_uuid' => $callUuid,
                'from' => $from,
                'to' => $to
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log Vonage Voice answer', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
        }

        // Return NCCO instructions
        // This is where you can return custom NCCO based on your logic
        // For now, return a simple talk action
        $ncco = [
            [
                'action' => 'talk',
                'text' => 'Hello! This is a call from DoctorOnTap. How can we help you today?',
                'language' => 'en-US',
                'style' => 0
            ]
        ];

        return response()->json($ncco);
    }

    /**
     * Handle event webhook - called for call status updates
     * POST /vonage/webhook/voice/event
     */
    public function handleEvent(Request $request)
    {
        Log::info('Vonage Voice event webhook received', [
            'data' => $request->all()
        ]);

        $data = $request->all();
        $callUuid = $data['uuid'] ?? null;
        $status = $data['status'] ?? null;
        $direction = $data['direction'] ?? null;
        $from = $data['from'] ?? null;
        $to = $data['to'] ?? null;
        $duration = $data['duration'] ?? null;
        $price = $data['price'] ?? null;
        $rate = $data['rate'] ?? null;

        // Store call event
        try {
            DB::table('voice_call_logs')->insert([
                'call_uuid' => $callUuid,
                'event_type' => $status ?? 'unknown',
                'direction' => $direction,
                'from' => $from,
                'to' => $to,
                'duration' => $duration,
                'price' => $price,
                'rate' => $rate,
                'raw_data' => json_encode($data),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update call status if it exists
            if ($callUuid && $status) {
                DB::table('voice_call_logs')
                    ->where('call_uuid', $callUuid)
                    ->where('event_type', '!=', $status)
                    ->update([
                        'status' => $status,
                        'updated_at' => now(),
                    ]);
            }

            Log::info('Vonage Voice event logged', [
                'call_uuid' => $callUuid,
                'status' => $status,
                'direction' => $direction
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log Vonage Voice event', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
        }

        // Return 200 OK
        return response('OK', 200);
    }

    /**
     * Handle recording webhook - called when recording is complete
     * POST /vonage/webhook/voice/recording
     */
    public function handleRecording(Request $request)
    {
        Log::info('Vonage Voice recording webhook received', [
            'data' => $request->all()
        ]);

        $data = $request->all();
        $recordingUuid = $data['recording_uuid'] ?? null;
        $recordingUrl = $data['recording_url'] ?? null;
        $callUuid = $data['conversation_uuid'] ?? null;
        $startTime = $data['start_time'] ?? null;
        $endTime = $data['end_time'] ?? null;

        // Store recording information
        try {
            DB::table('voice_recordings')->insert([
                'recording_uuid' => $recordingUuid,
                'call_uuid' => $callUuid,
                'recording_url' => $recordingUrl,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'raw_data' => json_encode($data),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('Vonage Voice recording logged', [
                'recording_uuid' => $recordingUuid,
                'call_uuid' => $callUuid,
                'recording_url' => $recordingUrl
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log Vonage Voice recording', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
        }

        // Return 200 OK
        return response('OK', 200);
    }
}

