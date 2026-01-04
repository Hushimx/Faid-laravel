@extends('layouts.app')

@section('title', 'Vendor Application Details')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="card-title mb-0">Vendor Application #{{ $application->id }}</h4>
                <small class="text-muted">Submitted by {{ $application->user->name }}</small>
            </div>
            <div class="d-flex gap-2 align-items-center">
                @if($application->status === 'pending')
                    <span class="badge bg-warning">Pending</span>
                @elseif($application->status === 'approved')
                    <span class="badge bg-success">Approved</span>
                @else
                    <span class="badge bg-danger">Rejected</span>
                @endif
                <a href="{{ route('vendor-applications.index') }}" class="btn btn-sm btn-light">
                    <i class="fe fe-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3">User Information</h5>
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 150px;">Name:</th>
                            <td>{{ $application->user->name }}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{ $application->user->email }}</td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td>{{ $application->user->phone ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>User Type:</th>
                            <td>
                                <span class="badge bg-info">{{ ucfirst($application->user->type) }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5 class="mb-3">Application Details</h5>
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 150px;">Status:</th>
                            <td>
                                @if($application->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($application->status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Submitted:</th>
                            <td>{{ $application->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        @if($application->reviewed_at)
                            <tr>
                                <th>Reviewed:</th>
                                <td>
                                    {{ $application->reviewed_at->format('Y-m-d H:i:s') }}
                                    @if($application->reviewer)
                                        <br><small class="text-muted">by {{ $application->reviewer->name }}</small>
                                    @endif
                                </td>
                            </tr>
                        @endif
                        @if($application->rejection_reason)
                            <tr>
                                <th>Rejection Reason:</th>
                                <td class="text-danger">{{ $application->rejection_reason }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-12">
                    <h5 class="mb-3">Vendor Profile Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th style="width: 150px;">Country:</th>
                                    <td>{{ $application->country->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>City:</th>
                                    <td>{{ $application->city->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Category:</th>
                                    <td>
                                        @if($application->category)
                                            {{ $application->category->name }}
                                        @elseif($application->custom_category)
                                            {{ $application->custom_category }} <span class="badge bg-info">Custom</span>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                @if($application->lat && $application->lng)
                                    <tr>
                                        <th>Location:</th>
                                        <td>
                                            <a href="https://www.google.com/maps?q={{ $application->lat }},{{ $application->lng }}" 
                                               target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fe fe-map-pin me-1"></i>View on Map
                                            </a>
                                            <br>
                                            <small class="text-muted">Lat: {{ $application->lat }}, Lng: {{ $application->lng }}</small>
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            @if($application->banner)
                                <div class="mb-3">
                                    <label class="form-label">Banner Image:</label>
                                    <div>
                                        <img src="{{ Storage::url($application->banner) }}" 
                                             alt="Banner" 
                                             class="img-fluid rounded" 
                                             style="max-height: 200px;">
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bio:</label>
                        <div class="p-3 bg-light rounded">
                            {{ $application->bio ?? 'N/A' }}
                        </div>
                    </div>

                    @if($application->meta && count($application->meta) > 0)
                        <div class="mb-3">
                            <label class="form-label">Additional Information:</label>
                            <div class="p-3 bg-light rounded">
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
                                onclick="return confirm('Are you sure you want to approve this application? This will convert the user to a vendor.');">
                            <i class="fe fe-check me-1"></i>Approve Application
                        </button>
                    </form>

                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="fe fe-x me-1"></i>Reject Application
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
                    <h5 class="modal-title" id="rejectModalLabel">Reject Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('vendor-applications.reject', $application) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" 
                                      placeholder="Please provide a reason for rejecting this application..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Application</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


