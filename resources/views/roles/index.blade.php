@extends('layouts.app')

@section('title', 'Roles')

@section('content')

<x-ui.page-header title="Roles" subtitle="Manage roles and their permissions">
    <x-slot:action>
        @can('roles.create')
        <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Add Role
        </a>
        @endcan
    </x-slot:action>
</x-ui.page-header>

<div class="card">
    @if($roles->isEmpty())
    <x-ui.empty-state icon="bi-shield-lock" title="No roles found"
        description="Create your first role to start assigning permissions." />
    @else
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Machine Name</th>
                    <th>Permissions</th>
                    <th>Users</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $role)
                <tr>
                    <td class="fw-medium">{{ $role->display_name }}</td>
                    <td><code>{{ $role->name }}</code></td>
                    <td><span class="badge badge-active">{{ $role->permissions_count }}</span></td>
                    <td><span class="badge badge-inactive">{{ $role->users_count }}</span></td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-1">
                            @can('roles.edit')
                            <a href="{{ route('roles.edit', $role) }}" class="btn-action btn-action-edit" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endcan
                            @can('roles.delete')
                            @if($role->users_count > 0)
                            <span class="btn-action btn-action-locked" data-bs-toggle="tooltip"
                                title="Assigned to users — cannot delete">
                                <i class="bi bi-lock"></i>
                            </span>
                            @else
                            <form action="{{ route('roles.destroy', $role) }}" method="POST"
                                onsubmit="return confirm('Delete this role? This cannot be undone.');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-action btn-action-delete" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@endsection