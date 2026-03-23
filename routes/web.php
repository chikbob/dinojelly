<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartRecoveryController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Admin\AdminHomeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Load health check routes
require __DIR__.'/health.php';

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

// Главная страница (каталог)
Route::get('/', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/cart/recover/{token}', [CartRecoveryController::class, 'recover'])->name('cart.recover');

/*
|--------------------------------------------------------------------------
| Guest (auth)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

/*
|--------------------------------------------------------------------------
| Auth common
|--------------------------------------------------------------------------
*/

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
});


/*
|--------------------------------------------------------------------------
| Favorites
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/products/{product}/reviews', [ReviewController::class, 'destroy'])->name('reviews.destroy');
});

/*
|--------------------------------------------------------------------------
| Cart
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/increase', [CartController::class, 'increase'])->name('cart.increase');
    Route::post('/cart/decrease', [CartController::class, 'decrease'])->name('cart.decrease');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
});

/*
|--------------------------------------------------------------------------
| Orders & Checkout
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/checkout', [OrderController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [OrderController::class, 'store'])->name('checkout.store');
    Route::get('/payments/mock/{payment}', [PaymentController::class, 'showMock'])->name('payments.mock.show');
    Route::post('/orders/{order}/payments/retry', [PaymentController::class, 'retry'])->name('payments.retry');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/{order}/reorder', [OrderController::class, 'reorder'])->name('orders.reorder');
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('/orders/{order}/subscriptions', [SubscriptionController::class, 'storeFromOrder'])->name('subscriptions.store-from-order');
    Route::put('/subscriptions/{subscription}', [SubscriptionController::class, 'update'])->name('subscriptions.update');
    Route::post('/subscriptions/{subscription}/run', [SubscriptionController::class, 'run'])->name('subscriptions.run');
});

Route::post('/webhooks/payments/mock', [PaymentWebhookController::class, 'mock'])
    ->name('webhooks.payments.mock');

/*
|--------------------------------------------------------------------------
| Admin panel
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard (через контроллер, НЕ closure)
        Route::get('/', [AdminHomeController::class, 'index'])
            ->name('dashboard');

        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class)
            ->except(['show']);
        Route::resource('collections', \App\Http\Controllers\Admin\CollectionController::class)
            ->except(['show']);
        Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);
        Route::resource('orders', \App\Http\Controllers\Admin\OrderController::class)
            ->only(['index', 'show', 'update']);
        Route::post('orders/{order}/notes', [\App\Http\Controllers\Admin\OrderController::class, 'addNote'])
            ->name('orders.notes.store');
        Route::get('payments', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])
            ->name('payments.index');
        Route::put('payments/{payment}', [\App\Http\Controllers\Admin\PaymentController::class, 'update'])
            ->name('payments.update');
        Route::resource('delivery-slots', \App\Http\Controllers\Admin\DeliverySlotController::class)
            ->except(['show']);
        Route::get('inventory', [\App\Http\Controllers\Admin\InventoryController::class, 'index'])
            ->name('inventory.index');
        Route::put('inventory/{inventory}', [\App\Http\Controllers\Admin\InventoryController::class, 'update'])
            ->name('inventory.update');
        Route::get('reviews', [\App\Http\Controllers\Admin\ReviewController::class, 'index'])
            ->name('reviews.index');
        Route::put('reviews/{review}', [\App\Http\Controllers\Admin\ReviewController::class, 'update'])
            ->name('reviews.update');
        Route::delete('reviews/{review}', [\App\Http\Controllers\Admin\ReviewController::class, 'destroy'])
            ->name('reviews.destroy');
        Route::get('recoveries', [\App\Http\Controllers\Admin\CartRecoveryController::class, 'index'])
            ->name('recoveries.index');
        Route::resource('promo-codes', \App\Http\Controllers\Admin\PromoCodeController::class)
            ->except(['show']);
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)
            ->only(['index', 'show']);
    });
