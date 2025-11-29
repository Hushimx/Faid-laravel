<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Models\Media;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    /**
     * Display a listing of services (public or vendor's own).
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $isVendor = $user && $user->type === 'vendor';

        $filters = [
            'search' => $request->string('search')->toString(),
            'category_id' => $request->integer('category_id') ?: null,
            'vendor_id' => $request->integer('vendor_id') ?: null,
            'status' => $request->string('status')->toString(),
            'price_min' => $request->has('price_min') ? (float) $request->input('price_min') : null,
            'price_max' => $request->has('price_max') ? (float) $request->input('price_max') : null,
            'per_page' => $request->integer('per_page', 15),
            'include_media' => $request->boolean('include_media', false),
            'include_faqs' => $request->boolean('include_faqs', false),
        ];

        $query = Service::query()
            ->with(['category', 'vendor'])
            ->withCount('images')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        // Include FAQs if requested
        if ($filters['include_faqs']) {
            $query->with('faqs');
        }

        // Public users only see visible services
        if (!$isVendor) {
            $query->where('status', Service::STATUS_ACTIVE)
                ->whereNull('admin_status');
        }

        // Vendors see only their own services (any status except suspended)
        if ($isVendor) {
            $query->where('vendor_id', $user->id)
                ->where(function ($q) {
                    $q->whereNull('admin_status')
                        ->orWhere('admin_status', '!=', Service::ADMIN_STATUS_SUSPENDED);
                });
        }

        // Apply filters
        if ($filters['search']) {
            $locale = app()->getLocale();
            $search = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($search, $locale) {
                $q->where("title->{$locale}", 'like', $search)
                    ->orWhere("description->{$locale}", 'like', $search);
            });
        }

        if ($filters['category_id']) {
            $query->where('category_id', $filters['category_id']);
        }

        if ($filters['vendor_id'] && $isVendor && $filters['vendor_id'] == $user->id) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        if ($filters['price_min'] !== null) {
            $query->where('price', '>=', $filters['price_min']);
        }

        if ($filters['price_max'] !== null) {
            $query->where('price', '<=', $filters['price_max']);
        }

        // Include media if requested
        if ($request->boolean('include_media', false)) {
            $query->with(['images', 'videos']);
        }

        $services = $query->latest()->paginate($filters['per_page']);

        $services->setCollection(
            $services->getCollection()->map(fn($service) => new ServiceResource($service))
        );

        return ApiResponse::paginated(
            $services,
            'Services retrieved successfully',
            200,
            ['applied_filters' => $filters]
        );
    }

    /**
     * Display the specified service.
     */
    public function show(Request $request, Service $service)
    {
        $user = $request->user();
        $isVendor = $user && $user->type === 'vendor';

        // Public users can only see visible services
        if (!$isVendor && !$service->isVisible()) {
            return ApiResponse::error('Service not found', [], 404);
        }

        // Vendors can only see their own services (unless admin suspended)
        if ($isVendor && $service->vendor_id !== $user->id) {
            if (!$service->isVisible()) {
                return ApiResponse::error('Service not found', [], 404);
            }
        }

        $service->load(['category', 'vendor', 'images', 'videos', 'reviews.user', 'faqs']);

        return ApiResponse::success(
            new ServiceResource($service),
            'Service retrieved successfully'
        );
    }

    /**
     * Store a newly created service (vendor only).
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->type !== 'vendor') {
            return ApiResponse::error('Only vendors can create services', [], 403);
        }

        $validated = $this->validateServiceData($request);

        DB::beginTransaction();
        try {
            $service = new Service();
            $service->vendor_id = $user->id;
            $service->category_id = $validated['category_id'];
            $service->title = normalize_translations($request->input('title'));
            $service->description = $this->normalizeOptionalTranslations($request->input('description'));
            $service->price_type = $validated['price_type'];
            $service->price = $validated['price'] ?? null;
            $service->status = $validated['status'];
            $service->attributes = $validated['attributes'] ?? null;
            $service->published_at = $validated['status'] === Service::STATUS_ACTIVE ? now() : null;
            $service->lat = $validated['lat'] ?? null;
            $service->lng = $validated['lng'] ?? null;
            $service->save();

            // Handle media uploads
            $this->handleMediaUpload($service, $request);

            // Handle FAQs
            $this->handleFaqsUpdate($service, $request);

            DB::commit();

            $service->load(['category', 'vendor', 'images', 'videos', 'faqs']);

            return ApiResponse::success(
                new ServiceResource($service),
                'Service created successfully',
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to create service: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Update the specified service.
     */
    public function update(Request $request, Service $service)
    {
        $user = $request->user();

        // Only vendor owner can update
        if (!$user || $user->type !== 'vendor' || $service->vendor_id !== $user->id) {
            return ApiResponse::error('Unauthorized', [], 403);
        }

        // Vendors cannot update if admin suspended
        if ($service->admin_status === Service::ADMIN_STATUS_SUSPENDED) {
            return ApiResponse::error('Service is suspended and cannot be updated', [], 403);
        }

        $validated = $this->validateServiceData($request, $service);

        DB::beginTransaction();
        try {
            $service->category_id = $validated['category_id'];
            $service->title = normalize_translations($request->input('title'));
            $service->description = $this->normalizeOptionalTranslations($request->input('description'));
            $service->price_type = $validated['price_type'];
            $service->price = $validated['price'] ?? null;
            $service->status = $validated['status'];
            $service->attributes = $validated['attributes'] ?? null;
            $service->lat = $validated['lat'] ?? $service->lat;
            $service->lng = $validated['lng'] ?? $service->lng;

            // Update published_at based on status
            if ($service->status === Service::STATUS_ACTIVE && !$service->published_at) {
                $service->published_at = now();
            } elseif ($service->status !== Service::STATUS_ACTIVE) {
                $service->published_at = null;
            }

            $service->save();

            // Handle media uploads if provided
            if ($request->has('media')) {
                $this->handleMediaUpload($service, $request);
            }

            // Handle FAQs
            $this->handleFaqsUpdate($service, $request);

            DB::commit();

            $service->load(['category', 'vendor', 'images', 'videos', 'faqs']);

            return ApiResponse::success(
                new ServiceResource($service),
                'Service updated successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to update service: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Remove the specified service.
     */
    public function destroy(Request $request, Service $service)
    {
        $user = $request->user();

        // Only vendor owner can delete
        if (!$user || $user->type !== 'vendor' || $service->vendor_id !== $user->id) {
            return ApiResponse::error('Unauthorized', [], 403);
        }

        DB::beginTransaction();
        try {
            // Delete associated media files
            foreach ($service->media as $media) {
                if ($media->path && Storage::exists($media->path)) {
                    Storage::delete($media->path);
                }
            }

            $service->delete();
            DB::commit();

            return ApiResponse::success(null, 'Service deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to delete service', [], 500);
        }
    }


    /**
     * Validate service data.
     */
    protected function validateServiceData(Request $request, ?Service $service = null): array
    {
        $rules = [
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'array'],
            'title.en' => ['required', 'string', 'max:255'],
            'title.*' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.*' => ['nullable', 'string', 'max:5000'],
            'price_type' => ['required', Rule::in(Service::priceTypes())],
            'price' => ['nullable', 'required_if:price_type,fixed', 'numeric', 'min:0', 'max:999999.99'],
            'status' => ['required', Rule::in(Service::vendorStatuses())],
            'attributes' => ['nullable', 'array'],
            'media' => ['nullable', 'array'],
            'media.*.file' => ['required_with:media', 'file', 'mimes:jpeg,jpg,png,gif,mp4,mov,avi', 'max:10240'],
            'media.*.type' => ['required_with:media', Rule::in(['image', 'video'])],
            'media.*.is_primary' => ['nullable', 'boolean'],
            'media.*.order' => ['nullable', 'integer', 'min:0'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'faqs' => ['nullable', 'array'],
            'faqs.*.id' => ['nullable', 'exists:service_faqs,id'],
            'faqs.*.question' => ['required_with:faqs.*', 'array'],
            'faqs.*.question.*' => ['nullable', 'string', 'max:500'],
            'faqs.*.answer' => ['required_with:faqs.*', 'array'],
            'faqs.*.answer.*' => ['nullable', 'string', 'max:2000'],
            'faqs.*.order' => ['nullable', 'integer', 'min:0'],
            'faqs.*.delete' => ['nullable', 'boolean'],
        ];

        return $request->validate($rules);
    }

    /**
     * Handle media uploads.
     */
    protected function handleMediaUpload(Service $service, Request $request)
    {
        if (!$request->has('media') || !is_array($request->input('media'))) {
            return;
        }

        foreach ($request->input('media') as $index => $mediaData) {
            if (!$request->hasFile("media.{$index}.file")) {
                continue;
            }

            $file = $request->file("media.{$index}.file");
            $type = $mediaData['type'] ?? 'image';
            $isPrimary = $mediaData['is_primary'] ?? false;
            $order = $mediaData['order'] ?? $index;

            $path = null;
            if ($type === 'image') {
                $path = uploadImage($file, 'services', ['width' => 1200, 'height' => 1200]);
            } else {
                $path = uploadFile($file, 'services/videos');
            }

            if ($path) {
                // If this is primary, unset other primary media
                if ($isPrimary) {
                    $service->media()->where('is_primary', true)->update(['is_primary' => false]);
                }

                Media::create([
                    'mediable_type' => Service::class,
                    'mediable_id' => $service->id,
                    'type' => $type,
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'order' => $order,
                    'is_primary' => $isPrimary,
                ]);
            }
        }
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
     * Handle FAQ updates for a service.
     */
    protected function handleFaqsUpdate(Service $service, Request $request): void
    {
        if (!$request->has('faqs')) {
            return;
        }

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
}
