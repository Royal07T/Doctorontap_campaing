<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunicationTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CommunicationTemplateController extends Controller
{
    /**
     * Display a listing of templates
     */
    public function index(Request $request)
    {
        $query = CommunicationTemplate::with('createdBy');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%");
            });
        }

        // Filter by channel
        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('active', false);
            }
        }

        $templates = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get statistics
        $stats = [
            'total' => CommunicationTemplate::count(),
            'active' => CommunicationTemplate::where('active', true)->count(),
            'inactive' => CommunicationTemplate::where('active', false)->count(),
            'sms' => CommunicationTemplate::where('channel', 'sms')->count(),
            'email' => CommunicationTemplate::where('channel', 'email')->count(),
            'whatsapp' => CommunicationTemplate::where('channel', 'whatsapp')->count(),
        ];

        return view('admin.communication-templates.index', compact('templates', 'stats'));
    }

    /**
     * Show the form for creating a new template
     */
    public function create()
    {
        return view('admin.communication-templates.create');
    }

    /**
     * Store a newly created template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'channel' => 'required|in:sms,email,whatsapp',
            'subject' => 'nullable|string|max:255|required_if:channel,email',
            'body' => 'required|string|max:5000',
            'variables' => 'nullable|array',
            'active' => 'boolean',
        ]);

        $admin = Auth::guard('admin')->user();

        $template = CommunicationTemplate::create([
            'name' => $validated['name'],
            'channel' => $validated['channel'],
            'subject' => $validated['subject'] ?? null,
            'body' => $validated['body'],
            'variables' => $validated['variables'] ?? [],
            'active' => $request->has('active') ? true : false,
            'created_by' => $admin->id,
        ]);

        // Extract variables from body
        preg_match_all('/\{\{(\w+)\}\}/', $validated['body'], $matches);
        $detectedVariables = $matches[1] ?? [];
        if (!empty($detectedVariables)) {
            $template->update(['variables' => array_unique($detectedVariables)]);
        }

        Log::info('Communication template created by Admin', [
            'template_id' => $template->id,
            'template_name' => $template->name,
            'channel' => $template->channel,
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
        ]);

        return redirect()
            ->route('admin.communication-templates.index')
            ->with('success', 'Template created successfully.');
    }

    /**
     * Display the specified template
     */
    public function show(CommunicationTemplate $communicationTemplate)
    {
        $communicationTemplate->load('createdBy');
        return view('admin.communication-templates.show', compact('communicationTemplate'));
    }

    /**
     * Show the form for editing the specified template
     */
    public function edit(CommunicationTemplate $communicationTemplate)
    {
        return view('admin.communication-templates.edit', compact('communicationTemplate'));
    }

    /**
     * Update the specified template
     */
    public function update(Request $request, CommunicationTemplate $communicationTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'channel' => 'required|in:sms,email,whatsapp',
            'subject' => 'nullable|string|max:255|required_if:channel,email',
            'body' => 'required|string|max:5000',
            'variables' => 'nullable|array',
            'active' => 'boolean',
        ]);

        $admin = Auth::guard('admin')->user();

        // Extract variables from body
        preg_match_all('/\{\{(\w+)\}\}/', $validated['body'], $matches);
        $detectedVariables = $matches[1] ?? [];

        $communicationTemplate->update([
            'name' => $validated['name'],
            'channel' => $validated['channel'],
            'subject' => $validated['subject'] ?? null,
            'body' => $validated['body'],
            'variables' => !empty($detectedVariables) ? array_unique($detectedVariables) : ($validated['variables'] ?? []),
            'active' => $request->has('active') ? true : false,
        ]);

        Log::info('Communication template updated by Admin', [
            'template_id' => $communicationTemplate->id,
            'template_name' => $communicationTemplate->name,
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
        ]);

        return redirect()
            ->route('admin.communication-templates.show', $communicationTemplate)
            ->with('success', 'Template updated successfully.');
    }

    /**
     * Remove the specified template
     */
    public function destroy(CommunicationTemplate $communicationTemplate)
    {
        $admin = Auth::guard('admin')->user();

        Log::info('Communication template deleted by Admin', [
            'template_id' => $communicationTemplate->id,
            'template_name' => $communicationTemplate->name,
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
        ]);

        $communicationTemplate->delete();

        return redirect()
            ->route('admin.communication-templates.index')
            ->with('success', 'Template deleted successfully.');
    }

    /**
     * Toggle template active status
     */
    public function toggleStatus(CommunicationTemplate $communicationTemplate)
    {
        $communicationTemplate->update([
            'active' => !$communicationTemplate->active,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Template status updated successfully.');
    }
}
