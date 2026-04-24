<?php

namespace App\Services;

use App\Models\User;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{
    /**
     * Register a new user
     *
     * @param array $data
     * @return User
     * @throws Exception
     */
    public function register(array $data): User
    {
        try {
            DB::beginTransaction();

            // Check if email already exists
            if (User::where('email', $data['email'])->exists()) {
                throw new Exception('Email already registered', 409);
            }

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'is_active' => true,
            ]);

            DB::commit();

            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('User registration failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Login user
     *
     * @param array $credentials
     * @return array
     * @throws Exception
     */
    public function login(array $credentials): array
    {
        try {
            $user = User::where('email', $credentials['email'])->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                throw new Exception('Invalid email or password', 401);
            }

            if (!$user->is_active) {
                throw new Exception('Your account is inactive. Please contact support.', 403);
            }

            // Revoke old tokens (optional)
            $user->tokens()->where('name', 'api-token')->delete();

            $token = $user->createToken('api-token')->plainTextToken;

            return [
                'user' => $user,
                'token' => $token,
            ];
        } catch (Exception $e) {
            Log::error('User login failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get authenticated user
     *
     * @param Authenticatable $user
     * @return User
     */
    public function getAuthenticatedUser(Authenticatable $user): User
    {
        return $user;
    }

    /**
     * Update user profile
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateProfile(User $user, array $data): User
    {
        try {
            DB::beginTransaction();

            $user->update(array_filter($data));

            DB::commit();

            return $user->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Profile update failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update user password
     *
     * @param User $user
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     * @throws Exception
     */
    public function updatePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw new Exception('Current password is incorrect', 400);
        }

        try {
            $user->password = Hash::make($newPassword);
            $user->save();

            // Revoke all tokens after password change (optional)
            $user->tokens()->delete();

            return true;
        } catch (Exception $e) {
            Log::error('Password update failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get user orders with pagination
     *
     * @param User $user
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUserOrders(User $user, int $perPage = 15)
    {
        return $user->orders()
            ->with(['product', 'vendor'])
            ->recent()
            ->paginate($perPage);
    }

    /**
     * Get user statistics
     *
     * @param User $user
     * @return array
     */
    public function getUserStats(User $user): array
    {

        return [
            'total_orders' => $user->orders()->count(),
            'completed_orders' => $user->orders()->completed()->count(),
            'pending_orders' => $user->orders()->pending()->count(),
            'total_spent' => $user->orders()->completed()->sum('total_price'),
            'average_order_value' => $user->orders()->completed()->avg('total_price'),
        ];

    }

    /**
     * Logout user (revoke current token)
     *
     * @param User $user
     * @return bool
     */
    public function logout(User $user): bool
    {
        try {
            $user->currentAccessToken()->delete();
            return true;
        } catch (Exception $e) {
            Log::error('Logout failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Logout from all devices (revoke all tokens)
     *
     * @param User $user
     * @return bool
     */
    public function logoutAllDevices(User $user): bool
    {
        try {
            $user->tokens()->delete();
            return true;
        } catch (Exception $e) {
            Log::error('Logout from all devices failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all active users with pagination
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllUsers(int $perPage = 15)
    {
        return User::where('is_active', true)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get user by ID
     *
     * @param int $id
     * @return User|null
     */
    public function getUserById(int $id): ?User
    {
        return User::findOrFail($id);
    }

    /**
     * Deactivate user account
     *
     * @param User $user
     * @return bool
     */
    public function deactivateAccount(User $user): bool
    {
        try {
            $user->update(['is_active' => false]);
            // Revoke all tokens when deactivating
            $user->tokens()->delete();
            return true;
        } catch (Exception $e) {
            Log::error('Account deactivation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reactivate user account
     *
     * @param User $user
     * @return bool
     */
    public function reactivateAccount(User $user): bool
    {
        try {
            $user->update(['is_active' => true]);
            return true;
        } catch (Exception $e) {
            Log::error('Account reactivation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete user account (soft delete)
     *
     * @param User $user
     * @return bool
     */
    public function deleteAccount(User $user): bool
    {
        try {
            // Revoke all tokens
            $user->tokens()->delete();
            // Soft delete the user
            return $user->delete();
        } catch (Exception $e) {
            Log::error('Account deletion failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Search users
     *
     * @param string $keyword
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function searchUsers(string $keyword, int $perPage = 15)
    {
        return User::where('name', 'LIKE', "%{$keyword}%")
            ->orWhere('email', 'LIKE', "%{$keyword}%")
            ->orWhere('phone', 'LIKE', "%{$keyword}%")
            ->paginate($perPage);
    }
}
