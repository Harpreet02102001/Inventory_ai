@extends('layouts.app')

@section('title', 'Users')

@section('content')

<x-ui.page-header title="Users" subtitle="Manage user accounts and role assignments">
    <x-slot:action>
        @can('users.create')
        <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Add User
        </a>
        @endcan
    </x-slot:action>
</x-ui.page-header>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" value="{{ $search }}"
                    class="form-control form-control-sm" placeholder="Search by name or email...">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
            @if($search)
            <div class="col-auto">
                <a href="{{ route('users.index') }}" class="btn btn-link btn-sm text-muted">Clear</a>
            </div>
            @endif
        </form>
    </div>
</div>

<div class="card">
    @if($users->isEmpty())
    <x-ui.empty-state icon="bi-people" title="No users found"
        description="{{ $search ? 'No users match your search.' : 'Add your first user to get started.' }}" />
    @else
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Roles</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td class="fw-medium">
                        {{ $user->name }}
                        @if($user->id === auth()->id())
                        <span class="badge badge-inactive ms-1">You</span>
                        @endif
                    </td>
                    <td class="text-muted">{{ $user->email }}</td>
                    <td>
                        @forelse($user->roles as $role)
                        <span class="badge badge-active me-1">{{ $role->display_name }}</span>
                        @empty
                        <span class="text-muted small">No role assigned</span>
                        @endforelse
                    </td>
                    <td><x-ui.status-badge :status="$user->status" /></td>
                    <td class="text-end">
                        @can('users.edit')
                        @if($user->id === auth()->id())
                        <span class="btn-action btn-action-locked" data-bs-toggle="tooltip"
                            title="You cannot edit your own account here">
                            <i class="bi bi-lock"></i>
                        </span>
                        @else
                        <a href="{{ route('users.edit', $user) }}" class="btn-action btn-action-edit" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @endif
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="pagination-info d-flex justify-content-between align-items-center px-3 py-2">
        <small class="text-muted">
            Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }}
        </small>
        {{ $users->links() }}
    </div>
    @endif
</div>

@endsection