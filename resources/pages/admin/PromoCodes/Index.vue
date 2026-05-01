<template>
    <div class="admin-list">
        <div class="admin-list__head">
            <h1 class="admin-list__title">{{ t("admin.promoCodes.title") }}</h1>
            <a :href="route('admin.promo-codes.create')" class="admin-list__create">{{ t("admin.actions.create") }}</a>
        </div>
        <div class="admin-table-card">
            <div class="admin-table-wrap">
                <table class="admin-table">
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
                            <div class="admin-table__actions">
                                <a :href="route('admin.promo-codes.edit', promoCode.id)" class="admin-action-link">{{ t("admin.actions.edit") }}</a>
                                <button class="admin-button admin-button--danger" @click="destroy(promoCode.id)">{{ t("admin.actions.delete") }}</button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
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
</style>
