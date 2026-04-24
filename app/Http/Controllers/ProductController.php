<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use App\Traits\ApiResponse;

class ProductController extends Controller
{
    use ApiResponse;

    public function __construct(private ProductService $productService)
    {
    }

    /**
     * Get all active products (public endpoint)
     * 
     * GET /api/products?search=query&page=1
     */
    public function index()
    {
        $search = request()->query('search');
        $paginator = $this->productService->getActiveProducts($search);

        return $this->paginated(
            $paginator,
            'Products retrieved successfully'
        );
    }

    /**
     * Get a single product (public endpoint)
     * 
     * GET /api/products/{id}
     */
    public function show(Product $product)
    {
        // Only show active products
        if ($product->status !== 'active') {
            return $this->notFound('Product not found');
        }

        return $this->success(
            new ProductResource($product),
            'Product retrieved successfully'
        );
    }

    /**
     * Create a new product (vendor only)
     * 
     * POST /api/vendor/products
     */
    public function store(StoreProductRequest $request)
    {
        $vendor = auth('sanctum')->user();

        $product = $this->productService->create($vendor, $request->validated());

        return $this->created(
            new ProductResource($product),
            'Product created successfully'
        );
    }

    /**
     * Update a product (vendor only)
     * 
     * PUT /api/vendor/products/{id}
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $vendor = auth('sanctum')->user();

        // Verify ownership
        if (!$vendor->ownsProduct($product)) {
            return $this->forbidden('You do not own this product');
        }

        $product = $this->productService->update($product, $request->validated());

        return $this->success(
            new ProductResource($product),
            'Product updated successfully'
        );
    }

    /**
     * Delete a product (vendor only)
     * 
     * DELETE /api/vendor/products/{id}
     */
    public function destroy(Product $product)
    {
        $vendor = auth('sanctum')->user();

        // Verify ownership
        if (!$vendor->ownsProduct($product)) {
            return $this->forbidden('You do not own this product');
        }

        $this->productService->delete($product);

        return $this->success(null, 'Product deleted successfully');
    }

    /**
     * Get vendor's products
     * 
     * GET /api/vendor/products
     */
    public function vendorProducts()
    {
        $vendor = auth('sanctum')->user();
        $paginator = $this->productService->getVendorProducts($vendor);

        return $this->paginated(
            $paginator,
            'Vendor products retrieved successfully'
        );
    }
}