<template>
    <div v-if="isOpen" class="modal modal--active">
        <div class="modal__overlay" @click="closeModal"></div>

        <div class="modal__content">
            <button class="modal__close" @click="closeModal">&times;</button>

            <!-- Заголовок DinoJelly -->
            <h1 class="auth-logo">
                <span class="auth-logo__dino">Dino</span>
                <span class="auth-logo__jelly">Jelly</span>
            </h1>

            <!-- Форма входа -->
            <form v-if="activeTab === 'login' || showAuth === true" @submit.prevent="submitLogin" class="auth-form">
                <div class="auth-form__title">{{ t("auth.loginTitle") }}</div>

                <div class="auth-form__group">
                    <input v-model="loginForm.email" type="email" class="auth-form__input"
                           :placeholder="t('auth.email')" required>
                    <div v-if="errors.email" class="auth-form__error">{{ t(errors.email) }}</div>
                </div>

                <div class="auth-form__group">
                    <input v-model="loginForm.password" type="password" class="auth-form__input"
                           :placeholder="t('auth.password')" required>
                    <div v-if="errors.password" class="auth-form__error">{{ t(errors.password) }}</div>
                </div>

                <!-- Ссылка над кнопкой -->
                <p class="auth-form__switch">
                    <a href="#" @click.prevent="switchToRegister">{{ t("auth.register") }}?</a>
                </p>

                <button type="submit" class="auth-form__submit" :disabled="processing">
                    <span v-if="processing">{{ t("auth.wait") }}</span>
                    <span v-else>{{ t("auth.login") }}</span>
                </button>
            </form>

            <!-- Форма регистрации -->
            <form v-else @submit.prevent="submitRegister" class="auth-form">
                <div class="auth-form__title">{{ t("auth.registerTitle") }}</div>

                <div class="auth-form__group">
                    <input v-model="registerForm.name" type="text" class="auth-form__input"
                           :placeholder="t('auth.name')" required>
                    <div v-if="errors.name" class="auth-form__error">{{ t(errors.name) }}</div>
                </div>

                <div class="auth-form__group">
                    <input v-model="registerForm.phone" type="tel" class="auth-form__input"
                           :placeholder="t('auth.phone')" required>
                    <div v-if="errors.phone" class="auth-form__error">{{ t(errors.phone) }}</div>
                </div>

                <div class="auth-form__group">
                    <input v-model="registerForm.email" type="email" class="auth-form__input"
                           :placeholder="t('auth.email')" required>
                    <div v-if="errors.email" class="auth-form__error">{{ t(errors.email) }}</div>
                </div>

                <div class="auth-form__group">
                    <input v-model="registerForm.password" type="password" class="auth-form__input"
                           :placeholder="t('auth.password')" required>
                    <div v-if="errors.password" class="auth-form__error">{{ t(errors.password) }}</div>
                </div>

                <div class="auth-form__group">
                    <input v-model="registerForm.password_confirmation" type="password" class="auth-form__input"
                           :placeholder="t('auth.passwordConfirm')" required>
                </div>

                <!-- Ссылка над кнопкой -->
                <p class="auth-form__switch">
                    <a href="#" @click.prevent="switchToLogin">{{ t("auth.login") }}?</a>
                </p>

                <button type="submit" class="auth-form__submit" :disabled="processing">
                    <span v-if="processing">{{ t("auth.wait") }}</span>
                    <span v-else>{{ t("auth.register") }}</span>
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { useI18n } from '../lang/useI18n'

const { t } = useI18n()
const page = usePage()
let showAuth = ref(false)

import { onMounted, onBeforeUnmount } from 'vue'

onMounted(() => {
    window.addEventListener('openAuthModal', () => {
        showAuth.value = true
    })
})

onBeforeUnmount(() => {
    window.removeEventListener('openAuthModal', () => {
        showAuth.value = true
    })
})

const props = defineProps({
    isOpen: Boolean,
})

const emit = defineEmits(['close'])

const activeTab = ref('login')
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

const closeModal = () => {
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
        preserveScroll: true,
        onSuccess: () => {
            closeModal()
            window.location.reload()
        },
        onFinish: () => {
            processing.value = false
        },
    })
}

const submitRegister = () => {
    processing.value = true
    registerForm.post('/register', {
        preserveScroll: true,
        onSuccess: () => {
            registerForm.reset()
            activeTab.value = 'login'
            window.location.reload()
        },
        onFinish: () => {
            processing.value = false
        },
    })
}
</script>

<style lang="scss">
.modal {
    position: fixed;
    inset: 0;
    z-index: 1000;
    display: none;
    align-items: center;
    justify-content: center;

    &--active {
        display: flex;
    }

    &__overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
    }

    &__content {
        position: relative;
        background: #fff;
        border-radius: 24px;
        padding: 40px 32px;
        width: 100%;
        max-width: 420px;
        z-index: 1001;
    }

    &__close {
        position: absolute;
        top: 20px;
        right: 20px;
        background: #a8ffce;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        border: none;
        cursor: pointer;
        color: #545454;
        transition: 0.2s;

        &:hover {
            background: #8cf7ba;
        }
    }
}

.auth-logo {
    font-size: 26px;
    font-weight: 900;
    margin-bottom: 20px;
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
        margin: 32px 0 28px; // больше отступ снизу
        color: #111;
    }

    &__group {
        margin-bottom: 24px; // больше отступ между полями
        text-align: center;
    }

    &__input {
        display: block;
        max-width: 388px;
        padding: 14px;
        border: 2px solid #333;
        border-radius: 8px;
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
        margin: 16px 0 20px auto; // больше отступ сверху и снизу
        font-size: 12px; // чуть меньше
        text-align: right;
        max-width: 360px;

        a {
            color: #777;
            font-size: 12px;
            text-decoration: underline;
        }
    }

    &__submit {
        display: block;
        width: 100%;
        max-width: 420px;
        margin-top: 24px; // больше отступ сверху
        margin-bottom: 16px; // снизу тоже
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
        max-width: 360px;
        margin-left: auto;
        margin-right: auto;
    }
}


</style>
