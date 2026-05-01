<template>
    <MainLayout>
        <div class="subscriptions">
            <div class="subscriptions__header">
                <div>
                    <h1 class="subscriptions__title">{{ t("subscriptions.title") }}</h1>
                    <p class="subscriptions__subtitle">{{ t("subscriptions.subtitle") }}</p>
                </div>
            </div>

            <div v-if="subscriptions.length" class="subscriptions__list">
                <article v-for="subscription in subscriptions" :key="subscription.id" class="subscriptions__card">
                    <div class="subscriptions__card-head">
                        <div>
                            <h2 class="subscriptions__name">{{ getSubscriptionName(subscription) }}</h2>
                            <p class="subscriptions__meta">
                                {{ t(`subscriptions.status.${subscription.status}`) }} ·
                                {{ t("subscriptions.every") }} {{ subscription.interval_days }} {{ t("subscriptions.days") }}
                            </p>
                        </div>
                        <span class="subscriptions__badge" :class="`subscriptions__badge--${subscription.status}`">
                            {{ t(`subscriptions.status.${subscription.status}`) }}
                        </span>
                    </div>

                    <div class="subscriptions__info">
                        <p v-if="subscription.address">{{ subscription.address.full_address }}</p>
                        <p v-if="subscription.delivery_slot">{{ subscription.delivery_slot.name }}</p>
                        <p>{{ subscription.payment_method === 'card' ? t("orders.card") : t("orders.cash") }}</p>
                        <p v-if="subscription.next_run_at">{{ t("subscriptions.nextRun") }}: {{ formatDate(subscription.next_run_at) }}</p>
                    </div>

                    <ul class="subscriptions__items">
                        <li v-for="item in subscription.items" :key="item.id">
                            {{ item.product?.name }} × {{ item.quantity }}
                        </li>
                    </ul>

                    <div class="subscriptions__actions">
                        <button class="subscriptions__action subscriptions__action--primary" @click="runNow(subscription.id)">
                            {{ t("subscriptions.runNow") }}
                        </button>
                        <button
                            v-if="subscription.status !== 'active'"
                            class="subscriptions__action"
                            @click="updateSubscription(subscription, 'active')"
                        >
                            {{ t("subscriptions.resume") }}
                        </button>
                        <button
                            v-if="subscription.status === 'active'"
                            class="subscriptions__action"
                            @click="updateSubscription(subscription, 'paused')"
                        >
                            {{ t("subscriptions.pause") }}
                        </button>
                        <button
                            v-if="subscription.status !== 'canceled'"
                            class="subscriptions__action subscriptions__action--danger"
                            @click="updateSubscription(subscription, 'canceled')"
                        >
                            {{ t("subscriptions.cancel") }}
                        </button>
                    </div>
                </article>
            </div>

            <p v-else class="subscriptions__empty">{{ t("subscriptions.empty") }}</p>
        </div>
    </MainLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import MainLayout from '../../layouts/mainLayout.vue'
import { useI18n } from '../../lang/useI18n'

defineProps({
    subscriptions: {
        type: Array,
        default: () => ([]),
    },
})

const { t, currentLang } = useI18n()

const formatDate = (value) => {
    if (!value) return '—'

    const localeMap = {
        ru: 'ru-RU',
        uk: 'uk-UA',
        en: 'en-US',
    }
    const locale = localeMap[currentLang.value] ?? 'ru-RU'

    return new Date(value).toLocaleDateString(locale, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    })
}

const getSubscriptionName = (subscription) => {
    const name = subscription.name ?? ''
    const orderId = subscription.source_order_id ?? subscription.last_order?.id
    const legacyPattern = /^(subscription-order-|Подписка на заказ #|Підписка на замовлення #|Subscription for order #)(\d+)$/i
    const matchedOrderId = name.match(legacyPattern)?.[2]
    const resolvedOrderId = orderId ?? matchedOrderId

    if (!resolvedOrderId) {
        return name
    }

    if (!name || legacyPattern.test(name)) {
        return t('subscriptions.defaultName').replace(':order', resolvedOrderId)
    }

    return name
}

const updateSubscription = (subscription, status) => {
    router.put(route('subscriptions.update', subscription.id), {
        status,
        interval_days: subscription.interval_days,
        name: subscription.name,
    }, { preserveScroll: true })
}

const runNow = (subscriptionId) => {
    router.post(route('subscriptions.run', subscriptionId), {}, { preserveScroll: true })
}
</script>

<style scoped lang="scss">
.subscriptions {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
    font-family: "Press Start 2P", system-ui;
}

.subscriptions__header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 32px;
}

.subscriptions__title {
    margin: 0 0 12px;
    font-size: 24px;
}

.subscriptions__subtitle {
    margin: 0;
    font-size: 10px;
    color: #64748b;
    line-height: 1.8;
}

.subscriptions__list {
    display: grid;
    gap: 20px;
}

.subscriptions__card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 20px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
}

.subscriptions__card-head {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 18px;
}

.subscriptions__name {
    margin: 0 0 8px;
    font-size: 14px;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

.subscriptions__meta,
.subscriptions__info,
.subscriptions__items,
.subscriptions__empty {
    font-size: 10px;
    color: #475569;
    line-height: 1.8;
}

.subscriptions__badge {
    padding: 8px 10px;
    border-radius: 999px;
    font-size: 9px;
    height: fit-content;
    text-align: center;
    line-height: 1.4;
}

.subscriptions__badge--active {
    background: #dcfce7;
    color: #166534;
}

.subscriptions__badge--paused {
    background: #fef3c7;
    color: #92400e;
}

.subscriptions__badge--canceled {
    background: #fee2e2;
    color: #991b1b;
}

.subscriptions__items {
    margin: 16px 0;
    padding-left: 16px;
}

.subscriptions__actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.subscriptions__action {
    border: 1px solid #cbd5e1;
    background: #fff;
    color: #0f172a;
    border-radius: 10px;
    padding: 10px 12px;
    font-family: inherit;
    font-size: 10px;
    cursor: pointer;
    line-height: 1.5;
    white-space: normal;
}

.subscriptions__action--primary {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}

.subscriptions__action--danger {
    color: #b91c1c;
    border-color: #fecaca;
}

@media (max-width: 720px) {
    .subscriptions__card-head {
        flex-direction: column;
    }

    .subscriptions__badge {
        align-self: flex-start;
    }
}
</style>
