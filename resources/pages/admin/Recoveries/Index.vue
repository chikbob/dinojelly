<template>
    <div class="admin-list">
        <h1 class="admin-list__title">{{ t("admin.recoveries.title") }}</h1>
        <table class="admin-list__table">
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
.admin-list { max-width: 1200px; margin: 0 auto; }
.admin-list__title { margin-bottom: 20px; font-size: 24px; }
.admin-list__table { width: 100%; border-collapse: collapse; background: #fff; }
.admin-list__table th, .admin-list__table td { padding: 14px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 11px; }
.admin-list__table th { background: #f8fafc; }
</style>
