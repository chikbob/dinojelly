<?php

namespace App\Services;

use App\Models\GiftCard;
use App\Models\GiftCardRedemption;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GiftCardService
{
    public function generateCode(): string
    {
        do {
            $code = 'DG-' . strtoupper(Str::random(10));
        } while (GiftCard::query()->where('code', $code)->exists());

        return $code;
    }

    /**
     * @return array<string, mixed>
     */
    public function preview(User $user, string $code, float $orderAmount): array
    {
        $giftCard = $this->findUsableCard($user, $code);
        $amount = min((float) $giftCard->balance, max($orderAmount, 0));

        return [
            'gift_card' => [
                'id' => $giftCard->id,
                'code' => $giftCard->code,
                'name' => $giftCard->name,
                'balance' => (float) $giftCard->balance,
                'expires_at' => $giftCard->expires_at,
            ],
            'applied_amount' => round($amount, 2),
            'remaining_total' => round(max($orderAmount - $amount, 0), 2),
        ];
    }

    public function claim(User $user, string $code): GiftCard
    {
        $giftCard = GiftCard::query()
            ->where('code', strtoupper(trim($code)))
            ->firstOrFail();

        if (!$giftCard->is_active || ($giftCard->expires_at && $giftCard->expires_at->isPast()) || $giftCard->balance <= 0) {
            throw new \RuntimeException('Подарочная карта недоступна');
        }

        if ($giftCard->recipient_user_id && $giftCard->recipient_user_id !== $user->id) {
            throw new \RuntimeException('Подарочная карта уже привязана к другому пользователю');
        }

        $giftCard->update([
            'recipient_user_id' => $user->id,
            'issued_at' => $giftCard->issued_at ?? now(),
        ]);

        return $giftCard->refresh();
    }

    /**
     * @return array{gift_card: ?GiftCard, amount: float}
     */
    public function resolveForOrder(User $user, ?string $code, float $orderAmount): array
    {
        if (!$code) {
            return ['gift_card' => null, 'amount' => 0];
        }

        $preview = $this->preview($user, $code, $orderAmount);

        return [
            'gift_card' => GiftCard::query()->find($preview['gift_card']['id']),
            'amount' => (float) $preview['applied_amount'],
        ];
    }

    public function applyToOrder(GiftCard $giftCard, User $user, Order $order, float $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        if (!$giftCard->recipient_user_id) {
            $giftCard->update([
                'recipient_user_id' => $user->id,
            ]);
        }

        $giftCard->decrement('balance', $amount);

        GiftCardRedemption::query()->create([
            'gift_card_id' => $giftCard->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
            'amount' => $amount,
            'redeemed_at' => now(),
            'meta' => [
                'order_total_before_redemption' => $order->total_price + $amount,
            ],
        ]);

        $giftCard->refresh();

        if ($giftCard->balance <= 0) {
            $giftCard->update([
                'is_active' => false,
            ]);
        }
    }

    public function refundOrderDiscounts(Order $order): void
    {
        if ($order->gift_card_id && $order->gift_card_amount > 0 && !$order->gift_card_refunded_at) {
            $giftCard = GiftCard::query()->find($order->gift_card_id);

            if ($giftCard) {
                $giftCard->increment('balance', (float) $order->gift_card_amount);
                $giftCard->update([
                    'is_active' => true,
                ]);

                GiftCardRedemption::query()->create([
                    'gift_card_id' => $giftCard->id,
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'amount' => -1 * (float) $order->gift_card_amount,
                    'redeemed_at' => now(),
                    'meta' => [
                        'action' => 'refund',
                    ],
                ]);
            }

            $order->forceFill([
                'gift_card_refunded_at' => now(),
            ])->save();
        }

        if ($order->referral_credit_amount > 0 && !$order->referral_credit_refunded_at) {
            $order->user()->increment('referral_credit_balance', (float) $order->referral_credit_amount);
            $order->forceFill([
                'referral_credit_refunded_at' => now(),
            ])->save();
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getProfilePayload(User $user): array
    {
        $giftCards = GiftCard::query()
            ->where('recipient_user_id', $user->id)
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (GiftCard $giftCard) => [
                'id' => $giftCard->id,
                'code' => $giftCard->code,
                'name' => $giftCard->name,
                'balance' => (float) $giftCard->balance,
                'initial_amount' => (float) $giftCard->initial_amount,
                'expires_at' => $giftCard->expires_at,
                'is_active' => $giftCard->is_active,
                'message' => $giftCard->message,
            ])
            ->values();

        return [
            'giftCards' => $giftCards,
        ];
    }

    protected function findUsableCard(User $user, string $code): GiftCard
    {
        $giftCard = GiftCard::query()
            ->where('code', strtoupper(trim($code)))
            ->firstOrFail();

        if (!$giftCard->is_active || ($giftCard->expires_at && $giftCard->expires_at->isPast()) || $giftCard->balance <= 0) {
            throw new \RuntimeException('Подарочная карта недоступна');
        }

        if ($giftCard->recipient_user_id && $giftCard->recipient_user_id !== $user->id) {
            throw new \RuntimeException('Подарочная карта принадлежит другому пользователю');
        }

        return $giftCard;
    }
}
