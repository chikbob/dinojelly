<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'rating' => fake()->numberBetween(3, 5),
            'title' => fake('ru_RU')->sentence(4),
            'body' => fake('ru_RU')->paragraph(3),
            'is_published' => fake()->boolean(90),
            'published_at' => now(),
        ];
    }
}
