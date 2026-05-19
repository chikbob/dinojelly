<template>
    <div
        v-if="visibleLinks.length > 3"
        class="pagination"
        style="display:flex; justify-content:center; align-items:center; gap:6px; flex-wrap:nowrap; width:100%; max-width:100%; min-width:0; overflow:hidden;"
    >
        <template v-for="(link, i) in visibleLinks" :key="`${i}-${link.label}`">
            <span
                v-if="!link.url"
                v-html="sanitizeLabel(link.label)"
                class="pagination__link pagination__link--disabled"
                style="display:inline-flex; align-items:center; justify-content:center; min-height:36px; min-width:40px; max-width:100%; padding:8px 10px; box-sizing:border-box; white-space:nowrap;"
            />

            <Link
                v-else
                :href="link.url"
                v-html="sanitizeLabel(link.label)"
                class="pagination__link"
                :class="{ 'pagination__link--active': link.active }"
                style="display:inline-flex; align-items:center; justify-content:center; min-height:36px; min-width:40px; max-width:100%; padding:8px 10px; box-sizing:border-box; white-space:nowrap;"
            />
        </template>
    </div>
</template>

<script setup>
import {computed, onBeforeUnmount, onMounted, ref} from 'vue'
import {Link} from "@inertiajs/vue3"
import {useI18n} from "../lang/useI18n"

const {t} = useI18n()

const props = defineProps({
    links: Array,
})

const isCompactViewport = ref(false)

const syncViewport = () => {
    isCompactViewport.value = window.innerWidth <= 480
}

onMounted(() => {
    syncViewport()
    window.addEventListener('resize', syncViewport)
})

onBeforeUnmount(() => {
    window.removeEventListener('resize', syncViewport)
})

const visibleLinks = computed(() => {
    const items = props.links ?? []

    if (items.length <= 9) {
        return items
    }

    const previous = items[0]
    const next = items[items.length - 1]
    const pages = items.slice(1, -1)
    const activeIndex = pages.findIndex((item) => item.active)

    if (activeIndex === -1) {
        return items
    }

    const start = Math.max(0, activeIndex - 1)
    const end = Math.min(pages.length, activeIndex + 2)
    const compactPages = pages.slice(start, end)
    const result = [previous]

    if (start > 0) {
        result.push(pages[0])
    }

    if (start > 1) {
        result.push({url: null, label: '...'})
    }

    result.push(...compactPages)

    if (end < pages.length - 1) {
        result.push({url: null, label: '...'})
    }

    if (end < pages.length) {
        result.push(pages[pages.length - 1])
    }

    result.push(next)

    return result
})

const decodeHtml = (value) => {
    return value
        .replaceAll('&laquo;', '«')
        .replaceAll('&raquo;', '»')
        .replaceAll('&amp;', '&')
        .replaceAll('&nbsp;', ' ')
}

const stripTags = (value) => value.replace(/<[^>]*>/g, '').trim()

const sanitizeLabel = (label) => {
    const normalized = stripTags(decodeHtml(String(label))).toLowerCase()

    const previousLabels = [
        '« previous',
        'previous',
        '« попередня',
        'попередня',
        '« предыдущая',
        'предыдущая',
        '« pagination.previous',
        'pagination.previous',
    ]

    const nextLabels = [
        'next »',
        'next',
        'наступна »',
        'наступна',
        'следующая »',
        'следующая',
        'pagination.next »',
        'pagination.next',
    ]

    if (previousLabels.includes(normalized)) {
        return isCompactViewport.value ? '‹' : `« ${t("pagination.previous")}`
    }

    if (nextLabels.includes(normalized)) {
        return isCompactViewport.value ? '›' : `${t("pagination.next")} »`
    }

    if (normalized === '...') {
        return '...'
    }

    return stripTags(decodeHtml(String(label)))
}
</script>


<style scoped lang="scss">
.pagination {
    display: flex;
    justify-content: center;
    margin-top: 35px;
    gap: 4px;
    font-size: 11px;
    color: #333;
    flex-wrap: nowrap;
    overflow-x: auto;
    overflow-y: hidden;
    padding-bottom: 4px;

    &__link {
        padding: 6px 10px;
        border: 1px solid #dcdcdc;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
        color: #333;
        background: #fff;
        transition: background 0.2s ease, color 0.2s ease;
        white-space: nowrap;

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

@media (max-width: 640px) {
    .pagination {
        justify-content: flex-start;
        gap: 6px;
    }

    .pagination__link {
        min-width: 38px;
        font-size: 10px;
    }
}
</style>
