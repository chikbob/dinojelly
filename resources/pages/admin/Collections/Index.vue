<template>
    <div class="admin-list">
        <div class="admin-list__head">
            <h1 class="admin-list__title">{{ t("admin.collections.title") }}</h1>
            <a :href="route('admin.collections.create')" class="admin-list__create">{{ t("admin.actions.create") }}</a>
        </div>
        <div class="admin-table-card">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>{{ t("admin.collections.name") }}</th>
                        <th>Slug</th>
                        <th>{{ t("admin.collections.productsCount") }}</th>
                        <th>{{ t("admin.collections.status") }}</th>
                        <th>{{ t("admin.orders.actions") }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="collection in collections.data" :key="collection.id">
                        <td>#{{ collection.id }}</td>
                        <td>{{ collection.name }}</td>
                        <td>{{ collection.slug }}</td>
                        <td>{{ collection.products_count }}</td>
                        <td>{{ collection.is_active ? t('admin.collections.active') : t('admin.collections.inactive') }}</td>
                        <td>
                            <div class="admin-table__actions">
                                <a :href="route('admin.collections.edit', collection.id)" class="admin-action-link">{{ t("admin.actions.edit") }}</a>
                                <button class="admin-button admin-button--danger" @click="destroy(collection.id)">{{ t("admin.actions.delete") }}</button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <Paginate :links="collections.links" />
    </div>
</template>

<script setup>
import {router} from '@inertiajs/vue3'
import {route} from 'ziggy-js'
import Paginate from '../../../components/pagination.vue'
import {useI18n} from '../../../lang/useI18n'
defineProps({ collections: Object })
const {t} = useI18n()
const destroy = (id) => router.delete(route('admin.collections.destroy', id))
</script>

<style scoped lang="scss">
</style>
