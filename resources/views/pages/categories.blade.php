@extends('layouts.app')

@section('title', __('dashboard.Categories'))

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
                        <i class="fe fe-layers fs-5"></i>
                    </span>
                    <div>
                        <p class="text-muted mb-1">@lang('dashboard.Total Categories')</p>
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
                        <p class="text-muted mb-1">@lang('dashboard.Active Categories')</p>
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
                        <p class="text-muted mb-1">@lang('dashboard.Inactive Categories')</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['inactive'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('categories.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-xl-4 col-lg-6 col-md-6">
                    <label for="search" class="form-label">@lang('dashboard.Search')</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="fe fe-search"></i></span>
                        <input type="text" class="form-control" id="search" name="search"
                            placeholder="@lang('dashboard.Search categories placeholder')" value="{{ $filters['search'] }}">
                    </div>
                </div>

                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="status" class="form-label">@lang('dashboard.Status')</label>
                    <select name="status" id="status" class="form-select">
                        <option value="" @selected(!$filters['status'])>@lang('dashboard.All Status')</option>
                        @foreach (App\Models\Category::statuses() as $status)
                            <option value="{{ $status }}" @selected($filters['status'] === $status)>
                                @lang('dashboard.' . ucfirst($status))
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-3 col-lg-3 col-md-6">
                    <!-- Parent category removed - flat categories only -->
                </div>

                <div class="col-xl-1 col-lg-2 col-md-3">
                    <label for="per_page" class="form-label">@lang('dashboard.Per Page')</label>
                    <select name="per_page" id="per_page" class="form-select">
                        @foreach ($perPageOptions as $option)
                            <option value="{{ $option }}" @selected($filters['per_page'] === $option)>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-2 col-lg-2 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fe fe-filter me-1"></i>@lang('dashboard.Filter')
                    </button>
                    <a href="{{ route('categories.index') }}" class="btn btn-light border">
                        <i class="fe fe-rotate-ccw"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h4 class="card-title mb-0">@lang('dashboard.Categories List')</h4>
                <small class="text-muted">@lang('dashboard.Categories List Subtitle')</small>
            </div>
            @can('categories.create')
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                    <i class="fe fe-plus me-2"></i>@lang('dashboard.Create New Category')
                </button>
            @endcan
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-nowrap align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">#</th>
                            <th>@lang('dashboard.Image')</th>
                            <th>@lang('dashboard.Name')</th>
                            <!-- Parent Category column removed -->
                            <th>@lang('dashboard.Status')</th>
                            <th>@lang('dashboard.Created At')</th>
                            <th class="text-end pe-4">@lang('dashboard.Actions')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td class="ps-4">
                                    {{ $loop->iteration + ($categories->currentPage() - 1) * $categories->perPage() }}</td>
                                <td>
                                    <div class="avatar avatar-md rounded-3 overflow-hidden">
                                        @if ($category->image)
                                            <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}"
                                                class="img-fluid">
                                        @else
                                            <span
                                                class="avatar-initial bg-light text-muted fw-semibold">{{ mb_substr($category->name, 0, 1) }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $category->name }}</div>
                                    @php
                                        $description = $category->getTranslation(
                                            'description',
                                            app()->getLocale(),
                                            false,
                                        );
                                    @endphp
                                    @if ($description)
                                        <div class="text-muted small">{{ Str::limit($description, 80) }}</div>
                                    @endif
                                </td>
                                <!-- Parent column removed - flat categories only -->
                                <td>
                                    <span
                                        class="badge {{ $category->isActive() ? 'bg-success-transparent text-success' : 'bg-danger-transparent text-danger' }}">
                                        @lang('dashboard.' . ucfirst($category->status))
                                    </span>
                                </td>
                                <td>{{ optional($category->created_at)->format('Y-m-d') }}</td>
                                <td class="">
                                    @can('categories.edit')
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#editCategoryModal{{ $category->id }}">
                                            <i class="fe fe-edit"></i>
                                        </button>
                                    @endcan
                                    @can('categories.delete')
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                            data-bs-target="#deleteCategoryModal{{ $category->id }}">
                                            <i class="fe fe-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>

                            <div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1"
                                aria-labelledby="editCategoryModalLabel{{ $category->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editCategoryModalLabel{{ $category->id }}">
                                                @lang('dashboard.Edit Category')
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('categories.update', $category) }}" method="POST"
                                            enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        @foreach (locales() as $locale)
                                                            <div class="mb-3">
                                                                <label class="form-label"
                                                                    for="edit-name-{{ $category->id }}-{{ $locale->code }}">
                                                                    @lang('dashboard.Name')
                                                                    ({{ strtoupper($locale->name) }})
                                                                </label>
                                                                <input type="text" class="form-control"
                                                                    id="edit-name-{{ $category->id }}-{{ $locale->code }}"
                                                                    name="name[{{ $locale->code }}]"
                                                                    value="{{ $category->getTranslation('name', $locale->code) }}"
                                                                    {{ $loop->first ? 'required' : '' }}>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <div class="col-md-6">
                                                        @foreach (locales() as $locale)
                                                            <div class="mb-3">
                                                                <label class="form-label"
                                                                    for="edit-description-{{ $category->id }}-{{ $locale->code }}">
                                                                    @lang('dashboard.Description')
                                                                    ({{ strtoupper($locale->name) }})
                                                                </label>
                                                                <textarea class="form-control" rows="3" id="edit-description-{{ $category->id }}-{{ $locale->code }}"
                                                                    name="description[{{ $locale->code }}]">{{ $category->getTranslation('description', $locale->code, false) }}</textarea>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>

                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="edit-status-{{ $category->id }}">
                                                            @lang('dashboard.Status')
                                                        </label>
                                                        <select class="form-select" id="edit-status-{{ $category->id }}"
                                                            name="status" required>
                                                            @foreach (App\Models\Category::statuses() as $status)
                                                                <option value="{{ $status }}"
                                                                    @selected($category->status === $status)>
                                                                    @lang('dashboard.' . ucfirst($status))
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="mt-3">
                                                    <label class="form-label" for="edit-image-{{ $category->id }}">
                                                        @lang('dashboard.Image')
                                                    </label>
                                                    <div class="border rounded-3 p-3 text-center">
                                                        <div
                                                            class="d-inline-block rounded-3 overflow-hidden shadow-sm mb-3">
                                                            <img src="{{ $category->image ? Storage::url($category->image) : asset('assets/images/media/36.png') }}"
                                                                alt="@lang('dashboard.Image')"
                                                                class="img-fluid object-fit-cover js-image-preview"
                                                                style="height: 180px;"
                                                                id="edit-preview-{{ $category->id }}">
                                                        </div>
                                                        <input class="form-control js-image-input" type="file"
                                                            id="edit-image-{{ $category->id }}" name="image"
                                                            accept="image/*"
                                                            data-preview="#edit-preview-{{ $category->id }}">
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

                            <div class="modal fade" id="deleteCategoryModal{{ $category->id }}" tabindex="-1"
                                aria-labelledby="deleteCategoryModalLabel{{ $category->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="deleteCategoryModalLabel{{ $category->id }}">
                                                @lang('dashboard.Delete Category')
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('categories.destroy', $category) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <div class="modal-body">
                                                <p class="mb-1">@lang('dashboard.Category delete confirmation')</p>
                                                <p class="text-danger fw-semibold mb-0">{{ $category->name }}</p>
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
                                <td colspan="7" class="text-center py-5">
                                    <div class="py-4">
                                        <h5 class="mb-2">@lang('dashboard.No Categories Found')</h5>
                                        <p class="text-muted mb-3">@lang('dashboard.Start by adding your first category')</p>
                                        @can('categories.create')
                                            <button class="btn btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#createCategoryModal">
                                                <i class="fe fe-plus me-2"></i>@lang('dashboard.Create New Category')
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
        @if ($categories->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-muted small">
                    @lang('dashboard.Showing results', ['from' => $categories->firstItem(), 'to' => $categories->lastItem(), 'total' => $categories->total()])
                </div>
                {{ $categories->links() }}
            </div>
        @endif
    </div>

    <div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createCategoryModalLabel">@lang('dashboard.Create New Category')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('categories.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                @foreach (locales() as $locale)
                                    <div class="mb-3">
                                        <label class="form-label" for="create-name-{{ $locale->code }}">
                                            @lang('dashboard.Name') ({{ strtoupper($locale->name) }})
                                        </label>
                                        <input type="text" class="form-control" id="create-name-{{ $locale->code }}"
                                            name="name[{{ $locale->code }}]" {{ $loop->first ? 'required' : '' }}>
                                    </div>
                                @endforeach
                            </div>
                            <div class="col-md-6">
                                @foreach (locales() as $locale)
                                    <div class="mb-3">
                                        <label class="form-label" for="create-description-{{ $locale->code }}">
                                            @lang('dashboard.Description') ({{ strtoupper($locale->name) }})
                                        </label>
                                        <textarea class="form-control" rows="3" id="create-description-{{ $locale->code }}"
                                            name="description[{{ $locale->code }}]"></textarea>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="create-status">@lang('dashboard.Status')</label>
                                <select class="form-select" id="create-status" name="status" required>
                                    @foreach (App\Models\Category::statuses() as $status)
                                        <option value="{{ $status }}" @selected($status === App\Models\Category::STATUS_ACTIVE)>
                                            @lang('dashboard.' . ucfirst($status))
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label" for="create-image">@lang('dashboard.Image')</label>
                            <div class="border rounded-3 p-3 text-center">
                                <div class="d-inline-block rounded-3 overflow-hidden shadow-sm mb-3">
                                    <img src="{{ asset('assets/images/media/36.png') }}" alt="@lang('dashboard.Image')"
                                        class="img-fluid object-fit-cover js-image-preview" style="height: 180px;"
                                        id="create-preview">
                                </div>
                                <input class="form-control js-image-input" type="file" id="create-image"
                                    name="image" accept="image/*" data-preview="#create-preview">
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const select2Elements = document.querySelectorAll('.select2');
            select2Elements.forEach(element => {
                $(element).select2({
                    dropdownParent: $(element).closest('.modal').length ? $(element).closest(
                        '.modal') : $(element).parent()
                });
            });

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
        });
    </script>
@endsection
