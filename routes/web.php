<?php

declare(strict_types=1);

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

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
