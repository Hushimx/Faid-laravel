@extends('layouts.app')

@section('title', __('dashboard.Tickets'))

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card custom-card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <span
                        class="rounded-circle bg-primary-transparent text-primary d-flex align-items-center justify-content-center me-3"
                        style="width: 48px; height: 48px;">
                        <i class="fe fe-ticket fs-5"></i>
                    </span>
                    <div>
                        <p class="text-muted mb-1">@lang('dashboard.Total Tickets')</p>
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
                        <p class="text-muted mb-1">@lang('dashboard.Open Tickets')</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['open'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card custom-card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <span
                        class="rounded-circle bg-secondary-transparent text-secondary d-flex align-items-center justify-content-center me-3"
                        style="width: 48px; height: 48px;">
                        <i class="fe fe-x-circle fs-5"></i>
                    </span>
                    <div>
                        <p class="text-muted mb-1">@lang('dashboard.Closed Tickets')</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['closed'] }}</h4>
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
                        <i class="fe fe-users fs-5"></i>
                    </span>
                    <div>
                        <p class="text-muted mb-1">@lang('dashboard.Vendors Tickets')</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['vendors'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card custom-card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <span
                        class="rounded-circle bg-info-transparent text-info d-flex align-items-center justify-content-center me-3"
                        style="width: 48px; height: 48px;">
                        <i class="fe fe-user fs-5"></i>
                    </span>
                    <div>
                        <p class="text-muted mb-1">@lang('dashboard.Users Tickets')</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['users'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('tickets.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-xl-4 col-lg-5 col-md-6">
                    <label for="search" class="form-label">@lang('dashboard.Search')</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="fe fe-search"></i></span>
                        <input type="text" class="form-control" id="search" name="search"
                            placeholder="@lang('dashboard.Search tickets placeholder')" value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="status" class="form-label">@lang('dashboard.Status')</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">@lang('dashboard.All Status')</option>
                        <option value="open" @selected(request('status') === 'open')>@lang('dashboard.Open')</option>
                        <option value="closed" @selected(request('status') === 'closed')>@lang('dashboard.Closed')</option>
                    </select>
                </div>

                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="priority" class="form-label">@lang('dashboard.Priority')</label>
                    <select name="priority" id="priority" class="form-select">
                        <option value="">@lang('dashboard.All Priorities')</option>
                        <option value="low" @selected(request('priority') === 'low')>@lang('dashboard.Low')</option>
                        <option value="medium" @selected(request('priority') === 'medium')>@lang('dashboard.Medium')</option>
                        <option value="high" @selected(request('priority') === 'high')>@lang('dashboard.High')</option>
                    </select>
                </div>

                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="user_type" class="form-label">@lang('dashboard.User Type')</label>
                    <select name="user_type" id="user_type" class="form-select">
                        <option value="">@lang('dashboard.All Types')</option>
                        <option value="vendor" @selected(request('user_type') === 'vendor')>@lang('dashboard.Vendor')</option>
                        <option value="user" @selected(request('user_type') === 'user')>@lang('dashboard.User')</option>
                    </select>
                </div>

                <div class="col-xl-4 col-lg-4 col-md-6 d-flex gap-2 align-items-end">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fe fe-filter me-1"></i>@lang('dashboard.Filter')
                    </button>
                    <a href="{{ route('tickets.index') }}" class="btn btn-light border">
                        <i class="fe fe-rotate-ccw"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h4 class="card-title mb-0">@lang('dashboard.Tickets List')</h4>
                <small class="text-muted">@lang('dashboard.Manage all tickets')</small>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-nowrap align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">#</th>
                            <th>@lang('dashboard.Subject')</th>
                            <th>@lang('dashboard.User')</th>
                            <th>@lang('dashboard.Priority')</th>
                            <th>@lang('dashboard.Status')</th>
                            <th>@lang('dashboard.Assigned To')</th>
                            <th>@lang('dashboard.Last Message')</th>
                            <th>@lang('dashboard.Created At')</th>
                            <th class="text-end pe-4">@lang('dashboard.Actions')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tickets as $ticket)
                            <tr>
                                <td class="ps-4">
                                    {{ $loop->iteration + ($tickets->currentPage() - 1) * $tickets->perPage() }}
                                </td>
                                <td>
                                    <div class="fw-semibold">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="text-decoration-none">
                                            {{ $ticket->subject }}
                                        </a>
                                    </div>
                                    <small class="text-muted">{{ Str::limit($ticket->description, 50) }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <div class="fw-semibold">
                                                {{ $ticket->user->first_name }} {{ $ticket->user->last_name }}
                                                <span class="badge ms-1 {{ $ticket->user->type === 'vendor' ? 'bg-warning-transparent text-warning' : 'bg-info-transparent text-info' }}">
                                                    @if($ticket->user->type === 'vendor')
                                                        @lang('dashboard.Vendor')
                                                    @else
                                                        @lang('dashboard.User')
                                                    @endif
                                                </span>
                                            </div>
                                            <small class="text-muted">{{ $ticket->user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge 
                                        @if($ticket->priority === 'high') bg-danger-transparent text-danger
                                        @elseif($ticket->priority === 'medium') bg-warning-transparent text-warning
                                        @else bg-info-transparent text-info
                                        @endif">
                                        @lang('dashboard.' . ucfirst($ticket->priority))
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $ticket->status === 'open' ? 'bg-success-transparent text-success' : 'bg-secondary-transparent text-secondary' }}">
                                        @lang('dashboard.' . ucfirst($ticket->status))
                                    </span>
                                </td>
                                <td>
                                    @if($ticket->assignedAdmin)
                                        <span class="badge bg-info-transparent text-info">
                                            {{ $ticket->assignedAdmin->first_name }} {{ $ticket->assignedAdmin->last_name }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ticket->latestMessage)
                                        <small class="text-muted">
                                            {{ $ticket->latestMessage->created_at->diffForHumans() }}
                                        </small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $ticket->created_at->format('Y-m-d H:i') }}</td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        @can('tickets.view')
                                            <a href="{{ route('tickets.show', $ticket) }}"
                                                class="btn btn-sm btn-outline-primary" title="@lang('dashboard.View')">
                                                <i class="fe fe-eye"></i>
                                            </a>
                                        @endcan
                                        @can('tickets.delete')
                                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                                data-bs-target="#deleteTicketModal{{ $ticket->id }}"
                                                title="@lang('dashboard.Delete')">
                                                <i class="fe fe-trash"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>

                            <!-- Delete Modal -->
                            <div class="modal fade" id="deleteTicketModal{{ $ticket->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">@lang('dashboard.Delete Ticket')</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('tickets.destroy', $ticket) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <div class="modal-body">
                                                <p class="mb-1">@lang('dashboard.Ticket delete confirmation')</p>
                                                <p class="text-danger fw-semibold mb-0">{{ $ticket->subject }}</p>
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
                                        <i class="fe fe-ticket fs-1 text-muted mb-3"></i>
                                        <h5 class="mb-2">@lang('dashboard.No Tickets Found')</h5>
                                        <p class="text-muted">@lang('dashboard.No tickets found message')</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($tickets->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-muted small">
                    @lang('dashboard.Showing results', ['from' => $tickets->firstItem(), 'to' => $tickets->lastItem(), 'total' => $tickets->total()])
                </div>
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
@endsection
