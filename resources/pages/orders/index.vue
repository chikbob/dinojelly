<template>
    <MainLayout>
        <div class="orders">
            <h1 class="orders__title">{{ t("orders.title") }}</h1>

            <div class="orders__filter" style="width:100%; max-width:100%; min-width:0;">
                <label for="status" class="orders__filter-label">{{ t("orders.filterByStatus") }}:</label>
                <select
                    id="status"
                    v-model="selectedStatus"
                    @change="filterOrders"
                    class="orders__select"
                    style="width:auto; max-width:none; min-width:0; box-sizing:border-box;"
                >
                    <option value="">{{ t("orders.all") }}</option>
                    <option value="pending">{{ t("orders.pending") }}</option>
                    <option value="completed">{{ t("orders.completed") }}</option>
                    <option value="canceled">{{ t("orders.canceled") }}</option>
                </select>
            </div>

            <p v-if="subscriptionError" class="orders__error">{{ subscriptionError }}</p>

            <div v-if="orders.length" class="orders__list">
                <div
                    v-for="order in orders"
                    :key="order.id"
                    class="orders__card"
                    @click="$inertia.visit(`/orders/${order.id}`)"
                >
                    <div class="orders__header">
                        <h2 class="orders__number">{{ t("order.orderNumber") }} №{{ order.id }}</h2>
                        <span
                            class="orders__status"
                            :class="{
                                'orders__status--success': order.status === 'completed',
                                'orders__status--pending': order.status === 'pending',
                                'orders__status--canceled': order.status === 'canceled',
                            }"
                        >
                            {{ formatStatus(order.status) }}
                        </span>
                    </div>

                    <div class="orders__body">
                        <div class="orders__info">
                            <span class="orders__label">{{ t("orders.amount") }}:</span>
                            <span class="orders__value">{{ order.total_price }} {{ t("currency.symbol") }}</span>
                        </div>
                        <div v-if="order.gift_card_amount" class="orders__info">
                            <span class="orders__label">{{ t("checkout.giftCardDiscount") }}:</span>
                            <span class="orders__value">-{{ order.gift_card_amount }} {{ t("currency.symbol") }}</span>
                        </div>
                        <div v-if="order.referral_credit_amount" class="orders__info">
                            <span class="orders__label">{{ t("checkout.referralCreditDiscount") }}:</span>
                            <span class="orders__value">-{{ order.referral_credit_amount }} {{ t("currency.symbol") }}</span>
                        </div>
                        <div class="orders__info">
                            <span class="orders__label">{{ t("orders.itemsCount") }}:</span>
                            <span class="orders__value">{{ order.total_quantity }}</span>
                        </div>
                        <div class="orders__info">
                            <span class="orders__label">{{ t("orders.payment") }}:</span>
                            <span class="orders__value">
                             {{ order.payment_method === 'card' ? t("orders.card") : t("orders.cash") }}
                            </span>
                        </div>
                        <div v-if="order.latest_payment" class="orders__info">
                            <span class="orders__label">{{ t("payments.statusLabel") }}:</span>
                            <span class="orders__value">{{ t(`payments.status.${order.latest_payment.status}`) }}</span>
                        </div>
                        <div class="orders__info">
                            <span class="orders__label">{{ t("orders.date") }}:</span>
                            <span class="orders__value">{{ formatDate(order.created_at) }}</span>
                        </div>
                        <div v-if="order.delivery_slot" class="orders__info">
                            <span class="orders__label">{{ t("checkout.deliveryTitle") }}:</span>
                            <span class="orders__value">{{ order.delivery_slot.name }}</span>
                        </div>
                        <div class="orders__actions" style="width:100%; max-width:100%; min-width:0;">
                            <button class="orders__action" @click.stop="reorder(order.id)" style="width:100%; max-width:100%; box-sizing:border-box;">
                                {{ t("orders.reorder") }}
                            </button>
                            <button
                                class="orders__action orders__action--secondary"
                                :disabled="order.source_subscription?.status === 'active'"
                                @click.stop="subscribe(order.id)"
                                style="width:100%; max-width:100%; box-sizing:border-box;"
                            >
                                {{ subscriptionLabel(order) }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <p v-else class="orders__empty">{{ t("orders.empty") }}</p>
        </div>
    </MainLayout>
</template>

<script setup>
import {ref} from 'vue'
import {router} from '@inertiajs/vue3'
import MainLayout from "../../layouts/mainLayout.vue";
import {useI18n} from "../../lang/useI18n.js";

const { t, currentLang } = useI18n()

const props = defineProps({
    orders: Array,
    pendingOrdersCount: Number,
    filters: Object
})

const selectedStatus = ref(props.filters?.status || '')
const subscriptionError = ref('')

const formatDate = (date) => {
    if (!date) return t("orders.date_unknown") || "Не указана";

    const localeMap = {
        ru: "ru-RU",
        uk: "uk-UA",
        en: "en-US",
    };

    const current = currentLang?.value || "ru";

    return new Date(date).toLocaleDateString(localeMap[current], {
        year: "numeric",
        month: "long",
        day: "numeric"
    });
};

const formatStatus = (status) => {
    return t(`orders.status.${status}`)
}

// при выборе фильтра
const filterOrders = () => {
    router.get('/orders', {status: selectedStatus.value}, {preserveState: true})
}

const reorder = (orderId) => {
    router.post(`/orders/${orderId}/reorder`, {})
}

const subscribe = (orderId) => {
    subscriptionError.value = ''

    router.post(`/orders/${orderId}/subscriptions`, {
        interval_days: 30,
    }, {
        preserveScroll: true,
        onError: (errors) => {
            subscriptionError.value = errors.subscription ?? 'Не удалось создать подписку для заказа'
        },
        onSuccess: () => {
            router.visit('/subscriptions')
        },
    })
}

const subscriptionLabel = (order) => {
    if (order.source_subscription?.status === 'active') {
        return t('subscriptions.alreadyActive')
    }

    if (['paused', 'canceled'].includes(order.source_subscription?.status)) {
        return t('subscriptions.resume')
    }

    return t('subscriptions.createFromOrder')
}
</script>

<style scoped lang="scss">
.orders {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
    font-family: "Press Start 2P", system-ui;

    &__filter {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-bottom: 30px;
        min-width: 0;
    }

    &__filter-label {
        font-size: 10px;
        text-transform: uppercase;
        color: #555;
    }

    &__select {
        width: fit-content;
        max-width: fit-content;
        min-width: 0;
        box-sizing: border-box;
        padding: 6px 10px;
        font-size: 10px;
        border-radius: 8px;
        border: 1px solid #ddd;
        background: #fff;
        color: #333;
        outline: none;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: "Press Start 2P", system-ui;

        &:hover {
            border-color: #29CC5F;
        }
    }

    &__title {
        font-size: 24px;
        font-weight: 400;
        text-align: center;
        margin-bottom: 40px;
        color: #333;
        position: relative;

        &::after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, #A8E62E, #29CC5F);
            margin: 0.5rem auto 0;
            border-radius: 2px;
        }
    }

    &__error {
        margin: 0 0 20px;
        text-align: center;
        color: #b91c1c;
        font-size: 10px;
        line-height: 1.8;
    }

    &__list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 24px;
    }

    &__card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #eaeaea;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        padding: 20px;
        cursor: pointer;
        transition: all 0.25s ease;

        &:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.15);
            border-color: #3ecf8e;
        }
    }

    &__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        border-bottom: 1px solid #eee;
        padding-bottom: 0.8rem;
    }

    &__number {
        font-size: 14px;
        color: #333;
    }

    &__status {
        font-size: 10px;
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 20px;
        text-transform: uppercase;

        &--success {
            background: #29CC5F;
            color: white;
        }

        &--pending {
            background: #ffc107;
            color: #333;
        }

        &--canceled {
            background: #ff6b6b;
            color: white;
        }
    }

    &__body {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 0.5rem;
    }

    &__info {
        display: flex;
        justify-content: space-between;
        font-size: 10px;
        color: #555;
    }

    &__label {
        font-weight: 600;
        color: #333;
    }

    &__value {
        color: #777;
    }

    &__actions {
        display: flex;
        gap: 10px;
        margin-top: 12px;
    }

    &__action {
        border: none;
        background: #2563eb;
        color: #fff;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 10px;
        font-family: "Press Start 2P", system-ui;
        cursor: pointer;
    }

    &__action--secondary {
        background: #0f172a;
    }

    &__empty {
        text-align: center;
        color: #777;
        font-size: 14px;
        margin-top: 40px;
    }
}

@media (max-width: 600px) {
    .orders {
        &__list {
            grid-template-columns: 1fr;
        }

        &__card {
            padding: 16px;
        }

        &__number {
            font-size: 12px;
        }

        &__status {
            font-size: 9px;
            padding: 4px 10px;
        }
    }
}

@media (max-width: 640px) {
    .orders {
        &__filter,
        &__actions,
        &__header {
            flex-direction: column;
            align-items: stretch;
        }

        &__action,
        &__select {
            width: 100%;
        }
    }
}
</style>
