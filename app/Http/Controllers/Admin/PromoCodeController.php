<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PromoCodeController extends Controller
{
    public function index()
    {
        $promoCodes = PromoCode::query()
            ->latest()
            ->paginate(20)
            ->through(fn (PromoCode $promoCode) => [
                'id' => $promoCode->id,
                'code' => $promoCode->code,
                'name' => $promoCode->name,
                'type' => $promoCode->type,
                'value' => $promoCode->value,
                'usage_limit' => $promoCode->usage_limit,
                'usage_count' => $promoCode->usage_count,
                'starts_at' => $promoCode->starts_at,
                'expires_at' => $promoCode->expires_at,
                'is_active' => $promoCode->is_active,
            ]);

        return Inertia::render('admin/PromoCodes/Index', [
            'promoCodes' => $promoCodes,
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/PromoCodes/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255', 'unique:promo_codes,code'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:fixed,percent'],
            'value' => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        PromoCode::query()->create([
            ...$validated,
            'usage_count' => 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.promo-codes.index')->with('success', 'Promo code created');
    }

    public function edit(PromoCode $promoCode)
    {
        return Inertia::render('admin/PromoCodes/Edit', [
            'promoCode' => $promoCode,
        ]);
    }

    public function update(Request $request, PromoCode $promoCode)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255', 'unique:promo_codes,code,' . $promoCode->id],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:fixed,percent'],
            'value' => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $promoCode->update([
            ...$validated,
            'is_active' => $validated['is_active'] ?? false,
        ]);

        return redirect()->route('admin.promo-codes.index')->with('success', 'Promo code updated');
    }

    public function destroy(PromoCode $promoCode)
    {
        $promoCode->delete();

        return redirect()->route('admin.promo-codes.index')->with('success', 'Promo code deleted');
    }
}
