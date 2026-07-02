<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidStatusTransitionException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /**
         * Append EnsureUserIsActive to the 'web' group.
         *
         * Every web request passes through this group, so active account
         * checking happens automatically on all routes — no need to add
         * it individually to every route or group.
         */
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        /**
         * Register named middleware aliases.
         *
         * Aliases allow short names in route definitions instead of
         * full class names. The string before => is what you use in routes.
         *
         * Usage: ->middleware('permission:products.create')
         *        ->middleware('role:admin|manager')
         */
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'role'       => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /**
         * Handle domain exceptions with user-friendly responses.
         *
         * Instead of a generic 500 error page, these exceptions redirect
         * back with clear, actionable error messages the user can respond to.
         * JSON responses are returned automatically for API/AJAX requests.
         */
        $exceptions->render(function (
            InsufficientStockException $e,
            \Illuminate\Http\Request $request
        ) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getUserMessage()], 422);
            }
            return back()->withErrors(['stock' => $e->getUserMessage()])->withInput();
        });

        $exceptions->render(function (
            InvalidStatusTransitionException $e,
            \Illuminate\Http\Request $request
        ) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getUserMessage()], 422);
            }
            return back()->withErrors(['status' => $e->getUserMessage()])->withInput();
        });
    })
    ->create();
