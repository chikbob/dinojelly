<template>
    <div
        class="product-card"
        :class="{ 'product-card--out-of-stock': !product.is_in_stock }"
        @click="$emit('open', product.id)"
    >
        <img :src="product.image_url" :alt="product.name" class="product-card__image" />

        <button
            v-if="showFavorite"
            class="product-card__favorite"
            type="button"
            @click.stop="$emit('favorite-toggle', product.id)"
        >
            <img
                :src="favorite ? '/images/Favorite.png' : '/images/unFavorite.png'"
                :alt="favorite ? 'favorite' : 'not favorite'"
                class="favorite-icon"
            />
        </button>

        <div class="product-card__content">
            <div v-if="showCategory && categoryLabel" class="product-card__category">
                {{ categoryLabel }}
            </div>

            <div class="product-card__name">{{ product.name }}</div>
            <div class="product-card__weight">{{ product.weight }} г</div>

            <div v-if="recommendationReason" class="product-card__recommendation">
                {{ recommendationReason }}
            </div>

            <div v-if="!product.is_in_stock" class="product-card__stock product-card__stock--out">
                {{ outOfStockLabel }}
            </div>

            <div class="product-card__prices">
                <span class="product-card__price">{{ product.price }} {{ currencySymbol }}</span>
                <span v-if="product.old_price" class="product-card__old-price">
                    {{ product.old_price }} {{ currencySymbol }}
                </span>
            </div>

            <div v-if="cartItem" class="cart-controls">
                <button type="button" class="cart-controls__btn" @click.stop="$emit('decrease', product.id)">-</button>
                <span class="cart-controls__count">{{ cartItem.quantity }}</span>
                <button
                    type="button"
                    class="cart-controls__btn"
                    :disabled="!product.is_in_stock"
                    @click.stop="$emit('increase', product.id)"
                >
                    +
                </button>
            </div>

            <button
                v-else
                type="button"
                class="product-card__button"
                :class="{ 'product-card__button--disabled': !product.is_in_stock }"
                :disabled="!product.is_in_stock"
                @click.stop="$emit('add-to-cart', product.id)"
            >
                {{ product.is_in_stock ? addToCartLabel : outOfStockLabel }}
            </button>
        </div>
    </div>
</template>

<script setup>
defineProps({
    product: {
        type: Object,
        required: true,
    },
    favorite: {
        type: Boolean,
        default: false,
    },
    cartItem: {
        type: Object,
        default: null,
    },
    showFavorite: {
        type: Boolean,
        default: true,
    },
    showCategory: {
        type: Boolean,
        default: false,
    },
    categoryLabel: {
        type: String,
        default: '',
    },
    recommendationReason: {
        type: String,
        default: '',
    },
    addToCartLabel: {
        type: String,
        required: true,
    },
    outOfStockLabel: {
        type: String,
        required: true,
    },
    currencySymbol: {
        type: String,
        required: true,
    },
})

defineEmits(['open', 'favorite-toggle', 'add-to-cart', 'increase', 'decrease'])
</script>

<style scoped lang="scss">
.product-card {
    cursor: pointer;
    position: relative;
    min-width: 0;
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

        .product-card__image {
            filter: grayscale(1);
        }
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

    &__recommendation {
        font-size: 9px;
        line-height: 1.6;
        color: #475569;
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
        flex-wrap: wrap;
    }

    &__price {
        font-size: 14px;
        font-weight: 400;
        color: #ff6b6b;
    }

    &__old-price {
        font-size: 10px;
        text-decoration: line-through;
        color: #aaa;
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

    &__button--disabled,
    &__button:disabled {
        background-color: #9ca3af;
        cursor: not-allowed;

        &:hover,
        &:active {
            background-color: #9ca3af;
            transform: none;
        }
    }
}

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

        &:disabled {
            background-color: #9ca3af;
            cursor: not-allowed;
            transform: none;
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

@media (max-width: 640px) {
    .product-card {
        &__image {
            height: 240px;
        }

        &__content {
            padding: 14px;
        }
    }
}
</style>
