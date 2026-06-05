<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\ForecastController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : redirect()->route('login'));

Route::get('dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('analytics', [AnalyticsController::class, 'index'])
        ->middleware('permission:analytics.view')
        ->name('analytics.index');
    Route::post('analytics/compile', [AnalyticsController::class, 'compile'])
        ->middleware('permission:analytics.compile')
        ->name('analytics.compile');
    Route::get('analytics/export/{format}', [AnalyticsController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel'])
        ->middleware('permission:analytics.export')
        ->name('analytics.export');

    Route::get('products', [ProductController::class, 'index'])
        ->middleware('permission:products.view')
        ->name('products.index');
    Route::get('products/create', [ProductController::class, 'create'])
        ->middleware('permission:products.create')
        ->name('products.create');
    Route::post('products', [ProductController::class, 'store'])
        ->middleware('permission:products.create')
        ->name('products.store');
    Route::get('products/{product}', [ProductController::class, 'show'])
        ->middleware('permission:products.view')
        ->name('products.show');
    Route::get('products/{product}/edit', [ProductController::class, 'edit'])
        ->middleware('permission:products.update')
        ->name('products.edit');
    Route::put('products/{product}', [ProductController::class, 'update'])
        ->middleware('permission:products.update')
        ->name('products.update');
    Route::patch('products/{product}/archive', [ProductController::class, 'archive'])
        ->middleware('permission:products.archive')
        ->name('products.archive');
    Route::post('products/{product}/movements', [StockMovementController::class, 'store'])
        ->middleware('permission:stock.entry|stock.exit|stock.adjustment')
        ->name('products.movements.store');

    Route::get('categories', [CategoryController::class, 'index'])
        ->middleware('permission:categories.view')
        ->name('categories.index');
    Route::get('categories/create', [CategoryController::class, 'create'])
        ->middleware('permission:categories.create')
        ->name('categories.create');
    Route::post('categories', [CategoryController::class, 'store'])
        ->middleware('permission:categories.create')
        ->name('categories.store');
    Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])
        ->middleware('permission:categories.update')
        ->name('categories.edit');
    Route::put('categories/{category}', [CategoryController::class, 'update'])
        ->middleware('permission:categories.update')
        ->name('categories.update');
    Route::patch('categories/{category}/archive', [CategoryController::class, 'archive'])
        ->middleware('permission:categories.update')
        ->name('categories.archive');

    Route::get('suppliers', [SupplierController::class, 'index'])
        ->middleware('permission:suppliers.view')
        ->name('suppliers.index');
    Route::get('suppliers/create', [SupplierController::class, 'create'])
        ->middleware('permission:suppliers.create')
        ->name('suppliers.create');
    Route::post('suppliers', [SupplierController::class, 'store'])
        ->middleware('permission:suppliers.create')
        ->name('suppliers.store');
    Route::get('suppliers/{supplier}/edit', [SupplierController::class, 'edit'])
        ->middleware('permission:suppliers.update')
        ->name('suppliers.edit');
    Route::put('suppliers/{supplier}', [SupplierController::class, 'update'])
        ->middleware('permission:suppliers.update')
        ->name('suppliers.update');
    Route::patch('suppliers/{supplier}/archive', [SupplierController::class, 'archive'])
        ->middleware('permission:suppliers.update')
        ->name('suppliers.archive');

    Route::get('inventories', [InventoryController::class, 'index'])
        ->middleware('permission:inventories.view')
        ->name('inventories.index');
    Route::get('inventories/create', [InventoryController::class, 'create'])
        ->middleware('permission:inventories.create')
        ->name('inventories.create');
    Route::post('inventories', [InventoryController::class, 'store'])
        ->middleware('permission:inventories.create')
        ->name('inventories.store');
    Route::get('inventories/{inventory}', [InventoryController::class, 'show'])
        ->middleware('permission:inventories.view')
        ->name('inventories.show');
    Route::put('inventories/{inventory}', [InventoryController::class, 'update'])
        ->middleware('permission:inventories.create')
        ->name('inventories.update');
    Route::post('inventories/{inventory}/validate', [InventoryController::class, 'validateInventory'])
        ->middleware('permission:inventories.validate')
        ->name('inventories.validate');

    Route::get('alerts', [AlertController::class, 'index'])
        ->middleware('permission:alerts.view')
        ->name('alerts.index');
    Route::get('alerts/{alert}', [AlertController::class, 'show'])
        ->middleware('permission:alerts.view')
        ->name('alerts.show');
    Route::patch('alerts/{alert}/resolve', [AlertController::class, 'resolve'])
        ->middleware('permission:alerts.resolve')
        ->name('alerts.resolve');

    Route::get('forecasts', [ForecastController::class, 'index'])
        ->middleware('permission:forecasts.view')
        ->name('forecasts.index');
    Route::post('forecasts/refresh', [ForecastController::class, 'refresh'])
        ->middleware('permission:forecasts.view')
        ->name('forecasts.refresh');
    Route::get('forecasts/{forecast}', [ForecastController::class, 'show'])
        ->middleware('permission:forecasts.view')
        ->name('forecasts.show');

    Route::get('reports', [ReportController::class, 'index'])
        ->middleware('permission:reports.view')
        ->name('reports.index');
    Route::get('reports/{report}/{format}', [ReportController::class, 'export'])
        ->whereIn('report', ['stock', 'critical-products', 'movements', 'inventories', 'forecasts'])
        ->whereIn('format', ['pdf', 'excel'])
        ->middleware('permission:reports.export')
        ->name('reports.export');

    Route::get('users', [UserController::class, 'index'])
        ->middleware('permission:users.view')
        ->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])
        ->middleware('permission:users.create')
        ->name('users.create');
    Route::post('users', [UserController::class, 'store'])
        ->middleware('permission:users.create')
        ->name('users.store');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])
        ->middleware('permission:users.update')
        ->name('users.edit');
    Route::put('users/{user}', [UserController::class, 'update'])
        ->middleware('permission:users.update')
        ->name('users.update');
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
        ->middleware('permission:users.disable')
        ->name('users.toggle-status');

    Route::get('roles', [RoleController::class, 'index'])
        ->middleware('permission:roles.view')
        ->name('roles.index');
    Route::get('roles/create', [RoleController::class, 'create'])
        ->middleware('permission:roles.manage')
        ->name('roles.create');
    Route::post('roles', [RoleController::class, 'store'])
        ->middleware('permission:roles.manage')
        ->name('roles.store');
    Route::get('roles/{role}/edit', [RoleController::class, 'edit'])
        ->middleware('permission:roles.manage')
        ->name('roles.edit');
    Route::put('roles/{role}', [RoleController::class, 'update'])
        ->middleware('permission:roles.manage')
        ->name('roles.update');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
