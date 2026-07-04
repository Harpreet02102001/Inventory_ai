<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * SupplierRepository
 *
 * Handles all database query logic for the Supplier model. Extends
 * BaseRepository to inherit common CRUD (findById, findOrFail, create,
 * update, delete, getPaginated) and adds supplier-specific queries here.
 *
 * This is the ONLY class in the application allowed to write
 * Supplier::where(...) or similar.
 */
class SupplierRepository extends BaseRepository
{
    /**
     * @param Supplier $supplier Injected fresh by Laravel's service container
     */
    public function __construct(Supplier $supplier)
    {
        parent::__construct($supplier);
    }

    /**
     * Paginated suppliers, optionally filtered by a search term matched
     * against name, email, or company_name simultaneously.
     *
     * The three orWhere() calls are wrapped inside an inner closure —
     * without it, Laravel would generate:
     *   WHERE name LIKE ? OR email LIKE ? OR company_name LIKE ? AND deleted_at IS NULL
     * and SQL operator precedence means that trailing condition only
     * binds to the last OR clause, not all three. Wrapping forces:
     *   WHERE (name LIKE ? OR email LIKE ? OR company_name LIKE ?) AND deleted_at IS NULL
     *
     * @param int $perPage Records per page (default: 15)
     * @param string|null $search Optional search term
     * @return LengthAwarePaginator
     */
    public function getPaginatedWithSearch(
        int $perPage = 5,
        ?string $search = null
    ): LengthAwarePaginator {
        return $this->model
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Active suppliers only, ordered by company name — used to populate
     * the supplier dropdown on the Product create/edit forms.
     *
     * @return Collection<int, Supplier>
     */
    public function getActiveForSelect(): Collection
    {
        return $this->model
            ->active()
            ->orderBy('company_name')
            ->get(['id', 'name', 'company_name']);
    }

    /**
     * Whether a supplier still has at least one product sourced from them.
     * Checked before deletion to avoid orphaning products.
     *
     * @param int $supplierId The supplier's primary key
     * @return bool
     */
    public function hasProducts(int $supplierId): bool
    {
        return $this->model
            ->where('id', $supplierId)
            ->whereHas('products')
            ->exists();
    }
}
