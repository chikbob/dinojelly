<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_adds_existing_product_until_available_stock_is_reached(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Dino Jelly Mix']);
        StockItem::query()->create([
            'product_id' => $product->id,
            'sku' => 'DJ-MIX',
            'quantity' => 2,
            'reserved_quantity' => 0,
            'low_stock_threshold' => 1,
            'is_active' => true,
        ]);

        $cartService = app(CartService::class);

        $cartService->addProduct($user, $product->id);
        $cartService->addProduct($user, $product->id);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
        $this->assertSame(2, $cartService->getCartCount($user));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Недостаточно остатка на складе');

        $cartService->addProduct($user, $product->id);
    }

    public function test_it_decreases_quantity_and_removes_last_cart_item(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $cartService = app(CartService::class);

        $cartService->decreaseQuantity($user, $product->id);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $cartService->decreaseQuantity($user, $product->id);

        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }
}
