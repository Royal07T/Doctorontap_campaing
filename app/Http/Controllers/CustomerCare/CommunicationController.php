<?php

namespace App\Http\Controllers\CustomerCare;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Doctor;
use App\Services\VonageService;
use App\Mail\CustomCommunication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class CommunicationController extends Controller
{
    protected $vonageService;

    public function __construct(VonageService $vonageService)
    {
        $this->vonageService = $vonageService;
    }

    /**
     * Send communication (SMS, WhatsApp, or Email)
     */
    public function send(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'user_type' => 'required|in:patient,doctor',
            'channel' => 'required|in:sms,whatsapp,email',
            'message' => 'required|string',
            'subject' => 'required_if:channel,email|string|max:255',
        ]);

        $user = null;
        if ($request->user_type === 'patient') {
            $user = Patient::findOrFail($request->user_id);
        } else {
            $user = Doctor::findOrFail($request->user_id);
        }

        try {
            $status = 'pending';
            $error = null;
            $messageId = null;

            // Log communication start
            $logId = DB::table('patient_communications')->insertGetId([
                'patient_id' => $request->user_type === 'patient' ? $user->id : null,
                'doctor_id' => $request->user_type === 'doctor' ? $user->id : null,
                'type' => $request->channel,
                'direction' => 'outbound',
                'content' => $request->message,
                'status' => 'pending',
                'created_by' => auth()->guard('customer_care')->user()->user_id ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $result = ['success' => false, 'message' => 'Invalid channel'];

            if ($request->channel === 'email') {
                Mail::to($user->email)->send(new CustomCommunication($request->message, $request->subject));
                $result = ['success' => true, 'message' => 'Email sent successfully'];
            } elseif ($request->channel === 'sms') {
                if ($this->vonageService) {
                    $result = $this->vonageService->sendSMS($user->phone, $request->message);
                } else {
                    $result = ['success' => false, 'message' => 'SMS service not available'];
                }
            } elseif ($request->channel === 'whatsapp') {
                if ($this->vonageService) {
                    $result = $this->vonageService->sendWhatsAppMessage($user->phone, $request->message);
                } else {
                    $result = ['success' => false, 'message' => 'WhatsApp service not available'];
                }
            }

            if ($result['success']) {
                $status = 'sent';
                $messageId = $result['data']['message_id'] ?? $result['data']['message_uuid'] ?? null;
            } else {
                $status = 'failed';
                $error = $result['message'] ?? 'Unknown error';
            }

            // Update log
            DB::table('patient_communications')->where('id', $logId)->update([
                'status' => $status,
                'message_id' => $messageId,
                'error' => $error,
                'sent_at' => $status === 'sent' ? now() : null,
                'failed_at' => $status === 'failed' ? now() : null,
                'updated_at' => now()
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Communication failed', [
                'user_id' => $request->user_id,
                'user_type' => $request->user_type,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'System error: ' . $e->getMessage()
            ], 500);
        }
    }
}
