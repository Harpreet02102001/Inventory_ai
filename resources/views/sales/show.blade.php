@extends('layouts.app')

@section('title', $sale->reference_number)

@section('content')

<x-ui.page-header :title="$sale->reference_number" subtitle="Sale details">
    <x-slot:action>
        <div class="d-flex gap-2">
            @can('sales.edit')
            @if($sale->status === 'draft')
            <form action="{{ route('sales.confirm', $sale) }}" method="POST"
                onsubmit="return confirm('Confirming this sale will deduct stock for every line item. Continue?');">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="bi bi-check-lg"></i> Confirm Sale
                </button>
            </form>

            <form action="{{ route('sales.cancel', $sale) }}" method="POST"
                onsubmit="return confirm('Cancel this sale?');">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-x-lg"></i> Cancel Sale
                </button>
            </form>
            @endif
            @endcan
        </div>
    </x-slot:action>
</x-ui.page-header>

<div class="row">
    <div class="col-lg-4 mb-3">
        <div class="card">
            <div class="card-body p-4">
                <dl class="row mb-0">
                    <dt class="col-sm-5 text-muted">Status</dt>
                    <dd class="col-sm-7"><x-ui.status-badge :status="$sale->status" /></dd>

                    <dt class="col-sm-5 text-muted">Created By</dt>
                    <dd class="col-sm-7">{{ $sale->user->name }}</dd>

                    <dt class="col-sm-5 text-muted">Date</dt>
                    <dd class="col-sm-7">{{ $sale->created_at->format('M d, Y g:i A') }}</dd>

                    @if($sale->notes)
                    <dt class="col-sm-5 text-muted">Notes</dt>
                    <dd class="col-sm-7">{{ $sale->notes }}</dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white"><strong>Line Items</strong></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td><code>{{ $item->product->sku }}</code></td>
                            <td>{{ $item->quantity }}</td>
                            <td>₹{{ number_format($item->unit_price, 2) }}</td>
                            <td>₹{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Subtotal</th>
                            <th>₹{{ number_format($sale->total_amount, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end text-muted">Discount</th>
                            <th class="text-muted">−₹{{ number_format($sale->discount_amount, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end">Net Total</th>
                            <th>₹{{ number_format($sale->total_amount - $sale->discount_amount, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('sales.index') }}" class="text-decoration-none">
        <i class="bi bi-arrow-left"></i> Back to Sales
    </a>
</div>

@endsection