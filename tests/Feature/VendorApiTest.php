<?php

namespace Tests\Feature;

use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_register()
    {
        $response = $this->postJson('/api/vendor/register', [
            'name' => 'meyor pop',
            'email' => 'meyorpop@gmail.com',
            'password' => 'password123',
            'business_name' => 'John\'s Store',
            'phone' => '+234801234567',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.email', 'meyorpop@gmail.com')
            ->assertJsonPath('data.name', 'meyor pop')
            ->assertJsonStructure(['data' => ['id', 'name', 'email', 'business_name']]);

        $this->assertDatabaseHas('vendors', [
            'email' => 'meyorpop@gmail.com',
        ]);
    }

    public function test_cannot_register_duplicate_email()
    {
        Vendor::factory()->create(['email' => 'duplicate@example.com']);

        $response = $this->postJson('/api/vendor/register', [
            'name' => 'Another Vendor',
            'email' => 'duplicate@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('success', false);
    }

    public function test_registration_validates_input()
    {
        $response = $this->postJson('/api/vendor/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_vendor_can_login()
    {
        $vendor = Vendor::factory()->create([
            'email' => 'vendor@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/vendor/login', [
            'email' => 'vendor@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.vendor.email', 'vendor@example.com')
            ->assertJsonStructure(['data' => ['vendor', 'token']]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_login_fails_with_invalid_credentials()
    {
        Vendor::factory()->create([
            'email' => 'vendor@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/vendor/login', [
            'email' => 'vendor@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_login_fails_with_nonexistent_email()
    {
        $response = $this->postJson('/api/vendor/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401);
    }

    public function test_vendor_can_logout()
    {
        $vendor = Vendor::factory()->create();

        $response = $this->actingAs($vendor, 'sanctum')
            ->postJson('/api/vendor/logout');

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Logged out successfully');
    }

    public function test_can_get_current_vendor()
    {
        $vendor = Vendor::factory()->create();

        $response = $this->actingAs($vendor, 'sanctum')
            ->getJson('/api/vendor/me');

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $vendor->id)
            ->assertJsonPath('data.email', $vendor->email);
    }

    public function test_cannot_access_protected_routes_without_token()
    {
        $response = $this->getJson('/api/vendor/me');

        $response->assertStatus(401);
    }

    public function test_cannot_access_protected_routes_with_invalid_token()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
        ])->getJson('/api/vendor/me');

        $response->assertStatus(401);
    }
}
