<template>
    <MainLayout>
        <div class="favorites">
            <h1 class="favorites__title">{{ t("favorites.title") }}</h1>

            <div class="favorites__controls">
                <label style="width:100%; max-width:320px; min-width:0;">
                    <select v-model="order" @change="changeOrder" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;">
                        <option value="created_at_desc">{{ t("favorites.newFirst") }}</option>
                        <option value="created_at_asc">{{ t("favorites.oldFirst") }}</option>
                        <option value="price_asc">{{ t("favorites.cheapFirst") }}</option>
                        <option value="price_desc">{{ t("favorites.expensiveFirst") }}</option>
                    </select>
                </label>
            </div>

            <div v-if="favorites.data.length" class="favorites__grid">
                <StoreProductCard
                    v-for="product in favorites.data"
                    :key="product.id"
                    :product="product"
                    :favorite="true"
                    :cart-item="cartItems[product.id] ?? null"
                    :add-to-cart-label="t('catalog.addToCart')"
                    :out-of-stock-label="t('catalog.outOfStock')"
                    :currency-symbol="t('currency.symbol')"
                    @open="goToProduct"
                    @favorite-toggle="removeFavorite"
                    @add-to-cart="addToCart"
                    @increase="increaseQty"
                    @decrease="decreaseQty"
                />
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
import StoreProductCard from "../components/StoreProductCard.vue";

const {t} = useI18n()

const props = defineProps({
    favorites: Object,
    cartItems: {
        type: Object,
        default: () => ({}),
    },
    cartCount: Number,
    order: String
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
        min-width: 0;

        label {
            width: min(100%, 320px);
            max-width: 100%;
            min-width: 0;
        }

        select {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            box-sizing: border-box;
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

    :deep(.product-card) {
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

        &.product-card--out-of-stock {
            opacity: 0.75;
            border-color: #d1d5db;
            background: linear-gradient(180deg, #f8fafc 0%, #f3f4f6 100%);

            &:hover {
                transform: none;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                border-color: #d1d5db;
            }
        }

        .product-card__favorite {
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

        .product-card__image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-bottom: 2px solid #eaeaea;
        }

        .product-card__content {
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            flex-grow: 1;
        }

        .product-card__name {
            font-size: 12px;
            font-weight: 400;
            color: #333;
            line-height: 1.4;
            min-height: 34px;
        }

        .product-card__weight {
            font-size: 10px;
            color: #777;
        }

        .product-card__stock {
            font-size: 9px;
            line-height: 1.4;
            text-transform: uppercase;
        }

        .product-card__stock--out {
            color: #6b7280;
        }

        .product-card__prices {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .product-card__price {
            font-size: 14px;
            font-weight: 400;
            color: #ff6b6b;
        }

        .product-card__old-price {
            font-size: 10px;
            text-decoration: line-through;
            color: #aaa;
        }

        .product-card__button {
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

        .product-card__button--disabled,
        .product-card__button:disabled {
            background-color: #9ca3af;
            cursor: not-allowed;
        }
    }

    :deep(.product-card--out-of-stock .product-card__image) {
        filter: grayscale(1);
    }

    /* Красивый блок с +/- */
    :deep(.cart-counter) {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-top: auto;

        .cart-controls__btn {
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

            &:disabled {
                background-color: #9ca3af;
                cursor: not-allowed;
                transform: none;
            }
        }

        .cart-controls__count {
            font-size: 17px;
            font-weight: 600;
            color: #333;
            min-width: 20px;
            text-align: center;
        }
    }

    :deep(.cart-counter--out .counter-value) {
        color: #6b7280;
    }
}

@media (max-width: 640px) {
    .favorites {
        &__controls {
            justify-content: stretch;
        }
    }
}
</style>
