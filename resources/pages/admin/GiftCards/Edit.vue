<template>
    <div class="admin-form">
        <h1>{{ t("admin.giftCards.edit") }}</h1>
        <form class="admin-form__grid" @submit.prevent="submit">
            <input :value="giftCard.code" disabled />
            <input v-model="form.name" :placeholder="t('admin.giftCards.name')" />
            <input v-model="form.balance" type="number" min="0" step="0.01" :placeholder="t('admin.giftCards.balance')" />
            <input v-model="form.expires_at" type="datetime-local" />
            <select v-model="form.recipient_user_id">
                <option :value="null">{{ t("admin.giftCards.noRecipient") }}</option>
                <option v-for="user in users" :key="user.id" :value="user.id">{{ user.email }}</option>
            </select>
            <textarea v-model="form.message" :placeholder="t('admin.giftCards.message')" rows="4" />
            <label><input v-model="form.is_active" type="checkbox" /> {{ t("admin.giftCards.active") }}</label>
            <button type="submit">{{ t("admin.actions.save") }}</button>
        </form>
    </div>
</template>

<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from '../../../lang/useI18n'

const props = defineProps({ giftCard: Object, users: Array })
const { t } = useI18n()

const toDateTimeLocal = (value) => value ? new Date(value).toISOString().slice(0, 16) : ''

const form = reactive({
    name: props.giftCard.name,
    message: props.giftCard.message ?? '',
    recipient_user_id: props.giftCard.recipient_user_id ?? null,
    balance: props.giftCard.balance,
    expires_at: toDateTimeLocal(props.giftCard.expires_at),
    is_active: !!props.giftCard.is_active,
})

const submit = () => router.put(`/admin/gift-cards/${props.giftCard.id}`, form)
</script>

<style scoped lang="scss">
.admin-form { max-width: 820px; margin: 0 auto; }
.admin-form__grid { display:grid; gap:16px; background:#fff; padding:24px; border-radius:16px; }
.admin-form__grid input, .admin-form__grid textarea, .admin-form__grid select { padding:12px; border:1px solid #cbd5e1; border-radius:10px; font: inherit; }
.admin-form__grid button { padding:12px 16px; border:none; border-radius:10px; background:#111827; color:#fff; font: inherit; cursor:pointer; }
</style>
