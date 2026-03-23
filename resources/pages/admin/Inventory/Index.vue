<template>
    <div class="admin-list">
        <h1 class="admin-list__title">{{ t("admin.inventory.title") }}</h1>
        <table class="admin-list__table">
            <thead>
            <tr>
                <th>SKU</th>
                <th>{{ t("admin.inventory.product") }}</th>
                <th>{{ t("admin.inventory.quantity") }}</th>
                <th>{{ t("admin.inventory.reserved") }}</th>
                <th>{{ t("admin.inventory.available") }}</th>
                <th>{{ t("admin.inventory.status") }}</th>
                <th>{{ t("admin.orders.actions") }}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="item in stockItems.data" :key="item.id">
                <td>{{ item.sku }}</td>
                <td>{{ item.product?.name }}</td>
                <td>{{ item.quantity }}</td>
                <td>{{ item.reserved_quantity }}</td>
                <td>{{ item.available_quantity }}</td>
                <td>{{ item.is_active ? t('admin.inventory.active') : t('admin.inventory.inactive') }}</td>
                <td>
                    <form class="inventory-form" @submit.prevent="saveItem(item)">
                        <input v-model="item.sku" class="inventory-form__input" type="text" />
                        <input v-model.number="item.quantity" class="inventory-form__input inventory-form__input--small" type="number" min="0" />
                        <input v-model.number="item.low_stock_threshold" class="inventory-form__input inventory-form__input--small" type="number" min="0" />
                        <label class="inventory-form__toggle">
                            <input v-model="item.is_active" type="checkbox" />
                        </label>
                        <button class="inventory-form__save" type="submit">{{ t("admin.actions.save") }}</button>
                    </form>
                </td>
            </tr>
            </tbody>
        </table>
        <Paginate :links="stockItems.links" />
    </div>
</template>

<script setup>
import {router} from '@inertiajs/vue3'
import {route} from 'ziggy-js'
import Paginate from '../../../components/pagination.vue'
import {useI18n} from '../../../lang/useI18n'
defineProps({ stockItems: Object })
const {t} = useI18n()
const saveItem = (item) => router.put(route('admin.inventory.update', item.id), {
    sku: item.sku,
    quantity: item.quantity,
    low_stock_threshold: item.low_stock_threshold,
    is_active: item.is_active ? 1 : 0,
}, { preserveScroll: true })
</script>

<style scoped lang="scss">
.admin-list { max-width: 1200px; margin: 0 auto; }
.admin-list__title { margin-bottom: 20px; font-size: 24px; }
.admin-list__table { width: 100%; border-collapse: collapse; background: #fff; }
.admin-list__table th, .admin-list__table td { padding: 14px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 11px; }
.admin-list__table th { background: #f8fafc; }
.inventory-form { display: flex; gap: 8px; align-items: center; }
.inventory-form__input { min-width: 120px; padding: 8px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; font-size: 11px; }
.inventory-form__input--small { min-width: 72px; }
.inventory-form__save { border: none; border-radius: 8px; padding: 8px 10px; background: #2563eb; color: #fff; font-family: inherit; font-size: 11px; cursor: pointer; }
</style>
