<template>
    <div v-if="links.length > 3" class="pagination">
        <template v-for="(link, i) in links" :key="i">
            <span
                v-if="!link.url"
                v-html="sanitizeLabel(link.label)"
                class="pagination__link pagination__link--disabled"
            />

            <Link
                v-else
                :href="link.url"
                v-html="sanitizeLabel(link.label)"
                class="pagination__link"
                :class="{ 'pagination__link--active': link.active }"
            />
        </template>
    </div>
</template>

<script setup>
import {Link} from "@inertiajs/vue3"
import {useI18n} from "../lang/useI18n"

const {t} = useI18n()

defineProps({
    links: Array,
})

const sanitizeLabel = (label) => {
    return label
        .replace("&laquo; Previous", `« ${t("pagination.previous")}`)
        .replace("Next &raquo;", `${t("pagination.next")} »`)
}
</script>


<style scoped lang="scss">
.pagination {
    display: flex;
    justify-content: center;
    margin-top: 35px;
    gap: 4px;
    font-size: 13px;
    color: #333;

    &__link {
        padding: 6px 12px;
        border: 1px solid #dcdcdc;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
        color: #333;
        background: #fff;
        transition: background 0.2s ease, color 0.2s ease;

        &:hover {
            background: #35b67c;
        }

        &--active {
            background: #3ecf8e; /* как в браузере */
            border-color: #35b27a;
            color: #fff !important;
        }

        &--disabled {
            opacity: 0.6;
            cursor: default;
            background: #fafafa;
        }
    }
}
</style>
