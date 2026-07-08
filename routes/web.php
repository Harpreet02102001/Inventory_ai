<?php

declare(strict_types=1);

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;

/**
 * Web Routes
 *
 * Route structure follows three tiers:
 *
 * 1. Public routes     → accessible without authentication
 * 2. Authenticated     → requires valid login + active account
 * 3. Permission-gated  → requires specific permission via our RBAC
 *
 * Middleware execution order on authenticated routes:
 *   auth             → redirects unauthenticated users to /login (Breeze)
 *   EnsureUserIsActive → blocks deactivated accounts (runs via bootstrap/app.php)
 *   permission:*     → checks specific permission via AuthServiceProvider Gates
 *
 * Convention: modules are added here one by one as they are built.
 * Each module gets its own named route group with permission middleware.
 */

// ── Public: redirect root to login ────────────────────────────────────────────
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// ── Authenticated Routes ───────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function (): void {

    /**
     * Dashboard
     *
     * All authenticated roles have 'dashboard.view' permission (seeded).
     * The view itself renders different widgets based on the user's permissions,
     * so admin sees full stats while staff sees only low stock + recent updates.
     */
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware('permission:dashboard.view')->name('dashboard');

    /**
     * Profile
     *
     * Every authenticated user can view and update their own profile.
     * No permission check needed — this is personal account management,
     * not a business module requiring role-based access.
     */
    Route::prefix('profile')->name('profile.')->group(function (): void {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('categories')->name('categories.')->group(function (): void {
        Route::get('/', [CategoryController::class, 'index'])
            ->name('index')->middleware('permission:categories.view');

        Route::get('/create', [CategoryController::class, 'create'])
            ->name('create')->middleware('permission:categories.create');

        Route::post('/', [CategoryController::class, 'store'])
            ->name('store')->middleware('permission:categories.create');

        Route::get('/{category}', [CategoryController::class, 'show'])
            ->name('show')->middleware('permission:categories.view');

        Route::get('/{category}/edit', [CategoryController::class, 'edit'])
            ->name('edit')->middleware('permission:categories.edit');

        Route::put('/{category}', [CategoryController::class, 'update'])
            ->name('update')->middleware('permission:categories.edit');

        Route::delete('/{category}', [CategoryController::class, 'destroy'])
            ->name('destroy')->middleware('permission:categories.delete');
    });

    Route::prefix('suppliers')->name('suppliers.')->group(function (): void {
        Route::get('/', [SupplierController::class, 'index'])
            ->name('index')->middleware('permission:suppliers.view');

        Route::get('/create', [SupplierController::class, 'create'])
            ->name('create')->middleware('permission:suppliers.create');

        Route::post('/', [SupplierController::class, 'store'])
            ->name('store')->middleware('permission:suppliers.create');

        Route::get('/{supplier}', [SupplierController::class, 'show'])
            ->name('show')->middleware('permission:suppliers.view');

        Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])
            ->name('edit')->middleware('permission:suppliers.edit');

        Route::put('/{supplier}', [SupplierController::class, 'update'])
            ->name('update')->middleware('permission:suppliers.edit');

        Route::delete('/{supplier}', [SupplierController::class, 'destroy'])
            ->name('destroy')->middleware('permission:suppliers.delete');
    });

    Route::prefix('products')->name('products.')->group(function (): void {
        Route::get('/', [ProductController::class, 'index'])
            ->name('index')->middleware('permission:products.view');

        Route::get('/create', [ProductController::class, 'create'])
            ->name('create')->middleware('permission:products.create');

        Route::post('/', [ProductController::class, 'store'])
            ->name('store')->middleware('permission:products.create');

        Route::get('/{product}', [ProductController::class, 'show'])
            ->name('show')->middleware('permission:products.view');

        Route::get('/{product}/edit', [ProductController::class, 'edit'])
            ->name('edit')->middleware('permission:products.edit');

        Route::put('/{product}', [ProductController::class, 'update'])
            ->name('update')->middleware('permission:products.edit');

        Route::delete('/{product}', [ProductController::class, 'destroy'])
            ->name('destroy')->middleware('permission:products.delete');
    });


    Route::prefix('purchase-orders')->name('purchase_orders.')->group(function (): void {
        Route::get('/', [PurchaseOrderController::class, 'index'])
            ->name('index')->middleware('permission:purchase_orders.view');

        Route::get('/create', [PurchaseOrderController::class, 'create'])
            ->name('create')->middleware('permission:purchase_orders.create');

        Route::post('/', [PurchaseOrderController::class, 'store'])
            ->name('store')->middleware('permission:purchase_orders.create');

        Route::get('/{purchaseOrder}', [PurchaseOrderController::class, 'show'])
            ->name('show')->middleware('permission:purchase_orders.view');

        Route::patch('/{purchaseOrder}/mark-as-ordered', [PurchaseOrderController::class, 'markAsOrdered'])
            ->name('mark-as-ordered')->middleware('permission:purchase_orders.edit');

        Route::patch('/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])
            ->name('receive')->middleware('permission:purchase_orders.edit');

        Route::patch('/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])
            ->name('cancel')->middleware('permission:purchase_orders.edit');
    });

    Route::prefix('sales')->name('sales.')->group(function (): void {
        Route::get('/', [SaleController::class, 'index'])
            ->name('index')->middleware('permission:sales.view');

        Route::get('/create', [SaleController::class, 'create'])
            ->name('create')->middleware('permission:sales.create');

        Route::post('/', [SaleController::class, 'store'])
            ->name('store')->middleware('permission:sales.create');

        Route::get('/{sale}', [SaleController::class, 'show'])
            ->name('show')->middleware('permission:sales.view');

        Route::patch('/{sale}/confirm', [SaleController::class, 'confirm'])
            ->name('confirm')->middleware('permission:sales.edit');

        Route::patch('/{sale}/cancel', [SaleController::class, 'cancel'])
            ->name('cancel')->middleware('permission:sales.edit');
    });
    Route::prefix('roles')->name('roles.')->group(function (): void {
        Route::get('/', [RoleController::class, 'index'])->name('index')->middleware('permission:roles.view');
        Route::get('/create', [RoleController::class, 'create'])->name('create')->middleware('permission:roles.create');
        Route::post('/', [RoleController::class, 'store'])->name('store')->middleware('permission:roles.create');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit')->middleware('permission:roles.edit');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update')->middleware('permission:roles.edit');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy')->middleware('permission:roles.delete');
    });

    Route::prefix('users')->name('users.')->group(function (): void {
        Route::get('/', [UserController::class, 'index'])->name('index')->middleware('permission:users.view');
        Route::get('/create', [UserController::class, 'create'])->name('create')->middleware('permission:users.create');
        Route::post('/', [UserController::class, 'store'])->name('store')->middleware('permission:users.create');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit')->middleware('permission:users.edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update')->middleware('permission:users.edit');
    });

    // ── Module routes are added below as each module is built ─────────────────
    // Categories  → added when CategoryController is built
    // Suppliers   → added when SupplierController is built
    // Products    → added when ProductController is built
    // Stock       → added when StockController is built
    // Users       → added when UserController is built
    // Roles       → added when RoleController is built
    // Reports     → added when ReportController is built

});

require __DIR__ . '/auth.php';
