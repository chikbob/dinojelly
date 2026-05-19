<template>
    <div class="auth-panel">
        <button v-if="showClose" class="modal__close" type="button" @click="closePanel">&times;</button>

        <Link v-if="showBack" href="/" class="auth-panel__back">
            <span class="auth-panel__back-arrow">←</span>
            <span>{{ backLabel }}</span>
        </Link>

        <h1 class="auth-logo">
            <span class="auth-logo__dino">Dino</span>
            <span class="auth-logo__jelly">Jelly</span>
        </h1>

        <form
            v-if="activeTab === 'login'"
            class="auth-form"
            @submit.prevent="submitLogin"
        >
            <div class="auth-form__title">{{ t('auth.loginTitle') }}</div>

            <div class="auth-form__group">
                <input
                    v-model="loginForm.email"
                    type="email"
                    class="auth-form__input"
                    :placeholder="t('auth.email')"
                    required
                >
                <div v-if="errors.email" class="auth-form__error">{{ t(errors.email) }}</div>
            </div>

            <div class="auth-form__group">
                <input
                    v-model="loginForm.password"
                    type="password"
                    class="auth-form__input"
                    :placeholder="t('auth.password')"
                    required
                >
                <div v-if="errors.password" class="auth-form__error">{{ t(errors.password) }}</div>
            </div>

            <p class="auth-form__switch">
                <template v-if="standalone">
                    <Link href="/register">{{ t('auth.register') }}?</Link>
                </template>
                <template v-else>
                    <a href="#" @click.prevent="switchToRegister">{{ t('auth.register') }}?</a>
                </template>
            </p>

            <button type="submit" class="auth-form__submit" :disabled="processing">
                <span v-if="processing">{{ t('auth.wait') }}</span>
                <span v-else>{{ t('auth.login') }}</span>
            </button>
        </form>

        <form
            v-else
            class="auth-form"
            @submit.prevent="submitRegister"
        >
            <div class="auth-form__title">{{ t('auth.registerTitle') }}</div>

            <div class="auth-form__group">
                <input
                    v-model="registerForm.name"
                    type="text"
                    class="auth-form__input"
                    :placeholder="t('auth.name')"
                    required
                >
                <div v-if="errors.name" class="auth-form__error">{{ t(errors.name) }}</div>
            </div>

            <div class="auth-form__group">
                <input
                    v-model="registerForm.phone"
                    type="tel"
                    class="auth-form__input"
                    :placeholder="t('auth.phone')"
                    required
                >
                <div v-if="errors.phone" class="auth-form__error">{{ t(errors.phone) }}</div>
            </div>

            <div class="auth-form__group">
                <input
                    v-model="registerForm.email"
                    type="email"
                    class="auth-form__input"
                    :placeholder="t('auth.email')"
                    required
                >
                <div v-if="errors.email" class="auth-form__error">{{ t(errors.email) }}</div>
            </div>

            <div class="auth-form__group">
                <input
                    v-model="registerForm.password"
                    type="password"
                    class="auth-form__input"
                    :placeholder="t('auth.password')"
                    required
                >
                <div v-if="errors.password" class="auth-form__error">{{ t(errors.password) }}</div>
            </div>

            <div class="auth-form__group">
                <input
                    v-model="registerForm.password_confirmation"
                    type="password"
                    class="auth-form__input"
                    :placeholder="t('auth.passwordConfirm')"
                    required
                >
            </div>

            <p class="auth-form__switch">
                <template v-if="standalone">
                    <Link href="/login">{{ t('auth.login') }}?</Link>
                </template>
                <template v-else>
                    <a href="#" @click.prevent="switchToLogin">{{ t('auth.login') }}?</a>
                </template>
            </p>

            <button type="submit" class="auth-form__submit" :disabled="processing">
                <span v-if="processing">{{ t('auth.wait') }}</span>
                <span v-else>{{ t('auth.register') }}</span>
            </button>
        </form>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { useI18n } from '../lang/useI18n'

const { t } = useI18n()

const props = defineProps({
    initialTab: {
        type: String,
        default: 'login',
    },
    standalone: {
        type: Boolean,
        default: false,
    },
    showClose: {
        type: Boolean,
        default: false,
    },
    showBack: {
        type: Boolean,
        default: false,
    },
    backLabel: {
        type: String,
        default: 'Назад',
    },
})

const emit = defineEmits(['close'])

const activeTab = ref(props.initialTab)
const processing = ref(false)
const errors = computed(() => usePage().props.errors || {})

const loginForm = useForm({
    email: '',
    password: '',
})

const registerForm = useForm({
    name: '',
    phone: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const closePanel = () => {
    loginForm.reset()
    registerForm.reset()
    emit('close')
}

const switchToRegister = () => {
    activeTab.value = 'register'
    loginForm.reset()
}

const switchToLogin = () => {
    activeTab.value = 'login'
    registerForm.reset()
}

const submitLogin = () => {
    processing.value = true
    loginForm.post('/login', {
        preserveScroll: !props.standalone,
        onSuccess: () => {
            if (!props.standalone) {
                closePanel()
                window.location.reload()
            }
        },
        onFinish: () => {
            processing.value = false
        },
    })
}

const submitRegister = () => {
    processing.value = true
    registerForm.post('/register', {
        preserveScroll: !props.standalone,
        onSuccess: () => {
            if (!props.standalone) {
                registerForm.reset()
                activeTab.value = 'login'
                window.location.reload()
            }
        },
        onFinish: () => {
            processing.value = false
        },
    })
}
</script>

<style scoped lang="scss">
.auth-panel__back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 20px;
    color: #111827;
    text-decoration: none;
    font-size: 10px;
}

.auth-panel__back-arrow {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 999px;
    background: #ecfdf5;
    color: #047857;
}

.auth-logo {
    font-size: 26px;
    font-weight: 900;
    margin: 0 0 20px;
    text-align: left;

    &__dino {
        color: #A8E62E;
    }

    &__jelly {
        color: #29CC5F;
    }
}

.auth-form {
    text-align: left;

    &__title {
        font-size: 22px;
        font-weight: 700;
        margin: 32px 0 28px;
        color: #111;
    }

    &__group {
        margin-bottom: 24px;
        text-align: center;
    }

    &__input {
        display: block;
        width: 100%;
        min-width: 0;
        max-width: 100%;
        padding: 14px;
        border: 2px solid #333;
        border-radius: 8px;
        box-sizing: border-box;
        font-size: 16px;
        font-family: inherit;
        background: #fff;
        color: #111;

        &::placeholder {
            color: #888;
        }

        &:focus {
            outline: none;
            border-color: #3acb6d;
        }
    }

    &__switch {
        margin: 16px 0 20px auto;
        font-size: 12px;
        text-align: right;
        max-width: 100%;

        a {
            color: #777;
            font-size: 12px;
            text-decoration: underline;
        }
    }

    &__submit {
        display: block;
        width: 100%;
        margin-top: 24px;
        margin-bottom: 16px;
        padding: 16px;
        background: #3acb6d;
        color: #fff;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 600;
        font-family: inherit;
        cursor: pointer;
        transition: 0.2s;

        &:hover:not(:disabled) {
            background: #2faa57;
        }

        &:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
    }

    &__error {
        margin-top: 8px;
        padding: 8px;
        background: #ffe1e1;
        color: #b90000;
        border-radius: 4px;
        font-size: 13px;
        max-width: 100%;
        margin-left: auto;
        margin-right: auto;
    }
}
</style>
