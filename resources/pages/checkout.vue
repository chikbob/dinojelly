<template>
    <MainLayout>
        <div class="checkout">
            <h1 class="checkout__title">{{ t("cart.checkout") }}</h1>

            <div class="checkout__items">
                <div v-for="item in items" :key="item.id" class="checkout__item">
                    <img :src="item.image_url" class="checkout__img"/>
                    <div class="checkout__info">
                        <h2>{{ item.name }}</h2>
                        <p>{{ item.price }} {{ t("currency.symbol") }} × {{ item.quantity }}</p>
                        <p><b>{{ t("cart.total") }}:</b> {{ item.subtotal }} {{ t("currency.symbol") }}</p>
                    </div>
                </div>
            </div>

            <div class="checkout__summary">
                <p>{{ t("cart.items") }}: {{ totalQuantity }}</p>
                <p>{{ t("cart.finalTotal") }}: <b>{{ totalPrice }} {{ t("currency.symbol") }}</b></p>
            </div>

            <div class="checkout__payment-methods">
                <button
                    class="checkout__btn checkout__btn--card"
                    @click.prevent="submitOrder('card')"
                >
                    {{ t("orders.card") }}
                </button>

                <button
                    class="checkout__btn checkout__btn--cash"
                    @click.prevent="submitOrder('cash')"
                >
                    {{ t("orders.cash") }}
                </button>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import {ref} from "vue";
import {router} from "@inertiajs/vue3";
import MainLayout from "../layouts/mainLayout.vue";
import {useI18n} from "../lang/useI18n.js";

const {t} = useI18n();

const props = defineProps({
    items: Array,
    totalQuantity: Number,
    totalPrice: Number,
    orders: Array
});

// выбраный способ оплаты
const paymentMethod = ref(null);

// отправка заказа
function submitOrder(method) {
    paymentMethod.value = method;

    router.post("/checkout", {
        payment_method: paymentMethod.value,
        items: props.items,
        total_price: props.totalPrice,
    });
}
</script>

<style scoped>
.checkout {
    max-width: 800px;
    margin: auto;
    padding: 24px;
}

.checkout__title {
    text-align: center;
    margin-bottom: 20px;
    font-family: "Press Start 2P", system-ui;
    font-size: 18px;
}

.checkout__items {
    margin-bottom: 20px;
}

.checkout__item {
    display: flex;
    gap: 16px;
    padding: 12px;
    border-bottom: 1px solid #ddd;
}

.checkout__img {
    width: 80px;
    height: 80px;
    object-fit: cover;
}

.checkout__info h2 {
    font-size: 14px;
    margin: 0;
}

.checkout__btn {
    display: flex;
    justify-content: center;
    margin-top: 20px;
    padding: 12px 24px;
    border: none;
    color: #fff;
    cursor: pointer;
    border-radius: 8px;
    font-family: "Press Start 2P", system-ui;
    transition: 0.3s ease;
}

.checkout__payment-methods {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 30px;
}

.checkout__btn--card {
    background: #3b82f6;
}

.checkout__btn--card:hover {
    background: #2563eb;
}

.checkout__btn--cash {
    background: #22c55e;
}

.checkout__btn--cash:hover {
    background: #16a34a;
}

.checkout__btn i {
    margin-right: 8px;
}
</style>
