<?php

namespace App\Http\Controllers\CustomerCare;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    /**
     * Display settings page
     */
    public function index()
    {
        $user = auth()->guard('customer_care')->user();
        
        // Get account statistics
        $stats = [
            'consultations_handled' => $user->consultations()->count(),
            'tickets_assigned' => $user->supportTickets()->count(),
            'tickets_resolved' => $user->supportTickets()->where('status', 'resolved')->count(),
            'tickets_open' => $user->supportTickets()->whereIn('status', ['pending', 'in_progress'])->count(),
            'prospects_created' => $user->prospects()->count(),
            'prospects_converted' => $user->prospects()->where('status', 'Converted')->count(),
            'escalations_created' => $user->escalations()->count(),
            'member_since' => $user->created_at,
            'last_login' => $user->last_login_at,
            'is_active' => $user->is_active,
        ];
        
        return view('customer-care.settings', compact('user', 'stats'));
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::guard('customer_care')->user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Audit log
        Log::info('Customer Care password changed', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'action' => 'password_changed',
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    /**
     * Update dashboard preferences
     */
    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'auto_refresh_interval' => 'nullable|integer|min:10|max:300',
            'items_per_page' => 'nullable|integer|min:5|max:50',
            'show_statistics' => 'nullable|boolean',
            'show_queue_management' => 'nullable|boolean',
            'show_team_status' => 'nullable|boolean',
            'show_performance_metrics' => 'nullable|boolean',
            'show_activity_feed' => 'nullable|boolean',
            'show_priority_queue' => 'nullable|boolean',
            'show_pipeline_metrics' => 'nullable|boolean',
            'default_view' => 'nullable|in:enhanced,standard',
        ]);

        $user = Auth::guard('customer_care')->user();
        
        // Remove null values to keep existing preferences
        $preferences = array_filter($validated, function($value) {
            return $value !== null;
        });

        $user->updateDashboardPreferences($preferences);

        Log::info('Customer Care dashboard preferences updated', [
            'agent_id' => $user->id,
            'agent_email' => $user->email,
            'preferences' => $preferences,
        ]);

        return back()->with('success', 'Dashboard preferences updated successfully.');
    }
}
