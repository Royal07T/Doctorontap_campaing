<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Canvasser;
use App\Models\Nurse;
use App\Models\CustomerCare;
use App\Models\CareGiver;
use App\Services\ActivityLogService;
use App\Services\UnifiedUserUpdateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    protected ActivityLogService $activityLogService;
    protected UnifiedUserUpdateService $userUpdateService;

    public function __construct(
        ActivityLogService $activityLogService,
        UnifiedUserUpdateService $userUpdateService
    ) {
        $this->activityLogService = $activityLogService;
        $this->userUpdateService = $userUpdateService;
    }

    /**
     * Display unified user management interface
     */
    public function index(Request $request)
    {
        $userType = $request->get('type', 'all');
        $search = $request->get('search');

        $users = collect();

        switch ($userType) {
            case 'admin':
                $users = $this->getAdmins($search);
                break;
            case 'doctor':
                $users = $this->getDoctors($search);
                break;
            case 'patient':
                $users = $this->getPatients($search);
                break;
            case 'canvasser':
                $users = $this->getCanvassers($search);
                break;
            case 'nurse':
                $users = $this->getNurses($search);
                break;
            case 'customer_care':
                $users = $this->getCustomerCares($search);
                break;
            case 'care_giver':
                $users = $this->getCareGivers($search);
                break;
            default:
                // Get counts for all types - no users to display
                break;
        }

        $stats = [
            'admins' => AdminUser::count(),
            'doctors' => Doctor::count(),
            'patients' => Patient::count(),
            'canvassers' => Canvasser::count(),
            'nurses' => Nurse::count(),
            'customer_cares' => CustomerCare::count(),
            'care_givers' => CareGiver::count(),
        ];

        // Log access
        $this->activityLogService->log('viewed', null, null, null, [
            'section' => 'user_management',
            'user_type' => $userType,
        ]);

        return view('super-admin.users.index', compact('users', 'userType', 'stats', 'search'));
    }

    /**
     * Get admins
     */
    private function getAdmins(?string $search)
    {
        $query = AdminUser::query();
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        return $query->latest()->paginate(20);
    }

    /**
     * Get doctors
     */
    private function getDoctors(?string $search)
    {
        $query = Doctor::query();
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('specialization', 'like', "%{$search}%");
            });
        }
        
        return $query->latest()->paginate(20);
    }

    /**
     * Get patients
     */
    private function getPatients(?string $search)
    {
        $query = Patient::query();
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        return $query->latest()->paginate(20);
    }

    /**
     * Get canvassers
     */
    private function getCanvassers(?string $search)
    {
        $query = Canvasser::query();
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        return $query->latest()->paginate(20);
    }

    /**
     * Get nurses
     */
    private function getNurses(?string $search)
    {
        $query = Nurse::query();
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        return $query->latest()->paginate(20);
    }

    /**
     * Get customer care
     */
    private function getCustomerCares(?string $search)
    {
        $query = CustomerCare::query();
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        return $query->latest()->paginate(20);
    }

    /**
     * Get care givers
     */
    private function getCareGivers(?string $search)
    {
        $query = CareGiver::query();
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        return $query->latest()->paginate(20);
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(Request $request, string $type, int $id)
    {
        $user = $this->getUserByType($type, $id);
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $isActive = $user->is_active ?? true;
        $user->is_active = !$isActive;
        $user->save();

        $this->activityLogService->log(
            'updated',
            get_class($user),
            $user->id,
            ['is_active' => [$isActive, !$isActive]],
            ['action' => 'toggle_status']
        );

        return response()->json([
            'message' => 'Status updated successfully',
            'is_active' => $user->is_active,
        ]);
    }

    /**
     * Update user information (email, name, password, role-specific data)
     * 
     * This method updates both the users table (source of truth) and role-specific table
     */
    public function updateUser(Request $request, string $type, int $id)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'password' => 'sometimes|string|min:8|confirmed',
            // Role-specific fields can be added here based on type
        ]);

        $result = $this->userUpdateService->updateUser($type, $id, $request->only([
            'name', 'email', 'password'
        ]));

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 400);
        }

        // Log the update
        $this->activityLogService->log(
            'updated',
            get_class($result['roleModel']),
            $result['roleModel']->id,
            $request->only(['name', 'email']),
            ['action' => 'user_update', 'user_id' => $result['user']->id]
        );

        return response()->json([
            'message' => $result['message'],
            'user' => $result['user'],
            'role_model' => $result['roleModel'],
        ]);
    }

    /**
     * Update user email only
     */
    public function updateEmail(Request $request, string $type, int $id)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $result = $this->userUpdateService->updateEmail($type, $id, $request->email);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 400);
        }

        $this->activityLogService->log(
            'updated',
            get_class($result['roleModel']),
            $result['roleModel']->id,
            ['email' => $request->email],
            ['action' => 'email_update', 'user_id' => $result['user']->id]
        );

        return response()->json([
            'message' => 'Email updated successfully',
            'user' => $result['user'],
        ]);
    }

    /**
     * Reset user password
     * 
     * Updated to use unified user system
     */
    public function resetPassword(Request $request, string $type, int $id)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $result = $this->userUpdateService->updatePassword($type, $id, $request->password);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 400);
        }

        $this->activityLogService->log(
            'updated',
            get_class($result['roleModel']),
            $result['roleModel']->id,
            null,
            ['action' => 'password_reset', 'user_id' => $result['user']->id]
        );

        return response()->json([
            'message' => 'Password reset successfully',
            'user' => $result['user'],
        ]);
    }

    /**
     * Get user information including unified user data
     */
    public function getUserInfo(string $type, int $id)
    {
        $info = $this->userUpdateService->getUserInfo($type, $id);

        if (!$info) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($info);
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
            'care_giver' => CareGiver::find($id),
            default => null,
        };
    }
}
