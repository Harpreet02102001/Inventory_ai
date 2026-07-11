<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * BaseRepository
 *
 * Abstract base class providing standard database operations for all repositories.
 *
 * TWO TIERS:
 *
 * Tier 1 - Reference Data (small, stable tables):
 *   └─ RoleRepository, PermissionRepository
 *   └─ Use: getAll(), getActiveForSelect(), count()
 *   └─ NO pagination needed
 *
 * Tier 2 - Transactional Data (large, frequently queried):
 *   └─ ProductRepository, UserRepository, SaleRepository, etc.
 *   └─ Use: getFiltered(), getPaginated(), getActiveForSelect()
 *   └─ Pagination required
 *
 * All repositories inherit these standard methods.
 * Concrete repos override as needed for their specific filters.
 *
 * NAMING CONVENTION (ALL repositories follow this):
 *   - findById(id)              → single record or null
 *   - findOrFail(id)            → single record or 404
 *   - getAll()                  → all records (reference data only)
 *   - getActive()               → all active records
 *   - getActiveForSelect()      → active records for dropdowns (minimal columns)
 *   - getFiltered(filters)      → paginated with module-specific filters
 *   - getPaginated(perPage)     → simple pagination, no filters
 *   - create(data)              → insert new record
 *   - update(id, data)          → update existing record
 *   - delete(id)                → soft or hard delete
 *   - count()                   → total records
 *   - countActive()             → active records only
 */
abstract class BaseRepository
{
    /**
     * The Eloquent model instance this repository operates on.
     *
     * @var Model
     */
    protected Model $model;

    /**
     * BaseRepository constructor.
     *
     * @param  Model  $model  The Eloquent model instance for this repository
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    // ────────────────────────────────────────────────────────────────────
    // BASIC CRUD OPERATIONS
    // ────────────────────────────────────────────────────────────────────

    /**
     * Find a single record by its primary key.
     *
     * Returns null if not found. Use findOrFail() for 404 behavior.
     *
     * @param  int  $id  Primary key value
     * @return Model|null
     */
    public function findById(int $id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * Find a record by primary key or throw ModelNotFoundException.
     *
     * Laravel automatically converts this to a 404 HTTP response.
     * Use this in show(), edit(), update(), destroy() controller methods.
     *
     * @param  int  $id  Primary key value
     * @return Model
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): Model
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Retrieve ALL records without any filters or pagination.
     *
     * ⚠️ USE ONLY for small reference data tables (roles, permissions, statuses).
     * For large tables, use getFiltered() or getPaginated() instead.
     *
     * @return Collection<int, Model>
     */
    public function getAll(): Collection
    {
        return $this->model->all();
    }

    /**
     * Create a new record with the given validated data.
     *
     * @param  array<string, mixed>  $data  Validated field values
     * @return Model  The newly created instance with ID populated
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing record by its primary key.
     *
     * Calls fresh() to return the record reloaded from the database,
     * ensuring any DB-level triggers or default values are reflected.
     *
     * @param  int                   $id    Primary key
     * @param  array<string, mixed>  $data  Validated field values to update
     * @return Model  The updated instance, freshly reloaded from DB
     */
    public function update(int $id, array $data): Model
    {
        $record = $this->findOrFail($id);
        $record->update($data);

        return $record->fresh();
    }

    /**
     * Delete a record by its primary key.
     *
     * If the model uses SoftDeletes trait, this performs a soft delete
     * (sets deleted_at timestamp). Hard deletion requires forceDelete().
     *
     * @param  int  $id  Primary key
     * @return bool  True if deleted successfully
     */
    public function delete(int $id): bool
    {
        $record = $this->findOrFail($id);

        return (bool) $record->delete();
    }

    // ────────────────────────────────────────────────────────────────────
    // COUNT OPERATIONS
    // ────────────────────────────────────────────────────────────────────

    /**
     * Total count of all records (includes soft-deleted if model uses SoftDeletes).
     *
     * @return int
     */
    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * Count of active records only.
     *
     * Uses the scopeActive() defined on the model.
     *
     * @return int
     */
    public function countActive(): int
    {
        return $this->model->active()->count();
    }

    // ────────────────────────────────────────────────────────────────────
    // PAGINATION & FILTERING (Tier 2: Transactional Data)
    // ────────────────────────────────────────────────────────────────────

    /**
     * Simple pagination without filters — ordered by latest.
     *
     * Use this for simple list views with no search/filter UI.
     * Most views should use getFiltered() instead.
     *
     * @param  int  $perPage  Records per page (default: 15)
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Paginated records with optional module-specific filters.
     *
     * CONCRETE REPOSITORIES OVERRIDE THIS to add their own filter logic.
     * This base implementation just returns everything paginated.
     *
     * Example override in ProductRepository:
     *
     *   public function getFiltered(array $filters = []): LengthAwarePaginator
     *   {
     *       return $this->model
     *           ->when(
     *               $filters['search'] ?? null,
     *               fn($q, $search) => $q->where('name', 'like', "%{$search}%")
     *           )
     *           ->when(
     *               $filters['category_id'] ?? null,
     *               fn($q, $catId) => $q->where('category_id', $catId)
     *           )
     *           ->when(
     *               $filters['status'] ?? null,
     *               fn($q, $status) => $q->where('status', $status)
     *           )
     *           ->latest()
     *           ->paginate($filters['perPage'] ?? 15)
     *           ->withQueryString();
     *   }
     *
     * @param  array<string, mixed>  $filters  Module-specific filters
     * @return LengthAwarePaginator
     */
    public function getFiltered(array $filters = []): LengthAwarePaginator
    {
        return $this->model
            ->latest()
            ->paginate($filters['perPage'] ?? 15)
            ->withQueryString();
    }

    // ────────────────────────────────────────────────────────────────────
    // SELECT/DROPDOWN OPERATIONS (All Repositories)
    // ────────────────────────────────────────────────────────────────────

    /**
     * All active records in minimal form for dropdown/select elements.
     *
     * Returns ONLY id and the model's display column (name, display_name, company_name, etc.)
     * to minimize data transfer and memory usage.
     *
     * Ordered alphabetically by name for better UX in dropdowns.
     *
     * CONCRETE REPOSITORIES OVERRIDE THIS to specify exact columns and ordering.
     *
     * Example override in ProductRepository:
     *
     *   public function getActiveForSelect(): Collection
     *   {
     *       return $this->model
     *           ->active()
     *           ->orderBy('name')
     *           ->get(['id', 'name', 'sku']);
     *   }
     *
     * @return Collection<int, Model>  Active records with minimal columns
     */
    public function getActiveForSelect(): Collection
    {
        return $this->model
            ->active()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * All active records (full models, not just for dropdowns).
     *
     * Use when you need the entire model object, not just for selects.
     *
     * @return Collection<int, Model>
     */
    public function getActive(): Collection
    {
        return $this->model
            ->active()
            ->orderBy('name')
            ->get();
    }
}
