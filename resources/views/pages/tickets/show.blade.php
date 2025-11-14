@extends('layouts.app')

@section('title', __('dashboard.Ticket') . ' #' . $ticket->id)

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
    <div class="container-fluid row">
        <!-- Ticket Info Card -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0">@lang('dashboard.Ticket') #{{ $ticket->id }}</h4>
                        <small class="text-muted">{{ $ticket->subject }}</small>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <span
                            class="badge {{ $ticket->status === 'open' ? 'bg-success-transparent text-success' : 'bg-secondary-transparent text-secondary' }}">
                            @lang('dashboard.' . ucfirst($ticket->status))
                        </span>
                        <span
                            class="badge 
                        @if ($ticket->priority === 'high') bg-danger-transparent text-danger
                        @elseif($ticket->priority === 'medium') bg-warning-transparent text-warning
                        @else bg-info-transparent text-info @endif">
                            @lang('dashboard.' . ucfirst($ticket->priority))
                        </span>
                        @if ($ticket->isOpen())
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
                        <div class="col-md-12">
                            <table class="table table-borderless">
                                <tr>
                                    <th style="width: 150px;">@lang('dashboard.User'):</th>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-semibold">{{ $ticket->user->first_name }}
                                                {{ $ticket->user->last_name }}</span>
                                            <span
                                                class="badge {{ $ticket->user->type === 'vendor' ? 'bg-warning-transparent text-warning' : 'bg-info-transparent text-info' }}">
                                                @if ($ticket->user->type === 'vendor')
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
                        <div class="col-md-12">
                            <table class="table table-borderless">
                                <tr>
                                    <th style="width: 150px;">@lang('dashboard.Priority'):</th>
                                    <td>
                                        <span
                                            class="badge 
                                        @if ($ticket->priority === 'high') bg-danger-transparent text-danger
                                        @elseif($ticket->priority === 'medium') bg-warning-transparent text-warning
                                        @else bg-info-transparent text-info @endif">
                                            @lang('dashboard.' . ucfirst($ticket->priority))
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>@lang('dashboard.Status'):</th>
                                    <td>
                                        <span
                                            class="badge {{ $ticket->status === 'open' ? 'bg-success-transparent text-success' : 'bg-secondary-transparent text-secondary' }}">
                                            @lang('dashboard.' . ucfirst($ticket->status))
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>@lang('dashboard.Assigned To'):</th>
                                    <td>
                                        @if ($ticket->assignedAdmin)
                                            <span class="badge bg-info-transparent text-info">
                                                {{ $ticket->assignedAdmin->first_name }}
                                                {{ $ticket->assignedAdmin->last_name }}
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
                                @if ($ticket->closed_at)
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
        </div>


        <!-- Chat Messages -->
        <div class="col-lg-6">
            <div class="card">
                <div class="main-content-app pt-0">
                    <div class="main-content-body main-content-body-chat h-100">
                        <!-- main-chat-header -->
                        <div class="main-chat-body flex-2" id="ChatBody" style="overflow-y: auto;">
                            <div class="content-inner">
                                @foreach ($ticket->messages as $message)
                                    @if ($message->user->type === 'admin')
                                        <div class="media flex-row-reverse chat-right">
                                            <div class="main-img-user online"><img alt="avatar"
                                                    src="../assets/images/users/21.jpg">
                                            </div>
                                            <div class="media-body">
                                                <div class="main-msg-wrapper">
                                                    {{ $message->message }}
                                                </div>
                                                <div>
                                                    <span>{{ $message->created_at->format('H:i') }}
                                                        {{ $message->created_at->format('A') }}</span> <a
                                                        href="javascript:void(0)"><i
                                                            class="icon ion-android-more-horizontal"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="media chat-left">
                                            <div class="main-img-user online"><img alt="avatar"
                                                    src="../assets/images/users/1.jpg">
                                            </div>
                                            <div class="media-body">
                                                <div class="main-msg-wrapper">
                                                    {{ $message->message }}
                                                </div>
                                                <div>
                                                    <span>{{ $message->created_at->format('H:i') }}
                                                        {{ $message->created_at->format('A') }}</span> <a
                                                        href="javascript:void(0)"><i
                                                            class="icon ion-android-more-horizontal"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <form action="{{ route('ticket-messages.store', $ticket->id) }}" method="POST">
                            <div class="main-chat-footer">
                                @csrf
                                <input class="form-control" required name="message" placeholder="Type your message here..."
                                    type="text">
                                {{-- <a class="nav-link" data-bs-toggle="tooltip" href="javascript:void(0)"
                                    title="Attach a File"><i class="fe fe-paperclip"></i></a> --}}
                                <button type="submit" class="btn btn-icon  btn-primary brround"><i
                                        class="fa fa-paper-plane-o"></i></button>
                                <nav class="nav">
                                </nav>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <script>
        setTimeout(() => {

            window.Echo.channel('ticket-chat')
                .listen('message.sent', e => {
                    alert('test');
                    console.log('Event received', e);
                });

        }, 500);
    </script>
@endsection
