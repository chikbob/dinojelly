<template>
    <MainLayout>
        <div class="profile">
            <h1 class="profile__title">{{ t("profile.title") }}</h1>

            <div class="profile-card">
                <div class="profile-card__header">
                    <h2 class="profile-card__name">{{ user.name }}</h2>
                    <p
                        class="profile-card__status"
                        :class="{
                            'profile-card__status--verified': user.email_verified_at,
                            'profile-card__status--not-verified': !user.email_verified_at
                        }"
                    >
                        {{ user.email_verified_at ? t("profile.verified") : t("profile.notVerified") }}
                    </p>
                </div>

                <div class="profile-card__body" v-if="!isEditing">
                    <div class="profile-info">
                        <div class="profile-info__item">
                            <span class="profile-info__label">{{ t("profile.email") }}:</span>
                            <span class="profile-info__value">{{ user.email }}</span>
                        </div>

                        <div class="profile-info__item">
                            <span class="profile-info__label">{{ t("profile.phone") }}:</span>
                            <span class="profile-info__value">{{ formatPhoneNumber(user.phone) }}</span>
                        </div>

                        <div class="profile-info__item" v-if="user.address">
                            <span class="profile-info__label">{{ t("profile.address") }}:</span>
                            <span class="profile-info__value">{{ user.address }}</span>
                        </div>

                        <div class="profile-info__item">
                            <span class="profile-info__label">{{ t("profile.registeredAt") }}:</span>
                            <span class="profile-info__value">{{ formatDate(user.created_at) }}</span>
                        </div>
                    </div>
                </div>

                <!-- EDIT FORM -->
                <div v-else class="profile-edit">
                    <h2 class="profile-edit__title">{{ t("profile.edit") }}</h2>

                    <form @submit.prevent="submit">
                        <div class="form-group">
                            <label>{{ t("profile.name") }}</label>
                            <input v-model="form.name" type="text"/>
                        </div>

                        <div class="form-group">
                            <label>{{ t("profile.phone") }}</label>
                            <input v-model="form.phone" type="text"/>
                        </div>

                        <div class="form-group">
                            <label>{{ t("profile.address") }}</label>
                            <textarea v-model="form.address" rows="3"></textarea>
                        </div>

                        <div class="profile-edit__actions">
                            <button type="submit" class="btn-save">
                                {{ t("profile.save") }}
                            </button>

                            <button
                                type="button"
                                class="btn-cancel"
                                @click="cancelEdit"
                            >
                                {{ t("profile.cancel") }}
                            </button>
                        </div>
                    </form>
                </div>

                <div class="profile-card__footer" v-if="!isEditing">
                    <button
                        class="profile-card__button"
                        @click="startEdit"
                    >
                        {{ t("profile.edit") }}
                    </button>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '../../layouts/mainLayout.vue'
import {useI18n} from "../../lang/useI18n.js"
import {ref, reactive, computed} from "vue"
import {router} from "@inertiajs/vue3"

/* ---------------- PROPS ---------------- */
const props = defineProps({
    user: Object,
    cartCount: Number,
    orders: Array,
})

/* ---------------- STATE ---------------- */
const isEditing = ref(false)

const user = computed(() => props.user)

const form = reactive({
    name: user.value.name,
    phone: user.value.phone,
    address: user.value.address,
})

/* ---------------- I18N ---------------- */
const {t, currentLang} = useI18n()

/* ---------------- METHODS ---------------- */
const startEdit = () => {
    form.name = user.value.name
    form.phone = user.value.phone
    form.address = user.value.address
    isEditing.value = true
}

const cancelEdit = () => {
    isEditing.value = false
}

const submit = () => {
    router.put('/profile', form, {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false
        },
    })
}

const formatDate = (dateString) => {
    if (!dateString) return t("profile.notProvided")

    const localeMap = {
        ru: "ru-RU",
        uk: "uk-UA",
        en: "en-US",
    }

    const lang = currentLang?.value ?? "ru"

    return new Date(dateString).toLocaleDateString(localeMap[lang], {
        year: "numeric",
        month: "long",
        day: "numeric",
    })
}

const formatPhoneNumber = (phone) => {
    if (!phone) return t("profile.notProvided")
    const cleaned = phone.replace(/\D/g, "")
    if (cleaned.length === 10) {
        return `+7 (${cleaned.slice(0, 3)}) ${cleaned.slice(3, 6)}-${cleaned.slice(6, 8)}-${cleaned.slice(8)}`
    }
    return phone
}
</script>

<style lang="scss">
.profile {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
    background: #fff;

    &__title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #111;
        margin-bottom: 2rem;
        text-align: center;
        position: relative;

        &::after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, #A8E62E, #29CC5F);
            margin: 0.5rem auto 0;
            border-radius: 2px;
        }
    }
}

.profile-card {
    background: #fff;
    border-radius: 12px;
    padding: 2.5rem;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    margin: 0 auto;
    border: 1px solid #3acb6d;

    &__header {
        text-align: center;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #eaeaea;
    }

    &__name {
        font-size: 1.8rem;
        font-weight: 700;
        color: #111;
        margin-bottom: 0.5rem;
    }

    &__status {
        font-size: 0.9rem;
        font-weight: 600;
        padding: 0.4rem 1.2rem;
        border-radius: 20px;
        display: inline-block;

        &--verified {
            background: #29CC5F;
            color: white;
        }

        &--not-verified {
            background: #ff6b6b;
            color: white;
        }
    }

    &__body {
        background: #f9f9f9;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid #eaeaea;
    }

    &__footer {
        text-align: center;
    }

    &__button {
        background: #3acb6d;
        color: white;
        border: none;
        padding: 0.9rem 2.2rem;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 1rem;
        font-family: "Press Start 2P", system-ui;

        &:hover {
            background: #29CC5F;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(42, 204, 95, 0.3);
        }

        &:disabled {
            background: #2faa57;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
    }
}

.profile-edit {
    margin-top: 2rem;
    background: #f9f9f9;
    padding: 2rem;
    border-radius: 12px;
    border: 1px solid #eaeaea;

    &__title {
        text-align: center;
        margin-bottom: 1.5rem;
        font-size: 1.4rem;
    }

    .form-group {
        margin-bottom: 1rem;

        label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 600;
        }

        input,
        textarea {
            width: 100%;
            padding: 0.6rem;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-family: "Press Start 2P", system-ui;
        }
    }

    &__actions {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .btn-save {
        background: #29CC5F;
        color: white;
        padding: 0.7rem 1.8rem;
        border-radius: 6px;
        border: none;
        cursor: pointer;
    }

    .btn-cancel {
        background: #ff6b6b;
        color: white;
        padding: 0.7rem 1.8rem;
        border-radius: 6px;
        border: none;
        cursor: pointer;
    }
}

.profile-info {
    &__item {
        display: flex;
        justify-content: space-between;
        padding: 0.9rem 0;
        border-bottom: 1px solid #eee;

        &:last-child {
            border-bottom: none;
        }
    }

    &__label {
        font-weight: 600;
        color: #333;
    }

    &__value {
        color: #555;
        text-align: right;
    }
}

/* Адаптивность */
@media (max-width: 600px) {
    .profile {
        padding: 1.5rem 1rem;

        &__title {
            font-size: 2rem;
        }
    }

    .profile-card {
        padding: 1.8rem;

        &__name {
            font-size: 1.6rem;
        }
    }

    .profile-info {
        &__item {
            flex-direction: column;
            gap: 0.3rem;
        }

        &__value {
            text-align: left;
            color: #777;
        }
    }
}
</style>
