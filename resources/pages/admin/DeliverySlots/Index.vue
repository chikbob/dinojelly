<template>
    <div class="admin-list">
        <div class="admin-list__head">
            <h1 class="admin-list__title">{{ t("admin.deliverySlots.title") }}</h1>
            <a :href="route('admin.delivery-slots.create')" class="admin-list__create">{{ t("admin.actions.create") }}</a>
        </div>
        <table class="admin-list__table">
            <thead>
            <tr>
                <th>{{ t("admin.deliverySlots.name") }}</th>
                <th>{{ t("admin.deliverySlots.window") }}</th>
                <th>{{ t("admin.deliverySlots.capacity") }}</th>
                <th>{{ t("admin.deliverySlots.price") }}</th>
                <th>{{ t("admin.deliverySlots.ordersCount") }}</th>
                <th>{{ t("admin.deliverySlots.status") }}</th>
                <th>{{ t("admin.orders.actions") }}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="slot in slots.data" :key="slot.id">
                <td>{{ slot.name }}</td>
                <td>{{ formatDate(slot.starts_at) }} - {{ formatDate(slot.ends_at) }}</td>
                <td>{{ slot.capacity }}</td>
                <td>{{ slot.price }}</td>
                <td>{{ slot.orders_count }}</td>
                <td>{{ slot.is_active ? t('admin.deliverySlots.active') : t('admin.deliverySlots.inactive') }}</td>
                <td>
                    <div class="admin-list__actions">
                        <a :href="route('admin.delivery-slots.edit', slot.id)" class="admin-list__link">{{ t("admin.actions.edit") }}</a>
                        <button class="admin-list__danger" @click="destroy(slot.id)">{{ t("admin.actions.delete") }}</button>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
        <Paginate :links="slots.links" />
    </div>
</template>

<script setup>
import {router} from '@inertiajs/vue3'
import {route} from 'ziggy-js'
import Paginate from '../../../components/pagination.vue'
import {useI18n} from '../../../lang/useI18n'
defineProps({ slots: Object })
const {t, currentLang} = useI18n()
const formatDate = (dateString) => new Date(dateString).toLocaleString(currentLang.value === 'en' ? 'en-US' : 'ru-RU')
const destroy = (id) => router.delete(route('admin.delivery-slots.destroy', id))
</script>

<style scoped lang="scss">
.admin-list { max-width: 1200px; margin: 0 auto; }
.admin-list__head { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:20px; }
.admin-list__title { margin-bottom: 20px; font-size: 24px; }
.admin-list__table { width: 100%; border-collapse: collapse; background: #fff; }
.admin-list__table th, .admin-list__table td { padding: 14px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 11px; }
.admin-list__table th { background: #f8fafc; }
.admin-list__create, .admin-list__link { color:#2563eb; text-decoration:none; }
.admin-list__actions { display:flex; gap:10px; align-items:center; }
.admin-list__danger { border:none; background:none; color:#dc2626; cursor:pointer; font-family:inherit; font-size:11px; }
</style>
