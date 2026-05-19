<template>
    <MainLayout>
        <section class="mock-payment">
            <div class="mock-payment__card">
                <p class="mock-payment__eyebrow">{{ t("payments.mockTitle") }}</p>
                <h1>{{ t("payments.completePayment") }}</h1>
                <p class="mock-payment__lead">
                    {{ t("payments.mockDescription") }}
                </p>

                <div class="mock-payment__summary">
                    <div>
                        <span>{{ t("order.orderNumber") }}</span>
                        <strong>#{{ orderId }}</strong>
                    </div>
                    <div>
                        <span>{{ t("payments.amount") }}</span>
                        <strong>{{ payment.amount }} {{ payment.currency }}</strong>
                    </div>
                    <div>
                        <span>{{ t("payments.provider") }}</span>
                        <strong>{{ payment.provider }}</strong>
                    </div>
                    <div>
                        <span>{{ t("payments.statusLabel") }}</span>
                        <strong>{{ t(`payments.status.${payment.status}`) }}</strong>
                    </div>
                </div>

                <div class="mock-payment__actions">
                    <form method="post" action="/webhooks/payments/mock">
                        <input type="hidden" name="provider_payment_id" :value="payment.provider_payment_id" />
                        <input type="hidden" name="status" value="paid" />
                        <button class="mock-payment__button mock-payment__button--success" type="submit">
                            {{ t("payments.payNow") }}
                        </button>
                    </form>

                    <form method="post" action="/webhooks/payments/mock">
                        <input type="hidden" name="provider_payment_id" :value="payment.provider_payment_id" />
                        <input type="hidden" name="status" value="failed" />
                        <button class="mock-payment__button mock-payment__button--warning" type="submit">
                            {{ t("payments.failPayment") }}
                        </button>
                    </form>

                    <form method="post" action="/webhooks/payments/mock">
                        <input type="hidden" name="provider_payment_id" :value="payment.provider_payment_id" />
                        <input type="hidden" name="status" value="canceled" />
                        <button class="mock-payment__button mock-payment__button--ghost" type="submit">
                            {{ t("payments.cancelPayment") }}
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </MainLayout>
</template>

<script setup>
import MainLayout from "../../layouts/mainLayout.vue";
import {useI18n} from "../../lang/useI18n.js";

const {t} = useI18n()

defineProps({
    payment: Object,
    orderId: Number,
})
</script>

<style scoped>
.mock-payment {
    min-height: calc(100dvh - 120px);
    display: grid;
    place-items: center;
    padding: 32px 16px;
}

.mock-payment__card {
    width: min(640px, 100%);
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    padding: 32px;
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
}

.mock-payment__eyebrow {
    margin: 0 0 8px;
    color: #2563eb;
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: 0.08em;
}

.mock-payment h1 {
    margin: 0 0 12px;
}

.mock-payment__lead {
    margin: 0 0 24px;
    color: #475569;
}

.mock-payment__summary {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
    padding: 20px;
    border-radius: 16px;
    background: #f8fafc;
    margin-bottom: 24px;
}

.mock-payment__summary span {
    display: block;
    margin-bottom: 6px;
    color: #64748b;
    font-size: 12px;
}

.mock-payment__actions {
    display: grid;
    gap: 12px;
}

.mock-payment__button {
    width: 100%;
    border: none;
    border-radius: 12px;
    padding: 14px 18px;
    font-weight: 700;
    cursor: pointer;
}

.mock-payment__button--success {
    background: #16a34a;
    color: #fff;
}

.mock-payment__button--warning {
    background: #f59e0b;
    color: #fff;
}

.mock-payment__button--ghost {
    background: #e2e8f0;
    color: #0f172a;
}

@media (max-width: 640px) {
    .mock-payment {
        padding: 20px 14px 32px;
        min-height: auto;
    }

    .mock-payment__card {
        padding: 20px 16px;
    }

    .mock-payment h1 {
        font-size: 18px;
        line-height: 1.35;
    }

    .mock-payment__summary {
        grid-template-columns: 1fr;
    }
}
</style>
