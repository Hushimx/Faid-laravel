@extends('layouts.app')

@section('title', 'Vendor Applications')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card custom-card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <span
                        class="rounded-circle bg-primary-transparent text-primary d-flex align-items-center justify-content-center me-3"
                        style="width: 48px; height: 48px;">
                        <i class="fe fe-file-text fs-5"></i>
                    </span>
                    <div>
                        <p class="text-muted mb-1">Total Applications</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['total'] }}</h4>
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
                        <p class="text-muted mb-1">Pending</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['pending'] }}</h4>
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
                        <p class="text-muted mb-1">Approved</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['approved'] }}</h4>
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
                        <p class="text-muted mb-1">Rejected</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['rejected'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('vendor-applications.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-xl-4 col-lg-5 col-md-6">
                    <label for="search" class="form-label">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="fe fe-search"></i></span>
                        <input type="text" class="form-control" id="search" name="search"
                            placeholder="Search by user name, email, or bio" value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                    </select>
                </div>

                <div class="col-xl-2 col-lg-2 col-md-6">
                    <label for="sort_by" class="form-label">Sort By</label>
                    <select name="sort_by" id="sort_by" class="form-select">
                        <option value="created_at" @selected(request('sort_by') === 'created_at')>Created Date</option>
                        <option value="status" @selected(request('sort_by') === 'status')>Status</option>
                    </select>
                </div>

                <div class="col-xl-2 col-lg-2 col-md-6">
                    <label for="sort_direction" class="form-label">Direction</label>
                    <select name="sort_direction" id="sort_direction" class="form-select">
                        <option value="desc" @selected(request('sort_direction') === 'desc')>Descending</option>
                        <option value="asc" @selected(request('sort_direction') === 'asc')>Ascending</option>
                    </select>
                </div>

                <div class="col-xl-2 col-lg-2 col-md-6">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header">
            <h5 class="mb-0">Vendor Applications</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Bio</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Reviewed</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $application)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if ($application->user->profile_picture)
                                            <img src="{{ Storage::url($application->user->profile_picture) }}"
                                                alt="{{ $application->user->name }}" class="rounded-circle me-2" width="40"
                                                height="40">
                                        @else
                                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-2"
                                                style="width: 40px; height: 40px;">
                                                <span class="text-white">{{ substr($application->user->first_name ?? 'U', 0, 1) }}</span>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-semibold">{{ $application->user->name }}</div>
                                            <small class="text-muted">{{ $application->user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="{{ $application->bio }}">
                                        {{ $application->bio ?? 'N/A' }}
                                    </div>
                                </td>
                                <td>
                                    @if($application->city)
                                        {{ $application->city->name }}, {{ $application->country->name ?? '' }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if($application->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($application->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @else
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>{{ $application->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    @if($application->reviewed_at)
                                        {{ $application->reviewed_at->format('Y-m-d H:i') }}
                                        <br>
                                        <small class="text-muted">by {{ $application->reviewer->name ?? 'N/A' }}</small>
                                    @else
                                        <span class="text-muted">Not reviewed</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('vendor-applications.show', $application) }}" class="btn btn-sm btn-primary">
                                        <i class="fe fe-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <p class="text-muted mb-0">No applications found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $applications->links() }}
            </div>
        </div>
    </div>
@endsection


