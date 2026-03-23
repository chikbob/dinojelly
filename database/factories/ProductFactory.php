<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $faker = fake('ru_RU');
        $price = $faker->numberBetween(190, 1990);
        $hasDiscount = $faker->boolean(55);

        return [
            'name' => ucfirst($faker->words(2, true)) . ' мармелад',
            'weight' => $faker->numberBetween(50, 300),
            'price' => $price,
            'old_price' => $hasDiscount ? $price + $faker->numberBetween(50, 500) : null,
            'description' => $faker->paragraph(2),
            'image' => $faker->randomElement([
                'https://cdn.27.ua/sc--media--prod/default/53/9e/a5/539ea5fa-f1cd-4e2b-bb07-823702dc1b6c.jpg',
                'https://images.unsplash.com/photo-1582058091505-f87a2e55a40f?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1621939514649-280e2ee25f60?auto=format&fit=crop&w=1200&q=80',
            ]),
        ];
    }
}
