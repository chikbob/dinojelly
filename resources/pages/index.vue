<template>
    <MainLayout>
        <div class="catalog">
            <h1 class="catalog__title">{{ t("catalog.title") }}</h1>

            <div class="catalog__grid">
                <div
                    v-for="product in products.data"
                    :key="product.id"
                    class="catalog__card product-card"
                    @click="goToProduct(product.id)"
                >
                    <img :src="product.image_url" :alt="product.name" class="product-card__image"/>

                    <!-- КНОПКА СЕРДЦЕ -->
                    <button
                        @click.stop="toggleFavorite(product.id)"
                        class="product-card__favorite"
                    >
                        <img
                            :src="favorites.includes(product.id) ? '/images/Favorite.png' : '/images/unFavorite.png'"
                            alt="favorite"
                            class="favorite-icon"
                        />
                    </button>

                    <div class="product-card__content">
                        <div class="product-card__name">{{ product.name }}</div>
                        <div class="product-card__weight">{{ product.weight }} г</div>

                        <div class="product-card__prices">
                            <span class="product-card__price">{{ product.price }}{{ t("currency.symbol") }}</span>
                            <span v-if="product.old_price" class="product-card__old-price">
                                {{ product.old_price }} {{ t("currency.symbol") }}
                            </span>
                        </div>

                        <!-- ✅ Если товар в корзине, показываем счетчик -->
                        <div v-if="cart[product.id]" class="cart-controls">
                            <button
                                @click.stop="decreaseQuantity(product.id)"
                                class="cart-controls__btn"
                            >-
                            </button>
                            <span class="cart-controls__count">{{ cart[product.id].quantity }}</span>
                            <button
                                @click.stop="increaseQuantity(product.id)"
                                class="cart-controls__btn"
                            >+
                            </button>
                        </div>

                        <!-- ✅ Если товара ещё нет в корзине -->
                        <button
                            v-else
                            @click.stop="addToCart(product.id)"
                            class="product-card__button"
                        >
                            {{ t("catalog.addToCart") }}
                        </button>
                    </div>
                </div>
            </div>
            <Pagination class="product-card__pagination" :links="products.links"/>
        </div>
    </MainLayout>
</template>

<script setup>
import {router} from '@inertiajs/vue3'
import MainLayout from '../layouts/mainLayout.vue'
import {route} from 'ziggy-js'
import {useI18n} from "../lang/useI18n"
import {computed, ref} from 'vue'
import Pagination from "../components/pagination.vue";

const {t} = useI18n()

const props = defineProps({
    products: Object, // { data: [...], links: [...] }
    favorites: Array,
    cartItems: {
        type: Object,
        default: () => ({})
    },
    orders: Array
})

// локальная реактивная корзина
const cart = ref({...props.cartItems})

const goToProduct = (id) => {
    router.visit(`/products/${id}`)
}

const addToCart = (productId) => {
    router.post(route('cart.add'), {product_id: productId}, {
        preserveScroll: true,
        preserveState: true,
        only: ['cartItems', 'cartCount'],
        onSuccess: () => {
            if (cart.value[productId]) {
                cart.value[productId].quantity++
            } else {
                cart.value[productId] = {id: productId, quantity: 1}
            }
        }
    })
}

const increaseQuantity = (productId) => {
    router.post(route('cart.increase'), {product_id: productId}, {
        preserveScroll: true,
        preserveState: true,
        only: ['cartItems', 'cartCount'],
        onSuccess: () => {
            if (cart.value[productId]) {
                cart.value[productId].quantity++
            }
        }
    })
}

const decreaseQuantity = (productId) => {
    router.post(route('cart.decrease'), {product_id: productId}, {
        preserveScroll: true,
        preserveState: true,
        only: ['cartItems', 'cartCount'],
        onSuccess: () => {
            if (cart.value[productId]) {
                cart.value[productId].quantity--
                if (cart.value[productId].quantity <= 0) {
                    delete cart.value[productId]
                }
            }
        }
    })
}

const toggleFavorite = (productId) => {
    router.post(route('favorites.toggle'), {product_id: productId}, {
        preserveScroll: true,
        preserveState: true,
    })
}
</script>

<style lang="scss" scoped>
.catalog {
    padding: 40px 20px;
    max-width: 1200px;
    margin: 0 auto;
    font-family: "Press Start 2P", system-ui;

    &__title {
        font-size: 24px;
        font-weight: 400;
        text-align: center;
        margin-bottom: 40px;
        color: #333;
    }

    &__grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 28px;
    }

    &__card {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 2px solid #eaeaea;

        &:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
            border-color: #3ecf8e;
        }
    }
}

.product-card {
    cursor: pointer;
    position: relative;

    &__pagination {
        display: flex;
        justify-content: center;
    }

    &__favorite {
        position: absolute;
        top: 10px;
        right: 10px;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;

        .favorite-icon {
            width: 22px;
            height: 22px;
            transition: transform 0.2s;
        }

        &:hover .favorite-icon {
            transform: scale(1.2);
        }
    }

    &__image {
        width: 100%;
        height: 300px;
        object-fit: cover;
        border-bottom: 2px solid #eaeaea;
    }

    &__content {
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        flex-grow: 1;
    }

    &__name {
        font-size: 12px;
        font-weight: 400;
        color: #333;
        line-height: 1.4;
        min-height: 34px;
    }

    &__weight {
        font-size: 10px;
        color: #777;
    }

    &__prices {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: nowrap;
        white-space: nowrap;
    }

    &__price {
        font-size: 14px;
        font-weight: 400;
        color: #ff6b6b;
        white-space: nowrap;
    }

    &__old-price {
        font-size: 10px;
        text-decoration: line-through;
        color: #aaa;
        white-space: nowrap;
    }

    &__button {
        margin-top: auto;
        padding: 12px;
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
}

/* ✅ Счетчик */
.cart-controls {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-top: auto;

    &__btn {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 30px;
        height: 30px;
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

    &__count {
        font-size: 17px;
        font-weight: 600;
        color: #333;
        min-width: 20px;
        text-align: center;
    }
}
</style>
