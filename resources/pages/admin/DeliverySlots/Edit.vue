<template>
    <div class="entity-form">
        <h1 class="entity-form__title">{{ t("admin.deliverySlots.edit") }}</h1>
        <form @submit.prevent="submit">
            <div class="form-group">
                <label for="name">{{ t("admin.deliverySlots.name") }}</label>
                <input id="name" v-model="form.name" type="text" />
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="starts_at">{{ t("admin.deliverySlots.startsAt") }}</label>
                    <input id="starts_at" v-model="form.starts_at" type="datetime-local" />
                </div>
                <div class="form-group">
                    <label for="ends_at">{{ t("admin.deliverySlots.endsAt") }}</label>
                    <input id="ends_at" v-model="form.ends_at" type="datetime-local" />
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="capacity">{{ t("admin.deliverySlots.capacity") }}</label>
                    <input id="capacity" v-model.number="form.capacity" type="number" min="1" />
                </div>
                <div class="form-group">
                    <label for="price">{{ t("admin.deliverySlots.price") }}</label>
                    <input id="price" v-model.number="form.price" type="number" step="0.01" min="0" />
                </div>
            </div>
            <label class="checkbox">
                <input v-model="form.is_active" type="checkbox" />
                <span>{{ t("admin.deliverySlots.active") }}</span>
            </label>
            <button class="btn-submit" type="submit">{{ t("admin.actions.save") }}</button>
        </form>
    </div>
</template>

<script setup>
import {reactive} from 'vue'
import {router} from '@inertiajs/vue3'
import {route} from 'ziggy-js'
import {useI18n} from '../../../lang/useI18n'
const props = defineProps({ slot: Object, errors: Object })
const {t} = useI18n()
const localDateTime = (value) => value ? new Date(value).toISOString().slice(0,16) : ''
const form = reactive({
    name: props.slot.name ?? '',
    starts_at: localDateTime(props.slot.starts_at),
    ends_at: localDateTime(props.slot.ends_at),
    capacity: props.slot.capacity ?? null,
    price: props.slot.price ?? 0,
    is_active: !!props.slot.is_active,
})
const submit = () => router.put(route('admin.delivery-slots.update', props.slot.id), form)
</script>

<style scoped lang="scss">
.entity-form { max-width: 720px; margin: 0 auto; padding: 40px 20px; font-family: "Press Start 2P", system-ui; }
.entity-form__title { font-size: 24px; margin-bottom: 24px; }
.form-grid { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 16px; }
.form-group { margin-bottom: 16px; }
.form-group label { display:block; margin-bottom: 6px; }
.form-group input { width:100%; padding:8px; border-radius:6px; border:1px solid #ddd; font-family:inherit; font-size:14px; }
.checkbox { display:flex; gap:10px; align-items:center; margin-bottom:20px; }
.btn-submit { background:#3ecf8e; color:#fff; padding:10px 20px; border:none; border-radius:6px; font-family:inherit; cursor:pointer; }
</style>
