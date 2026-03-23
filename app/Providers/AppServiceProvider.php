<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Inertia::share([
            'auth' => function () {
                $user = Auth::user();

                return [
                    'user' => $user,
                    'isAdmin' => $user?->role === 'admin',
                ];
            },

            'cartCount' => function () {
                if (Auth::check()) {
                    return (int)\App\Models\CartItem::where('user_id', Auth::id())
                        ->sum('quantity');
                }

                return 0;
            },

            'favorites' => function () {
                if (Auth::check()) {
                    return Auth::user()
                        ->favorites()
                        ->pluck('product_id')
                        ->toArray();
                }

                return [];
            },

            'adminIndicators' => function () {
                $user = Auth::user();

                if (!$user || $user->role !== 'admin') {
                    return null;
                }

                return [
                    'pending_orders' => (int) \App\Models\Order::where('status', 'pending')->count(),
                    'failed_payments' => (int) \App\Models\Payment::where('status', 'failed')->count(),
                    'low_stock' => (int) \App\Models\StockItem::query()
                        ->where('is_active', true)
                        ->whereRaw('(quantity - reserved_quantity) <= low_stock_threshold')
                        ->count(),
                    'pending_reviews' => (int) \App\Models\Review::where('is_published', false)->count(),
                    'pending_recovery' => (int) \App\Models\CartRecoveryReminder::where('status', 'pending')->count(),
                ];
            },
        ]);
    }
}
