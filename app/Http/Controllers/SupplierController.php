<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Repositories\SupplierRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

/**
 * SupplierController
 *
 * Handles HTTP requests for the Suppliers module. Contains no query
 * logic itself — every database interaction is delegated to
 * SupplierRepository.
 */
class SupplierController extends Controller
{
    /**
     * @param SupplierRepository $suppliers Injected automatically by the service container
     */
    public function __construct(private readonly SupplierRepository $suppliers) {}

    /**
     * Display a paginated, searchable list of suppliers.
     *
     * @param Request $request May contain a 'search' query string param
     * @return View
     */
    public function index(Request $request): View
    {
        $suppliers = $this->suppliers->getPaginatedWithSearch(
            search: $request->query('search'),
        );

        return view('suppliers.index', [
            'suppliers' => $suppliers,
            'search'    => $request->query('search', ''),
        ]);
    }

    /**
     * Show the form for creating a new supplier.
     *
     * @return View
     */
    public function create(): View
    {
        return view('suppliers.create');
    }

    /**
     * Persist a newly created supplier.
     *
     * @param StoreSupplierRequest $request
     * @return RedirectResponse
     */
    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request): void {
                $this->suppliers->create($request->validated());
            });
        } catch (Throwable $e) {
            Log::error('Supplier creation failed', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'Something went wrong while creating the supplier. Please try again.');
        }

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    /**
     * Display a single supplier's details, including linked product count.
     *
     * @param Supplier $supplier Resolved via route model binding
     * @return View
     */
    public function show(Supplier $supplier): View
    {
        return view('suppliers.show', [
            'supplier' => $supplier->loadCount('products'),
        ]);
    }

    /**
     * Show the pre-filled edit form for an existing supplier.
     *
     * @param Supplier $supplier
     * @return View
     */
    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', ['supplier' => $supplier]);
    }

    /**
     * Persist changes to an existing supplier.
     *
     * @param UpdateSupplierRequest $request
     * @param Supplier $supplier
     * @return RedirectResponse
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $supplier): void {
                $this->suppliers->update($supplier->id, $request->validated());
            });
        } catch (Throwable $e) {
            Log::error('Supplier update failed', [
                'supplier_id' => $supplier->id,
                'error'       => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Something went wrong while updating the supplier. Please try again.');
        }

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    /**
     * Soft-delete a supplier.
     *
     * Blocked if the supplier still has products sourced from them —
     * deleting it would orphan those products' supplier relationship.
     *
     * @param Supplier $supplier
     * @return RedirectResponse
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($this->suppliers->hasProducts($supplier->id)) {
            return back()->with(
                'error',
                "Cannot delete \"{$supplier->company_name}\" — it still has products linked to it. Reassign or remove those products first."
            );
        }

        try {
            DB::transaction(function () use ($supplier): void {
                $this->suppliers->delete($supplier->id);
            });
        } catch (Throwable $e) {
            Log::error('Supplier deletion failed', [
                'supplier_id' => $supplier->id,
                'error'       => $e->getMessage(),
            ]);

            return back()->with('error', 'Something went wrong while deleting the supplier. Please try again.');
        }

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }
}
