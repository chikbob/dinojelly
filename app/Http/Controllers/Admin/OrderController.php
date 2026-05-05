<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\AdminOrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function __construct(
        protected AdminOrderService $adminOrderService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:pending,completed,canceled'],
            'payment_status' => ['nullable', 'in:pending,paid,failed,canceled'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        return Inertia::render('admin/Orders/Index', $this->adminOrderService->getOrdersPage($filters));
    }

    public function show(Order $order)
    {
        return Inertia::render('admin/Orders/Show', [
            'order' => $this->adminOrderService->getOrderDetail($order),
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,completed,canceled',
        ]);

        $this->adminOrderService->updateStatus($order, $validated['status'], $request->user());

        return redirect()->back()->with('success', 'Order updated');
    }

    public function addNote(Request $request, Order $order)
    {
        $validated = $request->validate([
            'note' => ['required', 'string', 'min:3', 'max:2000'],
        ]);

        $this->adminOrderService->addNote($order, $request->user(), $validated['note']);

        return redirect()->back()->with('success', 'Note added');
    }
}
