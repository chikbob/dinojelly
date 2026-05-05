<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\CartItem;
use App\Models\CartRecoveryReminder;
use App\Models\Category;
use App\Models\Collection as ProductCollection;
use App\Models\DeliverySlot;
use App\Models\Favorite;
use App\Models\GiftCard;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\Referral;
use App\Models\Review;
use App\Models\StockItem;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use App\Models\User;
use Carbon\Carbon;
use Database\Factories\LocalizedTextFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

abstract class LocalizedDatabaseSeeder extends Seeder
{
    protected const ADMIN_EMAIL = 'admin@dinojelly.local';

    protected const DEMO_EMAIL = 'demo@dinojelly.local';

    protected const DEFAULT_PASSWORD = 'password';

    /**
     * @var array<string, mixed>|null
     */
    private ?array $resolvedSeedContent = null;

    /**
     * @return array<string, mixed>
     */
    abstract protected function seedContent(): array;

    public function run(): void
    {
        $this->configureSeedingContext();

        DB::transaction(function () {
            $admin = User::factory()->admin()->create([
                'name' => $this->content('admin_name'),
                'email' => self::ADMIN_EMAIL,
                'phone' => $this->content('admin_phone'),
                'password' => Hash::make(self::DEFAULT_PASSWORD),
            ]);

            $demoUser = User::factory()->create([
                'name' => $this->content('demo_user_name'),
                'email' => self::DEMO_EMAIL,
                'phone' => $this->content('demo_user_phone'),
                'address' => $this->content('demo_user_address'),
                'password' => Hash::make(self::DEFAULT_PASSWORD),
            ]);

            User::factory(260)->create();
            $allUsers = User::query()->whereKeyNot($admin->id)->get();
            $this->seedReferralCodes($allUsers->prepend($admin));

            $categories = $this->seedCategories();
            $products = $this->seedProducts($categories);
            $deliverySlots = $this->seedDeliverySlots();
            $promoCodes = PromoCode::factory(60)->create();
            $this->seedCollections($products);
            $this->seedAddresses($allUsers);
            $this->seedFavorites($allUsers, $products);
            $orders = $this->seedOrders($allUsers, $products, $promoCodes, $deliverySlots);
            $this->seedReferrals($allUsers, $orders);
            $this->seedGiftCards($allUsers, $orders);
            $this->seedReviews($orders);
            $this->seedCartsAndRecoveries($allUsers, $products);
            $this->seedDemoScenario($demoUser, $products, $promoCodes, $deliverySlots, $orders);
            $this->seedSubscriptions($orders);
        });
    }

    protected function configureSeedingContext(): void
    {
        Carbon::setLocale($this->appLocale());

        config([
            'app.locale' => $this->appLocale(),
            'database.seeding' => [
                'app_locale' => $this->appLocale(),
                'faker_locale' => $this->fakerLocale(),
                'phone_prefix' => $this->content('phone_prefix'),
                'cities' => $this->content('cities'),
                'address_labels' => $this->content('address_labels'),
                'delivery_slot_label' => $this->content('delivery.label'),
                'product_suffix' => $this->content('product_suffix'),
            ],
        ]);
    }

    protected function fakerLocale(): string
    {
        return (string) $this->content('faker_locale');
    }

    protected function appLocale(): string
    {
        return (string) $this->content('app_locale');
    }

    protected function currencyCode(): string
    {
        return (string) $this->content('currency');
    }

    protected function content(string $key): mixed
    {
        if ($this->resolvedSeedContent === null) {
            $this->resolvedSeedContent = $this->seedContent();
        }

        return data_get($this->resolvedSeedContent, $key);
    }

    /**
     * @param  Collection<int, User>  $users
     */
    protected function seedReferralCodes(Collection $users): void
    {
        foreach ($users as $user) {
            $user->update([
                'referral_code' => 'REF'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT),
            ]);
        }
    }

    /**
     * @return Collection<int, Category>
     */
    protected function seedCategories(): Collection
    {
        $fixedCategories = collect($this->content('categories'))
            ->map(fn (array $item) => Category::query()->create([
                'name' => $item['name'],
                'slug' => $item['slug'],
                'description' => LocalizedTextFactory::categoryDescription(),
                'sort_order' => $item['sort_order'],
                'is_active' => true,
            ]));

        $extraCategories = Category::factory(12)->create();

        return $fixedCategories->concat($extraCategories)->values();
    }

    /**
     * @param  Collection<int, Category>  $categories
     * @return Collection<int, Product>
     */
    protected function seedProducts(Collection $categories): Collection
    {
        $products = Product::factory(900)->make()->each(function (Product $product) use ($categories) {
            $product->category_id = $categories->random()->id;
            $product->created_at = fake()->dateTimeBetween('-10 months', 'now');
            $product->updated_at = now();
            $product->save();
        });

        foreach ($products as $product) {
            StockItem::query()->create([
                'product_id' => $product->id,
                'sku' => 'SKU-'.strtoupper(Str::random(10)),
                'quantity' => fake()->numberBetween(180, 1400),
                'reserved_quantity' => 0,
                'low_stock_threshold' => fake()->numberBetween(8, 40),
                'is_active' => fake()->boolean(96),
            ]);
        }

        return Product::query()->with('stockItem')->get();
    }

    /**
     * @return Collection<int, DeliverySlot>
     */
    protected function seedDeliverySlots(): Collection
    {
        $slots = collect();

        foreach (range(0, 13) as $dayOffset) {
            foreach ([[10, 13], [14, 17], [18, 21]] as [$from, $to]) {
                $start = now()->copy()->startOfDay()->addDays($dayOffset)->addHours($from);
                $end = now()->copy()->startOfDay()->addDays($dayOffset)->addHours($to);

                $dayLabel = match ($dayOffset) {
                    0 => $this->content('delivery.today'),
                    1 => $this->content('delivery.tomorrow'),
                    default => $start->translatedFormat('d M'),
                };

                $slots->push(DeliverySlot::query()->create([
                    'name' => sprintf('%s %s:00-%s:00', $dayLabel, $from, $to),
                    'starts_at' => $start,
                    'ends_at' => $end,
                    'capacity' => fake()->numberBetween(20, 80),
                    'price' => fake()->randomElement([0, 150, 200, 250, 300, 350]),
                    'is_active' => true,
                ]));
            }
        }

        return $slots;
    }

    /**
     * @param  Collection<int, Product>  $products
     */
    protected function seedCollections(Collection $products): void
    {
        $collections = ProductCollection::factory(28)->create();

        foreach ($collections as $collection) {
            $attach = $products->random(fake()->numberBetween(18, 80));
            $payload = [];

            foreach ($attach->values() as $index => $product) {
                $payload[$product->id] = ['sort_order' => $index + 1];
            }

            $collection->products()->sync($payload);
        }
    }

    /**
     * @param  Collection<int, User>  $users
     */
    protected function seedAddresses(Collection $users): void
    {
        foreach ($users as $user) {
            $addresses = Address::factory(fake()->numberBetween(1, 3))
                ->for($user)
                ->create();

            $default = $addresses->first();
            if ($default) {
                $default->update(['is_default' => true]);
            }
        }
    }

    /**
     * @param  Collection<int, User>  $users
     * @param  Collection<int, Product>  $products
     */
    protected function seedFavorites(Collection $users, Collection $products): void
    {
        $rows = [];

        foreach ($users->random(190) as $user) {
            foreach ($products->random(fake()->numberBetween(4, 18)) as $product) {
                $key = $user->id.':'.$product->id;
                $rows[$key] = [
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
                    'updated_at' => now(),
                ];
            }
        }

        Favorite::query()->insert(array_values($rows));
    }

    /**
     * @param  Collection<int, User>  $users
     * @param  Collection<int, Product>  $products
     * @param  Collection<int, PromoCode>  $promoCodes
     * @param  Collection<int, DeliverySlot>  $deliverySlots
     * @return Collection<int, Order>
     */
    protected function seedOrders(
        Collection $users,
        Collection $products,
        Collection $promoCodes,
        Collection $deliverySlots,
    ): Collection {
        $orders = collect();

        foreach ($users->random(210) as $user) {
            $addresses = $user->addresses()->get();
            if ($addresses->isEmpty()) {
                continue;
            }

            $ordersCount = fake()->numberBetween(1, 6);

            foreach (range(1, $ordersCount) as $_) {
                $order = $this->createOrderForUser($user, $addresses, $products, $promoCodes, $deliverySlots);

                if ($order) {
                    $orders->push($order);
                }
            }
        }

        return $orders;
    }

    /**
     * @param  Collection<int, Address>  $addresses
     * @param  Collection<int, Product>  $products
     * @param  Collection<int, PromoCode>  $promoCodes
     * @param  Collection<int, DeliverySlot>  $deliverySlots
     */
    protected function createOrderForUser(
        User $user,
        Collection $addresses,
        Collection $products,
        Collection $promoCodes,
        Collection $deliverySlots,
        ?string $forcedStatus = null,
    ): ?Order {
        $status = $forcedStatus ?? fake()->randomElement(['completed', 'completed', 'completed', 'pending', 'pending', 'canceled']);
        $paymentMethod = fake()->boolean(58) ? 'card' : 'cash';
        $address = $addresses->random();
        $deliverySlot = $deliverySlots->random();
        $selectedProducts = $products->random(fake()->numberBetween(1, 4));
        $selectedProducts = $selectedProducts instanceof Product ? collect([$selectedProducts]) : $selectedProducts;

        $lines = [];
        $subtotal = 0;
        $totalQuantity = 0;

        foreach ($selectedProducts as $product) {
            $stockItem = $product->stockItem;
            if (! $stockItem || ! $stockItem->is_active) {
                continue;
            }

            $available = max(0, (int) $stockItem->quantity - (int) $stockItem->reserved_quantity);
            if ($available < 1) {
                continue;
            }

            $quantity = min(fake()->numberBetween(1, 4), $available);
            $subtotal += $product->price * $quantity;
            $totalQuantity += $quantity;

            $lines[] = [
                'product' => $product,
                'quantity' => $quantity,
                'price' => $product->price,
            ];
        }

        if ($lines === []) {
            return null;
        }

        $promoCode = fake()->boolean(28) ? $promoCodes->random() : null;
        $discountAmount = $this->calculateDiscount($promoCode, $subtotal);
        if ($discountAmount > 0 && $promoCode) {
            $promoCode->increment('usage_count');
        } else {
            $promoCode = null;
        }

        $createdAt = fake()->dateTimeBetween('-7 months', 'now');
        $order = Order::query()->create([
            'user_id' => $user->id,
            'address_id' => $address->id,
            'delivery_slot_id' => $deliverySlot->id,
            'promo_code_id' => $promoCode?->id,
            'total_price' => max(0, $subtotal + $deliverySlot->price - $discountAmount),
            'delivery_price' => $deliverySlot->price,
            'discount_amount' => $discountAmount,
            'total_quantity' => $totalQuantity,
            'payment_method' => $paymentMethod,
            'status' => $status,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        foreach ($lines as $line) {
            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $line['product']->id,
                'quantity' => $line['quantity'],
                'price' => $line['price'],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $stockItem = $line['product']->stockItem;

            if ($status === 'completed') {
                $stockItem->decrement('quantity', min($stockItem->quantity, $line['quantity']));
            }

            if ($status === 'pending') {
                $stockItem->increment('reserved_quantity', $line['quantity']);
            }
        }

        if ($promoCode) {
            $user->usedPromoCodes()->attach($promoCode->id, [
                'order_id' => $order->id,
                'used_at' => $createdAt,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        $paymentStatus = match ($status) {
            'completed' => 'paid',
            'canceled' => fake()->boolean(50) ? 'failed' : 'canceled',
            default => 'pending',
        };

        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'provider' => $paymentMethod === 'card' ? 'mock' : 'offline',
            'provider_payment_id' => $paymentMethod === 'card' ? (string) Str::uuid() : null,
            'amount' => $order->total_price,
            'currency' => $this->currencyCode(),
            'status' => $paymentStatus,
            'method' => $paymentMethod,
            'payload' => $paymentMethod === 'card'
                ? ['payment_url' => url('/payments/mock/'.$order->id)]
                : ['label' => 'cash_on_delivery'],
            'paid_at' => $paymentStatus === 'paid' ? $createdAt : null,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        OrderEvent::query()->create([
            'order_id' => $order->id,
            'actor_user_id' => $user->id,
            'type' => 'order_created',
            'title' => $this->content('events.order_created.title'),
            'message' => $this->content('events.order_created.message'),
            'meta' => ['payment_id' => $payment->id],
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        OrderEvent::query()->create([
            'order_id' => $order->id,
            'actor_user_id' => null,
            'type' => 'status_changed',
            'title' => $this->content('events.status_changed.title'),
            'message' => str_replace(
                [':status', ':payment_status'],
                [$status, $paymentStatus],
                (string) $this->content('events.status_changed.message'),
            ),
            'meta' => ['status' => $status, 'payment_status' => $paymentStatus],
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        return $order->load(['items.product', 'latestPayment', 'address', 'deliverySlot']);
    }

    /**
     * @param  Collection<int, Product>  $products
     * @param  Collection<int, PromoCode>  $promoCodes
     * @param  Collection<int, DeliverySlot>  $deliverySlots
     * @param  Collection<int, Order>  $orders
     */
    protected function seedDemoScenario(
        User $demoUser,
        Collection $products,
        Collection $promoCodes,
        Collection $deliverySlots,
        Collection &$orders,
    ): void {
        $addresses = $demoUser->addresses()->get();

        if ($addresses->isNotEmpty() && ! $orders->contains(fn (Order $order) => $order->user_id === $demoUser->id && $order->status === 'completed')) {
            $completedOrder = $this->createOrderForUser($demoUser, $addresses, $products, $promoCodes, $deliverySlots, 'completed');

            if ($completedOrder) {
                $orders->push($completedOrder);
            }
        }

        if ($addresses->isNotEmpty() && ! $orders->contains(fn (Order $order) => $order->user_id === $demoUser->id && $order->status === 'pending')) {
            $pendingOrder = $this->createOrderForUser($demoUser, $addresses, $products, $promoCodes, $deliverySlots, 'pending');

            if ($pendingOrder) {
                $orders->push($pendingOrder);
            }
        }

        if ($demoUser->cartItems()->exists()) {
            return;
        }

        $demoProducts = $products
            ->filter(fn (Product $product) => ($product->stockItem?->is_active ?? false) && ($product->stockItem->quantity > 0))
            ->take(2)
            ->values();

        foreach ($demoProducts as $index => $product) {
            CartItem::query()->create([
                'user_id' => $demoUser->id,
                'product_id' => $product->id,
                'quantity' => min(2 + $index, max(1, (int) $product->stockItem->quantity)),
                'created_at' => now()->subMinutes(30 - ($index * 5)),
                'updated_at' => now()->subMinutes(10 - ($index * 2)),
            ]);
        }
    }

    protected function calculateDiscount(?PromoCode $promoCode, float $subtotal): float
    {
        if (! $promoCode || ! $promoCode->is_active) {
            return 0;
        }

        if ($promoCode->min_order_amount && $subtotal < $promoCode->min_order_amount) {
            return 0;
        }

        return $promoCode->type === 'percent'
            ? round($subtotal * ($promoCode->value / 100), 2)
            : min($subtotal, (float) $promoCode->value);
    }

    /**
     * @param  Collection<int, Order>  $orders
     */
    protected function seedReviews(Collection $orders): void
    {
        $reviewedPairs = [];

        foreach ($orders->where('status', 'completed')->random(min(420, $orders->where('status', 'completed')->count())) as $order) {
            $order->loadMissing('items.product', 'user');

            foreach ($order->items as $item) {
                $key = $order->user_id.':'.$item->product_id;
                if (isset($reviewedPairs[$key])) {
                    continue;
                }

                Review::factory()->create([
                    'product_id' => $item->product_id,
                    'user_id' => $order->user_id,
                    'published_at' => fake()->dateTimeBetween($order->created_at, 'now'),
                ]);

                $reviewedPairs[$key] = true;

                if (count($reviewedPairs) >= 650) {
                    return;
                }
            }
        }
    }

    /**
     * @param  Collection<int, User>  $users
     * @param  Collection<int, Product>  $products
     */
    protected function seedCartsAndRecoveries(Collection $users, Collection $products): void
    {
        $cartUsers = $users->random(120);

        foreach ($cartUsers as $user) {
            $picked = $products->random(fake()->numberBetween(1, 5));
            $picked = $picked instanceof Product ? collect([$picked]) : $picked;

            foreach ($picked as $product) {
                $quantity = min(fake()->numberBetween(1, 3), max(1, (int) ($product->stockItem?->quantity ?? 1)));
                CartItem::query()->create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'created_at' => fake()->dateTimeBetween('-5 days', '-2 hours'),
                    'updated_at' => fake()->dateTimeBetween('-3 days', '-90 minutes'),
                ]);
            }
        }

        foreach ($cartUsers->random(45) as $user) {
            $snapshot = $user->cartItems()->with('product')->get()->map(function (CartItem $item) {
                return [
                    'product_id' => $item->product_id,
                    'name' => $item->product?->name,
                    'quantity' => $item->quantity,
                    'price' => $item->product?->price,
                    'image_url' => $item->product?->image_url,
                ];
            })->values()->all();

            $status = fake()->randomElement(['pending', 'sent', 'recovered']);
            $sentAt = $status !== 'pending' ? fake()->dateTimeBetween('-3 days', '-1 hour') : null;
            $recoveredAt = $status === 'recovered' ? fake()->dateTimeBetween($sentAt ?: '-2 days', 'now') : null;

            CartRecoveryReminder::query()->create([
                'user_id' => $user->id,
                'email' => $user->email,
                'token' => Str::random(40),
                'status' => $status,
                'last_cart_activity_at' => fake()->dateTimeBetween('-4 days', '-2 hours'),
                'queued_at' => fake()->dateTimeBetween('-4 days', '-2 hours'),
                'sent_at' => $sentAt,
                'recovered_at' => $recoveredAt,
                'recovered_reason' => $status === 'recovered' ? fake()->randomElement(['order_completed', 'cart_updated', 'email_recovery_visit']) : null,
                'cart_snapshot' => $snapshot,
            ]);
        }
    }

    /**
     * @param  Collection<int, Order>  $orders
     */
    protected function seedSubscriptions(Collection $orders): void
    {
        $sourceOrders = $orders->where('status', 'completed')->random(min(70, $orders->where('status', 'completed')->count()));

        foreach ($sourceOrders as $order) {
            $order->loadMissing('items.product', 'address', 'deliverySlot', 'latestPayment');

            if (! $order->address_id || ! $order->delivery_slot_id) {
                continue;
            }

            $status = fake()->randomElement(['active', 'active', 'active', 'paused', 'canceled']);
            $interval = fake()->randomElement([7, 14, 21, 30, 45]);
            $subscription = Subscription::query()->create([
                'user_id' => $order->user_id,
                'address_id' => $order->address_id,
                'delivery_slot_id' => $order->delivery_slot_id,
                'last_order_id' => $order->id,
                'source_order_id' => $order->id,
                'name' => $this->content('subscription_prefix').$order->id,
                'payment_method' => $order->payment_method,
                'status' => $status,
                'interval_days' => $interval,
                'next_run_at' => $status === 'canceled'
                    ? null
                    : fake()->dateTimeBetween('-10 days', '+20 days'),
                'last_run_at' => fake()->dateTimeBetween($order->created_at, 'now'),
                'canceled_at' => $status === 'canceled' ? fake()->dateTimeBetween('-20 days', 'now') : null,
            ]);

            foreach ($order->items as $item) {
                SubscriptionItem::query()->create([
                    'subscription_id' => $subscription->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ]);
            }
        }
    }

    /**
     * @param  Collection<int, User>  $users
     * @param  Collection<int, Order>  $orders
     */
    protected function seedReferrals(Collection $users, Collection $orders): void
    {
        $pairs = collect();
        $pool = $users->shuffle()->values();

        foreach ($pool as $candidate) {
            if ($pairs->count() >= 90) {
                break;
            }

            $referrer = $users->where('id', '!=', $candidate->id)->random();
            if (! $referrer || $pairs->contains(fn (array $pair) => $pair['referred']->id === $candidate->id)) {
                continue;
            }

            $pairs->push([
                'referrer' => $referrer,
                'referred' => $candidate,
            ]);
        }

        foreach ($pairs as $pair) {
            /** @var User $referrer */
            $referrer = $pair['referrer'];
            /** @var User $referred */
            $referred = $pair['referred'];
            $completedOrder = $orders
                ->where('user_id', $referred->id)
                ->where('status', 'completed')
                ->sortBy('created_at')
                ->first();

            $status = $completedOrder ? 'rewarded' : 'pending';
            $reward = fake()->randomElement([250, 300, 350, 500]);

            $referred->update([
                'referred_by_user_id' => $referrer->id,
            ]);

            Referral::query()->create([
                'referrer_user_id' => $referrer->id,
                'referred_user_id' => $referred->id,
                'order_id' => $completedOrder?->id,
                'code' => $referrer->referral_code,
                'status' => $status,
                'referred_email' => $referred->email,
                'reward_amount' => $reward,
                'rewarded_at' => $completedOrder ? $completedOrder->created_at : null,
                'meta' => [
                    'seeded' => true,
                ],
                'created_at' => fake()->dateTimeBetween('-6 months', '-1 week'),
                'updated_at' => now(),
            ]);

            if ($status === 'rewarded') {
                $referrer->increment('referral_credit_balance', $reward);
            }
        }
    }

    /**
     * @param  Collection<int, User>  $users
     * @param  Collection<int, Order>  $orders
     */
    protected function seedGiftCards(Collection $users, Collection $orders): void
    {
        foreach (range(1, 110) as $index) {
            $recipient = fake()->boolean(70) ? $users->random() : null;
            $purchaser = fake()->boolean(60) ? $users->where('id', '!=', $recipient?->id)->random() : null;
            $initial = fake()->randomElement([500, 1000, 1500, 2000, 3000]);
            $used = fake()->boolean(55) ? fake()->numberBetween(0, (int) ($initial * 0.85)) : 0;
            $balance = max($initial - $used, 0);
            $issuedAt = fake()->dateTimeBetween('-8 months', '-2 days');

            $giftCard = GiftCard::query()->create([
                'code' => 'DG-SEED-'.str_pad((string) $index, 4, '0', STR_PAD_LEFT),
                'name' => fake()->randomElement($this->content('gift_card_names')).' #'.$index,
                'message' => fake()->boolean(40) ? LocalizedTextFactory::sentence() : null,
                'purchaser_user_id' => $purchaser?->id,
                'recipient_user_id' => $recipient?->id,
                'order_id' => null,
                'initial_amount' => $initial,
                'balance' => $balance,
                'currency' => $this->currencyCode(),
                'issued_at' => $issuedAt,
                'expires_at' => fake()->boolean(70) ? fake()->dateTimeBetween('+15 days', '+12 months') : null,
                'is_active' => $balance > 0,
                'created_at' => $issuedAt,
                'updated_at' => now(),
            ]);

            if ($recipient && $used > 0) {
                $linkedOrder = $orders->where('user_id', $recipient->id)->sortByDesc('created_at')->first();

                \App\Models\GiftCardRedemption::query()->create([
                    'gift_card_id' => $giftCard->id,
                    'user_id' => $recipient->id,
                    'order_id' => $linkedOrder?->id,
                    'amount' => $used,
                    'redeemed_at' => fake()->dateTimeBetween($issuedAt, 'now'),
                    'meta' => ['seeded' => true],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
