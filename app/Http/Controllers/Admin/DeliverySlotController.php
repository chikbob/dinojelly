<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliverySlot;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeliverySlotController extends Controller
{
    public function index()
    {
        $slots = DeliverySlot::query()
            ->withCount('orders')
            ->orderBy('starts_at')
            ->paginate(20)
            ->through(fn (DeliverySlot $slot) => [
                'id' => $slot->id,
                'name' => $slot->name,
                'starts_at' => $slot->starts_at,
                'ends_at' => $slot->ends_at,
                'capacity' => $slot->capacity,
                'price' => $slot->price,
                'is_active' => $slot->is_active,
                'orders_count' => $slot->orders_count,
            ]);

        return Inertia::render('admin/DeliverySlots/Index', [
            'slots' => $slots,
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/DeliverySlots/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DeliverySlot::query()->create([
            ...$validated,
            'capacity' => $validated['capacity'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.delivery-slots.index')->with('success', 'Delivery slot created');
    }

    public function edit(DeliverySlot $deliverySlot)
    {
        return Inertia::render('admin/DeliverySlots/Edit', [
            'slot' => $deliverySlot,
        ]);
    }

    public function update(Request $request, DeliverySlot $deliverySlot)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $deliverySlot->update([
            ...$validated,
            'capacity' => $validated['capacity'] ?? null,
            'is_active' => $validated['is_active'] ?? false,
        ]);

        return redirect()->route('admin.delivery-slots.index')->with('success', 'Delivery slot updated');
    }

    public function destroy(DeliverySlot $deliverySlot)
    {
        $deliverySlot->delete();

        return redirect()->route('admin.delivery-slots.index')->with('success', 'Delivery slot deleted');
    }
}
