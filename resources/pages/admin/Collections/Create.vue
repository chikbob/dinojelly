<template>
    <div class="entity-form">
        <h1 class="entity-form__title">{{ t("admin.collections.create") }}</h1>
        <form @submit.prevent="submit">
            <div class="form-group">
                <label for="name">{{ t("admin.collections.name") }}</label>
                <input id="name" v-model="form.name" type="text" />
            </div>
            <div class="form-group">
                <label for="slug">Slug</label>
                <input id="slug" v-model="form.slug" type="text" />
            </div>
            <div class="form-group">
                <label for="description">{{ t("admin.products.fields.description") }}</label>
                <textarea id="description" v-model="form.description" rows="4"></textarea>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="sort_order">{{ t("admin.categories.sortOrder") }}</label>
                    <input id="sort_order" v-model.number="form.sort_order" type="number" min="0" />
                </div>
                <div class="form-group">
                    <label for="image_url">{{ t("admin.products.fields.imageUrl") }}</label>
                    <input id="image_url" v-model="form.image_url" type="text" />
                </div>
            </div>
            <div class="form-group">
                <label for="image">{{ t("admin.products.fields.image") }}</label>
                <input id="image" type="file" accept="image/*" @change="handleFileChange" />
            </div>
            <label class="checkbox">
                <input v-model="form.is_active" type="checkbox" />
                <span>{{ t("admin.collections.active") }}</span>
            </label>
            <button class="btn-submit" type="submit">{{ t("admin.actions.create") }}</button>
        </form>
    </div>
</template>

<script setup>
import {reactive, ref, watch} from 'vue'
import {router} from '@inertiajs/vue3'
import {route} from 'ziggy-js'
import {useI18n} from '../../../lang/useI18n'
defineProps({ errors: Object })
const {t} = useI18n()
const form = reactive({ name:'', slug:'', description:'', sort_order:0, image_url:'', is_active:true })
const imageFile = ref(null)
watch(() => form.name, (value) => { if (!form.slug) form.slug = value.toLowerCase().trim().replace(/[^a-z0-9а-яё]+/gi, '-').replace(/^-+|-+$/g, '') })
const handleFileChange = (event) => { imageFile.value = event.target.files[0] ?? null }
const submit = () => {
    const fd = new FormData()
    Object.entries(form).forEach(([key,val]) => { if (val !== null && val !== '') fd.append(key, String(val)) })
    fd.set('is_active', form.is_active ? '1' : '0')
    if (imageFile.value) fd.append('image', imageFile.value)
    router.post(route('admin.collections.store'), fd, { forceFormData: true })
}
</script>

<style scoped lang="scss">
.entity-form { max-width: 720px; margin: 0 auto; padding: 40px 20px; font-family: "Press Start 2P", system-ui; }
.entity-form__title { font-size: 24px; margin-bottom: 24px; }
.form-grid { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 16px; }
.form-group { margin-bottom: 16px; }
.form-group label { display:block; margin-bottom: 6px; }
.form-group input, .form-group textarea { width:100%; padding:8px; border-radius:6px; border:1px solid #ddd; font-family:inherit; font-size:14px; }
.checkbox { display:flex; gap:10px; align-items:center; margin-bottom:20px; }
.btn-submit { background:#29cc5f; color:#fff; padding:10px 20px; border:none; border-radius:6px; font-family:inherit; cursor:pointer; }
</style>
