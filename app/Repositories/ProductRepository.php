<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * ProductRepository
 *
 * Handles all database query logic for the Product model. Products are
 * the most-queried entity in the system, so every list query here eager
 * loads category and supplier with them (via with()) to avoid N+1 —
 * without it, a 15-row product list would fire 30 extra queries just
 * to display each row's category and supplier name.
 */
class ProductRepository extends BaseRepository
{
    /**
     * @param Product $product Injected fresh by Laravel's service container
     */
    public function __construct(Product $product)
    {
        parent::__construct($product);
    }

    /**
     * Paginated products with optional search and filters, eager loading
     * category and supplier relationships.
     *
     * Each filter uses when() so it's only applied if actually provided —
     * calling this with all filters null returns every product, unfiltered.
     *
     * @param int $perPage Records per page
     * @param string|null $search Matched against product name or SKU
     * @param int|null $categoryId Filter to a specific category
     * @param int|null $supplierId Filter to a specific supplier
     * @param string|null $status Filter to 'active' or 'inactive'
     * @param bool $lowStockOnly If true, only products at/below their threshold
     * @return LengthAwarePaginator
     */
    public function getPaginatedWithFilters(
        int $perPage = 10,
        ?string $search = null,
        ?int $categoryId = null,
        ?int $supplierId = null,
        ?string $status = null,
        bool $lowStockOnly = false,
    ): LengthAwarePaginator {
        return $this->model
            ->with(['category:id,name', 'supplier:id,company_name'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, fn($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($supplierId, fn($query, $supplierId) => $query->where('supplier_id', $supplierId))
            ->when($status, fn($query, $status) => $query->where('status', $status))
            ->when($lowStockOnly, fn($query) => $query->lowStock())
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Total count of products currently at or below their low stock threshold.
     *
     * Used on the dashboard summary cards.
     *
     * @return int
     */
    public function countLowStock(): int
    {
        return $this->model->lowStock()->count();
    }

    /**
     * Whether a given SKU is already in use by another product.
     *
     * Not currently used directly (the unique validation rule handles this),
     * but kept here as the single place SKU-uniqueness logic would live if
     * we ever needed to check it outside a form (e.g. a CSV import feature).
     *
     * @param string $sku
     * @param int|null $ignoreId Exclude this product ID (used during updates)
     * @return bool
     */
    public function skuExists(string $sku, ?int $ignoreId = null): bool
    {
        return $this->model
            ->where('sku', $sku)
            ->when($ignoreId, fn($query, $ignoreId) => $query->where('id', '!=', $ignoreId))
            ->exists();
    }
    /**
     * Retrieve all active products, narrowed to only the columns a
     * picker/dropdown UI actually needs.
     *
     * Deliberately NOT paginated — a line-item picker on the Purchase
     * Order form needs the full list to search/select from, not abut that method does not exist in your ProductRepository.
     * page-by-page browsing experience. Also deliberately narrow on
     * columns (id, name, sku, purchase_price only) rather than fetching
     * the whole model — the picker never touches description, image,
     * stock_quantity, etc., so there's no reason to pull that data over
     * the wire.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Product> Active products, ordered by name
     */
    public function getAllForSelect(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'purchase_price']);
    }

    /**
     * Retrieve a product and lock it for update.
     *
     * Used inside database transactions to prevent concurrent
     * stock updates from modifying the same product at once.
     *
     * @param int $id
     * @return Product|null
     */
    public function findForUpdate(int $id): Product
    {
        return $this->model->lockForUpdate()->findOrFail($id);
    }
    /**
     * Update a product's stock quantity.
     *
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function updateStockQuantity(int $productId, int $quantity): bool
    {
        return $this->model
            ->whereKey($productId)
            ->update([
                'stock_quantity' => $quantity,
            ]) > 0;
    }
}
