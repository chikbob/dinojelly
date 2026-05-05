<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $faker = fake(FactoryLocale::fakerLocale());

        return [
            'name' => $faker->firstName.' '.$faker->lastName,
            'email' => $faker->unique()->safeEmail,
            'phone' => FactoryLocale::phoneNumber(),
            'address' => $faker->address,
            'role' => 'user',
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * Администратор
     */
    public function admin(): static
    {
        return $this->state([
            'role' => 'admin',
        ]);
    }
}
