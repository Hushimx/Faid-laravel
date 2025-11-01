@extends('layouts.app')

@section('title', __('dashboard.Edit Product'))

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">@lang('dashboard.Edit Product')</h4>
            <a href="{{ route('products.show', $product) }}" class="btn btn-light">
                <i class="fe fe-arrow-left me-1"></i>@lang('dashboard.Back')
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('products.update', $product) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">@lang('dashboard.Title')</label>
                        @foreach (locales() as $locale)
                            <div class="mb-3">
                                <input type="text" class="form-control" name="title[{{ $locale->code }}]"
                                    value="{{ old("title.{$locale->code}", $product->getTranslation('title', $locale->code, false)) }}"
                                    placeholder="@lang('dashboard.Title') ({{ strtoupper($locale->name) }})"
                                    {{ $loop->first ? 'required' : '' }}>
                            </div>
                        @endforeach
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">@lang('dashboard.Category')</label>
                        <select name="category_id" class="form-select" required>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) === $category->id)>
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
                                    placeholder="@lang('dashboard.Description') ({{ strtoupper($locale->name) }})">{{ old("description.{$locale->code}", $product->getTranslation('description', $locale->code, false)) }}</textarea>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">@lang('dashboard.SKU')</label>
                        <input type="text" class="form-control" name="sku" value="{{ old('sku', $product->sku) }}"
                            placeholder="SKU-001">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">@lang('dashboard.Price Type')</label>
                        <select name="price_type" class="form-select" required>
                            @foreach (App\Models\Product::priceTypes() as $type)
                                <option value="{{ $type }}" @selected(old('price_type', $product->price_type) === $type)>
                                    @lang('dashboard.' . ucfirst($type))
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">@lang('dashboard.Price')</label>
                        <input type="number" step="0.01" class="form-control" name="price"
                            value="{{ old('price', $product->price) }}" placeholder="0.00">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">@lang('dashboard.Stock Quantity')</label>
                        <input type="number" class="form-control" name="stock_quantity"
                            value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0" placeholder="0">
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">@lang('dashboard.Status')</label>
                        <select name="status" class="form-select" required>
                            @foreach (App\Models\Product::vendorStatuses() as $status)
                                <option value="{{ $status }}" @selected(old('status', $product->status) === $status)>
                                    @lang('dashboard.' . ucfirst($status))
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-12">
                        <label class="form-label">@lang('dashboard.Attributes') (JSON)</label>
                        <textarea class="form-control" rows="5" name="attributes" placeholder='{"key": "value"}'>{{ old('attributes', $product->attributes ? json_encode($product->attributes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                        <small class="text-muted">@lang('dashboard.Attributes helper')</small>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fe fe-save me-1"></i>@lang('dashboard.Save changes')
                    </button>
                    <a href="{{ route('products.show', $product) }}" class="btn btn-light">
                        @lang('dashboard.Cancel')
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
