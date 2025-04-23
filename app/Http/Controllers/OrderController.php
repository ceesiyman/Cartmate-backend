<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Create a new order from cart items",
     *     tags={"Orders"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="shipping_address", type="string", example="123 Main St, City, Country"),
     *             @OA\Property(property="billing_address", type="string", example="123 Main St, City, Country"),
     *             @OA\Property(property="phone_number", type="string", example="+1234567890"),
     *             @OA\Property(property="notes", type="string", example="Please deliver in the morning")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Order")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'shipping_address' => 'required|string',
            'billing_address' => 'required|string',
            'phone_number' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        $cartItems = Cart::where('user_id', $request->user_id)
            ->join('products', 'carts.product_id', '=', 'products.id')
            ->select('carts.*', 'products.total_price as product_price')
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ], 422);
        }

        $totalAmount = $cartItems->sum(function ($item) {
            return $item->quantity * $item->product_price;
        });

        $order = Order::create([
            'user_id' => $request->user_id,
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'status' => 'pending',
            'total_amount' => $totalAmount,
            'shipping_address' => $request->shipping_address,
            'billing_address' => $request->billing_address,
            'phone_number' => $request->phone_number,
            'notes' => $request->notes
        ]);

        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->product_price
            ]);
        }

        // Clear the cart after order is created
        Cart::where('user_id', $request->user_id)->delete();

        return response()->json([
            'success' => true,
            'data' => $order->load('items.product')
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Get user's orders",
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of orders",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Order")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id'
        ]);

        $orders = Order::where('user_id', $request->user_id)
            ->with('items.product')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{id}",
     *     summary="Get order details",
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order details",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Order")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     )
     * )
     */
    public function show(Request $request, $id): JsonResponse
    {
        $order = Order::with('items.product')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/orders/{id}/cancel",
     *     summary="Cancel an order",
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order cancelled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Order")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Order cannot be cancelled"
     *     )
     * )
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending orders can be cancelled'
            ], 422);
        }

        $order->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/orders/{id}/status",
     *     summary="Update order status",
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", enum={"pending", "processing", "completed", "cancelled"}, example="processing")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Order")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid status"
     *     )
     * )
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,completed,cancelled'
        ]);

        $order = Order::findOrFail($id);

        // Prevent changing status of cancelled orders
        if ($order->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update status of a cancelled order'
            ], 422);
        }

        $order->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }
} 