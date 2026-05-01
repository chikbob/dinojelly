<template>
    <div class="admin-list">
        <div class="admin-list__head">
            <h1 class="admin-list__title">{{ t("admin.referrals.title") }}</h1>
        </div>

        <div class="admin-kpis">
            <div class="admin-kpi">
                <strong>{{ stats.total }}</strong>
                <span>{{ t("admin.referrals.total") }}</span>
            </div>
            <div class="admin-kpi">
                <strong>{{ stats.rewarded }}</strong>
                <span>{{ t("admin.referrals.rewarded") }}</span>
            </div>
            <div class="admin-kpi">
                <strong>{{ stats.pending }}</strong>
                <span>{{ t("admin.referrals.pending") }}</span>
            </div>
            <div class="admin-kpi">
                <strong>{{ stats.credits_issued }}</strong>
                <span>{{ t("admin.referrals.creditsIssued") }}</span>
            </div>
        </div>

        <div class="admin-table-card">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>{{ t("admin.referrals.referrer") }}</th>
                        <th>{{ t("admin.referrals.referred") }}</th>
                        <th>{{ t("admin.referrals.status") }}</th>
                        <th>{{ t("admin.referrals.reward") }}</th>
                        <th>{{ t("admin.referrals.order") }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="referral in referrals.data" :key="referral.id">
                        <td>{{ referral.id }}</td>
                        <td>{{ referral.referrer }}</td>
                        <td>{{ referral.referred_user }}</td>
                        <td>{{ referral.status }}</td>
                        <td>{{ referral.reward_amount }}</td>
                        <td>{{ referral.order_id || '—' }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Paginate :links="referrals.links" />
    </div>
</template>

<script setup>
import Paginate from '../../../components/pagination.vue'
import { useI18n } from '../../../lang/useI18n'
defineProps({ referrals: Object, stats: Object })
const { t } = useI18n()
</script>

<style scoped lang="scss">
.admin-kpis { display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:16px; margin-bottom:20px; }
.admin-kpi { background:#fff; border-radius:16px; padding:18px; display:grid; gap:8px; }
</style>
