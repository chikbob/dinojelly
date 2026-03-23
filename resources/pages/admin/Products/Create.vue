<template>
    <div class="products-create">
        <h1 class="products-create__title">{{ t("admin.products.createProduct") }}</h1>

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
                <label for="image">{{ t("admin.products.fields.image") }} ({{ t("admin.products.optional") }})</label>
                <input @change="handleFileChange" id="image" type="file" accept="image/*"/>
                <p v-if="errors.image" class="error">{{ errors.image }}</p>
            </div>

            <button type="submit" class="btn-submit">{{ t("admin.products.create") }}</button>
        </form>
    </div>
</template>

<script setup>
import {reactive, ref} from 'vue'
import {router} from '@inertiajs/vue3'
import {route} from 'ziggy-js'
import {useI18n} from '../../../lang/useI18n'

const props = defineProps({
    errors: Object,
    categories: Array,
})

const {t} = useI18n()

const form = reactive({
    category_id: null,
    name: '',
    weight: null,
    price: 0,
    old_price: null,
    description: '',
})

const imageFile = ref(null)

const handleFileChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        imageFile.value = file
    }
}

const submit = () => {
    const formData = new FormData()
    if (form.category_id !== null) formData.append('category_id', String(form.category_id))
    formData.append('name', form.name)
    if (form.weight !== null) formData.append('weight', form.weight)
    formData.append('price', form.price)
    if (form.old_price !== null) formData.append('old_price', form.old_price)
    if (form.description) formData.append('description', form.description)
    if (imageFile.value) formData.append('image', imageFile.value)

    router.post(route('admin.products.store'), formData, {
        forceFormData: true,
    })
}
</script>

<style scoped lang="scss">
.products-create {
    max-width: 600px;
    margin: 0 auto;
    padding: 40px 20px;
    font-family: "Press Start 2P", system-ui;

    &__title {
        font-size: 24px;
        margin-bottom: 24px;
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
            font-family: "Press Start 2P", system-ui;
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
        background-color: #29cc5f;
        color: white;
        padding: 10px 20px;
        border-radius: 6px;
        font-family: "Press Start 2P", system-ui;
        font-weight: bold;
        cursor: pointer;
        border: none;
    }
}
</style>
