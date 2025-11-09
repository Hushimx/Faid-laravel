@extends('layouts.app')

@section('title', __('dashboard.Permissions Management'))

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">@lang('dashboard.Permissions Management')</h5>
            </div>
            <div class="card-body">
                @foreach($permissions as $group => $groupPermissions)
                    <div class="mb-4">
                        <h5 class="text-uppercase text-muted mb-3">{{ ucfirst($group) }}</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>@lang('dashboard.Permission')</th>
                                        <th>@lang('dashboard.Roles Count')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($groupPermissions as $permission)
                                        <tr>
                                            <td>
                                                <code>{{ $permission->name }}</code>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $permission->roles_count }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

