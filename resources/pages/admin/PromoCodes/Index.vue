<template>
    <div class="admin-list">
        <div class="admin-list__head">
            <h1 class="admin-list__title">{{ t("admin.promoCodes.title") }}</h1>
            <a :href="route('admin.promo-codes.create')" class="admin-list__create">{{ t("admin.actions.create") }}</a>
        </div>
        <table class="admin-list__table">
            <thead>
            <tr>
                <th>Code</th>
                <th>{{ t("admin.promoCodes.name") }}</th>
                <th>{{ t("admin.promoCodes.type") }}</th>
                <th>{{ t("admin.promoCodes.value") }}</th>
                <th>{{ t("admin.promoCodes.usage") }}</th>
                <th>{{ t("admin.promoCodes.status") }}</th>
                <th>{{ t("admin.orders.actions") }}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="promoCode in promoCodes.data" :key="promoCode.id">
                <td>{{ promoCode.code }}</td>
                <td>{{ promoCode.name }}</td>
                <td>{{ promoCode.type }}</td>
                <td>{{ promoCode.value }}</td>
                <td>{{ promoCode.usage_count }} / {{ promoCode.usage_limit ?? '∞' }}</td>
                <td>{{ promoCode.is_active ? t('admin.promoCodes.active') : t('admin.promoCodes.inactive') }}</td>
                <td>
                    <div class="admin-list__actions">
                        <a :href="route('admin.promo-codes.edit', promoCode.id)" class="admin-list__link">{{ t("admin.actions.edit") }}</a>
                        <button class="admin-list__danger" @click="destroy(promoCode.id)">{{ t("admin.actions.delete") }}</button>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
        <Paginate :links="promoCodes.links" />
    </div>
</template>

<script setup>
import {router} from '@inertiajs/vue3'
import {route} from 'ziggy-js'
import Paginate from '../../../components/pagination.vue'
import {useI18n} from '../../../lang/useI18n'
defineProps({ promoCodes: Object })
const {t} = useI18n()
const destroy = (id) => router.delete(route('admin.promo-codes.destroy', id))
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
