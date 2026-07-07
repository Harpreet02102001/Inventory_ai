@extends('layouts.app')

@section('title', 'Purchase Orders')

@section('content')

<x-ui.page-header title="Purchase Orders" subtitle="Manage supplier orders and stock receipts">
    <x-slot:action>
        @can('purchase_orders.create')
        <a href="{{ route('purchase_orders.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> New Purchase Order
        </a>
        @endcan
    </x-slot:action>
</x-ui.page-header>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('purchase_orders.index') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Any Status</option>
                    @foreach(['draft', 'ordered', 'received', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? null)===$status)>
                        {{ ucfirst($status) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="supplier_id" class="form-select form-select-sm">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected(($filters['supplier_id'] ?? null)==$supplier->id)>
                        {{ $supplier->company_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
            <div class="col-auto">
                <a href="{{ route('purchase_orders.index') }}" class="btn btn-link btn-sm text-muted">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    @if($purchaseOrders->isEmpty())
    <x-ui.empty-state
        icon="bi-cart"
        title="No purchase orders found"
        description="Create your first purchase order to start restocking.">
        @can('purchase_orders.create')
        <a href="{{ route('purchase_orders.create') }}" class="btn btn-primary btn-sm mt-3">
            <i class="bi bi-plus-lg"></i> New Purchase Order
        </a>
        @endcan
    </x-ui.empty-state>
    @else
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Supplier</th>
                    <th>Created By</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseOrders as $po)
                <tr>
                    <td><code>{{ $po->reference_number }}</code></td>
                    <td>{{ $po->supplier->company_name }}</td>
                    <td>{{ $po->user->name }}</td>
                    <td>₹{{ number_format($po->total_amount, 2) }}</td>
                    <td><x-ui.status-badge :status="$po->status" /></td>
                    <td class="text-muted small">{{ $po->created_at->format('M d, Y') }}</td>
                    <td class="text-end">
                        <a href="{{ route('purchase_orders.show', $po) }}" class="btn-action btn-action-view" title="View">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="pagination-info d-flex justify-content-between align-items-center px-3 py-2">
        <small class="text-muted">
            Showing {{ $purchaseOrders->firstItem() }}–{{ $purchaseOrders->lastItem() }} of {{ $purchaseOrders->total() }}
        </small>
        {{ $purchaseOrders->links() }}
    </div>
    @endif
</div>

@endsection