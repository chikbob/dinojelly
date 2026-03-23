<template>
    <div class="products-edit">
        <h1 class="products-edit__title">{{ t("admin.products.editProduct") }}</h1>

        <form @submit.prevent="submit" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">{{ t("admin.products.fields.name") }}</label>
                <input v-model="form.name" id="name" type="text"/>
                <p v-if="errors.name" class="error">{{ errors.name }}</p>
            </div>

            <div class="form-group">
                <label for="category_id">{{ t("admin.products.fields.category") }} ({{ t("admin.products.optional") }})</label>
                <select v-model="form.category_id" id="category_id">
                    <option :value="null">—</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                    </option>
                </select>
                <p v-if="errors.category_id" class="error">{{ errors.category_id }}</p>
            </div>

            <div class="form-group">
                <label for="weight">{{ t("admin.products.fields.weight") }} ({{ t("admin.products.optional") }})</label>
                <input v-model.number="form.weight" id="weight" type="number" min="0"/>
                <p v-if="errors.weight" class="error">{{ errors.weight }}</p>
            </div>

            <div class="form-group">
                <label for="price">{{ t("admin.products.fields.price") }}</label>
                <input v-model.number="form.price" id="price" type="number" step="0.01"/>
                <p v-if="errors.price" class="error">{{ errors.price }}</p>
            </div>

            <div class="form-group">
                <label for="old_price">{{ t("admin.products.fields.oldPrice") }} ({{
                        t("admin.products.optional")
                    }})</label>
                <input v-model.number="form.old_price" id="old_price" type="number" step="0.01"/>
                <p v-if="errors.old_price" class="error">{{ errors.old_price }}</p>
            </div>

            <div class="form-group">
                <label for="description">{{ t("admin.products.fields.description") }} ({{
                        t("admin.products.optional")
                    }})</label>
                <textarea v-model="form.description" id="description" rows="4"></textarea>
                <p v-if="errors.description" class="error">{{ errors.description }}</p>
            </div>

            <div class="form-group">
                <label for="sku">{{ t("admin.products.fields.sku") }}</label>
                <input v-model="form.sku" id="sku" type="text"/>
                <p v-if="errors.sku" class="error">{{ errors.sku }}</p>
            </div>

            <div class="form-group">
                <label for="stock_quantity">{{ t("admin.products.fields.stockQuantity") }}</label>
                <input v-model.number="form.stock_quantity" id="stock_quantity" type="number" min="0"/>
                <p v-if="errors.stock_quantity" class="error">{{ errors.stock_quantity }}</p>
            </div>

            <div class="form-group">
                <label for="low_stock_threshold">{{ t("admin.products.fields.lowStockThreshold") }}</label>
                <input v-model.number="form.low_stock_threshold" id="low_stock_threshold" type="number" min="0"/>
                <p v-if="errors.low_stock_threshold" class="error">{{ errors.low_stock_threshold }}</p>
            </div>

            <div class="form-group form-group--checkbox">
                <label>
                    <input v-model="form.stock_is_active" type="checkbox"/>
                    {{ t("admin.products.fields.stockActive") }}
                </label>
            </div>

            <div class="form-group">
                <label for="image_url">{{ t("admin.products.fields.imageUrl") }} ({{
                        t("admin.products.optional")
                    }})</label>
                <input v-model="form.image_url" id="image_url" type="text" placeholder="https://example.com/image.jpg"/>
                <p v-if="errors.image_url" class="error">{{ errors.image_url }}</p>
            </div>

            <div v-if="form.image_url" class="image-preview">
                <img :src="form.image_url" :alt="form.name"/>
            </div>

            <button type="submit" class="btn-submit">{{ t("admin.products.save") }}</button>
        </form>
    </div>
</template>

<script setup>
import {reactive, watch} from 'vue'
import {router} from '@inertiajs/vue3'
import {route} from 'ziggy-js'
import {useI18n} from '../../../lang/useI18n'

const props = defineProps({
    product: Object,
    categories: Array,
    errors: Object,
})

const {t} = useI18n()

const form = reactive({
    category_id: props.product.category_id ?? null,
    name: props.product.name ?? '',
    weight: props.product.weight ?? null,
    price: props.product.price ?? 0,
    old_price: props.product.old_price ?? null,
    description: props.product.description ?? '',
    image_url: props.product.image_url ?? '',
    sku: props.product.stock_item?.sku ?? `SKU-${props.product.id}`,
    stock_quantity: props.product.stock_item?.quantity ?? 0,
    low_stock_threshold: props.product.stock_item?.low_stock_threshold ?? 5,
    stock_is_active: props.product.stock_item?.is_active ?? true,
})

watch(() => props.product, (newVal) => {
    form.name = newVal.name ?? ''
    form.category_id = newVal.category_id ?? null
    form.weight = newVal.weight ?? null
    form.price = newVal.price ?? 0
    form.old_price = newVal.old_price ?? null
    form.description = newVal.description ?? ''
    form.image_url = newVal.image_url ?? ''
    form.sku = newVal.stock_item?.sku ?? `SKU-${newVal.id}`
    form.stock_quantity = newVal.stock_item?.quantity ?? 0
    form.low_stock_threshold = newVal.stock_item?.low_stock_threshold ?? 5
    form.stock_is_active = newVal.stock_item?.is_active ?? true
}, {immediate: true})

const submit = () => {
    const formData = new FormData()
    if (form.category_id !== null) formData.append('category_id', String(form.category_id))
    formData.append('name', String(form.name).trim())
    if (form.weight !== null) formData.append('weight', String(form.weight))
    formData.append('price', String(form.price))
    if (form.old_price !== null) formData.append('old_price', String(form.old_price))
    formData.append('description', form.description || '')
    if (form.image_url.trim()) {
        formData.append('image_url', form.image_url.trim())
    }
    formData.append('sku', form.sku)
    formData.append('stock_quantity', String(form.stock_quantity))
    if (form.low_stock_threshold !== null) formData.append('low_stock_threshold', String(form.low_stock_threshold))
    formData.append('stock_is_active', form.stock_is_active ? '1' : '0')
    formData.append('_method', 'PUT')

    router.post(route('admin.products.update', props.product.id), formData, {
        forceFormData: true,
    })
}
</script>

<style scoped lang="scss">
.products-edit {
    max-width: 600px;
    margin: 0 auto;
    padding: 40px 20px;
    font-family: 'Press Start 2P', system-ui;

    &__title {
        font-size: 24px;
        margin-bottom: 24px;
    }

    .image-preview {
        margin: 10px 0 15px;

        img {
            max-width: 150px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
    }

    .form-group {
        margin-bottom: 16px;

        label {
            display: block;
            margin-bottom: 6px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-family: 'Press Start 2P', system-ui;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
        }

        .error {
            margin-top: 4px;
            color: #ef4444;
            font-size: 12px;
        }
    }

    .btn-submit {
        background-color: #3ecf8e;
        color: white;
        padding: 10px 20px;
        border-radius: 6px;
        font-family: 'Press Start 2P', system-ui;
        font-weight: bold;
        cursor: pointer;
        border: none;
    }
}
</style>
