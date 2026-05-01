<template>
    <div class="admin-list">
        <div class="admin-list__head">
            <h1 class="admin-list__title">{{ t("admin.inventory.title") }}</h1>
        </div>
        <div class="admin-table-card">
            <div class="admin-table-wrap">
                <table class="admin-table" style="min-width:1080px;">
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
                            <form class="inventory-form" @submit.prevent="saveItem(item)" style="width:100%; max-width:100%; min-width:0; overflow:hidden;">
                                <input v-model="item.sku" class="inventory-form__input admin-input" type="text" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" />
                                <input v-model.number="item.quantity" class="inventory-form__input inventory-form__input--small admin-input" type="number" min="0" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" />
                                <input v-model.number="item.low_stock_threshold" class="inventory-form__input inventory-form__input--small admin-input" type="number" min="0" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" />
                                <label class="inventory-form__toggle">
                                    <input v-model="item.is_active" type="checkbox" />
                                </label>
                                <button class="inventory-form__save admin-button admin-button--primary" type="submit" style="width:100%; max-width:100%; box-sizing:border-box;">{{ t("admin.actions.save") }}</button>
                            </form>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
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
.inventory-form { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; min-width: 0; }
.inventory-form__input--small { min-width: 72px; }
.inventory-form__save { max-width: 100%; }
</style>
