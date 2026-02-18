<template>
    <MainLayout>
        <div class="favorites">
            <h1 class="favorites__title">{{ t("favorites.title") }}</h1>

            <div class="favorites__controls">
                <label>
                    <select v-model="order" @change="changeOrder">
                        <option value="created_at_desc">{{ t("favorites.newFirst") }}</option>
                        <option value="created_at_asc">{{ t("favorites.oldFirst") }}</option>
                        <option value="price_asc">{{ t("favorites.cheapFirst") }}</option>
                        <option value="price_desc">{{ t("favorites.expensiveFirst") }}</option>
                    </select>
                </label>
            </div>

            <div v-if="favorites.data.length" class="favorites__grid">
                <div
                    v-for="product in favorites.data"
                    :key="product.id"
                    class="favorites__card product-card"
                    @click="goToProduct(product.id)"
                >
                    <img :src="product.image_url" :alt="product.name" class="product-card__image"/>

                    <!-- КНОПКА УДАЛЕНИЯ ИЗ ИЗБРАННОГО -->
                    <button
                        @click.stop="removeFavorite(product.id)"
                        class="product-card__favorite"
                    >
                        <img src="/images/Favorite.png" alt="remove" class="favorite-icon"/>
                    </button>

                    <div class="product-card__content">
                        <div class="product-card__name">{{ product.name }}</div>
                        <div class="product-card__weight">{{ product.weight }} г</div>

                        <div class="product-card__prices">
                            <span class="product-card__price">{{ product.price }}{{ t("currency.symbol") }}</span>
                            <span v-if="product.old_price" class="product-card__old-price">
                                {{ product.old_price }}{{ t("currency.symbol") }}
                            </span>
                        </div>

                        <!-- ЕСЛИ ТОВАРА НЕТ В КОРЗИНЕ -->
                        <button
                            v-if="!cartItems[product.id]"
                            @click.stop="addToCart(product.id)"
                            class="product-card__button"
                        >
                            {{ t("catalog.addToCart") }}
                        </button>

                        <!-- ЕСЛИ ТОВАР УЖЕ В КОРЗИНЕ -->
                        <div v-else class="cart-counter">
                            <button @click.stop="decreaseQty(product.id)" class="counter-btn">-</button>
                            <span class="counter-value">{{ cartItems[product.id].quantity }}</span>
                            <button @click.stop="increaseQty(product.id)" class="counter-btn">+</button>
                        </div>
                    </div>
                </div>
            </div>

            <p v-else class="favorites__empty">{{ t("favorites.empty") }}</p>

            <Pagination class="favorites__pagination" :links="favorites.links"/>
        </div>
    </MainLayout>
</template>

<script setup>
import {router} from "@inertiajs/vue3"
import {useI18n} from "../lang/useI18n"
import MainLayout from "../layouts/mainLayout.vue"
import {route} from "ziggy-js"
import Pagination from "../components/pagination.vue";
import {ref, watch} from "vue";

const {t} = useI18n()

const props = defineProps({
    favorites: Object,
    cartItems: {
        type: Object,
        default: () => ({}),
    },
    cartCount: Number,
    order: String,
    orders: Array
})

const order = ref(props.order || 'created_at_desc');

watch(() => props.order, (newVal) => {
    if (newVal) order.value = newVal;
})

function changeOrder() {
    router.get(route('favorites.index', {order: order.value}), {}, {preserveScroll: true});
}

const goToProduct = (id) => {
    router.visit(`/products/${id}`)
}

const removeFavorite = (id) => {
    router.post(route("favorites.toggle"), {product_id: id}, {
        preserveScroll: true,
        preserveState: true,
        only: ["favorites", "cartItems", "cartCount"],
    })
}

const addToCart = (productId) => {
    router.post(route("cart.add"), {product_id: productId}, {
        preserveScroll: true,
        preserveState: true,
        only: ["cartItems", "cartCount"],
    })
}

const increaseQty = (productId) => {
    router.post(route("cart.increase"), {product_id: productId}, {
        preserveScroll: true,
        preserveState: true,
        only: ["cartItems", "cartCount"],
    })
}

const decreaseQty = (productId) => {
    router.post(route("cart.decrease"), {product_id: productId}, {
        preserveScroll: true,
        preserveState: true,
        only: ["cartItems", "cartCount"],
    })
}
</script>

<style scoped lang="scss">
.favorites {
    font-family: "Press Start 2P", system-ui;
    padding: 40px 20px;
    max-width: 1200px;
    margin: 0 auto;

    &__pagination {
        display: flex;
        justify-content: center;
    }

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

    &__empty {
        text-align: center;
        margin-top: 50px;
        color: #888;
        font-size: 14px;
    }

    &__controls {
        margin-bottom: 20px;
        display: flex;
        justify-content: flex-end;
        font-size: 12px;

        select {
            margin-left: 8px;
            padding: 8px 12px;
            font-size: 12px;
            font-family: "Press Start 2P", system-ui;
            border: 2px solid #3ecf8e;
            border-radius: 8px;
            background: #fff;
            color: #333;
            cursor: pointer;
            transition: all 0.2s ease;

            &:hover {
                border-color: #2ebd7d;
                background: #f9f9f9;
            }

            &:focus {
                outline: none;
                border-color: #2ebd7d;
                box-shadow: 0 0 4px rgba(46, 189, 125, 0.6);
            }
        }
    }

    .product-card {
        cursor: pointer;
        position: relative;
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
        }

        &__old-price {
            font-size: 10px;
            text-decoration: line-through;
            color: #aaa;
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

    /* Красивый блок с +/- */
    .cart-counter {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-top: auto;

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
            font-size: 17px;
            font-weight: 600;
            color: #333;
            min-width: 20px;
            text-align: center;
        }
    }
}
</style>
