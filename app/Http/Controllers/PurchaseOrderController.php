<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\InvalidPurchaseOrderStatusException;
use App\Http\Requests\PurchaseOrder\StorePurchaseOrderRequest;
use App\Models\PurchaseOrder;
use App\Repositories\PurchaseOrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\SupplierRepository;
use App\Services\PurchaseOrderService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * PurchaseOrderController
 *
 * Handles HTTP requests for Purchase Orders. All business logic —
 * total calculation, status transitions, stock effects on receiving —
 * lives in PurchaseOrderService; this controller only translates
 * HTTP in and out.
 */
class PurchaseOrderController extends Controller
{
    /**
     * @param PurchaseOrderService $purchaseOrderService
     * @param PurchaseOrderRepository $purchaseOrders
     * @param SupplierRepository $suppliers Populates the supplier dropdown
     * @param ProductRepository $products Populates the product picker for line items
     */
    public function __construct(
        private readonly PurchaseOrderService $purchaseOrderService,
        private readonly PurchaseOrderRepository $purchaseOrders,
        private readonly SupplierRepository $suppliers,
        private readonly ProductRepository $products,
    ) {}

    /**
     * Display a paginated, filterable list of purchase orders.
     *
     * @param Request $request May contain 'status' and 'supplier_id' filters
     * @return View
     */
    public function index(Request $request): View
    {
        $purchaseOrders = $this->purchaseOrders->getPaginatedWithFilters(
            perPage: 15,
            status: $request->query('status'),
            supplierId: $request->query('supplier_id') ? (int) $request->query('supplier_id') : null,
        );

        return view('purchase-orders.index', [
            'purchaseOrders' => $purchaseOrders,
            'suppliers'      => $this->suppliers->getActiveForSelect(),
            'filters'        => $request->only(['status', 'supplier_id']),
        ]);
    }

    /**
     * Show the form for creating a new purchase order.
     *
     * @return View
     */
    public function create(): View
    {
        return view('purchase-orders.create', [
            'suppliers' => $this->suppliers->getActiveForSelect(),
            'products'  => $this->products->getAllForSelect(),
        ]);
    }

    /**
     * Persist a new purchase order with its line items.
     *
     * @param StorePurchaseOrderRequest $request
     * @return RedirectResponse
     */
    public function store(StorePurchaseOrderRequest $request): RedirectResponse
    {
        try {
            $purchaseOrder = $this->purchaseOrderService->createPurchaseOrder(
                data: $request->safe()->only(['supplier_id', 'notes']),
                items: $request->validated('items'),
                userId: $request->user()->id,
            );
        } catch (Throwable $e) {
            Log::error('Purchase order creation failed', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'Something went wrong while creating the purchase order. Please try again.');
        }

        return redirect()
            ->route('purchase_orders.show', $purchaseOrder)
            ->with('success', "Purchase order {$purchaseOrder->reference_number} created successfully.");
    }

    /**
     * Display a single purchase order with its line items.
     *
     * @param PurchaseOrder $purchaseOrder
     * @return View
     */
    public function show(PurchaseOrder $purchaseOrder): View
    {
        return view('purchase-orders.show', [
            'purchaseOrder' => $purchaseOrder->load(['supplier', 'user', 'items.product']),
        ]);
    }

    /**
     * Transition a draft order to 'ordered'.
     *
     * @param PurchaseOrder $purchaseOrder
     * @return RedirectResponse
     */
    public function markAsOrdered(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        try {
            $this->purchaseOrderService->markAsOrdered($purchaseOrder->id);
        } catch (InvalidPurchaseOrderStatusException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            Log::error('Marking PO as ordered failed', ['po_id' => $purchaseOrder->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'Something went wrong. Please try again.');
        }

        return back()->with('success', 'Order marked as ordered.');
    }

    /**
     * Receive a purchase order — increases stock for every line item.
     *
     * @param Request $request Only used to get the authenticated user
     * @param PurchaseOrder $purchaseOrder
     * @return RedirectResponse
     */
    public function receive(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        try {
            $this->purchaseOrderService->receivePurchaseOrder($purchaseOrder->id, $request->user()->id);
        } catch (InvalidPurchaseOrderStatusException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            Log::error('Receiving PO failed', ['po_id' => $purchaseOrder->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'Something went wrong while receiving this order. Please try again.');
        }

        return back()->with('success', 'Order received — stock has been updated.');
    }

    /**
     * Cancel a purchase order.
     *
     * @param PurchaseOrder $purchaseOrder
     * @return RedirectResponse
     */
    public function cancel(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        try {
            $this->purchaseOrderService->cancelPurchaseOrder($purchaseOrder->id);
        } catch (InvalidPurchaseOrderStatusException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            Log::error('Cancelling PO failed', ['po_id' => $purchaseOrder->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'Something went wrong. Please try again.');
        }

        return back()->with('success', 'Order cancelled.');
    }
}
