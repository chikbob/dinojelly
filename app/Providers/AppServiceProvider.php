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
        ]);
    }
}
