<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;
           // Disable auto-incrementing primary key
           public $incrementing = false;
    
           // Set the primary key type to string
           protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'original_price',
        'discounted_price',
        'image',
        'description',
        'store',
        'original_url',
        'shipping',
        'customs',
        'service_fee',
        'vat',
        'total_price',
        'images',
        'similar_products',
        'features',           // New
        'specifications',     // New
        'brand',    
        'rating',            // New
        'review_count',      // New
        'in_stock',          // New
        'sku',               // New
        'additional_info',   // New
        'store_id',
        'category_id',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'original_price' => 'float',
        'discounted_price' => 'float',
        'shipping' => 'float',
        'customs' => 'float',
        'service_fee' => 'float',
        'vat' => 'float',
        'total_price' => 'float',
        'images' => 'array',
        'similar_products' => 'array',
        'features' => 'array',
        'specifications' => 'array',
        'additional_info' => 'array',
        'in_stock' => 'boolean',
        'price' => 'decimal:2',
        'is_active' => 'boolean'
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

        
    /**
     * Get the store that owns the product.
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
} 