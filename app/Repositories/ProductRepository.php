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
        int $perPage = 15,
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
}
