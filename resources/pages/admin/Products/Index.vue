<template>
    <div class="products-index">
        <h1 class="products-index__title">{{ t("admin.products.title") }}</h1>

        <a :href="route('admin.products.create')" class="btn btn-create">
            {{ t("admin.products.createNew") }}
        </a>

        <table class="products-index__table">
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
                    <div class="actions">
                        <a :href="route('admin.products.edit', product.id)" class="btn btn-edit">
                            {{ t("admin.products.edit") }}
                        </a>
                        <button @click="destroy(product.id)" class="btn btn-delete">
                            {{ t("admin.products.delete") }}
                        </button>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>

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
.products-index {
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
            vertical-align: middle;
        }

        th {
            background-color: #f5f7fa;
        }

        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
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
            margin: 0 10px 0 0;
        }

        &-delete {
            background-color: #ef4444;
            border: none;
        }

        &-create {
            background-color: #29cc5f;
            display: inline-block;
            margin: 0 0 18px 0;
        }
    }
}

.actions {
    display: flex;
    align-items: center; /* Вертикальное выравнивание */
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
