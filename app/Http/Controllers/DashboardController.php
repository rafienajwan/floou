<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Plant;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Count users, plants, orders
        $totalUsers = User::where('role', 'user')->count();
        $totalPlants = Plant::count();
        $totalOrders = Order::count();

        // Low stock plants
        $lowStockPlants = Plant::where('stock', '<', 5)->get();

        // Recent orders
        $recentOrders = Order::with(['user', 'orderDetails.plant'])
            ->latest()
            ->take(5)
            ->get();

        // Sales statistics
        $salesStats = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_price) as total_sales')
            )
            ->where('status', '!=', 'canceled')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->take(7)
            ->get();

        // Order status counts
        $orderStatusCounts = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        return response()->json([
            'statistics' => [
                'total_users' => $totalUsers,
                'total_plants' => $totalPlants,
                'total_orders' => $totalOrders,
                'order_status_counts' => $orderStatusCounts
            ],
            'low_stock_plants' => $lowStockPlants,
            'recent_orders' => $recentOrders,
            'sales_stats' => $salesStats
        ]);
    }
}
