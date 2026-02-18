<template>
    <div class="orders-index">
        <h1 class="orders-index__title">{{ t("admin.orders.title") }}</h1>

        <table class="orders-index__table">
            <thead>
            <tr>
                <th>{{ t("admin.orders.id") }}</th>
                <th>{{ t("admin.orders.status") }}</th>
                <th>{{ t("admin.orders.totalPrice") }}</th>
                <th>{{ t("admin.orders.createdAt") }}</th>
                <th>{{ t("admin.orders.actions") }}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="order in orders.data" :key="order.id">
                <td>{{ order.id }}</td>

                <!-- Select для изменения статуса -->
                <td>
                    <div
                        class="orders-index__status"
                        :class="statusClass(order.status)"
                    >
                        <select
                            v-model="order.status"
                            @change="updateStatus(order.id, order.status)"
                            class="orders-index__select"
                        >
                            <option value="pending">{{ t("admin.orders.statuses.pending") }}</option>
                            <option value="completed">{{ t("admin.orders.statuses.completed") }}</option>
                            <option value="canceled">{{ t("admin.orders.statuses.canceled") }}</option>
                        </select>
                    </div>
                </td>

                <td>{{ order.total_price }}</td>
                <td>{{ formatDateTime(order.created_at) }}</td>
                <td>
                    <a :href="route('admin.orders.show', order.id)" class="btn btn-show">
                        {{ t("admin.orders.view") }}
                    </a>
                </td>
            </tr>
            </tbody>
        </table>

        <Paginate :links="orders.links"/>
    </div>
</template>

<script setup>
import {router} from '@inertiajs/vue3'
import {route} from 'ziggy-js'
import {useI18n} from '../../../lang/useI18n.js'
import Paginate from '../../../components/pagination.vue';

const props = defineProps({
    orders: Object,
})

const {t, currentLang} = useI18n()

const formatDateTime = (dateString) => {
    if (!dateString) return t("profile.notProvided")

    const localeMap = {
        ru: "ru-RU",
        uk: "uk-UA",
        en: "en-US",
    }

    const lang = currentLang?.value ?? "ru"

    return new Date(dateString).toLocaleDateString(localeMap[lang], {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    })
}

const statusClass = (status) => {
    return {
        'orders-index__status--success': status === 'completed',
        'orders-index__status--pending': status === 'pending',
        'orders-index__status--canceled': status === 'canceled',
    }
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
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
    font-family: "Press Start 2P", system-ui;

    &__title {
        font-size: 24px;
        margin-bottom: 24px;
    }

    &__table {
        width: 100%;
        border-collapse: collapse;

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f5f7fa;
        }
    }

    &__select {
        font-family: "Press Start 2P", system-ui;
        font-size: 12px;
        padding: 6px;
        border-radius: 6px;
        border: 1px solid #ddd;
        cursor: pointer;
        background-color: white;
        width: 100%;
        max-width: 140px;
        appearance: none;
    }

    .btn-show {
        background-color: #3ecf8e;
        color: white;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 14px;
        text-decoration: none;
    }
}

.orders-index__status {
    display: inline-block;
    padding: 4px 6px;
    border-radius: 6px;

    &--success {
        background: #29CC5F;
        color: #333;
    }

    &--pending {
        background: #ffc107;
        color: #333;
    }

    &--canceled {
        background: #ff6b6b;
        color: #333;
    }

    select {
        background: transparent;
        border: none;
        color: inherit;
        font-weight: 600;
        cursor: pointer;
        outline: none;
    }
}

</style>
