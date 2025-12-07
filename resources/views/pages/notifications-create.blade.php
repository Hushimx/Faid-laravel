@extends('layouts.app')

@section('title', __('dashboard.Send Notification'))

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('dashboard.Send Notification') }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('notifications.store') }}" method="POST" id="notificationForm">
                        @csrf

                        <!-- Title Section -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('dashboard.Title (Arabic)') }} <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="title_ar" id="title_ar"
                                    class="form-control @error('title_ar') is-invalid @enderror"
                                    value="{{ old('title_ar') }}" required maxlength="100">
                                @error('title_ar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('dashboard.Title (English)') }} <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="title_en" id="title_en"
                                    class="form-control @error('title_en') is-invalid @enderror"
                                    value="{{ old('title_en') }}" required maxlength="100">
                                @error('title_en')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Message Section -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('dashboard.Message (Arabic)') }} <span
                                        class="text-danger">*</span></label>
                                <textarea name="body_ar" id="body_ar" class="form-control @error('body_ar') is-invalid @enderror" rows="4"
                                    required maxlength="500">{{ old('body_ar') }}</textarea>
                                @error('body_ar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('dashboard.Message (English)') }} <span
                                        class="text-danger">*</span></label>
                                <textarea name="body_en" id="body_en" class="form-control @error('body_en') is-invalid @enderror" rows="4"
                                    required maxlength="500">{{ old('body_en') }}</textarea>
                                @error('body_en')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Target Type -->
                        <div class="mb-3">
                            <label class="form-label">{{ __('dashboard.Target Users') }} <span
                                    class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="card-radio-label" for="target_all">
                                        <input type="radio" name="target_type" id="target_all" value="all"
                                            {{ old('target_type', 'all') == 'all' ? 'checked' : '' }}>
                                        <div class="card-radio-content">
                                            <i class="fe fe-users me-2"></i>{{ __('dashboard.All Users') }}
                                        </div>
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <label class="card-radio-label" for="target_role">
                                        <input type="radio" name="target_type" id="target_role" value="role"
                                            {{ old('target_type') == 'role' ? 'checked' : '' }}>
                                        <div class="card-radio-content">
                                            <i class="fe fe-shield me-2"></i>{{ __('dashboard.Specific Role') }}
                                        </div>
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <label class="card-radio-label" for="target_individual">
                                        <input type="radio" name="target_type" id="target_individual" value="individual"
                                            {{ old('target_type') == 'individual' ? 'checked' : '' }}>
                                        <div class="card-radio-content">
                                            <i class="fe fe-user me-2"></i>{{ __('dashboard.Individual Users') }}
                                        </div>
                                    </label>
                                </div>
                            </div>
                            @error('target_type')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Role Selection (hidden by default) -->
                        <div class="mb-3" id="role_selection" style="display: none;">
                            <label class="form-label">{{ __('dashboard.Select Role') }} <span
                                    class="text-danger">*</span></label>
                            <select name="target_role" class="form-select @error('target_role') is-invalid @enderror">
                                <option value="">{{ __('dashboard.Select Role') }}</option>
                                <option value="user" {{ old('target_role') == 'user' ? 'selected' : '' }}>
                                    {{ __('dashboard.User') }}</option>
                                <option value="vendor" {{ old('target_role') == 'vendor' ? 'selected' : '' }}>
                                    {{ __('dashboard.Vendor') }}</option>
                            </select>
                            @error('target_role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Individual Users Selection (hidden by default) -->
                        <div class="mb-3" id="users_selection" style="display: none;">
                            <label class="form-label">{{ __('dashboard.Select Users') }} <span
                                    class="text-danger">*</span></label>
                            <select name="target_users[]" id="target_users"
                                class="form-select @error('target_users') is-invalid @enderror" multiple>
                                @foreach ($users as $user)
                                    <option value="{{ $user['id'] }}"
                                        {{ in_array($user['id'], old('target_users', [])) ? 'selected' : '' }}>
                                        {{ $user['name'] }} ({{ $user['email'] }}) - {{ ucfirst($user['type']) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('target_users')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('notifications.index') }}" class="btn btn-secondary">
                                <i class="fe fe-arrow-left me-2"></i>{{ __('dashboard.Back') }}
                            </a>
                            <button type="submit" class="btn btn-primary" id="sendBtn">
                                <i class="fe fe-send me-2"></i>{{ __('dashboard.Send Notification') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize Select2 for users selection
            $('#target_users').select2({
                placeholder: "{{ __('dashboard.Select Users') }}",
                allowClear: true,
                width: '100%'
            });

            // Toggle target selection fields
            $('input[name="target_type"]').change(function() {
                var targetType = $(this).val();

                $('#role_selection').hide();
                $('#users_selection').hide();

                if (targetType === 'role') {
                    $('#role_selection').show();
                } else if (targetType === 'individual') {
                    $('#users_selection').show();
                }
            });

            // Trigger on page load to show correct fields
            $('input[name="target_type"]:checked').trigger('change');

            // Form submission confirmation
            $('#notificationForm').submit(function(e) {
                var targetType = $('input[name="target_type"]:checked').val();
                var confirmMessage = '';

                if (targetType === 'all') {
                    confirmMessage =
                        "{{ __('dashboard.Are you sure you want to send this notification to all users?') }}";
                } else {
                    confirmMessage =
                        "{{ __('dashboard.Are you sure you want to send this notification?') }}";
                }

                if (!confirm(confirmMessage)) {
                    e.preventDefault();
                    return false;
                }

                $('#sendBtn').prop('disabled', true).html(
                    '<i class="fa fa-spinner fa-spin me-2"></i>{{ __('dashboard.Sending...') }}');
            });
        });
    </script>


    <style>
        /* Card Radio Styles - Hide native radio and make card clickable */
        .card-radio-label {
            display: block;
            cursor: pointer;
            margin-bottom: 0;
        }

        .card-radio-label input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .card-radio-content {
            border: 2px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 1rem;
            transition: all 0.3s;
            text-align: center;
            font-size: 14px;
            font-weight: 500;
        }

        .card-radio-label:hover .card-radio-content {
            border-color: #d1d5db;
            background-color: #f9fafb;
        }

        .card-radio-label input[type="radio"]:checked~.card-radio-content {
            border-color: #6259ca;
            background-color: #f8f9fa;
            color: #6259ca;
        }

        .card-radio-label input[type="radio"]:checked~.card-radio-content i {
            color: #6259ca;
        }

        /* Select2 Enhancement */
        .select2-container--default .select2-selection--multiple {
            min-height: 38px;
            border: 1px solid #e1e5ee !important;
            border-radius: 5px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #6259ca !important;
            border: 1px solid #6259ca !important;
            color: white !important;
            padding: 3px 10px;
            margin-top: 5px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: white !important;
            margin-right: 5px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #fff !important;
        }

        .select2-container {
            width: 100% !important;
        }
    </style>
@endpush
