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
            $product = $this->products->findForUpdate($productId);

            $oldQuantity = $product->stock_quantity;

            $increases = in_array($type, ['add', 'purchase'], true);

            $newQuantity = $increases
                ? $oldQuantity + $quantity
                : $oldQuantity - $quantity;

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

    /**
     * Set a product's stock to an exact quantity, recording the difference
     * as an 'adjustment' — used to correct drift after a physical stock
     * count, where the TRUE current count is known but the delta that
     * caused the discrepancy is not.
     *
     * Unlike adjustStock() (which takes a delta the caller already knows),
     * this takes the target quantity directly and calculates the signed
     * delta itself for the audit record.
     *
     * @param int $productId
     * @param int $newQuantity The corrected, true stock count
     * @param int $userId
     * @param string $reason Mandatory — an adjustment overriding the system's
     *        count must always be explained, unlike a routine add/reduce
     * @return StockHistory
     */
    public function adjustToExactQuantity(
        int $productId,
        int $newQuantity,
        int $userId,
        string $reason,
    ): StockHistory {
        return DB::transaction(function () use ($productId, $newQuantity, $userId, $reason) {
            $product = $this->products->findForUpdate($productId);
            $oldQuantity = $product->stock_quantity;
            $delta = $newQuantity - $oldQuantity;

            $this->products->updateStockQuantity($product->id, $newQuantity);

            return $this->stockHistories->create([
                'product_id'       => $product->id,
                'user_id'          => $userId,
                'old_quantity'     => $oldQuantity,
                'changed_quantity' => $delta,
                'new_quantity'     => $newQuantity,
                'type'             => 'adjustment',
                'remarks'          => $reason,
            ]);
        });
    }
}
