<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Mail\OrderNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

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
     *             @OA\Property(property="user_id", type="string", example="905932a9-dbaf-49cd-bca9-451914c71d19"),
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
            'user_id' => 'required|string|size:36|exists:users,id',
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
    
        // Send notification to admin about new order
        $adminUsers = User::where('role', 'ADMIN')->get();
        foreach ($adminUsers as $admin) {
            Mail::to($admin->email)->send(new OrderNotification($order, 'new_order'));
        }
    
        // Send notification to customer
        Mail::to($order->user->email)->send(new OrderNotification($order, 'new_order'));
    
        $trafficSource = new \App\Models\TrafficSource();
        $trafficSource->source = 'get_orders';
        $trafficSource->visits = 1;
        $trafficSource->orders = 1;
        $trafficSource->recorded_at = now();
        $trafficSource->save();
    
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
     *         @OA\Schema(type="string")
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
            $trafficSource = new \App\Models\TrafficSource();
            $trafficSource->source = 'get_orders';
            $trafficSource->visits = 1;
            $trafficSource->orders = 0;
            $trafficSource->recorded_at = now();
            $trafficSource->save();

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
    $order = Order::with(['user', 'items.product'])
        ->findOrFail($id);
        $trafficSource = new \App\Models\TrafficSource();
        $trafficSource->source = 'get_order_details';
        $trafficSource->visits = 1;
        $trafficSource->orders = 0;
        $trafficSource->recorded_at = now();
        $trafficSource->save();

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

        $trafficSource = new \App\Models\TrafficSource();
        $trafficSource->source = 'cancel_order';
        $trafficSource->visits = 1;
        $trafficSource->orders = 0;
        $trafficSource->recorded_at = now();
        $trafficSource->save();

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

        $oldStatus = $order->status;
        $order->update(['status' => $request->status]);

        // Send notification to customer about status update
        if ($oldStatus !== $request->status) {
            Mail::to($order->user->email)->send(new OrderNotification($order, 'status_update'));
        }

        $trafficSource = new \App\Models\TrafficSource();
        $trafficSource->source = 'update_order_status';
        $trafficSource->visits = 1;
        $trafficSource->orders = 0;
        $trafficSource->recorded_at = now();
        $trafficSource->save();

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

        /**
     * Update tracking number for an order
     *
     * @OA\Put(
     *     path="/api/orders/{id}/tracking",
     *     operationId="updateTrackingNumber",
     *     tags={"Orders"},
     *     summary="Update tracking number for an order",
     *     description="Allows admin to update the tracking number for a specific order",
     *     security={{"sanctum": {}}},
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
     *             required={"tracking_number"},
     *             @OA\Property(
     *                 property="tracking_number",
     *                 type="string",
     *                 example="TRK123456789"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tracking number updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tracking number updated successfully"),
     *             @OA\Property(property="order", ref="#/components/schemas/Order")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized action",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The tracking number field is required")
     *         )
     *     )
     * )
     */
    public function updateTrackingNumber(Request $request, $id)
    {
      

        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $validated = $request->validate([
            'tracking_number' => 'required|string|max:255'
        ]);

        $order->tracking_number = $validated['tracking_number'];
        $order->save();

        return response()->json([
            'message' => 'Tracking number updated successfully',
            'order' => $order
        ], 200);
    }

 
    /**
     * @OA\Get(
     *     path="/api/orders-filter",
     *     summary="Get filtered orders with pagination",
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Filter by order status",
     *         @OA\Schema(type="string", enum={"pending", "processing", "completed", "cancelled"})
     *     ),
     *     @OA\Parameter(
     *         name="customer_type",
     *         in="query",
     *         required=false,
     *         description="Filter by customer type",
     *         @OA\Schema(type="string", enum={"employee", "individual", "company"})
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         required=false,
     *         description="Sort orders by creation date",
     *         @OA\Schema(type="string", enum={"newest", "oldest"}, default="newest")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Items per page",
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of filtered orders with user and item details",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array", @OA\Items(
     *                     allOf={
     *                         @OA\Schema(ref="#/components/schemas/Order"),
     *                         @OA\Schema(
     *                             @OA\Property(property="user", type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="name", type="string", example="John Doe"),
     *                                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                                 @OA\Property(property="phone", type="string", example="123-456-7890", nullable=true),
     *                                 @OA\Property(property="address", type="string", example="123 Main St", nullable=true),
     *                                 @OA\Property(property="customer_type", type="string", enum={"employee", "individual", "company"})
     *                             ),
     *                             @OA\Property(property="items", type="array", @OA\Items(
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="description", type="string", example="Product Name"),
     *                                 @OA\Property(property="price", type="number", format="float", example=49.99),
     *                                 @OA\Property(property="quantity", type="integer", example=2),
     *                                 @OA\Property(property="total", type="number", format="float", example=99.98)
     *                             ))
     *                         )
     *                     }
     *                 )),
     *                 @OA\Property(property="first_page_url", type="string"),
     *                 @OA\Property(property="from", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="last_page_url", type="string"),
     *                 @OA\Property(property="next_page_url", type="string"),
     *                 @OA\Property(property="path", type="string"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="prev_page_url", type="string"),
     *                 @OA\Property(property="to", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             ),
     *             @OA\Property(property="total_orders", type="integer", example=50)
     *         )
     *     )
     * )
     */
    public function filterOrders(Request $request): JsonResponse
    {
        $query = Order::with(['items.product', 'user']);
        
        // Filter by status if provided
        if ($request->has('status') && in_array($request->status, ['pending', 'processing', 'completed', 'cancelled'])) {
            $query->where('status', $request->status);
        }
        
        // Filter by customer type if provided
        if ($request->has('customer_type') && in_array($request->customer_type, ['employee', 'individual', 'company'])) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('customer_type', $request->customer_type);
            });
        }
        
        // Sort by creation date
        $sortDirection = $request->get('sort', 'newest') === 'newest' ? 'desc' : 'asc';
        $query->orderBy('created_at', $sortDirection);
        
        // Get pagination parameters
        $perPage = $request->get('per_page', 15);
        
        // Execute the paginated query
        $orders = $query->paginate($perPage);
        
        // Format user and items data to include only necessary fields
        $orders->getCollection()->transform(function ($order) {
            // Format the user data
            if ($order->user) {
                $order->user = [
                    'id' => $order->user->id,
                    'name' => $order->user->name,
                    'email' => $order->user->email,
                    'phone' => $order->user->phone ?? null,
                    'address' => $order->user->address ?? null,
                    'customer_type' => $order->user->customer_type,
                ];
            }
            
            // Format the items data
            $order->items = $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'description' => $item->product->name ?? 'Unknown Product',
                    'price' => floatval($item->price),
                    'quantity' => $item->quantity,
                    'total' => floatval($item->price * $item->quantity),
                ];
            })->toArray();
            
            return $order;
        });
        
        // Get total count of orders matching the filter criteria
        $totalOrdersCount = $query->count();
        
        return response()->json([
            'success' => true,
            'data' => $orders,
            'total_orders' => $totalOrdersCount
        ]);
    }
/**
 * @OA\Get(
 *     path="/api/orders/{id}/details",
 *     summary="Get detailed order information including user details",
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
 *         description="Order details with user information",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="order", ref="#/components/schemas/Order"),
 *                 @OA\Property(property="user", type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="John Doe"),
 *                     @OA\Property(property="email", type="string", example="john@example.com"),
 *                     @OA\Property(property="created_at", type="string", format="date-time"),
 *                     @OA\Property(property="updated_at", type="string", format="date-time")
 *                 ),
 *                 @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/OrderItem"))
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Order not found"
 *     )
 * )
 */
public function getOrderDetails($id): JsonResponse
{
    $order = Order::findOrFail($id);
    
    // Eager load relationships
    $order->load('items.product');
    $user = $order->user()->first();
    
    // Format the response data
    $responseData = [
        'order' => $order,
        'user' => $user,
        'items' => $order->items
    ];
    
    return response()->json([
        'success' => true,
        'data' => $responseData
    ]);
}

/**
 * @OA\Get(
 *     path="/api/orders-stats",
 *     summary="Get order statistics",
 *     tags={"Orders"},
 *     @OA\Response(
 *         response=200,
 *         description="Order statistics",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="total_orders", type="integer", example=100),
 *                 @OA\Property(property="pending_orders", type="integer", example=25),
 *                 @OA\Property(property="processing_orders", type="integer", example=30),
 *                 @OA\Property(property="completed_orders", type="integer", example=40),
 *                 @OA\Property(property="cancelled_orders", type="integer", example=5),
 *                 @OA\Property(property="total_sales_amount", type="number", format="float", example=9999.99)
 *             )
 *         )
 *     )
 * )
 */
public function getOrderStats(): JsonResponse
{
    $stats = [
        'total_orders' => Order::count(),
        'pending_orders' => Order::where('status', 'pending')->count(),
        'processing_orders' => Order::where('status', 'processing')->count(),
        'completed_orders' => Order::where('status', 'completed')->count(),
        'cancelled_orders' => Order::where('status', 'cancelled')->count(),
        'total_sales_amount' => Order::where('status', '!=', 'cancelled')->sum('total_amount')
    ];
    
    return response()->json([
        'success' => true,
        'data' => $stats
    ]);
}

/**
 * @OA\Get(
 *    path="/api/orders-recent",
 *     summary="Get recent orders with pagination",
 *     tags={"Orders"},
 *     @OA\Parameter(
 *         name="limit",
 *         in="query",
 *         required=false,
 *         description="Number of recent orders to fetch",
 *         @OA\Schema(type="integer", default=10)
 *     ),
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         required=false,
 *         description="Page number",
 *         @OA\Schema(type="integer", default=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="List of recent orders",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="current_page", type="integer", example=1),
 *                 @OA\Property(property="data", type="array", 
 *                     @OA\Items(
 *                         @OA\Property(property="id", type="integer"),
 *                         @OA\Property(property="order_number", type="string"),
 *                         @OA\Property(property="status", type="string"),
 *                         @OA\Property(property="total_amount", type="number", format="float"),
 *                         @OA\Property(property="created_at", type="string", format="date-time"),
 *                         @OA\Property(property="user", type="object",
 *                             @OA\Property(property="id", type="integer"),
 *                             @OA\Property(property="name", type="string"),
 *                             @OA\Property(property="email", type="string")
 *                         ),
 *                         @OA\Property(property="items", type="array", 
 *                             @OA\Items(ref="#/components/schemas/OrderItem")
 *                         )
 *                     )
 *                 ),
 *                 @OA\Property(property="first_page_url", type="string"),
 *                 @OA\Property(property="from", type="integer"),
 *                 @OA\Property(property="last_page", type="integer"),
 *                 @OA\Property(property="last_page_url", type="string"),
 *                 @OA\Property(property="next_page_url", type="string"),
 *                 @OA\Property(property="path", type="string"),
 *                 @OA\Property(property="per_page", type="integer"),
 *                 @OA\Property(property="prev_page_url", type="string"),
 *                 @OA\Property(property="to", type="integer"),
 *                 @OA\Property(property="total", type="integer")
 *             )
 *         )
 *     )
 * )
 */
public function getRecentOrders(Request $request): JsonResponse
{
    $limit = $request->get('limit', 10);
    
    $recentOrders = Order::with(['items.product', 'user'])
        ->orderBy('created_at', 'desc')
        ->paginate($limit);
    
    return response()->json([
        'success' => true,
        'data' => $recentOrders
    ]);
}

/**
     * @OA\Get(
     *     path="/api/orders/{id}/updates",
     *     summary="Get order updates",
     *     tags={"Order Updates"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order updates",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="upd-123"),
     *                     @OA\Property(property="title", type="string", example="Order processed"),
     *                     @OA\Property(property="description", type="string", example="Your order has been processed and is ready for shipping"),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     )
     * )
     */
    public function getUpdates(Request $request, $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $order->updates ?? []
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/orders/{id}/updates",
     *     summary="Add order update",
     *     tags={"Order Updates"},
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
     *             @OA\Property(property="title", type="string", example="Order shipped"),
     *             @OA\Property(property="description", type="string", example="Your order has been shipped and is on its way")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Update added",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function addUpdate(Request $request, $id): JsonResponse
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $order = Order::findOrFail($id);
        
        // Add update to order
        $updates = $order->updates ?? [];
        $updates[] = [
            'id' => 'upd-' . Str::random(10),
            'message' => $request->message,
            'created_at' => now()->toIso8601String()
        ];
        $order->update(['updates' => $updates]);

        // Send notification to customer about admin update
        Mail::to($order->user->email)->send(new OrderNotification($order, 'admin_update', $request->message));

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/orders/{id}/updates/{update_id}",
     *     summary="Update order update",
     *     tags={"Order Updates"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="update_id",
     *         in="path",
     *         required=true,
     *         description="Update ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Order shipped"),
     *             @OA\Property(property="description", type="string", example="Your order has been shipped and is on its way")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Update modified",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order or update not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function updateUpdate(Request $request, $id, $updateId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::findOrFail($id);
        $updates = $order->updates ?? [];
        
        $updateIndex = array_search($updateId, array_column($updates, 'id'));
        
        if ($updateIndex === false) {
            return response()->json([
                'success' => false,
                'message' => 'Update not found'
            ], 404);
        }
        
        $updates[$updateIndex]['title'] = $request->title;
        $updates[$updateIndex]['description'] = $request->description;
        
        $order->updates = $updates;
        $order->save();
        
        return response()->json([
            'success' => true,
            'data' => $updates[$updateIndex]
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/orders/{id}/updates/{update_id}",
     *     summary="Delete order update",
     *     tags={"Order Updates"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="update_id",
     *         in="path",
     *         required=true,
     *         description="Update ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Update deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Update deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order or update not found"
     *     )
     * )
     */
    public function deleteUpdate(Request $request, $id, $updateId): JsonResponse
    {
        $order = Order::findOrFail($id);
        $updates = $order->updates ?? [];
        
        $updateIndex = array_search($updateId, array_column($updates, 'id'));
        
        if ($updateIndex === false) {
            return response()->json([
                'success' => false,
                'message' => 'Update not found'
            ], 404);
        }
        
        array_splice($updates, $updateIndex, 1);
        $order->updates = $updates;
        $order->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Update deleted successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{id}/messages",
     *     summary="Get order messages",
     *     tags={"Order Messages"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order messages",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="msg-123"),
     *                     @OA\Property(property="text", type="string", example="Your order will be delivered tomorrow"),
     *                     @OA\Property(property="admin_id", type="integer", example=5),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     )
     * )
     */
    public function getMessages(Request $request, $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $order->messages ?? []
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/orders/{id}/messages",
     *     summary="Add order message",
     *     tags={"Order Messages"},
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
     *             @OA\Property(property="text", type="string", example="Your order will be delivered tomorrow"),
     *             @OA\Property(property="admin_id", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message added",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function addMessage(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string',
            'admin_id' => 'required|integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::findOrFail($id);
        $messages = $order->messages ?? [];
        
        $newMessage = [
            'id' => 'msg-' . Str::random(8),
            'text' => $request->text,
            'admin_id' => $request->admin_id,
            'created_at' => now()->toIso8601String()
        ];
        
        $messages[] = $newMessage;
        $order->messages = $messages;
        $order->save();
        
        return response()->json([
            'success' => true,
            'data' => $newMessage
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/orders/{id}/messages/{message_id}",
     *     summary="Delete order message",
     *     tags={"Order Messages"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="message_id",
     *         in="path",
     *         required=true,
     *         description="Message ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Message deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order or message not found"
     *     )
     * )
     */
    public function deleteMessage(Request $request, $id, $messageId): JsonResponse
    {
        $order = Order::findOrFail($id);
        $messages = $order->messages ?? [];
        
        $messageIndex = array_search($messageId, array_column($messages, 'id'));
        
        if ($messageIndex === false) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found'
            ], 404);
        }
        
        array_splice($messages, $messageIndex, 1);
        $order->messages = $messages;
        $order->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{id}/documents",
     *     summary="Get order documents",
     *     tags={"Order Documents"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order documents",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="doc-123"),
     *                     @OA\Property(property="name", type="string", example="invoice.pdf"),
     *                     @OA\Property(property="path", type="string", example="documents/orders/1/invoice.pdf"),
     *                     @OA\Property(property="type", type="string", example="invoice"),
     *                     @OA\Property(property="admin_id", type="integer", example=5),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     )
     * )
     */
    public function getDocuments(Request $request, $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $order->documents ?? []
        ]);
    }

    /**
 * @OA\Post(
 *     path="/api/orders/{id}/documents",
 *     summary="Upload order document",
 *     tags={"Order Documents"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Order ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 @OA\Property(property="document", type="file"),
 *                 @OA\Property(property="type", type="string", example="invoice"),
 *                 @OA\Property(property="admin_id", type="integer", example=5)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Document uploaded",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Order not found"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error"
 *     )
 * )
 */
public function uploadDocument(Request $request, $id): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'document' => 'required|file|max:10240', // 10MB max
        'type' => 'required|string|max:50',
        'admin_id' => 'required|integer|exists:users,id'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    $order = Order::findOrFail($id);
    $documents = $order->documents ?? [];
    
    // Store the file in public/orderdocument folder
    $file = $request->file('document');
    $fileName = time() . '_' . $file->getClientOriginalName();
    
    // Create the directory if it doesn't exist
    $path = "orderdocument";
    $fullPath = public_path($path);
    if (!file_exists($fullPath)) {
        mkdir($fullPath, 0755, true);
    }
    
    // Move the uploaded file
    $file->move($fullPath, $fileName);
    $filePath = "{$path}/{$fileName}";
    
    $newDocument = [
        'id' => 'doc-' . Str::random(8),
        'name' => $file->getClientOriginalName(),
        'path' => $filePath,
        'type' => $request->type,
        'admin_id' => $request->admin_id,
        'created_at' => now()->toIso8601String()
    ];
    
    $documents[] = $newDocument;
    $order->documents = $documents;
    $order->save();
    
    return response()->json([
        'success' => true,
        'data' => $newDocument
    ]);
}

    /**
 * @OA\Delete(
 *     path="/api/orders/{id}/documents/{document_id}",
 *     summary="Delete order document",
 *     tags={"Order Documents"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Order ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="document_id",
 *         in="path",
 *         required=true,
 *         description="Document ID",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Document deleted",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Document deleted successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Order or document not found"
 *     )
 * )
 */
public function deleteDocument(Request $request, $id, $documentId): JsonResponse
{
    $order = Order::findOrFail($id);
    $documents = $order->documents ?? [];
    
    $documentIndex = array_search($documentId, array_column($documents, 'id'));
    
    if ($documentIndex === false) {
        return response()->json([
            'success' => false,
            'message' => 'Document not found'
        ], 404);
    }
    
    // Delete the file from public directory
    $document = $documents[$documentIndex];
    $filePath = public_path($document['path']);
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    
    array_splice($documents, $documentIndex, 1);
    $order->documents = $documents;
    $order->save();
    
    return response()->json([
        'success' => true,
        'message' => 'Document deleted successfully'
    ]);
}
} 