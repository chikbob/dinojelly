<?php

namespace App\Services;

use App\Http\Resources\StorefrontProductResource;
use App\Models\Product;
use App\Models\RecommendationRequest;
use App\Models\User;
use Illuminate\Support\Collection;

class RecommendationAssistantService
{
    /**
     * @param array{occasion: string, taste: string, budget: float|int, format: string, priority: string} $input
     * @return array<string, mixed>
     */
    public function recommend(?User $user, array $input): array
    {
        $signals = $this->buildUserSignals($user);
        $favoriteProductIds = $signals['favorite_product_ids'];

        $products = Product::query()
            ->with(['category', 'stockItem'])
            ->withCount(['reviews' => fn ($query) => $query->where('is_published', true), 'favorites'])
            ->withAvg(['reviews as average_rating' => fn ($query) => $query->where('is_published', true)], 'rating')
            ->get()
            ->filter(fn (Product $product) => ($product->stockItem?->is_active ?? false) && (($product->stockItem->quantity - $product->stockItem->reserved_quantity) > 0))
            ->map(fn (Product $product) => $this->scoreProduct($product, $input, $signals))
            ->sortByDesc('score')
            ->take(4)
            ->values();

        $result = [
            'summary' => $this->buildSummary($products, $input),
            'products' => $products->map(function (array $item) use ($favoriteProductIds) {
                /** @var Product $product */
                $product = $item['product'];

                return array_merge(
                    StorefrontProductResource::make($product)->resolve(request()),
                    [
                        'is_favorite' => in_array($product->id, $favoriteProductIds, true),
                        'recommendation_reason_keys' => $item['reason_keys'],
                        'recommendation_score' => $item['score'],
                    ],
                );
            })->all(),
            'input' => $input,
        ];

        RecommendationRequest::query()->create([
            'user_id' => $user?->id,
            'input_payload' => $input,
            'result_payload' => [
                'product_ids' => collect($result['products'])->pluck('id')->all(),
                'summary' => $result['summary'],
            ],
        ]);

        return $result;
    }

    /**
     * @param array{occasion: string, taste: string, budget: float|int, format: string, priority: string} $input
     * @return array{product: Product, score: int, reason_keys: array<int, string>}
     */
    protected function scoreProduct(Product $product, array $input, array $signals): array
    {
        $score = 0;
        $reasons = [];
        $categorySlug = $product->category?->slug ?? '';
        $budget = (float) $input['budget'];

        if ($budget > 0) {
            $delta = abs($budget - (float) $product->price);
            $score += max(0, 40 - (int) floor($delta / 50));

            if ($product->price <= $budget) {
                $reasons[] = 'catalog.assistant.reasons.fitsBudget';
            }
        }

        if ($input['occasion'] === 'gift' && ($categorySlug === 'gift-sets' || $product->weight >= 600)) {
            $score += 45;
            $reasons[] = 'catalog.assistant.reasons.goodForGift';
        }

        if ($input['occasion'] === 'party' && $product->weight >= 500) {
            $score += 35;
            $reasons[] = 'catalog.assistant.reasons.goodForCompany';
        }

        if ($input['occasion'] === 'kids' && in_array($categorySlug, ['fruit', 'best-sellers'], true)) {
            $score += 35;
            $reasons[] = 'catalog.assistant.reasons.kidsLike';
        }

        if ($input['taste'] === 'sour' && $categorySlug === 'sour') {
            $score += 50;
            $reasons[] = 'catalog.assistant.reasons.matchesSourTaste';
        }

        if ($input['taste'] === 'fruity' && in_array($categorySlug, ['fruit', 'new-arrivals'], true)) {
            $score += 45;
            $reasons[] = 'catalog.assistant.reasons.matchesFruityTaste';
        }

        if ($input['taste'] === 'light' && in_array($categorySlug, ['sugar-free', 'fruit'], true)) {
            $score += 40;
            $reasons[] = 'catalog.assistant.reasons.lighterOption';
        }

        if ($input['format'] === 'set' && ($categorySlug === 'gift-sets' || $product->weight >= 700)) {
            $score += 40;
            $reasons[] = 'catalog.assistant.reasons.setFormat';
        }

        if ($input['format'] === 'single' && $product->weight <= 350) {
            $score += 30;
            $reasons[] = 'catalog.assistant.reasons.singleFormat';
        }

        if ($input['format'] === 'variety' && $product->old_price) {
            $score += 20;
            $reasons[] = 'catalog.assistant.reasons.varietyTrial';
        }

        if ($input['priority'] === 'value' && $product->old_price && $product->old_price > $product->price) {
            $score += 35;
            $reasons[] = 'catalog.assistant.reasons.hasDiscount';
        }

        if ($input['priority'] === 'popular') {
            $score += (int) min(30, ($product->favorites_count ?? 0) * 2);
            $score += (int) min(20, round((float) ($product->average_rating ?? 0) * 4));
            $reasons[] = 'catalog.assistant.reasons.socialProof';
        }

        if ($input['priority'] === 'new' && $product->created_at?->gt(now()->subMonths(2))) {
            $score += 35;
            $reasons[] = 'catalog.assistant.reasons.freshNew';
        }

        if ($product->category_id && in_array($product->category_id, $signals['favorite_category_ids'], true)) {
            $score += 25;
            $reasons[] = 'catalog.assistant.reasons.similarToFavorites';
        }

        if ($product->category_id && in_array($product->category_id, $signals['ordered_category_ids'], true)) {
            $score += 18;
            $reasons[] = 'catalog.assistant.reasons.basedOnOrders';
        }

        if ($reasons === []) {
            $reasons[] = 'catalog.assistant.reasons.balanced';
        }

        return [
            'product' => $product,
            'score' => $score,
            'reason_keys' => collect($reasons)->unique()->take(2)->values()->all(),
        ];
    }

    /**
     * @param Collection<int, array{product: Product, score: int, reason_keys: array<int, string>}> $products
     */
    protected function buildSummary(Collection $products, array $input): array
    {
        if ($products->isEmpty()) {
            return [
                'key' => 'catalog.assistant.summary.empty',
                'params' => [],
            ];
        }

        $top = $products->first();
        $names = $products->pluck('product.name')->take(3)->implode(', ');

        return [
            'key' => 'catalog.assistant.summary.found',
            'params' => [
                'occasion' => $input['occasion'],
                'top' => $top['product']->name,
                'names' => $names,
            ],
        ];
    }

    /**
     * @return array{favorite_product_ids: array<int, int>, favorite_category_ids: array<int, int>, ordered_category_ids: array<int, int>}
     */
    protected function buildUserSignals(?User $user): array
    {
        if (!$user) {
            return [
                'favorite_product_ids' => [],
                'favorite_category_ids' => [],
                'ordered_category_ids' => [],
            ];
        }

        return [
            'favorite_product_ids' => $user->favorites()
                ->pluck('product_id')
                ->filter()
                ->unique()
                ->values()
                ->all(),
            'favorite_category_ids' => $user->favorites()
                ->join('products', 'favorites.product_id', '=', 'products.id')
                ->pluck('products.category_id')
                ->filter()
                ->unique()
                ->values()
                ->all(),
            'ordered_category_ids' => $user->orders()
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->pluck('products.category_id')
                ->filter()
                ->unique()
                ->values()
                ->all(),
        ];
    }
}
