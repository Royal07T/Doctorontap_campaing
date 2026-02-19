<?php

namespace App\Http\Controllers\CustomerCare;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\CommunicationTemplate;
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
     * Get available templates for a channel
     */
    public function getTemplates(Request $request)
    {
        $request->validate([
            'channel' => 'required|in:sms,whatsapp,email',
        ]);

        $templates = CommunicationTemplate::active()
            ->byChannel($request->channel)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'templates' => $templates,
        ]);
    }

    /**
     * Send communication using template (NO FREE TEXT ALLOWED)
     */
    public function send(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'user_type' => 'required|in:patient,doctor',
            'channel' => 'required|in:sms,whatsapp,email',
            'template_id' => 'required|exists:communication_templates,id',
        ]);

        $agent = auth()->guard('customer_care')->user();

        // Get template
        $template = CommunicationTemplate::active()
            ->byChannel($request->channel)
            ->findOrFail($request->template_id);

        // Verify template channel matches
        if ($template->channel !== $request->channel) {
            return response()->json([
                'success' => false,
                'message' => 'Template channel mismatch'
            ], 400);
        }

        $user = null;
        if ($request->user_type === 'patient') {
            $user = Patient::findOrFail($request->user_id);
        } else {
            $user = Doctor::findOrFail($request->user_id);
        }

        try {
            // Prepare comprehensive recipient information
            $messageData = [
                'first_name' => $user->first_name ?? (explode(' ', $user->name ?? 'Valued Customer')[0] ?? 'Valued'),
                'last_name' => $user->last_name ?? (count(explode(' ', $user->name ?? '')) > 1 ? explode(' ', $user->name)[1] : ''),
                'full_name' => $user->name ?? ($user->first_name ?? '') . ' ' . ($user->last_name ?? ''),
                'name' => $user->name ?? ($user->first_name ?? 'Customer'),
                'email' => $user->email ?? '',
                'phone' => $user->phone ?? $user->mobile ?? '',
                'mobile' => $user->phone ?? $user->mobile ?? '',
                'age' => isset($user->age) ? (string)$user->age : '',
                'gender' => $user->gender ?? '',
            ];

            $message = $template->replaceVariables($messageData);
            $subject = $template->replaceVariablesInSubject($messageData);

            $status = 'pending';
            $error = null;
            $messageId = null;

            // Log communication start
            $logId = DB::table('patient_communications')->insertGetId([
                'patient_id' => $request->user_type === 'patient' ? $user->id : null,
                'doctor_id' => $request->user_type === 'doctor' ? $user->id : null,
                'type' => $request->channel,
                'direction' => 'outbound',
                'content' => $message,
                'status' => 'pending',
                'template_id' => $template->id,
                'created_by' => $agent->user_id ?? $agent->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $result = ['success' => false, 'message' => 'Invalid channel'];

            if ($request->channel === 'email') {
                Mail::to($user->email)->send(new CustomCommunication($message, $subject ?? 'Message from DoctorOnTap', $messageData));
                $result = ['success' => true, 'message' => 'Email sent successfully'];
            } elseif ($request->channel === 'sms') {
                if ($this->vonageService) {
                    $result = $this->vonageService->sendSMS($user->phone, $message);
                } else {
                    $result = ['success' => false, 'message' => 'SMS service not available'];
                }
            } elseif ($request->channel === 'whatsapp') {
                if ($this->vonageService) {
                    $result = $this->vonageService->sendWhatsAppMessage($user->phone, $message);
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

            // Audit log
            Log::info('Communication sent via template', [
                'template_id' => $template->id,
                'template_name' => $template->name,
                'channel' => $request->channel,
                'user_id' => $user->id,
                'user_type' => $request->user_type,
                'agent_id' => $agent->id,
                'agent_name' => $agent->name,
                'status' => $status,
                'action' => 'communication_sent_template',
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
