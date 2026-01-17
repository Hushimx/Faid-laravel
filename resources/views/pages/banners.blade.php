@extends('layouts.app')

@section('title', __('dashboard.Banners'))

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
@endphp

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-lg-4 col-md-6">
            <div class="card custom-card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <span
                        class="rounded-circle bg-primary-transparent text-primary d-flex align-items-center justify-content-center me-3"
                        style="width: 48px; height: 48px;">
                        <i class="fe fe-image fs-5"></i>
                    </span>
                    <div>
                        <p class="text-muted mb-1">@lang('dashboard.Total Banners')</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['total'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card custom-card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <span
                        class="rounded-circle bg-success-transparent text-success d-flex align-items-center justify-content-center me-3"
                        style="width: 48px; height: 48px;">
                        <i class="fe fe-check-circle fs-5"></i>
                    </span>
                    <div>
                        <p class="text-muted mb-1">@lang('dashboard.Active Banners')</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['active'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <div class="card custom-card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <span
                        class="rounded-circle bg-warning-transparent text-warning d-flex align-items-center justify-content-center me-3"
                        style="width: 48px; height: 48px;">
                        <i class="fe fe-alert-circle fs-5"></i>
                    </span>
                    <div>
                        <p class="text-muted mb-1">@lang('dashboard.Inactive Banners')</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['inactive'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('banners.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <label for="status" class="form-label">@lang('dashboard.Status')</label>
                    <select name="status" id="status" class="form-select">
                        <option value="" @selected(!$filters['status'])>@lang('dashboard.All Status')</option>
                        @foreach (App\Models\Banner::statuses() as $status)
                            <option value="{{ $status }}" @selected($filters['status'] === $status)>
                                @lang('dashboard.' . ucfirst($status))
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="per_page" class="form-label">@lang('dashboard.Per Page')</label>
                    <select name="per_page" id="per_page" class="form-select">
                        @foreach ($perPageOptions as $option)
                            <option value="{{ $option }}" @selected($filters['per_page'] === $option)>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-3 col-lg-3 col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fe fe-filter me-1"></i>@lang('dashboard.Filter')
                    </button>
                    <a href="{{ route('banners.index') }}" class="btn btn-light border">
                        <i class="fe fe-rotate-ccw"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h4 class="card-title mb-0">@lang('dashboard.Banners List')</h4>
                <small class="text-muted">@lang('dashboard.Banners List Subtitle')</small>
            </div>
            @can('banners.create')
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBannerModal">
                    <i class="fe fe-plus me-2"></i>@lang('dashboard.Create New Banner')
                </button>
            @endcan
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-nowrap align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width: 50px;">
                                <i class="fe fe-move"></i>
                            </th>
                            <th>#</th>
                            <th>@lang('dashboard.Image')</th>
                            <th>@lang('dashboard.Link')</th>
                            <th>@lang('dashboard.Status')</th>
                            <th>@lang('dashboard.Order')</th>
                            <th>@lang('dashboard.Created At')</th>
                            <th class="text-end pe-4">@lang('dashboard.Actions')</th>
                        </tr>
                    </thead>
                    <tbody id="banners-list">
                        @forelse ($banners as $banner)
                            <tr data-banner-id="{{ $banner->id }}" class="banner-item">
                                <td class="ps-4">
                                    <i class="fe fe-move text-muted" style="cursor: move;"></i>
                                </td>
                                <td>
                                    {{ $loop->iteration + ($banners->currentPage() - 1) * $banners->perPage() }}
                                </td>
                                <td>
                                    @if ($banner->image)
                                        <div class="overflow-hidden shadow-sm" style="width: 150px; height: 90px; cursor: pointer;"
                                            data-bs-toggle="modal" data-bs-target="#imagePreviewModal"
                                            data-image-url="{{ asset('storage/' . $banner->image) }}"
                                            data-image-alt="Banner">
                                            <img src="{{ asset('storage/' . $banner->image) }}" alt="Banner"
                                                class="img-fluid w-100 h-100" style="object-fit: cover;">
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center justify-content-center bg-light" style="width: 150px; height: 90px;">
                                            <span class="text-muted">
                                                <i class="fe fe-image"></i>
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if ($banner->link)
                                        <a href="{{ $banner->link }}" target="_blank" class="text-primary">
                                            {{ Str::limit($banner->link, 40) }}
                                            <i class="fe fe-external-link ms-1"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span
                                        class="badge {{ $banner->isActive() ? 'bg-success-transparent text-success' : 'bg-danger-transparent text-danger' }}">
                                        @lang('dashboard.' . ucfirst($banner->status))
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info-transparent text-info">{{ $banner->order ?? '-' }}</span>
                                </td>
                                <td>{{ optional($banner->created_at)->format('Y-m-d') }}</td>
                                <td class="text-end">
                                    @can('banners.edit')
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#editBannerModal{{ $banner->id }}">
                                            <i class="fe fe-edit"></i>
                                        </button>
                                    @endcan
                                    @can('banners.delete')
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                            data-bs-target="#deleteBannerModal{{ $banner->id }}">
                                            <i class="fe fe-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editBannerModal{{ $banner->id }}" tabindex="-1"
                                aria-labelledby="editBannerModalLabel{{ $banner->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editBannerModalLabel{{ $banner->id }}">
                                                @lang('dashboard.Edit Banner')
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('banners.update', $banner) }}" method="POST"
                                            enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <div class="row g-3">
                                                    <div class="col-md-12">
                                                        <label class="form-label" for="edit-link-{{ $banner->id }}">
                                                            @lang('dashboard.Link')
                                                        </label>
                                                        <input type="url" class="form-control"
                                                            id="edit-link-{{ $banner->id }}" name="link"
                                                            value="{{ $banner->link }}" placeholder="https://example.com">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="edit-status-{{ $banner->id }}">
                                                            @lang('dashboard.Status')
                                                        </label>
                                                        <select class="form-select" id="edit-status-{{ $banner->id }}"
                                                            name="status" required>
                                                            @foreach (App\Models\Banner::statuses() as $status)
                                                                <option value="{{ $status }}"
                                                                    @selected($banner->status === $status)>
                                                                    @lang('dashboard.' . ucfirst($status))
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="edit-order-{{ $banner->id }}">
                                                            @lang('dashboard.Order')
                                                        </label>
                                                        <input type="number" class="form-control"
                                                            id="edit-order-{{ $banner->id }}" name="order"
                                                            value="{{ $banner->order }}" min="0">
                                                    </div>
                                                </div>

                                                <div class="mt-3">
                                                    <label class="form-label" for="edit-image-{{ $banner->id }}">
                                                        @lang('dashboard.Image')
                                                    </label>
                                                    <div class="border rounded-3 p-3 text-center">
                                                        <div class="rounded overflow-hidden shadow-sm mb-3" style="max-width: 100%;">
                                                            <img src="{{ $banner->image ? asset('storage/' . $banner->image) : asset('assets/images/media/36.png') }}"
                                                                alt="@lang('dashboard.Image')"
                                                                class="img-fluid js-image-preview"
                                                                style="width: 100%; max-height: 300px; object-fit: contain;"
                                                                id="edit-preview-{{ $banner->id }}">
                                                        </div>
                                                        <input class="form-control js-image-input" type="file"
                                                            id="edit-image-{{ $banner->id }}" name="image"
                                                            accept="image/*"
                                                            data-preview="#edit-preview-{{ $banner->id }}">
                                                        <small class="text-muted d-block mt-2">@lang('dashboard.Image helper')</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light"
                                                    data-bs-dismiss="modal">@lang('dashboard.Cancel')</button>
                                                <button type="submit" class="btn btn-primary">
                                                    @lang('dashboard.Save changes')
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Modal -->
                            <div class="modal fade" id="deleteBannerModal{{ $banner->id }}" tabindex="-1"
                                aria-labelledby="deleteBannerModalLabel{{ $banner->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="deleteBannerModalLabel{{ $banner->id }}">
                                                @lang('dashboard.Delete Banner')
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('banners.destroy', $banner) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <div class="modal-body">
                                                <p class="mb-3">@lang('dashboard.Banner delete confirmation')</p>
                                                @if ($banner->image)
                                                    <div class="text-center mb-3">
                                                        <img src="{{ asset('storage/' . $banner->image) }}" alt="Banner"
                                                            class="img-fluid rounded shadow-sm" style="max-width: 100%; max-height: 300px; object-fit: contain;">
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light"
                                                    data-bs-dismiss="modal">@lang('dashboard.Cancel')</button>
                                                <button type="submit" class="btn btn-danger">
                                                    @lang('dashboard.Delete')
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="py-4">
                                        <h5 class="mb-2">@lang('dashboard.No Banners Found')</h5>
                                        <p class="text-muted mb-3">@lang('dashboard.Start by adding your first banner')</p>
                                        @can('banners.create')
                                            <button class="btn btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#createBannerModal">
                                                <i class="fe fe-plus me-2"></i>@lang('dashboard.Create New Banner')
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($banners->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-muted small">
                    @lang('dashboard.Showing results', ['from' => $banners->firstItem(), 'to' => $banners->lastItem(), 'total' => $banners->total()])
                </div>
                {{ $banners->links() }}
            </div>
        @endif
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createBannerModal" tabindex="-1" aria-labelledby="createBannerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createBannerModalLabel">@lang('dashboard.Create New Banner')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('banners.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label" for="create-link">
                                    @lang('dashboard.Link')
                                </label>
                                <input type="url" class="form-control" id="create-link" name="link"
                                    placeholder="https://example.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="create-status">
                                    @lang('dashboard.Status')
                                </label>
                                <select class="form-select" id="create-status" name="status" required>
                                    @foreach (App\Models\Banner::statuses() as $status)
                                        <option value="{{ $status }}"
                                            @selected($status === App\Models\Banner::STATUS_ACTIVE)>
                                            @lang('dashboard.' . ucfirst($status))
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="create-order">
                                    @lang('dashboard.Order')
                                </label>
                                <input type="number" class="form-control" id="create-order" name="order" min="0">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label" for="create-image">
                                @lang('dashboard.Image') <span class="text-danger">*</span>
                            </label>
                            <div class="border rounded-3 p-3 text-center">
                                <div class="rounded overflow-hidden shadow-sm mb-3" style="max-width: 100%;">
                                    <img src="{{ asset('assets/images/media/36.png') }}" alt="@lang('dashboard.Image')"
                                        class="img-fluid js-image-preview"
                                        style="width: 100%; max-height: 300px; object-fit: contain;"
                                        id="create-preview">
                                </div>
                                <input class="form-control js-image-input @error('image') is-invalid @enderror" type="file" id="create-image"
                                    name="image" accept="image/*" data-preview="#create-preview" required>
                                @error('image')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted d-block mt-2">@lang('dashboard.Image helper')</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">@lang('dashboard.Cancel')</button>
                        <button type="submit" class="btn btn-primary">@lang('dashboard.Save changes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <!-- SortableJS -->
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

        <script>
            // Reopen modal if there are validation errors
            @if ($errors->hasAny(['image', 'status', 'link', 'order']))
                $(document).ready(function() {
                    setTimeout(function() {
                        $('#createBannerModal').modal('show');
                    }, 100);
                });
            @endif

            document.addEventListener('DOMContentLoaded', function() {
                // Image preview functionality
                document.querySelectorAll('.js-image-input').forEach(input => {
                    input.addEventListener('change', function(event) {
                        const previewSelector = event.target.getAttribute('data-preview');
                        if (!previewSelector) {
                            return;
                        }

                        const previewElement = document.querySelector(previewSelector);
                        if (!previewElement) {
                            return;
                        }

                        const file = event.target.files && event.target.files[0];
                        if (!file) {
                            return;
                        }

                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewElement.setAttribute('src', e.target.result);
                        };
                        reader.readAsDataURL(file);
                    });
                });

                // Initialize Sortable
                const bannersList = document.getElementById('banners-list');
                if (bannersList) {
                    const sortable = new Sortable(bannersList, {
                        handle: '.fe-move',
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        dragClass: 'sortable-drag',
                        onEnd: function(evt) {
                            // Get all banner IDs in the new order
                            const bannerItems = bannersList.querySelectorAll('.banner-item');
                            const order = Array.from(bannerItems).map(item => {
                                return item.getAttribute('data-banner-id');
                            });

                            // Send AJAX request to update order
                            fetch('{{ route("banners.update-order") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    order: order
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success && data.changed) {
                                    // Show success message only if order changed
                                    if (typeof Swal !== 'undefined') {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Success',
                                            text: data.message,
                                            timer: 2000,
                                            showConfirmButton: false
                                        });
                                    }
                                    // Reload page to reflect new order numbers
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 1000);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Failed to update banner order'
                                    });
                                }
                            });
                        }
                    });
                }
            });
        </script>

        <style>
            .sortable-ghost {
                opacity: 0.4;
            }

            .sortable-chosen {
                cursor: move;
            }

            .sortable-drag {
                opacity: 0.8;
            }

            .banner-item:hover {
                background-color: #f8f9fa;
            }
        </style>
    @endpush

    <!-- Image Preview Modal -->
    <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imagePreviewModalLabel">@lang('dashboard.Image Preview')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img id="previewImage" src="" alt="" class="img-fluid" style="max-height: 80vh; width: 100%; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>

    <script>
        // Handle image preview modal
        document.addEventListener('DOMContentLoaded', function() {
            const imagePreviewModal = document.getElementById('imagePreviewModal');
            if (imagePreviewModal) {
                imagePreviewModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const imageUrl = button.getAttribute('data-image-url');
                    const imageAlt = button.getAttribute('data-image-alt') || 'Image';
                    const previewImage = document.getElementById('previewImage');
                    if (previewImage) {
                        previewImage.src = imageUrl;
                        previewImage.alt = imageAlt;
                    }
                });
            }
        });
    </script>
@endsection
