<template>
    <div class="users-index admin-list">
        <div class="admin-list__head">
            <h1 class="admin-list__title">
            {{ t("admin.users.title") }}
            </h1>
        </div>

        <div class="admin-table-card">
            <div class="admin-table-wrap">
                <table class="admin-table" style="min-width:640px;">
                    <thead>
                    <tr>
                        <th>{{ t("admin.users.id") }}</th>
                        <th>{{ t("admin.users.name") }}</th>
                        <th>{{ t("admin.users.email") }}</th>
                        <th>{{ t("admin.orders.actions") }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="user in users.data" :key="user.id">
                        <td>{{ user.id }}</td>
                        <td>{{ user.name }}</td>
                        <td>{{ user.email }}</td>
                        <td>
                            <a :href="route('admin.users.show', user.id)" class="admin-action-link">
                                {{ t("admin.orders.view") }}
                            </a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Paginate :links="users.links" />
    </div>
</template>

<script setup>
import { route } from 'ziggy-js'
import { useI18n } from '../../../lang/useI18n'
import Paginate from '../../../components/pagination.vue'

const { t } = useI18n()

const props = defineProps({
    users: Object,
})
</script>

<style scoped lang="scss">
</style>
