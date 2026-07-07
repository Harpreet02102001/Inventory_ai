<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\InvalidPurchaseOrderStatusException;
use App\Models\PurchaseOrder;
use App\Repositories\PurchaseOrderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * PurchaseOrderService
 *
 * Owns every business rule around Purchase Orders: creating one with its
 * line items, calculating totals server-side, and enforcing the
 * draft -> ordered -> received / cancelled status workflow. Controllers
 * never touch PurchaseOrderItem or StockService directly — everything
 * flows through here, keeping the transition rules in exactly one place.
 */
class PurchaseOrderService
{
    /**
     * @param PurchaseOrderRepository $purchaseOrders
     * @param StockService $stockService Reused as-is for the 'receive' step
     */
    public function __construct(
        private readonly PurchaseOrderRepository $purchaseOrders,
        private readonly StockService $stockService,
    ) {}

    /**
     * Create a new purchase order with its line items, in 'draft' status.
     *
     * Trusts NOTHING price-related from the client beyond product_id and
     * quantity/unit_price inputs — total_price per item and total_amount
     * for the order are both calculated here, server-side, from those
     * validated inputs. This is what stops a tampered hidden field from
     * ever controlling a financial total.
     *
     * @param array<string, mixed> $data Validated header fields: supplier_id, notes
     * @param array<int, array{product_id:int, quantity:int, unit_price:float}> $items
     * @param int $userId The Admin creating this order
     * @return PurchaseOrder The created order with its items loaded
     */
    public function createPurchaseOrder(array $data, array $items, int $userId): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $items, $userId) {
            $totalAmount = 0;

            $purchaseOrder = $this->purchaseOrders->create([
                ...$data,
                'user_id'          => $userId,
                'reference_number' => $this->generateReferenceNumber(),
                'status'           => 'draft',
                'total_amount'     => 0, // corrected below, once line items are summed
            ]);

            foreach ($items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $totalAmount += $lineTotal;

                $purchaseOrder->items()->create([
                    'product_id'  => $item['product_id'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'total_price' => $lineTotal,
                ]);
            }

            $purchaseOrder->update(['total_amount' => $totalAmount]);

            return $purchaseOrder->fresh('items.product');
        });
    }

    /**
     * Transition a draft purchase order to 'ordered' — signals it has
     * been sent to the supplier. No stock effect happens at this stage.
     *
     * @param int $purchaseOrderId
     * @return PurchaseOrder
     *
     * @throws InvalidPurchaseOrderStatusException If not currently 'draft'
     */
    public function markAsOrdered(int $purchaseOrderId): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrderId) {
            $purchaseOrder = $this->purchaseOrders->findForUpdate($purchaseOrderId);

            if ($purchaseOrder->status !== 'draft') {
                throw new InvalidPurchaseOrderStatusException(
                    "Only draft orders can be marked as ordered. This order is currently '{$purchaseOrder->status}'."
                );
            }

            $purchaseOrder->update([
                'status'     => 'ordered',
                'ordered_at' => now(),
            ]);

            return $purchaseOrder;
        });
    }

    /**
     * Receive a purchase order: increases stock for every line item and
     * marks the order 'received'. This is the critical operation — it
     * calls StockService::adjustStock() once per item with type
     * 'purchase', reusing the exact same row-locking and audit-trail
     * logic already built for manual stock updates. Wrapping the whole
     * loop in one outer transaction means: if item 3 of 5 somehow fails,
     * items 1-2's stock increases AND the status change all roll back
     * together — never a half-received order.
     *
     * @param int $purchaseOrderId
     * @param int $userId The Admin marking this order as received
     * @return PurchaseOrder
     *
     * @throws InvalidPurchaseOrderStatusException If not currently 'ordered'
     */
    public function receivePurchaseOrder(int $purchaseOrderId, int $userId): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrderId, $userId) {
            $purchaseOrder = $this->purchaseOrders->findForUpdate($purchaseOrderId);

            if ($purchaseOrder->status !== 'ordered') {
                throw new InvalidPurchaseOrderStatusException(
                    "Only orders marked 'ordered' can be received. This order is currently '{$purchaseOrder->status}'."
                );
            }

            foreach ($purchaseOrder->items as $item) {
                $this->stockService->adjustStock(
                    productId: $item->product_id,
                    type: 'purchase',
                    quantity: $item->quantity,
                    userId: $userId,
                    remarks: "Received via PO {$purchaseOrder->reference_number}",
                );
            }

            $purchaseOrder->update([
                'status'      => 'received',
                'received_at' => now(),
            ]);

            return $purchaseOrder->fresh('items.product');
        });
    }

    /**
     * Cancel a purchase order. Allowed from 'draft' or 'ordered' only —
     * a 'received' order already affected real stock and can't be
     * un-received by simply flipping a status flag.
     *
     * @param int $purchaseOrderId
     * @return PurchaseOrder
     *
     * @throws InvalidPurchaseOrderStatusException If already 'received' or 'cancelled'
     */
    public function cancelPurchaseOrder(int $purchaseOrderId): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrderId) {
            $purchaseOrder = $this->purchaseOrders->findForUpdate($purchaseOrderId);

            if (!in_array($purchaseOrder->status, ['draft', 'ordered'], true)) {
                throw new InvalidPurchaseOrderStatusException(
                    "Cannot cancel an order that is already '{$purchaseOrder->status}'."
                );
            }

            $purchaseOrder->update(['status' => 'cancelled']);

            return $purchaseOrder;
        });
    }

    /**
     * Generate a unique, human-readable reference number.
     *
     * Format: PO-{today's date}-{random 6-char suffix}. A random suffix
     * (rather than counting today's existing orders and incrementing)
     * avoids a race condition where two concurrent creates could both
     * count "3 orders today" and generate the same "PO-...-004" — the
     * random suffix makes collisions practically impossible without
     * needing a database lock just to generate a number.
     *
     * @return string
     */
    private function generateReferenceNumber(): string
    {
        return 'PO-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
    }
}
