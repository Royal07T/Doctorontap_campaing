<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\CustomerCare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerCareAuthController extends Controller
{
    /**
     * Login customer care
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $customerCare = CustomerCare::where('email', $request->email)->first();

        if (!$customerCare || !Hash::check($request->password, $customerCare->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$customerCare->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated.',
            ], 403);
        }

        // Create token with expiration and abilities
        $token = $customerCare->createToken(
            'customer-care-api-token',
            ['customer-care:read', 'customer-care:write', 'ticket:read', 'ticket:write'],
            now()->addDays(7) // Token expires in 7 days
        )->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'customer_care' => $customerCare,
                'token' => $token,
            ]
        ]);
    }
}

