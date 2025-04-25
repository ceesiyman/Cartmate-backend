<?php

namespace App\Http\Middleware;

use App\Models\TrafficSource;
use Closure;
use Illuminate\Http\Request;

class TrackTrafficMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $source = $this->determineTrafficSource($request);
        
        // Store the traffic source in the request for later use
        $request->attributes->add(['traffic_source' => $source]);
        
        // Track the visit
        $this->recordTraffic($source);
        
        return $next($request);
    }
    
    /**
     * Determine the traffic source from the request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function determineTrafficSource(Request $request)
    {
        // First check for UTM source
        if ($request->has('utm_source')) {
            return $request->get('utm_source');
        }
        
        // Check referrer
        $referrer = $request->headers->get('referer');
        if ($referrer) {
            $parsedUrl = parse_url($referrer);
            if (isset($parsedUrl['host'])) {
                return $parsedUrl['host'];
            }
        }
        
        // If no source found, mark as direct
        return 'direct';
    }
    
    /**
     * Record the traffic
     *
     * @param string $source
     * @return void
     */
    protected function recordTraffic(string $source)
    {
        TrafficSource::create([
            'source' => $source,
            'visits' => 1,
            'orders' => 0,
            'recorded_at' => now()
        ]);
    }
}