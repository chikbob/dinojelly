<template>
    <MainLayout>
        <div class="product-page">
            <div class="product">
                <div class="product__gallery">
                    <img :src="product.image_url" :alt="product.name" class="product__image"/>
                </div>

                <div class="product__info">
                    <h1 class="product__title">{{ product.name }}</h1>
                    <p class="product__meta">{{ product.weight }} г</p>

                    <div class="product__prices">
                        <span class="product__price">{{ product.price }} {{ t("currency.symbol") }}</span>
                        <span v-if="product.old_price" class="product__old-price">
                            {{ product.old_price }} {{ t("currency.symbol") }}
                        </span>
                    </div>

                    <p class="product__description">{{ product.description }}</p>

                    <!-- Блок с кнопками -->
                    <div class="product__actions">
                        <!-- Избранное -->
                        <button class="product__favorite" @click="toggleFavorite">
                            <img
                                :src="product.is_favorite ? '/images/Favorite.png' : '/images/unFavorite.png'"
                                alt="favorite"
                                width="24"
                                height="24"
                            />
                        </button>

                        <!-- Корзина -->
                        <div v-if="cartItems[product.id]" class="cart-counter">
                            <button class="counter-btn" @click="updateQuantity(-1)">-</button>
                            <span class="counter-value">{{ cartItems[product.id].quantity }}</span>
                            <button class="counter-btn" @click="updateQuantity(1)">+</button>
                        </div>
                        <button v-else class="product__add-to-cart" @click="addToCart">
                            {{ t("product.addToCart") }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import {router} from '@inertiajs/vue3'
import MainLayout from '../layouts/mainLayout.vue'
import {route} from "ziggy-js"
import {useI18n} from "../lang/useI18n"
import {computed} from "vue"

const {t} = useI18n()

const props = defineProps({
    product: Object,
    favorites: Array,
    cartItems: Object,
    orders: Array
})

// Добавление в корзину
function addToCart() {
    router.post(route('cart.add'), {
        product_id: props.product.id
    }, {
        preserveScroll: true,
        only: ['cartItems', 'cartCount'],
    })
}

function updateQuantity(change) {
    if (change > 0) {
        router.post(route('cart.increase'), {
            product_id: props.product.id
        }, {
            preserveScroll: true,
            only: ['cartItems', 'cartCount'],
        })
    } else {
        router.post(route('cart.decrease'), {
            product_id: props.product.id
        }, {
            preserveScroll: true,
            only: ['cartItems', 'cartCount'],
        })
    }
}


// Переключение избранного
function toggleFavorite() {
    router.post(route('favorites.toggle'), {
        product_id: props.product.id
    }, {
        preserveScroll: true,
        only: ['favorites', 'product'],
    })
}
</script>

<style lang="scss">
.product__actions {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-top: 20px;
}

.product__favorite {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;

    .favorite-icon {
        width: 28px;
        height: 28px;
        transition: transform 0.2s;
        color: #333;
    }

    .favorite-icon.active {
        fill: red;
    }

    &:hover .favorite-icon {
        transform: scale(1.2);
    }
}

.product__add-to-cart {
    padding: 12px 16px;
    background-color: #3ecf8e;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: "Press Start 2P", system-ui;
    font-size: 10px;
    letter-spacing: 0.5px;

    &:hover {
        background-color: #2ebd7d;
        transform: translateY(-2px);
    }

    &:active {
        transform: translateY(0);
    }
}

.cart-counter {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;

    .counter-btn {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: none;
        background-color: #3ecf8e;
        color: white;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.2s ease;

        &:hover {
            background-color: #2ebd7d;
            transform: scale(1.05);
        }
    }

    .counter-value {
        font-size: 14px;
        font-weight: 600;
        color: #333;
        min-width: 20px;
        text-align: center;
    }
}
</style>
