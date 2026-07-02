<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\StockAdjusted;

/**
 * CheckLowStockListener
 *
 * Listens for StockAdjusted events and checks whether the affected
 * product has dropped to or below its configured low stock threshold.
 *
 * Currently logs a warning to the Laravel log file.
 * In a future iteration, this can be extended to:
 *   - Send an email notification to the admin
 *   - Create an in-app notification record
 *   - Trigger a push notification
 *   - Update a dashboard cache entry
 * All without modifying this listener or the AdjustStockAction.
 *
 * Registered in: AppServiceProvider::boot()
 */
class CheckLowStockListener
{
    /**
     * Handle the StockAdjusted event.
     *
     * Reloads the product fresh from the database to get the actual
     * post-adjustment quantity (the event's product may have stale data
     * if the model was not refreshed after the stock update).
     *
     * @param  StockAdjusted  $event  The fired event with product + history
     * @return void
     */
    public function handle(StockAdjusted $event): void
    {
        // fresh() ensures we have the latest stock_quantity from DB
        $product = $event->product->fresh();

        if ($product->isLowStock()) {
            logger()->warning('Low stock alert', [
                'product_id'        => $product->id,
                'product_name'      => $product->name,
                'sku'               => $product->sku,
                'current_stock'     => $product->stock_quantity,
                'low_stock_threshold' => $product->low_stock_threshold,
                'change_type'       => $event->history->type,
                'changed_by_user_id' => $event->history->user_id,
            ]);
        }
    }
}
