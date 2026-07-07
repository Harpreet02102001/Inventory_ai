@extends('layouts.app')

@section('title', $purchaseOrder->reference_number)

@section('content')

<x-ui.page-header :title="$purchaseOrder->reference_number" subtitle="Purchase order details">
    <x-slot:action>
        <div class="d-flex gap-2">
            @can('purchase_orders.edit')
            @if($purchaseOrder->status === 'draft')
            <form action="{{ route('purchase_orders.mark-as-ordered', $purchaseOrder) }}" method="POST">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-warning btn-sm">
                    <i class="bi bi-send"></i> Mark as Ordered
                </button>
            </form>
            @endif

            @if($purchaseOrder->status === 'ordered')
            <form action="{{ route('purchase_orders.receive', $purchaseOrder) }}" method="POST"
                onsubmit="return confirm('Receiving this order will increase stock for every line item. Continue?');">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="bi bi-box-arrow-in-down"></i> Mark as Received
                </button>
            </form>
            @endif

            @if(in_array($purchaseOrder->status, ['draft', 'ordered']))
            <form action="{{ route('purchase_orders.cancel', $purchaseOrder) }}" method="POST"
                onsubmit="return confirm('Cancel this purchase order?');">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-x-lg"></i> Cancel Order
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
                    <dd class="col-sm-7"><x-ui.status-badge :status="$purchaseOrder->status" /></dd>

                    <dt class="col-sm-5 text-muted">Supplier</dt>
                    <dd class="col-sm-7">{{ $purchaseOrder->supplier->company_name }}</dd>

                    <dt class="col-sm-5 text-muted">Created By</dt>
                    <dd class="col-sm-7">{{ $purchaseOrder->user->name }}</dd>

                    <dt class="col-sm-5 text-muted">Created</dt>
                    <dd class="col-sm-7">{{ $purchaseOrder->created_at->format('M d, Y g:i A') }}</dd>

                    @if($purchaseOrder->ordered_at)
                    <dt class="col-sm-5 text-muted">Ordered</dt>
                    <dd class="col-sm-7">{{ $purchaseOrder->ordered_at->format('M d, Y g:i A') }}</dd>
                    @endif

                    @if($purchaseOrder->received_at)
                    <dt class="col-sm-5 text-muted">Received</dt>
                    <dd class="col-sm-7">{{ $purchaseOrder->received_at->format('M d, Y g:i A') }}</dd>
                    @endif

                    @if($purchaseOrder->notes)
                    <dt class="col-sm-5 text-muted">Notes</dt>
                    <dd class="col-sm-7">{{ $purchaseOrder->notes }}</dd>
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
                        @foreach($purchaseOrder->items as $item)
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
                            <th colspan="4" class="text-end">Total</th>
                            <th>₹{{ number_format($purchaseOrder->total_amount, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('purchase_orders.index') }}" class="text-decoration-none">
        <i class="bi bi-arrow-left"></i> Back to Purchase Orders
    </a>
</div>

@endsection