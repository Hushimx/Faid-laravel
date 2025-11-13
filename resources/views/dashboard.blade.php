@extends('layouts.app')
@section('title', __('dashboard.dashboard'))
@section('content')

    <!-- Statistics Cards Row 1: Users & Products -->
    <div class="row g-3 mb-4">
        <!-- Total Users -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card custom-card shadow-sm border-0 overflow-hidden h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <p class="text-muted mb-1">@lang('dashboard.Total Users')</p>
                            <h2 class="fw-bold mb-0">{{ $usersStats['total'] }}</h2>
                        </div>
                        <div class="rounded-circle bg-primary-transparent text-primary d-flex align-items-center justify-content-center"
                            style="width: 60px; height: 60px;">
                            <i class="fe fe-users fs-3"></i>
                        </div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-6">
                            <small class="text-muted d-block">@lang('dashboard.Admins')</small>
                            <p class="mb-0 fw-semibold">{{ $usersStats['admins'] }}</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">@lang('dashboard.Vendors')</small>
                            <p class="mb-0 fw-semibold">{{ $usersStats['vendors'] }}</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">@lang('dashboard.Active')</small>
                            <p class="mb-0 fw-semibold text-success">{{ $usersStats['active'] }}</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">@lang('dashboard.Inactive')</small>
                            <p class="mb-0 fw-semibold text-muted">{{ $usersStats['inactive'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Products -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card custom-card shadow-sm border-0 overflow-hidden h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <p class="text-muted mb-1">@lang('dashboard.Total Products')</p>
                            <h2 class="fw-bold mb-0">{{ $productsStats['total'] }}</h2>
                        </div>
                        <div class="rounded-circle bg-success-transparent text-success d-flex align-items-center justify-content-center"
                            style="width: 60px; height: 60px;">
                            <i class="fe fe-package fs-3"></i>
                        </div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-6">
                            <small class="text-muted d-block">@lang('dashboard.Active')</small>
                            <p class="mb-0 fw-semibold text-success">{{ $productsStats['active'] }}</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">@lang('dashboard.Out of Stock')</small>
                            <p class="mb-0 fw-semibold text-danger">{{ $productsStats['out_of_stock'] }}</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">@lang('dashboard.Pending')</small>
                            <p class="mb-0 fw-semibold text-warning">{{ $productsStats['pending'] }}</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">@lang('dashboard.Draft')</small>
                            <p class="mb-0 fw-semibold text-secondary">{{ $productsStats['draft'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Services -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card custom-card shadow-sm border-0 overflow-hidden h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <p class="text-muted mb-1">@lang('dashboard.Total Services')</p>
                            <h2 class="fw-bold mb-0">{{ $servicesStats['total'] }}</h2>
                        </div>
                        <div class="rounded-circle bg-info-transparent text-info d-flex align-items-center justify-content-center"
                            style="width: 60px; height: 60px;">
                            <i class="fe fe-briefcase fs-3"></i>
                        </div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-6">
                            <small class="text-muted d-block">@lang('dashboard.Active')</small>
                            <p class="mb-0 fw-semibold text-success">{{ $servicesStats['active'] }}</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">@lang('dashboard.Pending')</small>
                            <p class="mb-0 fw-semibold text-warning">{{ $servicesStats['pending'] }}</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">@lang('dashboard.Suspended')</small>
                            <p class="mb-0 fw-semibold text-danger">{{ $servicesStats['suspended'] }}</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">@lang('dashboard.Draft')</small>
                            <p class="mb-0 fw-semibold text-secondary">{{ $servicesStats['draft'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Categories -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card custom-card shadow-sm border-0 overflow-hidden h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <p class="text-muted mb-1">@lang('dashboard.Total Categories')</p>
                            <h2 class="fw-bold mb-0">{{ $categoriesStats['total'] }}</h2>
                        </div>
                        <div class="rounded-circle bg-warning-transparent text-warning d-flex align-items-center justify-content-center"
                            style="width: 60px; height: 60px;">
                            <i class="fe fe-grid fs-3"></i>
                        </div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-6">
                            <small class="text-muted d-block">@lang('dashboard.Active')</small>
                            <p class="mb-0 fw-semibold text-success">{{ $categoriesStats['active'] }}</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">@lang('dashboard.Inactive')</small>
                            <p class="mb-0 fw-semibold text-muted">{{ $categoriesStats['inactive'] }}</p>
                        </div>
                        <!-- Root/Sub category breakdown removed -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards Row 2: Detailed Stats -->
    <div class="row g-3 mb-4">
        <!-- Products Status -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card custom-card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span
                            class="rounded-circle bg-success-transparent text-success d-flex align-items-center justify-content-center me-3"
                            style="width: 48px; height: 48px;">
                            <i class="fe fe-check-circle fs-5"></i>
                        </span>
                        <div>
                            <p class="text-muted mb-1">@lang('dashboard.Active Products')</p>
                            <h4 class="fw-semibold mb-0">{{ $productsStats['active'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card custom-card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span
                            class="rounded-circle bg-danger-transparent text-danger d-flex align-items-center justify-content-center me-3"
                            style="width: 48px; height: 48px;">
                            <i class="fe fe-x-circle fs-5"></i>
                        </span>
                        <div>
                            <p class="text-muted mb-1">@lang('dashboard.Suspended Products')</p>
                            <h4 class="fw-semibold mb-0">{{ $productsStats['suspended'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card custom-card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span
                            class="rounded-circle bg-warning-transparent text-warning d-flex align-items-center justify-content-center me-3"
                            style="width: 48px; height: 48px;">
                            <i class="fe fe-alert-triangle fs-5"></i>
                        </span>
                        <div>
                            <p class="text-muted mb-1">@lang('dashboard.Pending Products')</p>
                            <h4 class="fw-semibold mb-0">{{ $productsStats['pending'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card custom-card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span
                            class="rounded-circle bg-secondary-transparent text-secondary d-flex align-items-center justify-content-center me-3"
                            style="width: 48px; height: 48px;">
                            <i class="fe fe-file-text fs-5"></i>
                        </span>
                        <div>
                            <p class="text-muted mb-1">@lang('dashboard.Draft Products')</p>
                            <h4 class="fw-semibold mb-0">{{ $productsStats['draft'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards Row 3: Additional Stats -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card custom-card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span
                            class="rounded-circle bg-info-transparent text-info d-flex align-items-center justify-content-center me-3"
                            style="width: 48px; height: 48px;">
                            <i class="fe fe-check-circle fs-5"></i>
                        </span>
                        <div>
                            <p class="text-muted mb-1">@lang('dashboard.Active Services')</p>
                            <h4 class="fw-semibold mb-0">{{ $servicesStats['active'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card custom-card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span
                            class="rounded-circle bg-danger-transparent text-danger d-flex align-items-center justify-content-center me-3"
                            style="width: 48px; height: 48px;">
                            <i class="fe fe-x-circle fs-5"></i>
                        </span>
                        <div>
                            <p class="text-muted mb-1">@lang('dashboard.Suspended Services')</p>
                            <h4 class="fw-semibold mb-0">{{ $servicesStats['suspended'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card custom-card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span
                            class="rounded-circle bg-warning-transparent text-warning d-flex align-items-center justify-content-center me-3"
                            style="width: 48px; height: 48px;">
                            <i class="fe fe-alert-triangle fs-5"></i>
                        </span>
                        <div>
                            <p class="text-muted mb-1">@lang('dashboard.Pending Services')</p>
                            <h4 class="fw-semibold mb-0">{{ $servicesStats['pending'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card custom-card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span
                            class="rounded-circle bg-secondary-transparent text-secondary d-flex align-items-center justify-content-center me-3"
                            style="width: 48px; height: 48px;">
                            <i class="fe fe-map-pin fs-5"></i>
                        </span>
                        <div>
                            <p class="text-muted mb-1">@lang('dashboard.Locations')</p>
                            <h4 class="fw-semibold mb-0">
                                {{ $locationsStats['countries'] }} @lang('dashboard.Countries') / {{ $locationsStats['cities'] }}
                                @lang('dashboard.Cities')
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-4">
        <!-- Products & Services Chart -->
        <div class="col-xl-8 col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header border-bottom">
                    <h4 class="card-title mb-0">@lang('dashboard.Recent Activity')</h4>
                    <small class="text-muted">@lang('dashboard.Products and Services created in last 7 days')</small>
                </div>
                <div class="card-body">
                    <div id="activityChart" style="min-height: 300px;"></div>
                </div>
            </div>
        </div>

        <!-- Status Distribution -->
        <div class="col-xl-4 col-lg-12">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header border-bottom">
                    <h4 class="card-title mb-0">@lang('dashboard.Products Status')</h4>
                </div>
                <div class="card-body">
                    <div id="productsStatusChart" style="min-height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Items Row -->
    <div class="row g-3 mb-4">
        <!-- Recent Products -->
        <div class="col-xl-6 col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0">@lang('dashboard.Recent Products')</h4>
                        <small class="text-muted">@lang('dashboard.Latest 5 products')</small>
                    </div>
                    <a href="{{ route('products.index') }}" class="btn btn-sm btn-primary">
                        @lang('dashboard.View All')
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-nowrap align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">@lang('dashboard.Title')</th>
                                    <th>@lang('dashboard.Vendor')</th>
                                    <th>@lang('dashboard.Status')</th>
                                    <th class="text-end pe-4">@lang('dashboard.Created At')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentProducts as $product)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-semibold">
                                                <a href="{{ route('products.show', $product) }}"
                                                    class="text-decoration-none">
                                                    {{ Str::limit($product->title, 30) }}
                                                </a>
                                            </div>
                                        </td>
                                        <td>
                                            <small>{{ $product->vendor->name ?? 'N/A' }}</small>
                                        </td>
                                        <td>
                                            <span
                                                class="badge {{ $product->status === 'active' ? 'bg-success-transparent text-success' : ($product->status === 'pending' ? 'bg-warning-transparent text-warning' : 'bg-secondary-transparent text-secondary') }}">
                                                @lang('dashboard.' . ucfirst($product->status))
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <small class="text-muted">{{ $product->created_at->diffForHumans() }}</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <p class="text-muted mb-0">@lang('dashboard.No Products Found')</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Services -->
        <div class="col-xl-6 col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0">@lang('dashboard.Recent Services')</h4>
                        <small class="text-muted">@lang('dashboard.Latest 5 services')</small>
                    </div>
                    <a href="{{ route('services.index') }}" class="btn btn-sm btn-primary">
                        @lang('dashboard.View All')
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-nowrap align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">@lang('dashboard.Title')</th>
                                    <th>@lang('dashboard.Vendor')</th>
                                    <th>@lang('dashboard.Status')</th>
                                    <th class="text-end pe-4">@lang('dashboard.Created At')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentServices as $service)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-semibold">
                                                <a href="{{ route('services.show', $service) }}"
                                                    class="text-decoration-none">
                                                    {{ Str::limit($service->title, 30) }}
                                                </a>
                                            </div>
                                        </td>
                                        <td>
                                            <small>{{ $service->vendor->name ?? 'N/A' }}</small>
                                        </td>
                                        <td>
                                            <span
                                                class="badge {{ $service->status === 'active' ? 'bg-success-transparent text-success' : ($service->status === 'pending' ? 'bg-warning-transparent text-warning' : 'bg-secondary-transparent text-secondary') }}">
                                                @lang('dashboard.' . ucfirst($service->status))
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <small class="text-muted">{{ $service->created_at->diffForHumans() }}</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <p class="text-muted mb-0">@lang('dashboard.No Services Found')</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Activity Chart (Line Chart)
                var activityOptions = {
                    series: [{
                        name: '@lang('dashboard.Products')',
                        data: @json($chartData['products'])
                    }, {
                        name: '@lang('dashboard.Services')',
                        data: @json($chartData['services'])
                    }],
                    chart: {
                        type: 'area',
                        height: 300,
                        toolbar: {
                            show: false
                        },
                        zoom: {
                            enabled: false
                        }
                    },
                    colors: ['#22c55e', '#3b82f6'],
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.7,
                            opacityTo: 0.3,
                            stops: [0, 90, 100]
                        }
                    },
                    xaxis: {
                        categories: @json($chartData['dates']),
                        labels: {
                            style: {
                                fontSize: '12px'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                fontSize: '12px'
                            }
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right'
                    },
                    grid: {
                        borderColor: '#e0e6ed',
                        strokeDashArray: 5,
                        xaxis: {
                            lines: {
                                show: true
                            }
                        },
                        yaxis: {
                            lines: {
                                show: true
                            }
                        }
                    }
                };

                var activityChart = new ApexCharts(document.querySelector("#activityChart"), activityOptions);
                activityChart.render();

                // Products Status Chart (Donut Chart)
                var statusOptions = {
                    series: [
                        {{ $productsStats['active'] }},
                        {{ $productsStats['suspended'] }},
                        {{ $productsStats['pending'] }},
                        {{ $productsStats['draft'] }}
                    ],
                    chart: {
                        type: 'donut',
                        height: 300
                    },
                    colors: ['#22c55e', '#ef4444', '#f59e0b', '#6b7280'],
                    labels: [
                        '@lang('dashboard.Active')',
                        '@lang('dashboard.Suspended')',
                        '@lang('dashboard.Pending')',
                        '@lang('dashboard.Draft')'
                    ],
                    legend: {
                        position: 'bottom'
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val) {
                            return val.toFixed(1) + "%";
                        }
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '65%',
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label: '@lang('dashboard.Total')',
                                        formatter: function() {
                                            return {{ $productsStats['total'] }};
                                        }
                                    }
                                }
                            }
                        }
                    }
                };

                var statusChart = new ApexCharts(document.querySelector("#productsStatusChart"), statusOptions);
                statusChart.render();
            });
        </script>
    @endpush

@endsection
