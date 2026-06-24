<?php

namespace Database\Factories;

class FactoryLocale
{
    public static function fakerLocale(): string
    {
        return (string) config('database.seeding.faker_locale', config('app.faker_locale', 'en_US'));
    }

    public static function phonePrefix(): string
    {
        return (string) config('database.seeding.phone_prefix', '+79');
    }

    public static function phoneNumber(): string
    {
        return self::phonePrefix().fake()->numerify('#########');
    }

    /**
     * @return array<int, string>
     */
    public static function addressLabels(): array
    {
        return (array) config('database.seeding.address_labels', ['Дом', 'Работа', 'Родители', 'Офис']);
    }

    /**
     * @return array<int, string>
     */
    public static function cities(): array
    {
        return (array) config('database.seeding.cities', ['Москва', 'Санкт-Петербург', 'Казань', 'Екатеринбург', 'Новосибирск']);
    }

    public static function deliverySlotLabel(): string
    {
        return (string) config('database.seeding.delivery_slot_label', 'Доставка');
    }

    public static function productSuffix(): string
    {
        return (string) config('database.seeding.product_suffix', 'мармелад');
    }
}
