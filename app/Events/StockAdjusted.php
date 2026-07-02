<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Product;
use App\Models\StockHistory;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * StockAdjusted Event
 *
 * Fired by AdjustStockAction whenever a stock change is successfully
 * recorded. Carries the affected product and the stock history record
 * so listeners have full context without additional DB queries.
 *
 * Listeners registered in AppServiceProvider::boot():
 *   → CheckLowStockListener
 *
 * How to fire this event:
 *   StockAdjusted::dispatch($product, $history);
 *   // or:
 *   event(new StockAdjusted($product, $history));
 */
class StockAdjusted
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a StockAdjusted event instance.
     *
     * Both parameters are public readonly — listeners access them
     * directly as $event->product and $event->history. No getters needed.
     *
     * @param  Product       $product  The product whose stock changed
     * @param  StockHistory  $history  The immutable audit record of this change
     */
    public function __construct(
        public readonly Product $product,
        public readonly StockHistory $history,
    ) {}
}
