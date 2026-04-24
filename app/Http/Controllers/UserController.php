<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    use ApiResponse;

    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }


    public function register(RegisterRequest $request)
    {
        try {
            $user = $this->userService->register($request->validated());

            return $this->created(
                new UserResource($user),
                'User registered successfully'
            );
        } catch (Exception $e) {
            $statusCode = $e->getCode() >= 100 && $e->getCode() <= 599 ? $e->getCode() : 500;
            return $this->error($e->getMessage(), $statusCode);
        }
    }


    public function login(LoginRequest $request)
    {
        try {
            $result = $this->userService->login($request->validated());

            return $this->success([
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
                'token_type' => 'Bearer',
            ], 'Login successful');
        } catch (Exception $e) {
            $statusCode = $e->getCode() >= 100 && $e->getCode() <= 599 ? $e->getCode() : 401;
            return $this->error($e->getMessage(), $statusCode);
        }
    }


    public function me()
    {
        try {
            $user = $this->userService->getAuthenticatedUser(Auth::user());

            return $this->success(
                new UserResource($user),
                'Current user information retrieved successfully'
            );
        } catch (Exception $e) {
            return $this->serverError($e->getMessage());
        }
    }


    public function updateProfile(UpdateProfileRequest $request)
    {
        try {
            $user = $this->userService->updateProfile(
                Auth::user(),
                $request->validated()
            );

            return $this->success(
                new UserResource($user),
                'Profile updated successfully'
            );
        } catch (Exception $e) {
            return $this->serverError($e->getMessage());
        }
    }


    public function getOrders(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $orders = $this->userService->getUserOrders(Auth::user(), $perPage);

            return $this->paginated($orders, 'User orders retrieved successfully');
        } catch (Exception $e) {
            return $this->serverError($e->getMessage());
        }
    }


    public function getStats()
    {
        try {
            $stats = $this->userService->getUserStats(Auth::user());

            return $this->success($stats, 'User statistics retrieved successfully');
        } catch (Exception $e) {
            return $this->serverError($e->getMessage());
        }
    }


    public function logout()
    {
        try {
            $this->userService->logout(Auth::user());

            return $this->success(null, 'Logged out successfully');
        } catch (Exception $e) {
            return $this->serverError($e->getMessage());
        }
    }


    public function logoutAllDevices()
    {
        try {
            $this->userService->logoutAllDevices(Auth::user());

            return $this->success(null, 'Logged out from all devices successfully');
        } catch (Exception $e) {
            return $this->serverError($e->getMessage());
        }
    }


    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $users = $this->userService->getAllUsers($perPage);

            return UserResource::collection($users)->additional([
                'success' => true,
                'message' => 'Users retrieved successfully'
            ]);
        } catch (Exception $e) {
            return $this->serverError($e->getMessage());
        }
    }


    public function show($id)
    {
        try {
            $user = $this->userService->getUserById($id);

            return $this->success(
                new UserResource($user),
                'User retrieved successfully'
            );
        } catch (Exception $e) {
            return $this->notFound('User not found');
        }
    }


    public function deactivateAccount()
    {
        try {
            $this->userService->deactivateAccount(Auth::user());

            return $this->success(null, 'Account deactivated successfully');
        } catch (Exception $e) {
            return $this->serverError($e->getMessage());
        }
    }


    public function reactivateAccount()
    {
        try {
            $this->userService->reactivateAccount(Auth::user());

            return $this->success(null, 'Account reactivated successfully');
        } catch (Exception $e) {
            return $this->serverError($e->getMessage());
        }
    }


    public function deleteAccount()
    {
        try {
            $this->userService->deleteAccount(Auth::user());

            return $this->success(null, 'Account deleted successfully');
        } catch (Exception $e) {
            return $this->serverError($e->getMessage());
        }
    }
}
