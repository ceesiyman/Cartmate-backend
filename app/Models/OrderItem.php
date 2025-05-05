<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @OA\Schema(
 *     schema="OrderItem",
 *     title="OrderItem",
 *     description="OrderItem model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="order_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="product_id", type="string", example="string"),
 *     @OA\Property(property="quantity", type="integer", example=2),
 *     @OA\Property(property="price", type="number", format="float", example=49.99),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class OrderItem extends Model
{
    use HasFactory;
   // Disable auto-incrementing primary key
   public $incrementing = false;
    
   // Set the primary key type to string
   protected $keyType = 'string';
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price'
    ];

    protected $casts = [
        'price' => 'float',
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

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}