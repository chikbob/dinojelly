<?php

namespace Database\Seeders;

class UkrainianDatabaseSeeder extends LocalizedDatabaseSeeder
{
    /**
     * @return array<string, mixed>
     */
    protected function seedContent(): array
    {
        return [
            'app_locale' => 'uk',
            'faker_locale' => 'uk_UA',
            'currency' => 'UAH',
            'phone_prefix' => '+380',
            'product_suffix' => 'мармелад',
            'admin_name' => 'Адмін',
            'admin_phone' => '+380990000001',
            'demo_user_name' => 'Демо Користувач',
            'demo_user_phone' => '+380990000002',
            'demo_user_address' => 'Київ, Тестовий провулок, 10',
            'address_labels' => ['Дім', 'Робота', 'Батьки', 'Офіс'],
            'cities' => ['Київ', 'Львів', 'Одеса', 'Харків', 'Дніпро'],
            'delivery' => [
                'label' => 'Доставка',
                'today' => 'Сьогодні',
                'tomorrow' => 'Завтра',
            ],
            'categories' => [
                ['name' => 'Кислі', 'slug' => 'sour', 'sort_order' => 1],
                ['name' => 'Фруктові', 'slug' => 'fruit', 'sort_order' => 2],
                ['name' => 'Подарункові набори', 'slug' => 'gift-sets', 'sort_order' => 3],
                ['name' => 'Новинки', 'slug' => 'new-arrivals', 'sort_order' => 4],
                ['name' => 'Без цукру', 'slug' => 'sugar-free', 'sort_order' => 5],
                ['name' => 'Хіти продажу', 'slug' => 'best-sellers', 'sort_order' => 6],
            ],
            'events' => [
                'order_created' => [
                    'title' => 'Замовлення створено',
                    'message' => 'Сидер створив замовлення з повним кошиком і оплатою',
                ],
                'status_changed' => [
                    'title' => 'Статус замовлення зафіксовано',
                    'message' => 'Сидер створив замовлення зі статусом :status та платежем :payment_status',
                ],
            ],
            'subscription_prefix' => 'Підписка #',
            'gift_card_names' => ['Подарункова картка', 'Солодкий подарунок', 'Jelly bonus'],
        ];
    }
}
