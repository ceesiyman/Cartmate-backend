<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    use HasFactory;

        // Disable auto-incrementing primary key
        public $incrementing = false;
    
        // Set the primary key type to string
        protected $keyType = 'string';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'is_active'
    ];

    protected $casts = [
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
     * Get the products for the category.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Scope a query to find a category by name.
     */
    public function scopeFindByName($query, $name)
    {
        return $query->where('name', $name)->first();
    }
}