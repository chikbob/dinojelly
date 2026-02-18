<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Inertia\Inertia;

class AdminHomeController extends Controller
{
    public function index()
    {
        $days = collect(range(6, 0))->map(function ($i) {
            return Carbon::now()->subDays($i)->format('Y-m-d');
        });

        $orders = Order::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('date')
            ->pluck('total', 'date');

        $chartData = $days->map(fn ($day) => [
            'date' => $day,
            'total' => $orders[$day] ?? 0,
        ]);

        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'users'    => User::count(),
                'products' => Product::count(),
                'orders'   => Order::count(),
            ],
            'ordersChart' => $chartData,
        ]);
    }
}
