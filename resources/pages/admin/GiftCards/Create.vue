<template>
    <div class="admin-form">
        <h1>{{ t("admin.giftCards.create") }}</h1>
        <form class="admin-form__grid" @submit.prevent="submit" style="width:100%; max-width:100%; min-width:0; overflow:hidden;">
            <input v-model="form.name" :placeholder="t('admin.giftCards.name')" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" />
            <input v-model="form.initial_amount" type="number" min="100" :placeholder="t('admin.giftCards.initialAmount')" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" />
            <input v-model="form.expires_at" type="datetime-local" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" />
            <select v-model="form.recipient_user_id" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;">
                <option :value="null">{{ t("admin.giftCards.noRecipient") }}</option>
                <option v-for="user in users" :key="user.id" :value="user.id">{{ user.email }}</option>
            </select>
            <textarea v-model="form.message" :placeholder="t('admin.giftCards.message')" rows="4" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;"></textarea>
            <label><input v-model="form.is_active" type="checkbox" /> {{ t("admin.giftCards.active") }}</label>
            <button type="submit" style="width:100%; max-width:100%; box-sizing:border-box;">{{ t("admin.actions.create") }}</button>
        </form>
    </div>
</template>

<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from '../../../lang/useI18n'

const props = defineProps({ users: Array })
const { t } = useI18n()

const form = reactive({
    name: '',
    message: '',
    recipient_user_id: null,
    initial_amount: 1000,
    expires_at: '',
    is_active: true,
})

const submit = () => router.post('/admin/gift-cards', form)
</script>

<style scoped lang="scss">
.admin-form { max-width: 820px; margin: 0 auto; min-width: 0; }
.admin-form__grid { display:grid; gap:16px; background:#fff; padding:24px; border-radius:16px; min-width:0; }
.admin-form__grid input, .admin-form__grid textarea, .admin-form__grid select { width:100%; max-width:100%; min-width:0; box-sizing:border-box; padding:12px; border:1px solid #cbd5e1; border-radius:10px; font: inherit; }
.admin-form__grid button { width:100%; max-width:100%; padding:12px 16px; border:none; border-radius:10px; background:#111827; color:#fff; font: inherit; cursor:pointer; }
</style>
