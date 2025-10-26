@extends('layouts.app')

@section('title', __('dashboard.users-management')))

@section('content')
    <div class="container-fluid">
        <!-- Search and Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('users.index') }}" method="GET" class="row g-3">
                    <!-- Search -->
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search users..."
                            value="{{ request('search') }}">
                    </div>

                    <!-- Type Filter -->
                    <div class="col-md-2">
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="admin" {{ request('type') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="user" {{ request('type') === 'user' ? 'selected' : '' }}>User</option>
                            <option value="vendor" {{ request('type') === 'vendor' ? 'selected' : '' }}>Vendor</option>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive
                            </option>
                        </select>
                    </div>

                    <!-- Sort -->
                    <div class="col-md-2">
                        <select name="sort_by" class="form-select">
                            <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Created
                                Date</option>
                            <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Name</option>
                            <option value="email" {{ request('sort_by') === 'email' ? 'selected' : '' }}>Email</option>
                        </select>
                    </div>

                    <div class="col-md-1">
                        <select name="sort_direction" class="form-select">
                            <option value="desc" {{ request('sort_direction') === 'desc' ? 'selected' : '' }}>DESC
                            </option>
                            <option value="asc" {{ request('sort_direction') === 'asc' ? 'selected' : '' }}>ASC</option>
                        </select>
                    </div>

                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Users</h5>
                <a href="{{ route('users.create') }}" class="btn btn-primary">Add User</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>@lang('dashboard.Image')</th>
                                <th>@lang('dashboard.Name')</th>
                                <th>@lang('dashboard.Email')</th>
                                <th>@lang('dashboard.Type')</th>
                                <th>@lang('dashboard.Status')</th>
                                <th>@lang('dashboard.Created At')</th>
                                <th>@lang('dashboard.Actions')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>
                                        @if ($user->profile_picture)
                                            <img src="{{ Storage::url($user->profile_picture) }}"
                                                alt="{{ $user->name }}" class="rounded-circle" width="40"
                                                height="40">
                                        @else
                                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center"
                                                style="width: 40px; height: 40px;">
                                                <span class="text-white">{{ substr($user->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $user->type === 'admin' ? 'danger' : ($user->type === 'vendor' ? 'warning' : 'info') }}">
                                            {{ ucfirst($user->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'danger' }}">
                                            {{ ucfirst($user->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $user->created_at ? $user->created_at->format('Y-m-d') : 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        @if ($user->id !== auth()->id())
                                            <form action="{{ route('users.destroy', $user) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No users found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
