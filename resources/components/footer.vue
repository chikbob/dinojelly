<template>
    <footer class="footer" style="width:100%; max-width:100%; overflow:hidden; box-sizing:border-box;">
        <div class="footer__links" style="display:flex; gap:12px; flex-wrap:wrap; justify-content:center; width:100%; max-width:100%; min-width:0;">
            <a href="/help" class="footer__link">{{ t("footer.help") }}</a>
            <a href="/returns" class="footer__link">{{ t("footer.return") }}</a>
            <a href="/payment" class="footer__link">{{ t("footer.payment") }}</a>
        </div>
        <p class="footer__copyright">
            {{'© ' + new Date().getFullYear()}}  {{ t("footer.copyright") }}
        </p>
    </footer>
</template>

<script setup>
import { useI18n } from "../lang/useI18n"
const { t } = useI18n()
</script>

<style scoped lang="scss">
.footer {
    width: 100%;
}

.footer__links {
    row-gap: 10px;
}

.footer__link,
.footer__copyright {
    line-height: 1.8;
    word-break: break-word;
}

@media (max-width: 640px) {
    .footer {
        padding: 24px 14px;
    }

    .footer__links {
        gap: 10px !important;
        flex-direction: column;
        align-items: center;
    }

    .footer__link {
        font-size: 11px;
    }

    .footer__copyright {
        font-size: 10px;
        line-height: 1.9;
    }
}
</style>
