<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\DeliverySlot;
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

        // Категории
        $categories = collect([
            ['name' => 'Кислые', 'slug' => 'sour', 'sort_order' => 1],
            ['name' => 'Фруктовые', 'slug' => 'fruit', 'sort_order' => 2],
            ['name' => 'Подарочные наборы', 'slug' => 'gift-sets', 'sort_order' => 3],
            ['name' => 'Новинки', 'slug' => 'new-arrivals', 'sort_order' => 4],
        ])->map(fn ($item) => Category::query()->create([
            'name' => $item['name'],
            'slug' => $item['slug'],
            'sort_order' => $item['sort_order'],
            'is_active' => true,
        ]));

        collect([
            ['name' => 'Сегодня 12:00–15:00', 'offset_start' => 2, 'offset_end' => 5, 'price' => 250],
            ['name' => 'Сегодня 18:00–21:00', 'offset_start' => 8, 'offset_end' => 11, 'price' => 300],
            ['name' => 'Завтра 10:00–13:00', 'offset_start' => 24, 'offset_end' => 27, 'price' => 200],
        ])->each(function ($slot) {
            DeliverySlot::query()->create([
                'name' => $slot['name'],
                'starts_at' => now()->addHours($slot['offset_start']),
                'ends_at' => now()->addHours($slot['offset_end']),
                'capacity' => 20,
                'price' => $slot['price'],
                'is_active' => true,
            ]);
        });

        // Продукты
        Product::factory(200)->make()->each(function (Product $product) use ($categories) {
            $product->category_id = $categories->random()->id;
            $product->save();
        });
    }

    // +79499914746
}
