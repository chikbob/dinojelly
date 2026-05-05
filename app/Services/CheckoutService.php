<?php

namespace App\Services;

use App\Http\Resources\AddressResource;
use App\Http\Resources\CheckoutItemResource;
use App\Http\Resources\DeliverySlotResource;
use App\Models\Address;
use App\Models\DeliverySlot;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function __construct(
        protected CartService $cartService,
        protected OrderService $orderService,
        protected PaymentService $paymentService,
        protected OrderEventService $orderEventService,
        protected AbandonedCartService $abandonedCartService,
        protected InventoryService $inventoryService,
        protected GiftCardService $giftCardService,
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function getCheckoutPage(User $user): ?array
    {
        $cartItems = $this->cartService->getCartItems($user);

        if ($cartItems->isEmpty()) {
            return null;
        }

        $stockErrors = $this->inventoryService->validateCartStock($user, $cartItems);

        $items = CheckoutItemResource::collection($cartItems)->resolve(request());
        $addresses = $user->addresses()->orderByDesc('is_default')->latest()->get();
        $slots = DeliverySlot::query()
            ->where('is_active', true)
            ->where('ends_at', '>', now())
            ->orderBy('starts_at')
            ->get();
        $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();
        $defaultSlot = $slots->first();
        $subtotal = (float) $cartItems->sum(fn ($item) => (float) $item->product->price * $item->quantity);
        $deliveryPrice = (float) ($defaultSlot?->price ?? 0);

        return [
            'items' => $items,
            'totalQuantity' => $cartItems->sum('quantity'),
            'subtotalPrice' => $subtotal,
            'deliveryPrice' => $deliveryPrice,
            'totalPrice' => (float) ($subtotal + $deliveryPrice),
            'referralCreditBalance' => (float) $user->referral_credit_balance,
            'giftCards' => $this->giftCardService->getProfilePayload($user)['giftCards'],
            'addresses' => AddressResource::collection($addresses)->resolve(request()),
            'deliverySlots' => DeliverySlotResource::collection($slots)->resolve(request()),
            'defaultAddressId' => $defaultAddress?->id,
            'defaultDeliverySlotId' => $defaultSlot?->id,
            'stockErrors' => $stockErrors,
            'pendingOrdersCount' => $this->orderService->getPendingOrdersCount($user),
            'cartCount' => $cartItems->sum('quantity'),
        ];
    }

    public function createOrder(
        User $user,
        string $paymentMethod,
        int $addressId,
        int $deliverySlotId,
        ?string $giftCardCode = null,
        bool $useReferralCredit = false,
    ): Order {
        $cartItems = $this->cartService->getCartItems($user);

        if ($cartItems->isEmpty()) {
            throw new \RuntimeException('Корзина пуста');
        }

        $stockErrors = $this->inventoryService->validateCartStock($user, $cartItems);
        if ($stockErrors !== []) {
            throw new \RuntimeException(implode(' ', $stockErrors));
        }

        $address = Address::query()
            ->where('user_id', $user->id)
            ->find($addressId);

        if (! $address) {
            throw new \RuntimeException('Адрес доставки не найден');
        }

        $deliverySlot = DeliverySlot::query()
            ->where('is_active', true)
            ->find($deliverySlotId);

        if (! $deliverySlot) {
            throw new \RuntimeException('Слот доставки недоступен');
        }

        return DB::transaction(function () use ($user, $paymentMethod, $cartItems, $address, $deliverySlot, $giftCardCode, $useReferralCredit) {
            $totalQuantity = $cartItems->sum('quantity');
            $subtotal = $cartItems->sum(fn ($item) => $item->product->price * $item->quantity);
            $beforeDiscounts = $subtotal + $deliverySlot->price;
            $giftCardResolution = $this->giftCardService->resolveForOrder($user, $giftCardCode, $beforeDiscounts);
            $giftCard = $giftCardResolution['gift_card'];
            $giftCardAmount = $giftCardResolution['amount'];
            $referralCreditAmount = $useReferralCredit
                ? min((float) $user->referral_credit_balance, max($beforeDiscounts - $giftCardAmount, 0))
                : 0;
            $finalTotal = max($beforeDiscounts - $giftCardAmount - $referralCreditAmount, 0);

            $order = Order::query()->create([
                'user_id' => $user->id,
                'address_id' => $address->id,
                'delivery_slot_id' => $deliverySlot->id,
                'gift_card_id' => $giftCard?->id,
                'total_price' => $finalTotal,
                'delivery_price' => $deliverySlot->price,
                'discount_amount' => 0,
                'gift_card_amount' => $giftCardAmount,
                'referral_credit_amount' => $referralCreditAmount,
                'total_quantity' => $totalQuantity,
                'payment_method' => $paymentMethod,
                'status' => 'pending',
            ]);

            foreach ($cartItems as $item) {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);
            }

            $this->paymentService->createForOrder($order);
            $this->inventoryService->reserveOrderStock($order);
            if ($giftCard) {
                $this->giftCardService->applyToOrder($giftCard, $user, $order, $giftCardAmount);
            }
            if ($referralCreditAmount > 0) {
                $user->decrement('referral_credit_balance', $referralCreditAmount);
            }
            $this->orderEventService->log(
                $order,
                'order_created',
                'Заказ создан',
                'Пользователь оформил заказ через checkout',
                $user,
                [
                    'payment_method' => $paymentMethod,
                    'address_id' => $address->id,
                    'delivery_slot_id' => $deliverySlot->id,
                    'gift_card_id' => $giftCard?->id,
                    'gift_card_amount' => $giftCardAmount,
                    'referral_credit_amount' => $referralCreditAmount,
                ],
            );

            $this->cartService->clear($user);
            $this->abandonedCartService->markRecovered($user, 'order_completed');

            return $order->load('latestPayment');
        });
    }
}
