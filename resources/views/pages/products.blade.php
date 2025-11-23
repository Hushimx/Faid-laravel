@extends('layouts.app')

@section('title', __('dashboard.Products'))

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card custom-card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <span
                        class="rounded-circle bg-primary-transparent text-primary d-flex align-items-center justify-content-center me-3"
                        style="width: 48px; height: 48px;">
                        <i class="fe fe-package fs-5"></i>
                    </span>
                    <div>
                        <p class="text-muted mb-1">@lang('dashboard.Total Products')</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['total'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card custom-card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <span
                        class="rounded-circle bg-success-transparent text-success d-flex align-items-center justify-content-center me-3"
                        style="width: 48px; height: 48px;">
                        <i class="fe fe-check-circle fs-5"></i>
                    </span>
                    <div>
                        <p class="text-muted mb-1">@lang('dashboard.Active Products')</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['active'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card custom-card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <span
                        class="rounded-circle bg-danger-transparent text-danger d-flex align-items-center justify-content-center me-3"
                        style="width: 48px; height: 48px;">
                        <i class="fe fe-x-circle fs-5"></i>
                    </span>
                    <div>
                        <p class="text-muted mb-1">@lang('dashboard.Suspended Products')</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['suspended'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card custom-card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <span
                        class="rounded-circle bg-warning-transparent text-warning d-flex align-items-center justify-content-center me-3"
                        style="width: 48px; height: 48px;">
                        <i class="fe fe-alert-triangle fs-5"></i>
                    </span>
                    <div>
                        <p class="text-muted mb-1">@lang('dashboard.Out of Stock')</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['out_of_stock'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('products.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <label for="search" class="form-label">@lang('dashboard.Search')</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="fe fe-search"></i></span>
                        <input type="text" class="form-control" id="search" name="search"
                            placeholder="@lang('dashboard.Search products placeholder')" value="{{ $filters['search'] }}">
                    </div>
                </div>

                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="status" class="form-label">@lang('dashboard.Status')</label>
                    <select name="status" id="status" class="form-select">
                        <option value="" @selected(!$filters['status'])>@lang('dashboard.All Status')</option>
                        @foreach (App\Models\Product::vendorStatuses() as $status)
                            <option value="{{ $status }}" @selected($filters['status'] === $status)>
                                @lang('dashboard.' . ucfirst($status))
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="admin_status" class="form-label">@lang('dashboard.Admin Status')</label>
                    <select name="admin_status" id="admin_status" class="form-select">
                        <option value="" @selected(!$filters['admin_status'])>@lang('dashboard.All')</option>
                        <option value="active" @selected($filters['admin_status'] === 'active')>@lang('dashboard.Active')</option>
                        <option value="suspended" @selected($filters['admin_status'] === 'suspended')>@lang('dashboard.Suspended')</option>
                    </select>
                </div>

                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="vendor_id" class="form-label">@lang('dashboard.Vendor')</label>
                    <select name="vendor_id" id="vendor_id" class="form-select select2">
                        <option value="">@lang('dashboard.All Vendors')</option>
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor->id }}" @selected($filters['vendor_id'] === $vendor->id)>
                                {{ $vendor->first_name }} {{ $vendor->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="category_id" class="form-label">@lang('dashboard.Category')</label>
                    <select name="category_id" id="category_id" class="form-select select2">
                        <option value="">@lang('dashboard.All Categories')</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected($filters['category_id'] === $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-1 col-lg-2 col-md-3">
                    <label for="per_page" class="form-label">@lang('dashboard.Per Page')</label>
                    <select name="per_page" id="per_page" class="form-select">
                        @foreach ($perPageOptions as $option)
                            <option value="{{ $option }}" @selected($filters['per_page'] === $option)>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-2 col-lg-2 col-md-3 d-flex gap-2 align-items-end">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fe fe-filter me-1"></i>@lang('dashboard.Filter')
                    </button>
                    <a href="{{ route('products.index') }}" class="btn btn-light border">
                        <i class="fe fe-rotate-ccw"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h4 class="card-title mb-0">@lang('dashboard.Products List')</h4>
                <small class="text-muted">@lang('dashboard.Manage all products')</small>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-nowrap align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">#</th>
                            <th>@lang('dashboard.Title')</th>
                            <th>@lang('dashboard.Vendor')</th>
                            <th>@lang('dashboard.Category')</th>
                            <th>@lang('dashboard.Price')</th>
                            <th>@lang('dashboard.Stock')</th>
                            <th>@lang('dashboard.Status')</th>
                            <th>@lang('dashboard.Admin Status')</th>
                            <th>@lang('dashboard.Created At')</th>
                            <th class="text-end pe-4">@lang('dashboard.Actions')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td class="ps-4">
                                    {{ $loop->iteration + ($products->currentPage() - 1) * $products->perPage() }}</td>
                                <td>
                                    <div class="fw-semibold">
                                        <a href="{{ route('products.show', $product) }}" class="text-decoration-none">
                                            {{ $product->title }}
                                        </a>
                                    </div>
                                    @if ($product->sku)
                                        <small class="text-muted">SKU: {{ $product->sku }}</small>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('users.edit', $product->vendor) }}" class="text-decoration-none">
                                        {{ $product->vendor->name }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-transparent text-secondary">
                                        {{ $product->category->name }}
                                    </span>
                                </td>
                                <td>
                                    @if ($product->price)
                                        {{ number_format($product->price, 2) }} @lang('dashboard.Currency')
                                    @else
                                        @lang('dashboard.Negotiable')
                                    @endif
                                </td>
                                <td>
                                    <span
                                        class="badge {{ $product->stock_quantity > 0 ? 'bg-success-transparent text-success' : 'bg-danger-transparent text-danger' }}">
                                        {{ $product->stock_quantity ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                    <span
                                        class="badge {{ $product->status === 'active' ? 'bg-success-transparent text-success' : ($product->status === 'pending' ? 'bg-warning-transparent text-warning' : 'bg-secondary-transparent text-secondary') }}">
                                        @lang('dashboard.' . ucfirst($product->status))
                                    </span>
                                </td>
                                <td>
                                    @if ($product->admin_status === 'suspended')
                                        <span class="badge bg-danger-transparent text-danger">@lang('dashboard.Suspended')</span>
                                    @else
                                        <span class="badge bg-success-transparent text-success">@lang('dashboard.Active')</span>
                                    @endif
                                </td>
                                <td>{{ optional($product->created_at)->format('Y-m-d') }}</td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        @can('products.view')
                                            <a href="{{ route('products.show', $product) }}"
                                                class="btn btn-sm btn-outline-primary" title="@lang('dashboard.View')">
                                                <i class="fe fe-eye"></i>
                                            </a>
                                        @endcan
                                        @can('products.edit')
                                            <a href="{{ route('products.edit', $product) }}"
                                                class="btn btn-sm btn-outline-info" title="@lang('dashboard.Edit')">
                                                <i class="fe fe-edit"></i>
                                            </a>
                                        @endcan
                                        @can('products.manage')
                                            @if ($product->admin_status === 'suspended')
                                                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal"
                                                    data-bs-target="#activateProductModal{{ $product->id }}"
                                                    title="@lang('dashboard.Activate')">
                                                    <i class="fe fe-check"></i>
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                                    data-bs-target="#suspendProductModal{{ $product->id }}"
                                                    title="@lang('dashboard.Suspend')">
                                                    <i class="fe fe-x-circle"></i>
                                                </button>
                                            @endif
                                        @endcan
                                        @can('products.delete')
                                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                                data-bs-target="#deleteProductModal{{ $product->id }}"
                                                title="@lang('dashboard.Delete')">
                                                <i class="fe fe-trash"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>

                            <!-- Suspend Modal -->
                            <div class="modal fade" id="suspendProductModal{{ $product->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">@lang('dashboard.Suspend Product')</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('products.status.update', $product) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="admin_status" value="suspended">
                                            <div class="modal-body">
                                                <p>@lang('dashboard.Suspend product confirmation')</p>
                                                <p class="text-danger fw-semibold mb-0">{{ $product->title }}</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light"
                                                    data-bs-dismiss="modal">@lang('dashboard.Cancel')</button>
                                                <button type="submit" class="btn btn-danger">@lang('dashboard.Suspend')</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Activate Modal -->
                            <div class="modal fade" id="activateProductModal{{ $product->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">@lang('dashboard.Activate Product')</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('products.status.update', $product) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="admin_status" value="">
                                            <div class="modal-body">
                                                <p>@lang('dashboard.Activate product confirmation')</p>
                                                <p class="text-success fw-semibold mb-0">{{ $product->title }}</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light"
                                                    data-bs-dismiss="modal">@lang('dashboard.Cancel')</button>
                                                <button type="submit" class="btn btn-success">@lang('dashboard.Activate')</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Modal -->
                            <div class="modal fade" id="deleteProductModal{{ $product->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">@lang('dashboard.Delete Product')</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('products.destroy', $product) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <div class="modal-body">
                                                <p class="mb-1">@lang('dashboard.Product delete confirmation')</p>
                                                <p class="text-danger fw-semibold mb-0">{{ $product->title }}</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light"
                                                    data-bs-dismiss="modal">@lang('dashboard.Cancel')</button>
                                                <button type="submit" class="btn btn-danger">@lang('dashboard.Delete')</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <div class="py-4">
                                        <h5 class="mb-2">@lang('dashboard.No Products Found')</h5>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($products->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-muted small">
                    @lang('dashboard.Showing results', ['from' => $products->firstItem(), 'to' => $products->lastItem(), 'total' => $products->total()])
                </div>
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('.select2').select2();
        });
    </script>
@endsection
