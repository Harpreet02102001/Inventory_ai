<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidSaleStatusException;
use App\Models\Sale;
use App\Repositories\SaleRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * SaleService
 *
 * Owns every business rule around Sales: creating one with its line
 * items, calculating totals server-side, and enforcing the
 * draft -> confirmed / cancelled workflow. Confirming a sale reuses
 * StockService::adjustStock() per item with type 'sale' — the same
 * locking and audit-trail logic already proven in Stock Update and
 * Purchase Orders, just moving stock in the opposite direction.
 */
class SaleService
{
    /**
     * @param SaleRepository $sales
     * @param StockService $stockService Reused as-is for the 'confirm' step
     */
    public function __construct(
        private readonly SaleRepository $sales,
        private readonly StockService $stockService,
    ) {}

    /**
     * Create a new sale with its line items, in 'draft' status. No stock
     * is affected yet — draft sales are just a saved cart, not a
     * completed transaction.
     *
     * @param array<string, mixed> $data Validated header fields: discount_amount, notes
     * @param array<int, array{product_id:int, quantity:int, unit_price:float}> $items
     * @param int $userId The user creating this sale
     * @return Sale The created sale with its items loaded
     */
    public function createSale(array $data, array $items, int $userId): Sale
    {
        return DB::transaction(function () use ($data, $items, $userId) {
            $totalAmount = 0;

            $sale = $this->sales->create([
                ...$data,
                'user_id'          => $userId,
                'reference_number' => $this->generateReferenceNumber(),
                'status'           => 'draft',
                'total_amount'     => 0, // corrected below once items are summed
            ]);

            foreach ($items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $totalAmount += $lineTotal;

                $sale->items()->create([
                    'product_id'  => $item['product_id'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'total_price' => $lineTotal,
                ]);
            }

            $sale->update(['total_amount' => $totalAmount]);

            return $sale->fresh('items.product');
        });
    }

    /**
     * Confirm a draft sale: deducts stock for every line item and marks
     * the sale 'confirmed'. If ANY item lacks sufficient stock, the
     * entire operation rolls back — no partial sale is ever recorded.
     * This guarantee comes from the surrounding DB::transaction, not
     * from any manual "undo" logic: an uncaught exception from
     * StockService::adjustStock() automatically unwinds every stock
     * change already applied earlier in this same loop.
     *
     * @param int $saleId
     * @param int $userId The user confirming this sale
     * @return Sale
     *
     * @throws InvalidSaleStatusException If not currently 'draft'
     * @throws InsufficientStockException If any item lacks enough stock —
     *         re-thrown here with the product's name added, since
     *         StockService alone has no visibility into which sale
     *         or product context this failure occurred in
     */
    public function confirmSale(int $saleId, int $userId): Sale
    {
        return DB::transaction(function () use ($saleId, $userId) {
            $sale = $this->sales->findForUpdate($saleId);

            if ($sale->status !== 'draft') {
                throw new InvalidSaleStatusException(
                    "Only draft sales can be confirmed. This sale is currently '{$sale->status}'."
                );
            }

            foreach ($sale->items as $item) {
                try {
                    $this->stockService->adjustStock(
                        productId: $item->product_id,
                        type: 'sale',
                        quantity: $item->quantity,
                        userId: $userId,
                        remarks: "Sold via {$sale->reference_number}",
                    );
                } catch (InsufficientStockException $e) {
                    // Re-thrown with the product's name for a clearer
                    // message — StockService only knows the product ID,
                    // not a human-readable name, since it has no reason
                    // to load that relationship itself.
                    throw new InsufficientStockException(
                        "Cannot confirm sale — insufficient stock for \"{$item->product->name}\": {$e->getMessage()}"
                    );
                }
            }

            $sale->update(['status' => 'confirmed']);

            return $sale->fresh('items.product');
        });
    }

    /**
     * Cancel a sale. Only allowed from 'draft' — a 'confirmed' sale has
     * already deducted real stock, and reversing that is a stock
     * reversal operation (belongs in Stock Adjustments), not a simple
     * status flip.
     *
     * @param int $saleId
     * @return Sale
     *
     * @throws InvalidSaleStatusException If not currently 'draft'
     */
    public function cancelSale(int $saleId): Sale
    {
        return DB::transaction(function () use ($saleId) {
            $sale = $this->sales->findForUpdate($saleId);

            if ($sale->status !== 'draft') {
                throw new InvalidSaleStatusException(
                    "Only draft sales can be cancelled directly. This sale is '{$sale->status}' — use Stock Adjustments to reverse a confirmed sale."
                );
            }

            $sale->update(['status' => 'cancelled']);

            return $sale;
        });
    }

    /**
     * Generate a unique, human-readable reference number.
     *
     * @return string
     */
    private function generateReferenceNumber(): string
    {
        return 'SALE-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
    }
}
