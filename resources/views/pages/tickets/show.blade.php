@extends('layouts.app')

@section('title', __('dashboard.Ticket') . ' #' . $ticket->id)

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
    <div class="container-fluid">
        <!-- Ticket Info Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title mb-0">@lang('dashboard.Ticket') #{{ $ticket->id }}</h4>
                    <small class="text-muted">{{ $ticket->subject }}</small>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge {{ $ticket->status === 'open' ? 'bg-success-transparent text-success' : 'bg-secondary-transparent text-secondary' }}">
                        @lang('dashboard.' . ucfirst($ticket->status))
                    </span>
                    <span class="badge 
                        @if($ticket->priority === 'high') bg-danger-transparent text-danger
                        @elseif($ticket->priority === 'medium') bg-warning-transparent text-warning
                        @else bg-info-transparent text-info
                        @endif">
                        @lang('dashboard.' . ucfirst($ticket->priority))
                    </span>
                    @if($ticket->isOpen())
                        <form action="{{ route('tickets.update', $ticket) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="closed">
                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                <i class="fe fe-x me-1"></i>@lang('dashboard.Close Ticket')
                            </button>
                        </form>
                    @else
                        <form action="{{ route('tickets.update', $ticket) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="open">
                            <button type="submit" class="btn btn-sm btn-outline-success">
                                <i class="fe fe-check me-1"></i>@lang('dashboard.Reopen Ticket')
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-light">
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
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-semibold">{{ $ticket->user->first_name }} {{ $ticket->user->last_name }}</span>
                                        <span class="badge {{ $ticket->user->type === 'vendor' ? 'bg-warning-transparent text-warning' : 'bg-info-transparent text-info' }}">
                                            @if($ticket->user->type === 'vendor')
                                                @lang('dashboard.Vendor')
                                            @else
                                                @lang('dashboard.User')
                                            @endif
                                        </span>
                                    </div>
                                    <small class="text-muted">{{ $ticket->user->email }}</small>
                                </td>
                            </tr>
                            <tr>
                                <th>@lang('dashboard.Subject'):</th>
                                <td>{{ $ticket->subject }}</td>
                            </tr>
                            <tr>
                                <th>@lang('dashboard.Description'):</th>
                                <td>{{ $ticket->description }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 150px;">@lang('dashboard.Priority'):</th>
                                <td>
                                    <span class="badge 
                                        @if($ticket->priority === 'high') bg-danger-transparent text-danger
                                        @elseif($ticket->priority === 'medium') bg-warning-transparent text-warning
                                        @else bg-info-transparent text-info
                                        @endif">
                                        @lang('dashboard.' . ucfirst($ticket->priority))
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>@lang('dashboard.Status'):</th>
                                <td>
                                    <span class="badge {{ $ticket->status === 'open' ? 'bg-success-transparent text-success' : 'bg-secondary-transparent text-secondary' }}">
                                        @lang('dashboard.' . ucfirst($ticket->status))
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>@lang('dashboard.Assigned To'):</th>
                                <td>
                                    @if($ticket->assignedAdmin)
                                        <span class="badge bg-info-transparent text-info">
                                            {{ $ticket->assignedAdmin->first_name }} {{ $ticket->assignedAdmin->last_name }}
                                        </span>
                                    @else
                                        <span class="text-muted">@lang('dashboard.Unassigned')</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>@lang('dashboard.Created At'):</th>
                                <td>{{ $ticket->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            @if($ticket->closed_at)
                                <tr>
                                    <th>@lang('dashboard.Closed At'):</th>
                                    <td>{{ $ticket->closed_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Messages -->
        <div class="card shadow-sm border-0">
            <div class="card-header">
                <h5 class="mb-0">@lang('dashboard.Messages')</h5>
            </div>
            <div class="card-body">
                <!-- Messages Container -->
                <div id="messages-container" style="height: 500px; overflow-y: auto; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; margin-bottom: 20px; background-color: #f8f9fa;">
                    @foreach($ticket->messages as $message)
                        <div class="message-item mb-3 {{ $message->user_id === auth()->id() ? 'text-end' : '' }}">
                            <div class="d-inline-block p-3 rounded {{ $message->user_id === auth()->id() ? 'bg-primary text-white' : 'bg-white border' }}" 
                                style="max-width: 70%;">
                                <div class="d-flex align-items-center mb-2 {{ $message->user_id === auth()->id() ? 'flex-row-reverse' : '' }}">
                                    @if($message->user->profile_picture)
                                        <img src="{{ Storage::url($message->user->profile_picture) }}" 
                                            alt="{{ $message->user->first_name }} {{ $message->user->last_name }}" 
                                            class="rounded-circle me-2" 
                                            style="width: 32px; height: 32px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2" 
                                            style="width: 32px; height: 32px;">
                                            {{ strtoupper(substr($message->user->first_name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold {{ $message->user_id === auth()->id() ? 'text-white' : '' }}">
                                            {{ $message->user->first_name }} {{ $message->user->last_name }}
                                            @if($message->user->type === 'admin')
                                                <span class="badge bg-danger-transparent text-danger ms-1">@lang('dashboard.Admin')</span>
                                            @endif
                                        </div>
                                        <small class="{{ $message->user_id === auth()->id() ? 'text-white-50' : 'text-muted' }}">
                                            {{ $message->created_at->format('Y-m-d H:i:s') }}
                                        </small>
                                    </div>
                                </div>
                                <div class="{{ $message->user_id === auth()->id() ? 'text-white' : '' }}">
                                    {{ $message->message }}
                                </div>
                                @if($message->attachment)
                                    <div class="mt-2">
                                        <a href="{{ Storage::url($message->attachment) }}" target="_blank" 
                                            class="btn btn-sm {{ $message->user_id === auth()->id() ? 'btn-light' : 'btn-primary' }}">
                                            <i class="fe fe-paperclip me-1"></i>@lang('dashboard.View Attachment')
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Message Form -->
                @if($ticket->isOpen())
                    <form id="message-form" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-10">
                                <textarea class="form-control" id="message" name="message" rows="3" 
                                    placeholder="@lang('dashboard.Type your message...')" required></textarea>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100 h-100" id="send-message-btn">
                                    <i class="fe fe-send me-1"></i>@lang('dashboard.Send')
                                </button>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <input type="file" class="form-control" id="attachment" name="attachment" accept="image/*,application/pdf,.doc,.docx">
                                <small class="text-muted">@lang('dashboard.Max file size: 10MB')</small>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="alert alert-warning">
                        <i class="fe fe-info me-1"></i>@lang('dashboard.Ticket is closed. Cannot send messages.')
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            const ticketId = {{ $ticket->id }};
            const messagesContainer = $('#messages-container');
            
            // Auto scroll to bottom
            messagesContainer.scrollTop(messagesContainer[0].scrollHeight);

            // Message form submission
            $('#message-form').on('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const sendBtn = $('#send-message-btn');
                const originalText = sendBtn.html();
                
                sendBtn.prop('disabled', true).html('<i class="fe fe-loader fa-spin me-1"></i>@lang('dashboard.Sending...')');
                
                $.ajax({
                    url: '{{ route('ticket-messages.store', $ticket) }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            // Add new message to container
                            const message = response.data;
                            const isOwnMessage = message.user.id === {{ auth()->id() }};
                            const messageHtml = `
                                <div class="message-item mb-3 ${isOwnMessage ? 'text-end' : ''}">
                                    <div class="d-inline-block p-3 rounded ${isOwnMessage ? 'bg-primary text-white' : 'bg-white border'}" 
                                        style="max-width: 70%;">
                                        <div class="d-flex align-items-center mb-2 ${isOwnMessage ? 'flex-row-reverse' : ''}">
                                            ${message.user.profile_picture ? 
                                                `<img src="${message.user.profile_picture}" alt="${message.user.first_name} ${message.user.last_name}" 
                                                    class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">` :
                                                `<div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2" 
                                                    style="width: 32px; height: 32px;">
                                                    ${message.user.first_name.charAt(0).toUpperCase()}
                                                </div>`
                                            }
                                            <div>
                                                <div class="fw-semibold ${isOwnMessage ? 'text-white' : ''}">
                                                    ${message.user.first_name} ${message.user.last_name}
                                                    ${message.user.type === 'admin' ? 
                                                        '<span class="badge bg-danger-transparent text-danger ms-1">@lang('dashboard.Admin')</span>' : ''
                                                    }
                                                </div>
                                                <small class="${isOwnMessage ? 'text-white-50' : 'text-muted'}">
                                                    ${message.created_at_human}
                                                </small>
                                            </div>
                                        </div>
                                        <div class="${isOwnMessage ? 'text-white' : ''}">
                                            ${message.message}
                                        </div>
                                        ${message.attachment ? 
                                            `<div class="mt-2">
                                                <a href="${message.attachment}" target="_blank" 
                                                    class="btn btn-sm ${isOwnMessage ? 'btn-light' : 'btn-primary'}">
                                                    <i class="fe fe-paperclip me-1"></i>@lang('dashboard.View Attachment')
                                                </a>
                                            </div>` : ''
                                        }
                                    </div>
                                </div>
                            `;
                            
                            messagesContainer.append(messageHtml);
                            messagesContainer.scrollTop(messagesContainer[0].scrollHeight);
                            
                            // Reset form
                            $('#message-form')[0].reset();
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = '@lang('dashboard.Failed to send message')';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        alert(errorMessage);
                    },
                    complete: function() {
                        sendBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Auto-refresh messages every 30 seconds if ticket is open
            @if($ticket->isOpen())
            setInterval(function() {
                $.ajax({
                    url: '{{ route('ticket-messages.index', $ticket) }}',
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            // This is a simple approach - in production, you might want to compare and only add new messages
                            // For now, we'll just reload if there are new messages
                            // You can implement a more sophisticated solution later
                        }
                    }
                });
            }, 30000);
            @endif
        });
    </script>
    @endpush
@endsection
