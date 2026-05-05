<?php

namespace App\Http\Controllers;

use App\Http\Resources\AddressResource;
use App\Http\Resources\SubscriptionResource;
use App\Models\CartItem;
use App\Models\Order;
use App\Services\GiftCardService;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function __construct(
        protected ReferralService $referralService,
        protected GiftCardService $giftCardService,
    ) {}

    public function index()
    {
        $cartCount = 0;
        $user = Auth::user();

        $ordersCount = Order::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        if (Auth::check()) {
            $cartCount = CartItem::where('user_id', Auth::id())->sum('quantity');
        }

        return Inertia::render('Profile/profile', [
            'user' => $user,
            'cartCount' => $cartCount,
            'addresses' => AddressResource::collection(
                $user->addresses()->orderByDesc('is_default')->latest()->get()
            )->resolve(request()),
            'subscriptions' => SubscriptionResource::collection(
                $user->subscriptions()
                    ->with(['address', 'deliverySlot', 'items.product.stockItem', 'lastOrder.latestPayment'])
                    ->latest()
                    ->limit(6)
                    ->get()
            )->resolve(request()),
            'pendingOrdersCount' => $ordersCount,
            ...$this->referralService->getProfilePayload($user),
            ...$this->giftCardService->getProfilePayload($user),
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
