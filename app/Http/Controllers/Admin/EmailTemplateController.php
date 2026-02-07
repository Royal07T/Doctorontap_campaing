<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EmailTemplateController extends Controller
{
    /**
     * Display a listing of email templates
     */
    public function index(Request $request)
    {
        $query = EmailTemplate::with(['creator', 'updater']);

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->category($request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $templates = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get statistics
        $stats = [
            'total' => EmailTemplate::count(),
            'active' => EmailTemplate::where('is_active', true)->count(),
            'inactive' => EmailTemplate::where('is_active', false)->count(),
            'marketing' => EmailTemplate::where('category', 'marketing')->count(),
            'transactional' => EmailTemplate::where('category', 'transactional')->count(),
            'newsletter' => EmailTemplate::where('category', 'newsletter')->count(),
        ];

        return view('admin.email-templates.index', compact('templates', 'stats'));
    }

    /**
     * Show the form for creating a new email template
     */
    public function create()
    {
        $categories = [
            'marketing' => 'Marketing',
            'transactional' => 'Transactional',
            'notification' => 'Notification',
            'reminder' => 'Reminder',
            'promotional' => 'Promotional',
            'newsletter' => 'Newsletter',
        ];

        return view('admin.email-templates.create', compact('categories'));
    }

    /**
     * Store a newly created email template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:email_templates,name',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'plain_text_content' => 'nullable|string',
            'description' => 'nullable|string|max:500',
            'category' => 'required|in:marketing,transactional,notification,reminder,promotional,newsletter',
            'from_name' => 'nullable|string|max:255',
            'from_email' => 'nullable|email|max:255',
            'reply_to' => 'nullable|email|max:255',
            'is_active' => 'boolean',
        ]);

        $template = new EmailTemplate($validated);
        $template->slug = Str::slug($validated['name']);
        $template->created_by = Auth::guard('admin')->id();
        $template->updated_by = Auth::guard('admin')->id();
        
        // Auto-extract variables from content and subject
        $template->variables = $template->extractVariables();
        
        $template->save();

        return redirect()
            ->route('admin.email-templates.index')
            ->with('success', 'Email template created successfully!');
    }

    /**
     * Display the specified email template
     */
    public function show(EmailTemplate $emailTemplate)
    {
        $emailTemplate->load(['creator', 'updater', 'campaigns' => function($query) {
            $query->orderBy('created_at', 'desc')->take(10);
        }]);

        // Get usage statistics
        $stats = [
            'total_campaigns' => $emailTemplate->campaigns()->count(),
            'successful_sends' => $emailTemplate->campaigns()->sum('successful_sends'),
            'failed_sends' => $emailTemplate->campaigns()->sum('failed_sends'),
            'total_recipients' => $emailTemplate->campaigns()->sum('total_recipients'),
            'total_opens' => $emailTemplate->campaigns()->sum('opened_count'),
            'total_clicks' => $emailTemplate->campaigns()->sum('clicked_count'),
        ];

        return view('admin.email-templates.show', compact('emailTemplate', 'stats'));
    }

    /**
     * Show the form for editing the specified email template
     */
    public function edit(EmailTemplate $emailTemplate)
    {
        $categories = [
            'marketing' => 'Marketing',
            'transactional' => 'Transactional',
            'notification' => 'Notification',
            'reminder' => 'Reminder',
            'promotional' => 'Promotional',
            'newsletter' => 'Newsletter',
        ];

        return view('admin.email-templates.edit', compact('emailTemplate', 'categories'));
    }

    /**
     * Update the specified email template
     */
    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:email_templates,name,' . $emailTemplate->id,
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'plain_text_content' => 'nullable|string',
            'description' => 'nullable|string|max:500',
            'category' => 'required|in:marketing,transactional,notification,reminder,promotional,newsletter',
            'from_name' => 'nullable|string|max:255',
            'from_email' => 'nullable|email|max:255',
            'reply_to' => 'nullable|email|max:255',
            'is_active' => 'boolean',
        ]);

        $emailTemplate->fill($validated);
        $emailTemplate->slug = Str::slug($validated['name']);
        $emailTemplate->updated_by = Auth::guard('admin')->id();
        
        // Auto-extract variables from content and subject
        $emailTemplate->variables = $emailTemplate->extractVariables();
        
        $emailTemplate->save();

        return redirect()
            ->route('admin.email-templates.index')
            ->with('success', 'Email template updated successfully!');
    }

    /**
     * Remove the specified email template (soft delete)
     */
    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();

        return redirect()
            ->route('admin.email-templates.index')
            ->with('success', 'Email template deleted successfully!');
    }

    /**
     * Toggle template active status
     */
    public function toggleStatus(EmailTemplate $emailTemplate)
    {
        $emailTemplate->is_active = !$emailTemplate->is_active;
        $emailTemplate->updated_by = Auth::guard('admin')->id();
        $emailTemplate->save();

        $status = $emailTemplate->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Template {$status} successfully!");
    }

    /**
     * Duplicate an existing template
     */
    public function duplicate(EmailTemplate $emailTemplate)
    {
        $newTemplate = $emailTemplate->replicate();
        $newTemplate->name = $emailTemplate->name . ' (Copy)';
        $newTemplate->slug = Str::slug($newTemplate->name);
        $newTemplate->created_by = Auth::guard('admin')->id();
        $newTemplate->updated_by = Auth::guard('admin')->id();
        $newTemplate->usage_count = 0;
        $newTemplate->save();

        return redirect()
            ->route('admin.email-templates.edit', $newTemplate)
            ->with('success', 'Template duplicated successfully! You can now edit it.');
    }

    /**
     * Preview template with sample data
     */
    public function preview(Request $request, EmailTemplate $emailTemplate)
    {
        $sampleData = $request->all();
        $rendered = $emailTemplate->render($sampleData);

        return response()->json([
            'success' => true,
            'subject' => $rendered['subject'],
            'content' => $rendered['content'],
            'plain_text' => $rendered['plain_text'],
            'variables' => $emailTemplate->variables,
        ]);
    }
}
