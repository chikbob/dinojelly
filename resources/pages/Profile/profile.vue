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

                    <form @submit.prevent="submit" style="width:100%; max-width:100%; min-width:0;">
                        <div class="form-group">
                            <label>{{ t("profile.name") }}</label>
                            <input v-model="form.name" type="text" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;"/>
                        </div>

                        <div class="form-group">
                            <label>{{ t("profile.phone") }}</label>
                            <input v-model="form.phone" type="text" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;"/>
                        </div>

                        <div class="form-group">
                            <label>{{ t("profile.address") }}</label>
                            <textarea v-model="form.address" rows="3" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;"></textarea>
                        </div>

                        <div class="profile-edit__actions">
                            <button type="submit" class="btn-save" style="width:100%; max-width:100%; box-sizing:border-box;">
                                {{ t("profile.save") }}
                            </button>

                            <button
                                type="button"
                                class="btn-cancel"
                                style="width:100%; max-width:100%; box-sizing:border-box;"
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

            <div class="profile-addresses">
                <div class="profile-addresses__header">
                    <h2>{{ t("profile.addresses") }}</h2>
                </div>

                <div v-if="addresses.length" class="profile-addresses__list">
                    <div v-for="address in addresses" :key="address.id" class="profile-address">
                        <div>
                            <strong>{{ address.label || address.recipient_name }}</strong>
                            <p>{{ address.full_address }}</p>
                            <small>{{ address.phone }}</small>
                        </div>
                        <button class="profile-address__delete" @click="removeAddress(address.id)">
                            {{ t("cart.delete") }}
                        </button>
                    </div>
                </div>

                <form class="profile-addresses__form" @submit.prevent="submitAddress" style="width:100%; max-width:100%; min-width:0;">
                    <div class="profile-addresses__grid" style="width:100%; max-width:100%; min-width:0;">
                        <input v-model="addressForm.label" :placeholder="t('profile.addressLabel')" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" />
                        <input v-model="addressForm.recipient_name" :placeholder="t('profile.name')" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" />
                        <input v-model="addressForm.phone" :placeholder="t('profile.phone')" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" />
                        <input v-model="addressForm.city" :placeholder="t('profile.city')" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" />
                        <input v-model="addressForm.street" :placeholder="t('profile.street')" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" />
                        <input v-model="addressForm.building" :placeholder="t('profile.building')" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" />
                        <input v-model="addressForm.apartment" :placeholder="t('profile.apartment')" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" />
                        <input v-model="addressForm.postal_code" :placeholder="t('profile.postalCode')" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" />
                    </div>
                    <textarea v-model="addressForm.comment" :placeholder="t('profile.addressComment')" rows="3" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;"></textarea>
                    <label class="profile-addresses__checkbox" style="width:100%; max-width:100%; min-width:0;">
                        <span>{{ t("profile.defaultAddress") }}</span>
                        <input v-model="addressForm.is_default" type="checkbox" class="profile-addresses__checkbox-input" />
                    </label>
                    <button type="submit" class="profile-card__button" style="width:100%; max-width:100%; box-sizing:border-box;">
                        {{ t("profile.addAddress") }}
                    </button>
                </form>
            </div>

            <div class="profile-addresses">
                <div class="profile-addresses__header">
                    <h2>{{ t("subscriptions.title") }}</h2>
                    <button class="profile-card__button" @click="router.visit('/subscriptions')">
                        {{ t("subscriptions.openAll") }}
                    </button>
                </div>

                <div v-if="subscriptions.length" class="profile-addresses__list">
                    <div v-for="subscription in subscriptions" :key="subscription.id" class="profile-address">
                        <div>
                            <strong>{{ getSubscriptionName(subscription) }}</strong>
                            <p>{{ t(`subscriptions.status.${subscription.status}`) }}</p>
                            <small v-if="subscription.next_run_at">
                                {{ t("subscriptions.nextRun") }}: {{ formatDate(subscription.next_run_at) }}
                            </small>
                        </div>
                    </div>
                </div>
                <p v-else class="profile-info__value">{{ t("subscriptions.empty") }}</p>
            </div>

            <div class="profile-addresses">
                <div class="profile-addresses__header">
                    <h2>{{ t("profile.referralsTitle") }}</h2>
                </div>

                <div class="profile-info__item">
                    <span class="profile-info__label">{{ t("profile.referralCode") }}:</span>
                    <span class="profile-info__value">{{ referralCode }}</span>
                </div>
                <div class="profile-info__item">
                    <span class="profile-info__label">{{ t("profile.referralBalance") }}:</span>
                    <span class="profile-info__value">{{ referralCreditBalance }}</span>
                </div>
                <div class="profile-edit__actions profile-edit__actions--with-bottom-margin">
                    <button class="profile-card__button" @click="copyReferralLink">{{ t("profile.copyReferralLink") }}</button>
                </div>

                <div class="profile-addresses__list">
                    <div class="profile-address">
                        <div>
                            <strong>{{ t("profile.referralTotal") }}: {{ referralStats?.total ?? 0 }}</strong>
                            <p>{{ t("profile.referralRewarded") }}: {{ referralStats?.rewarded ?? 0 }}</p>
                            <small>{{ t("profile.referralPending") }}: {{ referralStats?.pending ?? 0 }}</small>
                        </div>
                    </div>
                    <div v-for="referral in referrals" :key="referral.id" class="profile-address">
                        <div>
                            <strong>{{ referral.referred_user?.email || t("profile.referralInvitePending") }}</strong>
                            <p>{{ t(`profile.referralStatus.${referral.status}`) }}</p>
                            <small>{{ referral.reward_amount }} {{ t("currency.symbol") }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-addresses">
                <div class="profile-addresses__header">
                    <h2>{{ t("profile.giftCardsTitle") }}</h2>
                </div>

                <form class="profile-addresses__form" @submit.prevent="claimGiftCard" style="width:100%; max-width:100%; min-width:0;">
                    <div class="profile-addresses__grid" style="width:100%; max-width:100%; min-width:0;">
                        <input v-model="giftCardClaimForm.code" :placeholder="t('profile.giftCardCode')" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" />
                    </div>
                    <button type="submit" class="profile-card__button">
                        {{ t("profile.claimGiftCard") }}
                    </button>
                </form>

                <div v-if="giftCards.length" class="profile-addresses__list">
                    <div v-for="giftCard in giftCards" :key="giftCard.id" class="profile-address">
                        <div>
                            <strong>{{ giftCard.name }} · {{ giftCard.code }}</strong>
                            <p>{{ giftCard.balance }} / {{ giftCard.initial_amount }} {{ t("currency.symbol") }}</p>
                            <small v-if="giftCard.expires_at">{{ formatDate(giftCard.expires_at) }}</small>
                        </div>
                    </div>
                </div>
                <p v-else class="profile-info__value profile-info__value--left">{{ t("profile.noGiftCards") }}</p>
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
    addresses: {
        type: Array,
        default: () => ([]),
    },
    subscriptions: {
        type: Array,
        default: () => ([]),
    },
    referralCode: String,
    referralLink: String,
    referralCreditBalance: Number,
    referralStats: Object,
    referrals: {
        type: Array,
        default: () => ([]),
    },
    giftCards: {
        type: Array,
        default: () => ([]),
    },
})

/* ---------------- STATE ---------------- */
const isEditing = ref(false)

const user = computed(() => props.user)

const form = reactive({
    name: user.value.name,
    phone: user.value.phone,
    address: user.value.address,
})

const addressForm = reactive({
    label: '',
    recipient_name: user.value.name ?? '',
    phone: user.value.phone ?? '',
    city: '',
    street: '',
    building: '',
    apartment: '',
    postal_code: '',
    comment: '',
    is_default: props.addresses.length === 0,
})

const giftCardClaimForm = reactive({
    code: '',
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

const submitAddress = () => {
    router.post('/addresses', addressForm, {
        preserveScroll: true,
        onSuccess: () => {
            addressForm.label = ''
            addressForm.city = ''
            addressForm.street = ''
            addressForm.building = ''
            addressForm.apartment = ''
            addressForm.postal_code = ''
            addressForm.comment = ''
            addressForm.is_default = false
        },
    })
}

const removeAddress = (id) => {
    router.delete(`/addresses/${id}`, {
        preserveScroll: true,
    })
}

const claimGiftCard = () => {
    router.post('/gift-cards/claim', giftCardClaimForm, {
        preserveScroll: true,
        onSuccess: () => {
            giftCardClaimForm.code = ''
        },
    })
}

const copyReferralLink = async () => {
    if (!props.referralLink) {
        return
    }

    await navigator.clipboard.writeText(props.referralLink)
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

const getSubscriptionName = (subscription) => {
    const name = subscription.name ?? ""
    const orderId = subscription.source_order_id ?? subscription.last_order?.id
    const legacyPattern = /^(subscription-order-|Подписка на заказ #|Підписка на замовлення #|Subscription for order #)(\d+)$/i
    const matchedOrderId = name.match(legacyPattern)?.[2]
    const resolvedOrderId = orderId ?? matchedOrderId

    if (!resolvedOrderId) {
        return name
    }

    if (!name || legacyPattern.test(name)) {
        return t("subscriptions.defaultName").replace(":order", resolvedOrderId)
    }

    return name
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
    min-width: 0;

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
        max-width: 100%;

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

.profile-addresses {
    margin-top: 32px;
    padding: 24px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    min-width: 0;

    &__header {
        margin-bottom: 16px;
    }

    &__list {
        display: grid;
        gap: 12px;
        margin-bottom: 20px;
    }

    &__form {
        display: grid;
        gap: 14px;
        min-width: 0;
    }

    &__grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    &__checkbox {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    input,
    textarea {
        width: 100%;
        max-width: 100%;
        padding: 10px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-family: "Press Start 2P", system-ui;
    }
}

.profile-address {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    padding: 14px;
    border-radius: 10px;
    background: #f8fafc;
    min-width: 0;

    &__delete {
        border: none;
        background: transparent;
        color: #ef4444;
        cursor: pointer;
        font-family: "Press Start 2P", system-ui;
    }
}

.profile-addresses__checkbox {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: flex-start;
    gap: 12px;
    margin: 12px 0 0;
    width: 100%;

    span {
        line-height: 1.4;
        flex: 0 1 auto;
    }
}

.profile-addresses__checkbox-input {
    width: auto !important;
    max-width: none !important;
    min-width: 18px !important;
    height: 18px;
    margin: 0;
    padding: 0 !important;
    border: none !important;
    border-radius: 0 !important;
    flex: 0 0 auto;
    appearance: auto;
    -webkit-appearance: checkbox;
    background: transparent !important;
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
        min-width: 0;

        label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 600;
        }

        input,
        textarea {
            width: 100%;
            max-width: 100%;
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

    &__actions--with-bottom-margin {
        margin-bottom: 1.5rem;
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

    &__value--left {
        text-align: left;
    }
}

/* Адаптивность */
@media (max-width: 600px) {
    .profile {
        padding: 1.5rem 0.875rem 2rem;

        &__title {
            font-size: 1.6rem;
            line-height: 1.25;
        }
    }

    .profile-card {
        padding: 1.1rem;

        &__name {
            font-size: 1.2rem;
            line-height: 1.35;
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

    .profile-addresses {
        padding: 16px 14px;

        &__grid {
            grid-template-columns: 1fr;
        }
    }

    .profile-address,
    .profile-edit__actions {
        flex-direction: column;
    }

    .profile-addresses__header,
    .profile-addresses__checkbox {
        flex-direction: column;
        align-items: stretch;
    }

    .profile-card__button,
    .profile-edit .btn-save,
    .profile-edit .btn-cancel {
        width: 100%;
    }

    .profile-edit {
        padding: 1.1rem;
    }
}
</style>
