<?php

namespace App\Services;

use App\Models\CartRecoveryReminder;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminAnalyticsService
{
    /**
     * @return array<string, mixed>
     */
    public function getDashboardPayload(): array
    {
        $days = collect(range(13, 0))->map(fn ($i) => Carbon::now()->subDays($i)->format('Y-m-d'));

        $dailyOrders = Order::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(13))
            ->groupBy('date')
            ->pluck('total', 'date');

        $dailyRevenue = Payment::query()
            ->selectRaw('DATE(paid_at) as date, SUM(amount) as revenue')
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', now()->subDays(13))
            ->groupBy('date')
            ->pluck('revenue', 'date');

        $ordersChart = $days->map(fn ($day) => [
            'date' => $day,
            'orders' => (int) ($dailyOrders[$day] ?? 0),
            'revenue' => (float) ($dailyRevenue[$day] ?? 0),
        ]);

        $completedOrdersCount = (int) Order::query()->where('status', 'completed')->count();
        $totalRevenue = (float) Payment::query()->where('status', 'paid')->sum('amount');
        $averageOrderValue = $completedOrdersCount > 0
            ? round($totalRevenue / $completedOrdersCount, 2)
            : 0.0;

        $repeatCustomers = (int) Order::query()
            ->select('user_id')
            ->where('status', '!=', 'canceled')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        $paymentBreakdown = Payment::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $topProducts = OrderItem::query()
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->selectRaw('products.id, products.name, SUM(order_items.quantity) as total_quantity, SUM(order_items.quantity * order_items.price) as revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'total_quantity' => (int) $item->total_quantity,
                'revenue' => (float) $item->revenue,
            ]);

        $funnel = [
            'users' => User::query()->count(),
            'favorites' => Favorite::query()->distinct('user_id')->count('user_id'),
            'carts' => DB::table('cart_items')->distinct('user_id')->count('user_id'),
            'orders' => Order::query()->distinct('user_id')->count('user_id'),
            'completed_orders' => Order::query()
                ->where('status', 'completed')
                ->distinct('user_id')
                ->count('user_id'),
        ];

        $recovery = [
            'sent' => CartRecoveryReminder::query()->where('status', 'sent')->count(),
            'recovered' => CartRecoveryReminder::query()->where('status', 'recovered')->count(),
            'pending' => CartRecoveryReminder::query()->where('status', 'pending')->count(),
        ];

        return [
            'stats' => [
                'users' => User::count(),
                'products' => Product::count(),
                'orders' => Order::count(),
                'revenue' => $totalRevenue,
                'average_order_value' => $averageOrderValue,
                'repeat_customers' => $repeatCustomers,
            ],
            'ordersChart' => $ordersChart,
            'paymentBreakdown' => [
                'pending' => (int) ($paymentBreakdown['pending'] ?? 0),
                'paid' => (int) ($paymentBreakdown['paid'] ?? 0),
                'failed' => (int) ($paymentBreakdown['failed'] ?? 0),
                'canceled' => (int) ($paymentBreakdown['canceled'] ?? 0),
            ],
            'topProducts' => $topProducts,
            'funnel' => $funnel,
            'recovery' => $recovery,
        ];
    }
}
