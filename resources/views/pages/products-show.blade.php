@extends('layouts.app')

@section('title', __('dashboard.View Product'))

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">@lang('dashboard.Product Details')</h4>
            <div>
                @can('products.edit')
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">
                        <i class="fe fe-edit me-1"></i>@lang('dashboard.Edit')
                    </a>
                @endcan
                <a href="{{ route('products.index') }}" class="btn btn-light">
                    <i class="fe fe-arrow-left me-1"></i>@lang('dashboard.Back')
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="mb-4">
                        <h5 class="fw-semibold mb-3">@lang('dashboard.Basic Information')</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th class="bg-light" style="width: 200px;">@lang('dashboard.Title')</th>
                                <td>{{ $product->title }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">@lang('dashboard.Description')</th>
                                <td>{{ $product->description ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">@lang('dashboard.Category')</th>
                                <td>
                                    <span class="badge bg-secondary-transparent text-secondary">
                                        {{ $product->category->name }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">@lang('dashboard.Vendor')</th>
                                <td>
                                    <a href="{{ route('users.edit', $product->vendor) }}" class="text-decoration-none">
                                        {{ $product->vendor->name }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">@lang('dashboard.SKU')</th>
                                <td>{{ $product->sku ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">@lang('dashboard.Price Type')</th>
                                <td>@lang('dashboard.' . ucfirst($product->price_type))</td>
                            </tr>
                            <tr>
                                <th class="bg-light">@lang('dashboard.Price')</th>
                                <td>
                                    @if ($product->price)
                                        {{ number_format($product->price, 2) }} @lang('dashboard.Currency')
                                    @else
                                        @lang('dashboard.Negotiable')
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">@lang('dashboard.Stock Quantity')</th>
                                <td>
                                    <span
                                        class="badge {{ $product->stock_quantity > 0 ? 'bg-success-transparent text-success' : 'bg-danger-transparent text-danger' }}">
                                        {{ $product->stock_quantity ?? 0 }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">@lang('dashboard.Status')</th>
                                <td>
                                    <span
                                        class="badge {{ $product->status === 'active' ? 'bg-success-transparent text-success' : ($product->status === 'pending' ? 'bg-warning-transparent text-warning' : 'bg-secondary-transparent text-secondary') }}">
                                        @lang('dashboard.' . ucfirst($product->status))
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">@lang('dashboard.Admin Status')</th>
                                <td>
                                    @if ($product->admin_status === 'suspended')
                                        <span class="badge bg-danger-transparent text-danger">@lang('dashboard.Suspended')</span>
                                    @else
                                        <span class="badge bg-success-transparent text-success">@lang('dashboard.Active')</span>
                                    @endif
                                </td>
                            </tr>
                            @if ($product->attributes)
                                <tr>
                                    <th class="bg-light">@lang('dashboard.Attributes')</th>
                                    <td>
                                        <pre class="mb-0">{{ json_encode($product->attributes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <th class="bg-light">@lang('dashboard.Published At')</th>
                                <td>{{ $product->published_at ? $product->published_at->format('Y-m-d H:i:s') : '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">@lang('dashboard.Created At')</th>
                                <td>{{ $product->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">@lang('dashboard.Updated At')</th>
                                <td>{{ $product->updated_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="mb-4">
                        <h5 class="fw-semibold mb-3">@lang('dashboard.Media')</h5>
                        @if ($product->images->count() > 0 || $product->videos->count() > 0)
                            <div class="row g-2">
                                @foreach ($product->images as $image)
                                    <div class="col-6">
                                        <div class="border rounded p-2">
                                            <img src="{{ url(Storage::url($image->path)) }}" alt="Image"
                                                class="img-fluid rounded">
                                        </div>
                                    </div>
                                @endforeach
                                @foreach ($product->videos as $video)
                                    <div class="col-12">
                                        <div class="border rounded p-2">
                                            <video controls class="w-100" style="max-height: 200px;">
                                                <source src="{{ url(Storage::url($video->path)) }}"
                                                    type="{{ $video->mime_type }}">
                                            </video>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">@lang('dashboard.No media found')</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
