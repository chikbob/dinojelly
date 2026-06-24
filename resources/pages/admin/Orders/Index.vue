<template>
    <div class="orders-index">
        <div class="orders-index__head">
            <div>
                <h1 class="orders-index__title">{{ t("admin.orders.title") }}</h1>
                <p class="orders-index__subtitle">{{ t("admin.orders.subtitle") }}</p>
            </div>
        </div>

        <section class="orders-index__filters" style="width:100%; max-width:100%; min-width:0; overflow:hidden;">
            <input
                v-model="localFilters.search"
                type="text"
                :placeholder="t('admin.orders.searchPlaceholder')"
                class="orders-index__input"
                style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;"
                @keyup.enter="applyFilters"
            />

            <select v-model="localFilters.status" class="orders-index__select" @change="applyFilters" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;">
                <option value="">{{ t("orders.all") }}</option>
                <option value="pending">{{ t("admin.orders.statuses.pending") }}</option>
                <option value="completed">{{ t("admin.orders.statuses.completed") }}</option>
                <option value="canceled">{{ t("admin.orders.statuses.canceled") }}</option>
            </select>

            <select v-model="localFilters.payment_status" class="orders-index__select" @change="applyFilters" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;">
                <option value="">{{ t("admin.orders.paymentStatuses.all") }}</option>
                <option value="pending">{{ t("payments.status.pending") }}</option>
                <option value="paid">{{ t("payments.status.paid") }}</option>
                <option value="failed">{{ t("payments.status.failed") }}</option>
                <option value="canceled">{{ t("payments.status.canceled") }}</option>
            </select>

            <button class="orders-index__button orders-index__button--apply" @click="applyFilters" style="width:100%; max-width:100%; box-sizing:border-box;">
                {{ t("catalog.apply") }}
            </button>
            <button class="orders-index__button orders-index__button--ghost" @click="resetFilters" style="width:100%; max-width:100%; box-sizing:border-box;">
                {{ t("catalog.reset") }}
            </button>
        </section>

        <div class="orders-index__table-wrap">
            <table class="orders-index__table">
                <thead>
                <tr>
                    <th>{{ t("admin.orders.id") }}</th>
                    <th>{{ t("admin.orders.customer") }}</th>
                    <th>{{ t("admin.orders.status") }}</th>
                    <th>{{ t("admin.orders.paymentStatus") }}</th>
                    <th>{{ t("admin.orders.totalPrice") }}</th>
                    <th>{{ t("admin.orders.createdAt") }}</th>
                    <th>{{ t("admin.orders.actions") }}</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="order in orders.data" :key="order.id">
                    <td>#{{ order.id }}</td>
                    <td>
                        <div class="orders-index__customer">
                            <strong>{{ order.customer?.name || t("profile.notProvided") }}</strong>
                            <span>{{ order.customer?.email || t("profile.notProvided") }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="orders-index__status" :class="statusClass(order.status)">
                            <select
                                v-model="order.status"
                                @change="updateStatus(order.id, order.status)"
                                class="orders-index__status-select"
                                style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;"
                            >
                                <option value="pending">{{ t("admin.orders.statuses.pending") }}</option>
                                <option value="completed">{{ t("admin.orders.statuses.completed") }}</option>
                                <option value="canceled">{{ t("admin.orders.statuses.canceled") }}</option>
                            </select>
                        </div>
                    </td>
                    <td>
                        <span
                            v-if="order.latest_payment"
                            class="orders-index__payment-pill"
                            :class="`orders-index__payment-pill--${order.latest_payment.status}`"
                        >
                            {{ t(`payments.status.${order.latest_payment.status}`) }}
                        </span>
                        <span v-else>—</span>
                    </td>
                    <td>{{ order.total_price }} {{ t("currency.symbol") }}</td>
                    <td>{{ formatDateTime(order.created_at) }}</td>
                    <td>
                        <a :href="route('admin.orders.show', order.id)" class="orders-index__link" style="width:100%; max-width:100%; box-sizing:border-box;">
                            {{ t("admin.orders.view") }}
                        </a>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <Paginate :links="orders.links"/>
    </div>
</template>

<script setup>
import {reactive} from 'vue'
import {router} from '@inertiajs/vue3'
import {route} from 'ziggy-js'
import {useI18n} from '../../../lang/useI18n.js'
import Paginate from '../../../components/pagination.vue'

const props = defineProps({
    orders: Object,
    filters: Object,
})

const {t, currentLang} = useI18n()

const localFilters = reactive({
    status: props.filters?.status ?? '',
    payment_status: props.filters?.payment_status ?? '',
    search: props.filters?.search ?? '',
})

const formatDateTime = (dateString) => {
    if (!dateString) return t("profile.notProvided")

    const localeMap = {ru: "ru-RU", en: "en-US"}
    const lang = currentLang?.value ?? "ru"

    return new Date(dateString).toLocaleDateString(localeMap[lang], {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    })
}

const statusClass = (status) => ({
    'orders-index__status--success': status === 'completed',
    'orders-index__status--pending': status === 'pending',
    'orders-index__status--canceled': status === 'canceled',
})

const applyFilters = () => {
    router.get(route('admin.orders.index'), {
        status: localFilters.status || undefined,
        payment_status: localFilters.payment_status || undefined,
        search: localFilters.search || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    })
}

const resetFilters = () => {
    localFilters.status = ''
    localFilters.payment_status = ''
    localFilters.search = ''
    applyFilters()
}

const updateStatus = (orderId, status) => {
    router.put(route('admin.orders.update', orderId), {status}, {
        preserveState: true,
        preserveScroll: true,
    })
}
</script>

<style scoped lang="scss">
.orders-index {
    max-width: 1320px;
    margin: 0 auto;
    padding: 24px 8px 40px;
    font-family: "Press Start 2P", system-ui;
    min-width: 0;

    &__head {
        display: flex;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 20px;
    }

    &__title {
        margin: 0 0 8px;
        font-size: 24px;
    }

    &__subtitle {
        margin: 0;
        color: #64748b;
        font-size: 12px;
        line-height: 1.6;
    }

    &__filters {
        display: grid;
        grid-template-columns: 1.4fr repeat(2, minmax(0, 220px)) auto auto;
        gap: 12px;
        margin-bottom: 24px;
        padding: 16px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        min-width: 0;
    }

    &__input,
    &__select,
    &__status-select {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        box-sizing: border-box;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        background: #fff;
        padding: 10px 12px;
        font-family: inherit;
        font-size: 11px;
    }

    &__button {
        max-width: 100%;
        border: none;
        border-radius: 10px;
        padding: 10px 14px;
        cursor: pointer;
        font-family: inherit;
        font-size: 11px;

        &--apply {
            background: #16a34a;
            color: #fff;
        }

        &--ghost {
            background: #e2e8f0;
            color: #0f172a;
        }
    }

    &__table-wrap {
        overflow-x: auto;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        margin-bottom: 24px;
    }

    &__table {
        width: 100%;
        border-collapse: collapse;

        th,
        td {
            padding: 14px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            vertical-align: top;
            font-size: 11px;
        }

        th {
            background: #f8fafc;
        }
    }

    &__customer {
        display: grid;
        gap: 6px;

        span {
            color: #64748b;
            font-size: 10px;
        }
    }

    &__status {
        display: inline-flex;
        padding: 4px;
        border-radius: 12px;

        &--success {
            background: #dcfce7;
        }

        &--pending {
            background: #fef3c7;
        }

        &--canceled {
            background: #fee2e2;
        }
    }

    &__payment-pill {
        display: inline-flex;
        padding: 8px 10px;
        border-radius: 999px;
        font-size: 10px;

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

    &__link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 12px;
        border-radius: 10px;
        text-decoration: none;
        background: #2563eb;
        color: #fff;
    }
}

@media (max-width: 960px) {
    .orders-index {
        &__filters {
            grid-template-columns: 1fr;
        }

        &__button,
        &__link {
            width: 100%;
        }
    }
}
</style>
