<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Администратор
        User::factory()
            ->admin()
            ->create([
                'name' => 'Admin',
                'email' => 'admin@dinogel.ru',
                'password' => bcrypt('password'),
            ]);

        // Тестовый пользователь
        User::factory()->create([
            'name' => 'Chikbob',
            'email' => 'ricarrdo1488@gmail.com',
            'phone' => '+380992794421',
            'address' => 'Test Address',
            'password' => bcrypt('password'),
        ]);

        // Обычные пользователи
        User::factory(5)->create();

        // Продукты
        Product::factory(200)->create();
    }

    // +79499914746
}
