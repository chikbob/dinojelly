<template>
    <MainLayout>
        <div class="order">
            <div class="order__container">
                <h1 class="order__title">{{ t("order.orderNumber") }} №{{ order.id }}</h1>

                <div class="order__summary">
                    <div v-if="order.address" class="order__summary-item">
                        <span class="order__label">{{ t("profile.address") }}:</span>
                        <span class="order__value">{{ order.address.full_address }}</span>
                    </div>
                    <div v-if="order.delivery_slot" class="order__summary-item">
                        <span class="order__label">{{ t("checkout.deliveryTitle") }}:</span>
                        <span class="order__value">{{ order.delivery_slot.name }}</span>
                    </div>
                    <div class="order__summary-item">
                        <span class="order__label">{{ t("order.statusText") }}:</span>
                        <span
                            class="order__status"
                            :class="{
                                'order__status--success': order.status === 'completed',
                                'order__status--pending': order.status === 'pending',
                                'order__status--canceled': order.status === 'canceled',
                            }"
                        >
                            {{ formatStatus(order.status) }}
                        </span>
                    </div>
                    <div class="order__summary-item">
                        <span class="order__label">{{ t("order.payment") }}:</span>
                        <span class="order__value">
                            {{ order.payment_method === 'card' ? t("order.card") : t("order.cash") }}
                        </span>
                    </div>
                    <div v-if="order.latest_payment" class="order__summary-item">
                        <span class="order__label">{{ t("payments.statusLabel") }}:</span>
                        <span class="order__payment-status" :class="`order__payment-status--${order.latest_payment.status}`">
                            {{ t(`payments.status.${order.latest_payment.status}`) }}
                        </span>
                    </div>
                    <div class="order__summary-item">
                        <span class="order__label">{{ t("order.amount") }}:</span>
                        <span class="order__value">{{ order.total_price }} {{ t("currency.symbol") }}</span>
                    </div>
                    <div class="order__summary-item">
                        <span class="order__label">{{ t("checkout.deliveryPrice") }}:</span>
                        <span class="order__value">{{ order.delivery_price }} {{ t("currency.symbol") }}</span>
                    </div>
                    <div class="order__summary-item">
                        <span class="order__label">{{ t("order.quantity") }}:</span>
                        <span class="order__value">{{ order.total_quantity }}</span>
                    </div>
                </div>

                <!-- Кнопка отмены заказа -->
                <div v-if="order.status === 'pending'" class="order__actions">
                    <button @click="cancelOrder" class="order__cancel-btn">
                        {{ isCanceling ? t("order.canceling") : t("order.cancel") }}
                    </button>
                    <button
                        v-if="canRetryPayment"
                        @click="retryPayment"
                        class="order__retry-btn"
                    >
                        {{ retryLabel }}
                    </button>
                </div>

                <div class="order__items">
                    <h2 class="order__subtitle">{{ t("order.items") }}</h2>
                    <div
                        v-for="item in order.items"
                        :key="item.id"
                        class="order__item"
                    >
                        <img
                            v-if="item.product?.image_url"
                            :src="item.product.image_url"
                            alt="product"
                            class="order__img"
                        />
                        <div class="order__details">
                            <p class="order__product-name">{{ item.product.name }}</p>
                            <p class="order__quantity">× {{ item.quantity }}</p>
                            <p class="order__price">{{ item.price }} {{ t("currency.symbol") }}</p>
                        </div>
                    </div>
                </div>

                <button class="order__back" @click="goBack">
                    {{ t("order.back") }}
                </button>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from "../../layouts/mainLayout.vue";
import {computed, ref} from 'vue';
import {router} from '@inertiajs/vue3';
import {useI18n} from "../../lang/useI18n.js";

const {t} = useI18n()

const props = defineProps({
    order: Object
});

const isCanceling = ref(false);
const isRetrying = ref(false);

const canRetryPayment = computed(() => {
    if (props.order.payment_method !== 'card' || !props.order.latest_payment) {
        return false
    }

    return ['pending', 'failed', 'canceled'].includes(props.order.latest_payment.status)
})

const retryLabel = computed(() => {
    if (props.order.latest_payment?.status === 'pending') {
        return t("payments.continuePayment")
    }

    return isRetrying.value ? t("payments.redirecting") : t("payments.retryPayment")
})

const formatStatus = (status) => {
    return t(`order.status.${status}`);
};

const goBack = () => {
    router.visit('/orders');
};

const cancelOrder = async () => {
    if (!confirm(t("order.confirmCancel"))) {
        return;
    }

    isCanceling.value = true;

    try {
        await router.post(`/orders/${props.order.id}/cancel`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                // Обновляем данные после успешной отмены
                router.reload({only: ['order']});
            },
            onError: (errors) => {
                console.error('Errors:', errors);
                alert('Ошибка при отмене заказа: ' + (errors.error || 'Неизвестная ошибка'));
            },
            onFinish: () => {
                isCanceling.value = false;
            }
        });
    } catch (error) {
        isCanceling.value = false;
        console.error('Cancel order error:', error);
        alert('Ошибка при отмене заказа');
    }
};

const retryPayment = () => {
    isRetrying.value = true;

    router.post(`/orders/${props.order.id}/payments/retry`, {}, {
        preserveScroll: true,
        onFinish: () => {
            isRetrying.value = false;
        }
    });
};
</script>

<style scoped lang="scss">
.order {
    background: #f8fafc;
    min-height: 100dvh;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 40px 20px;
    color: #1e293b;
    font-family: "Press Start 2P", system-ui;

    &__container {
        background: #ffffff;
        border-radius: 16px;
        padding: 30px;
        max-width: 800px;
        width: 100%;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        display: flex;
        flex-direction: column;
    }

    &__title {
        font-size: 18px;
        text-align: center;
        color: #1e293b;
        margin-bottom: 24px;
    }

    &__summary {
        background: #f9fafb;
        border-radius: 12px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 30px;
        border: 1px solid #e5e7eb;
    }

    &__summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 10px;
        text-transform: uppercase;
    }

    &__label {
        color: #6b7280;
    }

    &__value {
        color: #111827;
    }

    &__status {
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 9px;
        text-transform: uppercase;

        &--success {
            background: #22c55e;
            color: #fff;
        }

        &--pending {
            background: #fde047;
            color: #111;
        }

        &--canceled {
            background: #ef4444;
            color: #fff;
        }
    }

    /* Стили для кнопки отмены */
    &__actions {
        margin-bottom: 30px;
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    &__cancel-btn {
        padding: 12px 24px;
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 10px;
        font-family: inherit;
        text-transform: uppercase;
        transition: all 0.3s ease;

        &:hover:not(:disabled) {
            background: #dc2626;
            transform: translateY(-2px);
        }

        &:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }
    }

    &__retry-btn {
        padding: 12px 24px;
        background: #2563eb;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 10px;
        font-family: inherit;
        text-transform: uppercase;
        transition: all 0.3s ease;

        &:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }
    }

    &__payment-status {
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 9px;
        text-transform: uppercase;

        &--pending {
            background: #dbeafe;
            color: #1d4ed8;
        }

        &--paid {
            background: #dcfce7;
            color: #166534;
        }

        &--failed {
            background: #fef3c7;
            color: #92400e;
        }

        &--canceled {
            background: #fee2e2;
            color: #b91c1c;
        }
    }

    &__subtitle {
        font-size: 12px;
        color: #1e293b;
        margin-bottom: 16px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    &__items {
        background: #f9fafb;
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #e5e7eb;
    }

    &__item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #e5e7eb;
        padding: 12px 0;

        &:last-child {
            border-bottom: none;
        }
    }

    &__img {
        width: 64px;
        height: 64px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }

    &__details {
        flex: 1;
        margin-left: 16px;
        display: grid;
        grid-template-columns: 1fr 60px 120px;
        align-items: center;
        gap: 12px;
    }

    &__product-name {
        font-size: 10px;
        color: #111827;
        word-break: break-word;
    }

    &__quantity {
        font-size: 10px;
        color: #6b7280;
        text-align: center;
        width: 60px;
        flex-shrink: 0;
    }

    &__price {
        font-size: 10px;
        color: #29CC5F;
        text-align: right;
        width: 120px;
        flex-shrink: 0;
    }

    &__back {
        display: block;
        width: 100%;
        margin-top: 30px;
        padding: 14px;
        background: #29CC5F;
        color: white;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-size: 10px;
        font-family: inherit;
        text-transform: uppercase;
        transition: all 0.3s ease;

        &:hover {
            background: #1e9b47;
            transform: translateY(-2px);
        }
    }

    @media (max-width: 600px) {
        &__container {
            padding: 20px;
        }

        &__summary-item {
            font-size: 9px;
        }

        &__item {
            flex-direction: column;
            align-items: flex-start;
        }

        &__details {
            margin-left: 0;
            flex-direction: column;
            align-items: flex-start;
        }
    }
}
</style>
