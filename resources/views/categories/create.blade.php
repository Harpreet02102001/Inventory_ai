@extends('layouts.app')

@section('title', 'Add Category')

@section('content')

<x-ui.page-header title="Add Category" subtitle="Create a new product category" />

<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('categories.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input
                            type="text" id="name" name="name" value="{{ old('name') }}"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="e.g. Electronics" required autofocus>
                        @error('name')
                        <div class="invalid-feedback"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea
                            id="description" name="description" rows="3"
                            class="form-control @error('description') is-invalid @enderror"
                            placeholder="Optional short description">{{ old('description') }}</textarea>
                        @error('description')
                        <div class="invalid-feedback"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="active" @selected(old('status', 'active' )==='active' )>Active</option>
                            <option value="inactive" @selected(old('status')==='inactive' )>Inactive</option>
                        </select>
                        @error('status')
                        <div class="invalid-feedback"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                        @enderror
                        <div class="form-text">Inactive categories won't appear when creating new products.</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Save Category
                        </button>
                        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <x-ui.alert type="info">
            Inactive categories will not appear in the product creation dropdown, but existing products keep their category.
        </x-ui.alert>
    </div>
</div>

@endsection