<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * InventoryService
 * 
 * Handles all inventory-related business logic.
 * Analogy: Like a warehouse manager ensuring stock accuracy
 * and preventing double-booking even during rush hours.
 */
class InventoryService
{
    /**
     * Create an order and atomically reduce stock
     * 
     * Uses database transactions to prevent race conditions
     * when multiple users order simultaneously.
     * 
     * @throws Exception
     */
    public function createOrder(Product $product, int $quantity): Order
    {
        return DB::transaction(function () use ($product, $quantity) {
            // Lock the row to prevent concurrent updates
            $product = Product::lockForUpdate()->find($product->id);

            // Validate stock availability
            if (!$product->hasStock($quantity)) {
                throw new Exception(
                    'Insufficient stock. Available: ' . $product->stock_quantity,
                    'INSUFFICIENT_STOCK'
                );
            }

            // Create order record
            $order = Order::create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'total_price' => $product->price * $quantity,
                'status' => 'completed',
            ]);

            // Reduce stock atomically within transaction
            $product->decrement('stock_quantity', $quantity);

            return $order;
        });
    }

    /**
     * Update product stock with validation
     */
    public function updateStock(Product $product, int $newQuantity): Product
    {
        if ($newQuantity < 0) {
            throw new Exception('Stock quantity cannot be negative', 'INVALID_STOCK');
        }

        $product->update(['stock_quantity' => $newQuantity]);

        return $product->fresh();
    }

    /**
     * Adjust stock (increase or decrease by delta)
     */
    public function adjustStock(Product $product, int $delta): Product
    {
        $newQuantity = $product->stock_quantity + $delta;

        if ($newQuantity < 0) {
            throw new Exception(
                'Operation would result in negative stock',
                'INVALID_ADJUSTMENT'
            );
        }

        return $this->updateStock($product, $newQuantity);
    }
}