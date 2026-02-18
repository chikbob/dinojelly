<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::paginate(15);
        return Inertia::render('admin/Orders/Index', ['orders' => $orders]);
    }

    public function show(Order $order)
    {
        return Inertia::render('admin/Orders/Show', ['order' => $order]);
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,completed,canceled',
        ]);

        $order->update($validated);

        return redirect()->route('admin.orders.index')->with('success', 'Order updated');
    }
}
