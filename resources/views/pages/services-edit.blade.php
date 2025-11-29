@extends('layouts.app')

@section('title', __('dashboard.Edit Service'))

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">@lang('dashboard.Edit Service')</h4>
            <a href="{{ route('services.show', $service) }}" class="btn btn-light">
                <i class="fe fe-arrow-left me-1"></i>@lang('dashboard.Back')
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('services.update', $service) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">@lang('dashboard.Title')</label>
                        @foreach (locales() as $locale)
                            <div class="mb-3">
                                <input type="text" class="form-control" name="title[{{ $locale->code }}]"
                                    value="{{ old("title.{$locale->code}", $service->getTranslation('title', $locale->code, false)) }}"
                                    placeholder="@lang('dashboard.Title') ({{ strtoupper($locale->name) }})"
                                    {{ $loop->first ? 'required' : '' }}>
                            </div>
                        @endforeach
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">@lang('dashboard.Category')</label>
                        <select name="category_id" class="form-select" required>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $service->category_id) === $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">@lang('dashboard.Description')</label>
                        @foreach (locales() as $locale)
                            <div class="mb-3">
                                <textarea class="form-control" rows="3" name="description[{{ $locale->code }}]"
                                    placeholder="@lang('dashboard.Description') ({{ strtoupper($locale->name) }})">{{ old("description.{$locale->code}", $service->getTranslation('description', $locale->code, false)) }}</textarea>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">@lang('dashboard.Price Type')</label>
                        <select name="price_type" class="form-select" required>
                            @foreach (App\Models\Service::priceTypes() as $type)
                                <option value="{{ $type }}" @selected(old('price_type', $service->price_type) === $type)>
                                    @lang('dashboard.' . ucfirst($type))
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">@lang('dashboard.Price')</label>
                        <input type="number" step="0.01" class="form-control" name="price"
                            value="{{ old('price', $service->price) }}" placeholder="0.00">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">@lang('dashboard.Status')</label>
                        <select name="status" class="form-select" required>
                            @foreach (App\Models\Service::vendorStatuses() as $status)
                                <option value="{{ $status }}" @selected(old('status', $service->status) === $status)>
                                    @lang('dashboard.' . ucfirst($status))
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-12">
                        <label class="form-label">@lang('dashboard.Attributes') (JSON)</label>
                        <textarea class="form-control" rows="5" name="attributes" placeholder='{"key": "value"}'>{{ old('attributes', $service->attributes ? json_encode($service->attributes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                        <small class="text-muted">@lang('dashboard.Attributes helper')</small>
                    </div>
                </div>

                {{-- FAQs Section --}}
                <div class="row g-3 mt-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">@lang('dashboard.FAQs')</h5>
                                <button type="button" class="btn btn-sm btn-primary" onclick="addFaq()">
                                    <i class="fe fe-plus me-1"></i>@lang('dashboard.Add FAQ')
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="faqsContainer">
                                    @foreach ($service->faqs as $index => $faq)
                                        <div class="faq-item card mb-3" data-index="{{ $index }}">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="mb-0">FAQ #<span
                                                            class="faq-number">{{ $index + 1 }}</span></h6>
                                                    <div>
                                                        <input type="number" name="faqs[{{ $index }}][order]"
                                                            value="{{ old("faqs.{$index}.order", $faq->order) }}"
                                                            class="form-control form-control-sm d-inline-block"
                                                            style="width: 80px;" placeholder="@lang('dashboard.Order')">
                                                        <button type="button" class="btn btn-sm btn-danger ms-2"
                                                            onclick="removeFaq(this)">
                                                            <i class="fe fe-trash"></i> @lang('dashboard.Remove FAQ')
                                                        </button>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="faqs[{{ $index }}][id]"
                                                    value="{{ $faq->id }}">
                                                <input type="hidden" name="faqs[{{ $index }}][delete]"
                                                    value="0" class="delete-flag">

                                                <div class="mb-3">
                                                    <label class="form-label">@lang('dashboard.Question')</label>
                                                    @foreach (locales() as $locale)
                                                        <div class="mb-2">
                                                            <input type="text" class="form-control"
                                                                name="faqs[{{ $index }}][question][{{ $locale->code }}]"
                                                                value="{{ old("faqs.{$index}.question.{$locale->code}", $faq->getTranslation('question', $locale->code, false)) }}"
                                                                placeholder="@lang('dashboard.Question') ({{ strtoupper($locale->name) }})">
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <div class="mb-0">
                                                    <label class="form-label">@lang('dashboard.Answer')</label>
                                                    @foreach (locales() as $locale)
                                                        <div class="mb-2">
                                                            <textarea class="form-control" rows="2" name="faqs[{{ $index }}][answer][{{ $locale->code }}]"
                                                                placeholder="@lang('dashboard.Answer') ({{ strtoupper($locale->name) }})">{{ old("faqs.{$index}.answer.{$locale->code}", $faq->getTranslation('answer', $locale->code, false)) }}</textarea>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @if ($service->faqs->count() == 0)
                                    <p class="text-muted text-center mb-0">@lang('dashboard.No FAQs found')</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fe fe-save me-1"></i>@lang('dashboard.Save changes')
                    </button>
                    <a href="{{ route('services.show', $service) }}" class="btn btn-light">
                        @lang('dashboard.Cancel')
                    </a>
                </div>
            </form>
        </div>
    </div>

@section('scripts')
    <script>
        let faqIndex = {{ $service->faqs->count() }};

        function addFaq() {
            const container = document.getElementById('faqsContainer');
            const locales = @json(collect(locales())->map(fn($l) => ['code' => $l->code, 'name' => strtoupper($l->name)])->toArray());

            let questionInputs = '';
            let answerInputs = '';

            locales.forEach(locale => {
                questionInputs += `
            <div class="mb-2">
                <input type="text" class="form-control"
                    name="faqs[${faqIndex}][question][${locale.code}]"
                    placeholder="@lang('dashboard.Question') (${locale.name})">
            </div>
        `;

                answerInputs += `
            <div class="mb-2">
                <textarea class="form-control" rows="2"
                    name="faqs[${faqIndex}][answer][${locale.code}]"
                    placeholder="@lang('dashboard.Answer') (${locale.name})"></textarea>
            </div>
        `;
            });

            const faqHtml = `
        <div class="faq-item card mb-3" data-index="${faqIndex}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">FAQ #<span class="faq-number">${faqIndex + 1}</span></h6>
                    <div>
                        <input type="number" name="faqs[${faqIndex}][order]"
                            value="${faqIndex}"
                            class="form-control form-control-sm d-inline-block"
                            style="width: 80px;" placeholder="@lang('dashboard.Order')">
                        <button type="button" class="btn btn-sm btn-danger ms-2" onclick="removeFaq(this)">
                            <i class="fe fe-trash"></i> @lang('dashboard.Remove FAQ')
                        </button>
                    </div>
                </div>
                <input type="hidden" name="faqs[${faqIndex}][delete]" value="0" class="delete-flag">

                <div class="mb-3">
                    <label class="form-label">@lang('dashboard.Question')</label>
                    ${questionInputs}
                </div>

                <div class="mb-0">
                    <label class="form-label">@lang('dashboard.Answer')</label>
                    ${answerInputs}
                </div>
            </div>
        </div>
    `;

            container.insertAdjacentHTML('beforeend', faqHtml);
            faqIndex++;
            updateFaqNumbers();
        }

        function removeFaq(button) {
            const faqItem = button.closest('.faq-item');
            const deleteFlag = faqItem.querySelector('.delete-flag');

            if (deleteFlag) {
                // Mark existing FAQ for deletion
                deleteFlag.value = '1';
                faqItem.style.display = 'none';
            } else {
                // Remove new FAQ that hasn't been saved yet
                faqItem.remove();
            }

            updateFaqNumbers();
        }

        function updateFaqNumbers() {
            const faqItems = document.querySelectorAll('.faq-item:not([style*="display: none"])');
            faqItems.forEach((item, index) => {
                const numberSpan = item.querySelector('.faq-number');
                if (numberSpan) {
                    numberSpan.textContent = index + 1;
                }
            });
        }
    </script>
@endsection
@endsection
