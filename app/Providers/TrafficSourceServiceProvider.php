<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Session;

class TrafficSourceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Track traffic sources in cache for later aggregation
        Event::listen('session.started', function () {
            if (Session::has('traffic_source')) {
                $source = Session::get('traffic_source');
                $date = now()->format('Y-m-d');
                $cacheKey = "traffic_sources:{$date}";
                
                $sources = Cache::get($cacheKey, []);
                
                if (!in_array($source, $sources)) {
                    $sources[] = $source;
                    Cache::put($cacheKey, $sources, now()->addDays(2));
                }
            }
        });
    }
}