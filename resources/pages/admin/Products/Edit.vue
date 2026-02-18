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
    errors: Object,
})

const {t} = useI18n()

const form = reactive({
    name: props.product.name ?? '',
    weight: props.product.weight ?? null,
    price: props.product.price ?? 0,
    old_price: props.product.old_price ?? null,
    description: props.product.description ?? '',
    image_url: props.product.image_url ?? '',
})

watch(() => props.product, (newVal) => {
    form.name = newVal.name ?? ''
    form.weight = newVal.weight ?? null
    form.price = newVal.price ?? 0
    form.old_price = newVal.old_price ?? null
    form.description = newVal.description ?? ''
    form.image_url = newVal.image_url ?? ''
}, {immediate: true})

const submit = () => {
    const formData = new FormData()
    formData.append('name', String(form.name).trim())
    if (form.weight !== null) formData.append('weight', String(form.weight))
    formData.append('price', String(form.price))
    if (form.old_price !== null) formData.append('old_price', String(form.old_price))
    formData.append('description', form.description || '')
    if (form.image_url.trim()) {
        formData.append('image_url', form.image_url.trim())
    }
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
