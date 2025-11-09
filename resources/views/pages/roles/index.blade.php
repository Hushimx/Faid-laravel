@extends('layouts.app')

@section('title', __('dashboard.Roles Management'))

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">@lang('dashboard.Roles Management')</h5>
                @can('roles.create')
                <a href="{{ route('roles.create') }}" class="btn btn-primary">@lang('dashboard.Add Role')</a>
                @endcan
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>@lang('dashboard.Name')</th>
                                <th>@lang('dashboard.Permissions')</th>
                                <th>@lang('dashboard.Users Count')</th>
                                <th>@lang('dashboard.Created At')</th>
                                <th>@lang('dashboard.Actions')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roles as $role)
                                <tr>
                                    <td>
                                        <strong>{{ $role->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $role->permissions_count }} @lang('dashboard.permissions')</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $role->users_count }} @lang('dashboard.users')</span>
                                    </td>
                                    <td>{{ $role->created_at ? $role->created_at->format('Y-m-d') : 'N/A' }}</td>
                                    <td>
                                        @can('roles.edit')
                                        <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-primary">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('roles.delete')
                                        @if($role->name !== 'Super Admin')
                                        <form action="{{ route('roles.destroy', $role) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('@lang('dashboard.Are you sure you want to delete this role?')');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">@lang('dashboard.No roles found')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $roles->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

