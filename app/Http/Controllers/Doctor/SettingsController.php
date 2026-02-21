<?php

namespace App\Http\Controllers\Doctor;

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
        $doctor = auth()->guard('doctor')->user();
        
        // Get account statistics
        $supportTickets = \App\Models\SupportTicket::where('user_type', 'doctor')
            ->where('doctor_id', $doctor->id);
        
        $stats = [
            'consultations_completed' => $doctor->consultations()->where('status', 'completed')->count(),
            'consultations_pending' => $doctor->consultations()->whereIn('status', ['pending', 'scheduled'])->count(),
            'total_earnings' => $doctor->payments()->where('status', 'completed')->sum('doctor_amount'),
            'pending_payouts' => $doctor->payments()->where('status', 'pending')->sum('doctor_amount'),
            'bank_accounts' => $doctor->bankAccounts()->count(),
            'verified_bank_accounts' => $doctor->bankAccounts()->where('is_verified', true)->count(),
            'support_tickets' => $supportTickets->count(),
            'resolved_tickets' => (clone $supportTickets)->where('status', 'resolved')->count(),
            'member_since' => $doctor->created_at,
            'last_login' => $doctor->last_login_at ?? null,
            'is_verified' => $doctor->is_verified ?? false,
            'is_available' => $doctor->is_available ?? false,
        ];
        
        return view('doctor.settings', compact('doctor', 'stats'));
    }

    /**
     * Deactivate account
     */
    public function deactivateAccount(Request $request)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:1000',
            'confirmation' => 'required|accepted',
        ], [
            'reason.required' => 'Please provide a reason for deactivating your account.',
            'reason.min' => 'Reason must be at least 10 characters.',
            'confirmation.required' => 'You must confirm that you want to deactivate your account.',
            'confirmation.accepted' => 'You must confirm that you want to deactivate your account.',
        ]);

        $doctor = Auth::guard('doctor')->user();

        try {
            // Set account as unavailable (deactivated)
            $doctor->update([
                'is_available' => false,
                'unavailable_reason' => $validated['reason'],
            ]);

            // Audit log
            Log::warning('Doctor account deactivated', [
                'doctor_id' => $doctor->id,
                'doctor_email' => $doctor->email,
                'reason' => $validated['reason'],
                'action' => 'account_deactivated',
            ]);

            // Logout the user
            Auth::guard('doctor')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('doctor.login')
                ->with('success', 'Your account has been deactivated. You can contact support to reactivate it.');
        } catch (\Exception $e) {
            Log::error('Failed to deactivate doctor account', [
                'doctor_id' => $doctor->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to deactivate account. Please try again or contact support.')->withInput();
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ], [
            'current_password.required' => 'Current password is required.',
            'password.required' => 'New password is required.',
            'password.min' => 'New password must be at least 8 characters.',
            'password.confirmed' => 'New password confirmation does not match.',
        ]);

        $doctor = Auth::guard('doctor')->user();

        // Verify current password
        if (!Hash::check($request->current_password, $doctor->password)) {
            return back()->with('error', 'The current password is incorrect.')->withInput();
        }

        try {
            // Update password
            $doctor->update([
                'password' => Hash::make($request->password),
            ]);

            // Audit log
            Log::info('Doctor password changed', [
                'doctor_id' => $doctor->id,
                'doctor_email' => $doctor->email,
                'action' => 'password_changed',
            ]);

            return back()->with('success', 'Password updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to change doctor password', [
                'doctor_id' => $doctor->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to update password. Please try again.')->withInput();
        }
    }

    /**
     * Update consultation fee
     */
    public function updatePricing(Request $request)
    {
        $validated = $request->validate([
            'consultation_fee' => 'required_without_all:min_consultation_fee,max_consultation_fee|nullable|numeric|min:0',
            'min_consultation_fee' => 'required_with:max_consultation_fee|nullable|numeric|min:0',
            'max_consultation_fee' => 'required_with:min_consultation_fee|nullable|numeric|min:0|gte:min_consultation_fee',
        ], [
            'consultation_fee.required_without_all' => 'Please set either a consultation fee or a fee range.',
            'consultation_fee.numeric' => 'Consultation fee must be a valid number.',
            'consultation_fee.min' => 'Consultation fee must be at least 0.',
            'min_consultation_fee.required_with' => 'Minimum fee is required when maximum fee is set.',
            'min_consultation_fee.numeric' => 'Minimum fee must be a valid number.',
            'min_consultation_fee.min' => 'Minimum fee must be at least 0.',
            'max_consultation_fee.required_with' => 'Maximum fee is required when minimum fee is set.',
            'max_consultation_fee.numeric' => 'Maximum fee must be a valid number.',
            'max_consultation_fee.min' => 'Maximum fee must be at least 0.',
            'max_consultation_fee.gte' => 'Maximum fee must be greater than or equal to minimum fee.',
        ]);

        $doctor = Auth::guard('doctor')->user();

        try {
            $updateData = [
                'use_default_fee' => false, // Always set to false so doctor's own price is used
            ];

            // If fee range is provided, use it; otherwise use single fee
            if (!empty($validated['min_consultation_fee']) && !empty($validated['max_consultation_fee'])) {
                $updateData['min_consultation_fee'] = $validated['min_consultation_fee'];
                $updateData['max_consultation_fee'] = $validated['max_consultation_fee'];
                $updateData['consultation_fee'] = null; // Clear single fee if range is set
            } elseif (!empty($validated['consultation_fee'])) {
                $updateData['consultation_fee'] = $validated['consultation_fee'];
                $updateData['min_consultation_fee'] = null; // Clear range if single fee is set
                $updateData['max_consultation_fee'] = null;
            }

            $doctor->update($updateData);

            // Audit log
            Log::info('Doctor consultation fee updated', [
                'doctor_id' => $doctor->id,
                'doctor_email' => $doctor->email,
                'consultation_fee' => $updateData['consultation_fee'] ?? null,
                'min_consultation_fee' => $updateData['min_consultation_fee'] ?? null,
                'max_consultation_fee' => $updateData['max_consultation_fee'] ?? null,
                'action' => 'consultation_fee_updated',
            ]);

            return back()->with('success', 'Consultation fee updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update doctor consultation fee', [
                'doctor_id' => $doctor->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to update consultation fee. Please try again.')->withInput();
        }
    }
}

