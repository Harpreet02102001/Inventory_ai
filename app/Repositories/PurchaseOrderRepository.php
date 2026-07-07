<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\PurchaseOrder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * PurchaseOrderRepository
 *
 * Handles queries for the PurchaseOrder model. PurchaseOrderItem is
 * deliberately NOT given its own repository — items are never queried
 * independently of their parent PO, so accessing them via the
 * $purchaseOrder->items() relationship (inside the Service) is the
 * correct, idiomatic approach. A repository earns its place only when
 * something needs standalone querying; items don't.
 */
class PurchaseOrderRepository extends BaseRepository
{
    /**
     * @param PurchaseOrder $purchaseOrder Injected fresh by the service container
     */
    public function __construct(PurchaseOrder $purchaseOrder)
    {
        parent::__construct($purchaseOrder);
    }

    /**
     * Paginated purchase orders, optionally filtered by status or supplier,
     * with supplier and creating-user eager loaded to avoid N+1 on the list.
     *
     * @param int $perPage
     * @param string|null $status
     * @param int|null $supplierId
     * @return LengthAwarePaginator
     */
    public function getPaginatedWithFilters(
        int $perPage = 15,
        ?string $status = null,
        ?int $supplierId = null,
    ): LengthAwarePaginator {
        return $this->model
            ->with(['supplier:id,company_name', 'user:id,name'])
            ->when($status, fn($query, $status) => $query->where('status', $status))
            ->when($supplierId, fn($query, $supplierId) => $query->where('supplier_id', $supplierId))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Find a purchase order and lock its row for the current transaction.
     *
     * Required before any status transition (ordered/received/cancelled)
     * to prevent two concurrent requests from both acting on the same PO
     * — e.g. a double-click on "Mark as Received" adding stock twice.
     *
     * @param int $id
     * @return PurchaseOrder
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findForUpdate(int $id): PurchaseOrder
    {
        return $this->model->lockForUpdate()->findOrFail($id);
    }
}
