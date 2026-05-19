<template>
    <MainLayout>
        <section class="info-page">
            <div class="info-page__hero">
                <p class="info-page__eyebrow">DINOJELLY</p>
                <h1 class="info-page__title">{{ content.title }}</h1>
                <p class="info-page__subtitle">{{ content.subtitle }}</p>
            </div>

            <div class="info-page__grid">
                <article
                    v-for="section in content.sections"
                    :key="section.title"
                    class="info-page__card"
                >
                    <h2>{{ section.title }}</h2>
                    <p>{{ section.body }}</p>
                </article>
            </div>
        </section>
    </MainLayout>
</template>

<script setup>
import { computed } from 'vue'
import MainLayout from '../../layouts/mainLayout.vue'
import { useI18n } from '../../lang/useI18n'

const props = defineProps({
    pageKey: {
        type: String,
        required: true,
    },
})

const { t } = useI18n()

const content = computed(() => ({
    title: t(`infoPages.${props.pageKey}.title`),
    subtitle: t(`infoPages.${props.pageKey}.subtitle`),
    sections: [
        {
            title: t(`infoPages.${props.pageKey}.section1Title`),
            body: t(`infoPages.${props.pageKey}.section1Body`),
        },
        {
            title: t(`infoPages.${props.pageKey}.section2Title`),
            body: t(`infoPages.${props.pageKey}.section2Body`),
        },
        {
            title: t(`infoPages.${props.pageKey}.section3Title`),
            body: t(`infoPages.${props.pageKey}.section3Body`),
        },
    ],
}))
</script>

<style scoped lang="scss">
.info-page {
    width: min(100%, 1180px);
    margin: 0 auto;
    padding: 48px 20px 72px;
    font-family: "Press Start 2P", system-ui;
}

.info-page__hero {
    padding: 28px;
    border-radius: 28px;
    background:
        radial-gradient(circle at top right, rgba(41, 204, 95, 0.22), transparent 38%),
        linear-gradient(135deg, #f7fee7 0%, #ffffff 54%, #ecfccb 100%);
    border: 1px solid rgba(41, 204, 95, 0.22);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
}

.info-page__eyebrow {
    margin: 0 0 14px;
    font-size: 10px;
    color: #15803d;
}

.info-page__title {
    margin: 0 0 18px;
    font-size: clamp(24px, 3vw, 38px);
    line-height: 1.4;
    color: #111827;
}

.info-page__subtitle {
    margin: 0;
    max-width: 760px;
    font-size: 11px;
    line-height: 1.9;
    color: #475569;
}

.info-page__grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 20px;
    margin-top: 28px;
}

.info-page__card {
    padding: 24px;
    border-radius: 22px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 26px rgba(15, 23, 42, 0.06);

    h2 {
        margin: 0 0 16px;
        font-size: 13px;
        line-height: 1.7;
        color: #0f172a;
    }

    p {
        margin: 0;
        font-size: 10px;
        line-height: 2;
        color: #475569;
    }
}

@media (max-width: 640px) {
    .info-page {
        padding: 32px 16px 56px;
    }

    .info-page__hero,
    .info-page__card {
        padding: 20px;
    }

    .info-page__title {
        font-size: 20px;
        line-height: 1.35;
    }

    .info-page__subtitle,
    .info-page__card p {
        font-size: 10px;
        line-height: 1.9;
    }

    .info-page__card h2 {
        font-size: 12px;
    }
}
</style>
