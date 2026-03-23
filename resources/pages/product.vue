<template>
    <MainLayout>
        <div class="product-page">
            <div class="product">
                <div class="product__gallery">
                    <img :src="product.image_url" :alt="product.name" class="product__image"/>
                </div>

                <div class="product__info">
                    <h1 class="product__title">{{ product.name }}</h1>
                    <p class="product__meta">{{ product.weight }} г</p>
                    <div class="product__rating-summary">
                        <strong>{{ product.average_rating ?? '—' }}</strong>
                        <span>{{ t("reviews.basedOn") }} {{ product.reviews_count }}</span>
                    </div>

                    <div class="product__prices">
                        <span class="product__price">{{ product.price }} {{ t("currency.symbol") }}</span>
                        <span v-if="product.old_price" class="product__old-price">
                            {{ product.old_price }} {{ t("currency.symbol") }}
                        </span>
                    </div>

                    <p class="product__description">{{ product.description }}</p>

                    <!-- Блок с кнопками -->
                    <div class="product__actions">
                        <!-- Избранное -->
                        <button class="product__favorite" @click="toggleFavorite">
                            <img
                                :src="product.is_favorite ? '/images/Favorite.png' : '/images/unFavorite.png'"
                                alt="favorite"
                                width="24"
                                height="24"
                            />
                        </button>

                        <!-- Корзина -->
                        <div v-if="cartItems[product.id]" class="cart-counter">
                            <button class="counter-btn" @click="updateQuantity(-1)">-</button>
                            <span class="counter-value">{{ cartItems[product.id].quantity }}</span>
                            <button class="counter-btn" @click="updateQuantity(1)">+</button>
                        </div>
                        <button v-else class="product__add-to-cart" @click="addToCart">
                            {{ t("product.addToCart") }}
                        </button>
                    </div>
                </div>
            </div>

            <section class="product-reviews">
                <div class="product-reviews__head">
                    <div>
                        <h2>{{ t("reviews.title") }}</h2>
                        <p>{{ t("reviews.subtitle") }}</p>
                    </div>
                </div>

                <form v-if="canReview" class="product-reviews__form" @submit.prevent="submitReview">
                    <div class="product-reviews__rating">
                        <button
                            v-for="star in 5"
                            :key="star"
                            type="button"
                            class="product-reviews__star"
                            :class="{ active: star <= reviewForm.rating }"
                            @click="reviewForm.rating = star"
                        >
                            ★
                        </button>
                    </div>
                    <input
                        v-model="reviewForm.title"
                        type="text"
                        class="product-reviews__input"
                        :placeholder="t('reviews.titlePlaceholder')"
                    />
                    <textarea
                        v-model="reviewForm.body"
                        class="product-reviews__textarea"
                        :placeholder="t('reviews.bodyPlaceholder')"
                    />
                    <div class="product-reviews__form-actions">
                        <button class="product-reviews__submit" type="submit">
                            {{ userReview ? t("reviews.update") : t("reviews.submit") }}
                        </button>
                        <button
                            v-if="userReview"
                            type="button"
                            class="product-reviews__delete"
                            @click="deleteReview"
                        >
                            {{ t("reviews.delete") }}
                        </button>
                    </div>
                </form>

                <p v-else class="product-reviews__gate">
                    {{ t("reviews.gate") }}
                </p>

                <div class="product-reviews__list">
                    <article v-for="review in reviews" :key="review.id" class="product-reviews__card">
                        <div class="product-reviews__card-top">
                            <div>
                                <strong>{{ review.user?.name }}</strong>
                                <small>{{ t("reviews.verifiedPurchase") }}</small>
                            </div>
                            <span>{{ review.rating }}/5</span>
                        </div>
                        <h3 v-if="review.title">{{ review.title }}</h3>
                        <p v-if="review.body">{{ review.body }}</p>
                    </article>
                    <p v-if="!reviews.length" class="product-reviews__empty">{{ t("reviews.empty") }}</p>
                </div>
            </section>
        </div>
    </MainLayout>
</template>

<script setup>
import {router, useForm} from '@inertiajs/vue3'
import MainLayout from '../layouts/mainLayout.vue'
import {route} from "ziggy-js"
import {useI18n} from "../lang/useI18n"

const {t} = useI18n()

const props = defineProps({
    product: Object,
    reviews: Array,
    userReview: Object,
    canReview: Boolean,
    favorites: Array,
    cartItems: Object
})

const reviewForm = useForm({
    rating: props.userReview?.rating ?? 5,
    title: props.userReview?.title ?? '',
    body: props.userReview?.body ?? '',
})

// Добавление в корзину
function addToCart() {
    router.post(route('cart.add'), {
        product_id: props.product.id
    }, {
        preserveScroll: true,
        only: ['cartItems', 'cartCount'],
    })
}

function updateQuantity(change) {
    if (change > 0) {
        router.post(route('cart.increase'), {
            product_id: props.product.id
        }, {
            preserveScroll: true,
            only: ['cartItems', 'cartCount'],
        })
    } else {
        router.post(route('cart.decrease'), {
            product_id: props.product.id
        }, {
            preserveScroll: true,
            only: ['cartItems', 'cartCount'],
        })
    }
}


// Переключение избранного
function toggleFavorite() {
    router.post(route('favorites.toggle'), {
        product_id: props.product.id
    }, {
        preserveScroll: true,
        only: ['favorites', 'product'],
    })
}

function submitReview() {
    reviewForm.post(route('reviews.store', props.product.id), {
        preserveScroll: true,
    })
}

function deleteReview() {
    router.delete(route('reviews.destroy', props.product.id), {
        preserveScroll: true,
    })
}
</script>

<style lang="scss">
.product-page {
    padding: 24px 20px 48px;
}

.product {
    display: grid;
    grid-template-columns: minmax(280px, 480px) 1fr;
    gap: 32px;
    margin-bottom: 40px;
}

.product__rating-summary {
    display: flex;
    gap: 12px;
    align-items: center;
    margin: 10px 0 18px;
    color: #475569;
}

.product__actions {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-top: 20px;
}

.product__favorite {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;

    .favorite-icon {
        width: 28px;
        height: 28px;
        transition: transform 0.2s;
        color: #333;
    }

    .favorite-icon.active {
        fill: red;
    }

    &:hover .favorite-icon {
        transform: scale(1.2);
    }
}

.product__add-to-cart {
    padding: 12px 16px;
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

.cart-counter {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;

    .counter-btn {
        width: 28px;
        height: 28px;
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

    .counter-value {
        font-size: 14px;
        font-weight: 600;
        color: #333;
        min-width: 20px;
        text-align: center;
    }
}

.product-reviews {
    max-width: 1080px;
    margin: 0 auto;
    display: grid;
    gap: 20px;
}

.product-reviews__head p,
.product-reviews__gate,
.product-reviews__empty {
    color: #64748b;
}

.product-reviews__form,
.product-reviews__card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 20px;
}

.product-reviews__rating {
    display: flex;
    gap: 8px;
    margin-bottom: 14px;
}

.product-reviews__star {
    border: none;
    background: transparent;
    font-size: 28px;
    color: #cbd5e1;
    cursor: pointer;
}

.product-reviews__star.active {
    color: #f59e0b;
}

.product-reviews__input,
.product-reviews__textarea {
    width: 100%;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    padding: 12px 14px;
    margin-bottom: 12px;
    font: inherit;
}

.product-reviews__textarea {
    min-height: 120px;
    resize: vertical;
}

.product-reviews__form-actions {
    display: flex;
    gap: 12px;
}

.product-reviews__submit,
.product-reviews__delete {
    border: none;
    border-radius: 12px;
    padding: 12px 16px;
    cursor: pointer;
    font-family: "Press Start 2P", system-ui;
    font-size: 10px;
}

.product-reviews__submit {
    background: #16a34a;
    color: #fff;
}

.product-reviews__delete {
    background: #fee2e2;
    color: #b91c1c;
}

.product-reviews__list {
    display: grid;
    gap: 14px;
}

.product-reviews__card-top {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 12px;
}

.product-reviews__card-top small {
    display: block;
    margin-top: 6px;
    color: #16a34a;
}
</style>
