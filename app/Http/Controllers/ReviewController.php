<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        protected ReviewService $reviewService,
    ) {
    }

    public function store(Product $product, Request $request)
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->reviewService->upsertReview($product, $request->user(), $data);

        return redirect()->back()->with('success', 'Отзыв сохранён');
    }

    public function destroy(Product $product, Request $request)
    {
        $this->reviewService->deleteReview($product, $request->user());

        return redirect()->back()->with('success', 'Отзыв удалён');
    }
}
