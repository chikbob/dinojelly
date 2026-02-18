<template>
    <div class="orders-show">
        <h1 class="orders-show__title">
            {{ t("admin.orders.orderNumber") }} #{{ order.id }}
        </h1>

        <div
            class="orders-show__status"
            :class="statusClass(status)"
        >
            <strong>{{ t("admin.orders.status") }}:</strong>

            <select
                v-model="status"
                @change="updateStatus"
                class="orders-show__select"
            >
                <option value="pending">{{ t("admin.orders.statuses.pending") }}</option>
                <option value="completed">{{ t("admin.orders.statuses.completed") }}</option>
                <option value="canceled">{{ t("admin.orders.statuses.canceled") }}</option>
            </select>
        </div>


        <p class="orders-show__field">
            <strong>{{ t("admin.orders.totalPrice") }}:</strong> {{ order.total_price }}
        </p>
        <p class="orders-show__field">
            <strong>{{ t("admin.orders.createdAt") }}:</strong> {{ formatDateTime(order.created_at) }}
        </p>

        <!-- Здесь можно добавить детали заказа, товары и т.д. -->
    </div>
</template>

<script setup>
import {ref} from 'vue'
import {router} from '@inertiajs/vue3'
import {route} from 'ziggy-js'
import {useI18n} from '../../../lang/useI18n'

const props = defineProps({
    order: Object,
})

const {t, currentLang} = useI18n()

const status = ref(props.order.status)

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
        'orders-show__status--success': status === 'completed',
        'orders-show__status--pending': status === 'pending',
        'orders-show__status--canceled': status === 'canceled',
    }
}

const updateStatus = () => {
    router.put(route('admin.orders.update', props.order.id), {status: status.value}, {
        preserveState: true,
        preserveScroll: true,
    })
}
</script>

<style scoped lang="scss">
.orders-show {
    max-width: 800px;
    margin: 0 auto;
    padding: 40px 20px;
    font-family: "Press Start 2P", system-ui;

    &__title {
        font-size: 24px;
        margin-bottom: 24px;
    }

    &__field {
        margin-bottom: 16px;
        font-size: 16px;
    }

    &__select {
        font-family: "Press Start 2P", system-ui;
        font-size: 14px;
        padding: 6px;
        border-radius: 6px;
        border: 1px solid #ddd;
        cursor: pointer;
        background-color: white;
        margin-left: 8px;
        max-width: 160px;
    }
}

.orders-show__status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 10px;
    border-radius: 8px;
    font-weight: 600;

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
