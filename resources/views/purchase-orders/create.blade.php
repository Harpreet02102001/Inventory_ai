@extends('layouts.app')

@section('title', 'New Purchase Order')

@section('content')

<x-ui.page-header title="New Purchase Order" subtitle="Order stock from a supplier" />

@if($errors->any())
<x-ui.alert type="danger">
    Please fix the errors below before submitting.
</x-ui.alert>
@endif

<form method="POST" action="{{ route('purchase_orders.store') }}" id="po-form">
    @csrf

    <div class="row">
        <div class="col-lg-4 mb-3">
            <div class="card">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label for="supplier_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                        <select id="supplier_id" name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
                            <option value="">Select a supplier</option>
                            @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id')==$supplier->id)>
                                {{ $supplier->company_name }}
                            </option>
                            @endforeach
                        </select>
                        @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea id="notes" name="notes" rows="3"
                            class="form-control @error('notes') is-invalid @enderror"
                            placeholder="Optional note about this order">{{ old('notes') }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex justify-content-between border-top pt-3">
                        <span class="text-muted">Estimated Total</span>
                        <strong id="grand-total">₹0.00</strong>
                    </div>
                    <div class="form-text mb-3">
                        This total is for your reference only — the final total is calculated and stored by the server.
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-lg"></i> Create Purchase Order
                    </button>
                    <a href="{{ route('purchase_orders.index') }}" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong>Line Items</strong>
                    <button type="button" id="add-row-btn" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-plus-lg"></i> Add Item
                    </button>
                </div>
                @error('items')
                <div class="alert alert-danger m-3 mb-0 py-2">{{ $message }}</div>
                @enderror
                <div class="table-responsive">
                    <table class="table mb-0" id="items-table">
                        <thead>
                            <tr>
                                <th style="width: 32%">Product</th>
                                <th style="width: 15%">Quantity</th>
                                <th style="width: 18%">Unit Price</th>
                                <th style="width: 18%">Subtotal</th>
                                <th style="width: 7%"></th>
                            </tr>
                        </thead>
                        <tbody id="items-body">
                            {{-- Rows are injected here by JavaScript --}}
                        </tbody>
                    </table>
                </div>
                <div class="p-3 text-muted small" id="empty-hint">
                    Click "Add Item" to add your first product.
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Hidden template for ONE line-item row. Never rendered directly —
     JavaScript clones this content and injects it into #items-body. --}}
<template id="row-template">
    <tr class="item-row">
        <td>
            <select class="form-select form-select-sm product-select" required>
                <option value="">Select product</option>
                @foreach($products as $product)
                <option value="{{ $product->id }}" data-price="{{ $product->purchase_price }}">
                    {{ $product->name }} ({{ $product->sku }})
                </option>
                @endforeach
            </select>
        </td>
        <td><input type="number" min="1" value="1" class="form-control form-control-sm quantity-input" required></td>
        <td><input type="number" min="0.01" step="0.01" class="form-control form-control-sm price-input" required></td>
        <td><span class="subtotal-display">₹0.00</span></td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" title="Remove">
                <i class="bi bi-x-lg"></i>
            </button>
        </td>
    </tr>
</template>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const itemsBody = document.getElementById('items-body');
        const template = document.getElementById('row-template');
        const addRowBtn = document.getElementById('add-row-btn');
        const grandTotalEl = document.getElementById('grand-total');
        const emptyHint = document.getElementById('empty-hint');
        const form = document.getElementById('po-form');

        // Old input re-population if the form was redisplayed after a
        // validation error — without this, a failed submission would wipe
        // out every row the Admin already filled in.
        const oldItems = @json(old('items', []));

        let rowIndex = 0;

        /**
         * Add one new line-item row to the table, cloned from the hidden
         * <template>. If oldData is provided, pre-fills the row's inputs
         * (used when redisplaying the form after a validation failure).
         *
         * @param {object|null} oldData Optional {product_id, quantity, unit_price} to restore
         */
        function addRow(oldData = null) {
            const clone = template.content.cloneNode(true);
            const row = clone.querySelector('.item-row');

            const select = row.querySelector('.product-select');
            const qtyInput = row.querySelector('.quantity-input');
            const priceInput = row.querySelector('.price-input');

            // Setting `name` here (not in the template) is what makes each
            // cloned row submit as items[0][...], items[1][...], etc. —
            // matching the items.*.field validation rules on the server.
            select.name = `items[${rowIndex}][product_id]`;
            qtyInput.name = `items[${rowIndex}][quantity]`;
            priceInput.name = `items[${rowIndex}][unit_price]`;

            if (oldData) {
                select.value = oldData.product_id ?? '';
                qtyInput.value = oldData.quantity ?? 1;
                priceInput.value = oldData.unit_price ?? '';
            }

            // Auto-fill unit price from the product's stored purchase_price
            // when a product is selected — a convenience default, not a
            // locked value; the Admin can still overwrite it manually.
            select.addEventListener('change', function() {
                const selectedOption = select.options[select.selectedIndex];
                const price = selectedOption?.dataset?.price;
                if (price && !priceInput.value) {
                    priceInput.value = parseFloat(price).toFixed(2);
                }
                recalcRow(row);
            });

            qtyInput.addEventListener('input', () => recalcRow(row));
            priceInput.addEventListener('input', () => recalcRow(row));

            row.querySelector('.remove-row-btn').addEventListener('click', function() {
                if (itemsBody.children.length <= 1) {
                    alert('A purchase order needs at least one line item.');
                    return;
                }
                row.remove();
                recalcGrandTotal();
            });

            itemsBody.appendChild(clone);
            rowIndex++;
            emptyHint.style.display = 'none';
            recalcRow(row);
        }

        /**
         * Recalculate a single row's displayed subtotal (quantity × unit
         * price), then recalculate the grand total across all rows. This
         * is DISPLAY ONLY — the actual stored total is calculated server-side
         * in PurchaseOrderService, never trusted from this client-side math.
         *
         * @param {HTMLElement} row The <tr> element to recalculate
         */
        function recalcRow(row) {
            const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            row.querySelector('.subtotal-display').textContent = '₹' + (qty * price).toFixed(2);
            recalcGrandTotal();
        }

        /**
         * Sum every row's subtotal into the displayed grand total.
         */
        function recalcGrandTotal() {
            let total = 0;
            itemsBody.querySelectorAll('.item-row').forEach(function(row) {
                const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
                const price = parseFloat(row.querySelector('.price-input').value) || 0;
                total += qty * price;
            });
            grandTotalEl.textContent = '₹' + total.toFixed(2);
        }

        addRowBtn.addEventListener('click', () => addRow());

        // Prevent submitting an order with zero line items — the server
        // would reject it anyway ('items' => 'required|array|min:1'), but
        // catching it client-side avoids an unnecessary round trip.
        form.addEventListener('submit', function(e) {
            if (itemsBody.children.length === 0) {
                e.preventDefault();
                alert('Add at least one product before submitting.');
            }
        });

        // Restore previous rows on validation failure, or start with one
        // empty row on a fresh page load.
        if (oldItems.length > 0) {
            oldItems.forEach(item => addRow(item));
        } else {
            addRow();
        }
    });
</script>
@endpush

@endsection