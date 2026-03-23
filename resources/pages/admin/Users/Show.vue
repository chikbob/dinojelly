<template>
    <div class="user-show">
        <h1 class="user-show__title">{{ user.name }}</h1>

        <div class="user-show__card">
            <div class="user-show__row">
                <span>ID</span>
                <strong>{{ user.id }}</strong>
            </div>
            <div class="user-show__row">
                <span>Email</span>
                <strong>{{ user.email }}</strong>
            </div>
            <div class="user-show__row">
                <span>{{ t("profile.phone") }}</span>
                <strong>{{ user.phone || t("profile.notProvided") }}</strong>
            </div>
            <div class="user-show__row">
                <span>{{ t("profile.address") }}</span>
                <strong>{{ user.address || t("profile.notProvided") }}</strong>
            </div>
            <div class="user-show__row">
                <span>Role</span>
                <strong>{{ user.role }}</strong>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useI18n } from '../../../lang/useI18n'

defineProps({
    user: Object,
})

const { t } = useI18n()
</script>

<style scoped lang="scss">
.user-show {
    max-width: 800px;
    margin: 0 auto;
    padding: 40px 20px;

    &__title {
        margin-bottom: 24px;
        font-size: 24px;
    }

    &__card {
        display: grid;
        gap: 12px;
        padding: 24px;
        border-radius: 12px;
        background: #fff;
        border: 1px solid #e5e7eb;
    }

    &__row {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f1f5f9;
    }
}
</style>
