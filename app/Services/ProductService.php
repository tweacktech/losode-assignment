<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Vendor;

/**
 * ProductService
 *
 * Handles product CRUD operations and business rules.
 */
class ProductService
{
    /**
     * Create a new product for a vendor
     */
    public function create(Vendor $vendor, array $data): Product
    {
        return $vendor->products()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'stock_quantity' => $data['stock_quantity'] ?? 0,
            'status' => $data['status'] ?? 'active',
        ]);
    }

    /**
     * Update a product
     */
    public function update(Product $product, array $data): Product
    {
        $product->update(array_filter([
            'name' => $data['name'] ?? null,
            'description' => $data['description'] ?? null,
            'price' => $data['price'] ?? null,
            'stock_quantity' => $data['stock_quantity'] ?? null,
            'status' => $data['status'] ?? null,
        ], fn($value) => $value !== null));

        return $product->fresh();
    }

    /**
     * Delete a product
     */
    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    /**
     * Get all products for a vendor with optional pagination
     */
    public function getVendorProducts(Vendor $vendor, int $perPage = 15)
    {
        return $vendor->products()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get all active products with search
     */
    public function getActiveProducts(string $search = null, int $perPage = 15)
    {
        $query = Product::active();

        if ($search) {
            $query->search($search);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
