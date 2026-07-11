@extends('layouts.app')

@section('title', 'Categories')

@section('content')

<x-ui.page-header title="Categories" subtitle="View and manage product categories">
    <x-slot:action>
        @can('categories.create')
        <a href="{{ route('categories.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Add Category
        </a>
        @endcan
    </x-slot:action>
</x-ui.page-header>

{{-- Search --}}
<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('categories.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    class="form-control form-control-sm"
                    placeholder="Search by category name...">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
            @if($search)
            <div class="col-auto">
                <a href="{{ route('categories.index') }}" class="btn btn-link btn-sm text-muted">Clear</a>
            </div>
            @endif
        </form>
    </div>
</div>

<div class="card">
    @if($categories->isEmpty())
    <x-ui.empty-state
        icon="bi-grid"
        title="No categories found"
        description="{{ $search ? 'No categories match your search.' : 'Create your first category to get started.' }}">
        @can('categories.create')
        <a href="{{ route('categories.create') }}" class="btn btn-primary btn-sm mt-3">
            <i class="bi bi-plus-lg"></i> Add Category
        </a>
        @endcan
    </x-ui.empty-state>
    @else
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Products</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                <tr>
                    <td class="fw-medium">{{ $category->name }}</td>
                    <td class="text-muted">{{ Str::limit($category->description, 60) ?: '—' }}</td>
                    <td><x-ui.status-badge :status="$category->status" /></td>
                    <td>{{ $category->products_count }}</td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-1">
                            <a href="{{ route('categories.show', $category) }}" class="btn-action btn-action-view" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            @can('categories.edit')
                            <a href="{{ route('categories.edit', $category) }}" class="btn-action btn-action-edit" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endcan
                            @can('categories.delete')
                            @if($category->products_count > 0)
                            <span
                                class="btn-action btn-action-locked"
                                data-bs-toggle="tooltip"
                                title="Linked to products — cannot delete">
                                <i class="bi bi-lock"></i>
                            </span>
                            @else
                            <form action="{{ route('categories.destroy', $category) }}" method="POST"
                                onsubmit="return confirm('Delete this category? This cannot be undone.');">
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
            Showing {{ $categories->firstItem() }}–{{ $categories->lastItem() }} of {{ $categories->total() }}
        </small>
        {{ $categories->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@endsection