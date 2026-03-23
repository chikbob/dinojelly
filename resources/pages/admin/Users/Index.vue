<template>
    <div class="users-index">
        <h1 class="users-index__title">
            {{ t("admin.users.title") }}
        </h1>

        <table class="users-index__table">
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
                    <a :href="route('admin.users.show', user.id)" class="users-index__link">
                        {{ t("admin.orders.view") }}
                    </a>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { route } from 'ziggy-js'
import { useI18n } from '../../../lang/useI18n'

const { t } = useI18n()

const props = defineProps({
    users: Object,
})
</script>

<style scoped lang="scss">
.users-index {
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

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f5f7fa;
        }
    }

    &__link {
        color: #29cc5f;
        text-decoration: none;
    }
}
</style>
