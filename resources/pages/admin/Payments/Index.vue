<template>
    <div class="admin-list">
        <h1 class="admin-list__title">{{ t("admin.payments.title") }}</h1>
        <table class="admin-list__table">
            <thead>
            <tr>
                <th>ID</th>
                <th>{{ t("admin.payments.order") }}</th>
                <th>{{ t("admin.payments.provider") }}</th>
                <th>{{ t("admin.payments.status") }}</th>
                <th>{{ t("admin.payments.amount") }}</th>
                <th>{{ t("admin.payments.createdAt") }}</th>
                <th>{{ t("admin.orders.actions") }}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="payment in payments.data" :key="payment.id">
                <td>#{{ payment.id }}</td>
                <td>{{ payment.order ? `#${payment.order.id} · ${payment.order.customer_name ?? '—'}` : '—' }}</td>
                <td>{{ payment.provider }}</td>
                <td>{{ t(`payments.status.${payment.status}`) }}</td>
                <td>{{ payment.amount }} {{ payment.currency }}</td>
                <td>{{ formatDate(payment.created_at) }}</td>
                <td>
                    <select class="admin-list__select" :value="payment.status" @change="updateStatus(payment.id, $event.target.value)">
                        <option value="pending">{{ t("payments.status.pending") }}</option>
                        <option value="paid">{{ t("payments.status.paid") }}</option>
                        <option value="failed">{{ t("payments.status.failed") }}</option>
                        <option value="canceled">{{ t("payments.status.canceled") }}</option>
                    </select>
                </td>
            </tr>
            </tbody>
        </table>
        <Paginate :links="payments.links" />
    </div>
</template>

<script setup>
import {router} from '@inertiajs/vue3'
import {route} from 'ziggy-js'
import Paginate from '../../../components/pagination.vue'
import {useI18n} from '../../../lang/useI18n'

defineProps({ payments: Object })
const {t, currentLang} = useI18n()
const formatDate = (dateString) => new Date(dateString).toLocaleDateString(currentLang.value === 'en' ? 'en-US' : 'ru-RU')
const updateStatus = (id, status) => router.put(route('admin.payments.update', id), { status }, { preserveScroll: true })
</script>

<style scoped lang="scss">
.admin-list { max-width: 1200px; margin: 0 auto; }
.admin-list__title { margin-bottom: 20px; font-size: 24px; }
.admin-list__table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 16px; overflow: hidden; }
.admin-list__table th, .admin-list__table td { padding: 14px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 11px; }
.admin-list__table th { background: #f8fafc; }
.admin-list__select { width: 100%; min-width: 140px; padding: 8px; border-radius: 8px; border: 1px solid #cbd5e1; font-family: inherit; font-size: 11px; }
</style>
