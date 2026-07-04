@extends('layouts.app')

@section('title', $product->name)

@section('content')

<x-ui.page-header :title="$product->name" subtitle="Product details">
    <x-slot:action>
        @can('products.edit')
        <a href="{{ route('products.edit', $product) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil"></i> Edit
        </a>
        @endcan
    </x-slot:action>
</x-ui.page-header>

<div class="row">
    <div class="col-md-3">
        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="img-fluid rounded border">
    </div>
    <div class="col-md-9">
        <div class="card">
            <div class="card-body p-4">
                <dl class="row mb-0">
                    <dt class="col-sm-3 text-muted">SKU</dt>
                    <dd class="col-sm-9"><code>{{ $product->sku }}</code></dd>

                    <dt class="col-sm-3 text-muted">Category</dt>
                    <dd class="col-sm-9">{{ $product->category->name }}</dd>

                    <dt class="col-sm-3 text-muted">Supplier</dt>
                    <dd class="col-sm-9">{{ $product->supplier->company_name }}</dd>

                    <dt class="col-sm-3 text-muted">Description</dt>
                    <dd class="col-sm-9">{{ $product->description ?: '—' }}</dd>

                    <dt class="col-sm-3 text-muted">Purchase Price</dt>
                    <dd class="col-sm-9">₹{{ $product->purchase_price }}</dd>

                    <dt class="col-sm-3 text-muted">Selling Price</dt>
                    <dd class="col-sm-9">₹{{ $product->selling_price }}</dd>

                    <dt class="col-sm-3 text-muted">Stock Quantity</dt>
                    <dd class="col-sm-9">
                        {{ $product->stock_quantity }}
                        @if($product->isLowStock())
                        <span class="badge badge-inactive ms-1">Low Stock</span>
                        @endif
                    </dd>

                    <dt class="col-sm-3 text-muted">Status</dt>
                    <dd class="col-sm-9"><x-ui.status-badge :status="$product->status" /></dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('products.index') }}" class="text-decoration-none">
        <i class="bi bi-arrow-left"></i> Back to Products
    </a>
</div>

@endsection