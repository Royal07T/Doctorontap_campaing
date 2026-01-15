<?php

namespace App\Services;

use App\Models\User;
use App\Models\Patient;
use App\Models\AdminUser;
use App\Models\Canvasser;
use App\Models\Nurse;
use App\Models\Doctor;
use App\Models\CustomerCare;
use App\Models\CareGiver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UnifiedUserUpdateService
{
    /**
     * Update user information in the unified system
     * 
     * This method updates both the users table (source of truth for email/password)
     * and the role-specific table (for role-specific data)
     * 
     * @param string $roleType The role type (patient, admin, doctor, etc.)
     * @param int $roleId The ID of the role-specific record
     * @param array $data The data to update
     * @return array ['success' => bool, 'message' => string, 'user' => User|null, 'roleModel' => Model|null]
     */
    public function updateUser(string $roleType, int $roleId, array $data): array
    {
        try {
            DB::beginTransaction();

            // Get the role-specific model
            $roleModel = $this->getRoleModel($roleType, $roleId);
            
            if (!$roleModel) {
                return [
                    'success' => false,
                    'message' => 'User not found',
                    'user' => null,
                    'roleModel' => null,
                ];
            }

            // Separate user data (email, password, name) from role-specific data
            $userData = [];
            $roleData = [];

            // Fields that belong to users table
            $userFields = ['email', 'password', 'name'];
            
            foreach ($data as $key => $value) {
                if (in_array($key, $userFields)) {
                    $userData[$key] = $value;
                } else {
                    $roleData[$key] = $value;
                }
            }

            // Handle password hashing if provided
            if (isset($userData['password']) && !empty($userData['password'])) {
                $userData['password'] = Hash::make($userData['password']);
            } else {
                unset($userData['password']);
            }

            // Update or create user record
            $user = null;
            if ($roleModel->user_id) {
                // User exists, update it
                $user = User::find($roleModel->user_id);
                if ($user) {
                    $user->update($userData);
                }
            } else {
                // No user record exists, create one
                $userData['role'] = $roleType;
                $userData['email_verified_at'] = $roleModel->email_verified_at ?? null;
                $user = User::create($userData);
                
                // Link the role model to the user
                $roleModel->user_id = $user->id;
                $roleModel->save();
            }

            // Update role-specific data
            if (!empty($roleData)) {
                // Also update name in role table if it was provided (for backward compatibility)
                if (isset($userData['name'])) {
                    $roleData['name'] = $userData['name'];
                }
                
                $roleModel->update($roleData);
            } else if (isset($userData['name'])) {
                // Only name was updated, update role table too
                $roleModel->update(['name' => $userData['name']]);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'User updated successfully',
                'user' => $user->fresh(),
                'roleModel' => $roleModel->fresh(),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update unified user', [
                'role_type' => $roleType,
                'role_id' => $roleId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage(),
                'user' => null,
                'roleModel' => null,
            ];
        }
    }

    /**
     * Update only email address
     * 
     * @param string $roleType
     * @param int $roleId
     * @param string $email
     * @return array
     */
    public function updateEmail(string $roleType, int $roleId, string $email): array
    {
        // Validate email uniqueness in users table
        $existingUser = User::where('email', $email)->first();
        
        if ($existingUser) {
            $roleModel = $this->getRoleModel($roleType, $roleId);
            
            // If the existing user is the same user, it's okay
            if ($roleModel && $roleModel->user_id && $existingUser->id === $roleModel->user_id) {
                // Same user, just update email
                return $this->updateUser($roleType, $roleId, ['email' => $email]);
            }
            
            // Different user with same email - conflict
            return [
                'success' => false,
                'message' => 'Email already exists for another user',
                'user' => null,
                'roleModel' => null,
            ];
        }

        return $this->updateUser($roleType, $roleId, ['email' => $email]);
    }

    /**
     * Update only password
     * 
     * @param string $roleType
     * @param int $roleId
     * @param string $password
     * @return array
     */
    public function updatePassword(string $roleType, int $roleId, string $password): array
    {
        return $this->updateUser($roleType, $roleId, ['password' => $password]);
    }

    /**
     * Get role model by type and ID
     * 
     * @param string $roleType
     * @param int $id
     * @return Model|null
     */
    private function getRoleModel(string $roleType, int $id)
    {
        return match ($roleType) {
            'patient' => Patient::find($id),
            'admin' => AdminUser::find($id),
            'canvasser' => Canvasser::find($id),
            'nurse' => Nurse::find($id),
            'doctor' => Doctor::find($id),
            'customer_care' => CustomerCare::find($id),
            'care_giver' => CareGiver::find($id),
            default => null,
        };
    }

    /**
     * Get user information including role-specific data
     * 
     * @param string $roleType
     * @param int $roleId
     * @return array|null
     */
    public function getUserInfo(string $roleType, int $roleId): ?array
    {
        $roleModel = $this->getRoleModel($roleType, $roleId);
        
        if (!$roleModel) {
            return null;
        }

        $user = $roleModel->user;
        
        return [
            'user' => $user,
            'role_model' => $roleModel,
            'email' => $user ? $user->email : $roleModel->email,
            'name' => $user ? $user->name : $roleModel->name,
            'role' => $user ? $user->role : $roleType,
        ];
    }
}

