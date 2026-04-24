<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/**
 * Public Routes (No Authentication Required)
 */
Route::prefix('')->group(function () {
    // Vendor Authentication
    Route::post('/vendor/register', [AuthController::class, 'register']);
    Route::post('/vendor/login', [AuthController::class, 'login']);

    // User/Customer Authentication
    Route::post('/users/register', [UserController::class, 'register']);
    Route::post('/users/login', [UserController::class, 'login']);

    // Public Product Access
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);

    // Public Order Statistics
    Route::get('/orders/stats', [OrderController::class, 'stats']);
    Route::get('/orders/recent', [OrderController::class, 'recent']);
});

/**
 * Protected Routes - Users/Customers (Authentication Required)
 */
Route::prefix('')->middleware(['auth:sanctum'])->group(function () {
    // User Profile Management
    Route::prefix('users')->group(function () {
        Route::post('/logout', [UserController::class, 'logout']);
        Route::get('/me', [UserController::class, 'me']);
        Route::put('/profile', [UserController::class, 'updateProfile']);
        Route::delete('/account', [UserController::class, 'deactivateAccount']);
        Route::get('/stats', [UserController::class, 'getStats']);
    });

    // User Orders
    Route::prefix('orders')->group(function () {
        Route::get('/my-orders', [OrderController::class, 'myOrders']);
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/{order}', [OrderController::class, 'show']);
        Route::put('/{order}/cancel', [OrderController::class, 'cancel']);
    });
});

/**
 * Protected Routes - Vendors (Authentication Required)
 */
Route::prefix('')->middleware(['auth:sanctum'])->group(function () {
    // Auth
    Route::post('/vendor/logout', [AuthController::class, 'logout']);
    Route::get('/vendor/me', [AuthController::class, 'me']);

    // Vendor Product Management
    Route::prefix('vendor')->group(function () {
        Route::get('/products', [ProductController::class, 'vendorProducts']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);

        // Vendor Orders Management
        Route::get('/orders', [OrderController::class, 'vendorOrders']);
        Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus']);
    });
});

// feedback: The API routes are well-structured and follow RESTful conventions. The separation of public and protected routes is clear, and the use of middleware for authentication is appropriate. The endpoints cover a wide range of functionalities for both users and vendors, making the API comprehensive for an e-commerce platform.
Route::fallback(function () {
    return response()->json(['message' => 'Route not found'], 404);
});
