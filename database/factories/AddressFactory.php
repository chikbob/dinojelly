<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    public function definition(): array
    {
        $faker = fake(FactoryLocale::fakerLocale());

        return [
            'label' => $faker->randomElement(FactoryLocale::addressLabels()),
            'recipient_name' => $faker->name(),
            'phone' => FactoryLocale::phoneNumber(),
            'city' => $faker->randomElement(FactoryLocale::cities()),
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
