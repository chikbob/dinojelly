<template>
    <div class="orders-show">
        <div class="orders-show__hero">
            <div>
                <p class="orders-show__eyebrow">{{ t("admin.orders.crmTitle") }}</p>
                <h1 class="orders-show__title">{{ t("admin.orders.orderNumber") }} #{{ order.id }}</h1>
            </div>

            <div class="orders-show__hero-actions">
                <a :href="route('admin.users.show', order.customer.id)" class="orders-show__link">
                    {{ t("admin.orders.openCustomer") }}
                </a>
            </div>
        </div>

        <div class="orders-show__grid">
            <section class="orders-show__panel">
                <h2>{{ t("admin.orders.summary") }}</h2>

                <div class="orders-show__rows">
                    <div class="orders-show__row">
                        <span>{{ t("admin.orders.customer") }}</span>
                        <strong>{{ order.customer.name }}</strong>
                    </div>
                    <div class="orders-show__row">
                        <span>Email</span>
                        <strong>{{ order.customer.email }}</strong>
                    </div>
                    <div class="orders-show__row">
                        <span>{{ t("profile.phone") }}</span>
                        <strong>{{ order.customer.phone || t("profile.notProvided") }}</strong>
                    </div>
                    <div class="orders-show__row">
                        <span>{{ t("admin.orders.status") }}</span>
                        <select v-model="status" class="orders-show__select" @change="updateStatus">
                            <option value="pending">{{ t("admin.orders.statuses.pending") }}</option>
                            <option value="completed">{{ t("admin.orders.statuses.completed") }}</option>
                            <option value="canceled">{{ t("admin.orders.statuses.canceled") }}</option>
                        </select>
                    </div>
                    <div class="orders-show__row">
                        <span>{{ t("admin.orders.paymentStatus") }}</span>
                        <strong v-if="order.latest_payment">
                            {{ t(`payments.status.${order.latest_payment.status}`) }}
                        </strong>
                        <strong v-else>—</strong>
                    </div>
                    <div class="orders-show__row">
                        <span>{{ t("admin.orders.totalPrice") }}</span>
                        <strong>{{ order.total_price }} {{ t("currency.symbol") }}</strong>
                    </div>
                    <div class="orders-show__row">
                        <span>{{ t("checkout.deliveryPrice") }}</span>
                        <strong>{{ order.delivery_price }} {{ t("currency.symbol") }}</strong>
                    </div>
                    <div class="orders-show__row">
                        <span>{{ t("admin.orders.discountAmount") }}</span>
                        <strong>{{ order.discount_amount }} {{ t("currency.symbol") }}</strong>
                    </div>
                    <div class="orders-show__row">
                        <span>{{ t("admin.orders.createdAt") }}</span>
                        <strong>{{ formatDateTime(order.created_at) }}</strong>
                    </div>
                </div>
            </section>

            <section class="orders-show__panel">
                <h2>{{ t("admin.orders.deliveryBlock") }}</h2>

                <div class="orders-show__rows">
                    <div class="orders-show__row">
                        <span>{{ t("profile.address") }}</span>
                        <strong>{{ order.address?.full_address || t("profile.notProvided") }}</strong>
                    </div>
                    <div class="orders-show__row">
                        <span>{{ t("checkout.deliveryTitle") }}</span>
                        <strong>{{ order.delivery_slot?.name || t("profile.notProvided") }}</strong>
                    </div>
                    <div class="orders-show__row">
                        <span>{{ t("order.payment") }}</span>
                        <strong>{{ order.payment_method === 'card' ? t("order.card") : t("order.cash") }}</strong>
                    </div>
                </div>
            </section>
        </div>

        <div class="orders-show__grid orders-show__grid--bottom">
            <section class="orders-show__panel">
                <h2>{{ t("admin.orders.itemsTitle") }}</h2>

                <div class="orders-show__items">
                    <article v-for="item in order.items" :key="item.id" class="orders-show__item">
                        <img
                            v-if="item.product?.image_url"
                            :src="item.product.image_url"
                            :alt="item.product?.name"
                            class="orders-show__item-image"
                        />
                        <div class="orders-show__item-body">
                            <strong>{{ item.product?.name }}</strong>
                            <span>{{ item.quantity }} × {{ item.price }} {{ t("currency.symbol") }}</span>
                        </div>
                    </article>
                </div>

                <h3 class="orders-show__subheading">{{ t("admin.orders.paymentsTitle") }}</h3>
                <div class="orders-show__payments">
                    <div v-for="payment in order.payments" :key="payment.id" class="orders-show__payment-card">
                        <strong>{{ payment.provider }}</strong>
                        <span>{{ payment.amount }} {{ payment.currency }}</span>
                        <span>{{ t(`payments.status.${payment.status}`) }}</span>
                        <span>{{ payment.provider_payment_id || '—' }}</span>
                    </div>
                </div>
            </section>

            <section class="orders-show__panel">
                <div class="orders-show__timeline-head">
                    <h2>{{ t("admin.orders.timelineTitle") }}</h2>
                </div>

                <form class="orders-show__note-form" @submit.prevent="submitNote">
                    <textarea
                        v-model="noteForm.note"
                        class="orders-show__textarea"
                        :placeholder="t('admin.orders.notePlaceholder')"
                    />
                    <button type="submit" class="orders-show__note-button" :disabled="noteForm.processing">
                        {{ noteForm.processing ? t("auth.wait") : t("admin.orders.addNote") }}
                    </button>
                </form>

                <div class="orders-show__timeline">
                    <article v-for="event in order.events" :key="event.id" class="orders-show__event">
                        <div class="orders-show__event-top">
                            <strong>{{ event.title }}</strong>
                            <span>{{ formatDateTime(event.created_at) }}</span>
                        </div>
                        <p v-if="event.message" class="orders-show__event-message">{{ event.message }}</p>
                        <small v-if="event.actor" class="orders-show__event-actor">
                            {{ event.actor.name }} · {{ event.actor.email }}
                        </small>
                    </article>
                </div>
            </section>
        </div>
    </div>
</template>

<script setup>
import {ref} from 'vue'
import {router, useForm} from '@inertiajs/vue3'
import {route} from 'ziggy-js'
import {useI18n} from '../../../lang/useI18n'

const props = defineProps({
    order: Object,
})

const {t, currentLang} = useI18n()
const status = ref(props.order.status)
const noteForm = useForm({
    note: '',
})

const formatDateTime = (dateString) => {
    if (!dateString) return t("profile.notProvided")

    const localeMap = {ru: "ru-RU", uk: "uk-UA", en: "en-US"}
    const lang = currentLang?.value ?? "ru"

    return new Date(dateString).toLocaleDateString(localeMap[lang], {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    })
}

const updateStatus = () => {
    router.put(route('admin.orders.update', props.order.id), {status: status.value}, {
        preserveScroll: true,
    })
}

const submitNote = () => {
    noteForm.post(route('admin.orders.notes.store', props.order.id), {
        preserveScroll: true,
        onSuccess: () => {
            noteForm.reset()
        },
    })
}
</script>

<style scoped lang="scss">
.orders-show {
    max-width: 1320px;
    margin: 0 auto;
    padding: 24px 8px 40px;
    font-family: "Press Start 2P", system-ui;

    &__hero {
        display: flex;
        justify-content: space-between;
        align-items: start;
        gap: 20px;
        margin-bottom: 24px;
    }

    &__eyebrow {
        margin: 0 0 8px;
        color: #64748b;
        font-size: 11px;
    }

    &__title {
        margin: 0;
        font-size: 26px;
    }

    &__hero-actions {
        display: flex;
        gap: 12px;
    }

    &__link {
        display: inline-flex;
        align-items: center;
        padding: 12px 14px;
        border-radius: 12px;
        background: #2563eb;
        color: #fff;
        text-decoration: none;
    }

    &__grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    &__grid--bottom {
        align-items: start;
    }

    &__panel {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 24px;
    }

    &__rows {
        display: grid;
        gap: 14px;
    }

    &__row {
        display: grid;
        grid-template-columns: 180px 1fr;
        gap: 16px;
        align-items: center;

        span {
            color: #64748b;
            font-size: 11px;
        }

        strong {
            font-size: 11px;
            line-height: 1.5;
        }
    }

    &__select,
    &__textarea {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        background: #fff;
        padding: 12px;
        font-family: inherit;
        font-size: 11px;
    }

    &__items,
    &__payments,
    &__timeline {
        display: grid;
        gap: 12px;
    }

    &__item,
    &__payment-card,
    &__event {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px;
        background: #f8fafc;
    }

    &__item {
        display: grid;
        grid-template-columns: 72px 1fr;
        gap: 14px;
    }

    &__item-image {
        width: 72px;
        height: 72px;
        border-radius: 12px;
        object-fit: cover;
    }

    &__item-body,
    &__payment-card {
        display: grid;
        gap: 8px;
        font-size: 11px;
    }

    &__subheading {
        margin: 24px 0 12px;
        font-size: 14px;
    }

    &__timeline-head {
        margin-bottom: 16px;
    }

    &__note-form {
        display: grid;
        gap: 12px;
        margin-bottom: 18px;
    }

    &__textarea {
        min-height: 120px;
        resize: vertical;
    }

    &__note-button {
        justify-self: start;
        border: none;
        border-radius: 12px;
        padding: 12px 16px;
        background: #16a34a;
        color: #fff;
        font-family: inherit;
        font-size: 11px;
        cursor: pointer;
    }

    &__event-top {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 10px;

        strong,
        span {
            font-size: 11px;
        }
    }

    &__event-message {
        margin: 0 0 10px;
        font-size: 11px;
        line-height: 1.7;
        color: #0f172a;
    }

    &__event-actor {
        color: #64748b;
        font-size: 10px;
    }
}
</style>
