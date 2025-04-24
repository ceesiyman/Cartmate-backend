<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/customers/{id}",
     *     summary="Get customer details",
     *     tags={"Customers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Customer details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="phone", type="string"),
     *                 @OA\Property(property="location", type="string"),
     *                 @OA\Property(property="avatar", type="string"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="total_orders", type="integer"),
     *                 @OA\Property(property="total_spent", type="number"),
     *                 @OA\Property(property="last_order", type="string", format="date-time"),
     *                 @OA\Property(property="join_date", type="string", format="date-time"),
     *                 @OA\Property(property="payment_method", type="string"),
     *                 @OA\Property(property="orders", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string"),
     *                         @OA\Property(property="date", type="string", format="date-time"),
     *                         @OA\Property(property="amount", type="number"),
     *                         @OA\Property(property="status", type="string"),
     *                         @OA\Property(property="items", type="integer")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Customer not found"
     *     )
     * )
     */
    public function show($id)
    {
        $customer = User::with(['orders' => function($query) {
            $query->withCount('items')
                  ->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        // Calculate total spent and get last order
        $totalSpent = $customer->orders->sum('total_amount');
        $lastOrder = $customer->orders->first();

        // Format the response
        $response = [
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'location' => $customer->location,
                'avatar' => $customer->image,
                'status' => $customer->status,
                'total_orders' => $customer->orders->count(),
                'total_spent' => $totalSpent,
                'last_order' => $lastOrder ? $lastOrder->created_at->toIso8601String() : null,
                'join_date' => $customer->created_at->toIso8601String(),
                'payment_method' => $customer->payment_method,
                'orders' => $customer->orders->map(function($order) {
                    return [
                        'id' => $order->order_number,
                        'date' => $order->created_at->toIso8601String(),
                        'amount' => $order->total_amount,
                        'status' => $order->status,
                        'items' => $order->items_count
                    ];
                })
            ]
        ];

        return response()->json($response);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/customers",
     *     summary="Get all customers",
     *     tags={"Customers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of customers retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="email", type="string"),
     *                     @OA\Property(property="phone", type="string"),
     *                     @OA\Property(property="location", type="string"),
     *                     @OA\Property(property="avatar", type="string"),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(property="total_orders", type="integer"),
     *                     @OA\Property(property="total_spent", type="number"),
     *                     @OA\Property(property="last_order", type="string", format="date-time"),
     *                     @OA\Property(property="join_date", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        
        $customers = User::withCount('orders')
            ->with(['orders' => function($query) {
                $query->select('id', 'user_id', 'total_amount', 'created_at')
                      ->orderBy('created_at', 'desc');
            }])
            ->paginate($perPage);

        $formattedCustomers = $customers->map(function($customer) {
            $lastOrder = $customer->orders->first();
            $totalSpent = $customer->orders->sum('total_amount');

            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone_number,
                'location' => $customer->location,
                'avatar' => $customer->avatar,
                'status' => $customer->status,
                'total_orders' => $customer->orders_count,
                'total_spent' => $totalSpent,
                'last_order' => $lastOrder ? $lastOrder->created_at->toIso8601String() : null,
                'join_date' => $customer->created_at->toIso8601String()
            ];
        });

        return response()->json([
            'data' => $formattedCustomers,
            'meta' => [
                'current_page' => $customers->currentPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total()
            ]
        ]);
    }
} 