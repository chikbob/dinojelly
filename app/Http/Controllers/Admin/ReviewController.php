<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::query()
            ->with(['product', 'user'])
            ->latest()
            ->paginate(20)
            ->through(fn (Review $review) => [
                'id' => $review->id,
                'rating' => $review->rating,
                'title' => $review->title,
                'body' => $review->body,
                'is_published' => $review->is_published,
                'published_at' => $review->published_at,
                'created_at' => $review->created_at,
                'product' => $review->product ? [
                    'id' => $review->product->id,
                    'name' => $review->product->name,
                ] : null,
                'user' => $review->user ? [
                    'id' => $review->user->id,
                    'name' => $review->user->name,
                    'email' => $review->user->email,
                ] : null,
            ]);

        return Inertia::render('admin/Reviews/Index', [
            'reviews' => $reviews,
        ]);
    }

    public function update(Request $request, Review $review)
    {
        $data = $request->validate([
            'is_published' => ['required', 'boolean'],
        ]);

        $review->update([
            'is_published' => $data['is_published'],
            'published_at' => $data['is_published'] ? now() : null,
        ]);

        return redirect()->back()->with('success', 'Review updated');
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return redirect()->back()->with('success', 'Review deleted');
    }
}
