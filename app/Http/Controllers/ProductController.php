<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use App\Repositories\SupplierRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

/**
 * ProductController
 *
 * Handles HTTP requests for the Products module. Slightly larger than
 * Category/Supplier controllers because it also coordinates file uploads
 * alongside the database write — but the query logic itself still lives
 * entirely in ProductRepository.
 */
class ProductController extends Controller
{
    /**
     * @param ProductRepository $products
     * @param CategoryRepository $categories Used to populate the category dropdown
     * @param SupplierRepository $suppliers Used to populate the supplier dropdown
     */
    public function __construct(
        private readonly ProductRepository $products,
        private readonly CategoryRepository $categories,
        private readonly SupplierRepository $suppliers,
    ) {}

    /**
     * Display a paginated, filterable list of products.
     *
     * @param Request $request May contain search, category_id, supplier_id, status, low_stock
     * @return View
     */
    public function index(Request $request): View
    {
        $products = $this->products->getPaginatedWithFilters(
            perPage: 15,
            search: $request->query('search'),
            categoryId: $request->query('category_id') ? (int) $request->query('category_id') : null,
            supplierId: $request->query('supplier_id') ? (int) $request->query('supplier_id') : null,
            status: $request->query('status'),
            lowStockOnly: $request->boolean('low_stock'),
        );

        return view('products.index', [
            'products'   => $products,
            'categories' => $this->categories->getActiveForSelect(),
            'suppliers'  => $this->suppliers->getActiveForSelect(),
            'filters'    => $request->only(['search', 'category_id', 'supplier_id', 'status', 'low_stock']),
        ]);
    }

    /**
     * Show the form for creating a new product.
     *
     * Only ACTIVE categories/suppliers are offered — this is the business
     * rule from the spec: "Inactive categories should not be available
     * while creating new products."
     *
     * @return View
     */
    public function create(): View
    {
        return view('products.create', [
            'categories' => $this->categories->getActiveForSelect(),
            'suppliers'  => $this->suppliers->getActiveForSelect(),
        ]);
    }

    /**
     * Persist a newly created product, including an optional image upload.
     *
     * Sequence matters here: the file is stored to disk FIRST, outside
     * the DB transaction (file writes can't be rolled back by MySQL). If
     * the subsequent database insert then fails, we manually delete the
     * just-uploaded file in the catch block — otherwise it would become
     * an orphaned file with nothing in the database pointing to it.
     *
     * @param StoreProductRequest $request
     * @return RedirectResponse
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $imagePath = null;

        if ($request->hasFile('image')) {
            // putFile() generates a unique filename automatically and stores
            // it on the 'public' disk under products/ — returns the relative
            // path we then save in the database (never the full URL).
            $imagePath = Storage::disk('public')->putFile('products', $request->file('image'));
            $data['image'] = $imagePath;
        }

        try {
            DB::transaction(function () use ($data): void {
                $this->products->create($data);
            });
        } catch (Throwable $e) {
            // Roll back the file upload manually, since the DB transaction
            // couldn't do it for us.
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            Log::error('Product creation failed', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'Something went wrong while creating the product. Please try again.');
        }

        return redirect()
            ->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display a single product's details.
     *
     * @param Product $product Resolved via route model binding, with relations eager loaded
     * @return View
     */
    public function show(Product $product): View
    {
        return view('products.show', [
            'product' => $product->load(['category', 'supplier']),
        ]);
    }

    /**
     * Show the pre-filled edit form for an existing product.
     *
     * @param Product $product
     * @return View
     */
    public function edit(Product $product): View
    {
        return view('products.edit', [
            'product'    => $product,
            'categories' => $this->categories->getActiveForSelect(),
            'suppliers'  => $this->suppliers->getActiveForSelect(),
        ]);
    }

    /**
     * Persist changes to an existing product.
     *
     * The old image is only deleted AFTER the database update succeeds —
     * if we deleted it first and the update then failed, the product
     * would be left with no image at all, which is worse than keeping
     * the stale one.
     *
     * @param UpdateProductRequest $request
     * @param Product $product
     * @return RedirectResponse
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        $newImagePath = null;
        $oldImagePath = $product->image;

        if ($request->hasFile('image')) {
            $newImagePath = Storage::disk('public')->putFile('products', $request->file('image'));
            $data['image'] = $newImagePath;
        }

        try {
            DB::transaction(function () use ($product, $data): void {
                $this->products->update($product->id, $data);
            });
        } catch (Throwable $e) {
            // Update failed — roll back the NEW file we just uploaded,
            // leaving the product's original image untouched.
            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }

            Log::error('Product update failed', [
                'product_id' => $product->id,
                'error'      => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Something went wrong while updating the product. Please try again.');
        }

        // Update succeeded — now it's safe to remove the old image.
        if ($newImagePath && $oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return redirect()
            ->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Soft-delete a product and remove its image file from disk.
     *
     * Soft delete keeps the database row (so historical stock_histories
     * entries still resolve their product relationship), but the physical
     * image file is genuinely removed — there's no value in keeping an
     * orphaned file around just because the row is soft-deleted.
     *
     * @param Product $product
     * @return RedirectResponse
     */
    public function destroy(Product $product): RedirectResponse
    {
        $imagePath = $product->image;

        try {
            DB::transaction(function () use ($product): void {
                $this->products->delete($product->id);
            });
        } catch (Throwable $e) {
            Log::error('Product deletion failed', [
                'product_id' => $product->id,
                'error'      => $e->getMessage(),
            ]);

            return back()->with('error', 'Something went wrong while deleting the product. Please try again.');
        }

        if ($imagePath) {
            Storage::disk('public')->delete($imagePath);
        }

        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
