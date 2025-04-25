<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="TrafficSource",
 *     title="TrafficSource",
 *     description="TrafficSource model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="source", type="string", example="Direct"),
 *     @OA\Property(property="visits", type="integer", example=1000),
 *     @OA\Property(property="orders", type="integer", example=50),
 *     @OA\Property(property="recorded_at", type="string", format="date"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class TrafficSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'source',
        'visits',
        'orders',
        'recorded_at',
    ];

    protected $casts = [
        'visits' => 'integer',
        'orders' => 'integer',
        'recorded_at' => 'date',
    ];
}