<?php

namespace Database\Factories;

use Illuminate\Support\Str;

class LocalizedTextFactory
{
    public static function categoryName(): string
    {
        return fake()->unique()->randomElement(self::data()['category_names']);
    }

    public static function categoryDescription(): string
    {
        return self::paragraph(2);
    }

    public static function collectionName(): string
    {
        return fake()->randomElement(self::data()['collection_names']);
    }

    public static function collectionDescription(): string
    {
        return self::paragraph(2);
    }

    public static function productName(): string
    {
        $prefix = fake()->randomElement(self::data()['product_prefixes']);
        $flavor = fake()->randomElement(self::data()['product_flavors']);

        return Str::ucfirst($prefix.' '.$flavor.' '.FactoryLocale::productSuffix());
    }

    public static function productDescription(): string
    {
        return self::paragraph(3);
    }

    public static function promoName(): string
    {
        return fake()->randomElement(self::data()['promo_names']);
    }

    public static function reviewTitle(): string
    {
        return fake()->randomElement(self::data()['review_titles']);
    }

    public static function reviewBody(): string
    {
        return self::paragraph(3);
    }

    public static function sentence(): string
    {
        return fake()->randomElement(self::data()['sentences']);
    }

    public static function paragraph(int $sentences = 3): string
    {
        return implode(' ', fake()->randomElements(self::data()['sentences'], $sentences));
    }

    /**
     * @return array<string, array<int, string>>
     */
    private static function data(): array
    {
        return self::russian();
    }

    /**
     * @return array<string, array<int, string>>
     */
    private static function russian(): array
    {
        return [
            'category_names' => [
                'Ягодные миксы',
                'Тропические вкусы',
                'Для детей',
                'Желейные ленты',
                'Пикантные вкусы',
                'Классические формы',
                'Цитрусовые',
                'Мягкие жевательные',
                'Мини-наборы',
                'Большие наборы',
                'Праздничные боксы',
                'Экзотические вкусы',
                'Лимитированная серия',
                'Яркие ассорти',
                'Мармеладные кубики',
                'Сезонные вкусы',
                'Коллекция манго',
                'Клубничные фавориты',
                'Миксы для вечеринки',
                'Премиальные наборы',
                'Желейные сердца',
                'Кисло-сладкие',
                'Легкие десерты',
                'Семейные наборы',
            ],
            'collection_names' => [
                'Фруктовый праздник',
                'Коробка недели',
                'Яркий микс',
                'Сладкий сюрприз',
                'Большой хит',
                'Подарок к празднику',
                'Ассорти для друзей',
                'Тропический набор',
                'Любимые вкусы',
                'Домашняя вечеринка',
            ],
            'product_prefixes' => [
                'ягодный',
                'манговый',
                'цитрусовый',
                'яблочный',
                'вишневый',
                'персиковый',
                'малиновый',
                'ананасовый',
                'лимонный',
                'клубничный',
            ],
            'product_flavors' => [
                'микс',
                'взрыв',
                'твист',
                'бриз',
                'джем',
                'ритм',
                'заряд',
                'дуэт',
                'коктейль',
                'бум',
            ],
            'promo_names' => [
                'Сладкая скидка',
                'Весенний бонус',
                'Подарок к заказу',
                'Вкусная выгода',
                'Яркий купон',
                'Сезонный бонус',
                'Скидка для друзей',
                'Фруктовая акция',
            ],
            'review_titles' => [
                'Очень понравился вкус',
                'Отличный набор к подарку',
                'Мягкий и свежий мармелад',
                'Упаковка приехала аккуратно',
                'Снова заказал бы этот вкус',
                'Хороший баланс сладости',
                'Детям особенно понравилось',
                'Удачный вариант для праздника',
            ],
            'sentences' => [
                'Яркий вкус хорошо раскрывается уже с первой конфеты и оставляет приятное фруктовое послевкусие.',
                'Мармелад удобно брать в подарок, потому что упаковка выглядит аккуратно и празднично.',
                'Текстура остается мягкой, а сахарная обсыпка не перебивает основной вкус.',
                'Набор хорошо подходит для семейных заказов, когда хочется сразу несколько разных сочетаний.',
                'В этой позиции удачно сочетаются сладкие ноты и легкая кислинка без лишней резкости.',
                'Такой вкус часто выбирают для вечеринок, потому что его удобно делить на компанию.',
                'Плотность и форма сохраняются даже после доставки, поэтому товар доезжает в хорошем состоянии.',
                'Это хороший вариант для покупателей, которые хотят попробовать что-то яркое, но не слишком приторное.',
                'Внутри набора удобно собраны популярные позиции, поэтому он подходит и для знакомства с каталогом.',
                'Такой мармелад особенно нравится тем, кто любит насыщенные фруктовые сочетания и мягкую текстуру.',
                'В описании акцент сделан на балансе вкуса, и на практике это действительно чувствуется.',
                'Порция подходит как для небольшого перекуса, так и для дополнения к праздничному столу.',
            ],
        ];
    }

}
