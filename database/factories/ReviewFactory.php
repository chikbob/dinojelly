<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'rating' => fake()->numberBetween(3, 5),
            'title' => LocalizedTextFactory::reviewTitle(),
            'body' => LocalizedTextFactory::reviewBody(),
            'is_published' => fake()->boolean(90),
            'published_at' => now(),
        ];
    }
}
