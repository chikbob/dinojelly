<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiftCard;
use App\Models\User;
use App\Services\GiftCardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GiftCardController extends Controller
{
    public function __construct(
        protected GiftCardService $giftCardService,
    ) {}

    public function index()
    {
        $giftCards = GiftCard::query()
            ->with(['recipient', 'purchaser'])
            ->latest()
            ->paginate(20)
            ->through(fn (GiftCard $giftCard) => [
                'id' => $giftCard->id,
                'code' => $giftCard->code,
                'name' => $giftCard->name,
                'balance' => (float) $giftCard->balance,
                'initial_amount' => (float) $giftCard->initial_amount,
                'recipient' => $giftCard->recipient?->email,
                'purchaser' => $giftCard->purchaser?->email,
                'expires_at' => $giftCard->expires_at,
                'is_active' => $giftCard->is_active,
            ]);

        return Inertia::render('admin/GiftCards/Index', [
            'giftCards' => $giftCards,
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/GiftCards/Create', [
            'users' => User::query()->orderBy('email')->limit(100)->get(['id', 'email']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:1000'],
            'recipient_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'initial_amount' => ['required', 'numeric', 'min:100'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        GiftCard::query()->create([
            'code' => $this->giftCardService->generateCode(),
            'name' => $data['name'],
            'message' => $data['message'] ?? null,
            'recipient_user_id' => $data['recipient_user_id'] ?? null,
            'initial_amount' => $data['initial_amount'],
            'balance' => $data['initial_amount'],
            'expires_at' => $data['expires_at'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'issued_at' => now(),
        ]);

        return redirect()->route('admin.gift-cards.index')->with('success', 'Gift card created');
    }

    public function edit(GiftCard $giftCard)
    {
        return Inertia::render('admin/GiftCards/Edit', [
            'giftCard' => $giftCard,
            'users' => User::query()->orderBy('email')->limit(100)->get(['id', 'email']),
        ]);
    }

    public function update(Request $request, GiftCard $giftCard)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:1000'],
            'recipient_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'balance' => ['required', 'numeric', 'min:0'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $giftCard->update([
            'name' => $data['name'],
            'message' => $data['message'] ?? null,
            'recipient_user_id' => $data['recipient_user_id'] ?? null,
            'balance' => $data['balance'],
            'expires_at' => $data['expires_at'] ?? null,
            'is_active' => $data['is_active'] ?? false,
        ]);

        return redirect()->route('admin.gift-cards.index')->with('success', 'Gift card updated');
    }

    public function destroy(GiftCard $giftCard)
    {
        $giftCard->delete();

        return redirect()->route('admin.gift-cards.index')->with('success', 'Gift card deleted');
    }
}
