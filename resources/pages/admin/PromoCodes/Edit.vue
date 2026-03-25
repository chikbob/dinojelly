<template>
    <div class="entity-form">
        <h1 class="entity-form__title">{{ t("admin.promoCodes.edit") }}</h1>
        <form @submit.prevent="submit" style="width:100%; max-width:100%; min-width:0;">
            <div class="form-grid" style="width:100%; max-width:100%; min-width:0;">
                <div class="form-group"><label for="code">Code</label><input id="code" v-model="form.code" type="text" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" /></div>
                <div class="form-group"><label for="name">{{ t("admin.promoCodes.name") }}</label><input id="name" v-model="form.name" type="text" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" /></div>
            </div>
            <div class="form-grid" style="width:100%; max-width:100%; min-width:0;">
                <div class="form-group"><label for="type">{{ t("admin.promoCodes.type") }}</label><select id="type" v-model="form.type" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;"><option value="fixed">fixed</option><option value="percent">percent</option></select></div>
                <div class="form-group"><label for="value">{{ t("admin.promoCodes.value") }}</label><input id="value" v-model.number="form.value" type="number" min="0" step="0.01" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" /></div>
            </div>
            <div class="form-grid" style="width:100%; max-width:100%; min-width:0;">
                <div class="form-group"><label for="min_order_amount">{{ t("admin.promoCodes.minOrder") }}</label><input id="min_order_amount" v-model.number="form.min_order_amount" type="number" min="0" step="0.01" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" /></div>
                <div class="form-group"><label for="usage_limit">{{ t("admin.promoCodes.usageLimit") }}</label><input id="usage_limit" v-model.number="form.usage_limit" type="number" min="1" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" /></div>
            </div>
            <div class="form-grid" style="width:100%; max-width:100%; min-width:0;">
                <div class="form-group"><label for="starts_at">{{ t("admin.promoCodes.startsAt") }}</label><input id="starts_at" v-model="form.starts_at" type="datetime-local" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" /></div>
                <div class="form-group"><label for="expires_at">{{ t("admin.promoCodes.expiresAt") }}</label><input id="expires_at" v-model="form.expires_at" type="datetime-local" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" /></div>
            </div>
            <label class="checkbox"><input v-model="form.is_active" type="checkbox" /><span>{{ t("admin.promoCodes.active") }}</span></label>
            <button class="btn-submit" type="submit" style="width:100%; max-width:100%; box-sizing:border-box;">{{ t("admin.actions.save") }}</button>
        </form>
    </div>
</template>

<script setup>
import {reactive} from 'vue'
import {router} from '@inertiajs/vue3'
import {route} from 'ziggy-js'
import {useI18n} from '../../../lang/useI18n'
const props = defineProps({ promoCode: Object, errors: Object })
const {t} = useI18n()
const localDateTime = (value) => value ? new Date(value).toISOString().slice(0,16) : ''
const form = reactive({
    code: props.promoCode.code ?? '',
    name: props.promoCode.name ?? '',
    type: props.promoCode.type ?? 'fixed',
    value: props.promoCode.value ?? 0,
    min_order_amount: props.promoCode.min_order_amount ?? null,
    usage_limit: props.promoCode.usage_limit ?? null,
    starts_at: localDateTime(props.promoCode.starts_at),
    expires_at: localDateTime(props.promoCode.expires_at),
    is_active: !!props.promoCode.is_active,
})
const submit = () => router.put(route('admin.promo-codes.update', props.promoCode.id), form)
</script>

<style scoped lang="scss">
.entity-form { max-width: 720px; margin: 0 auto; padding: 40px 20px; font-family: "Press Start 2P", system-ui; min-width: 0; }
.entity-form__title { font-size: 24px; margin-bottom: 24px; }
.form-grid { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 16px; min-width: 0; }
.form-group { margin-bottom: 16px; }
.form-group label { display:block; margin-bottom: 6px; }
.form-group input, .form-group select { width:100%; max-width:100%; min-width:0; box-sizing:border-box; padding:8px; border-radius:6px; border:1px solid #ddd; font-family:inherit; font-size:14px; }
.checkbox { display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-bottom:20px; }
.btn-submit { width:100%; max-width:100%; background:#3ecf8e; color:#fff; padding:10px 20px; border:none; border-radius:6px; font-family:inherit; cursor:pointer; }
@media (max-width: 640px) { .form-grid { grid-template-columns: 1fr; } }
</style>
