<template>
    <div class="categories-index admin-list">
        <div class="admin-list__head">
            <h1 class="admin-list__title">{{ t("admin.categories.title") }}</h1>
            <a :href="route('admin.categories.create')" class="admin-list__create">
                {{ t("admin.categories.createNew") }}
            </a>
        </div>

        <div class="admin-table-card">
            <div class="admin-table-wrap">
                <table class="admin-table" style="min-width:720px;">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>{{ t("admin.categories.name") }}</th>
                        <th>{{ t("admin.categories.slug") }}</th>
                        <th>{{ t("admin.categories.status") }}</th>
                        <th>{{ t("admin.products.actions") }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="category in categories.data" :key="category.id">
                        <td>{{ category.id }}</td>
                        <td>{{ category.name }}</td>
                        <td>{{ category.slug }}</td>
                        <td>{{ category.is_active ? t("admin.categories.active") : t("admin.categories.inactive") }}</td>
                        <td>
                            <div class="admin-table__actions">
                                <a :href="route('admin.categories.edit', category.id)" class="admin-action-link">
                                    {{ t("admin.products.edit") }}
                                </a>
                                <button class="admin-button admin-button--danger" @click="destroy(category.id)">
                                    {{ t("admin.products.delete") }}
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Paginate :links="categories.links" />
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Paginate from '../../../components/pagination.vue'
import { useI18n } from '../../../lang/useI18n'

defineProps({
    categories: Object,
})

const { t } = useI18n()

const destroy = (id) => {
    if (confirm(t("admin.categories.confirmDelete"))) {
        router.delete(route('admin.categories.destroy', id))
    }
}
</script>

<style scoped lang="scss">
</style>
