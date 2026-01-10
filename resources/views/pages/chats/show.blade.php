@extends('layouts.app')

@section('title', __('dashboard.Chat') . ' #' . $chat->id)

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title mb-0">@lang('dashboard.Chat') #{{ $chat->id }}</h4>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <a href="{{ route('chats.index') }}" class="btn btn-sm btn-light">
                        <i class="fe fe-arrow-left me-1"></i>@lang('dashboard.Back')
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 150px;">@lang('dashboard.User'):</th>
                                <td>
                                    <div class="fw-semibold">{{ $chat->user->first_name }} {{ $chat->user->last_name }}</div>
                                    <small class="text-muted">{{ $chat->user->email }}</small>
                                </td>
                            </tr>
                            <tr>
                                <th>@lang('dashboard.Vendor'):</th>
                                <td>
                                    <div class="fw-semibold">{{ $chat->vendor->first_name }} {{ $chat->vendor->last_name }}</div>
                                    <small class="text-muted">{{ $chat->vendor->email }}</small>
                                </td>
                            </tr>
                            @if($chat->service)
                            <tr>
                                <th>@lang('dashboard.Service'):</th>
                                <td>{{ $chat->service->serviceTitleAr ?? 'N/A' }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 150px;">@lang('dashboard.Messages Count'):</th>
                                <td><span class="badge bg-info-transparent text-info">{{ $messages->count() }}</span></td>
                            </tr>
                            <tr>
                                <th>@lang('dashboard.Created At'):</th>
                                <td>{{ $chat->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            @if($chat->reports->count() > 0)
                            <tr>
                                <th>@lang('dashboard.Reports'):</th>
                                <td>
                                    <span class="badge bg-danger-transparent text-danger">{{ $chat->reports->count() }}</span>
                                    <a href="{{ route('chat-reports.index', ['chat_id' => $chat->id]) }}" class="btn btn-sm btn-link">
                                        @lang('dashboard.View Reports')
                                    </a>
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <div class="card shadow-sm border-0">
            <div class="card-header">
                <h4 class="card-title mb-0">@lang('dashboard.Messages')</h4>
            </div>
            <div class="card-body">
                <div class="chat-messages" style="max-height: 600px; overflow-y: auto;">
                    @forelse ($messages as $message)
                        <div class="d-flex mb-3 {{ $message->sender_id === $chat->user_id ? 'justify-content-start' : 'justify-content-end' }}">
                            <div class="message-bubble {{ $message->sender_id === $chat->user_id ? 'bg-light' : 'bg-primary text-white' }}" 
                                 style="max-width: 70%; padding: 12px; border-radius: 12px;">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <small class="{{ $message->sender_id === $chat->user_id ? 'text-muted' : 'text-white-50' }}">
                                        <strong>{{ $message->sender->first_name }} {{ $message->sender->last_name }}</strong>
                                    </small>
                                    <small class="{{ $message->sender_id === $chat->user_id ? 'text-muted' : 'text-white-50' }}">
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
                                        @if($message->latitude && $message->longitude)
                                            <br>
                                            <small>Lat: {{ $message->latitude }}, Lng: {{ $message->longitude }}</small>
                                        @endif
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
    </div>
@endsection

