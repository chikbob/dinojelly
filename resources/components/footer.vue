<template>
    <footer class="footer">
        <div class="footer__links">
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
