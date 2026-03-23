<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    public function definition(): array
    {
        $faker = fake('ru_RU');

        return [
            'label' => $faker->randomElement(['Дом', 'Работа', 'Родители', 'Офис']),
            'recipient_name' => $faker->name(),
            'phone' => '+79' . $faker->numerify('#########'),
            'city' => $faker->randomElement(['Москва', 'Санкт-Петербург', 'Казань', 'Екатеринбург', 'Новосибирск']),
            'street' => $faker->streetName(),
            'building' => (string) $faker->buildingNumber(),
            'apartment' => (string) $faker->numberBetween(1, 250),
            'entrance' => (string) $faker->numberBetween(1, 8),
            'floor' => (string) $faker->numberBetween(1, 25),
            'postal_code' => $faker->postcode(),
            'comment' => $faker->boolean(35) ? $faker->sentence() : null,
            'is_default' => false,
        ];
    }
}
