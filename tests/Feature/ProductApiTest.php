<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_all_active_products()
    {
        $vendor = Vendor::factory()->create();
        Product::factory(5)->for($vendor)->create(['status' => 'active']);
        Product::factory(2)->for($vendor)->create(['status' => 'inactive']);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'status',
                     'message',
                     'data' => ['*' => ['id', 'name', 'price', 'stock_quantity']],
                     'pagination' => ['total', 'per_page', 'current_page', 'last_page'],
                 ])
                 ->assertJson(['pagination' => ['total' => 5]]);
    }

    public function test_search_products_by_name()
    {
        $vendor = Vendor::factory()->create();
        Product::factory()->for($vendor)->create([
            'name' => 'Wireless Earbuds',
            'status' => 'active',
        ]);
        Product::factory()->for($vendor)->create([
            'name' => 'USB Cable',
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/products?search=earbuds');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.name', 'Wireless Earbuds');
    }

    public function test_get_single_product()
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->for($vendor)->create(['status' => 'active']);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.id', $product->id)
                 ->assertJsonPath('data.name', $product->name);
    }

    public function test_cannot_get_inactive_product()
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->for($vendor)->create(['status' => 'inactive']);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(404);
    }

    public function test_vendor_can_create_product()
    {
        $vendor = Vendor::factory()->create();

        $response = $this->actingAs($vendor, 'sanctum')
                         ->postJson('/api/vendor/products', [
                             'name' => 'New Product',
                             'description' => 'A great product',
                             'price' => 25000,
                             'stock_quantity' => 100,
                             'status' => 'active',
                         ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.name', 'New Product')
                 ->assertJsonPath('data.vendor_id', $vendor->id);

        $this->assertDatabaseHas('products', [
            'vendor_id' => $vendor->id,
            'name' => 'New Product',
        ]);
    }

    public function test_vendor_can_update_own_product()
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->for($vendor)->create(['price' => 10000]);

        $response = $this->actingAs($vendor, 'sanctum')
                         ->putJson("/api/vendor/products/{$product->id}", [
                             'price' => 15000,
                         ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.price', 15000);

        $this->assertEquals($product->fresh()->price, 15000);
    }

    public function test_vendor_cannot_update_other_vendor_product()
    {
        $vendor1 = Vendor::factory()->create();
        $vendor2 = Vendor::factory()->create();
        $product = Product::factory()->for($vendor1)->create();

        $response = $this->actingAs($vendor2, 'sanctum')
                         ->putJson("/api/vendor/products/{$product->id}", [
                             'price' => 15000,
                         ]);

        $response->assertStatus(403)
                 ->assertJsonPath('message', 'You do not own this product');
    }

    public function test_vendor_can_delete_own_product()
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->for($vendor)->create();

        $response = $this->actingAs($vendor, 'sanctum')
                         ->deleteJson("/api/vendor/products/{$product->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_get_vendor_products_requires_auth()
    {
        $response = $this->getJson('/api/vendor/products');

        $response->assertStatus(401);
    }

    public function test_vendor_can_get_own_products()
    {
        $vendor = Vendor::factory()->create();
        Product::factory(3)->for($vendor)->create();

        $response = $this->actingAs($vendor, 'sanctum')
                         ->getJson('/api/vendor/products');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data')
                 ->assertJsonStructure(['data' => ['*' => ['id', 'name', 'vendor_id']]]);
    }

    public function test_create_product_validates_input()
    {
        $vendor = Vendor::factory()->create();

        $response = $this->actingAs($vendor, 'sanctum')
                         ->postJson('/api/vendor/products', [
                             'name' => '',
                             'price' => -100,
                             'stock_quantity' => -5,
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'price', 'stock_quantity']);
    }
}
