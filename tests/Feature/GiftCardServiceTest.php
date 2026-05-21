<?php

namespace Tests\Feature;

use App\Models\GiftCard;
use App\Models\Order;
use App\Models\User;
use App\Services\GiftCardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class GiftCardServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_caps_redemption_amount_and_normalizes_code(): void
    {
        $user = User::factory()->create();
        $giftCard = GiftCard::query()->create([
            'code' => 'DG-TESTCARD',
            'name' => 'Gift card',
            'recipient_user_id' => $user->id,
            'initial_amount' => 500,
            'balance' => 500,
            'currency' => 'RUB',
            'issued_at' => now(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        $preview = app(GiftCardService::class)->preview($user, ' dg-testcard ', 320.45);

        $this->assertSame($giftCard->id, $preview['gift_card']['id']);
        $this->assertSame(320.45, $preview['applied_amount']);
        $this->assertSame(0.0, $preview['remaining_total']);
    }

    public function test_preview_rejects_cards_assigned_to_another_user(): void
    {
        $owner = User::factory()->create();
        $anotherUser = User::factory()->create();
        GiftCard::query()->create([
            'code' => 'DG-PRIVATE',
            'name' => 'Private gift card',
            'recipient_user_id' => $owner->id,
            'initial_amount' => 250,
            'balance' => 250,
            'currency' => 'RUB',
            'issued_at' => now(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Подарочная карта принадлежит другому пользователю');

        app(GiftCardService::class)->preview($anotherUser, 'DG-PRIVATE', 100);
    }

    public function test_refund_order_discounts_restores_balances_only_once(): void
    {
        $user = User::factory()->create(['referral_credit_balance' => 10]);
        $giftCard = GiftCard::query()->create([
            'code' => 'DG-REFUND',
            'name' => 'Refundable gift card',
            'recipient_user_id' => $user->id,
            'initial_amount' => 300,
            'balance' => 0,
            'currency' => 'RUB',
            'issued_at' => now(),
            'expires_at' => now()->addMonth(),
            'is_active' => false,
        ]);
        $order = Order::query()->create([
            'user_id' => $user->id,
            'gift_card_id' => $giftCard->id,
            'total_price' => 150,
            'delivery_price' => 0,
            'discount_amount' => 0,
            'gift_card_amount' => 300,
            'referral_credit_amount' => 40,
            'total_quantity' => 1,
            'payment_method' => 'card',
            'status' => 'canceled',
        ]);

        $giftCardService = app(GiftCardService::class);

        $giftCardService->refundOrderDiscounts($order);
        $giftCardService->refundOrderDiscounts($order->refresh());

        $this->assertSame('300.00', $giftCard->refresh()->balance);
        $this->assertTrue($giftCard->is_active);
        $this->assertSame('50.00', $user->refresh()->referral_credit_balance);
        $this->assertNotNull($order->refresh()->gift_card_refunded_at);
        $this->assertNotNull($order->referral_credit_refunded_at);
        $this->assertDatabaseCount('gift_card_redemptions', 1);
    }
}
