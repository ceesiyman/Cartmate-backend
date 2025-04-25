<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Models\Category;
use App\Models\Returns;
use App\Models\TrafficSource;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    protected function getDateRange($timeRange)
    {
        $endDate = Carbon::now();
        switch ($timeRange) {
            case '7d':
                $startDate = $endDate->copy()->subDays(7);
                break;
            case '30d':
                $startDate = $endDate->copy()->subDays(30);
                break;
            case '90d':
                $startDate = $endDate->copy()->subDays(90);
                break;
            case '1y':
                $startDate = $endDate->copy()->subYears(1);
                break;
            default:
                $startDate = $endDate->copy()->subDays(30);
        }
        return [$startDate, $endDate];
    }

    protected function getPreviousPeriod($startDate, $endDate)
    {
        $diff = $endDate->diffInDays($startDate);
        return [
            $startDate->copy()->subDays($diff),
            $startDate->copy(),
        ];
    }

    public function keyMetrics(Request $request)
    {
        $timeRange = $request->query('time_range', '30d');
        [$startDate, $endDate] = $this->getDateRange($timeRange);
        [$prevStartDate, $prevEndDate] = $this->getPreviousPeriod($startDate, $endDate);
    
        // Total Orders
        $totalOrders = Order::whereBetween('created_at', [$startDate, $endDate])->count();
        $prevTotalOrders = Order::whereBetween('created_at', [$prevStartDate, $prevEndDate])->count();
        $totalOrdersChange = $prevTotalOrders ? (($totalOrders - $prevTotalOrders) / $prevTotalOrders * 100) : 0;
    
        // Total Revenue
        $totalRevenue = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');
    
        $prevTotalRevenue = Order::where('status', 'completed')
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->sum('total_amount');
    
        $totalRevenueChange = $prevTotalRevenue ? (($totalRevenue - $prevTotalRevenue) / $prevTotalRevenue * 100) : 0;
    
        // Total Customers (unique users who placed orders)
        $totalCustomers = Order::whereBetween('created_at', [$startDate, $endDate])
            ->distinct('user_id')
            ->count('user_id');
        $prevTotalCustomers = Order::whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->distinct('user_id')
            ->count('user_id');
        $totalCustomersChange = $prevTotalCustomers ? (($totalCustomers - $prevTotalCustomers) / $prevTotalCustomers * 100) : 0;
    
        // Average Order Value
        $averageOrderValue = $totalOrders ? ($totalRevenue / $totalOrders) : 0;
        $prevAverageOrderValue = $prevTotalOrders ? ($prevTotalRevenue / $prevTotalOrders) : 0;
        $averageOrderValueChange = $prevAverageOrderValue ? (($averageOrderValue - $prevAverageOrderValue) / $prevAverageOrderValue * 100) : 0;
    
        // Conversion Rate (orders/visits) - Using orders data
        $totalVisits = TrafficSource::whereBetween('recorded_at', [$startDate, $endDate])->sum('visits');
        $prevTotalVisits = TrafficSource::whereBetween('recorded_at', [$prevStartDate, $prevEndDate])->sum('visits');
        $conversionRate = $totalVisits ? ($totalOrders / $totalVisits * 100) : 0;
        $prevConversionRate = $prevTotalVisits ? ($prevTotalOrders / $prevTotalVisits * 100) : 0;
        $conversionRateChange = $prevConversionRate ? (($conversionRate - $prevConversionRate) / $prevConversionRate * 100) : 0;
    
        // Return Rate - Using orders with Returns records
        // Get orders with approved returns
        $returnedOrderIds = Returns::where('status', 'approved')
            ->pluck('order_id')
            ->toArray();
        
        // Calculate total returned amount from orders table
        $totalReturns = Order::whereIn('id', $returnedOrderIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        
        $totalReturnAmount = Order::whereIn('id', $returnedOrderIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');
        
        // Previous period
        $prevReturnedOrderIds = Returns::where('status', 'approved')
            ->pluck('order_id')
            ->toArray();
        
        $prevTotalReturns = Order::whereIn('id', $prevReturnedOrderIds)
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->count();
        
        $prevTotalReturnAmount = Order::whereIn('id', $prevReturnedOrderIds)
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->sum('total_amount');
        
        $returnRate = $totalOrders ? ($totalReturns / $totalOrders * 100) : 0;
        $prevReturnRate = $prevTotalOrders ? ($prevTotalReturns / $prevTotalOrders * 100) : 0;
        $returnRateChange = $prevReturnRate ? (($returnRate - $prevReturnRate) / $prevReturnRate * 100) : 0;
    
        // Update Returns table with calculated rates
        $completedOrders = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
            
        foreach ($completedOrders as $order) {
            $existingReturn = Returns::where('order_id', $order->id)->first();
            
            if ($existingReturn) {
                // Update existing return
                $existingReturn->update([
                    'notes' => "Return rate: {$returnRate}%, Conversion rate: {$conversionRate}%",
                    'amount' => in_array($order->id, $returnedOrderIds) ? $order->total_amount : 0
                ]);
            } else {
                // Create new return record
                Returns::create([
                    'order_id' => $order->id,
                    'reason' => in_array($order->id, $returnedOrderIds) ? 'Order returned' : 'No return',
                    'status' => in_array($order->id, $returnedOrderIds) ? 'approved' : 'no_return',
                    'amount' => in_array($order->id, $returnedOrderIds) ? $order->total_amount : 0,
                    'notes' => "Return rate: {$returnRate}%, Conversion rate: {$conversionRate}%"
                ]);
            }
        }
    
        return response()->json([
            'success' => true,
            'data' => [
                'total_orders' => [
                    'count' => $totalOrders,
                    'percentage_change' => round($totalOrdersChange, 1),
                    'trend' => $totalOrdersChange >= 0 ? 'increase' : 'decrease',
                ],
                'total_revenue' => [
                    'amount' => round($totalRevenue, 2),
                    'percentage_change' => round($totalRevenueChange, 1),
                    'trend' => $totalRevenueChange >= 0 ? 'increase' : 'decrease',
                ],
                'total_customers' => [
                    'count' => $totalCustomers,
                    'percentage_change' => round($totalCustomersChange, 1),
                    'trend' => $totalCustomersChange >= 0 ? 'increase' : 'decrease',
                ],
                'average_order_value' => [
                    'amount' => round($averageOrderValue, 2),
                    'percentage_change' => round($averageOrderValueChange, 1),
                    'trend' => $averageOrderValueChange >= 0 ? 'increase' : 'decrease',
                ],
                'conversion_rate' => [
                    'percentage' => round($conversionRate, 1),
                    'percentage_change' => round($conversionRateChange, 1),
                    'trend' => $conversionRateChange >= 0 ? 'increase' : 'decrease',
                ],
                'return_rate' => [
                    'percentage' => round($returnRate, 1),
                    'percentage_change' => round($returnRateChange, 1),
                    'trend' => $returnRateChange >= 0 ? 'increase' : 'decrease',
                    'amount' => round($totalReturnAmount, 2) // Added total return amount
                ],
            ],
        ]);
    }

    public function topProducts(Request $request)
    {
        $timeRange = $request->query('time_range', '30d');
        [$startDate, $endDate] = $this->getDateRange($timeRange);
        [$prevStartDate, $prevEndDate] = $this->getPreviousPeriod($startDate, $endDate);

        $products = OrderItem::query()
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('products.id', 'products.name')
            ->selectRaw('
                products.name,
                SUM(order_items.quantity) as sales,
                SUM(order_items.quantity * order_items.price) as revenue
            ')
            ->orderBy('revenue', 'desc')
            ->take(5)
            ->get();

        $data = $products->map(function ($product) use ($startDate, $endDate, $prevStartDate, $prevEndDate) {
            $prevRevenue = OrderItem::query()
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereBetween('orders.created_at', [$prevStartDate, $prevEndDate])
                ->where('products.name', $product->name)
                ->sum(\DB::raw('order_items.quantity * order_items.price'));
            $change = $prevRevenue ? (($product->revenue - $prevRevenue) / $prevRevenue * 100) : 0;

            return [
                'name' => $product->name,
                'sales' => (int) $product->sales,
                'revenue' => round($product->revenue, 2),
                'percentage_change' => round($change, 1),
                'trend' => $change >= 0 ? 'increase' : 'decrease',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data->toArray(),
        ]);
    }

    public function topStores(Request $request)
    {
        $timeRange = $request->query('time_range', '30d');
        [$startDate, $endDate] = $this->getDateRange($timeRange);
        [$prevStartDate, $prevEndDate] = $this->getPreviousPeriod($startDate, $endDate);

        $stores = Order::query()
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('products.store')
            ->selectRaw('
                products.store as name,
                COUNT(DISTINCT orders.id) as orders,
                SUM(order_items.quantity * order_items.price) as revenue
            ')
            ->orderBy('revenue', 'desc')
            ->take(5)
            ->get();

        $data = $stores->map(function ($store) use ($startDate, $endDate, $prevStartDate, $prevEndDate) {
            $prevRevenue = Order::query()
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->whereBetween('orders.created_at', [$prevStartDate, $prevEndDate])
                ->where('products.store', $store->name)
                ->sum(\DB::raw('order_items.quantity * order_items.price'));
            $change = $prevRevenue ? (($store->revenue - $prevRevenue) / $prevRevenue * 100) : 0;

            return [
                'name' => $store->name,
                'orders' => (int) $store->orders,
                'revenue' => round($store->revenue, 2),
                'percentage_change' => round($change, 1),
                'trend' => $change >= 0 ? 'increase' : 'decrease',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data->toArray(),
        ]);
    }

    public function salesByCategory(Request $request)
    {
        $timeRange = $request->query('time_range', '30d');
        [$startDate, $endDate] = $this->getDateRange($timeRange);

        $categories = Order::query()
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('categories.id', 'categories.name')
            ->selectRaw('
                categories.name as label,
                SUM(order_items.quantity * order_items.price) as revenue
            ')
            ->orderBy('revenue', 'desc')
            ->get();

        $labels = $categories->pluck('label')->toArray();
        $data = $categories->pluck('revenue')->map(fn ($val) => round($val, 2))->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Revenue',
                        'data' => $data,
                        'backgroundColor' => array_fill(0, count($labels), 'rgba(249, 115, 22, 0.8)'),
                    ],
                ],
            ],
        ]);
    }

    public function ordersByStatus(Request $request)
    {
        $timeRange = $request->query('time_range', '30d');
        [$startDate, $endDate] = $this->getDateRange($timeRange);

        $statuses = Order::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('status')
            ->selectRaw('
                status as label,
                COUNT(*) as count
            ')
            ->get();

        $labels = $statuses->pluck('label')->toArray();
        $data = $statuses->pluck('count')->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Orders',
                        'data' => $data,
                        'backgroundColor' => [
                            'rgba(249, 115, 22, 0.8)',
                            'rgba(236, 72, 153, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(59, 130, 246, 0.8)',
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function geographicDistribution(Request $request)
    {
        $timeRange = $request->query('time_range', '30d');
        [$startDate, $endDate] = $this->getDateRange($timeRange);

        $locations = Order::query()
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('shipping_address')
            ->selectRaw('
                shipping_address as location,
                COUNT(*) as orders,
                SUM(total_amount) as revenue
            ')
            ->orderBy('revenue', 'desc')
            ->get();

        $data = $locations->map(function ($location) {
            return [
                'location' => $location->location ?: 'Unknown',
                'orders' => (int) $location->orders,
                'revenue' => round($location->revenue, 2),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data->toArray(),
        ]);
    }

    public function trafficSources(Request $request)
    {
        $timeRange = $request->query('time_range', '30d');
        [$startDate, $endDate] = $this->getDateRange($timeRange);
        [$prevStartDate, $prevEndDate] = $this->getPreviousPeriod($startDate, $endDate);

        $sources = TrafficSource::query()
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->groupBy('source')
            ->selectRaw('
                source,
                SUM(visits) as visits,
                SUM(orders) as orders
            ')
            ->orderBy('visits', 'desc')
            ->get();

        $data = $sources->map(function ($source) use ($startDate, $endDate, $prevStartDate, $prevEndDate) {
            $prevSource = TrafficSource::query()
                ->whereBetween('recorded_at', [$prevStartDate, $prevEndDate])
                ->where('source', $source->source)
                ->selectRaw('
                    SUM(visits) as visits,
                    SUM(orders) as orders
                ')
                ->first();

            $prevVisits = $prevSource ? $prevSource->visits : 0;
            $change = $prevVisits ? (($source->visits - $prevVisits) / $prevVisits * 100) : 0;
            $conversionRate = $source->visits ? ($source->orders / $source->visits * 100) : 0;

            return [
                'source' => $source->source,
                'visits' => (int) $source->visits,
                'orders' => (int) $source->orders,
                'conversion_rate' => round($conversionRate, 1),
                'percentage_change' => round($change, 1),
                'trend' => $change >= 0 ? 'increase' : 'decrease',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data->toArray(),
        ]);
    }

    public function revenueTrends(Request $request)
{
    $timeRange = $request->query('time_range', '30d');
    [$startDate, $endDate] = $this->getDateRange($timeRange);

    // Map frontend time ranges (week, month, year) to controller ranges
    $timeRange = match ($timeRange) {
        'week' => '7d',
        'month' => '30d',
        'year' => '1y',
        default => '30d',
    };

    $dateFormat = match ($timeRange) {
        '7d', '30d' => '%Y-%m-%d', // Daily
        '90d' => '%Y-%m-%w', // Weekly
        '1y' => '%Y-%m', // Monthly
        default => '%Y-%m-%d',
    };

    $revenues = Order::query()
        ->whereBetween('created_at', [$startDate, $endDate])
        ->groupBy(\DB::raw("DATE_FORMAT(created_at, '$dateFormat')"))
        ->selectRaw("
            DATE_FORMAT(created_at, '$dateFormat') as date,
            SUM(total_amount) as revenue
        ")
        ->orderBy('date', 'asc')
        ->get();

    // Fill missing dates
    $dates = [];
    $currentDate = $startDate->copy();
    while ($currentDate <= $endDate) {
        $formattedDate = match ($timeRange) {
            '7d', '30d' => $currentDate->format('Y-m-d'),
            '90d' => $currentDate->startOfWeek()->format('Y-m-W'),
            '1y' => $currentDate->format('Y-m'),
            default => $currentDate->format('Y-m-d'),
        };
        $dates[$formattedDate] = 0;
        $currentDate->addDay();
    }

    foreach ($revenues as $revenue) {
        $dates[$revenue->date] = round($revenue->revenue, 2);
    }

    $labels = array_keys($dates);
    $data = array_values($dates);

    return response()->json([
        'success' => true,
        'data' => [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $data,
                    'borderColor' => 'rgba(249, 115, 22, 1)',
                    'backgroundColor' => 'rgba(249, 115, 22, 0.2)',
                    'fill' => true,
                ],
            ],
        ],
    ]);
}
}