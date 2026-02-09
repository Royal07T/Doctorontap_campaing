<?php

namespace App\Http\Controllers\CustomerCare;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\EmailCampaign;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class BulkEmailController extends Controller
{
    /**
     * Show email campaign page
     */
    public function index()
    {
        $templates = EmailTemplate::active()->orderBy('name')->get();
        
        // Get recent campaigns by this customer care
        $campaigns = EmailCampaign::with('template')
            ->where('sent_by', Auth::guard('customer_care')->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get statistics
        $stats = [
            'total_campaigns' => EmailCampaign::where('sent_by', Auth::guard('customer_care')->id())->count(),
            'total_sent' => EmailCampaign::where('sent_by', Auth::guard('customer_care')->id())->sum('successful_sends'),
            'total_failed' => EmailCampaign::where('sent_by', Auth::guard('customer_care')->id())->sum('failed_sends'),
            'total_opened' => EmailCampaign::where('sent_by', Auth::guard('customer_care')->id())->sum('opened_count'),
            'success_rate' => 0,
            'open_rate' => 0,
        ];

        $totalMessages = $stats['total_sent'] + $stats['total_failed'];
        if ($totalMessages > 0) {
            $stats['success_rate'] = round(($stats['total_sent'] / $totalMessages) * 100, 2);
        }

        if ($stats['total_sent'] > 0) {
            $stats['open_rate'] = round(($stats['total_opened'] / $stats['total_sent']) * 100, 2);
        }

        return view('customer-care.bulk-email.index', compact('templates', 'campaigns', 'stats'));
    }

    /**
     * Show form to compose email
     */
    public function create()
    {
        $templates = EmailTemplate::active()->orderBy('name')->get();
        
        return view('customer-care.bulk-email.create', compact('templates'));
    }

    /**
     * Preview template with variables
     */
    public function preview(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:email_templates,id',
            'variables' => 'nullable|array',
        ]);

        $template = EmailTemplate::findOrFail($request->template_id);
        $rendered = $template->render($request->variables ?? []);

        return response()->json([
            'success' => true,
            'subject' => $rendered['subject'],
            'content' => $rendered['content'],
            'plain_text' => $rendered['plain_text'],
            'variables' => $template->variables,
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
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $patients = $query->whereNotNull('email')->take(50)->get(['id', 'name', 'email', 'phone']);

        return response()->json([
            'success' => true,
            'patients' => $patients,
        ]);
    }

    /**
     * Send bulk email
     */
    public function send(Request $request)
    {
        $request->validate([
            'campaign_name' => 'required|string|max:255',
            'template_id' => 'nullable|exists:email_templates,id',
            'subject' => 'required|string|max:255',
            'content_hidden' => 'required|string',
            'plain_text_content' => 'nullable|string',
            'recipients' => 'nullable|array',
            'recipients.*' => 'email',
            'send_to_all' => 'nullable|boolean',
            'variables' => 'nullable|array',
            'from_name' => 'nullable|string|max:255',
            'from_email' => 'nullable|email|max:255',
        ]);
        
        // Get content from hidden field (Summernote output)
        $content = $request->input('content_hidden');
        
        // Get recipients
        if ($request->send_to_all) {
            // Get all patients with email
            $recipients = Patient::whereNotNull('email')->pluck('email')->toArray();
        } else {
            $recipients = $request->recipients ?? [];
        }
        
        if (empty($recipients)) {
            return back()->withErrors(['error' => 'Please select at least one recipient or choose "All Patients"']);
        }

        DB::beginTransaction();
        try {
            // Create campaign record
            $campaign = EmailCampaign::create([
                'campaign_name' => $request->campaign_name,
                'template_id' => $request->template_id,
                'sent_by' => Auth::guard('customer_care')->id(),
                'subject' => $request->subject,
                'message_content' => $content,
                'plain_text_content' => $request->plain_text_content,
                'recipient_emails' => $recipients,
                'total_recipients' => count($recipients),
                'status' => 'processing',
                'from_name' => $request->from_name ?? config('mail.from.name'),
                'from_email' => $request->from_email ?? config('mail.from.address'),
            ]);

            // Increment template usage if template was used
            if ($request->template_id) {
                $template = EmailTemplate::find($request->template_id);
                $template?->incrementUsage();
            }

            DB::commit();

            // Send emails (synchronously for now, could be queued)
            $this->processCampaign($campaign, $request->subject, $content, $request->plain_text_content, $recipients);

            return redirect()
                ->route('customer-care.bulk-email.show', $campaign)
                ->with('success', 'Email campaign started! Messages are being sent.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk Email Campaign Failed', [
                'error' => $e->getMessage(),
                'user' => Auth::guard('customer_care')->id(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to start email campaign: ' . $e->getMessage()]);
        }
    }

    /**
     * Process campaign and send emails
     */
    protected function processCampaign(EmailCampaign $campaign, string $subject, string $content, ?string $plainText, array $recipients)
    {
        $results = [];
        $successful = 0;
        $failed = 0;

        foreach ($recipients as $email) {
            try {
                // Get patient data for personalization
                $patient = Patient::where('email', $email)->first();
                
                // Prepare personalization data (handle null patient safely)
                $patientName = $patient?->name ?? 'Valued Patient';
                $patientPhone = $patient?->phone ?? 'N/A';
                
                $personalData = [
                    'name' => $patientName,
                    'first_name' => $patientName ? explode(' ', $patientName)[0] : 'Valued',
                    'last_name' => $patientName && count(explode(' ', $patientName)) > 1 ? explode(' ', $patientName)[1] : 'Patient',
                    'email' => $email,
                    'phone' => $patientPhone,
                    'company_name' => config('app.name', 'DoctorOnTap'),
                    'date' => now()->format('F j, Y'),
                    'time' => now()->format('g:i A'),
                    'link' => url('/'),
                    'unsubscribe_link' => url('/unsubscribe?email=' . urlencode($email)),
                ];
                
                // Replace variables in subject and content
                $personalizedSubject = $this->replaceVariables($subject, $personalData);
                $personalizedContent = $this->replaceVariables($content, $personalData);
                $personalizedPlainText = $plainText ? $this->replaceVariables($plainText, $personalData) : null;
                
                // Send personalized email
                Mail::send([], [], function ($message) use ($email, $personalizedSubject, $personalizedContent, $personalizedPlainText, $campaign) {
                    $message->to($email)
                            ->subject($personalizedSubject)
                            ->from($campaign->from_email, $campaign->from_name)
                            ->html($personalizedContent);
                    
                    if ($personalizedPlainText) {
                        $message->text($personalizedPlainText);
                    }
                });

                $successful++;
                $results[] = [
                    'email' => $email,
                    'status' => 'success',
                ];
            } catch (\Exception $e) {
                $failed++;
                $results[] = [
                    'email' => $email,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];

                Log::error('Email send failed', [
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
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
    public function show(EmailCampaign $campaign)
    {
        // Ensure user can only see their own campaigns
        if ($campaign->sent_by !== Auth::guard('customer_care')->id()) {
            abort(403, 'Unauthorized access to this campaign.');
        }

        $campaign->load('template', 'sender');

        return view('customer-care.bulk-email.show', compact('campaign'));
    }

    /**
     * Show campaign history
     */
    public function history(Request $request)
    {
        $query = EmailCampaign::with('template')
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

        return view('customer-care.bulk-email.history', compact('campaigns'));
    }

    /**
     * Export campaign results
     */
    public function export(EmailCampaign $campaign)
    {
        // Ensure user can only export their own campaigns
        if ($campaign->sent_by !== Auth::guard('customer_care')->id()) {
            abort(403, 'Unauthorized access to this campaign.');
        }

        $filename = 'email-campaign-' . $campaign->id . '-results.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($campaign) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, ['Email Address', 'Status', 'Error']);
            
            // Add results
            foreach ($campaign->send_results as $result) {
                fputcsv($file, [
                    $result['email'] ?? '',
                    $result['status'] ?? '',
                    $result['error'] ?? '',
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
