<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function overview()
    {
        return Cache::remember('dashboard.overview', 60, function () {
            $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total');
            
            $prevMonthStart = Carbon::now()->subMonth()->startOfMonth();
            $prevMonthEnd = Carbon::now()->subMonth()->endOfMonth();
            $currentMonthStart = Carbon::now()->startOfMonth();
            
            $prevMonthRevenue = Order::where('status', '!=', 'cancelled')
                ->whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])
                ->sum('total');
                
            $currentMonthRevenue = Order::where('status', '!=', 'cancelled')
                ->where('created_at', '>=', $currentMonthStart)
                ->sum('total');

            $revenueGrowth = $prevMonthRevenue > 0 
                ? (($currentMonthRevenue - $prevMonthRevenue) / $prevMonthRevenue) * 100 
                : ($currentMonthRevenue > 0 ? 100 : 0);

            $totalOrders = Order::count();
            $prevMonthOrders = Order::whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])->count();
            $currentMonthOrders = Order::where('created_at', '>=', $currentMonthStart)->count();
            
            $ordersGrowth = $prevMonthOrders > 0 
                ? (($currentMonthOrders - $prevMonthOrders) / $prevMonthOrders) * 100 
                : ($currentMonthOrders > 0 ? 100 : 0);

            $totalCustomers = DB::table('orders')->distinct('customer_phone')->count('customer_phone');
            
            $prevMonthCustomers = DB::table('orders')
                ->whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])
                ->distinct('customer_phone')
                ->count('customer_phone');
                
            $currentMonthCustomers = DB::table('orders')
                ->where('created_at', '>=', $currentMonthStart)
                ->distinct('customer_phone')
                ->count('customer_phone');
                
            $customersGrowth = $prevMonthCustomers > 0 
                ? (($currentMonthCustomers - $prevMonthCustomers) / $prevMonthCustomers) * 100 
                : ($currentMonthCustomers > 0 ? 100 : 0);

            $totalProducts = Product::count();

            return response()->json([
                'revenue' => [
                    'total' => (float)$totalRevenue,
                    'growth' => round($revenueGrowth, 2),
                ],
                'orders' => [
                    'total' => $totalOrders,
                    'growth' => round($ordersGrowth, 2),
                ],
                'customers' => [
                    'total' => $totalCustomers,
                    'growth' => round($customersGrowth, 2),
                ],
                'products' => [
                    'total' => $totalProducts,
                    'growth' => 0,
                ]
            ]);
        });
    }

    public function revenue(Request $request)
    {
        $period = $request->query('period', '7days');
        
        $cacheKey = 'dashboard.revenue.' . $period;
        
        return Cache::remember($cacheKey, 60, function () use ($period) {
            $query = Order::where('status', '!=', 'cancelled');
            $format = '%Y-%m-%d';
            $groupBy = 'date';
            
            if ($period === '7days') {
                $query->where('created_at', '>=', Carbon::now()->subDays(7));
            } elseif ($period === '30days') {
                $query->where('created_at', '>=', Carbon::now()->subDays(30));
            } elseif ($period === '3months') {
                $query->where('created_at', '>=', Carbon::now()->subMonths(3));
                $format = '%Y-%m'; // group by month
                $groupBy = 'month';
            } elseif ($period === '12months') {
                $query->where('created_at', '>=', Carbon::now()->subMonths(12));
                $format = '%Y-%m'; // group by month
                $groupBy = 'month';
            }

            // MySQL specific DATE_FORMAT. (SQLite uses strftime, so we fallback for tests if needed)
            if (DB::getDriverName() === 'sqlite') {
                $format = $groupBy === 'month' ? '%Y-%m' : '%Y-%m-%d';
                $results = $query->select(
                    DB::raw("strftime('{$format}', created_at) as label"),
                    DB::raw('SUM(total) as revenue')
                )
                ->groupBy('label')
                ->orderBy('label')
                ->get();
            } else {
                $results = $query->select(
                    DB::raw("DATE_FORMAT(created_at, '{$format}') as label"),
                    DB::raw('SUM(total) as revenue')
                )
                ->groupBy('label')
                ->orderBy('label')
                ->get();
            }

            // Fill gaps
            $data = [];
            if ($period === '7days' || $period === '30days') {
                $days = $period === '7days' ? 6 : 29;
                for ($i = $days; $i >= 0; $i--) {
                    $dateStr = Carbon::now()->subDays($i)->format('Y-m-d');
                    $found = $results->firstWhere('label', $dateStr);
                    $data[] = [
                        'label' => Carbon::parse($dateStr)->format('M d'),
                        'revenue' => $found ? (float)$found->revenue : 0
                    ];
                }
            } else {
                $months = $period === '3months' ? 2 : 11;
                for ($i = $months; $i >= 0; $i--) {
                    $dateStr = Carbon::now()->subMonths($i)->format('Y-m');
                    $found = $results->firstWhere('label', $dateStr);
                    $data[] = [
                        'label' => Carbon::parse($dateStr . '-01')->format('M Y'),
                        'revenue' => $found ? (float)$found->revenue : 0
                    ];
                }
            }

            return response()->json($data);
        });
    }

    public function orderStatus()
    {
        return Cache::remember('dashboard.order-status', 60, function () {
            $statuses = Order::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get();
                
            return response()->json($statuses);
        });
    }

    public function topProducts()
    {
        return Cache::remember('dashboard.top-products', 60, function () {
            $products = OrderItem::select('product_name_en as name', 'product_name as name_ar', DB::raw('SUM(quantity) as units_sold'))
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.status', '!=', 'cancelled')
                ->groupBy('product_name_en', 'product_name')
                ->orderByDesc('units_sold')
                ->limit(10)
                ->get();
                
            return response()->json($products);
        });
    }

    public function categories()
    {
        return Cache::remember('dashboard.categories', 60, function () {
            $categories = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.status', '!=', 'cancelled')
                ->select('categories.name_en as name', 'categories.name_ar as name_ar', DB::raw('SUM(order_items.total) as revenue'))
                ->groupBy('categories.id', 'categories.name_en', 'categories.name_ar')
                ->orderByDesc('revenue')
                ->get();

            $total = $categories->sum('revenue');
            
            $data = $categories->map(function ($item) use ($total) {
                return [
                    'name' => $item->name ?: $item->name_ar,
                    'value' => (float)$item->revenue,
                    'percentage' => $total > 0 ? round(($item->revenue / $total) * 100, 1) : 0
                ];
            });

            return response()->json($data);
        });
    }

    public function latestOrders()
    {
        return Cache::remember('dashboard.latest-orders', 60, function () {
            $orders = Order::with('items:id,order_id,product_name_en')
                ->latest()
                ->limit(10)
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'customer' => $order->customer_name,
                        'total' => (float)$order->total,
                        'status' => $order->status,
                        'date' => $order->created_at->format('Y-m-d H:i'),
                        'items_count' => $order->items->count(),
                    ];
                });
                
            return response()->json($orders);
        });
    }

    public function topCustomers()
    {
        return Cache::remember('dashboard.top-customers', 60, function () {
            $customers = Order::where('status', '!=', 'cancelled')
                ->select('customer_name as name', 'customer_phone as phone', DB::raw('COUNT(id) as orders_count'), DB::raw('SUM(total) as total_spent'))
                ->groupBy('customer_phone', 'customer_name')
                ->orderByDesc('total_spent')
                ->limit(5)
                ->get();
                
            return response()->json($customers);
        });
    }

    public function activity()
    {
        return Cache::remember('dashboard.activity', 60, function () {
            // Recent Orders
            $orders = Order::latest()->limit(5)->get()->map(function ($order) {
                return [
                    'id' => 'order_' . $order->id,
                    'type' => 'order',
                    'title' => 'New Order #' . $order->id,
                    'description' => $order->customer_name . ' placed an order for ' . $order->total . ' SAR',
                    'date' => $order->created_at,
                ];
            });

            // Recent Products
            $products = Product::latest()->limit(5)->get()->map(function ($product) {
                return [
                    'id' => 'product_' . $product->id,
                    'type' => 'product',
                    'title' => 'Product Added',
                    'description' => ($product->name_en ?: $product->name_ar) . ' was added to catalog',
                    'date' => $product->created_at,
                ];
            });

            // Merge and sort
            $activities = collect($orders)->merge($products)
                ->sortByDesc('date')
                ->take(10)
                ->map(function ($item) {
                    $item['date'] = Carbon::parse($item['date'])->diffForHumans();
                    return $item;
                })
                ->values();

            return response()->json($activities);
        });
    }
}
