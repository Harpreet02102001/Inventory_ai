@extends('layouts.app')

@section('title', $category->name)

@section('content')

<x-ui.page-header :title="$category->name" subtitle="Category details">
    <x-slot:action>
        @can('categories.edit')
        <a href="{{ route('categories.edit', $category) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil"></i> Edit
        </a>
        @endcan
    </x-slot:action>
</x-ui.page-header>

<div class="card">
    <div class="card-body p-4">
        <dl class="row mb-0">
            <dt class="col-sm-3 text-muted">Name</dt>
            <dd class="col-sm-9">{{ $category->name }}</dd>

            <dt class="col-sm-3 text-muted">Description</dt>
            <dd class="col-sm-9">{{ $category->description ?: '—' }}</dd>

            <dt class="col-sm-3 text-muted">Status</dt>
            <dd class="col-sm-9"><x-ui.status-badge :status="$category->status" /></dd>

            <dt class="col-sm-3 text-muted">Products in this category</dt>
            <dd class="col-sm-9">{{ $category->products_count }}</dd>

            <dt class="col-sm-3 text-muted">Created</dt>
            <dd class="col-sm-9">{{ $category->created_at->format('M d, Y \a\t g:i A') }}</dd>
        </dl>
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('categories.index') }}" class="text-decoration-none">
        <i class="bi bi-arrow-left"></i> Back to Categories
    </a>
</div>

@endsection