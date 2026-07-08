<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\StockHistory;
use Illuminate\Pagination\LengthAwarePaginator;
use RuntimeException;

/**
 * StockHistoryRepository
 *
 * Handles queries for the StockHistory model — an append-only audit
 * log of every stock change in the system. update() and delete() are
 * deliberately overridden below to always throw an exception: nothing
 * in the application should ever be able to modify or remove a stock
 * history record after it's written, since its entire value as an
 * audit trail depends on being tamper-proof. BaseRepository technically
 * provides both methods through inheritance, so without this override,
 * the ability to misuse them would silently exist and someone could
 * call $stockHistoryRepo->delete($id) by accident with no warning.
 */
class StockHistoryRepository extends BaseRepository
{
    /**
     * @param StockHistory $stockHistory Injected fresh by the service container
     */
    public function __construct(StockHistory $stockHistory)
    {
        parent::__construct($stockHistory);
    }

    /**
     * Paginated stock history for a single product, most recent first,
     * with the acting user's name eager loaded to avoid an N+1 query
     * when the view displays "changed by [name]" per row.
     *
     * @param int $productId The product whose history to retrieve
     * @param int $perPage Records per page
     * @return LengthAwarePaginator
     */
    public function getPaginatedForProduct(int $productId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->with('user:id,name')
            ->where('product_id', $productId)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Overridden to always throw — stock history rows are immutable
     * once created. This is intentional, not a missing feature.
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @return never
     *
     * @throws RuntimeException Always
     */
    public function update(int $id, array $data): never
    {
        throw new RuntimeException('Stock history records are immutable and cannot be updated.');
    }

    /**
     * Overridden to always throw — stock history rows can never be
     * deleted, since doing so would break the audit trail's integrity.
     *
     * @param int $id
     * @return never
     *
     * @throws RuntimeException Always
     */
    public function delete(int $id): never
    {
        throw new RuntimeException('Stock history records are immutable and cannot be deleted.');
    }
    /**
     * Most recent stock changes across ALL products, for the Dashboard's
     * activity feed — not scoped to one product, unlike getPaginatedForProduct().
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection<int, StockHistory>
     */

    public function getRecent(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model
            ->with(['product:id,name', 'user:id,name'])
            ->latest()
            ->limit($limit)
            ->get();
    }
}
