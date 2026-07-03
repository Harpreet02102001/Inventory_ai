<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * CategoryRepository
 *
 * Handles all database query logic for the Category model. Extends
 * BaseRepository to inherit common CRUD (findById, findOrFail, create,
 * update, delete, getPaginated) and adds category-specific queries here.
 *
 * This is the ONLY class in the application allowed to write
 * Category::where(...) or similar — controllers and any future
 * service classes always go through this repository instead.
 */
class CategoryRepository extends BaseRepository
{
    /**
     * @param Category $category Injected fresh by Laravel's service container
     */
    public function __construct(Category $category)
    {
        parent::__construct($category);
    }

    /**
     * Paginated categories, optionally filtered by name, with a product
     * count attached per row (withCount) to avoid an N+1 query when the
     * index view displays "X products" per category.
     *
     * @param int $perPage Records per page
     * @param string|null $search Optional search term matched against name
     * @return LengthAwarePaginator
     */
    public function getPaginatedWithSearch(
        int $perPage = 10,
        ?string $search = null
    ): LengthAwarePaginator {
        return $this->model
            ->withCount('products')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Active categories only, ordered alphabetically — used to populate
     * the category dropdown on the product create/edit forms.
     *
     * @return Collection<int, Category>
     */
    public function getActiveForSelect(): Collection
    {
        return $this->model
            ->active()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Whether a category still has at least one product linked to it.
     * Checked before deletion to avoid orphaning products.
     *
     * @param int $categoryId The category's primary key
     * @return bool
     */
    public function hasProducts(int $categoryId): bool
    {
        return $this->model
            ->where('id', $categoryId)
            ->whereHas('products')
            ->exists();
    }
}
