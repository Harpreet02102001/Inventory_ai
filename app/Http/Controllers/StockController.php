<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Http\Requests\Stock\StockAdjustmentRequest;
use App\Http\Requests\Stock\StockUpdateRequest;
use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Repositories\StockHistoryRepository;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

/**
 * StockController
 *
 * Handles the Stock Overview list, routine stock updates (add/reduce),
 * and stock adjustments (correcting to an exact count) for products.
 * Contains no calculation or locking logic — that all lives in
 * StockService. This controller only translates HTTP requests into
 * service/repository calls, and the outcome into a view or redirect.
 */
class StockController extends Controller
{
    /**
     * @param StockService $stockService
     * @param StockHistoryRepository $stockHistories
     * @param ProductRepository $products Used for the Stock Overview list
     */
    public function __construct(
        private readonly StockService $stockService,
        private readonly StockHistoryRepository $stockHistories,
        private readonly ProductRepository $products,
    ) {}

    /**
     * Display all products with their current stock levels, optionally
     * filtered to only those at or below their low stock threshold.
     * This is the landing page the nav's "Stock" and "Low Stock" links
     * both point to — from here, an Admin/Staff picks a specific
     * product to update or adjust.
     *
     * @param Request $request May contain 'low_stock=1' and 'search'
     * @return View
     */
    public function index(Request $request): View
    {
        $products = $this->products->getPaginatedWithFilters(
            perPage: 15,
            search: $request->query('search'),
            lowStockOnly: $request->boolean('low_stock'),
        );

        return view('stock.index', [
            'products'     => $products,
            'lowStockOnly' => $request->boolean('low_stock'),
            'search'       => $request->query('search', ''),
        ]);
    }

    /**
     * Show the stock update form plus this product's stock history.
     *
     * @param Product $product
     * @return View
     */
    public function edit(Product $product): View
    {
        return view('stock.edit', [
            'product' => $product,
            'history' => $this->stockHistories->getPaginatedForProduct($product->id, 10),
        ]);
    }

    /**
     * Apply a routine stock add/reduce.
     *
     * @param StockUpdateRequest $request
     * @param Product $product
     * @return RedirectResponse
     */
    public function update(StockUpdateRequest $request, Product $product): RedirectResponse
    {
        try {
            $this->stockService->adjustStock(
                productId: $product->id,
                type: $request->validated('type'),
                quantity: (int) $request->validated('quantity'),
                userId: $request->user()->id,
                remarks: $request->validated('remarks'),
            );
        } catch (InsufficientStockException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            Log::error('Stock update failed', [
                'product_id' => $product->id,
                'error'      => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Something went wrong while updating stock. Please try again.');
        }

        return redirect()
            ->route('stock.edit', $product)
            ->with('success', 'Stock updated successfully.');
    }

    /**
     * Show the stock adjustment form for a product.
     *
     * @param Product $product
     * @return View
     */
    public function adjustForm(Product $product): View
    {
        return view('stock.adjust', ['product' => $product]);
    }

    /**
     * Apply a stock adjustment — corrects stock to an exact count.
     *
     * @param StockAdjustmentRequest $request
     * @param Product $product
     * @return RedirectResponse
     */
    public function adjust(StockAdjustmentRequest $request, Product $product): RedirectResponse
    {
        try {
            $this->stockService->adjustToExactQuantity(
                productId: $product->id,
                newQuantity: (int) $request->validated('new_quantity'),
                userId: $request->user()->id,
                reason: $request->validated('reason'),
            );
        } catch (Throwable $e) {
            Log::error('Stock adjustment failed', ['product_id' => $product->id, 'error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Something went wrong while adjusting stock. Please try again.');
        }

        return redirect()
            ->route('stock.edit', $product)
            ->with('success', 'Stock adjusted successfully.');
    }
}
