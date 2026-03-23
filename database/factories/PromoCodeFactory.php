<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PromoCodeFactory extends Factory
{
    public function definition(): array
    {
        $startsAt = fake()->boolean(70) ? fake()->dateTimeBetween('-30 days', '+10 days') : null;
        $expiresAt = $startsAt ? (clone $startsAt)->modify('+' . fake()->numberBetween(10, 90) . ' days') : null;
        $type = fake()->randomElement(['fixed', 'percent']);

        return [
            'code' => strtoupper(Str::random(8)),
            'name' => fake('ru_RU')->words(2, true),
            'type' => $type,
            'value' => $type === 'percent'
                ? fake()->numberBetween(5, 30)
                : fake()->numberBetween(100, 700),
            'min_order_amount' => fake()->boolean(60) ? fake()->numberBetween(800, 3500) : null,
            'usage_limit' => fake()->boolean(70) ? fake()->numberBetween(20, 400) : null,
            'usage_count' => 0,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'is_active' => fake()->boolean(85),
        ];
    }
}
