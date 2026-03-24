<template>
    <div class="admin">
        <aside class="admin__sidebar">
            <div class="admin__logo">Admin</div>

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
                    >
                        <option value="ru">RU</option>
                        <!-- <option value="uk">UA</option> -->
                        <option value="en">EN</option>
                    </select>

                    <Link
                        method="post"
                        as="button"
                        href="/logout"
                        class="admin__logout"
                    >
                        {{ t("admin.header.logout") }}
                    </Link>
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
}

.admin__sidebar {
    width: 240px;
    background: #0f172a;
    color: #fff;
    display: flex;
    flex-direction: column;
    padding: 20px;
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
    gap: 20px;
}

.admin__section {
    display: grid;
    gap: 8px;
}

.admin__section-title {
    margin: 0;
    color: #64748b;
    font-size: 10px;
    text-transform: uppercase;
}

.admin__link {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
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

.admin__badge {
    min-width: 20px;
    padding: 4px 6px;
    border-radius: 999px;
    background: rgba(255,255,255,0.16);
    color: inherit;
    font-size: 10px;
    text-align: center;
}

.admin__content {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.admin__header {
    height: 64px;
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 24px;
}

.admin__header-main {
    display: grid;
    gap: 6px;
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
}

.admin__actions {
    display: flex;
    align-items: center;
    gap: 12px;
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
    padding: 8px 14px;
    border-radius: 6px;
    cursor: pointer;
    font-family: "Press Start 2P", system-ui;
}

.admin__main {
    padding: 24px;
}
</style>
