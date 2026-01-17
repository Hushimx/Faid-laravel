@extends('layouts.app')

@section('title', __('dashboard.View Service'))

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('content')

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">@lang('dashboard.Service')</h4>
            <div>
                @can('services.edit')
                    <a href="{{ route('services.edit', $service) }}" class="btn btn-primary">
                        <i class="fe fe-edit me-1"></i>@lang('dashboard.Edit')
                    </a>
                @endcan
                <a href="{{ route('services.index') }}" class="btn btn-light">
                    <i class="fe fe-arrow-left me-1"></i>@lang('dashboard.Back')
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="mb-4">
                        <h5 class="fw-semibold mb-3">@lang('dashboard.Basic Information')</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th class="bg-light" style="width: 200px;">@lang('dashboard.Title')</th>
                                <td>{{ $service->title }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">@lang('dashboard.Description')</th>
                                <td>{{ $service->description ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">@lang('dashboard.Category')</th>
                                <td>
                                    <span class="badge bg-secondary-transparent text-secondary">
                                        {{ $service->category->name }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">@lang('dashboard.Vendor')</th>
                                <td>
                                    <a href="{{ route('users.edit', $service->vendor) }}" class="text-decoration-none">
                                        {{ $service->vendor->name }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">@lang('dashboard.Price Type')</th>
                                <td>@lang('dashboard.' . ucfirst($service->price_type))</td>
                            </tr>
                            <tr>
                                <th class="bg-light">@lang('dashboard.Price')</th>
                                <td>
                                    @if ($service->price_type === 'fixed' && $service->price)
                                        <strong class="text-primary">{{ number_format($service->price, 2) }} @lang('dashboard.Currency')</strong>
                                    @elseif ($service->price_type === 'negotiable')
                                        <span class="badge bg-info-transparent text-info">@lang('dashboard.Negotiable')</span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">@lang('dashboard.Address')</th>
                                <td>{{ $service->address ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">@lang('dashboard.City')</th>
                                <td>
                                    @if ($service->city)
                                        {{ $service->city->name }}
                                        @if ($service->city->country)
                                            <span class="text-muted">({{ $service->city->country->name }})</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">@lang('dashboard.Status')</th>
                                <td>
                                    <span
                                        class="badge {{ $service->status === 'active' ? 'bg-success-transparent text-success' : ($service->status === 'pending' ? 'bg-warning-transparent text-warning' : 'bg-secondary-transparent text-secondary') }}">
                                        @lang('dashboard.' . ucfirst($service->status))
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">@lang('dashboard.Admin Status')</th>
                                <td>
                                    @if ($service->admin_status === 'suspended')
                                        <span class="badge bg-danger-transparent text-danger">@lang('dashboard.Suspended')</span>
                                    @else
                                        <span class="badge bg-success-transparent text-success">@lang('dashboard.Active')</span>
                                    @endif
                                </td>
                            </tr>
                            @if ($service->attributes)
                                <tr>
                                    <th class="bg-light">@lang('dashboard.Attributes')</th>
                                    <td>
                                        <pre class="mb-0">{{ json_encode($service->attributes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <th class="bg-light">@lang('dashboard.Published At')</th>
                                <td>{{ $service->published_at ? $service->published_at->format('Y-m-d H:i:s') : '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">@lang('dashboard.Created At')</th>
                                <td>{{ $service->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">@lang('dashboard.Updated At')</th>
                                <td>{{ $service->updated_at ? $service->updated_at->format('Y-m-d H:i:s') : '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="mb-4">
                        <h5 class="fw-semibold mb-3">@lang('dashboard.Media')</h5>
                        @if ($service->images->count() > 0 || $service->videos->count() > 0)
                            <div class="row g-2">
                                @foreach ($service->images as $image)
                                    <div class="col-6">
                                        <div class="border p-2">
                                            <img src="{{ url(Storage::url($image->path)) }}" alt="Image"
                                                class="img-fluid">
                                        </div>
                                    </div>
                                @endforeach
                                @foreach ($service->videos as $video)
                                    <div class="col-12">
                                        <div class="border p-2">
                                            <video controls class="w-100" style="max-height: 200px;">
                                                <source src="{{ url(Storage::url($video->path)) }}"
                                                    type="{{ $video->mime_type }}">
                                            </video>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">@lang('dashboard.No media found')</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">@lang('dashboard.Rating')</h5>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-warning-transparent text-warning fs-6">
                    @if ($service->averageRating())
                        {{ number_format($service->averageRating(), 1) }}
                        <i class="fe fe-star-fill ms-1"></i>
                    @else
                        @lang('dashboard.No ratings yet')
                    @endif
                </span>
                <small class="text-muted">
                    @if ($service->reviewsCount() > 0)
                        ({{ $service->reviewsCount() }} @lang('dashboard.review' . ($service->reviewsCount() > 1 ? 's' : '')))
                    @endif
                </small>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead>
                        <tr>
                            <th>@lang('dashboard.Rating')</th>
                            <th>@lang('dashboard.Comment')</th>
                            <th>@lang('dashboard.User')</th>
                            <th>@lang('dashboard.Created At')</th>
                            <th>@lang('dashboard.Actions')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($service->reviews as $review)
                            <tr>
                                <td>
                                    <span class="badge bg-warning-transparent text-warning fs-6">
                                        {{ number_format($review->rating, 1) }}
                                        <i class="fe fe-star-fill ms-1"></i>
                                    </span>
                                </td>
                                <td>{{ $review->comment ?? '-' }}</td>
                                <td>{{ $review->user->name ?? '-' }}</td>
                                <td>{{ $review->created_at->format('Y-m-d H:i:s') }}</td>
                                <td>
                                    <form
                                        action="{{ route('services.reviews.destroy', ['service' => $service->id, 'review' => $review->id]) }}"
                                        method="POST" onsubmit="return confirm('@lang('dashboard.Are you sure you want to delete this review?')');">
                                        @csrf
                                        @method('DELETE')
                                        @can('services.manage')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fe fe-trash me-1"></i>@lang('dashboard.Delete')
                                            </button>
                                        @endcan
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <p class="text-muted mb-0">@lang('dashboard.No ratings yet')</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- FAQs Section --}}
    @if ($service->faqs->count() > 0)
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">@lang('dashboard.FAQs')</h5>
            </div>
            <div class="card-body">
                <div class="accordion" id="faqAccordion">
                    @foreach ($service->faqs as $index => $faq)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faqHeading{{ $index }}">
                                <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#faqCollapse{{ $index }}"
                                    aria-expanded="{{ $index == 0 ? 'true' : 'false' }}"
                                    aria-controls="faqCollapse{{ $index }}">
                                    <strong>{{ $faq->question }}</strong>
                                </button>
                            </h2>
                            <div id="faqCollapse{{ $index }}"
                                class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}"
                                aria-labelledby="faqHeading{{ $index }}" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    {{ $faq->answer }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

@endsection
