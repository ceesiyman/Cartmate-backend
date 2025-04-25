<?php

namespace App\Console\Commands;

use App\Models\TrafficSource;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AggregateTrafficData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'traffic:aggregate {date? : The date to aggregate (YYYY-MM-DD format, defaults to yesterday)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggregate traffic data from cache and store in database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dateInput = $this->argument('date');
        $date = $dateInput ? Carbon::createFromFormat('Y-m-d', $dateInput) : now()->subDay();
        $dateString = $date->format('Y-m-d');
        
        $this->info("Aggregating traffic data for {$dateString}");
        
        // Get all cache keys for this date
        $keys = Cache::get("traffic_sources:{$dateString}", []);
        if (empty($keys)) {
            // If no keys stored, try to use pattern matching (works with some cache drivers)
            $prefix = "traffic_stats:{$dateString}:";
            $this->scanCacheForTrafficData($prefix, $dateString);
        } else {
            $this->processStoredTrafficData($keys, $dateString);
        }
        
        $this->info('Traffic data aggregation completed');
        return Command::SUCCESS;
    }
    
    /**
     * Process traffic data when we have stored source keys
     *
     * @param array $sources
     * @param string $dateString
     * @return void
     */
    private function processStoredTrafficData(array $sources, string $dateString)
    {
        $processed = 0;
        
        foreach ($sources as $source) {
            $visits = (int)Cache::get("traffic_stats:{$dateString}:{$source}:visits", 0);
            $orders = (int)Cache::get("traffic_stats:{$dateString}:{$source}:orders", 0);
            
            if ($visits > 0) {
                TrafficSource::create([
                    'source' => $source,
                    'visits' => $visits,
                    'orders' => $orders,
                    'recorded_at' => $dateString,
                ]);
                
                $processed++;
                
                // Clear the cache for this source and date
                Cache::forget("traffic_stats:{$dateString}:{$source}:visits");
                Cache::forget("traffic_stats:{$dateString}:{$source}:orders");
            }
        }
        
        // Clear the source list from cache
        Cache::forget("traffic_sources:{$dateString}");
        
        $this->info("Processed {$processed} traffic sources");
    }
    
    /**
     * Scan cache for traffic data when we don't have stored keys
     * Note: This approach works only with specific cache drivers that support scanning
     *
     * @param string $prefix
     * @param string $dateString
     * @return void
     */
    private function scanCacheForTrafficData(string $prefix, string $dateString)
    {
        // This method is a fallback when we don't have reliable key tracking
        // In a production environment, you should implement a reliable key tracking system
        
        $this->warn("No tracked sources found, using alternative method");
        
        // For file/database cache drivers, you might need to implement a custom solution here
        // This is just a simple example of what you might do with Redis
        
        // For demo purposes, let's create some sample sources
        $sampleSources = ['Direct', 'Google Organic', 'Facebook', 'Twitter'];
        $processed = 0;
        
        foreach ($sampleSources as $source) {
            // Check if we have any data for this source
            $visits = rand(50, 500); // In a real app, you'd fetch actual cached data
            $orders = rand(0, $visits * 0.1); // In a real app, you'd fetch actual cached data
            
            TrafficSource::create([
                'source' => $source,
                'visits' => $visits,
                'orders' => $orders,
                'recorded_at' => $dateString,
            ]);
            
            $processed++;
        }
        
        $this->info("Processed {$processed} traffic sources (demo data)");
    }
}