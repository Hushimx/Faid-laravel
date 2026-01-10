@extends('layouts.app')

@section('title', __('dashboard.Chat Reports'))

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('chat-reports.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-xl-4 col-lg-5 col-md-6">
                    <label for="search" class="form-label">@lang('dashboard.Search')</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="fe fe-search"></i></span>
                        <input type="text" class="form-control" id="search" name="search"
                            placeholder="@lang('dashboard.Search reports placeholder')" value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="status" class="form-label">@lang('dashboard.Status')</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">@lang('dashboard.All Status')</option>
                        <option value="pending" @selected(request('status') === 'pending')>@lang('dashboard.Pending')</option>
                        <option value="reviewed" @selected(request('status') === 'reviewed')>@lang('dashboard.Reviewed')</option>
                        <option value="resolved" @selected(request('status') === 'resolved')>@lang('dashboard.Resolved')</option>
                        <option value="dismissed" @selected(request('status') === 'dismissed')>@lang('dashboard.Dismissed')</option>
                    </select>
                </div>

                <div class="col-xl-4 col-lg-4 col-md-6 d-flex gap-2 align-items-end">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fe fe-filter me-1"></i>@lang('dashboard.Filter')
                    </button>
                    <a href="{{ route('chat-reports.index') }}" class="btn btn-light border">
                        <i class="fe fe-rotate-ccw"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h4 class="card-title mb-0">@lang('dashboard.Chat Reports List')</h4>
                <small class="text-muted">@lang('dashboard.Manage all chat reports')</small>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-nowrap align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">#</th>
                            <th>@lang('dashboard.Chat')</th>
                            <th>@lang('dashboard.Reporter')</th>
                            <th>@lang('dashboard.Reported User')</th>
                            <th>@lang('dashboard.Reason')</th>
                            <th>@lang('dashboard.Status')</th>
                            <th>@lang('dashboard.Created At')</th>
                            <th class="text-end pe-4">@lang('dashboard.Actions')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $report)
                            <tr>
                                <td class="ps-4">
                                    {{ $loop->iteration + ($reports->currentPage() - 1) * $reports->perPage() }}
                                </td>
                                <td>
                                    <a href="{{ route('chats.show', $report->chat_id) }}" class="text-decoration-none">
                                        Chat #{{ $report->chat_id }}
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-semibold">
                                        {{ $report->reporter->first_name }} {{ $report->reporter->last_name }}
                                    </div>
                                    <small class="text-muted">{{ $report->reporter->email }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold">
                                        {{ $report->reportedUser->first_name }} {{ $report->reportedUser->last_name }}
                                    </div>
                                    <small class="text-muted">{{ $report->reportedUser->email }}</small>
                                </td>
                                <td>
                                    <small>{{ Str::limit($report->reason, 50) }}</small>
                                </td>
                                <td>
                                    <span class="badge 
                                        @if($report->status === 'pending') bg-warning-transparent text-warning
                                        @elseif($report->status === 'resolved') bg-success-transparent text-success
                                        @elseif($report->status === 'dismissed') bg-secondary-transparent text-secondary
                                        @else bg-info-transparent text-info
                                        @endif">
                                        @lang('dashboard.' . ucfirst($report->status))
                                    </span>
                                </td>
                                <td>{{ $report->created_at->format('Y-m-d H:i') }}</td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('chat-reports.show', $report) }}" class="btn btn-sm btn-primary">
                                        <i class="fe fe-eye me-1"></i>@lang('dashboard.View')
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <p class="text-muted mb-0">@lang('dashboard.No reports found')</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($reports->hasPages())
            <div class="card-footer">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
@endsection

