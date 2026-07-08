@extends('layouts.app')

@section('title', 'Sales')

@section('content')

<x-ui.page-header title="Sales" subtitle="Record and manage sales">
    <x-slot:action>
        @can('sales.create')
        <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> New Sale
        </a>
        @endcan
    </x-slot:action>
</x-ui.page-header>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('sales.index') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Any Status</option>
                    @foreach(['draft', 'confirmed', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? null)===$status)>
                        {{ ucfirst($status) }}
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
                <a href="{{ route('sales.index') }}" class="btn btn-link btn-sm text-muted">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    @if($sales->isEmpty())
    <x-ui.empty-state
        icon="bi-receipt"
        title="No sales found"
        description="Record your first sale to get started.">
        @can('sales.create')
        <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm mt-3">
            <i class="bi bi-plus-lg"></i> New Sale
        </a>
        @endcan
    </x-ui.empty-state>
    @else
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Created By</th>
                    <th>Discount</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales as $sale)
                <tr>
                    <td><code>{{ $sale->reference_number }}</code></td>
                    <td>{{ $sale->user->name }}</td>
                    <td>₹{{ number_format($sale->discount_amount, 2) }}</td>
                    <td class="fw-medium">₹{{ number_format($sale->total_amount, 2) }}</td>
                    <td><x-ui.status-badge :status="$sale->status" /></td>
                    <td class="text-muted small">{{ $sale->created_at->format('M d, Y') }}</td>
                    <td class="text-end">
                        <a href="{{ route('sales.show', $sale) }}" class="btn-action btn-action-view" title="View">
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
            Showing {{ $sales->firstItem() }}–{{ $sales->lastItem() }} of {{ $sales->total() }}
        </small>
        {{ $sales->links() }}
    </div>
    @endif
</div>

@endsection