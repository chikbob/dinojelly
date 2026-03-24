<template>
    <MainLayout>
        <div class="checkout">
            <h1 class="checkout__title">{{ t("cart.checkout") }}</h1>

            <div class="checkout__section">
                <h2>{{ t("checkout.addressTitle") }}</h2>
                <div v-if="addresses.length" class="checkout__options">
                    <label v-for="address in addresses" :key="address.id" class="checkout-option">
                        <input v-model="selectedAddressId" type="radio" :value="address.id" />
                        <div>
                            <strong>{{ address.label || address.recipient_name }}</strong>
                            <p>{{ address.full_address }}</p>
                            <small>{{ address.phone }}</small>
                        </div>
                    </label>
                </div>
                <p v-else class="checkout__empty">{{ t("checkout.addressEmpty") }}</p>
            </div>

            <div class="checkout__section">
                <h2>{{ t("checkout.deliveryTitle") }}</h2>
                <div v-if="deliverySlots.length" class="checkout__options">
                    <label v-for="slot in deliverySlots" :key="slot.id" class="checkout-option">
                        <input v-model="selectedDeliverySlotId" type="radio" :value="slot.id" />
                        <div>
                            <strong>{{ slot.name }}</strong>
                            <p>{{ formatSlot(slot) }}</p>
                            <small>+{{ slot.price }} {{ t("currency.symbol") }}</small>
                        </div>
                    </label>
                </div>
                <p v-else class="checkout__empty">{{ t("checkout.deliveryEmpty") }}</p>
            </div>

            <div class="checkout__section">
                <h2>{{ t("checkout.bonusesTitle") }}</h2>

                <div class="checkout__bonus">
                    <label class="checkout__bonus-label">
                        <input v-model="useReferralCredit" type="checkbox" />
                        <span>{{ t("checkout.useReferralCredit") }} ({{ referralCreditBalance }} {{ t("currency.symbol") }})</span>
                    </label>
                </div>

                <div class="checkout__bonus checkout__bonus--gift">
                    <div class="checkout__gift-row">
                        <input
                            v-model="giftCardCode"
                            type="text"
                            class="checkout__gift-input"
                            :placeholder="t('checkout.giftCardPlaceholder')"
                        />
                        <button class="checkout__gift-preview" @click.prevent="previewGiftCard">
                            {{ t("checkout.applyGiftCard") }}
                        </button>
                    </div>
                    <p v-if="giftCardError" class="checkout__gift-error">{{ giftCardError }}</p>
                    <p v-else-if="giftCardPreview" class="checkout__gift-success">
                        {{ t("checkout.giftCardApplied") }}: -{{ giftCardPreview.applied_amount }} {{ t("currency.symbol") }}
                    </p>
                    <div v-if="giftCards?.length" class="checkout__gift-list">
                        <button
                            v-for="card in giftCards"
                            :key="card.id"
                            class="checkout__gift-chip"
                            @click.prevent="selectGiftCard(card.code)"
                        >
                            {{ card.code }} · {{ card.balance }} {{ t("currency.symbol") }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="checkout__items">
                <p v-if="stockErrors?.length" class="checkout__stock-error">
                    {{ stockErrors.join(' ') }}
                </p>
                <div v-for="item in items" :key="item.id" class="checkout__item">
                    <img :src="item.image_url" class="checkout__img"/>
                    <div class="checkout__info">
                        <h2>{{ item.name }}</h2>
                        <p>{{ item.price }} {{ t("currency.symbol") }} × {{ item.quantity }}</p>
                        <p><b>{{ t("cart.total") }}:</b> {{ item.subtotal }} {{ t("currency.symbol") }}</p>
                    </div>
                </div>
            </div>

            <div class="checkout__summary">
                <p>{{ t("cart.items") }}: {{ totalQuantity }}</p>
                <p>{{ t("checkout.subtotal") }}: <b>{{ subtotalPrice }} {{ t("currency.symbol") }}</b></p>
                <p>{{ t("checkout.deliveryPrice") }}: <b>{{ currentDeliveryPrice }} {{ t("currency.symbol") }}</b></p>
                <p v-if="giftCardDiscount">{{ t("checkout.giftCardDiscount") }}: <b>-{{ giftCardDiscount }} {{ t("currency.symbol") }}</b></p>
                <p v-if="referralCreditDiscount">{{ t("checkout.referralCreditDiscount") }}: <b>-{{ referralCreditDiscount }} {{ t("currency.symbol") }}</b></p>
                <p>{{ t("cart.finalTotal") }}: <b>{{ finalTotal }} {{ t("currency.symbol") }}</b></p>
            </div>

            <div class="checkout__payment-methods">
                <button
                    class="checkout__btn checkout__btn--card"
                    :disabled="isDisabled"
                    @click.prevent="submitOrder('card')"
                >
                    {{ t("payments.payCard") }}
                </button>

                <button
                    class="checkout__btn checkout__btn--cash"
                    :disabled="isDisabled"
                    @click.prevent="submitOrder('cash')"
                >
                    {{ t("payments.payCash") }}
                </button>
            </div>
            <p class="checkout__payment-hint">{{ t("payments.mockHint") }}</p>
        </div>
    </MainLayout>
</template>

<script setup>
import {computed, ref} from "vue";
import {router} from "@inertiajs/vue3";
import MainLayout from "../layouts/mainLayout.vue";
import {useI18n} from "../lang/useI18n.js";

const {t, currentLang} = useI18n();

const props = defineProps({
    items: Array,
    totalQuantity: Number,
    subtotalPrice: Number,
    deliveryPrice: Number,
    totalPrice: Number,
    addresses: Array,
    deliverySlots: Array,
    defaultAddressId: Number,
    defaultDeliverySlotId: Number,
    stockErrors: Array,
    referralCreditBalance: Number,
    giftCards: Array,
});

const paymentMethod = ref(null);
const selectedAddressId = ref(props.defaultAddressId ?? null)
const selectedDeliverySlotId = ref(props.defaultDeliverySlotId ?? null)
const useReferralCredit = ref(false)
const giftCardCode = ref('')
const giftCardPreview = ref(null)
const giftCardError = ref('')

const selectedSlot = computed(() =>
    props.deliverySlots.find((slot) => slot.id === Number(selectedDeliverySlotId.value))
)

const currentDeliveryPrice = computed(() => selectedSlot.value?.price ?? 0)
const giftCardDiscount = computed(() => Number(giftCardPreview.value?.applied_amount ?? 0))
const referralCreditDiscount = computed(() => {
    if (!useReferralCredit.value) {
        return 0
    }

    return Math.min(Number(props.referralCreditBalance ?? 0), Math.max(Number(props.subtotalPrice) + Number(currentDeliveryPrice.value) - giftCardDiscount.value, 0))
})
const finalTotal = computed(() => Math.max(Number(props.subtotalPrice) + Number(currentDeliveryPrice.value) - giftCardDiscount.value - referralCreditDiscount.value, 0))
const isDisabled = computed(() => !selectedAddressId.value || !selectedDeliverySlotId.value || (props.stockErrors?.length ?? 0) > 0)

const formatSlot = (slot) => {
    const localeMap = { ru: "ru-RU", uk: "uk-UA", en: "en-US" }
    const locale = localeMap[currentLang?.value ?? 'ru']
    const starts = new Date(slot.starts_at).toLocaleString(locale, {
        day: 'numeric',
        month: 'long',
        hour: '2-digit',
        minute: '2-digit',
    })
    const ends = new Date(slot.ends_at).toLocaleString(locale, {
        hour: '2-digit',
        minute: '2-digit',
    })
    return `${starts} - ${ends}`
}

function submitOrder(method) {
    if (isDisabled.value) {
        return
    }

    paymentMethod.value = method;

    router.post("/checkout", {
        payment_method: paymentMethod.value,
        address_id: selectedAddressId.value,
        delivery_slot_id: selectedDeliverySlotId.value,
        gift_card_code: giftCardCode.value || null,
        use_referral_credit: useReferralCredit.value,
    });
}

async function previewGiftCard() {
    giftCardError.value = ''
    giftCardPreview.value = null

    if (!giftCardCode.value) {
        return
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    const response = await fetch('/checkout/gift-card-preview', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token ?? '',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
            code: giftCardCode.value,
            delivery_price: currentDeliveryPrice.value,
        }),
    })

    const data = await response.json()

    if (!response.ok) {
        giftCardError.value = data.message ?? t('checkout.giftCardInvalid')
        return
    }

    giftCardPreview.value = data
}

function selectGiftCard(code) {
    giftCardCode.value = code
    previewGiftCard()
}
</script>

<style scoped>
.checkout {
    max-width: 960px;
    margin: auto;
    padding: 24px;
}

.checkout__title {
    text-align: center;
    margin-bottom: 20px;
    font-family: "Press Start 2P", system-ui;
    font-size: 18px;
}

.checkout__items {
    margin-bottom: 20px;
}

.checkout__section {
    margin-bottom: 24px;
    padding: 20px;
    border-radius: 16px;
    background: #fff;
    border: 1px solid #e5e7eb;
}

.checkout__options {
    display: grid;
    gap: 12px;
}

.checkout-option {
    display: flex;
    gap: 14px;
    align-items: start;
    padding: 14px;
    border: 1px solid #d1d5db;
    border-radius: 12px;
    cursor: pointer;
}

.checkout__empty {
    color: #6b7280;
}

.checkout__bonus {
    display: grid;
    gap: 12px;
}

.checkout__bonus-label {
    display: flex;
    gap: 10px;
    align-items: center;
}

.checkout__gift-row {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.checkout__gift-input {
    flex: 1;
    min-width: 240px;
    padding: 12px 14px;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
}

.checkout__gift-preview {
    border: none;
    border-radius: 10px;
    padding: 12px 16px;
    background: #111827;
    color: #fff;
    cursor: pointer;
    font-family: "Press Start 2P", system-ui;
    font-size: 10px;
}

.checkout__gift-list {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.checkout__gift-chip {
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    border-radius: 999px;
    padding: 8px 10px;
    cursor: pointer;
    font-family: "Press Start 2P", system-ui;
    font-size: 9px;
}

.checkout__gift-error {
    color: #b91c1c;
}

.checkout__gift-success {
    color: #166534;
}

.checkout__item {
    display: flex;
    gap: 16px;
    padding: 12px;
    border-bottom: 1px solid #ddd;
}

.checkout__stock-error {
    margin-bottom: 16px;
    padding: 14px 16px;
    border-radius: 12px;
    background: #fee2e2;
    color: #b91c1c;
}

.checkout__img {
    width: 80px;
    height: 80px;
    object-fit: cover;
}

.checkout__info h2 {
    font-size: 14px;
    margin: 0;
}

.checkout__btn {
    display: flex;
    justify-content: center;
    margin-top: 20px;
    padding: 12px 24px;
    border: none;
    color: #fff;
    cursor: pointer;
    border-radius: 8px;
    font-family: "Press Start 2P", system-ui;
    transition: 0.3s ease;
}

.checkout__payment-methods {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 30px;
}

.checkout__payment-hint {
    margin-top: 16px;
    text-align: center;
    color: #64748b;
    font-size: 14px;
}

.checkout__btn--card {
    background: #3b82f6;
}

.checkout__btn--card:hover {
    background: #2563eb;
}

.checkout__btn--cash {
    background: #22c55e;
}

.checkout__btn--cash:hover {
    background: #16a34a;
}

.checkout__btn i {
    margin-right: 8px;
}

.checkout__btn:disabled {
    background: #9ca3af;
    cursor: not-allowed;
}
</style>
