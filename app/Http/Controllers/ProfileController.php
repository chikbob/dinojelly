<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function index()
    {
        $cartCount = 0;
        $user = Auth::user();

        $orders = Order::where('user_id', $user->id)
            ->with('items.product')
            ->orderByDesc('created_at')
                ->get();

        $ordersCount = Order::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        if (Auth::check()) {
            $cartCount = CartItem::where('user_id', Auth::id())->sum('quantity');
        }

        return Inertia::render('Profile/profile', [
            'user' => $user,
            'cartCount' => $cartCount,
            'orders' => $orders,
            'pendingOrdersCount' => $ordersCount,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $user->update($validated);

        return redirect()->route('profile')->with('success', 'Profile updated');
    }
}
