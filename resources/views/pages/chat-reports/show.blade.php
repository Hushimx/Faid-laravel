@extends('layouts.app')

@section('title', __('dashboard.Chat Report') . ' #' . $report->id)

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title mb-0">@lang('dashboard.Chat Report') #{{ $report->id }}</h4>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <a href="{{ route('chat-reports.index') }}" class="btn btn-sm btn-light">
                        <i class="fe fe-arrow-left me-1"></i>@lang('dashboard.Back')
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 150px;">@lang('dashboard.Reporter'):</th>
                                <td>
                                    <div class="fw-semibold">{{ $report->reporter->first_name }} {{ $report->reporter->last_name }}</div>
                                    <small class="text-muted">{{ $report->reporter->email }}</small>
                                </td>
                            </tr>
                            <tr>
                                <th>@lang('dashboard.Reported User'):</th>
                                <td>
                                    <div class="fw-semibold">{{ $report->reportedUser->first_name }} {{ $report->reportedUser->last_name }}</div>
                                    <small class="text-muted">{{ $report->reportedUser->email }}</small>
                                    <br>
                                    <span class="badge {{ $report->reportedUser->status === 'active' ? 'bg-success-transparent text-success' : 'bg-danger-transparent text-danger' }}">
                                        @lang('dashboard.' . ucfirst($report->reportedUser->status))
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>@lang('dashboard.Chat'):</th>
                                <td>
                                    <a href="{{ route('chats.show', $report->chat_id) }}" class="text-decoration-none">
                                        Chat #{{ $report->chat_id }}
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 150px;">@lang('dashboard.Status'):</th>
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
                            </tr>
                            <tr>
                                <th>@lang('dashboard.Created At'):</th>
                                <td>{{ $report->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            @if($report->reviewed_at)
                            <tr>
                                <th>@lang('dashboard.Reviewed At'):</th>
                                <td>{{ $report->reviewed_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>@lang('dashboard.Reviewed By'):</th>
                                <td>{{ $report->reviewer->first_name ?? 'N/A' }} {{ $report->reviewer->last_name ?? '' }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h5>@lang('dashboard.Reason'):</h5>
                        <p class="border p-3 rounded">{{ $report->reason }}</p>
                    </div>
                    @if($report->admin_notes)
                    <div class="col-12 mt-3">
                        <h5>@lang('dashboard.Admin Notes'):</h5>
                        <p class="border p-3 rounded bg-light">{{ $report->admin_notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Chat Messages -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">@lang('dashboard.Chat Messages')</h4>
            </div>
            <div class="card-body">
                <div class="chat-messages" style="max-height: 400px; overflow-y: auto;">
                    @forelse ($messages as $message)
                        <div class="d-flex mb-3 {{ $message->sender_id === $report->chat->user_id ? 'justify-content-start' : 'justify-content-end' }}">
                            <div class="message-bubble {{ $message->sender_id === $report->chat->user_id ? 'bg-light' : 'bg-primary text-white' }}" 
                                 style="max-width: 70%; padding: 12px; border-radius: 12px;">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <small class="{{ $message->sender_id === $report->chat->user_id ? 'text-muted' : 'text-white-50' }}">
                                        <strong>{{ $message->sender->first_name }} {{ $message->sender->last_name }}</strong>
                                    </small>
                                    <small class="{{ $message->sender_id === $report->chat->user_id ? 'text-muted' : 'text-white-50' }}">
                                        {{ $message->created_at->format('H:i') }}
                                    </small>
                                </div>
                                @if($message->message_type === 'text' && $message->message)
                                    <div>{{ $message->message }}</div>
                                @elseif($message->message_type === 'file')
                                    <div>
                                        <i class="fe fe-file me-1"></i>
                                        @if($message->file_type && in_array(strtolower($message->file_type), ['jpg', 'jpeg', 'png', 'gif']))
                                            <img src="{{ $message->file_path }}" alt="Image" style="max-width: 200px; border-radius: 8px;">
                                        @else
                                            <a href="{{ $message->file_path }}" target="_blank" class="text-decoration-none">
                                                @lang('dashboard.View File')
                                            </a>
                                        @endif
                                    </div>
                                @elseif($message->message_type === 'location')
                                    <div>
                                        <i class="fe fe-map-pin me-1"></i>
                                        @lang('dashboard.Location')
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-muted py-4">@lang('dashboard.No messages')</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Actions -->
        @if($report->status === 'pending')
        <div class="card shadow-sm border-0">
            <div class="card-header">
                <h4 class="card-title mb-0">@lang('dashboard.Actions')</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('chat-reports.ban', $report) }}" method="POST" class="mb-3">
                    @csrf
                    <div class="mb-3">
                        <label for="ban_admin_notes" class="form-label">@lang('dashboard.Admin Notes') (Optional)</label>
                        <textarea class="form-control" id="ban_admin_notes" name="admin_notes" rows="3" maxlength="1000">{{ old('admin_notes') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('@lang('dashboard.Are you sure you want to ban this user?')')">
                        <i class="fe fe-ban me-1"></i>@lang('dashboard.Ban User')
                    </button>
                </form>

                <form action="{{ route('chat-reports.dismiss', $report) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="dismiss_admin_notes" class="form-label">@lang('dashboard.Admin Notes') (Optional)</label>
                        <textarea class="form-control" id="dismiss_admin_notes" name="admin_notes" rows="3" maxlength="1000">{{ old('admin_notes') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-secondary" onclick="return confirm('@lang('dashboard.Are you sure you want to dismiss this report?')')">
                        <i class="fe fe-x me-1"></i>@lang('dashboard.Dismiss Report')
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
@endsection

