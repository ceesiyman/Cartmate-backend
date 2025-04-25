<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class TrafficSourceTracker
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
        // Skip for API requests or specific paths
        if ($request->is('api/*') || $request->is('admin/*') || $request->ajax()) {
            return $next($request);
        }

        // Get referrer
        $referer = $request->headers->get('referer');
        $source = $this->determineSource($referer, $request);
        
        // Store source in session
        if ($source && !Session::has('traffic_source')) {
            Session::put('traffic_source', $source);
            
            // Increment daily visit count in cache
            $date = now()->format('Y-m-d');
            $cacheKey = "traffic_stats:{$date}:{$source}:visits";
            Cache::increment($cacheKey);
        }
        
        return $next($request);
    }
    
    /**
     * Determine the traffic source based on the referrer.
     *
     * @param string|null $referer
     * @param Request $request
     * @return string
     */
    private function determineSource($referer, Request $request)
    {
        // Check UTM parameters first (highest priority)
        $utmSource = $request->query('utm_source');
        if ($utmSource) {
            return ucfirst($utmSource);
        }
        
        // No referrer means direct traffic
        if (empty($referer)) {
            return 'Direct';
        }
        
        // Check for search engines
        $searchEngines = [
            'google.com' => 'Google',
            'bing.com' => 'Bing',
            'yahoo.com' => 'Yahoo',
            'duckduckgo.com' => 'DuckDuckGo',
        ];
        
        foreach ($searchEngines as $domain => $name) {
            if (strpos($referer, $domain) !== false) {
                return $name . ' Organic';
            }
        }
        
        // Check for social networks
        $socialNetworks = [
            'facebook.com' => 'Facebook',
            'instagram.com' => 'Instagram',
            'twitter.com' => 'Twitter',
            'linkedin.com' => 'LinkedIn',
            'youtube.com' => 'YouTube',
            'pinterest.com' => 'Pinterest',
            't.co' => 'Twitter',
        ];
        
        foreach ($socialNetworks as $domain => $name) {
            if (strpos($referer, $domain) !== false) {
                return $name;
            }
        }
        
        // If we can extract a domain from the referrer
        $parsedUrl = parse_url($referer);
        if (isset($parsedUrl['host'])) {
            // Remove www. if present
            $host = preg_replace('/^www\./', '', $parsedUrl['host']);
            return 'Referral: ' . $host;
        }
        
        return 'Other';
    }
}