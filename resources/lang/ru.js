export default {
    header: {
        address: "проспект Мира, 111",
        return: "Возврат",
        payment: "Оплата",
        gift: "Подарочный сертификат",
        home: "Главная",
        login: "Войти",
        logout: "Выйти",
        favorites: "Избранное",
        cart: "Корзина",
        orders: "Заказы",
    },
    footer: {
        help: "Помощь",
        return: "Возврат",
        payment: "Оплата",
        copyright: " \"DINOJELLY\". Все права защищены."
    },
    catalog: {
        title: "Каталог мармелада",
        addToCart: "Добавить в корзину"
    },
    cart: {
        title: "Ваша корзина",
        empty: "Ваша корзина пуста.",
        delete: "Удалить",
        total: "Общая сумма",
        clear: "Очистить корзину",
        checkout: "Оформить заказ",
        deliveryHint: "Доступные способы и время доставки можно выбрать при оформлении заказа",
        yourCart: "Ваша корзина",
        items: "Товары",
        itemsShort: "шт.",
        discount: "Скидка",
        finalTotal: "Финальная стоимость",
    },
    product: {
        addToCart: "Добавить в корзину"
    },
    profile: {
        title: "Профиль пользователя",
        name: "Имя",
        email: "Email",
        verified: "Email подтверждён",
        notVerified: "Email не подтверждён",
        phone: "Телефон",
        address: "Адрес",
        registeredAt: "Дата регистрации",
        edit: "Редактировать",
        notProvided: "Не указано",
        save: "Сохранить",
        cancel: "Отмена",
    },
    auth: {
        loginTitle: "Войти",
        registerTitle: "Регистрация",
        email: "Электронная почта",
        password: "Пароль",
        passwordConfirm: "Подтвердите пароль",
        name: "Имя",
        phone: "Телефон",
        noAccount: "Нет аккаунта?",
        haveAccount: "Уже есть аккаунт?",
        wait: "Подождите...",
        login: "Войти",
        register: "Зарегистрироваться",
        failed: "Неверный email или пароль",
        register_success: "Регистрация успешна! Добро пожаловать.",
        logged_out: "Вы успешно вышли из аккаунта.",
    },
    validation: {
        name_required: "Имя обязательно",
        phone_required: "Телефон обязателен",
        email_required: "Электронная почта обязательна",
        email_email: "Неверный формат email",
        email_unique: "Этот email уже зарегистрирован",
        password_required: "Пароль обязателен",
        password_min: "Пароль должен содержать минимум 8 символов",
        password_confirmed: "Пароли не совпадают",
    },
    favorites: {
        title: "Ваше избранное",
        empty: "У вас пока нет избранных товаров.",
        sort: "Сортировка",
        newFirst: "Сначала новые",
        oldFirst: "Сначала старые",
        cheapFirst: "Сначала дешёвые",
        expensiveFirst: "Сначала дорогие",
    },
    currency: {
        symbol: "₽"
    },
    orders: {
        title: "Мои заказы",
        filterByStatus: "Фильтр по статусу",
        all: "Все",
        pending: "В обработке",
        completed: "Завершён",
        canceled: "Отменён",

        status: {
            pending: "В обработке",
            completed: "Завершён",
            canceled: "Отменён"
        },

        amount: "Сумма",
        itemsCount: "Товаров",
        payment: "Оплата",
        date: "Дата",
        card: "Карта",
        cash: "Наличные",

        empty: "У вас пока нет заказов"
    },
    order: {
        orderNumber: "Заказ",
        payment: "Оплата",
        amount: "Сумма",
        quantity: "Количество товаров",
        items: "Товары в заказе",
        statusText: "Статус заказа",

        status: {
            pending: "В обработке",
            completed: "Завершён",
            canceled: "Отменён"
        },

        card: "Карта",
        cash: "Наличные",

        cancel: "Отменить заказ",
        canceling: "Отмена...",
        confirmCancel: "Вы уверены, что хотите отменить заказ?",
        back: "Вернуться к заказам"
    },

    checkout: {
        title: "Оформление заказа",
        items: "Товары",
        total: "Итого",
        choosePayment: "Выберите способ оплаты",
        payCard: "Оплатить картой",
        payCash: "Оплатить наличными"
    },

    pagination: {
        previous: "Предыдущая",
        next: "Следующая",
    },

    admin: {
        sidebar: {
            dashboard: "Дашборд",
            products: "Товары",
            orders: "Заказы",
            users: "Пользователи",
        },
        users: {
            title: "Пользователи",
            id: "ID",
            name: "Имя",
            email: "Email",
        },
        orders: {
            title: "Заказы",
            id: "ID",
            status: "Статус",
            totalPrice: "Общая сумма",
            createdAt: "Дата создания",
            actions: "Действия",
            view: "Просмотр",
            orderNumber: "Заказ",

            statuses: {
                pending: "В ожидании",
                completed: "Завершён",
                canceled: "Отменён",
            }
        },
        dashboard: {
            title: "Дашборд",
            users: "Пользователи",
            products: "Товары",
            orders: "Заказы",
            chartTitle: "Заказы по дням",
        },
        products: {
            title: "Товары",
            createNew: "Создать новый товар",
            id: "ID",
            image: "Изображение",
            name: "Название",
            weight: "Вес",
            price: "Цена",
            oldPrice: "Старая цена",
            actions: "Действия",
            edit: "Редактировать",
            delete: "Удалить",
            confirmDelete: "Вы уверены, что хотите удалить этот товар?",

            editProduct: "Редактировать товар",
            createProduct: "Создать товар",
            save: "Сохранить",
            create: "Создать",
            optional: "необязательно",

            fields: {
                name: "Название",
                weight: "Вес",
                price: "Цена",
                oldPrice: "Старая цена",
                description: "Описание",
                imageUrl: "URL изображения",
                image: "Изображение",
            }
        },
        header: {
            title: "Админ-панель",
            logout: "Выйти",
        }
    }

}
