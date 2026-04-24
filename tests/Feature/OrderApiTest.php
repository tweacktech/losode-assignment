<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_place_order_for_product()
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->for($vendor)->create([
            'stock_quantity' => 10,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.quantity', 2)
                 ->assertJsonPath('data.total_price', $product->price * 2)
                 ->assertJsonPath('data.status', 'completed');

        $this->assertDatabaseHas('orders', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_order_reduces_stock()
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->for($vendor)->create([
            'stock_quantity' => 10,
        ]);

        $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $this->assertEquals($product->fresh()->stock_quantity, 7);
    }

    public function test_rejects_order_with_insufficient_stock()
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->for($vendor)->create([
            'stock_quantity' => 2,
        ]);

        $response = $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $response->assertStatus(400)
                 ->assertJsonPath('code', 'INSUFFICIENT_STOCK')
                 ->assertJsonPath('success', false);

        // Stock should not change
        $this->assertEquals($product->fresh()->stock_quantity, 2);
    }

    public function test_rejects_order_for_nonexistent_product()
    {
        $response = $this->postJson('/api/orders', [
            'product_id' => 99999,
            'quantity' => 1,
        ]);

        $response->assertStatus(422);
    }

    public function test_rejects_invalid_quantity()
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->for($vendor)->create();

        $response = $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'quantity' => 0,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('quantity');
    }

    public function test_order_total_price_calculation()
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->for($vendor)->create([
            'price' => 5000,
            'stock_quantity' => 100,
        ]);

        $response = $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $response->assertJsonPath('data.total_price', 50000);
    }

    public function test_multiple_orders_accumulate()
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->for($vendor)->create([
            'stock_quantity' => 10,
        ]);

        // First order
        $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        // Second order
        $response = $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'quantity' => 4,
        ]);

        $response->assertStatus(201);
        $this->assertEquals($product->fresh()->stock_quantity, 3);
        $this->assertEquals(Order::count(), 2);
    }

    public function test_order_cannot_exceed_total_stock()
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->for($vendor)->create([
            'stock_quantity' => 5,
        ]);

        // Try to order all stock
        $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        // Try to order more
        $response = $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(400)
                 ->assertJsonPath('code', 'INSUFFICIENT_STOCK');
    }
}
