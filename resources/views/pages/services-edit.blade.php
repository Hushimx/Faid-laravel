@extends('layouts.app')

@section('title', __('dashboard.Edit Service'))

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">@lang('dashboard.Edit Service')</h4>
            <a href="{{ route('services.show', $service) }}" class="btn btn-light">
                <i class="fe fe-arrow-left me-1"></i>@lang('dashboard.Back')
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('services.update', $service) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">@lang('dashboard.Title')</label>
                        @foreach (locales() as $locale)
                            <div class="mb-3">
                                <input type="text" class="form-control" name="title[{{ $locale->code }}]"
                                    value="{{ old("title.{$locale->code}", $service->getTranslation('title', $locale->code, false)) }}"
                                    placeholder="@lang('dashboard.Title') ({{ strtoupper($locale->name) }})"
                                    {{ $loop->first ? 'required' : '' }}>
                            </div>
                        @endforeach
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">@lang('dashboard.Category')</label>
                        <select name="category_id" class="form-select" required>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $service->category_id) === $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">@lang('dashboard.Description')</label>
                        @foreach (locales() as $locale)
                            <div class="mb-3">
                                <textarea class="form-control" rows="3" name="description[{{ $locale->code }}]"
                                    placeholder="@lang('dashboard.Description') ({{ strtoupper($locale->name) }})">{{ old("description.{$locale->code}", $service->getTranslation('description', $locale->code, false)) }}</textarea>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">@lang('dashboard.Price Type')</label>
                        <select name="price_type" class="form-select" required>
                            @foreach (App\Models\Service::priceTypes() as $type)
                                <option value="{{ $type }}" @selected(old('price_type', $service->price_type) === $type)>
                                    @lang('dashboard.' . ucfirst($type))
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">@lang('dashboard.Price')</label>
                        <input type="number" step="0.01" class="form-control" name="price"
                            value="{{ old('price', $service->price) }}" placeholder="0.00">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">@lang('dashboard.Status')</label>
                        <select name="status" class="form-select" required>
                            @foreach (App\Models\Service::vendorStatuses() as $status)
                                <option value="{{ $status }}" @selected(old('status', $service->status) === $status)>
                                    @lang('dashboard.' . ucfirst($status))
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-12">
                        <label class="form-label">@lang('dashboard.Attributes') (JSON)</label>
                        <textarea class="form-control" rows="5" name="attributes" placeholder='{"key": "value"}'>{{ old('attributes', $service->attributes ? json_encode($service->attributes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                        <small class="text-muted">@lang('dashboard.Attributes helper')</small>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fe fe-save me-1"></i>@lang('dashboard.Save changes')
                    </button>
                    <a href="{{ route('services.show', $service) }}" class="btn btn-light">
                        @lang('dashboard.Cancel')
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
