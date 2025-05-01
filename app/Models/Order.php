<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Order",
 *     title="Order",
 *     description="Order model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="order_number", type="string", example="ORD-2024-001"),
 *     @OA\Property(property="status", type="string", enum={"pending", "processing", "completed", "cancelled"}, example="pending"),
 *     @OA\Property(property="total_amount", type="number", format="float", example=99.99),
 *     @OA\Property(property="shipping_address", type="string", example="123 Main St, City, Country"),
 *     @OA\Property(property="billing_address", type="string", example="123 Main St, City, Country"),
 *     @OA\Property(property="phone_number", type="string", example="+1234567890"),
 *     @OA\Property(property="notes", type="string", example="Please deliver in the morning"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'tracking_number',
        'status',
        'total_amount',
        'shipping_address',
        'billing_address',
        'phone_number',
        'updates',
        'messages',
        'documents',
        'notes'
    ];

    protected $casts = [
        'total_amount' => 'float', 
        'updates' => 'array',
        'messages' => 'array',
        'documents' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
} 