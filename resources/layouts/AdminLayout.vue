<template>
    <div class="admin">
        <aside class="admin__sidebar">
            <div class="admin__logo">{{ t("admin.header.shortTitle") }}</div>

            <nav class="admin__nav">
                <section v-for="section in sections" :key="section.key" class="admin__section">
                    <p class="admin__section-title">{{ t(section.title) }}</p>
                    <Link
                        v-for="item in section.items"
                        :key="item.route"
                        class="admin__link"
                        :class="{ active: isActive(item.route) }"
                        :href="route(item.route)"
                    >
                        <span>{{ t(item.label) }}</span>
                        <small v-if="badgeValue(item.badgeKey)" class="admin__badge">{{ badgeValue(item.badgeKey) }}</small>
                    </Link>
                </section>
            </nav>
        </aside>

        <div class="admin__content">
            <header class="admin__header">
                <div class="admin__header-main">
                    <span>{{ t("admin.header.title") }}</span>
                    <div class="admin__breadcrumbs">
                        <Link
                            v-for="crumb in breadcrumbs"
                            :key="crumb.route"
                            class="admin__breadcrumb"
                            :href="route(crumb.route)"
                        >
                            {{ t(crumb.label) }}
                        </Link>
                    </div>
                </div>

                <div class="admin__actions">
                    <select
                        v-model="currentLang"
                        @change="setLang(currentLang)"
                        class="admin__lang"
                        style="width:auto; min-width:84px; box-sizing:border-box;"
                    >
                        <option value="ru">RU</option>
                        <option value="en">EN</option>
                    </select>

                    <form method="post" action="/logout">
                        <input type="hidden" name="_token" :value="csrfToken" />
                        <button
                            type="submit"
                            class="admin__logout"
                            style="width:auto; min-width:100px; box-sizing:border-box; display: inline-flex; justify-content: center"
                        >
                            {{ t("admin.header.logout") }}
                        </button>
                    </form>
                </div>
            </header>

            <main class="admin__main">
                <slot/>
            </main>
        </div>
    </div>
</template>

<script setup>
import {computed} from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { useI18n } from '../lang/useI18n'

const { t, setLang, currentLang } = useI18n()
const page = usePage()
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''

const sections = [
    {
        key: 'analytics',
        title: 'admin.nav.analytics',
        items: [
            { route: 'admin.dashboard', label: 'admin.sidebar.dashboard' },
        ],
    },
    {
        key: 'catalog',
        title: 'admin.nav.catalog',
        items: [
            { route: 'admin.products.index', label: 'admin.sidebar.products', badgeKey: 'low_stock' },
            { route: 'admin.categories.index', label: 'admin.sidebar.categories' },
            { route: 'admin.collections.index', label: 'admin.sidebar.collections' },
            { route: 'admin.reviews.index', label: 'admin.sidebar.reviews', badgeKey: 'pending_reviews' },
        ],
    },
    {
        key: 'operations',
        title: 'admin.nav.operations',
        items: [
            { route: 'admin.orders.index', label: 'admin.sidebar.orders', badgeKey: 'pending_orders' },
            { route: 'admin.payments.index', label: 'admin.sidebar.payments', badgeKey: 'failed_payments' },
            { route: 'admin.delivery-slots.index', label: 'admin.sidebar.deliverySlots' },
            { route: 'admin.inventory.index', label: 'admin.sidebar.inventory', badgeKey: 'low_stock' },
        ],
    },
    {
        key: 'customers',
        title: 'admin.nav.customers',
        items: [
            { route: 'admin.users.index', label: 'admin.sidebar.users' },
            { route: 'admin.recoveries.index', label: 'admin.sidebar.recoveries', badgeKey: 'pending_recovery' },
        ],
    },
    {
        key: 'marketing',
        title: 'admin.nav.marketing',
        items: [
            { route: 'admin.promo-codes.index', label: 'admin.sidebar.promoCodes' },
            { route: 'admin.gift-cards.index', label: 'admin.sidebar.giftCards' },
            { route: 'admin.referrals.index', label: 'admin.sidebar.referrals', badgeKey: 'pending_referrals' },
        ],
    },
]

const isActive = (name) => {
    return route().current(name) || route().current(`${name.replace('.index', '')}.*`)
}

const badgeValue = (badgeKey) => {
    if (!badgeKey) return null
    return page.props.adminIndicators?.[badgeKey] ?? null
}

const breadcrumbMap = {
    'admin.dashboard': [{ route: 'admin.dashboard', label: 'admin.sidebar.dashboard' }],
    'admin.products.index': [{ route: 'admin.dashboard', label: 'admin.sidebar.dashboard' }, { route: 'admin.products.index', label: 'admin.sidebar.products' }],
    'admin.products.create': [{ route: 'admin.dashboard', label: 'admin.sidebar.dashboard' }, { route: 'admin.products.index', label: 'admin.sidebar.products' }],
    'admin.products.edit': [{ route: 'admin.dashboard', label: 'admin.sidebar.dashboard' }, { route: 'admin.products.index', label: 'admin.sidebar.products' }],
    'admin.categories.index': [{ route: 'admin.dashboard', label: 'admin.sidebar.dashboard' }, { route: 'admin.categories.index', label: 'admin.sidebar.categories' }],
    'admin.orders.index': [{ route: 'admin.dashboard', label: 'admin.sidebar.dashboard' }, { route: 'admin.orders.index', label: 'admin.sidebar.orders' }],
    'admin.orders.show': [{ route: 'admin.dashboard', label: 'admin.sidebar.dashboard' }, { route: 'admin.orders.index', label: 'admin.sidebar.orders' }],
    'admin.users.index': [{ route: 'admin.dashboard', label: 'admin.sidebar.dashboard' }, { route: 'admin.users.index', label: 'admin.sidebar.users' }],
    'admin.users.show': [{ route: 'admin.dashboard', label: 'admin.sidebar.dashboard' }, { route: 'admin.users.index', label: 'admin.sidebar.users' }],
    'admin.payments.index': [{ route: 'admin.dashboard', label: 'admin.sidebar.dashboard' }, { route: 'admin.payments.index', label: 'admin.sidebar.payments' }],
    'admin.inventory.index': [{ route: 'admin.dashboard', label: 'admin.sidebar.dashboard' }, { route: 'admin.inventory.index', label: 'admin.sidebar.inventory' }],
    'admin.delivery-slots.index': [{ route: 'admin.dashboard', label: 'admin.sidebar.dashboard' }, { route: 'admin.delivery-slots.index', label: 'admin.sidebar.deliverySlots' }],
    'admin.reviews.index': [{ route: 'admin.dashboard', label: 'admin.sidebar.dashboard' }, { route: 'admin.reviews.index', label: 'admin.sidebar.reviews' }],
    'admin.recoveries.index': [{ route: 'admin.dashboard', label: 'admin.sidebar.dashboard' }, { route: 'admin.recoveries.index', label: 'admin.sidebar.recoveries' }],
    'admin.collections.index': [{ route: 'admin.dashboard', label: 'admin.sidebar.dashboard' }, { route: 'admin.collections.index', label: 'admin.sidebar.collections' }],
    'admin.collections.create': [{ route: 'admin.dashboard', label: 'admin.sidebar.dashboard' }, { route: 'admin.collections.index', label: 'admin.sidebar.collections' }],
    'admin.collections.edit': [{ route: 'admin.dashboard', label: 'admin.sidebar.dashboard' }, { route: 'admin.collections.index', label: 'admin.sidebar.collections' }],
    'admin.promo-codes.index': [{ route: 'admin.dashboard', label: 'admin.sidebar.dashboard' }, { route: 'admin.promo-codes.index', label: 'admin.sidebar.promoCodes' }],
    'admin.promo-codes.create': [{ route: 'admin.dashboard', label: 'admin.sidebar.dashboard' }, { route: 'admin.promo-codes.index', label: 'admin.sidebar.promoCodes' }],
    'admin.promo-codes.edit': [{ route: 'admin.dashboard', label: 'admin.sidebar.dashboard' }, { route: 'admin.promo-codes.index', label: 'admin.sidebar.promoCodes' }],
    'admin.gift-cards.index': [{ route: 'admin.dashboard', label: 'admin.sidebar.dashboard' }, { route: 'admin.gift-cards.index', label: 'admin.sidebar.giftCards' }],
    'admin.gift-cards.create': [{ route: 'admin.dashboard', label: 'admin.sidebar.dashboard' }, { route: 'admin.gift-cards.index', label: 'admin.sidebar.giftCards' }],
    'admin.gift-cards.edit': [{ route: 'admin.dashboard', label: 'admin.sidebar.dashboard' }, { route: 'admin.gift-cards.index', label: 'admin.sidebar.giftCards' }],
    'admin.referrals.index': [{ route: 'admin.dashboard', label: 'admin.sidebar.dashboard' }, { route: 'admin.referrals.index', label: 'admin.sidebar.referrals' }],
    'admin.delivery-slots.create': [{ route: 'admin.dashboard', label: 'admin.sidebar.dashboard' }, { route: 'admin.delivery-slots.index', label: 'admin.sidebar.deliverySlots' }],
    'admin.delivery-slots.edit': [{ route: 'admin.dashboard', label: 'admin.sidebar.dashboard' }, { route: 'admin.delivery-slots.index', label: 'admin.sidebar.deliverySlots' }],
}

const breadcrumbs = computed(() => {
    const current = route().current()
    return breadcrumbMap[current] ?? [{ route: 'admin.dashboard', label: 'admin.sidebar.dashboard' }]
})
</script>

<style scoped lang="scss">
.admin {
    display: flex;
    min-height: 100vh;
    background: #f5f7fa;
    font-family: "Press Start 2P", system-ui;
    min-width: 0;
}

.admin__sidebar {
    width: clamp(280px, 22vw, 340px);
    min-width: 0;
    background: #0f172a;
    color: #fff;
    display: flex;
    flex-direction: column;
    padding: 24px 18px 24px 20px;
    overflow-y: auto;
}

.admin__logo {
    font-size: 18px;
    margin-bottom: 30px;
    text-align: center;
    color: #3ecf8e;
}

.admin__nav {
    display: flex;
    flex-direction: column;
    gap: 24px;
    min-width: 0;
}

.admin__section {
    display: grid;
    gap: 8px;
    min-width: 0;
}

.admin__section-title {
    margin: 0;
    color: #64748b;
    font-size: 10px;
    text-transform: uppercase;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

.admin__link {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    min-width: 0;
    padding: 12px 14px;
    border-radius: 8px;
    color: #cbd5e1;
    text-decoration: none;
    transition: 0.2s ease;

    &:hover {
        background: #1e293b;
        color: #fff;
    }

    &.active {
        background: #3ecf8e;
        color: #022c22;
    }
}

.admin__link span {
    flex: 1;
    min-width: 0;
    line-height: 1.6;
    overflow-wrap: anywhere;
}

.admin__badge {
    min-width: 20px;
    padding: 4px 6px;
    border-radius: 999px;
    background: rgba(255,255,255,0.16);
    color: inherit;
    font-size: 10px;
    text-align: center;
    flex-shrink: 0;
    margin-top: 2px;
}

.admin__content {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.admin__header {
    height: 64px;
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 24px;
    gap: 16px;
    min-width: 0;
}

.admin__header-main {
    display: grid;
    gap: 6px;
    min-width: 0;

    > span {
        line-height: 1.5;
        overflow-wrap: anywhere;
    }
}

.admin__breadcrumbs {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.admin__breadcrumb {
    color: #64748b;
    text-decoration: none;
    font-size: 10px;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

.admin__actions {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
    flex-wrap: wrap;
}

.admin__lang {
    border: 2px solid #3ecf8e;
    border-radius: 6px;
    padding: 6px 10px;
    font-family: "Press Start 2P", system-ui;
    font-size: 10px;
    cursor: pointer;
}

.admin__logout {
    background: #ef4444;
    border: none;
    color: #fff;
    padding: 8px 18px;
    border-radius: 6px;
    cursor: pointer;
    font-family: "Press Start 2P", system-ui;
    line-height: 1.5;
    white-space: normal;
}

.admin__main {
    padding: 24px;
    min-width: 0;
}

:deep(.admin-list) {
    max-width: 1320px;
    margin: 0 auto;
    padding: 24px 8px 40px;
    font-family: "Press Start 2P", system-ui;
    min-width: 0;
}

:deep(.admin-list__head) {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 20px;
}

:deep(.admin-list__title) {
    margin: 0 0 8px;
    font-size: 24px;
    line-height: 1.35;
    overflow-wrap: anywhere;
}

:deep(.admin-list__subtitle) {
    margin: 0;
    color: #64748b;
    font-size: 12px;
    line-height: 1.6;
    overflow-wrap: anywhere;
}

:deep(.admin-list__create),
:deep(.admin-action-link) {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 40px;
    padding: 10px 14px;
    border-radius: 10px;
    text-decoration: none;
    background: #2563eb;
    color: #fff;
}

:deep(.admin-table-card) {
    margin-bottom: 24px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
}

:deep(.admin-table-wrap) {
    width: 100%;
    max-width: 100%;
    min-width: 0;
    overflow-x: auto;
}

:deep(.admin-table) {
    width: 100%;
    min-width: 720px;
    border-collapse: collapse;
}

:deep(.admin-table th),
:deep(.admin-table td) {
    padding: 14px;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
    vertical-align: top;
    font-size: 11px;
    overflow-wrap: anywhere;
}

:deep(.admin-table th) {
    background: #f8fafc;
}

:deep(.admin-table tbody tr:hover) {
    background: #f8fafc;
}

:deep(.admin-table__actions) {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    min-width: 0;
}

:deep(.admin-button) {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 38px;
    padding: 10px 12px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-family: inherit;
    font-size: 11px;
    text-decoration: none;
}

:deep(.admin-button--primary) {
    background: #2563eb;
    color: #fff;
}

:deep(.admin-button--success) {
    background: #16a34a;
    color: #fff;
}

:deep(.admin-button--ghost) {
    background: #e2e8f0;
    color: #0f172a;
}

:deep(.admin-button--danger) {
    background: #fee2e2;
    color: #b91c1c;
}

:deep(.admin-input),
:deep(.admin-select) {
    width: 100%;
    max-width: 100%;
    min-width: 0;
    box-sizing: border-box;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    background: #fff;
    padding: 10px 12px;
    font-family: inherit;
    font-size: 11px;
}

:deep(.admin-status-pill) {
    display: inline-flex;
    align-items: center;
    padding: 8px 10px;
    border-radius: 999px;
    font-size: 10px;
}

:deep(.admin-status-pill--success) {
    background: #dcfce7;
    color: #166534;
}

:deep(.admin-status-pill--warning) {
    background: #fef3c7;
    color: #92400e;
}

:deep(.admin-status-pill--danger) {
    background: #fee2e2;
    color: #b91c1c;
}

:deep(.admin-status-pill--info) {
    background: #dbeafe;
    color: #1d4ed8;
}

@media (max-width: 960px) {
    .admin {
        flex-direction: column;
    }

    .admin__sidebar {
        width: 100%;
    }

    .admin__header {
        height: auto;
        padding: 16px;
        flex-direction: column;
        align-items: stretch;
    }

    .admin__actions {
        flex-direction: column;
        align-items: stretch;
    }

    :deep(.admin-list) {
        padding: 20px 0 32px;
    }

    :deep(.admin-list__head) {
        flex-direction: column;
        align-items: stretch;
    }

    :deep(.admin-list__create),
    :deep(.admin-button),
    :deep(.admin-action-link) {
        width: 100%;
        max-width: 100%;
    }
}

@media (max-width: 640px) {
    .admin__main {
        padding: 16px;
    }
}
</style>
