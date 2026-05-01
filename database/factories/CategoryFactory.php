<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = LocalizedTextFactory::categoryName();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => LocalizedTextFactory::categoryDescription(),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
