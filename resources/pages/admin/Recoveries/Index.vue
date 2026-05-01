<template>
    <div class="admin-list">
        <div class="admin-list__head">
            <h1 class="admin-list__title">{{ t("admin.recoveries.title") }}</h1>
        </div>
        <div class="admin-table-card">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>{{ t("admin.recoveries.user") }}</th>
                        <th>Email</th>
                        <th>{{ t("admin.recoveries.status") }}</th>
                        <th>{{ t("admin.recoveries.items") }}</th>
                        <th>{{ t("admin.recoveries.lastActivity") }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="reminder in reminders.data" :key="reminder.id">
                        <td>#{{ reminder.id }}</td>
                        <td>{{ reminder.user?.name ?? '—' }}</td>
                        <td>{{ reminder.email }}</td>
                        <td>{{ reminder.status }}</td>
                        <td>{{ reminder.items_count }}</td>
                        <td>{{ formatDate(reminder.last_cart_activity_at) }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <Paginate :links="reminders.links" />
    </div>
</template>

<script setup>
import Paginate from '../../../components/pagination.vue'
import {useI18n} from '../../../lang/useI18n'
defineProps({ reminders: Object })
const {t, currentLang} = useI18n()
const formatDate = (dateString) => new Date(dateString).toLocaleString(currentLang.value === 'en' ? 'en-US' : 'ru-RU')
</script>

<style scoped lang="scss">
</style>
