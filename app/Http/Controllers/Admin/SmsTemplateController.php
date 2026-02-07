<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SmsTemplateController extends Controller
{
    /**
     * Display a listing of SMS templates
     */
    public function index(Request $request)
    {
        $query = SmsTemplate::with(['creator', 'updater']);

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
            'total' => SmsTemplate::count(),
            'active' => SmsTemplate::where('is_active', true)->count(),
            'inactive' => SmsTemplate::where('is_active', false)->count(),
            'marketing' => SmsTemplate::where('category', 'marketing')->count(),
            'transactional' => SmsTemplate::where('category', 'transactional')->count(),
        ];

        return view('admin.sms-templates.index', compact('templates', 'stats'));
    }

    /**
     * Show the form for creating a new SMS template
     */
    public function create()
    {
        $categories = [
            'marketing' => 'Marketing',
            'transactional' => 'Transactional',
            'reminder' => 'Reminder',
            'promotional' => 'Promotional',
            'notification' => 'Notification',
        ];

        return view('admin.sms-templates.create', compact('categories'));
    }

    /**
     * Store a newly created SMS template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sms_templates,name',
            'content' => 'required|string|max:1000',
            'description' => 'nullable|string|max:500',
            'category' => 'required|in:marketing,transactional,reminder,promotional,notification',
            'is_active' => 'boolean',
        ]);

        $template = new SmsTemplate($validated);
        $template->slug = Str::slug($validated['name']);
        $template->created_by = Auth::guard('admin')->id();
        $template->updated_by = Auth::guard('admin')->id();
        
        // Auto-extract variables from content
        $template->variables = $template->extractVariables();
        
        $template->save();

        return redirect()
            ->route('admin.sms-templates.index')
            ->with('success', 'SMS template created successfully!');
    }

    /**
     * Display the specified SMS template
     */
    public function show(SmsTemplate $smsTemplate)
    {
        $smsTemplate->load(['creator', 'updater', 'campaigns' => function($query) {
            $query->orderBy('created_at', 'desc')->take(10);
        }]);

        // Get usage statistics
        $stats = [
            'total_campaigns' => $smsTemplate->campaigns()->count(),
            'successful_sends' => $smsTemplate->campaigns()->sum('successful_sends'),
            'failed_sends' => $smsTemplate->campaigns()->sum('failed_sends'),
            'total_recipients' => $smsTemplate->campaigns()->sum('total_recipients'),
        ];

        return view('admin.sms-templates.show', compact('smsTemplate', 'stats'));
    }

    /**
     * Show the form for editing the specified SMS template
     */
    public function edit(SmsTemplate $smsTemplate)
    {
        $categories = [
            'marketing' => 'Marketing',
            'transactional' => 'Transactional',
            'reminder' => 'Reminder',
            'promotional' => 'Promotional',
            'notification' => 'Notification',
        ];

        return view('admin.sms-templates.edit', compact('smsTemplate', 'categories'));
    }

    /**
     * Update the specified SMS template
     */
    public function update(Request $request, SmsTemplate $smsTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sms_templates,name,' . $smsTemplate->id,
            'content' => 'required|string|max:1000',
            'description' => 'nullable|string|max:500',
            'category' => 'required|in:marketing,transactional,reminder,promotional,notification',
            'is_active' => 'boolean',
        ]);

        $smsTemplate->fill($validated);
        $smsTemplate->slug = Str::slug($validated['name']);
        $smsTemplate->updated_by = Auth::guard('admin')->id();
        
        // Auto-extract variables from content
        $smsTemplate->variables = $smsTemplate->extractVariables();
        
        $smsTemplate->save();

        return redirect()
            ->route('admin.sms-templates.index')
            ->with('success', 'SMS template updated successfully!');
    }

    /**
     * Remove the specified SMS template (soft delete)
     */
    public function destroy(SmsTemplate $smsTemplate)
    {
        $smsTemplate->delete();

        return redirect()
            ->route('admin.sms-templates.index')
            ->with('success', 'SMS template deleted successfully!');
    }

    /**
     * Toggle template active status
     */
    public function toggleStatus(SmsTemplate $smsTemplate)
    {
        $smsTemplate->is_active = !$smsTemplate->is_active;
        $smsTemplate->updated_by = Auth::guard('admin')->id();
        $smsTemplate->save();

        $status = $smsTemplate->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Template {$status} successfully!");
    }

    /**
     * Duplicate an existing template
     */
    public function duplicate(SmsTemplate $smsTemplate)
    {
        $newTemplate = $smsTemplate->replicate();
        $newTemplate->name = $smsTemplate->name . ' (Copy)';
        $newTemplate->slug = Str::slug($newTemplate->name);
        $newTemplate->created_by = Auth::guard('admin')->id();
        $newTemplate->updated_by = Auth::guard('admin')->id();
        $newTemplate->usage_count = 0;
        $newTemplate->save();

        return redirect()
            ->route('admin.sms-templates.edit', $newTemplate)
            ->with('success', 'Template duplicated successfully! You can now edit it.');
    }

    /**
     * Preview template with sample data
     */
    public function preview(Request $request, SmsTemplate $smsTemplate)
    {
        $sampleData = $request->all();
        $preview = $smsTemplate->render($sampleData);

        return response()->json([
            'success' => true,
            'preview' => $preview,
            'length' => strlen($preview),
            'variables' => $smsTemplate->variables,
        ]);
    }
}
