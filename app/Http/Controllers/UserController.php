<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ApiResponse;

    /**
     * Register a new user (customer)
     *
     * POST /api/users/register
     */
    public function register(RegisterRequest $request)
    {
        try {
            // Check if email already exists
            if (User::where('email', $request->email)->exists()) {
                return $this->conflict('Email already registered');
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone ?? null,
                'address' => $request->address ?? null,
                'city' => $request->city ?? null,
                'state' => $request->state ?? null,
                'postal_code' => $request->postal_code ?? null,
                'is_active' => true,
            ]);

            return $this->created(
                new UserResource($user),
                'User registered successfully'
            );
        } catch (Exception $e) {
            return $this->serverError($e->getMessage());
        }
    }

    /**
     * Login user (customer)
     *
     * POST /api/users/login
     */
    public function login(LoginRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->unauthorized('Invalid email or password');
            }

            if (!$user->isActive()) {
                return $this->forbidden('Your account is inactive');
            }

            $token = $user->createToken('api-token')->plainTextToken;

            return $this->success([
                'user' => new UserResource($user),
                'token' => $token,
            ], 'Login successful');
        } catch (Exception $e) {
            return $this->serverError($e->getMessage());
        }
    }

    /**
     * Get current authenticated user
     *
     * GET /api/users/me
     */
    public function me()
    {
        return $this->success(
            new UserResource(auth('sanctum')->user()),
            'Current user information'
        );
    }

    /**
     * Update user profile
     *
     * PUT /api/users/profile
     */
    public function updateProfile()
    {
        $user = auth('sanctum')->user();

        $validated = request()->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:20'],
            'address' => ['sometimes', 'string', 'max:255'],
            'city' => ['sometimes', 'string', 'max:100'],
            'state' => ['sometimes', 'string', 'max:100'],
            'postal_code' => ['sometimes', 'string', 'max:20'],
        ]);

        $user->update(array_filter($validated));

        return $this->success(
            new UserResource($user),
            'Profile updated successfully'
        );
    }

    /**
     * Get user's orders
     *
     * GET /api/users/orders
     */
    public function getOrders()
    {
        $user = auth('sanctum')->user();

        $orders = $user->orders()
            ->with(['product', 'vendor'])
            ->recent()
            ->paginate(15);

        return $this->paginated($orders, 'User orders retrieved');
    }

    /**
     * Get user's order statistics
     *
     * GET /api/users/stats
     */
    public function getStats()
    {
        $user = auth('sanctum')->user();

        $stats = [
            'total_orders' => $user->orders()->count(),
            'completed_orders' => $user->orders()->completed()->count(),
            'pending_orders' => $user->orders()->pending()->count(),
            'total_spent' => $user->orders()->completed()->sum('total_price'),
            'average_order_value' => $user->orders()->completed()->avg('total_price'),
        ];

        return $this->success($stats, 'User statistics');
    }

    /**
     * Logout user
     *
     * POST /api/users/logout
     */
    public function logout()
    {
        auth('sanctum')->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully');
    }

    /**
     * Get all users (admin only - optional)
     *
     * GET /api/users
     */
    public function index()
    {
        $users = User::where('is_active', true)
            ->paginate(15);

        return $this->paginated($users, 'Users retrieved');
    }

    /**
     * Get single user by ID
     *
     * GET /api/users/{id}
     */
    public function show(User $user)
    {
        return $this->success(
            new UserResource($user),
            'User retrieved successfully'
        );
    }

    /**
     * Deactivate user account
     *
     * DELETE /api/users/account
     */
    public function deactivateAccount()
    {
        $user = auth('sanctum')->user();
        $user->update(['is_active' => false]);

        return $this->success(null, 'Account deactivated successfully');
    }
}
