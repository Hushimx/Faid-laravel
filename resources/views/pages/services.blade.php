@extends('layouts.app')

@section('title', __('dashboard.Services'))

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card custom-card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <span
                        class="rounded-circle bg-primary-transparent text-primary d-flex align-items-center justify-content-center me-3"
                        style="width: 48px; height: 48px;">
                        <i class="fe fe-settings fs-5"></i>
                    </span>
                    <div>
                        <p class="text-muted mb-1">@lang('dashboard.Total Services')</p>
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
                        <p class="text-muted mb-1">@lang('dashboard.Active Services')</p>
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
                        <p class="text-muted mb-1">@lang('dashboard.Suspended Services')</p>
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
                        <i class="fe fe-clock fs-5"></i>
                    </span>
                    <div>
                        <p class="text-muted mb-1">@lang('dashboard.Pending Services')</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['pending'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('services.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <label for="search" class="form-label">@lang('dashboard.Search')</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="fe fe-search"></i></span>
                        <input type="text" class="form-control" id="search" name="search"
                            placeholder="@lang('dashboard.Search services placeholder')" value="{{ $filters['search'] }}">
                    </div>
                </div>

                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="status" class="form-label">@lang('dashboard.Status')</label>
                    <select name="status" id="status" class="form-select">
                        <option value="" @selected(!$filters['status'])>@lang('dashboard.All Status')</option>
                        @foreach (App\Models\Service::vendorStatuses() as $status)
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
                    <a href="{{ route('services.index') }}" class="btn btn-light border">
                        <i class="fe fe-rotate-ccw"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h4 class="card-title mb-0">@lang('dashboard.Services List')</h4>
                <small class="text-muted">@lang('dashboard.Manage all services')</small>
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
                            <th>@lang('dashboard.Status')</th>
                            <th>@lang('dashboard.Admin Status')</th>
                            <th>@lang('dashboard.Created At')</th>
                            <th class="text-end pe-4">@lang('dashboard.Actions')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($services as $service)
                            <tr>
                                <td class="ps-4">
                                    {{ $loop->iteration + ($services->currentPage() - 1) * $services->perPage() }}</td>
                                <td>
                                    <div class="fw-semibold">
                                        <a href="{{ route('services.show', $service) }}" class="text-decoration-none">
                                            {{ $service->title }}
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('users.edit', $service->vendor) }}" class="text-decoration-none">
                                        {{ $service->vendor->name }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-transparent text-secondary">
                                        {{ $service->category->name }}
                                    </span>
                                </td>
                                <td>
                                    @if ($service->price)
                                        {{ number_format($service->price, 2) }} @lang('dashboard.Currency')
                                    @else
                                        @lang('dashboard.Negotiable')
                                    @endif
                                </td>
                                <td>
                                    <span
                                        class="badge {{ $service->status === 'active' ? 'bg-success-transparent text-success' : ($service->status === 'pending' ? 'bg-warning-transparent text-warning' : 'bg-secondary-transparent text-secondary') }}">
                                        @lang('dashboard.' . ucfirst($service->status))
                                    </span>
                                </td>
                                <td>
                                    @if ($service->admin_status === 'suspended')
                                        <span class="badge bg-danger-transparent text-danger">@lang('dashboard.Suspended')</span>
                                    @else
                                        <span class="badge bg-success-transparent text-success">@lang('dashboard.Active')</span>
                                    @endif
                                </td>
                                <td>{{ optional($service->created_at)->format('Y-m-d') }}</td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="{{ route('services.show', $service) }}"
                                            class="btn btn-sm btn-outline-primary" title="@lang('dashboard.View')">
                                            <i class="fe fe-eye"></i>
                                        </a>
                                        <a href="{{ route('services.edit', $service) }}"
                                            class="btn btn-sm btn-outline-info" title="@lang('dashboard.Edit')">
                                            <i class="fe fe-edit"></i>
                                        </a>
                                        @if ($service->admin_status === 'suspended')
                                            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal"
                                                data-bs-target="#activateServiceModal{{ $service->id }}"
                                                title="@lang('dashboard.Activate')">
                                                <i class="fe fe-check"></i>
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                                data-bs-target="#suspendServiceModal{{ $service->id }}"
                                                title="@lang('dashboard.Suspend')">
                                                <i class="fe fe-x-circle"></i>
                                            </button>
                                        @endif
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                            data-bs-target="#deleteServiceModal{{ $service->id }}"
                                            title="@lang('dashboard.Delete')">
                                            <i class="fe fe-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Suspend Modal -->
                            <div class="modal fade" id="suspendServiceModal{{ $service->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">@lang('dashboard.Suspend Service')</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('services.status.update', $service) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="admin_status" value="suspended">
                                            <div class="modal-body">
                                                <p>@lang('dashboard.Suspend service confirmation')</p>
                                                <p class="text-danger fw-semibold mb-0">{{ $service->title }}</p>
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
                            <div class="modal fade" id="activateServiceModal{{ $service->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">@lang('dashboard.Activate Service')</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('services.status.update', $service) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="admin_status" value="">
                                            <div class="modal-body">
                                                <p>@lang('dashboard.Activate service confirmation')</p>
                                                <p class="text-success fw-semibold mb-0">{{ $service->title }}</p>
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
                            <div class="modal fade" id="deleteServiceModal{{ $service->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">@lang('dashboard.Delete Service')</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('services.destroy', $service) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <div class="modal-body">
                                                <p class="mb-1">@lang('dashboard.Service delete confirmation')</p>
                                                <p class="text-danger fw-semibold mb-0">{{ $service->title }}</p>
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
                                <td colspan="9" class="text-center py-5">
                                    <div class="py-4">
                                        <h5 class="mb-2">@lang('dashboard.No Services Found')</h5>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($services->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-muted small">
                    @lang('dashboard.Showing results', ['from' => $services->firstItem(), 'to' => $services->lastItem(), 'total' => $services->total()])
                </div>
                {{ $services->links() }}
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('.select2').select2();
        });
    </script>
@endsection
