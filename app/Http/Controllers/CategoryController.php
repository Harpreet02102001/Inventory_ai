<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

/**
 * CategoryController
 *
 * Handles HTTP requests for the Categories module. Contains no query
 * logic itself — every database interaction is delegated to
 * CategoryRepository. The controller's only job: translate HTTP input
 * into repository calls, and repository output into HTTP responses.
 */
class CategoryController extends Controller
{
    /**
     * @param CategoryRepository $categories Injected automatically by the service container
     */
    public function __construct(private readonly CategoryRepository $categories) {}

    /**
     * Display a paginated, searchable list of categories.
     *
     * Route middleware already confirmed 'categories.view' before this runs.
     *
     * @param Request $request May contain a 'search' query string param
     * @return View
     */
    public function index(Request $request): View
    {
        $categories = $this->categories->getPaginatedWithSearch(
            perPage: 15,
            search: $request->query('search'),
        );

        return view('categories.index', [
            'categories' => $categories,
            'search'     => $request->query('search', ''),
        ]);
    }

    /**
     * Show the empty form for creating a new category.
     *
     * @return View
     */
    public function create(): View
    {
        return view('categories.create');
    }

    /**
     * Persist a newly created category.
     *
     * $request->validated() is guaranteed clean — StoreCategoryRequest
     * already authorized and validated it before this method ran.
     *
     * @param StoreCategoryRequest $request
     * @return RedirectResponse
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request): void {
                $this->categories->create($request->validated());
            });
        } catch (Throwable $e) {
            Log::error('Category creation failed', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'Something went wrong while creating the category. Please try again.');
        }

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display a single category's details.
     *
     * {category} is resolved into a Category model automatically by
     * route model binding (or a 404 is thrown) — no findOrFail() needed.
     *
     * @param Category $category
     * @return View
     */
    public function show(Category $category): View
    {
        return view('categories.show', [
            'category' => $category->loadCount('products'),
        ]);
    }

    /**
     * Show the pre-filled edit form for an existing category.
     *
     * @param Category $category
     * @return View
     */
    public function edit(Category $category): View
    {
        return view('categories.edit', ['category' => $category]);
    }

    /**
     * Persist changes to an existing category.
     *
     * @param UpdateCategoryRequest $request
     * @param Category $category
     * @return RedirectResponse
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $category): void {
                $this->categories->update($category->id, $request->validated());
            });
        } catch (Throwable $e) {
            Log::error('Category update failed', [
                'category_id' => $category->id,
                'error'       => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Something went wrong while updating the category. Please try again.');
        }

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Soft-delete a category.
     *
     * Blocks deletion if products are still linked — deleting it would
     * orphan those products' category relationship. This is a business
     * rule check, kept outside the try/catch since it's an expected
     * outcome, not an error.
     *
     * @param Category $category
     * @return RedirectResponse
     */
    public function destroy(Category $category): RedirectResponse
    {
        if ($this->categories->hasProducts($category->id)) {
            return back()->with(
                'error',
                "Cannot delete \"{$category->name}\" — it still has products linked to it. Reassign or remove those products first."
            );
        }

        try {
            DB::transaction(function () use ($category): void {
                $this->categories->delete($category->id);
            });
        } catch (Throwable $e) {
            Log::error('Category deletion failed', [
                'category_id' => $category->id,
                'error'       => $e->getMessage(),
            ]);

            return back()->with('error', 'Something went wrong while deleting the category. Please try again.');
        }

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
