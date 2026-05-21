<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\CartItem;
use App\Models\DeliverySlot;
use App\Models\GiftCard;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_order_applies_discounts_reserves_stock_and_clears_cart(): void
    {
        $user = User::factory()->create(['referral_credit_balance' => 50]);
        $address = Address::factory()->create([
            'user_id' => $user->id,
            'is_default' => true,
        ]);
        $deliverySlot = DeliverySlot::factory()->create([
            'price' => 100,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHours(3),
            'is_active' => true,
        ]);
        $product = Product::factory()->create([
            'name' => 'Checkout Dino Jelly',
            'price' => 300,
        ]);
        $stockItem = StockItem::query()->create([
            'product_id' => $product->id,
            'sku' => 'DJ-CHECKOUT',
            'quantity' => 5,
            'reserved_quantity' => 0,
            'low_stock_threshold' => 1,
            'is_active' => true,
        ]);
        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
        $giftCard = GiftCard::query()->create([
            'code' => 'DG-CHECKOUT',
            'name' => 'Checkout gift card',
            'recipient_user_id' => $user->id,
            'initial_amount' => 200,
            'balance' => 200,
            'currency' => 'RUB',
            'issued_at' => now(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        $order = app(CheckoutService::class)->createOrder(
            $user,
            'cash',
            $address->id,
            $deliverySlot->id,
            'DG-CHECKOUT',
            true,
        );

        $this->assertSame($user->id, $order->user_id);
        $this->assertSame($address->id, $order->address_id);
        $this->assertSame($deliverySlot->id, $order->delivery_slot_id);
        $this->assertSame($giftCard->id, $order->gift_card_id);
        $this->assertEquals(450.00, $order->total_price);
        $this->assertEquals(200.00, $order->gift_card_amount);
        $this->assertEquals(50.00, $order->referral_credit_amount);
        $this->assertSame(2, $order->total_quantity);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => '300.00',
        ]);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'provider' => 'offline',
            'amount' => '450.00',
            'status' => 'pending',
            'method' => 'cash',
        ]);
        $this->assertDatabaseHas('order_events', [
            'order_id' => $order->id,
            'type' => 'order_created',
        ]);
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
        $this->assertSame(2, $stockItem->refresh()->reserved_quantity);
        $this->assertSame('0.00', $giftCard->refresh()->balance);
        $this->assertFalse($giftCard->is_active);
        $this->assertSame('0.00', $user->refresh()->referral_credit_balance);
    }
}
