<template>
    <header class="header" style="width:100%; max-width:100%; overflow:hidden; box-sizing:border-box;">
        <!-- Верхний ряд: адрес и навигация -->
        <div class="header__top" style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; width:100%; max-width:100%; min-width:0;">
            <div class="header__address" style="max-width:100%; min-width:0;">{{ t("header.address") }}</div>
            <nav class="header__nav" style="display:flex; gap:12px; flex-wrap:wrap; max-width:100%; min-width:0;">
                <a href="/returns" class="header__link">{{ t("header.return") }}</a>
                <a href="/payment" class="header__link">{{ t("header.payment") }}</a>
                <a href="/gift-certificate" class="header__link">{{ t("header.gift") }}</a>
                <a href="/help" class="header__link">{{ t("header.help") }}</a>
            </nav>
        </div>

        <!-- Нижний ряд: логотип, дом, пользовательские элементы -->
        <div class="header__bottom" style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:nowrap; width:100%; max-width:100%; min-width:0;">
            <div class="header__logo-block" style="display:flex; align-items:center; flex:0 0 auto; max-width:100%; min-width:0;">
                <a href="/" class="header__auth-link">
                    <img src="/logo.png" alt="Dino Jelly" class="header__logo-img"/>
                </a>
            </div>

            <div class="header__actions" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; justify-content:flex-end; flex:1 1 auto; width:auto; max-width:100%; min-width:0;">
                <!-- Переключение языка -->
                <div class="header__lang-wrapper" style="width:100%; max-width:70px; min-width:0;">
                    <span class="header__icon">🌐</span>
                    <select v-model="currentLang" @change="setLang(currentLang)" class="header__lang" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;">
                        <option value="ru">RU</option>
                        <option value="uk">UA</option>
                        <option value="en">EN</option>
                    </select>
                </div>

                <!-- Пользователь -->
                <template v-if="user">
                    <div class="header__user-wrapper" style="max-width:100%; min-width:0;">
                        <span class="header__icon">👤</span>
                        <a href="/profile" class="header__user-name">{{ user.name }}</a>
                    </div>
                    <button @click="logout" class="header__logout-btn" style="max-width:100%; box-sizing:border-box;">
                        <span class="header__icon">🚪</span>
                        {{ t("header.logout") }}
                    </button>
                </template>
                <template v-else>
                    <button @click="showAuth = true" class="header__login-btn" style="max-width:100%; box-sizing:border-box;">
                        <span class="header__icon">🔑</span>
                        {{ t("header.login") }}
                    </button>
                </template>

                <AuthModal :isOpen="showAuth" @close="showAuth = false"/>

                <!-- Избранное -->
                <button @click="handleFavoritesClick" class="header__favorites" style="max-width:100%; box-sizing:border-box;">
                    <span class="header__icon">❤️</span>
                    <span>{{ t("header.favorites") }}</span>
                    <span v-if="favoritesCount" class="header__badge">{{ favoritesCount }}</span>
                </button>

                <!-- Заказы -->
                <button @click="handleOrdersClick" class="header__orders" style="max-width:100%; box-sizing:border-box;">
                    <span class="header__icon">📦</span>
                    <span>{{ t("header.orders") }}</span>
                    <span v-if="ordersCount" class="header__badge">{{ ordersCount }}</span>
                </button>

                <button @click="handleSubscriptionsClick" class="header__orders" style="max-width:100%; box-sizing:border-box;">
                    <span class="header__icon">🔁</span>
                    <span>{{ t("header.subscriptions") }}</span>
                    <span v-if="activeSubscriptionsCount" class="header__badge">{{ activeSubscriptionsCount }}</span>
                </button>

                <!-- Корзина -->
                <button @click="handleCartClick" class="header__cart" style="max-width:100%; box-sizing:border-box;">
                    <span class="header__icon">🛒</span>
                    <span>{{ t("header.cart") }}</span>
                    <span v-if="cartCount" class="header__badge">{{ cartCount }}</span>
                </button>
            </div>
        </div>
    </header>
</template>

<script setup>
import {usePage, router} from "@inertiajs/vue3"
import {computed, ref} from "vue"
import {useI18n} from "../lang/useI18n"
import AuthModal from "../components/AuthModal.vue"

const {t, setLang, currentLang} = useI18n()
const page = usePage();
const cartCount = computed(() => {
    const v = page.props.cartCount ?? 0;
    return Number(v) || 0;
});

const favoritesCount = computed(() => {
    const f = page.props.favorites ?? [];
    if (Array.isArray(f)) return f.length;
    if (typeof f === 'object' && f !== null) return f.total ?? (f.length ?? 0);
    return 0;
});

const ordersCount = computed(() => {
    return page.props.pendingOrdersCount ?? 0;
});

const activeSubscriptionsCount = computed(() => {
    return Number(page.props.activeSubscriptionsCount ?? 0) || 0
});

const handleOrdersClick = () => {
    if (!user.value) {
        showAuth.value = true;
    } else {
        router.visit("/orders");
    }
};

const handleSubscriptionsClick = () => {
    if (!user.value) {
        showAuth.value = true
    } else {
        router.visit("/subscriptions")
    }
}

const user = computed(() => page.props.auth?.user)
const showAuth = ref(false)

const logout = () => {
    router.post("/logout", {}, {preserveScroll: true})
}

const handleFavoritesClick = () => {
    if (!user.value) {
        showAuth.value = true
    } else {
        router.visit("/favorites")
    }
}

const handleCartClick = () => {
    if (!user.value) {
        showAuth.value = true
    } else {
        router.visit("/cart")
    }
}

</script>

<style scoped>
.header {
    display: flex;
    flex-direction: column;
    padding: 10px 20px;
    background-color: #fff;
    border-bottom: 1px solid #eaeaea;
    font-family: "Press Start 2P", system-ui;
}

.header__orders {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: #333;
    font-size: 12px;
    padding: 8px 12px;
    border: 2px solid #eaeaea;
    border-radius: 6px;
    transition: all 0.3s ease;
    background: none;
    cursor: pointer;
    font-family: "Press Start 2P", system-ui;
}

.header__orders:hover {
    border-color: #3ecf8e;
    transform: translateY(-2px);
}

.header__top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 10px;
    color: #555;
    margin-bottom: 5px;
}

.header__nav .header__link {
    margin-left: 15px;
    text-decoration: none;
    color: #555;
    font-size: 10px;
}

.header__bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: nowrap;
}

.header__logo-block {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 0 0 auto;
}

.header__logo-img {
    width: 130px;
}

.header__actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
    flex: 1 1 auto;
    min-width: 0;
}

/* Стили для иконок */
.header__icon {
    font-size: 16px;
    margin-right: 5px;
    vertical-align: middle;
}

/* Стили для языка */
.header__lang-wrapper {
    display: flex;
    align-items: center;
    position: relative;
}

.header__lang {
    padding: 8px 12px;
    border: 2px solid #3ecf8e;
    border-radius: 6px;
    background-color: #fff;
    font-family: "Press Start 2P", system-ui;
    font-size: 10px;
    cursor: pointer;
    appearance: none;
    padding-left: 30px;
}

.header__lang-wrapper .header__icon {
    position: absolute;
    left: 10px;
    z-index: 1;
}

/* Стили для пользователя */
.header__user-wrapper {
    display: flex;
    align-items: center;
    gap: 5px;
}

.header__user-name {
    text-decoration: none;
    color: #333;
    font-size: 12px;
}

/* Стили для кнопок */
.header__login-btn,
.header__logout-btn {
    padding: 8px 15px;
    border: 2px solid #3ecf8e;
    border-radius: 6px;
    background-color: #3ecf8e;
    color: white;
    font-family: "Press Start 2P", system-ui;
    font-size: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: all 0.3s ease;
}

.header__login-btn:hover,
.header__logout-btn:hover {
    background-color: #2ebd7d;
    border-color: #2ebd7d;
    transform: translateY(-2px);
}

.header__logout-btn {
    background-color: #ff4757;
    border-color: #ff4757;
}

.header__logout-btn:hover {
    background-color: #ff3742;
    border-color: #ff3742;
}

/* Стили для избранного и корзины */
.header__favorites,
.header__cart {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: #333;
    font-size: 12px;
    padding: 8px 12px;
    border: 2px solid #eaeaea;
    border-radius: 6px;
    transition: all 0.3s ease;
    background: none;
    cursor: pointer;
    font-family: "Press Start 2P", system-ui;
}

.header__favorites:hover,
.header__cart:hover {
    border-color: #3ecf8e;
    transform: translateY(-2px);
}

.header__badge {
    background-color: #3ecf8e;
    color: #fff;
    border-radius: 10%;
    padding: 4px 5px;
    font-size: 10px;
    margin-left: 6px;
    min-width: 18px;
    text-align: center;
}

/* Адаптивность */
@media (max-width: 768px) {
    .header {
        padding: 10px 14px;
    }

    .header__top {
        gap: 10px;
        justify-content: center;
        text-align: center;
    }

    .header__nav {
        width: 100%;
        gap: 8px !important;
        justify-content: center;
    }

    .header__nav .header__link {
        margin-left: 0;
    }

    .header__bottom {
        flex-direction: column;
        align-items: stretch;
        gap: 14px;
    }

    .header__logo-block {
        justify-content: center;
    }

    .header__logo-img {
        width: 112px;
    }

    .header__actions {
        width: 100%;
        justify-content: stretch;
        gap: 10px;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .header__login-btn,
    .header__logout-btn,
    .header__favorites,
    .header__cart,
    .header__orders {
        padding: 8px 10px;
        font-size: 10px;
        width: 100%;
        justify-content: center;
        min-height: 48px;
    }

    .header__user-wrapper {
        display: none;
    }

    .header__lang {
        padding: 8px;
    }

    .header__lang option {
        font-size: 10px;
    }
}

@media (max-width: 480px) {
    .header__actions {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .header__lang-wrapper {
        max-width: none !important;
        width: 100% !important;
    }

    .header__login-btn,
    .header__logout-btn,
    .header__favorites,
    .header__cart,
    .header__orders {
        width: 100%;
        justify-content: center;
    }

    .header__icon {
        margin-right: 0;
    }

    .header__favorites span:not(.header__badge),
    .header__cart span:not(.header__badge),
    .header__orders span:not(.header__badge),
    .header__login-btn span:last-child,
    .header__logout-btn span:last-child {
        display: none;
    }
}
</style>
