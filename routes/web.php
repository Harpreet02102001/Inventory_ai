<?php

declare(strict_types=1);

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/**
 * Web Routes
 *
 * Route structure follows three tiers:
 *
 * 1. Public routes      — accessible without authentication
 * 2. Authenticated      — requires login + active account (via middleware group)
 * 3. Permission-gated   — requires specific permission on top of authentication
 *
 * Middleware applied in order:
 *   'auth'       → Breeze's built-in: redirects guests to /login
 *   'verified'   → Breeze's built-in: requires email verification (optional)
 *   'permission' → Our custom: checks specific permission via RBAC
 *   'role'       → Our custom: checks role membership
 *
 * EnsureUserIsActive runs automatically on all web requests via
 * bootstrap/app.php — no need to add it here per-group.
 */

// ── Public Routes ─────────────────────────────────────────────────────────────
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// ── Authenticated Routes ───────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function (): void {

    /**
     * Dashboard — accessible to all authenticated users.
     * The dashboard itself renders different content based on permissions.
     * We check 'dashboard.view' permission which all roles have.
     */
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware('permission:dashboard.view')->name('dashboard');

    /**
     * Profile routes — every authenticated user can manage their own profile.
     * No permission check needed — these are personal account settings.
     */
    Route::prefix('profile')->name('profile.')->group(function (): void {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    /**
     * Module routes will be added here as we build each module.
     * Each group will have its own permission middleware.
     *
     * Example structure (to be added per module):
     *
     * Route::prefix('categories')->name('categories.')->middleware('permission:categories.view')->group(function () {
     *     Route::get('/', [CategoryController::class, 'index'])->name('index');
     *     Route::get('/create', [CategoryController::class, 'create'])->middleware('permission:categories.create')->name('create');
     *     Route::post('/', [CategoryController::class, 'store'])->middleware('permission:categories.create')->name('store');
     *     Route::get('/{category}/edit', [CategoryController::class, 'edit'])->middleware('permission:categories.edit')->name('edit');
     *     Route::put('/{category}', [CategoryController::class, 'update'])->middleware('permission:categories.edit')->name('update');
     *     Route::delete('/{category}', [CategoryController::class, 'destroy'])->middleware('permission:categories.delete')->name('destroy');
     * });
     */
});

require __DIR__ . '/auth.php';
