<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\StockHistory;
use App\Repositories\ProductRepository;
use App\Repositories\StockHistoryRepository;
use Illuminate\Support\Facades\DB;

/**
 * StockService
 *
 * Owns the business logic for adjusting a product's stock: locking the
 * row, calculating the new quantity, enforcing "never negative," and
 * recording the change as an atomic unit. The transaction boundary
 * lives HERE (not in the controller) because this whole sequence — the
 * lock, the calculation, both writes — is one indivisible business
 * operation, not something the controller should need to know the
 * internals of.
 */
class StockService
{
    /**
     * @param ProductRepository $products
     * @param StockHistoryRepository $stockHistories
     */
    public function __construct(
        private readonly ProductRepository $products,
        private readonly StockHistoryRepository $stockHistories,
    ) {}

    /**
     * Adjust a product's stock quantity and record the change, atomically.
     *
     * @param int $productId The product being adjusted
     * @param string $type Either 'add', 'reduce', 'purchase', or 'sale'
     * @param int $quantity The amount to add or reduce (always a positive number)
     * @param int $userId The authenticated user making this change
     * @param string|null $remarks Optional note explaining the change
     * @return StockHistory The newly created, immutable audit record
     *
     * @throws InsufficientStockException If this change would take stock below zero
     */
    public function adjustStock(
        int $productId,
        string $type,
        int $quantity,
        int $userId,
        ?string $remarks = null,
    ): StockHistory {
        return DB::transaction(function () use ($productId, $type, $quantity, $userId, $remarks) {
            // Row lock held until this transaction commits or rolls back —
            // prevents two concurrent requests from both reading the same
            // stale stock_quantity and silently overwriting each other.
            $product = $this->products->findForUpdate($productId);

            $oldQuantity = $product->stock_quantity;

            // 'add' and 'purchase' both increase stock; 'reduce' and
            // 'sale' both decrease it — grouped this way so Purchase
            // Orders and (soon) Sales can reuse this exact method.
            $increases = in_array($type, ['add', 'purchase'], true);

            $newQuantity = $increases
                ? $oldQuantity + $quantity
                : $oldQuantity - $quantity;

            // Checked against the LOCKED, current value — not whatever
            // was on screen when the form was rendered, which could
            // already be stale by the time this request runs.
            if ($newQuantity < 0) {
                throw new InsufficientStockException(
                    "Cannot reduce stock by {$quantity} — only {$oldQuantity} currently in stock."
                );
            }

            $this->products->updateStockQuantity($product->id, $newQuantity);

            return $this->stockHistories->create([
                'product_id'       => $product->id,
                'user_id'          => $userId,
                'old_quantity'     => $oldQuantity,
                'changed_quantity' => $increases ? $quantity : -$quantity,
                'new_quantity'     => $newQuantity,
                'type'             => $type,
                'remarks'          => $remarks,
            ]);
        });
    }
}
