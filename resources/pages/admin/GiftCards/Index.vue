<template>
    <div class="admin-list">
        <div class="admin-list__head">
            <h1 class="admin-list__title">{{ t("admin.giftCards.title") }}</h1>
            <a :href="route('admin.gift-cards.create')" class="admin-list__create">{{ t("admin.actions.create") }}</a>
        </div>

        <table class="admin-list__table">
            <thead>
            <tr>
                <th>Code</th>
                <th>{{ t("admin.giftCards.name") }}</th>
                <th>{{ t("admin.giftCards.balance") }}</th>
                <th>{{ t("admin.giftCards.recipient") }}</th>
                <th>{{ t("admin.giftCards.expiresAt") }}</th>
                <th>{{ t("admin.giftCards.status") }}</th>
                <th>{{ t("admin.orders.actions") }}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="giftCard in giftCards.data" :key="giftCard.id">
                <td>{{ giftCard.code }}</td>
                <td>{{ giftCard.name }}</td>
                <td>{{ giftCard.balance }} / {{ giftCard.initial_amount }}</td>
                <td>{{ giftCard.recipient || '—' }}</td>
                <td>{{ giftCard.expires_at || '—' }}</td>
                <td>{{ giftCard.is_active ? t('admin.giftCards.active') : t('admin.giftCards.inactive') }}</td>
                <td>
                    <div class="admin-list__actions">
                        <a :href="route('admin.gift-cards.edit', giftCard.id)" class="admin-list__link">{{ t("admin.actions.edit") }}</a>
                        <button class="admin-list__danger" @click="destroy(giftCard.id)">{{ t("admin.actions.delete") }}</button>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>

        <Paginate :links="giftCards.links" />
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Paginate from '../../../components/pagination.vue'
import { useI18n } from '../../../lang/useI18n'

defineProps({ giftCards: Object })
const { t } = useI18n()
const destroy = (id) => router.delete(route('admin.gift-cards.destroy', id))
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
