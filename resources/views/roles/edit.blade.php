@extends('layouts.app')

@section('title', 'Edit Role')

@section('content')

<x-ui.page-header title="Edit Role" subtitle="Update role details and permissions" />

@php
// Flat list of this role's current permission IDs — computed once
// here rather than inside the loop below, so we're not calling
// ->pluck('id')->contains() repeatedly for every single checkbox.
$currentPermissionIds = old('permissions', $role->permissions->pluck('id')->toArray());
@endphp

<form method="POST" action="{{ route('roles.update', $role) }}">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-lg-5 mb-3">
            <div class="card">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label for="display_name" class="form-label">Role Name <span class="text-danger">*</span></label>
                        <input type="text" id="display_name" name="display_name"
                            value="{{ old('display_name', $role->display_name) }}"
                            class="form-control @error('display_name') is-invalid @enderror" required autofocus>
                        @error('display_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Machine Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $role->name) }}"
                            class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Changing this may break any code checking for the old name directly.</div>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" rows="3"
                            class="form-control @error('description') is-invalid @enderror">{{ old('description', $role->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-lg"></i> Update Role
                    </button>
                    <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-white"><strong>Permissions</strong></div>
                <div class="card-body p-4">
                    @error('permissions')
                    <div class="alert alert-danger py-2">{{ $message }}</div>
                    @enderror

                    @foreach($groupedPermissions as $groupName => $permissions)
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                            <strong>{{ $groupName }}</strong>
                            <div>
                                <button type="button" class="btn btn-link btn-sm p-0 select-all" data-group="{{ $loop->index }}">Select all</button>
                                <span class="text-muted mx-1">|</span>
                                <button type="button" class="btn btn-link btn-sm p-0 select-none" data-group="{{ $loop->index }}">None</button>
                            </div>
                        </div>
                        <div class="row" data-group-index="{{ $loop->index }}">
                            @foreach($permissions as $permission)
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                        id="perm_{{ $permission->id }}" class="form-check-input group-checkbox"
                                        @checked(in_array($permission->id, $currentPermissionIds))>
                                    <label for="perm_{{ $permission->id }}" class="form-check-label">
                                        {{ $permission->display_name }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    document.querySelectorAll('.select-all').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelector(`[data-group-index="${btn.dataset.group}"]`)
                .querySelectorAll('.group-checkbox').forEach(cb => cb.checked = true);
        });
    });
    document.querySelectorAll('.select-none').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelector(`[data-group-index="${btn.dataset.group}"]`)
                .querySelectorAll('.group-checkbox').forEach(cb => cb.checked = false);
        });
    });
</script>
@endpush

@endsection