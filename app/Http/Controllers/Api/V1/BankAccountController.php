<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DoctorBankAccount;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankAccountController extends Controller
{
    /**
     * Get doctor's bank accounts
     */
    public function index($doctorId)
    {
        $user = Auth::user();
        
        // Only the doctor themselves can view their accounts
        if ($user->getMorphClass() !== 'Doctor' || $user->id != $doctorId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $accounts = DoctorBankAccount::where('doctor_id', $doctorId)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $accounts
        ]);
    }

    /**
     * Store new bank account
     */
    public function store(Request $request, $doctorId)
    {
        $user = Auth::user();
        
        // Only the doctor themselves can add accounts
        if ($user->getMorphClass() !== 'Doctor' || $user->id != $doctorId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'bank_id' => 'required|exists:banks,id',
            'account_number' => ['required', 'string', 'regex:/^[0-9]+$/', 'min:10', 'max:20'],
            'account_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
        ]);

        // SECURITY: Sanitize account name
        $data = [
            'bank_id' => $request->bank_id,
            'account_number' => preg_replace('/[^0-9]/', '', $request->account_number), // Only numbers
            'account_name' => trim(strip_tags($request->account_name)),
        ];

        $account = DoctorBankAccount::create([
            'doctor_id' => $doctorId,
            'bank_id' => $request->bank_id,
            'account_number' => $request->account_number,
            'account_name' => $request->account_name,
            'is_default' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bank account added successfully',
            'data' => $account
        ], 201);
    }

    /**
     * Update bank account
     */
    public function update(Request $request, $doctorId, $accountId)
    {
        $user = Auth::user();
        $account = DoctorBankAccount::findOrFail($accountId);
        
        // Only the doctor themselves can update
        if ($user->getMorphClass() !== 'Doctor' || $account->doctor_id != $doctorId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'account_name' => 'sometimes|string|max:255',
        ]);

        $account->update($request->only(['account_name']));

        return response()->json([
            'success' => true,
            'message' => 'Bank account updated successfully',
            'data' => $account
        ]);
    }

    /**
     * Delete bank account
     */
    public function destroy($doctorId, $accountId)
    {
        $user = Auth::user();
        $account = DoctorBankAccount::findOrFail($accountId);
        
        // Only the doctor themselves can delete
        if ($user->getMorphClass() !== 'Doctor' || $account->doctor_id != $doctorId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $account->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bank account deleted successfully'
        ]);
    }

    /**
     * Set default bank account
     */
    public function setDefault($doctorId, $accountId)
    {
        $user = Auth::user();
        $account = DoctorBankAccount::findOrFail($accountId);
        
        // Only the doctor themselves can set default
        if ($user->getMorphClass() !== 'Doctor' || $account->doctor_id != $doctorId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Unset other defaults
        DoctorBankAccount::where('doctor_id', $doctorId)
            ->update(['is_default' => false]);

        // Set this as default
        $account->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default bank account updated successfully',
            'data' => $account
        ]);
    }

    /**
     * Verify bank account
     */
    public function verifyAccount(Request $request, $doctorId)
    {
        $user = Auth::user();
        
        // Only the doctor themselves can verify
        if ($user->getMorphClass() !== 'Doctor' || $user->id != $doctorId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'bank_id' => 'required|exists:banks,id',
            'account_number' => 'required|string',
        ]);

        // Implementation would verify with bank API
        // This is a placeholder
        return response()->json([
            'success' => true,
            'message' => 'Account verification initiated',
            'data' => [
                'account_name' => 'VERIFIED ACCOUNT NAME',
                'verified' => true
            ]
        ]);
    }

    /**
     * Get list of banks
     */
    public function getBanks()
    {
        $banks = Bank::orderBy('name', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $banks
        ]);
    }
}

