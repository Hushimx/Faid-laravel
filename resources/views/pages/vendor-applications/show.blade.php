@extends('layouts.app')

@section('title', __('dashboard.Vendor Application Details'))

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="card-title mb-0">@lang('dashboard.Vendor Application') #{{ $application->id }}</h4>
                <small class="text-muted">@lang('dashboard.Submitted by') {{ $application->user->name }}</small>
            </div>
            <div class="d-flex gap-2 align-items-center">
                @if($application->status === 'pending')
                    <span class="badge bg-warning">@lang('dashboard.Pending')</span>
                @elseif($application->status === 'approved')
                    <span class="badge bg-success">@lang('dashboard.Approved')</span>
                @else
                    <span class="badge bg-danger">@lang('dashboard.Rejected')</span>
                @endif
                <a href="{{ route('vendor-applications.index') }}" class="btn btn-sm btn-light">
                    <i class="fe fe-arrow-left me-1"></i>@lang('dashboard.Back')
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3">@lang('dashboard.User Information')</h5>
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 150px;">@lang('dashboard.Name'):</th>
                            <td>{{ $application->user->name }}</td>
                        </tr>
                        <tr>
                            <th>@lang('dashboard.Email'):</th>
                            <td>{{ $application->user->email }}</td>
                        </tr>
                        <tr>
                            <th>@lang('dashboard.Phone'):</th>
                            <td>{{ $application->user->phone ?? __('dashboard.N/A') }}</td>
                        </tr>
                        <tr>
                            <th>@lang('dashboard.User Type'):</th>
                            <td>
                                <span class="badge bg-info">{{ ucfirst($application->user->type) }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5 class="mb-3">@lang('dashboard.Application Details')</h5>
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 150px;">@lang('dashboard.Status'):</th>
                            <td>
                                @if($application->status === 'pending')
                                    <span class="badge bg-warning">@lang('dashboard.Pending')</span>
                                @elseif($application->status === 'approved')
                                    <span class="badge bg-success">@lang('dashboard.Approved')</span>
                                @else
                                    <span class="badge bg-danger">@lang('dashboard.Rejected')</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>@lang('dashboard.Submitted'):</th>
                            <td>{{ $application->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        @if($application->reviewed_at)
                            <tr>
                                <th>@lang('dashboard.Reviewed'):</th>
                                <td>
                                    {{ $application->reviewed_at->format('Y-m-d H:i:s') }}
                                    @if($application->reviewer)
                                        <br><small class="text-muted">@lang('dashboard.by') {{ $application->reviewer->name }}</small>
                                    @endif
                                </td>
                            </tr>
                        @endif
                        @if($application->rejection_reason)
                            <tr>
                                <th>@lang('dashboard.Rejection Reason'):</th>
                                <td class="text-danger">{{ $application->rejection_reason }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-12">
                    <h5 class="mb-3">@lang('dashboard.Vendor Profile Information')</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th style="width: 150px;">@lang('dashboard.Business Name'):</th>
                                    <td>{{ $application->business_name ?? __('dashboard.N/A') }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('dashboard.City'):</th>
                                    <td>{{ $application->city ?? __('dashboard.N/A') }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('dashboard.Category'):</th>
                                    <td>
                                        @if($application->category)
                                            {{ $application->category->name }}
                                        @elseif($application->custom_category)
                                            {{ $application->custom_category }} <span class="badge bg-info">@lang('dashboard.Custom')</span>
                                        @else
                                            @lang('dashboard.N/A')
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">@lang('dashboard.Description of Services'):</label>
                        <div class="p-3 ">
                            {{ $application->bio ?? __('dashboard.N/A') }}
                        </div>
                    </div>

                    @if($application->meta && count($application->meta) > 0)
                        <div class="mb-3">
                            <label class="form-label">@lang('dashboard.Additional Information'):</label>
                            <div class="p-3 ">
                                <pre class="mb-0">{{ json_encode($application->meta, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if($application->status === 'pending')
                <hr>
                <div class="d-flex gap-2">
                    <form action="{{ route('vendor-applications.approve', $application) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success" 
                                onclick="return confirm('@lang('dashboard.Are you sure you want to approve this application? This will convert the user to a vendor.')');">
                            <i class="fe fe-check me-1"></i>@lang('dashboard.Approve Application')
                        </button>
                    </form>

                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="fe fe-x me-1"></i>@lang('dashboard.Reject Application')
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">@lang('dashboard.Reject Application')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('vendor-applications.reject', $application) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label">@lang('dashboard.Rejection Reason') <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" 
                                      placeholder="@lang('dashboard.Please provide a reason for rejecting this application')" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('dashboard.Cancel')</button>
                        <button type="submit" class="btn btn-danger">@lang('dashboard.Reject Application')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


