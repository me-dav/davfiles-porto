<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * POST /api/v1/auth/login
     *
     * Body: { email, password }
     * Returns: { token, user }
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->error('Invalid credentials', 401);
        }

        // Delete old tokens for this user (one active session at a time)
        // Comment this out if you want multi-device login
        $user->tokens()->delete();

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ], 'Login successful');
    }

    /**
     * POST /api/v1/auth/logout
     *
     * Requires: Authorization: Bearer {token}
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke only the current token
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully');
    }

    /**
     * GET /api/v1/auth/me
     *
     * Returns the currently authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->success([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ], 'Authenticated user fetched');
    }
}