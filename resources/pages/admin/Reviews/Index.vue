<template>
    <div class="admin-list">
        <h1 class="admin-list__title">{{ t("admin.reviews.title") }}</h1>
        <table class="admin-list__table">
            <thead>
            <tr>
                <th>ID</th>
                <th>{{ t("admin.reviews.product") }}</th>
                <th>{{ t("admin.reviews.user") }}</th>
                <th>{{ t("admin.reviews.rating") }}</th>
                <th>{{ t("admin.reviews.body") }}</th>
                <th>{{ t("admin.reviews.status") }}</th>
                <th>{{ t("admin.reviews.actions") }}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="review in reviews.data" :key="review.id">
                <td>#{{ review.id }}</td>
                <td>{{ review.product?.name }}</td>
                <td>{{ review.user?.name }}</td>
                <td>{{ review.rating }}/5</td>
                <td>{{ review.title || review.body || '—' }}</td>
                <td>{{ review.is_published ? t('admin.reviews.published') : t('admin.reviews.hidden') }}</td>
                <td>
                    <div class="admin-list__actions">
                        <button class="admin-list__button" @click="toggleReview(review)">
                            {{ review.is_published ? t('admin.reviews.hide') : t('admin.reviews.publish') }}
                        </button>
                        <button class="admin-list__danger" @click="destroy(review.id)">
                            {{ t("admin.actions.delete") }}
                        </button>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
        <Paginate :links="reviews.links" />
    </div>
</template>

<script setup>
import {router} from '@inertiajs/vue3'
import {route} from 'ziggy-js'
import Paginate from '../../../components/pagination.vue'
import {useI18n} from '../../../lang/useI18n'
const props = defineProps({ reviews: Object })
const {t} = useI18n()
const toggleReview = (review) => router.put(route('admin.reviews.update', review.id), { is_published: !review.is_published }, { preserveScroll: true })
const destroy = (id) => router.delete(route('admin.reviews.destroy', id), { preserveScroll: true })
</script>

<style scoped lang="scss">
.admin-list { max-width: 1200px; margin: 0 auto; }
.admin-list__title { margin-bottom: 20px; font-size: 24px; }
.admin-list__table { width: 100%; border-collapse: collapse; background: #fff; }
.admin-list__table th, .admin-list__table td { padding: 14px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 11px; }
.admin-list__table th { background: #f8fafc; }
.admin-list__button { border: none; border-radius: 10px; padding: 10px 12px; background: #2563eb; color: #fff; cursor: pointer; font-family: inherit; font-size: 11px; }
.admin-list__actions { display: flex; gap: 10px; align-items: center; }
.admin-list__danger { border: none; background: none; color: #dc2626; cursor: pointer; font-family: inherit; font-size: 11px; }
</style>
