<template>
    <div class="entity-form">
        <h1 class="entity-form__title">{{ t("admin.collections.edit") }}</h1>
        <form @submit.prevent="submit" style="width:100%; max-width:100%; min-width:0;">
            <div class="form-group"><label for="name">{{ t("admin.collections.name") }}</label><input id="name" v-model="form.name" type="text" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" /></div>
            <div class="form-group"><label for="slug">Slug</label><input id="slug" v-model="form.slug" type="text" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" /></div>
            <div class="form-group"><label for="description">{{ t("admin.products.fields.description") }}</label><textarea id="description" v-model="form.description" rows="4" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;"></textarea></div>
            <div class="form-grid" style="width:100%; max-width:100%; min-width:0;">
                <div class="form-group"><label for="sort_order">{{ t("admin.categories.sortOrder") }}</label><input id="sort_order" v-model.number="form.sort_order" type="number" min="0" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" /></div>
                <div class="form-group"><label for="image_url">{{ t("admin.products.fields.imageUrl") }}</label><input id="image_url" v-model="form.image_url" type="text" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" /></div>
            </div>
            <div class="form-group"><label for="image">{{ t("admin.products.fields.image") }}</label><input id="image" type="file" accept="image/*" @change="handleFileChange" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" /></div>
            <label class="checkbox"><input v-model="form.is_active" type="checkbox" /><span>{{ t("admin.collections.active") }}</span></label>
            <button class="btn-submit" type="submit" style="width:100%; max-width:100%; box-sizing:border-box;">{{ t("admin.actions.save") }}</button>
        </form>
    </div>
</template>

<script setup>
import {reactive, ref} from 'vue'
import {router} from '@inertiajs/vue3'
import {route} from 'ziggy-js'
import {useI18n} from '../../../lang/useI18n'
const props = defineProps({ collection: Object, errors: Object })
const {t} = useI18n()
const imageFile = ref(null)
const form = reactive({
    name: props.collection.name ?? '',
    slug: props.collection.slug ?? '',
    description: props.collection.description ?? '',
    sort_order: props.collection.sort_order ?? 0,
    image_url: props.collection.image_url ?? '',
    is_active: !!props.collection.is_active,
})
const handleFileChange = (event) => { imageFile.value = event.target.files[0] ?? null }
const submit = () => {
    const fd = new FormData()
    Object.entries(form).forEach(([key,val]) => { if (val !== null && val !== '') fd.append(key, String(val)) })
    fd.set('is_active', form.is_active ? '1' : '0')
    fd.append('_method', 'PUT')
    if (imageFile.value) fd.append('image', imageFile.value)
    router.post(route('admin.collections.update', props.collection.id), fd, { forceFormData: true })
}
</script>

<style scoped lang="scss">
.entity-form { max-width: 720px; margin: 0 auto; padding: 40px 20px; font-family: "Press Start 2P", system-ui; min-width: 0; }
.entity-form__title { font-size: 24px; margin-bottom: 24px; }
.form-grid { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 16px; min-width: 0; }
.form-group { margin-bottom: 16px; }
.form-group label { display:block; margin-bottom: 6px; }
.form-group input, .form-group textarea { width:100%; max-width:100%; min-width:0; box-sizing:border-box; padding:8px; border-radius:6px; border:1px solid #ddd; font-family:inherit; font-size:14px; }
.checkbox { display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-bottom:20px; }
.btn-submit { width:100%; max-width:100%; background:#3ecf8e; color:#fff; padding:10px 20px; border:none; border-radius:6px; font-family:inherit; cursor:pointer; }
@media (max-width: 640px) { .form-grid { grid-template-columns: 1fr; } }
</style>
