<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DeliverySlotFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('now', '+20 days');
        $end = (clone $start)->modify('+3 hours');

        return [
            'name' => 'Доставка ' . $start->format('d.m H:i') . '-' . $end->format('H:i'),
            'starts_at' => $start,
            'ends_at' => $end,
            'capacity' => fake()->numberBetween(15, 60),
            'price' => fake()->randomElement([0, 150, 200, 250, 300, 350]),
            'is_active' => fake()->boolean(95),
        ];
    }
}
