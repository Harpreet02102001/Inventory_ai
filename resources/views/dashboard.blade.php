@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <div>
        @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('manager'))
        <h1>Admin Dashboard</h1>
        <p>Inventory summary and recent stock activity</p>
        @else
        <h1>Dashboard</h1>
        <p>View products and monitor recent inventory activity</p>
        @endif
    </div>
</div>

{{--
    Temporary dashboard placeholder.
    This will be replaced with full dashboard widgets
    (stat cards, low stock table, recent stock updates)
    when we build the Dashboard module.

    For now it confirms the layout, navigation, and
    authentication are all working correctly.
--}}
<div class="card p-4">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center"
            style="width:48px; height:48px; flex-shrink:0;">
            <i class="bi bi-check-lg text-success fs-4"></i>
        </div>
        <div>
            <h6 class="mb-0 fw-semibold">Welcome back, {{ Auth::user()->name }}</h6>
            <p class="mb-0 text-muted small">
                You are signed in as
                <span class="badge bg-primary bg-opacity-10 text-primary ms-1">
                    {{ Auth::user()->roles->first()?->display_name ?? 'User' }}
                </span>
            </p>
        </div>
    </div>
</div>
@endsection