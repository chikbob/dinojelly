<template>
    <div class="admin-list">
        <div class="admin-list__head">
            <h1 class="admin-list__title">{{ t("admin.reviews.title") }}</h1>
        </div>
        <div class="admin-table-card">
            <div class="admin-table-wrap">
                <table class="admin-table">
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
                            <div class="admin-table__actions">
                                <button class="admin-button admin-button--primary" @click="toggleReview(review)">
                                    {{ review.is_published ? t('admin.reviews.hide') : t('admin.reviews.publish') }}
                                </button>
                                <button class="admin-button admin-button--danger" @click="destroy(review.id)">
                                    {{ t("admin.actions.delete") }}
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
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
</style>
