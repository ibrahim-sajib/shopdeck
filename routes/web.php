<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\ShopController;
use App\Http\Controllers\Admin\ShopOwnerController;
use App\Http\Controllers\Owner\CatalogController;
use App\Http\Controllers\Owner\OwnerDashboardController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('owner.dashboard');
    })->name('dashboard');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Shop Management
    Route::get('/shops', [ShopController::class, 'index'])->name('shops.index');
    Route::get('/shops/create', [ShopController::class, 'create'])->name('shops.create');
    Route::post('/shops', [ShopController::class, 'store'])->name('shops.store');
    Route::get('/shops/{shop}/edit', [ShopController::class, 'edit'])->name('shops.edit');
    Route::put('/shops/{shop}', [ShopController::class, 'update'])->name('shops.update');
    Route::delete('/shops/{shop}', [ShopController::class, 'destroy'])->name('shops.destroy');
    Route::post('/shops/{shop}/toggle-status', [ShopController::class, 'toggleStatus'])->name('shops.toggle-status');

    // Shop Owner Management
    Route::get('/shop-owners', [ShopOwnerController::class, 'index'])->name('shop-owners.index');
    Route::get('/shop-owners/create', [ShopOwnerController::class, 'create'])->name('shop-owners.create');
    Route::post('/shop-owners', [ShopOwnerController::class, 'store'])->name('shop-owners.store');
    Route::get('/shop-owners/{user}/edit', [ShopOwnerController::class, 'edit'])->name('shop-owners.edit');
    Route::put('/shop-owners/{user}', [ShopOwnerController::class, 'update'])->name('shop-owners.update');
    Route::delete('/shop-owners/{user}', [ShopOwnerController::class, 'destroy'])->name('shop-owners.destroy');
    Route::post('/shop-owners/{user}/toggle-status', [ShopOwnerController::class, 'toggleStatus'])->name('shop-owners.toggle-status');

    // Global Product Management
    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
    Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');
    Route::post('/products/{product}/toggle-status', [AdminProductController::class, 'toggleStatus'])->name('products.toggle-status');
});

// Shop Owner Routes
Route::middleware(['auth', 'shop_owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [OwnerDashboardController::class, 'index'])->name('dashboard');

    // Shared Catalog
    Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
    Route::post('/catalog/{product}/add', [CatalogController::class, 'add'])->name('catalog.add');
});

require __DIR__.'/settings.php';
