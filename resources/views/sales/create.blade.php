@extends('layouts.app')

@section('title', 'New Sale')

@section('content')

<x-ui.page-header title="New Sale" subtitle="Record a new sale" />

@if($errors->any())
<x-ui.alert type="danger">
    Please fix the errors below before submitting.
</x-ui.alert>
@endif

<form method="POST" action="{{ route('sales.store') }}" id="sale-form">
    @csrf

    <div class="row">
        <div class="col-lg-4 mb-3">
            <div class="card">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label for="discount_amount" class="form-label">Discount Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" min="0" id="discount_amount" name="discount_amount"
                                value="{{ old('discount_amount', 0) }}"
                                class="form-control @error('discount_amount') is-invalid @enderror">
                        </div>
                        @error('discount_amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea id="notes" name="notes" rows="3"
                            class="form-control @error('notes') is-invalid @enderror"
                            placeholder="Optional note about this sale">{{ old('notes') }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Items Subtotal</span>
                        <span id="items-subtotal">₹0.00</span>
                    </div>
                    <div class="d-flex justify-content-between border-top pt-2 mt-2">
                        <span class="text-muted">Grand Total</span>
                        <strong id="grand-total">₹0.00</strong>
                    </div>
                    <div class="form-text mb-3">
                        For your reference only — the server calculates and stores the final total.
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-lg"></i> Save as Draft
                    </button>
                    <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
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
                        <tbody id="items-body"></tbody>
                    </table>
                </div>
                <div class="p-3 text-muted small" id="empty-hint">
                    Click "Add Item" to add your first product.
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Hidden template for ONE line-item row — cloned by JS, never rendered directly. --}}
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
        const itemsSubtotalEl = document.getElementById('items-subtotal');
        const grandTotalEl = document.getElementById('grand-total');
        const emptyHint = document.getElementById('empty-hint');
        const discountInput = document.getElementById('discount_amount');
        const form = document.getElementById('sale-form');

        const oldItems = @json(old('items', []));
        let rowIndex = 0;

        /**
         * Add one new line-item row, cloned from the hidden <template>.
         * Restores previous values via oldData if the form was redisplayed
         * after a validation error, so a failed submission doesn't wipe out
         * everything the user already entered.
         *
         * @param {object|null} oldData Optional {product_id, quantity, unit_price}
         */
        function addRow(oldData = null) {
            const clone = template.content.cloneNode(true);
            const row = clone.querySelector('.item-row');

            const select = row.querySelector('.product-select');
            const qtyInput = row.querySelector('.quantity-input');
            const priceInput = row.querySelector('.price-input');

            select.name = `items[${rowIndex}][product_id]`;
            qtyInput.name = `items[${rowIndex}][quantity]`;
            priceInput.name = `items[${rowIndex}][unit_price]`;

            if (oldData) {
                select.value = oldData.product_id ?? '';
                qtyInput.value = oldData.quantity ?? 1;
                priceInput.value = oldData.unit_price ?? '';
            }

            // Auto-fills from purchase_price as a starting point — Admin
            // can freely override for the actual selling price per sale.
            select.addEventListener('change', function() {
                const price = select.options[select.selectedIndex]?.dataset?.price;
                if (price && !priceInput.value) {
                    priceInput.value = parseFloat(price).toFixed(2);
                }
                recalcRow(row);
            });

            qtyInput.addEventListener('input', () => recalcRow(row));
            priceInput.addEventListener('input', () => recalcRow(row));

            row.querySelector('.remove-row-btn').addEventListener('click', function() {
                if (itemsBody.children.length <= 1) {
                    alert('A sale needs at least one line item.');
                    return;
                }
                row.remove();
                recalcTotals();
            });

            itemsBody.appendChild(clone);
            rowIndex++;
            emptyHint.style.display = 'none';
            recalcRow(row);
        }

        /** Recalculate one row's displayed subtotal, then the grand total. */
        function recalcRow(row) {
            const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            row.querySelector('.subtotal-display').textContent = '₹' + (qty * price).toFixed(2);
            recalcTotals();
        }

        /**
         * Sum every row's subtotal, subtract the discount, display both —
         * DISPLAY ONLY. The real total is calculated server-side in
         * SaleService, never trusted from this client-side arithmetic.
         */
        function recalcTotals() {
            let subtotal = 0;
            itemsBody.querySelectorAll('.item-row').forEach(function(row) {
                const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
                const price = parseFloat(row.querySelector('.price-input').value) || 0;
                subtotal += qty * price;
            });
            const discount = parseFloat(discountInput.value) || 0;
            itemsSubtotalEl.textContent = '₹' + subtotal.toFixed(2);
            grandTotalEl.textContent = '₹' + Math.max(0, subtotal - discount).toFixed(2);
        }

        discountInput.addEventListener('input', recalcTotals);
        addRowBtn.addEventListener('click', () => addRow());

        form.addEventListener('submit', function(e) {
            if (itemsBody.children.length === 0) {
                e.preventDefault();
                alert('Add at least one product before submitting.');
            }
        });

        if (oldItems.length > 0) {
            oldItems.forEach(item => addRow(item));
        } else {
            addRow();
        }
    });
</script>
@endpush

@endsection