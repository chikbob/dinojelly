<?php

namespace App\Services;

use App\Http\Resources\ReviewResource;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class ReviewService
{
    public function canUserReview(Product $product, ?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return OrderItem::query()
            ->where('product_id', $product->id)
            ->whereHas('order', function ($query) use ($user) {
                $query
                    ->where('user_id', $user->id)
                    ->where('status', '!=', 'canceled');
            })
            ->exists();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getUserReviewPayload(Product $product, ?User $user): ?array
    {
        if (! $user) {
            return null;
        }

        $review = Review::query()
            ->where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->with('user')
            ->first();

        return $review ? ReviewResource::make($review)->resolve(request()) : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPublishedReviewsPayload(Product $product): array
    {
        $reviews = Review::query()
            ->where('product_id', $product->id)
            ->where('is_published', true)
            ->with('user')
            ->latest('published_at')
            ->latest('id')
            ->get();

        return ReviewResource::collection($reviews)->resolve(request());
    }

    /**
     * @param  array{rating:int,title?:?string,body?:?string}  $data
     */
    public function upsertReview(Product $product, User $user, array $data): Review
    {
        if (! $this->canUserReview($product, $user)) {
            throw new AuthorizationException('Отзыв можно оставить только после покупки товара');
        }

        return Review::query()->updateOrCreate(
            [
                'product_id' => $product->id,
                'user_id' => $user->id,
            ],
            [
                'rating' => $data['rating'],
                'title' => $data['title'] ?? null,
                'body' => $data['body'] ?? null,
                'is_published' => true,
                'published_at' => now(),
            ],
        );
    }

    /**
     * @throws AuthorizationException
     */
    public function deleteReview(Product $product, User $user): void
    {
        $review = Review::query()
            ->where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $review) {
            throw new \RuntimeException('Отзыв не найден');
        }

        if ($review->user_id !== $user->id) {
            throw new AuthorizationException('Нельзя удалить чужой отзыв');
        }

        $review->delete();
    }
}
