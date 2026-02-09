<?php

namespace App\Http\Controllers\CustomerCare;

use App\Http\Controllers\Controller;
use App\Models\SmsTemplate;
use App\Models\SmsCampaign;
use App\Models\Patient;
use App\Services\TermiiService;
use App\Services\VonageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BulkSmsController extends Controller
{
    protected $smsService;

    public function __construct()
    {
        // Get the configured SMS provider
        $provider = config('services.sms_provider', 'termii');
        
        if ($provider === 'vonage') {
            $this->smsService = app(VonageService::class);
        } else {
            $this->smsService = app(TermiiService::class);
        }
    }

    /**
     * Show SMS campaign page
     */
    public function index()
    {
        $templates = SmsTemplate::active()->orderBy('name')->get();
        
        // Get recent campaigns by this customer care
        $campaigns = SmsCampaign::with('template')
            ->where('sent_by', Auth::guard('customer_care')->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get statistics
        $stats = [
            'total_campaigns' => SmsCampaign::where('sent_by', Auth::guard('customer_care')->id())->count(),
            'total_sent' => SmsCampaign::where('sent_by', Auth::guard('customer_care')->id())->sum('successful_sends'),
            'total_failed' => SmsCampaign::where('sent_by', Auth::guard('customer_care')->id())->sum('failed_sends'),
            'success_rate' => 0,
        ];

        $totalMessages = $stats['total_sent'] + $stats['total_failed'];
        if ($totalMessages > 0) {
            $stats['success_rate'] = round(($stats['total_sent'] / $totalMessages) * 100, 2);
        }

        return view('customer-care.bulk-sms.index', compact('templates', 'campaigns', 'stats'));
    }

    /**
     * Show form to compose SMS
     */
    public function create()
    {
        $templates = SmsTemplate::active()->orderBy('name')->get();
        
        return view('customer-care.bulk-sms.create', compact('templates'));
    }

    /**
     * Preview template with variables
     */
    public function preview(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:sms_templates,id',
            'variables' => 'nullable|array',
        ]);

        $template = SmsTemplate::findOrFail($request->template_id);
        $preview = $template->render($request->variables ?? []);

        return response()->json([
            'success' => true,
            'preview' => $preview,
            'length' => strlen($preview),
            'variables' => $template->variables,
            'content' => $template->content,
        ]);
    }

    /**
     * Get patients for recipient selection
     */
    public function getPatients(Request $request)
    {
        $query = Patient::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $patients = $query->take(50)->get(['id', 'name', 'phone', 'email']);

        return response()->json([
            'success' => true,
            'patients' => $patients,
        ]);
    }

    /**
     * Send bulk SMS
     */
    public function send(Request $request)
    {
        $request->validate([
            'campaign_name' => 'required|string|max:255',
            'template_id' => 'nullable|exists:sms_templates,id',
            'message' => 'required|string|max:1000',
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'required|string', // Phone numbers
            'variables' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            // Create campaign record
            $campaign = SmsCampaign::create([
                'campaign_name' => $request->campaign_name,
                'template_id' => $request->template_id,
                'sent_by' => Auth::guard('customer_care')->id(),
                'message_content' => $request->message,
                'recipient_phones' => $request->recipients,
                'total_recipients' => count($request->recipients),
                'status' => 'processing',
            ]);

            // Increment template usage if template was used
            if ($request->template_id) {
                $template = SmsTemplate::find($request->template_id);
                $template?->incrementUsage();
            }

            DB::commit();

            // Send SMS in background (or immediately for small batches)
            $this->processCampaign($campaign, $request->message, $request->recipients);

            return redirect()
                ->route('customer-care.bulk-sms.show', $campaign)
                ->with('success', 'SMS campaign started! Messages are being sent.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk SMS Campaign Failed', [
                'error' => $e->getMessage(),
                'user' => Auth::guard('customer_care')->id(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to start SMS campaign: ' . $e->getMessage()]);
        }
    }

    /**
     * Process campaign and send SMS
     */
    protected function processCampaign(SmsCampaign $campaign, string $message, array $recipients)
    {
        $results = [];
        $successful = 0;
        $failed = 0;

        foreach ($recipients as $phone) {
            try {
                // Get patient data for personalization
                $patient = Patient::where('phone', $phone)->first();
                
                // Prepare personalization data (handle null patient safely)
                $patientName = $patient?->name ?? 'Valued Patient';
                $patientEmail = $patient?->email ?? 'N/A';
                
                $personalData = [
                    'name' => $patientName,
                    'first_name' => $patientName ? explode(' ', $patientName)[0] : 'Valued',
                    'last_name' => $patientName && count(explode(' ', $patientName)) > 1 ? explode(' ', $patientName)[1] : 'Patient',
                    'email' => $patientEmail,
                    'phone' => $phone,
                    'company_name' => config('app.name', 'DoctorOnTap'),
                    'date' => now()->format('F j, Y'),
                    'time' => now()->format('g:i A'),
                    'link' => url('/'),
                ];
                
                // Replace variables in message
                $personalizedMessage = $this->replaceVariables($message, $personalData);
                
                // Send personalized SMS
                $result = $this->smsService->sendSMS($phone, $personalizedMessage);

                if ($result['success']) {
                    $successful++;
                    $results[] = [
                        'phone' => $phone,
                        'status' => 'success',
                        'message_id' => $result['data']['message_id'] ?? null,
                    ];
                } else {
                    $failed++;
                    $results[] = [
                        'phone' => $phone,
                        'status' => 'failed',
                        'error' => $result['message'] ?? 'Unknown error',
                    ];
                }
            } catch (\Exception $e) {
                $failed++;
                $results[] = [
                    'phone' => $phone,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Update campaign with results
        $campaign->update([
            'successful_sends' => $successful,
            'failed_sends' => $failed,
            'send_results' => $results,
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }
    
    /**
     * Replace variables in text with actual data
     */
    protected function replaceVariables(string $text, array $data): string
    {
        foreach ($data as $key => $value) {
            // Replace both {variable} and {{variable}} formats
            $text = str_replace(['{' . $key . '}', '{{' . $key . '}}'], $value, $text);
        }
        
        return $text;
    }

    /**
     * Show campaign details
     */
    public function show(SmsCampaign $campaign)
    {
        // Ensure user can only see their own campaigns
        if ($campaign->sent_by !== Auth::guard('customer_care')->id()) {
            abort(403, 'Unauthorized access to this campaign.');
        }

        $campaign->load('template', 'sender');

        return view('customer-care.bulk-sms.show', compact('campaign'));
    }

    /**
     * Show campaign history
     */
    public function history(Request $request)
    {
        $query = SmsCampaign::with('template')
            ->where('sent_by', Auth::guard('customer_care')->id());

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $campaigns = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('customer-care.bulk-sms.history', compact('campaigns'));
    }

    /**
     * Export campaign results
     */
    public function export(SmsCampaign $campaign)
    {
        // Ensure user can only export their own campaigns
        if ($campaign->sent_by !== Auth::guard('customer_care')->id()) {
            abort(403, 'Unauthorized access to this campaign.');
        }

        $filename = 'campaign-' . $campaign->id . '-results.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($campaign) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, ['Phone Number', 'Status', 'Message ID', 'Error']);
            
            // Add results
            foreach ($campaign->send_results as $result) {
                fputcsv($file, [
                    $result['phone'] ?? '',
                    $result['status'] ?? '',
                    $result['message_id'] ?? '',
                    $result['error'] ?? '',
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
