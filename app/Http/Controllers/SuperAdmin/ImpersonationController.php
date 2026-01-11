<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Canvasser;
use App\Models\Nurse;
use App\Models\CustomerCare;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ImpersonationController extends Controller
{
    protected ActivityLogService $activityLogService;
    protected const IMPERSONATION_SESSION_KEY = 'impersonation_data';
    protected const IMPERSONATION_TIMEOUT = 3600; // 1 hour

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Start impersonating a user
     */
    public function start(Request $request, string $type, int $id)
    {
        $superAdmin = Auth::guard('admin')->user();

        // Check if super admin can impersonate
        if (!$superAdmin->can_impersonate) {
            return response()->json(['message' => 'You do not have permission to impersonate users.'], 403);
        }

        // Get the user to impersonate
        $user = $this->getUserByType($type, $id);
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Store original admin in session
        Session::put(self::IMPERSONATION_SESSION_KEY, [
            'admin_id' => $superAdmin->id,
            'admin_email' => $superAdmin->email,
            'user_type' => $type,
            'user_id' => $id,
            'started_at' => now()->toIso8601String(),
        ]);

        // Log impersonation start
        $this->activityLogService->logImpersonationStart($id, $type);

        // Update last impersonation timestamp
        $superAdmin->last_impersonation_at = now();
        $superAdmin->save();

        return response()->json([
            'message' => 'Impersonation started',
            'user' => [
                'id' => $user->id,
                'name' => $user->name ?? $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'type' => $type,
            ],
        ]);
    }

    /**
     * Stop impersonating
     */
    public function stop()
    {
        $impersonationData = Session::get(self::IMPERSONATION_SESSION_KEY);

        if (!$impersonationData) {
            return response()->json(['message' => 'Not currently impersonating'], 400);
        }

        $startedAt = \Carbon\Carbon::parse($impersonationData['started_at']);
        $duration = now()->diffInSeconds($startedAt);

        // Log impersonation end
        $this->activityLogService->logImpersonationEnd(
            $impersonationData['user_id'],
            $impersonationData['user_type'],
            $duration
        );

        // Clear impersonation session
        Session::forget(self::IMPERSONATION_SESSION_KEY);

        return response()->json(['message' => 'Impersonation stopped']);
    }

    /**
     * Get current impersonation status
     */
    public function status()
    {
        $impersonationData = Session::get(self::IMPERSONATION_SESSION_KEY);

        if (!$impersonationData) {
            return response()->json(['impersonating' => false]);
        }

        // Check if impersonation has expired
        $startedAt = \Carbon\Carbon::parse($impersonationData['started_at']);
        if (now()->diffInSeconds($startedAt) > self::IMPERSONATION_TIMEOUT) {
            Session::forget(self::IMPERSONATION_SESSION_KEY);
            return response()->json(['impersonating' => false, 'expired' => true]);
        }

        $user = $this->getUserByType($impersonationData['user_type'], $impersonationData['user_id']);

        return response()->json([
            'impersonating' => true,
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name ?? ($user->first_name ?? '') . ' ' . ($user->last_name ?? ''),
                'email' => $user->email,
                'type' => $impersonationData['user_type'],
            ] : null,
            'started_at' => $impersonationData['started_at'],
            'admin' => [
                'id' => $impersonationData['admin_id'],
                'email' => $impersonationData['admin_email'],
            ],
        ]);
    }

    /**
     * Get user by type and ID
     */
    private function getUserByType(string $type, int $id)
    {
        return match ($type) {
            'admin' => AdminUser::find($id),
            'doctor' => Doctor::find($id),
            'patient' => Patient::find($id),
            'canvasser' => Canvasser::find($id),
            'nurse' => Nurse::find($id),
            'customer_care' => CustomerCare::find($id),
            default => null,
        };
    }
}
