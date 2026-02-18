<template>
    <div class="admin">
        <!-- Sidebar -->
        <aside class="admin__sidebar">
            <div class="admin__logo">Admin</div>

            <nav class="admin__nav">
                <Link
                    class="admin__link"
                    :class="{ active: isActive('admin.dashboard') }"
                    :href="route('admin.dashboard')"
                >
                    {{ t("admin.sidebar.dashboard") }}
                </Link>

                <Link
                    class="admin__link"
                    :class="{ active: isActive('admin.products.index') }"
                    :href="route('admin.products.index')"
                >
                    {{ t("admin.sidebar.products") }}
                </Link>

                <Link
                    class="admin__link"
                    :class="{ active: isActive('admin.orders.index') }"
                    :href="route('admin.orders.index')"
                >
                    {{ t("admin.sidebar.orders") }}
                </Link>

                <Link
                    class="admin__link"
                    :class="{ active: isActive('admin.users.index') }"
                    :href="route('admin.users.index')"
                >
                    {{ t("admin.sidebar.users") }}
                </Link>
            </nav>
        </aside>

        <!-- Content -->
        <div class="admin__content">
            <header class="admin__header">
                <span>{{ t("admin.header.title") }}</span>

                <div class="admin__actions">
                    <!-- Language switch -->
                    <select
                        v-model="currentLang"
                        @change="setLang(currentLang)"
                        class="admin__lang"
                    >
                        <option value="ru">RU</option>
                        <option value="uk">UA</option>
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
import { Link, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { useI18n } from '../lang/useI18n'

const { t, setLang, currentLang } = useI18n()
const page = usePage()

console.log(page)

const isActive = (name) => {
    return page.props?.ziggy?.location?.includes(route(name))
}
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
    gap: 12px;
}

.admin__link {
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
