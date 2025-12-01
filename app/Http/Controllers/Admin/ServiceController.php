<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
  /**
   * Display a listing of the services with filters & stats.
   */
  public function index(Request $request): View
  {
    $this->authorize('services.view');

    $filters = [
      'search' => $request->string('search')->toString(),
      'status' => $request->string('status')->toString(),
      'admin_status' => $request->string('admin_status')->toString(),
      'vendor_id' => $request->integer('vendor_id'),
      'category_id' => $request->integer('category_id'),
      'per_page' => $request->integer('per_page', 15),
    ];

    $perPageOptions = [15, 30, 50, 100];
    if (!in_array($filters['per_page'], $perPageOptions, true)) {
      $filters['per_page'] = 15;
    }

    $servicesQuery = Service::query()
      ->with(['category', 'vendor'])
      ->withCount('images');

    if ($filters['search']) {
      $locale = app()->getLocale();
      $search = '%' . trim($filters['search']) . '%';
      $servicesQuery->where(function ($query) use ($search, $locale) {
        $query->where("title->{$locale}", 'like', $search)
          ->orWhere("description->{$locale}", 'like', $search);
      });
    }

    if ($filters['status'] && in_array($filters['status'], Service::vendorStatuses(), true)) {
      $servicesQuery->where('status', $filters['status']);
    }

    if ($filters['admin_status'] === 'suspended') {
      $servicesQuery->where('admin_status', Service::ADMIN_STATUS_SUSPENDED);
    } elseif ($filters['admin_status'] === 'active') {
      $servicesQuery->whereNull('admin_status');
    }

    if ($filters['vendor_id']) {
      $servicesQuery->where('vendor_id', $filters['vendor_id']);
    }

    if ($filters['category_id']) {
      $servicesQuery->where('category_id', $filters['category_id']);
    }

    $services = $servicesQuery->latest()->paginate($filters['per_page'])->withQueryString();

    $stats = [
      'total' => Service::count(),
      'active' => Service::where('status', Service::STATUS_ACTIVE)->whereNull('admin_status')->count(),
      'suspended' => Service::where('admin_status', Service::ADMIN_STATUS_SUSPENDED)->count(),
      'pending' => Service::where('status', Service::STATUS_PENDING)->count(),
    ];

    $vendors = \App\Models\User::where('type', 'vendor')->get(['id', 'first_name', 'last_name']);
    $categories = \App\Models\Category::all(['id', 'name']);

    return view('pages.services', compact('services', 'stats', 'filters', 'perPageOptions', 'vendors', 'categories'));
  }

  /**
   * Display the specified service.
   */
  public function show(Service $service): View
  {
    $this->authorize('services.view');

    $service->load(['category', 'vendor', 'images', 'videos', 'reviews.user', 'faqs']);
    return view('pages.services-show', compact('service'));
  }

  /**
   * Show the form for editing the specified service.
   */
  public function edit(Service $service): View
  {
    $this->authorize('services.edit');

    $service->load(['category', 'vendor', 'images', 'videos', 'faqs']);
    $categories = \App\Models\Category::all(['id', 'name']);
    return view('pages.services-edit', compact('service', 'categories'));
  }

  /**
   * Update the specified service.
   */
  public function update(Request $request, Service $service): RedirectResponse
  {
    $this->authorize('services.edit');

    $validated = $request->validate([
      'category_id' => ['required', 'exists:categories,id'],
      'title' => ['required', 'array'],
      'title.en' => ['required', 'string', 'max:255'],
      'title.*' => ['nullable', 'string', 'max:255'],
      'description' => ['nullable', 'array'],
      'description.*' => ['nullable', 'string', 'max:5000'],
      'price_type' => ['required', Rule::in(Service::priceTypes())],
      'price' => ['nullable', 'required_if:price_type,fixed', 'numeric', 'min:0', 'max:999999.99'],
      'address' => ['nullable', 'string', 'max:1000'],
      'city' => ['nullable', 'string', 'max:100'],
      'status' => ['required', Rule::in(Service::vendorStatuses())],
      'attributes' => ['nullable'],
      'faqs' => ['nullable', 'array'],
      'faqs.*.id' => ['nullable', 'exists:service_faqs,id'],
      'faqs.*.question' => ['required', 'array'],
      'faqs.*.question.*' => ['nullable', 'string', 'max:500'],
      'faqs.*.answer' => ['required', 'array'],
      'faqs.*.answer.*' => ['nullable', 'string', 'max:2000'],
      'faqs.*.order' => ['nullable', 'integer', 'min:0'],
      'faqs.*.delete' => ['nullable', 'boolean'],
    ]);

    $service->category_id = $validated['category_id'];
    $service->title = normalize_translations($request->input('title'));
    $service->description = $this->normalizeOptionalTranslations($request->input('description'));
    $service->price_type = $validated['price_type'];
    $service->price = $validated['price'] ?? null;
    $service->address = $validated['address'] ?? null;
    $service->city = $validated['city'] ?? null;
    $service->status = $validated['status'];

    // Handle attributes - can be array or JSON string
    $attributes = $validated['attributes'] ?? null;
    if (is_string($attributes)) {
      $decoded = json_decode($attributes, true);
      $service->attributes = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    } else {
      $service->attributes = $attributes;
    }

    if ($service->status === Service::STATUS_ACTIVE && !$service->published_at) {
      $service->published_at = now();
    } elseif ($service->status !== Service::STATUS_ACTIVE) {
      $service->published_at = null;
    }

    $service->save();

    // Handle FAQs
    if ($request->has('faqs')) {
      $faqIds = [];
      foreach ($request->input('faqs', []) as $index => $faqData) {
        // Skip if marked for deletion
        if (!empty($faqData['delete'])) {
          if (!empty($faqData['id'])) {
            \App\Models\ServiceFaq::where('id', $faqData['id'])
              ->where('service_id', $service->id)
              ->delete();
          }
          continue;
        }

        // Normalize translations
        $question = normalize_translations($faqData['question']);
        $answer = normalize_translations($faqData['answer']);
        $order = $faqData['order'] ?? $index;

        if (!empty($faqData['id'])) {
          // Update existing FAQ
          $faq = \App\Models\ServiceFaq::where('id', $faqData['id'])
            ->where('service_id', $service->id)
            ->first();
          if ($faq) {
            $faq->question = $question;
            $faq->answer = $answer;
            $faq->order = $order;
            $faq->save();
            $faqIds[] = $faq->id;
          }
        } else {
          // Create new FAQ
          $faq = \App\Models\ServiceFaq::create([
            'service_id' => $service->id,
            'question' => $question,
            'answer' => $answer,
            'order' => $order,
          ]);
          $faqIds[] = $faq->id;
        }
      }
    }

    return redirect()->route('services.show', $service)
      ->with('success', __('dashboard.Service updated successfully'));
  }

  /**
   * Update the service admin status.
   */
  public function updateStatus(Request $request, Service $service): RedirectResponse
  {
    $this->authorize('services.manage');

    $validated = $request->validate([
      'admin_status' => ['nullable', Rule::in([null, Service::ADMIN_STATUS_SUSPENDED])],
    ]);

    $service->admin_status = $validated['admin_status'] ?? null;
    $service->save();

    $message = $service->admin_status === Service::ADMIN_STATUS_SUSPENDED
      ? __('dashboard.Service suspended successfully')
      : __('dashboard.Service activated successfully');

    return redirect()->back()->with('success', $message);
  }

  /**
   * Remove the specified service.
   */
  public function destroy(Service $service): RedirectResponse
  {
    $this->authorize('services.delete');

    // Delete associated media files
    foreach ($service->media as $media) {
      if ($media->path && Storage::exists($media->path)) {
        Storage::delete($media->path);
      }
    }

    $service->delete();

    return redirect()->back()->with('success', __('dashboard.Service deleted successfully'));
  }

  /**
   * Normalize optional translation array.
   */
  protected function normalizeOptionalTranslations(?array $input): ?array
  {
    if (empty($input)) {
      return null;
    }

    $hasContent = collect($input)->filter(fn($value) => filled($value))->isNotEmpty();

    return $hasContent ? normalize_translations($input) : null;
  }

  /**
   * Delete a review from a service.
   */
  public function destroyReview(Service $service, \App\Models\Review $review)
  {
    $this->authorize('services.manage');

    // Verify the review belongs to this service
    if ($review->service_id !== $service->id) {
      return redirect()->back()->with('error', 'Review not found');
    }

    $review->delete();

    return redirect()->back()->with('success', 'Review deleted successfully');
  }
}
