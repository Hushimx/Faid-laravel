@extends('layouts.app')
@section('title', __('dashboard.Cities'))
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">@lang('dashboard.Cities List')</h4>
            @can('cities.create')
                <a class="modal-effect btn btn-primary" data-bs-effect="effect-scale" data-bs-toggle="modal" href="#createModal">
                    @lang('dashboard.Create New City')
                </a>
            @endcan
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table text-center table-hover table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('dashboard.Name')</th>
                            <th>@lang('dashboard.Country')</th>
                            <th>@lang('dashboard.Actions')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cities as $city)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $city->name }}</td>
                                <td>{{ $city->country->name }}</td>
                                <td>
                                    @can('cities.edit')
                                        <a class="modal-effect btn btn-success btn-sm" data-bs-effect="effect-scale"
                                            data-bs-toggle="modal" href="#updateModal{{ $city->id }}">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('cities.delete')
                                        <a class="modal-effect btn btn-danger btn-sm" data-bs-effect="effect-scale"
                                            data-bs-toggle="modal" href="#deleteModal{{ $city->id }}">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    @endcan
                                </td>
                            </tr>

                            <div class="modal fade" id="updateModal{{ $city->id }}">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content modal-content-demo">
                                        <div class="modal-header">
                                            <h6 class="modal-title">@lang('dashboard.Edit City')</h6><button aria-label="Close"
                                                class="btn-close" data-bs-dismiss="modal"><span
                                                    aria-hidden="true">&times;</span></button>
                                        </div>
                                        <form action="{{ route('cities.update', $city->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                @php
                                                    $availableLocales = locales();
                                                    // Ensure at least 'en' is always present
                                                    $defaultLocales = [
                                                        (object)['code' => 'en', 'name' => 'English'],
                                                        (object)['code' => 'ar', 'name' => 'Arabic']
                                                    ];
                                                    
                                                    if (empty($availableLocales)) {
                                                        $availableLocales = $defaultLocales;
                                                    } else {
                                                        // Ensure 'en' exists in the locales
                                                        $hasEn = false;
                                                        foreach ($availableLocales as $locale) {
                                                            $code = is_object($locale) ? $locale->code : ($locale['code'] ?? null);
                                                            if ($code === 'en') {
                                                                $hasEn = true;
                                                                break;
                                                            }
                                                        }
                                                        if (!$hasEn) {
                                                            array_unshift($availableLocales, (object)['code' => 'en', 'name' => 'English']);
                                                        }
                                                    }
                                                @endphp
                                                @foreach ($availableLocales as $locale)
                                                    @php
                                                        $localeCode = is_object($locale) ? $locale->code : ($locale['code'] ?? '');
                                                        $localeName = is_object($locale) ? $locale->name : ($locale['name'] ?? strtoupper($localeCode));
                                                    @endphp
                                                    <div class="form-group">
                                                        <label for="name-{{ $localeCode }}">@lang('dashboard.Name') (
                                                            {{ strtoupper($localeName) }}
                                                            )</label>
                                                        <input type="text" name="name[{{ $localeCode }}]"
                                                            class="form-control @error('name.' . $localeCode) is-invalid @enderror" id="name-{{ $localeCode }}"
                                                            placeholder="@lang('dashboard.Name') ( {{ strtoupper($localeName) }} )"
                                                            value="{{ old('name.' . $localeCode, $city->getTranslation('name', $localeCode)) }}"
                                                            {{ $localeCode === 'en' ? 'required' : '' }}>
                                                        @error('name.' . $localeCode)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                @endforeach
                                                @error('name')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                                @error('name.en')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                                <div class="form-group">
                                                    <label for="country_id">@lang('dashboard.Country')</label>
                                                    <select name="country_id" class="form-control @error('country_id') is-invalid @enderror" id="country_id" required>
                                                        <option value="">@lang('dashboard.Select Country')</option>
                                                        @foreach ($countries as $country)
                                                            <option value="{{ $country->id }}"
                                                                {{ old('country_id', $city->country_id) == $country->id ? 'selected' : '' }}>
                                                                {{ $country->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('country_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary">@lang('dashboard.Save changes')</button>
                                                <button type="button" class="btn btn-light"
                                                    data-bs-dismiss="modal">@lang('dashboard.Cancel')</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Modal -->
                            <div class="modal fade" id="deleteModal{{ $city->id }}">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content modal-content-demo">
                                        <div class="modal-header">
                                            <h6 class="modal-title">@lang('dashboard.Delete City')</h6><button aria-label="Close"
                                                class="btn-close" data-bs-dismiss="modal"><span
                                                    aria-hidden="true">&times;</span></button>
                                        </div>
                                        <form action="{{ route('cities.destroy', $city->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <div class="modal-body">
                                                <p>@lang('dashboard.Are you sure you want to delete this city?')</p>
                                                <p class="text-danger">{{ $city->name }}</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-danger">@lang('dashboard.Delete')</button>
                                                <button type="button" class="btn btn-light"
                                                    data-bs-dismiss="modal">@lang('dashboard.Cancel')</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="text-center py-5">
                                        <h5>@lang('dashboard.No Cities Found')</h5>
                                        <p class="text-muted">@lang('dashboard.Start by adding your first city')</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($cities->hasPages())
            <div class="card-footer">
                {{ $cities->links() }}
            </div>
        @endif
    </div>

    <!-- Create City Modal -->
    <div class="modal fade" id="createModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">@lang('dashboard.Create New City')</h6><button aria-label="Close" class="btn-close"
                        data-bs-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <form action="{{ route('cities.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        @php
                            $availableLocales = locales();
                            // Ensure at least 'en' is always present
                            $defaultLocales = [
                                (object)['code' => 'en', 'name' => 'English'],
                                (object)['code' => 'ar', 'name' => 'Arabic']
                            ];
                            
                            if (empty($availableLocales)) {
                                $availableLocales = $defaultLocales;
                            } else {
                                // Ensure 'en' exists in the locales
                                $hasEn = false;
                                foreach ($availableLocales as $locale) {
                                    $code = is_object($locale) ? $locale->code : ($locale['code'] ?? null);
                                    if ($code === 'en') {
                                        $hasEn = true;
                                        break;
                                    }
                                }
                                if (!$hasEn) {
                                    array_unshift($availableLocales, (object)['code' => 'en', 'name' => 'English']);
                                }
                            }
                        @endphp
                        @foreach ($availableLocales as $locale)
                            @php
                                $localeCode = is_object($locale) ? $locale->code : ($locale['code'] ?? '');
                                $localeName = is_object($locale) ? $locale->name : ($locale['name'] ?? strtoupper($localeCode));
                            @endphp
                            <div class="form-group">
                                <label for="name-{{ $localeCode }}">@lang('dashboard.Name') ( {{ strtoupper($localeName) }}
                                    )</label>
                                <input type="text" name="name[{{ $localeCode }}]" class="form-control @error('name.' . $localeCode) is-invalid @enderror"
                                    id="name-{{ $localeCode }}"
                                    placeholder="@lang('dashboard.Name') ( {{ strtoupper($localeName) }} )"
                                    value="{{ old('name.' . $localeCode) }}"
                                    {{ $localeCode === 'en' ? 'required' : '' }}>
                                @error('name.' . $localeCode)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach
                        @error('name')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        @error('name.en')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <div class="form-group">
                            <label for="country_id">@lang('dashboard.Country')</label>
                            <select name="country_id" class="form-control @error('country_id') is-invalid @enderror" id="country_id" required>
                                <option value="">@lang('dashboard.Select Country')</option>
                                @foreach ($countries as $country)
                                    <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                                @endforeach
                            </select>
                            @error('country_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('dashboard.Save changes')</button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">@lang('dashboard.Cancel')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    // Reopen modal if there are validation errors
    @if ($errors->hasAny(['name', 'name.en', 'country_id']))
        $(document).ready(function() {
            // Use setTimeout to ensure Bootstrap is fully loaded
            setTimeout(function() {
                $('#createModal').modal('show');
            }, 100);
        });
    @endif
</script>
@endpush
