<?php

namespace Database\Seeders;

class RussianDatabaseSeeder extends LocalizedDatabaseSeeder
{
    /**
     * @return array<string, mixed>
     */
    protected function seedContent(): array
    {
        return [
            'app_locale' => 'ru',
            'faker_locale' => 'ru_RU',
            'currency' => 'RUB',
            'phone_prefix' => '+79',
            'product_suffix' => 'мармелад',
            'admin_name' => 'Админ',
            'admin_phone' => '+79990000001',
            'demo_user_name' => 'Демо Пользователь',
            'demo_user_phone' => '+79990000002',
            'demo_user_address' => 'Москва, Тестовый переулок, 10',
            'address_labels' => ['Дом', 'Работа', 'Родители', 'Офис'],
            'cities' => ['Москва', 'Санкт-Петербург', 'Казань', 'Екатеринбург', 'Новосибирск'],
            'delivery' => [
                'label' => 'Доставка',
                'today' => 'Сегодня',
                'tomorrow' => 'Завтра',
            ],
            'categories' => [
                ['name' => 'Кислые', 'slug' => 'sour', 'sort_order' => 1],
                ['name' => 'Фруктовые', 'slug' => 'fruit', 'sort_order' => 2],
                ['name' => 'Подарочные наборы', 'slug' => 'gift-sets', 'sort_order' => 3],
                ['name' => 'Новинки', 'slug' => 'new-arrivals', 'sort_order' => 4],
                ['name' => 'Без сахара', 'slug' => 'sugar-free', 'sort_order' => 5],
                ['name' => 'Хиты продаж', 'slug' => 'best-sellers', 'sort_order' => 6],
            ],
            'events' => [
                'order_created' => [
                    'title' => 'Заказ создан',
                    'message' => 'Сидер создал заказ с полной корзиной и оплатой',
                ],
                'status_changed' => [
                    'title' => 'Статус заказа зафиксирован',
                    'message' => 'Сидер создал заказ со статусом :status и платежом :payment_status',
                ],
            ],
            'subscription_prefix' => 'Подписка #',
            'gift_card_names' => ['Подарочная карта', 'Сладкий подарок', 'Jelly bonus'],
        ];
    }
}
