<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidSaleStatusException;
use App\Http\Requests\Sale\StoreSaleRequest;
use App\Models\Sale;
use App\Repositories\ProductRepository;
use App\Repositories\SaleRepository;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

/**
 * SaleController
 *
 * Handles HTTP requests for Sales. All business logic — total
 * calculation, status transitions, stock deduction on confirmation —
 * lives in SaleService; this controller only translates HTTP in and out.
 */
class SaleController extends Controller
{
    /**
     * @param SaleService $saleService
     * @param SaleRepository $sales
     * @param ProductRepository $products Populates the product picker for line items
     */
    public function __construct(
        private readonly SaleService $saleService,
        private readonly SaleRepository $sales,
        private readonly ProductRepository $products,
    ) {}

    /**
     * Display a paginated, filterable list of sales.
     *
     * @param Request $request May contain a 'status' filter
     * @return View
     */
    public function index(Request $request): View
    {
        $sales = $this->sales->getPaginatedWithFilters(
            perPage: 15,
            status: $request->query('status'),
        );

        return view('sales.index', [
            'sales'   => $sales,
            'filters' => $request->only(['status']),
        ]);
    }

    /**
     * Show the form for creating a new sale.
     *
     * @return View
     */
    public function create(): View
    {
        return view('sales.create', [
            'products' => $this->products->getAllForSelect(),
        ]);
    }

    /**
     * Persist a new sale with its line items, in 'draft' status.
     *
     * @param StoreSaleRequest $request
     * @return RedirectResponse
     */
    public function store(StoreSaleRequest $request): RedirectResponse
    {
        try {
            $sale = $this->saleService->createSale(
                data: $request->safe()->only(['discount_amount', 'notes']),
                items: $request->validated('items'),
                userId: $request->user()->id,
            );
        } catch (Throwable $e) {
            Log::error('Sale creation failed', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'Something went wrong while creating the sale. Please try again.');
        }

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', "Sale {$sale->reference_number} created as a draft.");
    }

    /**
     * Display a single sale with its line items.
     *
     * @param Sale $sale
     * @return View
     */
    public function show(Sale $sale): View
    {
        return view('sales.show', [
            'sale' => $sale->load(['user', 'items.product']),
        ]);
    }

    /**
     * Confirm a draft sale — deducts stock for every line item.
     *
     * InsufficientStockException is an EXPECTED outcome (not enough
     * stock to fulfill this sale) — caught specifically, with its
     * precise message shown directly to the user, since it tells them
     * exactly what to fix (e.g. reduce quantity, remove an item).
     *
     * @param Request $request Only used to get the authenticated user
     * @param Sale $sale
     * @return RedirectResponse
     */
    public function confirm(Request $request, Sale $sale): RedirectResponse
    {
        try {
            $this->saleService->confirmSale($sale->id, $request->user()->id);
        } catch (InsufficientStockException | InvalidSaleStatusException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            Log::error('Sale confirmation failed', ['sale_id' => $sale->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'Something went wrong while confirming this sale. Please try again.');
        }

        return back()->with('success', 'Sale confirmed — stock has been updated.');
    }

    /**
     * Cancel a draft sale.
     *
     * @param Sale $sale
     * @return RedirectResponse
     */
    public function cancel(Sale $sale): RedirectResponse
    {
        try {
            $this->saleService->cancelSale($sale->id);
        } catch (InvalidSaleStatusException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            Log::error('Sale cancellation failed', ['sale_id' => $sale->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'Something went wrong. Please try again.');
        }

        return back()->with('success', 'Sale cancelled.');
    }
}
