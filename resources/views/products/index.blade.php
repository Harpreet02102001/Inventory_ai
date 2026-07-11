@extends('layouts.app')

@section('title', 'Products')

@section('content')

<x-ui.page-header title="Products" subtitle="Manage your product catalog">
    <x-slot:action>
        @can('products.create')
        <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Add Product
        </a>
        @endcan
    </x-slot:action>
</x-ui.page-header>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('products.index') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                    class="form-control form-control-sm" placeholder="Search name or SKU...">
            </div>
            <div class="col-md-2">
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? null)==$category->id)>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="supplier_id" class="form-select form-select-sm">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected(($filters['supplier_id'] ?? null)==$supplier->id)>
                        {{ $supplier->company_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Any Status</option>
                    <option value="active" @selected(($filters['status'] ?? null)==='active' )>Active</option>
                    <option value="inactive" @selected(($filters['status'] ?? null)==='inactive' )>Inactive</option>
                </select>
            </div>
            <div class="col-md-auto form-check ms-2">
                <input type="checkbox" name="low_stock" value="1" id="low_stock" class="form-check-input"
                    @checked(($filters['low_stock'] ?? null))>
                <label for="low_stock" class="form-check-label small">Low stock only</label>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
            <div class="col-auto">
                <a href="{{ route('products.index') }}" class="btn btn-link btn-sm text-muted">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    @if($products->isEmpty())
    <x-ui.empty-state
        icon="bi-box-seam"
        title="No products found"
        description="Try adjusting your filters, or add your first product.">
        @can('products.create')
        <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm mt-3">
            <i class="bi bi-plus-lg"></i> Add Product
        </a>
        @endcan
    </x-ui.empty-state>
    @else
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th></th>
                    <th>Name</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>Supplier</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                    <td>
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                            class="rounded" style="width:40px;height:40px;object-fit:cover;">
                    </td>
                    <td class="fw-medium">{{ $product->name }}</td>
                    <td><code>{{ $product->sku }}</code></td>
                    <td>{{ $product->category->name }}</td>
                    <td>{{ $product->supplier->company_name }}</td>
                    <td>₹{{ $product->selling_price }}</td>
                    <td>
                        {{ $product->stock_quantity }}
                        @if($product->isLowStock())
                        <span class="badge badge-inactive ms-1">Low</span>
                        @endif
                    </td>
                    <td><x-ui.status-badge :status="$product->status" /></td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-1">
                            <a href="{{ route('products.show', $product) }}" class="btn-action btn-action-view" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            @can('products.edit')
                            <a href="{{ route('products.edit', $product) }}" class="btn-action btn-action-edit" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endcan
                            @can('products.delete')
                            <form action="{{ route('products.destroy', $product) }}" method="POST"
                                onsubmit="return confirm('Delete this product? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-action-delete" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="pagination-info d-flex justify-content-between align-items-center px-3 py-2">
        <small class="text-muted">
            Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }}
        </small>
        {{ $products->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@endsection