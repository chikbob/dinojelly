<template>
    <div class="admin-list">
        <div class="admin-list__head">
            <h1 class="admin-list__title">{{ t("admin.giftCards.title") }}</h1>
            <a :href="route('admin.gift-cards.create')" class="admin-list__create">{{ t("admin.actions.create") }}</a>
        </div>

        <div class="admin-table-card">
            <div class="admin-table-wrap">
                <table class="admin-table">
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
                            <div class="admin-table__actions">
                                <a :href="route('admin.gift-cards.edit', giftCard.id)" class="admin-action-link">{{ t("admin.actions.edit") }}</a>
                                <button class="admin-button admin-button--danger" @click="destroy(giftCard.id)">{{ t("admin.actions.delete") }}</button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

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
</style>
