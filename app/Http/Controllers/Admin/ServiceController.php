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
    $service->load(['category', 'vendor', 'images', 'videos', 'reviews.user']);
    return view('pages.services-show', compact('service'));
  }

  /**
   * Show the form for editing the specified service.
   */
  public function edit(Service $service): View
  {
    $service->load(['category', 'vendor', 'images', 'videos']);
    $categories = \App\Models\Category::all(['id', 'name']);
    return view('pages.services-edit', compact('service', 'categories'));
  }

  /**
   * Update the specified service.
   */
  public function update(Request $request, Service $service): RedirectResponse
  {
    $validated = $request->validate([
      'category_id' => ['required', 'exists:categories,id'],
      'title' => ['required', 'array'],
      'title.en' => ['required', 'string', 'max:255'],
      'title.*' => ['nullable', 'string', 'max:255'],
      'description' => ['nullable', 'array'],
      'description.*' => ['nullable', 'string', 'max:5000'],
      'price_type' => ['required', Rule::in(Service::priceTypes())],
      'price' => ['nullable', 'required_if:price_type,fixed', 'numeric', 'min:0', 'max:999999.99'],
      'status' => ['required', Rule::in(Service::vendorStatuses())],
      'attributes' => ['nullable'],
    ]);

    $service->category_id = $validated['category_id'];
    $service->title = normalize_translations($request->input('title'));
    $service->description = $this->normalizeOptionalTranslations($request->input('description'));
    $service->price_type = $validated['price_type'];
    $service->price = $validated['price'] ?? null;
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

    return redirect()->route('services.show', $service)
      ->with('success', __('dashboard.Service updated successfully'));
  }

  /**
   * Update the service admin status.
   */
  public function updateStatus(Request $request, Service $service): RedirectResponse
  {
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
    // Verify the review belongs to this service
    if ($review->service_id !== $service->id) {
      return redirect()->back()->with('error', 'Review not found');
    }

    $review->delete();

    return redirect()->back()->with('success', 'Review deleted successfully');
  }
}
