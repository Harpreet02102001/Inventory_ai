<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\StockAdjusted;
use App\Listeners\CheckLowStockListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * AppServiceProvider
 *
 * The general-purpose service provider for application bootstrapping.
 * Registers event → listener mappings so domain events trigger
 * their side effects automatically throughout the system.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * Registers event listeners using the Event facade.
     * When an event is fired anywhere in the application,
     * Laravel automatically dispatches it to all registered listeners.
     *
     * Adding a new side effect = add a new listener here.
     * Zero changes needed to the Action that fires the event.
     * This is the Open/Closed Principle in practice.
     *
     * @return void
     */
    public function boot(): void
    {
        /**
         * StockAdjusted → CheckLowStockListener
         *
         * Every time stock is adjusted (by staff, admin, PO receipt, or sale),
         * CheckLowStockListener checks if the product has dropped below
         * its low_stock_threshold and logs a warning.
         *
         * Future listeners can be added here without touching AdjustStockAction:
         *   Event::listen(StockAdjusted::class, SendLowStockNotificationListener::class);
         *   Event::listen(StockAdjusted::class, UpdateDashboardCacheListener::class);
         */
        Event::listen(StockAdjusted::class, CheckLowStockListener::class);
    }
}
