<?php

namespace App\Services;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\StorefrontProductResource;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CatalogService
{
    public function __construct(
        protected CartService $cartService,
        protected OrderService $orderService,
        protected ReviewService $reviewService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getCatalogPage(?User $user, array $filters = []): array
    {
        $favoriteIds = $this->getFavoriteIds($user);
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $productsQuery = Product::query()
            ->leftJoin('stock_items', 'stock_items.product_id', '=', 'products.id')
            ->select('products.*')
            ->with(['category', 'stockItem'])
            ->withCount(['reviews' => fn ($query) => $query->where('is_published', true)])
            ->withAvg(['reviews as average_rating' => fn ($query) => $query->where('is_published', true)], 'rating');

        if (!empty($filters['q'])) {
            $query = trim((string) $filters['q']);
            $productsQuery->where(function ($builder) use ($query) {
                $builder
                    ->where('name', 'like', '%' . $query . '%')
                    ->orWhere('description', 'like', '%' . $query . '%');
            });
        }

        if (!empty($filters['category'])) {
            $categorySlug = (string) $filters['category'];
            $productsQuery->whereHas('category', fn ($builder) => $builder->where('slug', $categorySlug));
        }

        if (isset($filters['min_price']) && $filters['min_price'] !== null && $filters['min_price'] !== '') {
            $productsQuery->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price']) && $filters['max_price'] !== null && $filters['max_price'] !== '') {
            $productsQuery->where('price', '<=', $filters['max_price']);
        }

        if (!empty($filters['on_sale'])) {
            $productsQuery->whereNotNull('old_price')
                ->whereColumn('old_price', '>', 'price');
        }

        $productsQuery->orderByRaw("
            CASE
                WHEN stock_items.is_active = 1
                    AND COALESCE(stock_items.quantity, 0) - COALESCE(stock_items.reserved_quantity, 0) > 0
                THEN 0
                ELSE 1
            END ASC
        ");

        match ($filters['sort'] ?? 'new') {
            'popular' => $productsQuery
                ->withCount('favorites')
                ->orderByDesc('favorites_count')
                ->orderByDesc('created_at'),
            'price_asc' => $productsQuery->orderBy('price'),
            'price_desc' => $productsQuery->orderByDesc('price'),
            'name_asc' => $productsQuery->orderBy('name'),
            default => $productsQuery->orderByDesc('created_at'),
        };

        $products = $productsQuery
            ->paginate(24)
            ->withQueryString()
            ->through(fn (Product $product) => $this->transformProduct($product, $favoriteIds));

        return [
            'products' => $products,
            'categories' => CategoryResource::collection($categories)->resolve(request()),
            'filters' => [
                'q' => $filters['q'] ?? '',
                'category' => $filters['category'] ?? '',
                'sort' => $filters['sort'] ?? 'new',
                'min_price' => $filters['min_price'] ?? '',
                'max_price' => $filters['max_price'] ?? '',
                'on_sale' => !empty($filters['on_sale']),
            ],
            'favorites' => $favoriteIds,
            'cartItems' => $user ? $this->cartService->getCartMap($user) : [],
            'cartCount' => $user ? $this->cartService->getCartCount($user) : 0,
            'pendingOrdersCount' => $user ? $this->orderService->getPendingOrdersCount($user) : 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getProductPage(Product $product, ?User $user): array
    {
        $product->loadMissing(['category', 'stockItem'])
            ->loadCount(['reviews' => fn ($query) => $query->where('is_published', true)])
            ->loadAvg(['reviews as average_rating' => fn ($query) => $query->where('is_published', true)], 'rating');
        $favoriteIds = $this->getFavoriteIds($user);
        $payload = $this->transformProduct($product, $favoriteIds);

        return [
            'product' => $payload,
            'reviews' => $this->reviewService->getPublishedReviewsPayload($product),
            'userReview' => $this->reviewService->getUserReviewPayload($product, $user),
            'canReview' => $this->reviewService->canUserReview($product, $user),
            'favorites' => $favoriteIds,
            'cartItems' => $user ? $this->cartService->getCartMap($user) : [],
            'cartCount' => $user ? $this->cartService->getCartCount($user) : 0,
            'pendingOrdersCount' => $user ? $this->orderService->getPendingOrdersCount($user) : 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getFavoritesPage(User $user, string $sort): array
    {
        $query = Favorite::query()
            ->where('user_id', $user->id)
            ->join('products', 'favorites.product_id', '=', 'products.id')
            ->select('favorites.*');

        match ($sort) {
            'created_at_asc' => $query->orderBy('favorites.created_at', 'asc'),
            'price_asc' => $query->orderBy('products.price', 'asc'),
            'price_desc' => $query->orderBy('products.price', 'desc'),
            default => $query->orderBy('favorites.created_at', 'desc'),
        };

        $favoriteIds = $this->getFavoriteIds($user);

        /** @var LengthAwarePaginator $favorites */
        $favorites = $query->with('product')
            ->paginate(24)
            ->withQueryString()
            ->through(function (Favorite $favorite) use ($favoriteIds) {
                $favorite->product->loadMissing(['category', 'stockItem']);
                return $this->transformProduct($favorite->product, $favoriteIds);
            });

        return [
            'favorites' => $favorites,
            'cartItems' => $this->cartService->getCartMap($user),
            'cartCount' => $this->cartService->getCartCount($user),
            'order' => $sort,
            'pendingOrdersCount' => $this->orderService->getPendingOrdersCount($user),
        ];
    }

    public function toggleFavorite(User $user, int $productId): void
    {
        $favorite = Favorite::query()
            ->where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return;
        }

        Favorite::query()->create([
            'user_id' => $user->id,
            'product_id' => $productId,
        ]);
    }

    /**
     * @param array<int, int> $favoriteIds
     * @return array<string, mixed>
     */
    protected function transformProduct(Product $product, array $favoriteIds): array
    {
        return array_merge(
            StorefrontProductResource::make($product)->resolve(request()),
            ['is_favorite' => in_array($product->id, $favoriteIds, true)]
        );
    }

    /**
     * @return array<int, int>
     */
    protected function getFavoriteIds(?User $user): array
    {
        if (!$user) {
            return [];
        }

        return $user->favorites()
            ->pluck('product_id')
            ->toArray();
    }
}
