<template>
    <div class="categories-index">
        <h1 class="categories-index__title">{{ t("admin.categories.title") }}</h1>

        <a :href="route('admin.categories.create')" class="btn btn-create">
            {{ t("admin.categories.createNew") }}
        </a>

        <table class="categories-index__table">
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
                    <div class="actions">
                        <a :href="route('admin.categories.edit', category.id)" class="btn btn-edit">
                            {{ t("admin.products.edit") }}
                        </a>
                        <button class="btn btn-delete" @click="destroy(category.id)">
                            {{ t("admin.products.delete") }}
                        </button>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>

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
.categories-index {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
    font-family: "Press Start 2P", system-ui;

    &__title {
        font-size: 24px;
        margin-bottom: 24px;
    }

    &__table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 24px;

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f5f7fa;
        }
    }
}

.btn {
    cursor: pointer;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 14px;
    text-decoration: none;
    color: white;

    &-edit {
        background-color: #3ecf8e;
        margin-right: 10px;
    }

    &-delete {
        background-color: #ef4444;
        border: none;
    }

    &-create {
        background-color: #29cc5f;
        display: inline-block;
        margin-bottom: 18px;
    }
}

.actions {
    display: flex;
    align-items: center;
}
</style>
