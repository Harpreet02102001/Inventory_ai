{{--
    403 Access Denied Page

    Laravel automatically serves this view when abort(403) is called
    anywhere in the application — by our CheckPermission middleware
    or by $this->authorize() in controllers.

    No controller needed — Laravel's exception handler detects files
    in resources/views/errors/ matching HTTP status codes.

    Matches the "Access Denied" design (Image 17).
--}}

@extends('layouts.app')

@section('title', 'Access Denied')

@section('content')
<div class="d-flex align-items-center justify-content-center" style="min-height: 70vh;">
    <div class="card text-center p-5" style="max-width: 520px; width: 100%;">

        {{-- Shield / Lock Icon --}}
        <div class="mb-4">
            <div
                class="rounded-circle bg-danger bg-opacity-10 d-inline-flex align-items-center justify-content-center"
                style="width: 96px; height: 96px;">
                <i class="bi bi-shield-lock text-danger" style="font-size: 2.5rem;"></i>
            </div>
        </div>

        {{-- Message --}}
        <h2 class="fw-bold text-dark mb-2">Access Denied</h2>
        <p class="text-muted mb-4">
            You do not have permission to access this page.
        </p>

        {{-- Back to Dashboard --}}
        <a href="{{ route('dashboard') }}" class="btn btn-primary mb-4">
            <i class="bi bi-house-door"></i>
            Go Back to Dashboard
        </a>

        {{-- Reason hints --}}
        <p class="text-muted small mb-3">
            This page may be restricted for your role or your session may have expired.
        </p>

        <div class="d-flex flex-wrap justify-content-center gap-2">
            <span class="badge bg-light text-secondary d-flex align-items-center gap-1 p-2">
                <i class="bi bi-person"></i>
                Staff accessing category management
            </span>
            <span class="badge bg-light text-secondary d-flex align-items-center gap-1 p-2">
                <i class="bi bi-pencil"></i>
                Staff editing product details
            </span>
            <span class="badge bg-light text-secondary d-flex align-items-center gap-1 p-2">
                <i class="bi bi-lock"></i>
                Unauthenticated access
            </span>
        </div>

    </div>
</div>
@endsection