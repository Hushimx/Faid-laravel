@extends('layouts.app')

@section('title', __('dashboard.Create User'))

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    @lang('dashboard.Create New User') -
                    <span class="badge bg-{{ $type === 'admin' ? 'danger' : ($type === 'vendor' ? 'warning' : 'info') }}">
                        @if($type === 'admin')
                            @lang('dashboard.Admin')
                        @elseif($type === 'vendor')
                            @lang('dashboard.Vendor')
                        @else
                            @lang('dashboard.User')
                        @endif
                    </span>
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <!-- First Name -->
                            <div class="mb-3">
                                <label for="first_name" class="form-label">@lang('dashboard.First Name')</label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                    id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Last Name -->
                            <div class="mb-3">
                                <label for="last_name" class="form-label">@lang('dashboard.Last Name')</label>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                    id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">@lang('dashboard.Email')</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <label for="password" class="form-label">@lang('dashboard.Password')</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                            @if ($type === 'admin')
                                <div class="form-group mb-3">
                                    <label class="form-label">@lang('dashboard.Roles')</label>
                                    <div class="selectgroup selectgroup-pills">
                                        @foreach ($roles as $role)
                                            <label class="selectgroup-item">
                                                <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                                    class="selectgroup-input">
                                                <span
                                                    class="selectgroup-button">{{ __('dashboard.' . str_replace('-', '.', Str::slug($role->name))) }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('roles')
                                        <div class="text-danger small mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            <!-- Type (Hidden) -->
                            <input type="hidden" name="type" value="{{ old('type', $type ?? 'user') }}">

                        </div>

                        <div class="col-md-6">
                            <!-- Phone -->
                            <div class="mb-3">
                                <label for="phone" class="form-label">@lang('dashboard.Phone')</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                    id="phone" name="phone" value="{{ old('phone') }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Address -->
                            <div class="mb-3">
                                <label for="address" class="form-label">@lang('dashboard.Address')</label>
                                <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3">{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="mb-3">
                                <label for="status" class="form-label">@lang('dashboard.Status')</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status"
                                    name="status" required>
                                    <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>
                                        @lang('dashboard.Active')
                                    </option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>
                                        @lang('dashboard.Inactive')
                                    </option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Profile Picture -->
                            <div class="mb-3">
                                <label for="profile_picture" class="form-label">@lang('dashboard.Profile Picture')</label>
                                <input type="file" class="form-control @error('profile_picture') is-invalid @enderror"
                                    id="profile_picture" name="profile_picture" accept="image/*">
                                @error('profile_picture')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Vendor Settings -->
                    @if ($type === 'vendor')
                        <div id="vendor-settings" class="border rounded p-3 mb-3">
                            <h6 class="mb-3">@lang('dashboard.Vendor Settings')</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="vendor_country_id" class="form-label">@lang('dashboard.Country')</label>
                                        <select class="form-select @error('vendor.country_id') is-invalid @enderror"
                                            id="vendor_country_id" name="vendor[country_id]">
                                            <option value="">@lang('dashboard.Select Country')</option>
                                            @isset($countries)
                                                @foreach ($countries as $country)
                                                    <option value="{{ $country->id }}"
                                                        {{ (string) old('vendor.country_id') === (string) $country->id ? 'selected' : '' }}>
                                                        {{ $country->name }}
                                                    </option>
                                                @endforeach
                                            @endisset
                                        </select>
                                        @error('vendor.country_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="vendor_city_id" class="form-label">@lang('dashboard.City')</label>
                                        <select class="form-select @error('vendor.city_id') is-invalid @enderror"
                                            id="vendor_city_id" name="vendor[city_id]">
                                            <option value="">@lang('dashboard.Select City')</option>
                                            @isset($cities)
                                                @foreach ($cities as $city)
                                                    <option value="{{ $city->id }}"
                                                        {{ (string) old('vendor.city_id') === (string) $city->id ? 'selected' : '' }}>
                                                        {{ $city->name }}
                                                    </option>
                                                @endforeach
                                            @endisset
                                        </select>
                                        @error('vendor.city_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="vendor_bio" class="form-label">@lang('dashboard.Bio')</label>
                                        <input type="text"
                                            class="form-control @error('vendor.bio') is-invalid @enderror" id="vendor_bio"
                                            name="vendor[bio]" value="{{ old('vendor.bio') }}">
                                        @error('vendor.bio')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">@lang('dashboard.Locations')</label>
                                        <div class="d-flex gap-2">
                                            <input type="number" step="0.0000001"
                                                class="form-control @error('vendor.lat') is-invalid @enderror"
                                                placeholder="@lang('dashboard.Latitude')" name="vendor[lat]"
                                                value="{{ old('vendor.lat') }}">
                                            <input type="number" step="0.0000001"
                                                class="form-control @error('vendor.lng') is-invalid @enderror"
                                                placeholder="@lang('dashboard.Longitude')" name="vendor[lng]"
                                                value="{{ old('vendor.lng') }}">
                                        </div>
                                        @error('vendor.lat')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        @error('vendor.lng')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="vendor_banner" class="form-label">@lang('dashboard.Banner')</label>
                                        <input type="file"
                                            class="form-control @error('vendor.banner') is-invalid @enderror"
                                            id="vendor_banner" name="vendor[banner]" accept="image/*">
                                        @error('vendor.banner')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">@lang('dashboard.Meta')</label>
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">@lang('dashboard.Website')</label>
                                                        <input type="url" class="form-control"
                                                            name="vendor[meta][website]"
                                                            value="{{ old('vendor.meta.website', '') }}"
                                                            placeholder="https://example.com">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">@lang('dashboard.WhatsApp')</label>
                                                        <input type="text" class="form-control"
                                                            name="vendor[meta][whatsapp]"
                                                            value="{{ old('vendor.meta.whatsapp', '') }}"
                                                            placeholder="+201234567890">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">@lang('dashboard.Instagram')</label>
                                                        <input type="text" class="form-control"
                                                            name="vendor[meta][instagram]"
                                                            value="{{ old('vendor.meta.instagram', '') }}"
                                                            placeholder="@your_handle">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">@lang('dashboard.Facebook')</label>
                                                        <input type="text" class="form-control"
                                                            name="vendor[meta][facebook]"
                                                            value="{{ old('vendor.meta.facebook', '') }}"
                                                            placeholder="/yourpage">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">@lang('dashboard.Tags')</label>
                                                        <input type="text" class="form-control"
                                                            name="vendor[meta][tags]"
                                                            value="{{ old('vendor.meta.tags', '') }}"
                                                            placeholder="coffee, desserts, breakfast">
                                                        <small class="text-muted">@lang('dashboard.Separate with commas')</small>
                                                    </div>
                                                </div>
                                                <hr class="my-3">
                                                <div>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary mb-2"
                                                        id="toggle-advanced-meta">@lang('dashboard.Advanced JSON')</button>
                                                    <div id="advanced-meta" style="display: none;">
                                                        <div
                                                            class="d-flex justify-content-between align-items-center mb-2">
                                                            <small class="text-muted mb-0">@lang('dashboard.Paste or edit custom JSON')</small>
                                                            <div class="d-flex gap-2">
                                                                <button type="button" class="btn btn-sm btn-light"
                                                                    id="meta-pretty">@lang('dashboard.Pretty')</button>
                                                                <button type="button" class="btn btn-sm btn-light"
                                                                    id="meta-from-fields">@lang('dashboard.Build from fields')</button>
                                                            </div>
                                                        </div>
                                                        <textarea class="form-control @error('vendor.meta') is-invalid @enderror" id="vendor_meta_json" name="vendor[meta]"
                                                            rows="6">{{ old('vendor.meta', '') }}</textarea>
                                                        @error('vendor.meta')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            (function() {
                                // Advanced meta controls
                                const toggleBtn = document.getElementById('toggle-advanced-meta');
                                const advanced = document.getElementById('advanced-meta');
                                const metaTextarea = document.getElementById('vendor_meta_json');
                                const prettyBtn = document.getElementById('meta-pretty');
                                const buildBtn = document.getElementById('meta-from-fields');

                                if (toggleBtn && advanced) {
                                    toggleBtn.addEventListener('click', function() {
                                        advanced.style.display = advanced.style.display === 'none' ? 'block' : 'none';
                                    });
                                }

                                if (prettyBtn && metaTextarea) {
                                    prettyBtn.addEventListener('click', function() {
                                        try {
                                            const parsed = JSON.parse(metaTextarea.value || '{}');
                                            metaTextarea.value = JSON.stringify(parsed, null, 2);
                                        } catch (e) {
                                            alert('{{ __('dashboard.Invalid JSON') }}');
                                        }
                                    });
                                }

                                if (buildBtn) {
                                    buildBtn.addEventListener('click', function() {
                                        const website = document.querySelector('[name="vendor[meta][website]"]').value || undefined;
                                        const whatsapp = document.querySelector('[name="vendor[meta][whatsapp]"]').value ||
                                            undefined;
                                        const instagram = document.querySelector('[name="vendor[meta][instagram]"]').value ||
                                            undefined;
                                        const facebook = document.querySelector('[name="vendor[meta][facebook]"]').value ||
                                            undefined;
                                        const tagsRaw = document.querySelector('[name="vendor[meta][tags]"]').value || '';
                                        const tags = Array.from(new Set(tagsRaw.split(',').map(s => s.trim()).filter(Boolean)));

                                        const out = {};
                                        if (website) out.website = website;
                                        if (whatsapp) out.whatsapp = whatsapp;
                                        if (instagram) out.instagram = instagram;
                                        if (facebook) out.facebook = facebook;
                                        if (tags.length) out.tags = tags;

                                        if (metaTextarea) {
                                            metaTextarea.value = JSON.stringify(out, null, 2);
                                            if (advanced.style.display === 'none') advanced.style.display = 'block';
                                        }
                                    });
                                }
                            })();
                        </script>
                    @endif

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ $type === 'admin' ? route('users.admins') : ($type === 'vendor' ? route('users.vendors') : ($type === 'user' ? route('users.users') : route('users.all'))) }}"
                            class="btn btn-secondary">@lang('dashboard.Cancel')</a>
                        <button type="submit" class="btn btn-primary">@lang('dashboard.Create User')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
