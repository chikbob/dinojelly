<template>
    <div class="category-form">
        <h1 class="category-form__title">{{ t("admin.categories.createNew") }}</h1>

        <form @submit.prevent="submit">
            <div class="form-group">
                <label for="name">{{ t("admin.categories.name") }}</label>
                <input id="name" v-model="form.name" type="text" />
                <p v-if="errors.name" class="error">{{ errors.name }}</p>
            </div>

            <div class="form-group">
                <label for="slug">{{ t("admin.categories.slug") }}</label>
                <input id="slug" v-model="form.slug" type="text" />
                <p v-if="errors.slug" class="error">{{ errors.slug }}</p>
            </div>

            <div class="form-group">
                <label for="description">{{ t("admin.products.fields.description") }}</label>
                <textarea id="description" v-model="form.description" rows="4"></textarea>
            </div>

            <div class="form-group">
                <label for="sort_order">{{ t("admin.categories.sortOrder") }}</label>
                <input id="sort_order" v-model.number="form.sort_order" type="number" min="0" />
            </div>

            <label class="checkbox">
                <input v-model="form.is_active" type="checkbox" />
                <span>{{ t("admin.categories.active") }}</span>
            </label>

            <button type="submit" class="btn-submit">{{ t("admin.products.create") }}</button>
        </form>
    </div>
</template>

<script setup>
import { reactive, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { useI18n } from '../../../lang/useI18n'

const props = defineProps({
    errors: Object,
})

const { t } = useI18n()

const form = reactive({
    name: '',
    slug: '',
    description: '',
    sort_order: 0,
    is_active: true,
})

watch(() => form.name, (value) => {
    if (!form.slug) {
        form.slug = value
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9а-яё]+/gi, '-')
            .replace(/^-+|-+$/g, '')
    }
})

const submit = () => {
    router.post(route('admin.categories.store'), form)
}
</script>

<style scoped lang="scss">
.category-form {
    max-width: 600px;
    margin: 0 auto;
    padding: 40px 20px;
    font-family: "Press Start 2P", system-ui;

    &__title {
        font-size: 24px;
        margin-bottom: 24px;
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
        font-family: inherit;
        font-size: 14px;
    }

    .error {
        margin-top: 4px;
        color: #ef4444;
        font-size: 12px;
    }
}

.checkbox {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 20px;
}

.btn-submit {
    background-color: #29cc5f;
    color: white;
    padding: 10px 20px;
    border-radius: 6px;
    font-family: inherit;
    font-weight: bold;
    cursor: pointer;
    border: none;
}
</style>
