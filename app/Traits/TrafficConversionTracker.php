<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

trait TrafficConversionTracker
{
    /**
     * Track order conversion by traffic source
     *
     * @return void
     */
    public function trackOrderConversion()
    {
        $source = Session::get('traffic_source', 'Direct');
        $date = now()->format('Y-m-d');
        
        // Increment order count in cache
        $cacheKey = "traffic_stats:{$date}:{$source}:orders";
        Cache::increment($cacheKey);
    }
}