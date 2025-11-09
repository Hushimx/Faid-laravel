@extends('layouts.app')

@section('title', __('dashboard.Create Role'))

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">@lang('dashboard.Create New Role')</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('roles.store') }}" method="POST">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <!-- Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label">@lang('dashboard.Name')</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Permissions -->
                            <div class="mb-3">
                                <label class="form-label">@lang('dashboard.Permissions')</label>
                                <div class="card">
                                    <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                                        @foreach($permissions as $group => $groupPermissions)
                                            <div class="mb-4 pb-3 border-bottom">
                                                <h6 class="text-uppercase text-muted mb-3 fw-bold">{{ ucfirst($group) }}</h6>
                                                <div class="row g-2">
                                                    @foreach($groupPermissions as $permission)
                                                        <div class="col-md-3 col-sm-4 col-6">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" 
                                                                    name="permissions[]" 
                                                                    value="{{ $permission->name }}" 
                                                                    id="permission_{{ $permission->id }}"
                                                                    {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                                    {{ str_replace($group . '.', '', $permission->name) }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @error('permissions')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('roles.index') }}" class="btn btn-secondary">@lang('dashboard.Cancel')</a>
                        <button type="submit" class="btn btn-primary">@lang('dashboard.Create Role')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

