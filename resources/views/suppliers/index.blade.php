@extends('layouts.app')

@section('title', 'Suppliers')

@section('content')

<x-ui.page-header title="Suppliers" subtitle="Manage your product suppliers">
    <x-slot:action>
        @can('suppliers.create')
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Add Supplier
        </a>
        @endcan
    </x-slot:action>
</x-ui.page-header>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('suppliers.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    class="form-control form-control-sm"
                    placeholder="Search by name, email, or company...">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
            @if($search)
            <div class="col-auto">
                <a href="{{ route('suppliers.index') }}" class="btn btn-link btn-sm text-muted">Clear</a>
            </div>
            @endif
        </form>
    </div>
</div>

<div class="card">
    @if($suppliers->isEmpty())
    <x-ui.empty-state
        icon="bi-truck"
        title="No suppliers found"
        description="{{ $search ? 'No suppliers match your search.' : 'Add your first supplier to get started.' }}">
        @can('suppliers.create')
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary btn-sm mt-3">
            <i class="bi bi-plus-lg"></i> Add Supplier
        </a>
        @endcan
    </x-ui.empty-state>
    @else
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Contact</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($suppliers as $supplier)
                <tr>
                    <td class="fw-medium">{{ $supplier->company_name }}</td>
                    <td>{{ $supplier->name }}</td>
                    <td class="text-muted">{{ $supplier->email }}</td>
                    <td>{{ $supplier->phone }}</td>
                    <td><x-ui.status-badge :status="$supplier->status" /></td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-1">
                            <a href="{{ route('suppliers.show', $supplier) }}" class="btn-action btn-action-view" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            @can('suppliers.edit')
                            <a href="{{ route('suppliers.edit', $supplier) }}" class="btn-action btn-action-edit" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endcan
                            @can('suppliers.delete')
                            @if($supplier->products()->exists())
                            <span class="btn-action btn-action-locked" data-bs-toggle="tooltip" title="Linked to products — cannot delete">
                                <i class="bi bi-lock"></i>
                            </span>
                            @else
                            <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST"
                                onsubmit="return confirm('Delete this supplier? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-action-delete" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
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
            Showing {{ $suppliers->firstItem() }}–{{ $suppliers->lastItem() }} of {{ $suppliers->total() }}
        </small>
        {{ $suppliers->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@endsection