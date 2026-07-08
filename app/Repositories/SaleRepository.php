<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Sale;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * SaleRepository
 *
 * Handles queries for the Sale model. SaleItem has no repository of
 * its own — same reasoning as PurchaseOrderItem: it's never queried
 * independently of its parent Sale, so $sale->items() (accessed inside
 * SaleService) is the correct, idiomatic approach.
 */
class SaleRepository extends BaseRepository
{
    /**
     * @param Sale $sale Injected fresh by the service container
     */
    public function __construct(Sale $sale)
    {
        parent::__construct($sale);
    }

    /**
     * Paginated sales, optionally filtered by status, with the creating
     * user eager loaded to avoid N+1 on the list view.
     *
     * @param int $perPage
     * @param string|null $status
     * @return LengthAwarePaginator
     */
    public function getPaginatedWithFilters(
        int $perPage = 15,
        ?string $status = null,
    ): LengthAwarePaginator {
        return $this->model
            ->with('user:id,name')
            ->when($status, fn($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Find a sale and lock its row for the current transaction — required
     * before any status transition, to prevent two concurrent requests
     * (e.g. a double-click on "Confirm Sale") from both processing the
     * same sale and deducting stock twice.
     *
     * @param int $id
     * @return Sale
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findForUpdate(int $id): Sale
    {
        return $this->model->lockForUpdate()->findOrFail($id);
    }
}
