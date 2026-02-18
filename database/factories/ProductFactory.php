<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition()
    {
        $faker = \Faker\Factory::create('ua_UA');

        return [
            'name' => $faker->words(2, true) . ' мармелад',
            'weight' => $faker->numberBetween(50, 300),
            'price' => $faker->numberBetween(300, 2000),
            'old_price' => $faker->numberBetween(2001, 3000),
            'description' => $faker->sentence(6),
            'image' => 'https://cdn.27.ua/sc--media--prod/default/53/9e/a5/539ea5fa-f1cd-4e2b-bb07-823702dc1b6c.jpg',
        ];
    }

    private function generateProductName($faker): string
    {
        $types = ['Кислий', 'Солодкий', 'Фруктовий', 'Ягодний'];
        $forms = ['мармелад', 'желе', 'десерт'];

        return $faker->randomElement($types) . ' ' .
            $faker->randomElement($forms) . ' ' .
            $faker->word;
    }
}
