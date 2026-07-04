@extends('layouts.app')

@section('title', $supplier->company_name)

@section('content')

<x-ui.page-header :title="$supplier->company_name" subtitle="Supplier details">
    <x-slot:action>
        @can('suppliers.edit')
        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil"></i> Edit
        </a>
        @endcan
    </x-slot:action>
</x-ui.page-header>

<div class="card">
    <div class="card-body p-4">
        <dl class="row mb-0">
            <dt class="col-sm-3 text-muted">Company Name</dt>
            <dd class="col-sm-9">{{ $supplier->company_name }}</dd>

            <dt class="col-sm-3 text-muted">Contact Person</dt>
            <dd class="col-sm-9">{{ $supplier->name }}</dd>

            <dt class="col-sm-3 text-muted">Email</dt>
            <dd class="col-sm-9">{{ $supplier->email }}</dd>

            <dt class="col-sm-3 text-muted">Phone</dt>
            <dd class="col-sm-9">{{ $supplier->phone }}</dd>

            <dt class="col-sm-3 text-muted">Address</dt>
            <dd class="col-sm-9">{{ $supplier->address ?: '—' }}</dd>

            <dt class="col-sm-3 text-muted">Status</dt>
            <dd class="col-sm-9"><x-ui.status-badge :status="$supplier->status" /></dd>

            <dt class="col-sm-3 text-muted">Products Supplied</dt>
            <dd class="col-sm-9">{{ $supplier->products_count }}</dd>
        </dl>
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('suppliers.index') }}" class="text-decoration-none">
        <i class="bi bi-arrow-left"></i> Back to Suppliers
    </a>
</div>

@endsection