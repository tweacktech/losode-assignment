<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use App\Services\InventoryService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    use ApiResponse;

    public function __construct(private InventoryService $inventoryService)
    {
    }

    /**
     * Place an order (authenticated user can order)
     *
     * POST /api/orders
     *
     * This endpoint handles atomic inventory reduction.
     * Multiple concurrent requests are safely handled using
     * database-level locking and transactions.
     */
    public function store(CreateOrderRequest $request)
    {
        try {
            $product = Product::findOrFail($request->product_id);

            // Check if product is available
            if (!$product->isAvailable()) {
                return $this->error(
                    'Product is not available for purchase',
                    400,
                    null,
                    'PRODUCT_UNAVAILABLE'
                );
            }

            // Get authenticated user
            $user = auth('sanctum')->user();

            // Create order with atomic inventory reduction
            $order = DB::transaction(function () use ($product, $request, $user) {
                // Lock product row to prevent concurrent issues
                $product = Product::lockForUpdate()->find($product->id);

                // Validate stock availability under lock
                if (!$product->hasStock($request->quantity)) {
                    throw new Exception(
                        'Insufficient stock. Available: ' . $product->stock_quantity,
                        'INSUFFICIENT_STOCK'
                    );
                }

                // Create the order
                $order = Order::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'vendor_id' => $product->vendor_id,
                    'quantity' => $request->quantity,
                    'unit_price' => $product->price,
                    'total_price' => $product->price * $request->quantity,
                    'status' => 'completed',
                    'notes' => $request->notes ?? null,
                ]);

                // Atomically reduce stock
                $product->decrement('stock_quantity', $request->quantity);

                return $order;
            });

            return $this->created(
                new OrderResource($order->load('product', 'vendor', 'user')),
                'Order placed successfully'
            );
        } catch (Exception $e) {
            // Handle insufficient stock
            if ($e->getCode() === 'INSUFFICIENT_STOCK') {
                return $this->error(
                    $e->getMessage(),
                    400,
                    null,
                    'INSUFFICIENT_STOCK'
                );
            }

            return $this->serverError($e->getMessage());
        }
    }

    /**
     * Get user's orders (authenticated)
     *
     * GET /api/orders/my-orders
     */
    public function myOrders()
    {
        $user = auth('sanctum')->user();

        $orders = $user->orders()
            ->with(['product', 'vendor'])
            ->recent()
            ->paginate(15);

        return $this->paginated($orders, 'User orders retrieved successfully');
    }

    /**
     * Get single order details (authenticated user)
     *
     * GET /api/orders/{id}
     */
    public function show(Order $order)
    {
        $user = auth('sanctum')->user();

        // Check if order belongs to user
        if ($order->user_id !== $user->id) {
            return $this->forbidden('You do not have access to this order');
        }

        return $this->success(
            new OrderResource($order->load('product', 'vendor', 'user')),
            'Order retrieved successfully'
        );
    }

    /**
     * Cancel an order (if possible)
     *
     * PUT /api/orders/{id}/cancel
     */
    public function cancel(Order $order)
    {
        $user = auth('sanctum')->user();

        // Verify ownership
        if ($order->user_id !== $user->id) {
            return $this->forbidden('You cannot cancel this order');
        }

        // Check if order can be cancelled
        if (!$order->canBeCancelled()) {
            return $this->error(
                'This order cannot be cancelled in its current status',
                400,
                null,
                'CANNOT_CANCEL_ORDER'
            );
        }

        // Cancel and restore stock atomically
        DB::transaction(function () use ($order) {
            $order->update(['status' => 'cancelled']);

            // Restore stock
            $order->product->increment('stock_quantity', $order->quantity);
        });

        return $this->success(
            new OrderResource($order),
            'Order cancelled successfully'
        );
    }

    /**
     * Get order statistics
     *
     * GET /api/orders/stats
     */
    public function stats()
    {
        $stats = [
            'total_orders' => Order::count(),
            'completed_orders' => Order::completed()->count(),
            'pending_orders' => Order::pending()->count(),
            'cancelled_orders' => Order::cancelled()->count(),
            'total_revenue' => Order::completed()->sum('total_price'),
            'average_order_value' => Order::completed()->avg('total_price'),
        ];

        return $this->success($stats, 'Order statistics');
    }

    /**
     * Get recent orders (public - last 10 completed)
     *
     * GET /api/orders/recent
     */
    public function recent()
    {
        $orders = Order::completed()
            ->with(['product', 'vendor'])
            ->recent()
            ->limit(10)
            ->get();

        return $this->success(
            OrderResource::collection($orders),
            'Recent orders retrieved successfully'
        );
    }

    /**
     * Get vendor's orders
     *
     * GET /api/vendor/orders
     */
    public function vendorOrders()
    {
        $vendor = auth('sanctum')->user();

        $orders = Order::where('vendor_id', $vendor->id)
            ->with(['product', 'user'])
            ->recent()
            ->paginate(15);

        return $this->paginated($orders, 'Vendor orders retrieved successfully');
    }

    /**
     * Update order status (vendor only)
     *
     * PUT /api/vendor/orders/{id}/status
     */
    public function updateStatus(Order $order)
    {
        $vendor = auth('sanctum')->user();

        // Verify vendor ownership
        if ($order->vendor_id !== $vendor->id) {
            return $this->forbidden('You do not have access to this order');
        }

        $validated = request()->validate([
            'status' => ['required', 'in:pending,processing,shipped,completed,cancelled'],
        ]);

        // If cancelling, restore stock
        if ($validated['status'] === 'cancelled' && !$order->canBeCancelled()) {
            return $this->error(
                'Cannot cancel orders in current status',
                400,
                null,
                'CANNOT_CANCEL'
            );
        }

        if ($validated['status'] === 'cancelled') {
            $order->product->increment('stock_quantity', $order->quantity);
        }

        $order->update(['status' => $validated['status']]);

        return $this->success(
            new OrderResource($order),
            'Order status updated successfully'
        );
    }
}

