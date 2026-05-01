<template>
    <MainLayout>
        <div class="cart">
            <h1 class="cart__title">
                {{ t("cart.title") }}
                <sup>{{ totalQuantity }}</sup>
            </h1>

            <div v-if="recovered" class="cart__recovered">
                {{ t("cart.recovered") }}
            </div>

            <div
                v-if="cartItems.length"
                class="cart__layout"
                style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(100%, 320px), 1fr)); gap:24px; width:100%; max-width:100%; min-width:0;"
            >
                <!-- Левая колонка -->
                <div class="cart__items" style="width:100%; max-width:100%; min-width:0;">
                    <div v-for="item in cartItems" :key="item.id" class="cart__item" style="width:100%; max-width:100%; min-width:0; overflow:hidden; box-sizing:border-box;">
                        <!-- ✅ картинка и название ведут на страницу товара -->
                        <img
                            :src="item.image_url"
                            alt=""
                            class="cart__item-image"
                            :class="{ 'cart__item-image--out': !item.is_in_stock }"
                            @click="goToProduct(item.id)"
                        />

                        <div class="cart__item-info" :class="{ 'cart__item-info--out': !item.is_in_stock }" style="width:100%; max-width:100%; min-width:0;">
                            <h2
                                class="cart__item-name"
                                @click="goToProduct(item.id)"
                            >
                                {{ item.name }}
                            </h2>
                            <p v-if="!item.is_in_stock" class="cart__item-stock">
                                {{ t("catalog.outOfStock") }}
                            </p>
                            <div class="cart__item-prices">
                                <span class="cart__item-price">{{ item.price }} {{ t("currency.symbol") }}</span>
                                <span v-if="item.old_price" class="cart__item-old">{{
                                        item.old_price
                                    }} {{ t("currency.symbol") }}</span>
                            </div>
                            <div class="cart__item-actions" style="width:100%; max-width:100%; min-width:0; flex-wrap:wrap;">
                                <!-- ✅ toggle избранное -->
                                <button class="cart__icon" @click="toggleFavorite(item.id)">
                                    <img
                                        :src="favorites.includes(item.id) ? '/images/Favorite.png' : '/images/unFavorite.png'"
                                        alt="favorite" width="20"
                                    />
                                </button>
                                <button @click="removeFromCart(item.id)" class="cart__delete" style="max-width:100%; box-sizing:border-box;">
                                    {{ t("cart.delete") }}
                                </button>
                                <div class="cart__quantity" :class="{ 'cart__quantity--out': !item.is_in_stock }" style="max-width:100%; min-width:0;">
                                    <button @click="decreaseQuantity(item.id)" class="cart__qty-btn">−</button>
                                    <span>{{ item.quantity }}</span>
                                    <button @click="increaseQuantity(item.id)" class="cart__qty-btn" :disabled="!item.is_in_stock">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Правая колонка -->
                <div class="cart__summary" style="width:100%; max-width:100%; min-width:0; overflow:hidden; box-sizing:border-box;">
                    <button class="cart__checkout" :class="{ 'cart__checkout--disabled': hasOutOfStockItems }" :disabled="hasOutOfStockItems" @click="goToCheckout" style="width:100%; max-width:100%; box-sizing:border-box;">{{ t("cart.checkout") }}</button>
                    <p class="cart__hint">
                        {{ hasOutOfStockItems ? t("catalog.outOfStock") : t("cart.deliveryHint") }}
                    </p>
                    <div class="cart__summary-info">
                        <div class="cart__summary-row">
                            <span>{{ t("cart.yourCart") }}</span>
                            <span>{{ totalQuantity }} {{ t("cart.itemsShort") }}</span>
                        </div>
                        <div class="cart__summary-row">
                            <span>{{ t("cart.items") }} ({{ totalQuantity }})</span>
                            <span>{{ totalPrice }} {{ t("currency.symbol") }}</span>
                        </div>
                        <div class="cart__summary-row cart__discount" v-if="discountTotal > 0">
                            <span>{{ t("cart.discount") }}</span>
                            <span>-{{ discountTotal }} {{ t("currency.symbol") }}</span>
                        </div>
                        <div class="cart__summary-row cart__final">
                            <span>{{ t("cart.finalTotal") }}</span>
                            <span>{{ finalTotal }} {{ t("currency.symbol") }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="cart__empty">
                <p>{{ t("cart.empty") }}</p>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import {router} from "@inertiajs/vue3"
import {computed} from "vue"
import {useI18n} from "../lang/useI18n"
import MainLayout from "../layouts/mainLayout.vue"
import {route} from "ziggy-js";

const {t} = useI18n()

const props = defineProps({
    cart: Object,
    favorites: Array,
    recovered: Boolean,
})

const cartItems = computed(() => props.cart ? Object.values(props.cart) : [])
const hasOutOfStockItems = computed(() => cartItems.value.some((item) => !item.is_in_stock))
const finalTotal = computed(() => totalPrice.value - discountTotal.value)

const totalQuantity = computed(() =>
    cartItems.value.reduce((sum, item) => sum + item.quantity, 0)
)

const totalPrice = computed(() =>
    cartItems.value.reduce((sum, item) => sum + (item.old_price ?? item.price) * item.quantity, 0)
)

const discountTotal = computed(() =>
    cartItems.value.reduce((sum, item) => {
        if (item.old_price) {
            return sum + (item.old_price - item.price) * item.quantity
        }
        return sum
    }, 0)
)

// ✅ переход к товару
function goToProduct(id) {
    router.get(`/products/${id}`)
}

// ✅ toggle избраннее
function toggleFavorite(productId) {
    router.post("/favorites/toggle", {product_id: productId}, {
        preserveScroll: true,
        preserveState: true
    })
}

function removeFromCart(id) {
    router.post("/cart/remove", {id}, {preserveScroll: true, preserveState: true})
}

function increaseQuantity(productId) {
    router.post("/cart/increase", {product_id: productId}, {preserveScroll: true, preserveState: true})
}

function decreaseQuantity(productId) {
    router.post("/cart/decrease", {product_id: productId}, {preserveScroll: true, preserveState: true})
}

function goToCheckout() {
    router.get(route('checkout.create'));
}
</script>


<style lang="scss" scoped>
.cart {
    padding: 32px;

    &__recovered {
        margin-bottom: 20px;
        padding: 16px 18px;
        border-radius: 14px;
        background: #dbeafe;
        color: #1d4ed8;
        font-weight: 700;
    }

    &__title {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 24px;
    }

    &__layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
    }

    &__items {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    &__item {
        display: flex;
        gap: 16px;
        background: #fff;
        padding: 16px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    &__item-image {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
    }

    &__item-image--out {
        filter: grayscale(1);
        opacity: 0.78;
    }

    &__item-name {
        font-size: 18px;
        font-weight: 600;
        cursor: pointer;
    }


    &__item-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    &__item-info--out {
        opacity: 0.88;
    }

    &__item-stock {
        margin: 8px 0 0;
        color: #6b7280;
        font-size: 12px;
        text-transform: uppercase;
    }

    &__item-prices {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    &__item-price {
        font-size: 20px;
        font-weight: bold;
        color: #16a34a;
    }

    &__item-old {
        font-size: 16px;
        color: #999;
        text-decoration: line-through;
    }

    &__item-actions {
        display: flex;
        margin: 14px 0 0 0;
        gap: 12px;
        align-items: center;
    }

    &__icon {
        background: none;
        border: none;
        cursor: pointer;
    }

    &__delete {
        font-family: "Press Start 2P", system-ui;
        background: none;
        border: none;
        cursor: pointer;
        color: red;
        font-size: 14px;
    }

    &__quantity {
        display: flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 4px 8px;
    }

    &__qty-btn {
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;

        &:disabled {
            color: #9ca3af;
            cursor: not-allowed;
        }
    }

    /* Правая колонка */
    &__summary {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        gap: 16px;
        align-self: start; /* чтобы блок не растягивался */
    }

    &__checkout {
        font-family: "Press Start 2P", system-ui;
        background: #29CC5F;
        color: #FAFAFA;
        padding: 20px 12px;
        border-radius: 8px;
        font-size: 18px;
        font-weight: bold;
        cursor: pointer;
        border: 0;
    }

    &__checkout--disabled {
        background: #9ca3af;
        cursor: not-allowed;
    }

    &__hint {
        text-justify: auto;
        font-size: 12px;
        color: #666;
    }

    &__summary-info {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    &__summary-row {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
    }

    &__discount {
        color: red;
    }

    &__final {
        font-weight: bold;
        font-size: 18px;
        color: #16a34a;
    }

    &__empty {
        text-align: center;
        font-size: 20px;
        color: #777;
        margin-top: 100px;
    }
}
</style>
