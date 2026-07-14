<?php

namespace App\Services\Admin;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function dashboard(): array
    {
        return [

            'stats' => $this->stats(),

            'revenue' => $this->revenue(),

            'weekly_revenue' => $this->weeklyRevenue(),

            'categories' => $this->categories(),

            'recent_orders' => $this->recentOrders(),

            'low_stock' => $this->lowStock(),

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Cards
    |--------------------------------------------------------------------------
    */

    private function stats(): array
    {
        $revenue = Order::sum('grand_total');

        $orders = Order::count();

        $customers = User::where('role_id',2)->count();

        return [

            'totalRevenue'=>[

                'label'=>'Revenue',

                'value'=>(float)$revenue,

                'change'=>0

            ],

            'totalOrders'=>[

                'label'=>'Orders',

                'value'=>$orders,

                'change'=>0

            ],

            'totalCustomers'=>[

                'label'=>'Customers',

                'value'=>$customers,

                'change'=>0

            ],

            'avgOrderValue'=>[

                'label'=>'Average',

                'value'=>$orders
                    ? round($revenue/$orders,2)
                    :0,

                'change'=>0

            ]

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Revenue Chart
    |--------------------------------------------------------------------------
    */

    private function revenue()
    {
        $customers = User::selectRaw(
                'MONTH(created_at) as month, COUNT(*) as customers'
            )
            ->groupByRaw('MONTH(created_at)')
            ->pluck('customers', 'month');

        return Order::selectRaw(

                'MONTH(created_at) as month,
                 SUM(grand_total) as revenue,
                 COUNT(*) as orders'

            )
            ->groupByRaw('MONTH(created_at)')
            ->orderByRaw('MONTH(created_at)')
            ->get()
            ->map(function ($row) {

                return [

                    'month'=>$row->month,

                    'revenue'=>(float)$row->revenue,

                    'orders'=>(int)$row->orders,

                    'customers'=>(int) ($customers[$row->month] ?? 0)

                ];

            });
    }

    /*
    |--------------------------------------------------------------------------
    | Category Chart
    |--------------------------------------------------------------------------
    */

    private function categories()
    {
        return Category::withCount('products')
            ->get()
            ->map(function ($category) {

                return [

                    'name'=>$category->name,

                    'value'=>$category->products_count

                ];

            });
    }

    private function weeklyRevenue()
    {
        $customers = User::selectRaw(
                'DATE(created_at) as date, COUNT(*) as customers'
            )
            ->groupByRaw('DATE(created_at)')
            ->pluck('customers', 'date');

        return Order::selectRaw(
                'DATE(created_at) as date, SUM(grand_total) as revenue, COUNT(*) as orders'
            )
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->get()
            ->map(function ($row) {
                return [
                    'month' => $row->date,
                    'revenue' => (float) $row->revenue,
                    'orders' => (int) $row->orders,
                    'customers' => (int) ($customers[$row->date] ?? 0),
                ];
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Recent Orders
    |--------------------------------------------------------------------------
    */

    private function recentOrders()
    {
        return Order::with('user')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {

                return [

                    'id'=>$order->order_number,

                    'customer'=>$order->user?->name,

                    'email'=>$order->user?->email,

                    'items'=>$order->items()->count(),

                    'total'=>(float)$order->grand_total,

                    'status'=>$order->order_status,

                ];

            });
    }

    /*
    |--------------------------------------------------------------------------
    | Low Stock
    |--------------------------------------------------------------------------
    */

    private function lowStock()
    {
        return Product::with('category')
            ->whereColumn(

                'stock',

                '<=',

                'low_stock_alert'

            )
            ->get()
            ->map(function ($product) {

                return [

                    'id'=>$product->id,

                    'name'=>$product->title,

                    'category'=>$product->category?->name,

                    'stock'=>$product->stock,

                ];

            });
    }
}
