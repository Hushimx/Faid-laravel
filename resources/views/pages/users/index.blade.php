@extends('layouts.app')

@section('title', __('dashboard.users-management')))

@section('content')
    <div class="container-fluid">
        <!-- Search and Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ $type === 'admin' ? route('users.admins') : ($type === 'vendor' ? route('users.vendors') : ($type === 'user' ? route('users.users') : route('users.all'))) }}" method="GET" class="row g-3">
                    <!-- Search -->
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" placeholder="@lang('dashboard.Search users placeholder')"
                            value="{{ request('search') }}">
                    </div>

                    <!-- Status Filter -->
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">@lang('dashboard.All Status')</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>@lang('dashboard.Active')</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>@lang('dashboard.Inactive')
                            </option>
                        </select>
                    </div>

                    <!-- Sort -->
                    <div class="col-md-2">
                        <select name="sort_by" class="form-select">
                            <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>@lang('dashboard.Created Date')</option>
                            <option value="first_name" {{ request('sort_by') === 'first_name' ? 'selected' : '' }}>@lang('dashboard.First Name')</option>
                            <option value="last_name" {{ request('sort_by') === 'last_name' ? 'selected' : '' }}>@lang('dashboard.Last Name')</option>
                            <option value="email" {{ request('sort_by') === 'email' ? 'selected' : '' }}>@lang('dashboard.Email')</option>
                        </select>
                    </div>

                    <div class="col-md-1">
                        <select name="sort_direction" class="form-select">
                            <option value="desc" {{ request('sort_direction') === 'desc' ? 'selected' : '' }}>@lang('dashboard.DESC')
                            </option>
                            <option value="asc" {{ request('sort_direction') === 'asc' ? 'selected' : '' }}>@lang('dashboard.ASC')</option>
                        </select>
                    </div>

                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">@lang('dashboard.Filter')</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    @if($type === 'admin')
                        @lang('dashboard.admins')
                    @elseif($type === 'vendor')
                        @lang('dashboard.vendors')
                    @elseif($type === 'user')
                        @lang('dashboard.users')
                    @else
                        @lang('dashboard.All')
                    @endif
                </h5>
                @if($type !== 'all')
                    @can('users.create')
                        <a href="{{ route('users.create', ['type' => $type]) }}" class="btn btn-primary">
                            @if($type === 'admin')
                                @lang('dashboard.Add Admin')
                            @elseif($type === 'vendor')
                                @lang('dashboard.Add Vendor')
                            @elseif($type === 'user')
                                @lang('dashboard.Add User')
                            @endif
                        </a>
                    @endcan
                @endif
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
                                                alt="{{ $user->first_name }} {{ $user->last_name }}" class="rounded-circle" width="40"
                                                height="40">
                                        @else
                                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center"
                                                style="width: 40px; height: 40px;">
                                                <span class="text-white">{{ substr($user->first_name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $user->type === 'admin' ? 'danger' : ($user->type === 'vendor' ? 'warning' : 'info') }}">
                                            @if($user->type === 'admin')
                                                @lang('dashboard.Admin')
                                            @elseif($user->type === 'vendor')
                                                @lang('dashboard.Vendor')
                                            @else
                                                @lang('dashboard.User')
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'danger' }}">
                                            @if($user->status === 'active')
                                                @lang('dashboard.Active')
                                            @else
                                                @lang('dashboard.Inactive')
                                            @endif
                                        </span>
                                    </td>
                                    <td>{{ $user->created_at ? $user->created_at->format('Y-m-d') : __('dashboard.N/A') }}</td>
                                    <td>
                                        @can('users.edit')
                                            <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('users.delete')
                                            @if ($user->id !== auth()->id())
                                                <form action="{{ route('users.destroy', $user) }}" method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('@lang('dashboard.Are you sure you want to delete this user?')');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">@lang('dashboard.No users found')</td>
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
