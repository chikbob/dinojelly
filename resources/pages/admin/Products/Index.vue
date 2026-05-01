<template>
    <div class="products-index admin-list">
        <div class="admin-list__head">
            <h1 class="admin-list__title">{{ t("admin.products.title") }}</h1>
            <a :href="route('admin.products.create')" class="admin-list__create">
                {{ t("admin.products.createNew") }}
            </a>
        </div>

        <div class="admin-table-card">
            <div class="admin-table-wrap">
                <table class="admin-table" style="min-width:1080px;">
                    <thead>
                    <tr>
                        <th>{{ t("admin.products.id") }}</th>
                        <th>{{ t("admin.products.image") }}</th>
                        <th>{{ t("admin.products.name") }}</th>
                        <th>{{ t("admin.products.category") }}</th>
                        <th>{{ t("admin.products.weight") }}</th>
                        <th>{{ t("admin.products.price") }}</th>
                        <th>{{ t("admin.products.oldPrice") }}</th>
                        <th>{{ t("admin.products.stock") }}</th>
                        <th>{{ t("admin.products.actions") }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="product in products.data" :key="product.id">
                        <td>{{ product.id }}</td>
                        <td>
                            <img
                                v-if="product.image_url"
                                :src="product.image_url"
                                :alt="product.name"
                                class="product-image"
                            />
                            <span v-else>—</span>
                        </td>
                        <td>{{ product.name }}</td>
                        <td>{{ product.category?.name ?? '—' }}</td>
                        <td>{{ product.weight ?? '—' }}</td>
                        <td>{{ product.price }}</td>
                        <td>{{ product.old_price ?? '—' }}</td>
                        <td>
                            <span :class="stockClass(product)">
                                {{ stockLabel(product) }}
                            </span>
                        </td>
                        <td>
                            <div class="admin-table__actions">
                                <a :href="route('admin.products.edit', product.id)" class="admin-action-link">
                                    {{ t("admin.products.edit") }}
                                </a>
                                <button @click="destroy(product.id)" class="admin-button admin-button--danger">
                                    {{ t("admin.products.delete") }}
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Paginate :links="products.links"/>
    </div>
</template>

<script setup>
import {router} from '@inertiajs/vue3'
import {route} from "ziggy-js";
import Paginate from '../../../components/pagination.vue';
import {useI18n} from '../../../lang/useI18n.js'

const props = defineProps({
    products: Object,
})

const {t} = useI18n()

const stockLabel = (product) => {
    if (!product.stock_item?.is_active) return t("admin.products.stockInactive")
    const quantity = (product.stock_item?.quantity ?? 0) - (product.stock_item?.reserved_quantity ?? 0)
    if (quantity <= 0) return t("admin.products.outOfStock")
    if (quantity <= (product.stock_item?.low_stock_threshold ?? 0)) {
        return `${t("admin.products.lowStock")} (${quantity})`
    }
    return `${t("admin.products.inStock")} (${quantity})`
}

const stockClass = (product) => {
    if (!product.stock_item?.is_active) return 'stock-pill stock-pill--inactive'
    const quantity = (product.stock_item?.quantity ?? 0) - (product.stock_item?.reserved_quantity ?? 0)
    if (quantity <= 0) return 'stock-pill stock-pill--out'
    if (quantity <= (product.stock_item?.low_stock_threshold ?? 0)) return 'stock-pill stock-pill--low'
    return 'stock-pill stock-pill--ok'
}

const destroy = (id) => {
    if (confirm(t("admin.products.confirmDelete"))) {
        router.delete(route('admin.products.destroy', id))
    }
}
</script>

<style scoped lang="scss">
.product-image {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 10px;
}

.stock-pill {
    display: inline-flex;
    padding: 8px 10px;
    border-radius: 999px;
    font-size: 11px;

    &--ok {
        background: #dcfce7;
        color: #166534;
    }

    &--low {
        background: #fef3c7;
        color: #92400e;
    }

    &--out,
    &--inactive {
        background: #fee2e2;
        color: #b91c1c;
    }
}
</style>
