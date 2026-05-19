<template>
    <div v-if="isOpen" class="modal modal--active">
        <div class="modal__overlay" @click="closeModal"></div>

        <div class="modal__content">
            <AuthPanel :initial-tab="showAuth ? 'login' : activeTab" :show-close="true" @close="closeModal" />
        </div>
    </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue'
import AuthPanel from './AuthPanel.vue'

const showAuth = ref(false)

const handleOpenAuthModal = () => {
    showAuth.value = true
}

onMounted(() => {
    window.addEventListener('openAuthModal', handleOpenAuthModal)
})

onBeforeUnmount(() => {
    window.removeEventListener('openAuthModal', handleOpenAuthModal)
})

const props = defineProps({
    isOpen: Boolean,
})

const emit = defineEmits(['close'])

const activeTab = ref('login')

const closeModal = () => {
    activeTab.value = 'login'
    showAuth.value = false
    emit('close')
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
        padding: 40px 32px 32px;
        width: 100%;
        max-width: 420px;
        max-height: calc(100dvh - 32px);
        overflow-y: auto;
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
        padding: 0;
        line-height: 1;
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
        margin: 32px 0 28px; // больше отступ снизу
        color: #111;
    }

    &__group {
        margin-bottom: 24px; // больше отступ между полями
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
        margin: 16px 0 20px auto; // больше отступ сверху и снизу
        font-size: 12px; // чуть меньше
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
        max-width: 100%;
        margin-left: auto;
        margin-right: auto;
    }
}

@media (max-width: 640px) {
    .modal {
        padding: 16px;
    }

    .modal__content {
        padding: 24px 18px;
    }

    .modal__close {
        top: 14px;
        right: 14px;
    }
}


</style>
