<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Returns extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'reason',
        'status',
        'amount',
        'notes',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}