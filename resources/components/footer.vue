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
