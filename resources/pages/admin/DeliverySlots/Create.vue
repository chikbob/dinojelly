<template>
    <div class="entity-form">
        <h1 class="entity-form__title">{{ t("admin.deliverySlots.create") }}</h1>
        <form @submit.prevent="submit" style="width:100%; max-width:100%; min-width:0;">
            <div class="form-group">
                <label for="name">{{ t("admin.deliverySlots.name") }}</label>
                <input id="name" v-model="form.name" type="text" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" />
                <p v-if="errors.name" class="error">{{ errors.name }}</p>
            </div>
            <div class="form-grid" style="width:100%; max-width:100%; min-width:0;">
                <div class="form-group">
                    <label for="starts_at">{{ t("admin.deliverySlots.startsAt") }}</label>
                    <input id="starts_at" v-model="form.starts_at" type="datetime-local" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" />
                </div>
                <div class="form-group">
                    <label for="ends_at">{{ t("admin.deliverySlots.endsAt") }}</label>
                    <input id="ends_at" v-model="form.ends_at" type="datetime-local" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" />
                </div>
            </div>
            <div class="form-grid" style="width:100%; max-width:100%; min-width:0;">
                <div class="form-group">
                    <label for="capacity">{{ t("admin.deliverySlots.capacity") }}</label>
                    <input id="capacity" v-model.number="form.capacity" type="number" min="1" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" />
                </div>
                <div class="form-group">
                    <label for="price">{{ t("admin.deliverySlots.price") }}</label>
                    <input id="price" v-model.number="form.price" type="number" step="0.01" min="0" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" />
                </div>
            </div>
            <label class="checkbox">
                <input v-model="form.is_active" type="checkbox" />
                <span>{{ t("admin.deliverySlots.active") }}</span>
            </label>
            <button class="btn-submit" type="submit" style="width:100%; max-width:100%; box-sizing:border-box;">{{ t("admin.actions.create") }}</button>
        </form>
    </div>
</template>

<script setup>
import {reactive} from 'vue'
import {router} from '@inertiajs/vue3'
import {route} from 'ziggy-js'
import {useI18n} from '../../../lang/useI18n'
defineProps({ errors: Object })
const {t} = useI18n()
const form = reactive({ name: '', starts_at: '', ends_at: '', capacity: null, price: 0, is_active: true })
const submit = () => router.post(route('admin.delivery-slots.store'), form)
</script>

<style scoped lang="scss">
.entity-form { max-width: 720px; margin: 0 auto; padding: 40px 20px; font-family: "Press Start 2P", system-ui; min-width: 0; }
.entity-form__title { font-size: 24px; margin-bottom: 24px; }
.form-grid { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 16px; min-width: 0; }
.form-group { margin-bottom: 16px; }
.form-group label { display:block; margin-bottom: 6px; }
.form-group input, .form-group textarea, .form-group select { width:100%; max-width:100%; min-width:0; box-sizing:border-box; padding:8px; border-radius:6px; border:1px solid #ddd; font-family:inherit; font-size:14px; }
.checkbox { display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-bottom:20px; }
.btn-submit { width:100%; max-width:100%; background:#29cc5f; color:#fff; padding:10px 20px; border:none; border-radius:6px; font-family:inherit; cursor:pointer; }
.error { margin-top:4px; color:#ef4444; font-size:12px; }
@media (max-width: 640px) { .form-grid { grid-template-columns: 1fr; } }
</style>
