<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CollectionFactory extends Factory
{
    public function definition(): array
    {
        $name = LocalizedTextFactory::collectionName();

        return [
            'name' => $name,
            'slug' => Str::slug($name . '-' . fake()->unique()->numberBetween(100, 999)),
            'description' => LocalizedTextFactory::collectionDescription(),
            'image' => 'https://images.unsplash.com/photo-1519864600265-abb23847ef2c?auto=format&fit=crop&w=1200&q=80',
            'is_active' => fake()->boolean(90),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
