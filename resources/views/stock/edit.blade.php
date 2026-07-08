@extends('layouts.app')

@section('title', 'Update Stock — ' . $product->name)

@section('content')

<x-ui.page-header :title="$product->name" subtitle="Update stock quantity" />

<div class="row">
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Current Stock</span>
                    <span class="fs-3 fw-bold">{{ $product->stock_quantity }}</span>
                </div>

                @if($product->isLowStock())
                <x-ui.alert type="warning">
                    This product is at or below its low stock threshold ({{ $product->low_stock_threshold }}).
                </x-ui.alert>
                @endif

                <form method="POST" action="{{ route('stock.update', $product) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Action <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input type="radio" name="type" value="add" id="type_add" class="form-check-input"
                                    @checked(old('type', 'add' )==='add' )>
                                <label for="type_add" class="form-check-label">Add Stock</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" name="type" value="reduce" id="type_reduce" class="form-check-input"
                                    @checked(old('type')==='reduce' )>
                                <label for="type_reduce" class="form-check-label">Reduce Stock</label>
                            </div>
                        </div>
                        @error('type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" min="1" id="quantity" name="quantity" value="{{ old('quantity') }}"
                            class="form-control @error('quantity') is-invalid @enderror" required>
                        @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label for="remarks" class="form-label">Remarks</label>
                        <input type="text" id="remarks" name="remarks" value="{{ old('remarks') }}"
                            class="form-control @error('remarks') is-invalid @enderror"
                            placeholder="Optional note, e.g. 'Damaged units removed'">
                        @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-lg"></i> Apply Stock Change
                    </button>
                </form>
            </div>
        </div>

        @can('stock.adjust')
        <a href="{{ route('stock.adjust-form', $product) }}" class="text-decoration-none small d-block mb-2">
            <i class="bi bi-clipboard-check"></i> Need to correct stock after a count instead?
        </a>
        @endcan

        <a href="{{ route('products.show', $product) }}" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> Back to Product
        </a>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header bg-white"><strong>Stock History</strong></div>

            @if($history->isEmpty())
            <x-ui.empty-state icon="bi-clock-history" title="No stock changes yet"
                description="Changes will appear here once recorded." />
            @else
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Old</th>
                            <th>Change</th>
                            <th>New</th>
                            <th>By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $entry)
                        <tr>
                            <td class="text-muted small">{{ $entry->created_at->format('M d, Y g:i A') }}</td>
                            <td>
                                <span class="badge {{ $entry->changed_quantity >= 0 ? 'badge-active' : 'badge-inactive' }}">
                                    {{ ucfirst($entry->type) }}
                                </span>
                            </td>
                            <td>{{ $entry->old_quantity }}</td>
                            <td class="{{ $entry->changed_quantity >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $entry->changed_quantity >= 0 ? '+' : '' }}{{ $entry->changed_quantity }}
                            </td>
                            <td class="fw-medium">{{ $entry->new_quantity }}</td>
                            <td class="small">{{ $entry->user->name }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2">{{ $history->links() }}</div>
            @endif
        </div>
    </div>
</div>

@endsection