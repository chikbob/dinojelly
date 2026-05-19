<template>
    <div class="auth-page">
        <div class="auth-page__card auth-page__card--wide">
            <AuthPanel initial-tab="register" :standalone="true" :show-back="true" />
        </div>
    </div>
</template>

<script setup>
import AuthPanel from '../../components/AuthPanel.vue'
</script>

<style scoped lang="scss">
.auth-page {
    min-height: 100dvh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px 16px;
    background: linear-gradient(180deg, #f7fdf8 0%, #eef9f1 100%);
}

.auth-page__card {
    width: min(100%, 460px);
    max-width: 100%;
    padding: 32px;
    border-radius: 24px;
    background: #fff;
    box-shadow: 0 18px 50px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

@media (max-width: 640px) {
    .auth-page {
        padding: 16px;
        align-items: flex-start;
    }

    .auth-page__card {
        padding: 24px 18px;
        margin-top: 16px;
    }
}
</style>
