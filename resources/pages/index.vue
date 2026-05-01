<template>
    <MainLayout>
        <div class="catalog">
            <div class="catalog__hero">
                <div>
                    <h1 class="catalog__title">{{ t("catalog.title") }}</h1>
                    <p class="catalog__subtitle">{{ t("catalog.subtitle") }}</p>
                </div>

                <div class="catalog__search" style="width:100%; max-width:100%; min-width:0;">
                    <input
                        v-model="localFilters.q"
                        type="search"
                        style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;"
                        :placeholder="t('catalog.searchPlaceholder')"
                        @keyup.enter="applyFilters"
                    />
                    <button @click="applyFilters" style="width:auto; min-width:96px; max-width:100%; box-sizing:border-box;">{{ t("catalog.search") }}</button>
                </div>
            </div>

            <section class="catalog-ai" style="width:100%; max-width:100%; overflow:hidden;">
                <div class="catalog-ai__intro" style="width:100%; max-width:100%; min-width:0;">
                    <div>
                        <h2>{{ t("catalog.assistant.title") }}</h2>
                        <p>{{ t("catalog.assistant.subtitle") }}</p>
                    </div>
                    <button class="catalog-ai__cta" @click="requestRecommendations" style="width:auto; min-width:132px; max-width:100%; box-sizing:border-box;">
                        {{ t("catalog.assistant.cta") }}
                    </button>
                </div>

                <div class="catalog-ai__grid" style="width:100%; max-width:100%; min-width:0;">
                    <select v-model="assistantForm.occasion" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;">
                        <option value="gift">{{ t("catalog.assistant.occasions.gift") }}</option>
                        <option value="party">{{ t("catalog.assistant.occasions.party") }}</option>
                        <option value="kids">{{ t("catalog.assistant.occasions.kids") }}</option>
                        <option value="self">{{ t("catalog.assistant.occasions.self") }}</option>
                    </select>
                    <select v-model="assistantForm.taste" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;">
                        <option value="sour">{{ t("catalog.assistant.tastes.sour") }}</option>
                        <option value="fruity">{{ t("catalog.assistant.tastes.fruity") }}</option>
                        <option value="light">{{ t("catalog.assistant.tastes.light") }}</option>
                        <option value="surprise">{{ t("catalog.assistant.tastes.surprise") }}</option>
                    </select>
                    <input v-model="assistantForm.budget" type="number" min="0" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" :placeholder="t('catalog.assistant.budget')" />
                    <select v-model="assistantForm.format" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;">
                        <option value="set">{{ t("catalog.assistant.formats.set") }}</option>
                        <option value="single">{{ t("catalog.assistant.formats.single") }}</option>
                        <option value="variety">{{ t("catalog.assistant.formats.variety") }}</option>
                    </select>
                    <select v-model="assistantForm.priority" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;">
                        <option value="popular">{{ t("catalog.assistant.priorities.popular") }}</option>
                        <option value="new">{{ t("catalog.assistant.priorities.new") }}</option>
                        <option value="value">{{ t("catalog.assistant.priorities.value") }}</option>
                    </select>
                </div>

                <p v-if="assistantError" class="catalog-ai__error">{{ assistantError }}</p>

                <div v-if="assistantResult" class="catalog-ai__result">
                    <p class="catalog-ai__summary">{{ formatAssistantSummary(assistantResult.summary) }}</p>
                    <div class="catalog-ai__recommendations" style="width:100%; max-width:100%; min-width:0;">
                        <article v-for="item in assistantResult.products" :key="item.id" class="catalog-ai__card">
                            <img :src="item.image_url" :alt="item.name" class="catalog-ai__image" />
                            <div class="catalog-ai__content">
                                <strong>{{ item.name }}</strong>
                                <p>{{ formatRecommendationReason(item) }}</p>
                                <small>{{ item.price }} {{ t("currency.symbol") }}</small>
                                <div class="catalog-ai__actions">
                                    <button @click="goToProduct(item.id)" style="width:100%; max-width:100%; box-sizing:border-box;">{{ t("catalog.assistant.open") }}</button>
                                    <button @click="addToCart(item.id)" style="width:100%; max-width:100%; box-sizing:border-box;">{{ t("catalog.addToCart") }}</button>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </section>

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
                        {{ getCategoryLabel(category) }}
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

            <div class="catalog__body" :style="catalogBodyStyle">
                <aside
                    class="catalog__filters"
                    style="width:100%; max-width:100%; min-width:0; align-self:start; overflow:hidden; box-sizing:border-box;"
                >
                    <div
                        class="catalog-filter"
                        style="width:100%; max-width:100%; min-width:0; overflow:hidden; box-sizing:border-box;"
                    >
                        <h2>{{ t("catalog.filters.title") }}</h2>

                        <label style="display:grid; width:100%; max-width:100%; min-width:0;">
                            <span>{{ t("catalog.filters.minPrice") }}</span>
                            <input v-model="localFilters.min_price" type="number" min="0" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" />
                        </label>

                        <label style="display:grid; width:100%; max-width:100%; min-width:0;">
                            <span>{{ t("catalog.filters.maxPrice") }}</span>
                            <input v-model="localFilters.max_price" type="number" min="0" style="width:100%; max-width:100%; min-width:0; box-sizing:border-box;" />
                        </label>

                        <label class="catalog-filter__checkbox" style="width:100%; max-width:100%; min-width:0;">
                            <input v-model="localFilters.on_sale" type="checkbox" />
                            <span>{{ t("catalog.filters.onSale") }}</span>
                        </label>

                        <div class="catalog-filter__actions" style="width:100%; max-width:100%; min-width:0;">
                            <button @click="applyFilters" style="width:100%; max-width:100%; box-sizing:border-box;">{{ t("catalog.apply") }}</button>
                            <button class="catalog-filter__reset" @click="resetFilters" style="width:100%; max-width:100%; box-sizing:border-box;">{{ t("catalog.reset") }}</button>
                        </div>
                    </div>
                </aside>

                <div
                    class="catalog__content"
                    style="width:100%; max-width:100%; min-width:0; overflow:hidden; box-sizing:border-box;"
                >
                    <div
                        v-if="products.data.length"
                        class="catalog__grid"
                        style="width:100%; max-width:100%; min-width:0;"
                    >
                        <div
                            v-for="product in products.data"
                            :key="product.id"
                            class="catalog__card product-card"
                            :class="{ 'catalog__card--out-of-stock': !product.is_in_stock }"
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
                                <div v-if="product.category" class="product-card__category">
                                    {{ getCategoryLabel(product.category) }}
                                </div>
                                <div class="product-card__name">{{ product.name }}</div>
                                <div class="product-card__weight">{{ product.weight }} г</div>
                                <div
                                    v-if="!product.is_in_stock"
                                    class="product-card__stock product-card__stock--out"
                                >
                                    {{ t("catalog.outOfStock") }}
                                </div>

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
                                    :class="{ 'product-card__button--disabled': !product.is_in_stock }"
                                    :disabled="!product.is_in_stock"
                                >
                                    {{ product.is_in_stock ? t("catalog.addToCart") : t("catalog.outOfStock") }}
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
import axios from 'axios'
import {router} from '@inertiajs/vue3'
import MainLayout from '../layouts/mainLayout.vue'
import {route} from 'ziggy-js'
import {useI18n} from "../lang/useI18n"
import {computed, onBeforeUnmount, onMounted, reactive, ref, watch} from 'vue'
import Pagination from "../components/pagination.vue";

const {t, currentLang} = useI18n()

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
const assistantResult = ref(null)
const assistantError = ref('')
const isNarrowViewport = ref(false)
const localFilters = reactive({
    q: props.filters?.q ?? '',
    category: props.filters?.category ?? '',
    sort: props.filters?.sort ?? 'new',
    min_price: props.filters?.min_price ?? '',
    max_price: props.filters?.max_price ?? '',
    on_sale: Boolean(props.filters?.on_sale),
})
const assistantForm = reactive({
    occasion: 'gift',
    taste: 'fruity',
    budget: 1500,
    format: 'set',
    priority: 'popular',
})

const syncViewport = () => {
    isNarrowViewport.value = window.innerWidth <= 960
}

onMounted(() => {
    syncViewport()
    window.addEventListener('resize', syncViewport)
})

onBeforeUnmount(() => {
    window.removeEventListener('resize', syncViewport)
})

const catalogBodyStyle = computed(() => ({
    display: 'grid',
    gridTemplateColumns: isNarrowViewport.value ? 'minmax(0, 1fr)' : 'minmax(0, 340px) minmax(0, 1fr)',
    gap: '28px',
    alignItems: 'start',
    width: '100%',
    maxWidth: '100%',
    minWidth: '0',
}))

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

const getCategoryLabel = (category) => {
    const translationKey = `catalog.categories.${category.slug}`
    const translated = t(translationKey)

    return translated === translationKey ? category.name : translated
}

const formatAssistantSummary = (summary) => {
    if (typeof summary === 'string') {
        return summary
    }

    if (!summary?.key) {
        return ''
    }

    const params = {
        ...summary.params,
        occasion: summary.params?.occasion
            ? t(`catalog.assistant.occasions.${summary.params.occasion}`)
            : '',
    }

    return t(summary.key, params)
}

const formatRecommendationReason = (item) => {
    if (Array.isArray(item.recommendation_reason_keys) && item.recommendation_reason_keys.length) {
        return item.recommendation_reason_keys.map((key) => t(key)).join(', ')
    }

    return item.recommendation_reason ?? ''
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

const requestRecommendations = async () => {
    assistantError.value = ''
    try {
        const payload = {
            ...assistantForm,
            locale: currentLang.value,
        }

        const { data } = await axios.post(route('assistant.recommend'), payload, {
            headers: {
                Accept: 'application/json',
            },
        })

        assistantResult.value = data
    } catch (error) {
        assistantError.value = error.response?.data?.message ?? t('catalog.assistant.error')
    }
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
    width: min(100%, 1480px);
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
            padding: 10px 14px;
            border: none;
            border-radius: 12px;
            background: #29cc5f;
            color: #fff;
            cursor: pointer;
            font-size: 10px;
            line-height: 1.2;
        }
    }

    &__toolbar {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-end;
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
        padding: 8px 12px;
        cursor: pointer;
        font-family: inherit;
        font-size: 9px;
        line-height: 1.35;

        &--active {
            background: #111827;
            color: #fff;
            border-color: #111827;
        }
    }

    &__sort {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 9px;

        select {
            padding: 8px 10px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            font-family: inherit;
            font-size: 9px;
        }
    }

    &__body {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 28px;
        align-items: start;
    }

    &__filters {
        width: 100%;
        min-width: 0;
    }

    &__content {
        min-width: 0;
    }

    &__grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
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

        &--out-of-stock {
            opacity: 0.75;
            border-color: #d1d5db;
            background: linear-gradient(180deg, #f8fafc 0%, #f3f4f6 100%);

            &:hover {
                transform: none;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                border-color: #d1d5db;
            }
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

.catalog-ai {
    margin-bottom: 28px;
    padding: 22px;
    border-radius: 22px;
    background: linear-gradient(135deg, #fef3c7, #ecfccb);
    border: 1px solid #d9f99d;
}

.catalog-ai__intro {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    align-items: start;
    margin-bottom: 18px;
}

.catalog-ai__grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
    margin-bottom: 16px;
}

.catalog-ai__grid select,
.catalog-ai__grid input {
    width: 100%;
    min-width: 0;
    box-sizing: border-box;
    min-height: 48px;
    padding: 12px 14px;
    border-radius: 12px;
    border: 1px solid #cbd5e1;
    font-family: inherit;
    font-size: 10px;
    line-height: 1.4;
    background: #fff;
    color: #111827;
    appearance: none;
}

.catalog-ai__cta,
.catalog-ai__actions button {
    border: none;
    border-radius: 12px;
    padding: 10px 12px;
    background: #111827;
    color: #fff;
    cursor: pointer;
    font-family: "Press Start 2P", system-ui;
    font-size: 9px;
    line-height: 1.4;
    text-align: center;
}

.catalog-ai__summary {
    margin-bottom: 14px;
}

.catalog-ai__recommendations {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 14px;
}

.catalog-ai__card {
    background: rgba(255,255,255,0.82);
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid rgba(15, 23, 42, 0.08);
}

.catalog-ai__image {
    width: 100%;
    height: 180px;
    object-fit: cover;
}

.catalog-ai__content {
    padding: 14px;
    display: grid;
    gap: 10px;
}

.catalog-ai__actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.catalog-ai__error {
    color: #b91c1c;
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

    &__stock {
        font-size: 9px;
        line-height: 1.4;
        text-transform: uppercase;
    }

    &__stock--out {
        color: #6b7280;
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

    &__button--disabled {
        background-color: #9ca3af;
        cursor: not-allowed;

        &:hover,
        &:active {
            background-color: #9ca3af;
            transform: none;
        }
    }
}

.catalog__card--out-of-stock .product-card__image {
    filter: grayscale(1);
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
    width: 100%;
    min-width: 0;
    max-width: 100%;
    overflow: hidden;
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
        width: 100%;
        min-width: 0;
        gap: 8px;
        font-size: 10px;
    }

    input {
        display: block;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        box-sizing: border-box;
        min-height: 44px;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        font-family: inherit;
        font-size: 10px;
        line-height: 1.4;
        background: #fff;
        color: #111827;
        appearance: none;
    }

    &__checkbox {
        display: grid !important;
        grid-template-columns: 20px 1fr;
        gap: 10px;
        cursor: pointer;

        input {
            appearance: auto;
            -webkit-appearance: checkbox;
            width: 18px;
            height: 18px;
            min-height: 18px;
            margin: 0;
            align-self: center;
            padding: 0;
            border: none;
            border-radius: 0;
            background: transparent;
        }

        span {
            align-self: center;
            line-height: 1.5;
        }
    }

    &__actions {
        display: grid;
        gap: 10px;

        button {
            width: 100%;
            box-sizing: border-box;
            min-height: 48px;
            padding: 12px;
            border: none;
            border-radius: 12px;
            background: #29cc5f;
            color: #fff;
            cursor: pointer;
            font-family: inherit;
            font-size: 10px;
            line-height: 1.4;
            text-align: center;
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
