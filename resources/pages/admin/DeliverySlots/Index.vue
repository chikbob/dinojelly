<template>
    <div class="admin-list">
        <div class="admin-list__head">
            <h1 class="admin-list__title">{{ t("admin.deliverySlots.title") }}</h1>
            <a :href="route('admin.delivery-slots.create')" class="admin-list__create">{{ t("admin.actions.create") }}</a>
        </div>
        <div class="admin-table-card">
            <div class="admin-table-wrap">
                <table class="admin-table">
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
                            <div class="admin-table__actions">
                                <a :href="route('admin.delivery-slots.edit', slot.id)" class="admin-action-link">{{ t("admin.actions.edit") }}</a>
                                <button class="admin-button admin-button--danger" @click="destroy(slot.id)">{{ t("admin.actions.delete") }}</button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
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
</style>
