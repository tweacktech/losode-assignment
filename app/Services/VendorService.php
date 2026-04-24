<?php

namespace App\Services;

use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;
use Exception;

/**
 * AuthService
 *
 * Manages vendor registration and authentication.
 */
class VendorService
{
    /**
     * Register a new vendor
     *
     * @throws Exception
     */
    public function register(array $credentials): Vendor
    {
        // Check if email already exists
        if (Vendor::where('email', $credentials['email'])->exists()) {
            throw new Exception('Email already registered', 'EMAIL_EXISTS');
        }

        $vendor = Vendor::create([
            'name' => $credentials['name'],
            'email' => $credentials['email'],
            'password' => Hash::make($credentials['password']),
            'business_name' => $credentials['business_name'] ?? $credentials['name'],
            'phone' => $credentials['phone'] ?? null,
        ]);

        return $vendor;
    }

    /**
     * Login vendor and return token
     *
     * @throws Exception
     */
    public function login(string $email, string $password): array
    {
        $vendor = Vendor::where('email', $email)->first();

        if (!$vendor || !Hash::check($password, $vendor->password)) {
            throw new Exception('Invalid email or password', '400');
        }

        $token = $vendor->createToken('api-token')->plainTextToken;

        return [
            'vendor' => $vendor,
            'token' => $token,
        ];
    }
}
