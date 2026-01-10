@extends('layouts.app')

@section('title', __('dashboard.Chats'))

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('chats.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-xl-4 col-lg-5 col-md-6">
                    <label for="search" class="form-label">@lang('dashboard.Search')</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="fe fe-search"></i></span>
                        <input type="text" class="form-control" id="search" name="search"
                            placeholder="@lang('dashboard.Search chats placeholder')" value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="date_from" class="form-label">@lang('dashboard.Date From')</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                </div>

                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="date_to" class="form-label">@lang('dashboard.Date To')</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                </div>

                <div class="col-xl-4 col-lg-4 col-md-6 d-flex gap-2 align-items-end">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fe fe-filter me-1"></i>@lang('dashboard.Filter')
                    </button>
                    <a href="{{ route('chats.index') }}" class="btn btn-light border">
                        <i class="fe fe-rotate-ccw"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h4 class="card-title mb-0">@lang('dashboard.Chats List')</h4>
                <small class="text-muted">@lang('dashboard.Manage all chats')</small>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-nowrap align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">#</th>
                            <th>@lang('dashboard.User')</th>
                            <th>@lang('dashboard.Vendor')</th>
                            <th>@lang('dashboard.Service')</th>
                            <th>@lang('dashboard.Messages Count')</th>
                            <th>@lang('dashboard.Last Message')</th>
                            <th>@lang('dashboard.Created At')</th>
                            <th class="text-end pe-4">@lang('dashboard.Actions')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($chats as $chat)
                            <tr>
                                <td class="ps-4">
                                    {{ $loop->iteration + ($chats->currentPage() - 1) * $chats->perPage() }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <div class="fw-semibold">
                                                {{ $chat->user->first_name }} {{ $chat->user->last_name }}
                                            </div>
                                            <small class="text-muted">{{ $chat->user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <div class="fw-semibold">
                                                {{ $chat->vendor->first_name }} {{ $chat->vendor->last_name }}
                                            </div>
                                            <small class="text-muted">{{ $chat->vendor->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($chat->service)
                                        <div class="fw-semibold">{{ $chat->service->serviceTitleAr ?? 'N/A' }}</div>
                                    @else
                                        <span class="text-muted">@lang('dashboard.N/A')</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info-transparent text-info">{{ $chat->messages_count ?? 0 }}</span>
                                </td>
                                <td>
                                    @if($chat->lastMessage)
                                        <small class="text-muted">{{ Str::limit($chat->lastMessage->message ?? 'File/Location', 30) }}</small>
                                        <br>
                                        <small class="text-muted">{{ $chat->lastMessage->created_at->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">@lang('dashboard.No messages')</span>
                                    @endif
                                </td>
                                <td>{{ $chat->created_at->format('Y-m-d H:i') }}</td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('chats.show', $chat) }}" class="btn btn-sm btn-primary">
                                        <i class="fe fe-eye me-1"></i>@lang('dashboard.View')
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <p class="text-muted mb-0">@lang('dashboard.No chats found')</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($chats->hasPages())
            <div class="card-footer">
                {{ $chats->links() }}
            </div>
        @endif
    </div>
@endsection

