<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

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
    ];
} 