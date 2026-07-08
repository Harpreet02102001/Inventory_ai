@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<x-ui.page-header title="Dashboard" subtitle="Welcome back, {{ auth()->user()->name }}" />

@if($widgets->isNotEmpty())
<div class="row g-3 mb-4">
    @foreach($widgets as $widget)
    <div class="col-md-3">
        <div class="card {{ $widget->highlight ? 'border-warning' : '' }}">
            <div class="card-body">
                <div class="text-muted small">
                    <i class="bi {{ $widget->icon }}"></i> {{ $widget->label }}
                </div>
                <div class="fs-3 fw-bold {{ $widget->highlight ? 'text-warning' : '' }}">
                    {{ $widget->value }}
                </div>
                @if($widget->link)
                <a href="{{ $widget->link }}" class="small">View all →</a>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

@if($recentStockActivity !== null)
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Recent Stock Activity</strong>
        <a href="{{ route('stock.index') }}" class="small">View all products →</a>
    </div>

    @if($recentStockActivity->isEmpty())
    <x-ui.empty-state icon="bi-clock-history" title="No recent activity"
        description="Stock changes will appear here as they happen." />
    @else
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Type</th>
                    <th>Change</th>
                    <th>New Qty</th>
                    <th>By</th>
                    <th>When</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentStockActivity as $entry)
                <tr>
                    <td>{{ $entry->product->name }}</td>
                    <td><span class="badge {{ $entry->changed_quantity >= 0 ? 'badge-active' : 'badge-inactive' }}">{{ ucfirst($entry->type) }}</span></td>
                    <td class="{{ $entry->changed_quantity >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $entry->changed_quantity >= 0 ? '+' : '' }}{{ $entry->changed_quantity }}
                    </td>
                    <td>{{ $entry->new_quantity }}</td>
                    <td class="small">{{ $entry->user->name }}</td>
                    <td class="text-muted small">{{ $entry->created_at->diffForHumans() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endif

@endsection