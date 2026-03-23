<template>
    <MainLayout>
        <div class="catalog">
            <div class="catalog__hero">
                <div>
                    <h1 class="catalog__title">{{ t("catalog.title") }}</h1>
                    <p class="catalog__subtitle">{{ t("catalog.subtitle") }}</p>
                </div>

                <div class="catalog__search">
                    <input
                        v-model="localFilters.q"
                        type="search"
                        :placeholder="t('catalog.searchPlaceholder')"
                        @keyup.enter="applyFilters"
                    />
                    <button @click="applyFilters">{{ t("catalog.search") }}</button>
                </div>
            </div>

            <div class="catalog__toolbar">
                <div class="catalog__categories">
                    <button
                        class="catalog__category"
                        :class="{ 'catalog__category--active': !localFilters.category }"
                        @click="selectCategory('')"
                    >
                        {{ t("catalog.allCategories") }}
                    </button>
                    <button
                        v-for="category in categories"
                        :key="category.id"
                        class="catalog__category"
                        :class="{ 'catalog__category--active': localFilters.category === category.slug }"
                        @click="selectCategory(category.slug)"
                    >
                        {{ category.name }}
                    </button>
                </div>

                <div class="catalog__sort">
                    <label for="sort">{{ t("catalog.sort") }}</label>
                    <select id="sort" v-model="localFilters.sort" @change="applyFilters">
                        <option value="new">{{ t("catalog.sortOptions.new") }}</option>
                        <option value="popular">{{ t("catalog.sortOptions.popular") }}</option>
                        <option value="price_asc">{{ t("catalog.sortOptions.priceAsc") }}</option>
                        <option value="price_desc">{{ t("catalog.sortOptions.priceDesc") }}</option>
                        <option value="name_asc">{{ t("catalog.sortOptions.nameAsc") }}</option>
                    </select>
                </div>
            </div>

            <div class="catalog__body">
                <aside class="catalog__filters">
                    <div class="catalog-filter">
                        <h2>{{ t("catalog.filters.title") }}</h2>

                        <label>
                            <span>{{ t("catalog.filters.minPrice") }}</span>
                            <input v-model="localFilters.min_price" type="number" min="0" />
                        </label>

                        <label>
                            <span>{{ t("catalog.filters.maxPrice") }}</span>
                            <input v-model="localFilters.max_price" type="number" min="0" />
                        </label>

                        <label class="catalog-filter__checkbox">
                            <input v-model="localFilters.on_sale" type="checkbox" />
                            <span>{{ t("catalog.filters.onSale") }}</span>
                        </label>

                        <div class="catalog-filter__actions">
                            <button @click="applyFilters">{{ t("catalog.apply") }}</button>
                            <button class="catalog-filter__reset" @click="resetFilters">{{ t("catalog.reset") }}</button>
                        </div>
                    </div>
                </aside>

                <div class="catalog__content">
                    <div v-if="products.data.length" class="catalog__grid">
                        <div
                            v-for="product in products.data"
                            :key="product.id"
                            class="catalog__card product-card"
                            @click="goToProduct(product.id)"
                        >
                            <img :src="product.image_url" :alt="product.name" class="product-card__image"/>

                            <button
                                @click.stop="toggleFavorite(product.id)"
                                class="product-card__favorite"
                            >
                                <img
                                    :src="favorites.includes(product.id) ? '/images/Favorite.png' : '/images/unFavorite.png'"
                                    alt="favorite"
                                    class="favorite-icon"
                                />
                            </button>

                            <div class="product-card__content">
                                <div v-if="product.category?.name" class="product-card__category">
                                    {{ product.category.name }}
                                </div>
                                <div class="product-card__name">{{ product.name }}</div>
                                <div class="product-card__weight">{{ product.weight }} г</div>

                                <div class="product-card__prices">
                                    <span class="product-card__price">{{ product.price }} {{ t("currency.symbol") }}</span>
                                    <span v-if="product.old_price" class="product-card__old-price">
                                        {{ product.old_price }} {{ t("currency.symbol") }}
                                    </span>
                                </div>

                                <div v-if="cart[product.id]" class="cart-controls">
                                    <button
                                        @click.stop="decreaseQuantity(product.id)"
                                        class="cart-controls__btn"
                                    >-
                                    </button>
                                    <span class="cart-controls__count">{{ cart[product.id].quantity }}</span>
                                    <button
                                        @click.stop="increaseQuantity(product.id)"
                                        class="cart-controls__btn"
                                    >+
                                    </button>
                                </div>

                                <button
                                    v-else
                                    @click.stop="addToCart(product.id)"
                                    class="product-card__button"
                                >
                                    {{ t("catalog.addToCart") }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-else class="catalog__empty">
                        {{ t("catalog.empty") }}
                    </div>

                    <Pagination class="product-card__pagination" :links="products.links"/>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import {router} from '@inertiajs/vue3'
import MainLayout from '../layouts/mainLayout.vue'
import {route} from 'ziggy-js'
import {useI18n} from "../lang/useI18n"
import {reactive, ref, watch} from 'vue'
import Pagination from "../components/pagination.vue";

const {t} = useI18n()

const props = defineProps({
    products: Object, // { data: [...], links: [...] }
    categories: Array,
    filters: Object,
    favorites: Array,
    cartItems: {
        type: Object,
        default: () => ({})
    }
})

// локальная реактивная корзина
const cart = ref({...props.cartItems})
const localFilters = reactive({
    q: props.filters?.q ?? '',
    category: props.filters?.category ?? '',
    sort: props.filters?.sort ?? 'new',
    min_price: props.filters?.min_price ?? '',
    max_price: props.filters?.max_price ?? '',
    on_sale: Boolean(props.filters?.on_sale),
})

watch(() => props.filters, (next) => {
    localFilters.q = next?.q ?? ''
    localFilters.category = next?.category ?? ''
    localFilters.sort = next?.sort ?? 'new'
    localFilters.min_price = next?.min_price ?? ''
    localFilters.max_price = next?.max_price ?? ''
    localFilters.on_sale = Boolean(next?.on_sale)
}, {deep: true})

const buildQuery = () => {
    const query = {
        q: localFilters.q || undefined,
        category: localFilters.category || undefined,
        sort: localFilters.sort || undefined,
        min_price: localFilters.min_price || undefined,
        max_price: localFilters.max_price || undefined,
        on_sale: localFilters.on_sale ? 1 : undefined,
    }

    return query
}

const applyFilters = () => {
    router.get(route('products.index'), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

const resetFilters = () => {
    localFilters.q = ''
    localFilters.category = ''
    localFilters.sort = 'new'
    localFilters.min_price = ''
    localFilters.max_price = ''
    localFilters.on_sale = false
    applyFilters()
}

const selectCategory = (slug) => {
    localFilters.category = slug
    applyFilters()
}

const goToProduct = (id) => {
    router.visit(`/products/${id}`)
}

const addToCart = (productId) => {
    router.post(route('cart.add'), {product_id: productId}, {
        preserveScroll: true,
        preserveState: true,
        only: ['cartItems', 'cartCount'],
        onSuccess: () => {
            if (cart.value[productId]) {
                cart.value[productId].quantity++
            } else {
                cart.value[productId] = {id: productId, quantity: 1}
            }
        }
    })
}

const increaseQuantity = (productId) => {
    router.post(route('cart.increase'), {product_id: productId}, {
        preserveScroll: true,
        preserveState: true,
        only: ['cartItems', 'cartCount'],
        onSuccess: () => {
            if (cart.value[productId]) {
                cart.value[productId].quantity++
            }
        }
    })
}

const decreaseQuantity = (productId) => {
    router.post(route('cart.decrease'), {product_id: productId}, {
        preserveScroll: true,
        preserveState: true,
        only: ['cartItems', 'cartCount'],
        onSuccess: () => {
            if (cart.value[productId]) {
                cart.value[productId].quantity--
                if (cart.value[productId].quantity <= 0) {
                    delete cart.value[productId]
                }
            }
        }
    })
}

const toggleFavorite = (productId) => {
    router.post(route('favorites.toggle'), {product_id: productId}, {
        preserveScroll: true,
        preserveState: true,
    })
}
</script>

<style lang="scss" scoped>
.catalog {
    padding: 40px 20px;
    max-width: 1200px;
    margin: 0 auto;
    font-family: "Press Start 2P", system-ui;

    &__hero {
        display: flex;
        justify-content: space-between;
        gap: 24px;
        align-items: end;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    &__title {
        font-size: 24px;
        font-weight: 400;
        margin-bottom: 12px;
        color: #333;
    }

    &__subtitle {
        max-width: 640px;
        color: #6b7280;
        font-size: 12px;
        line-height: 1.6;
    }

    &__search {
        display: flex;
        gap: 12px;
        align-items: center;

        input {
            min-width: 280px;
            padding: 12px 14px;
            border: 2px solid #d1fae5;
            border-radius: 12px;
            font-family: inherit;
        }

        button {
            padding: 12px 16px;
            border: none;
            border-radius: 12px;
            background: #29cc5f;
            color: #fff;
            cursor: pointer;
        }
    }

    &__toolbar {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    &__categories {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    &__category {
        border: 1px solid #d1d5db;
        background: #fff;
        border-radius: 999px;
        padding: 10px 14px;
        cursor: pointer;
        font-family: inherit;
        font-size: 10px;

        &--active {
            background: #111827;
            color: #fff;
            border-color: #111827;
        }
    }

    &__sort {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 10px;

        select {
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            font-family: inherit;
        }
    }

    &__body {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 28px;
        align-items: start;
    }

    &__content {
        min-width: 0;
    }

    &__grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 28px;
    }

    &__card {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 2px solid #eaeaea;

        &:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
            border-color: #3ecf8e;
        }
    }

    &__empty {
        padding: 48px 24px;
        text-align: center;
        color: #6b7280;
        background: #fff;
        border: 1px dashed #d1d5db;
        border-radius: 20px;
    }
}

.product-card {
    cursor: pointer;
    position: relative;

    &__pagination {
        display: flex;
        justify-content: center;
    }

    &__favorite {
        position: absolute;
        top: 10px;
        right: 10px;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;

        .favorite-icon {
            width: 22px;
            height: 22px;
            transition: transform 0.2s;
        }

        &:hover .favorite-icon {
            transform: scale(1.2);
        }
    }

    &__image {
        width: 100%;
        height: 300px;
        object-fit: cover;
        border-bottom: 2px solid #eaeaea;
    }

    &__content {
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        flex-grow: 1;
    }

    &__category {
        display: inline-flex;
        align-self: flex-start;
        padding: 6px 10px;
        border-radius: 999px;
        background: #ecfdf5;
        color: #047857;
        font-size: 9px;
    }

    &__name {
        font-size: 12px;
        font-weight: 400;
        color: #333;
        line-height: 1.4;
        min-height: 34px;
    }

    &__weight {
        font-size: 10px;
        color: #777;
    }

    &__prices {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: nowrap;
        white-space: nowrap;
    }

    &__price {
        font-size: 14px;
        font-weight: 400;
        color: #ff6b6b;
        white-space: nowrap;
    }

    &__old-price {
        font-size: 10px;
        text-decoration: line-through;
        color: #aaa;
        white-space: nowrap;
    }

    &__button {
        margin-top: auto;
        padding: 12px;
        background-color: #3ecf8e;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: "Press Start 2P", system-ui;
        font-size: 10px;
        letter-spacing: 0.5px;

        &:hover {
            background-color: #2ebd7d;
            transform: translateY(-2px);
        }

        &:active {
            transform: translateY(0);
        }
    }
}

/* ✅ Счетчик */
.cart-controls {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-top: auto;

    &__btn {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: none;
        background-color: #3ecf8e;
        color: white;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.2s ease;

        &:hover {
            background-color: #2ebd7d;
            transform: scale(1.05);
        }
    }

    &__count {
        font-size: 17px;
        font-weight: 600;
        color: #333;
        min-width: 20px;
        text-align: center;
    }
}

.catalog-filter {
    position: sticky;
    top: 24px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    padding: 20px;
    display: grid;
    gap: 16px;

    h2 {
        margin: 0;
        font-size: 14px;
    }

    label {
        display: grid;
        gap: 8px;
        font-size: 10px;
    }

    input {
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        font-family: inherit;
    }

    &__checkbox {
        display: flex !important;
        align-items: center;
        gap: 10px;
    }

    &__actions {
        display: grid;
        gap: 10px;

        button {
            padding: 12px;
            border: none;
            border-radius: 10px;
            background: #29cc5f;
            color: #fff;
            cursor: pointer;
            font-family: inherit;
        }
    }

    &__reset {
        background: #e5e7eb !important;
        color: #111827 !important;
    }
}

@media (max-width: 960px) {
    .catalog {
        &__body {
            grid-template-columns: 1fr;
        }

        &__search {
            width: 100%;

            input {
                min-width: 0;
                width: 100%;
            }
        }
    }

    .catalog-filter {
        position: static;
    }
}
</style>
