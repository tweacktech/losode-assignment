<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\VendorResource;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Exception;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private AuthService $authService)
    {
    }

    /**
     * Register a new vendor
     *
     * POST /api/auth/register
     */
    public function register(RegisterRequest $request)
    {
        try {
            $vendor = $this->authService->register($request->validated());

            return $this->created(
                new VendorResource($vendor),
                'Vendor registered successfully'
            );
        } catch (Exception $e) {
            return $this->conflict($e->getMessage());
        }
    }

    /**
     * Login vendor
     *
     * POST /api/auth/login
     */
    public function login(LoginRequest $request)
    {
        try {
            return $result = $this->authService->login(
                $request->email,
                $request->password
            );

            return $this->success([
                'vendor' => new VendorResource($result['vendor']),
                'token' => $result['token'],
            ], 'Login successful');
        } catch (Exception $e) {
            \Log::error('Login failed: ' . $e->getMessage(), [
                'email' => $request->email,
                'exception' => $e,
            ]);
            return $this->unauthorized($e->getMessage());
        }
    }

    /**
     * Logout vendor (revoke token)
     *
     * POST /api/auth/logout
     */
    public function logout()
    {
        auth('sanctum')->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully');
    }

    /**
     * Get current authenticated vendor
     *
     * GET /api/auth/me
     */
    public function me()
    {
        return $this->success(
            new VendorResource(auth('sanctum')->user()),
            'Current vendor'
        );
    }

    public function feedback()
    {

        return $this->error('Route/Auth not found', 400);
    }
}
