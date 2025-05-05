<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @OA\Schema(
 *     schema="Order",
 *     title="Order",
 *     description="Order model",
 *     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
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

    // Disable auto-incrementing primary key
    public $incrementing = false;
    
    // Set the primary key type to string
    protected $keyType = 'string';

    protected $fillable = [
        'id', // Added id to fillable
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
    
    // Add UUID generation when creating a new model
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}