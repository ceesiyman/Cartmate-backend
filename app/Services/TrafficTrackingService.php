<?php

namespace App\Services;

use App\Models\TrafficSource;

class TrafficTrackingService
{
    /**
     * Track and store traffic source
     *
     * @param string $source Traffic source name
     * @param bool $isOrder Whether this traffic resulted in an order
     * @return TrafficSource
     */
    public function trackTrafficSource(string $source, bool $isOrder = false)
    {
        $trafficSource = new TrafficSource();
        $trafficSource->source = $source;
        $trafficSource->visits = 1;
        $trafficSource->orders = $isOrder ? 1 : 0;
        $trafficSource->recorded_at = now();
        $trafficSource->save();
        
        return $trafficSource;
    }
}