@extends('layouts.app')

@section('title', __('dashboard.Notification Details'))

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('dashboard.Notification Details') }}</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>{{ __('dashboard.Title (Arabic)') }}</h5>
                            <p class="text-muted">{{ $notification->title['ar'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h5>{{ __('dashboard.Title (English)') }}</h5>
                            <p class="text-muted">{{ $notification->title['en'] ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>{{ __('dashboard.Message (Arabic)') }}</h5>
                            <p class="text-muted">{{ $notification->body['ar'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h5>{{ __('dashboard.Message (English)') }}</h5>
                            <p class="text-muted">{{ $notification->body['en'] ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <h5>{{ __('dashboard.Target Type') }}</h5>
                            <p>{{ $notification->target_display }}</p>
                        </div>
                        <div class="col-md-4">
                            <h5>{{ __('dashboard.Sent Count') }}</h5>
                            <p><span class="badge bg-success">{{ $notification->sent_count }}</span></p>
                        </div>
                        <div class="col-md-4">
                            <h5>{{ __('dashboard.Failed Count') }}</h5>
                            <p><span class="badge bg-danger">{{ $notification->failed_count }}</span></p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>{{ __('dashboard.Success Rate') }}</h5>
                            <p><span class="badge bg-info">{{ $notification->success_rate }}%</span></p>
                        </div>
                        <div class="col-md-6">
                            <h5>{{ __('dashboard.Sent At') }}</h5>
                            <p class="text-muted">{{ $notification->created_at->format('Y-m-d H:i:s') }}</p>
                        </div>
                    </div>

                    @if ($notification->data)
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5>{{ __('dashboard.Custom Data') }}</h5>
                                <pre class="bg-light p-3 rounded">{{ json_encode($notification->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('notifications.index') }}" class="btn btn-secondary">
                            <i class="fe fe-arrow-left me-2"></i>{{ __('dashboard.Back') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
