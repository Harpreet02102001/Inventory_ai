@extends('layouts.app')

@section('title', 'Stock Overview')

@section('content')

<x-ui.page-header :title="$lowStockOnly ? 'Low Stock Items' : 'Stock Overview'"
    subtitle="{{ $lowStockOnly ? 'Products at or below their threshold' : 'Current stock levels across all products' }}" />

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('stock.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" value="{{ $search }}"
                    class="form-control form-control-sm" placeholder="Search name or SKU...">
            </div>
            <div class="col-auto form-check">
                <input type="checkbox" name="low_stock" value="1" id="low_stock" class="form-check-input"
                    @checked($lowStockOnly) onchange="this.form.submit()">
                <label for="low_stock" class="form-check-label small">Low stock only</label>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    @if($products->isEmpty())
    <x-ui.empty-state icon="bi-boxes" title="No products found"
        description="{{ $lowStockOnly ? 'Nothing is currently low on stock.' : 'No products match your search.' }}" />
    @else
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Stock</th>
                    <th>Threshold</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                    <td class="fw-medium">{{ $product->name }}</td>
                    <td><code>{{ $product->sku }}</code></td>
                    <td>
                        {{ $product->stock_quantity }}
                        @if($product->isLowStock())
                        <span class="badge badge-inactive ms-1">Low</span>
                        @endif
                    </td>
                    <td class="text-muted">{{ $product->low_stock_threshold }}</td>
                    <td><x-ui.status-badge :status="$product->status" /></td>
                    <td class="text-end">
                        @can('stock.update')
                        <a href="{{ route('stock.edit', $product) }}" class="btn btn-sm btn-outline-primary">
                            Update Stock
                        </a>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="pagination-info d-flex justify-content-between align-items-center px-3 py-2">
        <small class="text-muted">Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }}</small>
        {{ $products->links() }}
    </div>
    @endif
</div>

@endsection