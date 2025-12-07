@extends('layouts.app')

@section('title', __('dashboard.Notifications'))

@section('content')
    <div class="row">
        @if($errors->any())
            <div class="col-12">
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
        <!-- Statistics Cards -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
            <div class="card overflow-hidden sales-card bg-primary-gradient">
                <div class="card-body pb-0">
                    <h5 class="mb-3 text-white">{{ __('dashboard.Total Notifications') }}</h5>
                    <div class="d-flex">
                        <div class="me-auto">
                            <h2 class="mb-0 number-font text-white">{{ number_format($stats['total_notifications']) }}</h2>
                        </div>
                    </div>
                </div>
                <span id="compositeline" class="pt-1"></span>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
            <div class="card overflow-hidden sales-card bg-success-gradient">
                <div class="card-body pb-0">
                    <h5 class="mb-3 text-white">{{ __('dashboard.Sent') }}</h5>
                    <div class="d-flex">
                        <div class="me-auto">
                            <h2 class="mb-0 number-font text-white">{{ number_format($stats['total_sent']) }}</h2>
                        </div>
                    </div>
                </div>
                <span id="compositeline2" class="pt-1"></span>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
            <div class="card overflow-hidden sales-card bg-danger-gradient">
                <div class="card-body pb-0">
                    <h5 class="mb-3 text-white">{{ __('dashboard.Failed') }}</h5>
                    <div class="d-flex">
                        <div class="me-auto">
                            <h2 class="mb-0 number-font text-white">{{ number_format($stats['total_failed']) }}</h2>
                        </div>
                    </div>
                </div>
                <span id="compositeline3" class="pt-1"></span>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
            <div class="card overflow-hidden sales-card bg-warning-gradient">
                <div class="card-body pb-0">
                    <h5 class="mb-3 text-white">{{ __('dashboard.Success Rate') }}</h5>
                    <div class="d-flex">
                        <div class="me-auto">
                            <h2 class="mb-0 number-font text-white">{{ number_format($stats['success_rate'], 2) }}%</h2>
                        </div>
                    </div>
                </div>
                <span id="compositeline4" class="pt-1"></span>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="row row-sm">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <h4 class="card-title mg-b-0">{{ __('dashboard.Notification History') }}</h4>
                        <a href="{{ route('notifications.create') }}" class="btn btn-primary">
                            <i class="fe fe-plus me-2"></i>{{ __('dashboard.Send Notification') }}
                        </a>
                    </div>
                    <p class="tx-12 tx-gray-500 mb-2">{{ __('dashboard.Manage all notifications') }}</p>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" action="{{ route('notifications.index') }}" class="row mb-3">
                        <div class="col-md-3">
                            <select name="target_type" class="form-select">
                                <option value="">{{ __('dashboard.All Targets') }}</option>
                                <option value="all" {{ request('target_type') == 'all' ? 'selected' : '' }}>
                                    {{ __('dashboard.All Users') }}</option>
                                <option value="role" {{ request('target_type') == 'role' ? 'selected' : '' }}>
                                    {{ __('dashboard.Specific Role') }}</option>
                                <option value="individual" {{ request('target_type') == 'individual' ? 'selected' : '' }}>
                                    {{ __('dashboard.Individual Users') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="date_from" class="form-control"
                                placeholder="{{ __('dashboard.From Date') }}" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="date_to" class="form-control"
                                placeholder="{{ __('dashboard.To Date') }}" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">{{ __('dashboard.Filter') }}</button>
                            <a href="{{ route('notifications.index') }}"
                                class="btn btn-secondary">{{ __('dashboard.Reset') }}</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap border-bottom" id="notificationsTable">
                            <thead>
                                <tr>
                                    <th>{{ __('dashboard.Title') }}</th>
                                    <th>{{ __('dashboard.Target Type') }}</th>
                                    <th>{{ __('dashboard.Sent Count') }}</th>
                                    <th>{{ __('dashboard.Failed Count') }}</th>
                                    <th>{{ __('dashboard.Success Rate') }}</th>
                                    <th>{{ __('dashboard.Sent At') }}</th>
                                    <th>{{ __('dashboard.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($notifications as $notification)
                                    <tr>
                                        <td>{{ $notification->getTitleInLanguage() }}</td>
                                        <td>
                                            @if ($notification->target_type == 'all')
                                                <span class="badge bg-primary">{{ __('dashboard.All Users') }}</span>
                                            @elseif($notification->target_type == 'role')
                                                <span class="badge bg-info">{{ __('dashboard.Role') }}:
                                                    {{ ucfirst($notification->target_value['role'] ?? '') }}</span>
                                            @else
                                                <span class="badge bg-warning">{{ __('dashboard.Individual Users') }}
                                                    ({{ count($notification->target_value ?? []) }})</span>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-success">{{ $notification->sent_count }}</span></td>
                                        <td><span class="badge bg-danger">{{ $notification->failed_count }}</span></td>
                                        <td>
                                            @php
                                                $rate = $notification->success_rate;
                                                $color = $rate >= 80 ? 'success' : ($rate >= 50 ? 'warning' : 'danger');
                                            @endphp
                                            <span class="badge bg-{{ $color }}">{{ $rate }}%</span>
                                        </td>
                                        <td>{{ $notification->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <a href="{{ route('notifications.show', $notification) }}"
                                                class="btn btn-sm btn-info">
                                                <i class="fe fe-eye"></i> {{ __('dashboard.View') }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <div class="py-4">
                                                <i class="fe fe-bell" style="font-size: 48px; color: #ccc;"></i>
                                                <p class="mt-2">{{ __('dashboard.No notifications found') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $notifications->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Optional: Initialize DataTables if needed
            // $('#notificationsTable').DataTable();
        });
    </script>
@endpush
