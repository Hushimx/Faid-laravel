@extends('layouts.app')
@section('title', __('dashboard.Countries'))
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">@lang('dashboard.Countries List')</h4>
            <a class="modal-effect btn btn-primary" data-bs-effect="effect-scale" data-bs-toggle="modal" href="#createModal">
                @lang('dashboard.Create New Country')
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table text-center table-hover table-bordered table-sm">
                    <thead>
                        <th>#</th>
                        <th>@lang('dashboard.Name')</th>
                        <th>@lang('dashboard.Actions')</th>
                    </thead>
                    <tbody></tbody>
                    @forelse($countries as $country)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $country->name }}</td>
                            <td>
                                <a class="modal-effect btn btn-success btn-sm" data-bs-effect="effect-scale"
                                    data-bs-toggle="modal" href="#updateModal{{ $country->id }}">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <a class="modal-effect btn btn-danger btn-sm" data-bs-effect="effect-scale"
                                    data-bs-toggle="modal" href="#deleteModal{{ $country->id }}">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>

                        <div class="modal fade" id="updateModal{{ $country->id }}">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content modal-content-demo">
                                    <div class="modal-header">
                                        <h6 class="modal-title">@lang('dashboard.Edit Country')</h6><button aria-label="Close"
                                            class="btn-close" data-bs-dismiss="modal"><span
                                                aria-hidden="true">&times;</span></button>
                                    </div>
                                    <form action="{{ route('countries.update', $country->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body">
                                            @foreach (locales() as $locale)
                                                <div class="form-group">
                                                    <label for="name-{{ $locale->code }}">@lang('dashboard.Name') (
                                                        {{ strtoupper($locale->name) }}
                                                        )</label>
                                                    <input type="text" name="name[{{ $locale->code }}]"
                                                        class="form-control" id="name-{{ $locale->code }}"
                                                        placeholder="@lang('dashboard.Name') ( {{ strtoupper($locale->name) }} )"
                                                        value="{{ $country->getTranslation('name', $locale->code) }}"
                                                        {{ $loop->first ? 'required' : '' }}>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary">@lang('dashboard.Save changes')</button>
                                            <button type="button" class="btn btn-light"
                                                data-bs-dismiss="modal">@lang('dashboard.Cancel')</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Delete Modal -->
                        <div class="modal fade" id="deleteModal{{ $country->id }}">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content modal-content-demo">
                                    <div class="modal-header">
                                        <h6 class="modal-title">@lang('dashboard.Delete Country')</h6><button aria-label="Close"
                                            class="btn-close" data-bs-dismiss="modal"><span
                                                aria-hidden="true">&times;</span></button>
                                    </div>
                                    <form action="{{ route('countries.destroy', $country->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <div class="modal-body">
                                            <p>@lang('dashboard.Are you sure you want to delete this country?')</p>
                                            <p class="text-danger">{{ $country->name }}</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-danger">@lang('dashboard.Delete')</button>
                                            <button type="button" class="btn btn-light"
                                                data-bs-dismiss="modal">@lang('dashboard.Cancel')</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    @empty
                        <td colspan="3">
                            <div class="text-center py-5">
                                <h5>@lang('dashboard.No Countries Found')</h5>
                                <p class="text-muted">@lang('dashboard.Start by adding your first country')</p>
                            </div>
                        </td>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($countries->hasPages())
            <div class="card-footer">
                {{ $countries->links() }}
            </div>
        @endif
    </div>

    <!-- Create Country Modal -->
    <div class="modal fade" id="createModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">@lang('dashboard.Create New Country')</h6><button aria-label="Close" class="btn-close"
                        data-bs-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <form action="{{ route('countries.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        @foreach (locales() as $locale)
                            <div class="form-group">
                                <label for="name-{{ $locale->code }}">@lang('dashboard.Name') ( {{ strtoupper($locale->name) }}
                                    )</label>
                                <input type="text" name="name[{{ $locale->code }}]" class="form-control"
                                    id="name-{{ $locale->code }}"
                                    placeholder="@lang('dashboard.Name') ( {{ strtoupper($locale->name) }} )"
                                    {{ $loop->first ? 'required' : '' }}>
                            </div>
                        @endforeach
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">@lang('dashboard.Save changes')</button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">@lang('dashboard.Cancel')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
