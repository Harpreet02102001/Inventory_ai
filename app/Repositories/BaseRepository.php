<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * BaseRepository
 *
 * Abstract base class providing common Eloquent database operations
 * shared across every concrete repository in the application (Category,
 * Supplier, Product, and so on). Each concrete repository extends this
 * class and receives these methods for free — only module-specific
 * query logic needs to be written in the child class.
 *
 * Why abstract? This class is a template, not a finished tool — it has
 * no model of its own to query until a child class supplies one via
 * its constructor. PHP refuses `new BaseRepository()` directly, which
 * catches misuse at compile time instead of failing confusingly later.
 *
 * Design principle: DRY (Don't Repeat Yourself). If pagination defaults
 * or the delete behavior ever need to change globally, this is the one
 * place to change it — every repository picks up the update automatically.
 */
abstract class BaseRepository
{
    /**
     * The Eloquent model instance this repository operates on.
     *
     * Set once, in the constructor, by whichever concrete repository
     * extends this class. Every method below queries through this
     * property rather than referencing a model class name directly —
     * that's what lets one BaseRepository serve every module.
     *
     * @var Model
     */
    protected Model $model;

    /**
     * BaseRepository constructor.
     *
     * Concrete repositories call parent::__construct($model) and pass
     * in their specific model instance (e.g. a fresh Category), wiring
     * every inherited method to the correct database table.
     *
     * @param Model $model The Eloquent model instance for this repository
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Find a single record by its primary key.
     *
     * Returns null when nothing is found — use findOrFail() instead
     * when a missing record should trigger an automatic 404 response.
     *
     * @param int $id Primary key to search for
     * @return Model|null The matching model, or null if not found
     */
    public function findById(int $id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * Find a record by primary key, or throw a ModelNotFoundException.
     *
     * Laravel automatically converts this exception into an HTTP 404
     * response, so controllers never need to check for null manually.
     *
     * @param int $id Primary key to search for
     * @return Model The matching model instance
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): Model
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Retrieve every record from this model's table, unpaginated.
     *
     * Use sparingly — fine for small reference tables (like Categories),
     * risky for anything that could grow to thousands of rows. Prefer
     * getPaginated() for tables without a known small upper bound.
     *
     * @return Collection<int, Model> All records as an Eloquent collection
     */
    public function getAll(): Collection
    {
        return $this->model->all();
    }

    /**
     * Retrieve records paginated, most recent first.
     *
     * Returns a LengthAwarePaginator, which Blade renders directly via
     * {{ $records->links() }} — Laravel builds the page-number links
     * (and preserves any query string, when withQueryString() is chained
     * in a child repository's own override) automatically.
     *
     * @param int $perPage Number of records per page (default: 15)
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->latest()->paginate($perPage);
    }

    /**
     * Create a new record from the given data.
     *
     * $data is expected to already be validated and filtered — that
     * happens upstream, in a Form Request — this method trusts its
     * input completely and simply persists it.
     *
     * @param array<string, mixed> $data Validated field values to insert
     * @return Model The newly created model, with its ID populated
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing record by its primary key.
     *
     * Fetches the record first (throwing a 404 if missing), applies the
     * update, then calls fresh() to reload it from the database — this
     * matters if any database-level defaults or triggers could have
     * changed values beyond what was just written.
     *
     * @param int $id Primary key of the record to update
     * @param array<string, mixed> $data Validated field values to update
     * @return Model The updated record, freshly reloaded from the database
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
     * If the model uses the SoftDeletes trait (as Category and Supplier
     * do), this performs a soft delete — it sets deleted_at rather than
     * removing the row. A true permanent delete would require calling
     * forceDelete() explicitly, which no repository does by default.
     *
     * @param int $id Primary key of the record to delete
     * @return bool True if the delete succeeded
     */
    public function delete(int $id): bool
    {
        $record = $this->findOrFail($id);

        return (bool) $record->delete();
    }
}
