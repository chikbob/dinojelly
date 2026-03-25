<template>
    <div class="auth-page">
        <div class="auth-card" style="width:100%; max-width:420px; box-sizing:border-box; overflow:hidden;">
            <h1 class="auth-card__title">{{ t("auth.loginTitle") }}</h1>

            <form class="auth-form" @submit.prevent="submit" style="width:100%; max-width:100%; min-width:0;">
                <label class="auth-form__label" style="display:grid; width:100%; max-width:100%; min-width:0;">
                    <span>{{ t("auth.email") }}</span>
                    <input v-model="form.email" type="email" required style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" />
                </label>
                <p v-if="form.errors.email" class="auth-form__error">{{ form.errors.email }}</p>

                <label class="auth-form__label" style="display:grid; width:100%; max-width:100%; min-width:0;">
                    <span>{{ t("auth.password") }}</span>
                    <input v-model="form.password" type="password" required style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" />
                </label>
                <p v-if="form.errors.password" class="auth-form__error">{{ form.errors.password }}</p>

                <button class="auth-form__submit" type="submit" :disabled="form.processing" style="width:100%; max-width:100%; box-sizing:border-box;">
                    {{ form.processing ? t("auth.wait") : t("auth.login") }}
                </button>
            </form>

            <Link class="auth-card__link" href="/register">
                {{ t("auth.register") }}
            </Link>
        </div>
    </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import { useI18n } from '../../lang/useI18n'

const { t } = useI18n()

const form = useForm({
    email: '',
    password: '',
})

const submit = () => {
    form.post('/login')
}
</script>

<style scoped lang="scss">
.auth-page {
    min-height: 100dvh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    background: linear-gradient(180deg, #f7fdf8 0%, #eef9f1 100%);
}

.auth-card {
    width: min(100%, 420px);
    max-width: 100%;
    padding: 32px;
    border-radius: 20px;
    background: #fff;
    box-shadow: 0 18px 50px rgba(0, 0, 0, 0.08);

    &__title {
        margin: 0 0 24px;
        font-size: 24px;
        text-align: center;
    }

    &__link {
        display: block;
        margin-top: 16px;
        text-align: center;
        color: #29cc5f;
        text-decoration: none;
    }
}

.auth-form {
    display: grid;
    gap: 14px;
    min-width: 0;

    &__label {
        display: grid;
        gap: 8px;
        font-size: 12px;
        min-width: 0;
    }

    input {
        display: block;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        box-sizing: border-box;
        padding: 12px;
        border: 1px solid #d8e4da;
        border-radius: 10px;
        font: inherit;
    }

    &__submit {
        margin-top: 8px;
        width: 100%;
        max-width: 100%;
        padding: 12px;
        border: none;
        border-radius: 10px;
        background: #29cc5f;
        color: #fff;
        cursor: pointer;
        font: inherit;
    }

    &__error {
        margin: -6px 0 0;
        color: #dc2626;
        font-size: 11px;
    }
}

@media (max-width: 640px) {
    .auth-card {
        padding: 24px 18px;
    }
}
</style>
