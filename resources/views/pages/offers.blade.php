@extends('layouts.app')
@section('title', __('dashboard.Offers'))
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">@lang('dashboard.Offers')</h4>
            <button class="btn btn-primary" data-bs-toggle="modal"
                data-bs-target="#createOfferModal">@lang('dashboard.Create New Offer')</button>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach ($offers as $offer)
                    <div class="col-md-4">
                        <div class="card">
                            <img src="{{ Storage::url($offer->image) }}" class="card-img-top"
                                style="height:200px;object-fit:cover;" alt="Offer">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <span
                                    class="badge {{ $offer->status == App\Models\Offer::STATUS_ACTIVE ? 'bg-success' : 'bg-secondary' }}">@lang('dashboard.' . ucfirst($offer->status))</span>
                                <div>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#editOfferModal{{ $offer->id }}">@lang('dashboard.Edit')</button>
                                    <form action="{{ route('offers.destroy', $offer) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">@lang('dashboard.Delete')</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editOfferModal{{ $offer->id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">@lang('dashboard.Edit Offer')</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('offers.update', $offer) }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3 text-center">
                                            <img src="{{ Storage::url($offer->image) }}"
                                                id="edit-preview-{{ $offer->id }}"
                                                style="width:100%;height:200px;object-fit:cover;" class="mb-3">
                                            <input type="file" name="image" class="form-control"
                                                data-preview="#edit-preview-{{ $offer->id }}">
                                        </div>
                                        <div class="mb-3">
                                            <label>@lang('dashboard.Status')</label>
                                            <select name="status" class="form-select">
                                                @foreach (App\Models\Offer::statuses() as $status)
                                                    <option value="{{ $status }}" @selected($offer->status === $status)>
                                                        @lang('dashboard.' . ucfirst($status))</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light"
                                            data-bs-dismiss="modal">@lang('dashboard.Cancel')</button>
                                        <button type="submit" class="btn btn-primary">@lang('dashboard.Save changes')</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card-footer">
            {{ $offers->links() }}
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createOfferModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('dashboard.Create New Offer')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('offers.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3 text-center">
                            <img src="{{ asset('assets/images/media/36.png') }}" id="create-preview"
                                style="width:100%;height:200px;object-fit:cover;" class="mb-3">
                            <input type="file" name="image" class="form-control" data-preview="#create-preview"
                                required>
                        </div>
                        <div class="mb-3">
                            <label>@lang('dashboard.Status')</label>
                            <select name="status" class="form-select">
                                @foreach (App\Models\Offer::statuses() as $status)
                                    <option value="{{ $status }}" @selected($status === App\Models\Offer::STATUS_ACTIVE)>@lang('dashboard.' . ucfirst($status))
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">@lang('dashboard.Cancel')</button>
                        <button type="submit" class="btn btn-primary">@lang('dashboard.Create')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('change', function(e) {
                if (!e.target.matches('input[type=file]')) return;
                const preview = document.querySelector(e.target.dataset.preview);
                if (!preview) return;
                const file = e.target.files && e.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = function(ev) {
                    preview.src = ev.target.result;
                };
                reader.readAsDataURL(file);
            });
        </script>
    @endpush

@endsection
