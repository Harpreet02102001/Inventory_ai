@extends('layouts.app')

@section('title', 'Adjust Stock — ' . $product->name)

@section('content')

<x-ui.page-header :title="$product->name" subtitle="Correct stock after a physical count" />

<div class="row">
    <div class="col-lg-5">
        <x-ui.alert type="warning">
            Use this only to correct the system's count after a physical stock check — not for routine restocking or sales. Every adjustment requires a reason and is permanently logged.
        </x-ui.alert>

        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">System's Current Count</span>
                    <span class="fs-4 fw-bold">{{ $product->stock_quantity }}</span>
                </div>

                <form method="POST" action="{{ route('stock.adjust', $product) }}">
                    @csrf

                    <div class="mb-3">
                        <label for="new_quantity" class="form-label">Actual Counted Quantity <span class="text-danger">*</span></label>
                        <input type="number" min="0" id="new_quantity" name="new_quantity"
                            value="{{ old('new_quantity', $product->stock_quantity) }}"
                            class="form-control @error('new_quantity') is-invalid @enderror" required>
                        @error('new_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label for="reason" class="form-label">Reason <span class="text-danger">*</span></label>
                        <input type="text" id="reason" name="reason" value="{{ old('reason') }}"
                            class="form-control @error('reason') is-invalid @enderror"
                            placeholder="e.g. Physical count discrepancy, damaged goods found" required>
                        @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-warning w-100">
                        <i class="bi bi-clipboard-check"></i> Apply Adjustment
                    </button>
                </form>
            </div>
        </div>
        <a href="{{ route('stock.edit', $product) }}" class="text-decoration-none d-block mt-2">
            <i class="bi bi-arrow-left"></i> Back to Stock Update
        </a>
    </div>
</div>

@endsection