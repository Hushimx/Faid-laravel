<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Models\Media;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\City;
use App\Models\Country;

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
            'city_id' => $request->integer('city_id') ?: null,
            'status' => $request->string('status')->toString(),
            'price_min' => $request->has('price_min') ? (float) $request->input('price_min') : null,
            'price_max' => $request->has('price_max') ? (float) $request->input('price_max') : null,
            'sort' => $request->string('sort')->toString() ?: 'latest',
            'per_page' => $request->integer('per_page', 20),
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

        if ($filters['city_id']) {
            $query->where('city_id', $filters['city_id']);
        }

        if ($filters['vendor_id'] && $isVendor && $filters['vendor_id'] == $user->id) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        if ($filters['price_min'] !== null && $filters['price_min'] > 0) {
            $query->where('price', '>=', $filters['price_min']);
        }

        if ($filters['price_max'] !== null && $filters['price_max'] > 0) {
            $query->where('price', '<=', $filters['price_max']);
        }

        // Include media if requested
        if ($request->boolean('include_media', false)) {
            $query->with(['images', 'videos']);
        }

        // Apply sorting
        switch ($filters['sort']) {
            case 'oldest':
                $query->oldest();
                break;
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'rating':
                $query->orderByRaw('(SELECT AVG(rating) FROM reviews WHERE reviews.service_id = services.id) DESC NULLS LAST');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $services = $query->paginate($filters['per_page']);

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
     * Get related services (same category and city, ordered by distance).
     */
    public function related(Request $request, $serviceId)
    {
        $service = Service::find($serviceId);
        
        if (!$service) {
            return ApiResponse::error('Service not found', [], 404);
        }

        // Check if service is visible to public
        $user = $request->user();
        $isVendor = $user && $user->type === 'vendor';
        
        if (!$isVendor && !$service->isVisible()) {
            return ApiResponse::error('Service not found', [], 404);
        }

        // Use cache to avoid recalculating distances
        $cacheKey = "related_services_{$serviceId}";
        
        $relatedServices = Cache::remember($cacheKey, 3600, function () use ($service) {
            $query = Service::query()
                ->where('id', '!=', $service->id)
                ->where('category_id', $service->category_id)
                ->where('status', Service::STATUS_ACTIVE)
                ->whereNull('admin_status')
                ->with(['category', 'vendor', 'images'])
                ->withCount('images')
                ->withCount('reviews')
                ->withAvg('reviews', 'rating');

            // Filter by city if service has city_id
            if ($service->city_id) {
                $query->where('city_id', $service->city_id);
            }

            // If service has coordinates, calculate distance and order by proximity
            if ($service->lat && $service->lng) {
                $lat = $service->lat;
                $lng = $service->lng;
                
                // Haversine formula for distance calculation (in kilometers)
                $query->selectRaw("
                    services.*,
                    (6371 * acos(
                        cos(radians(?)) * 
                        cos(radians(services.lat)) * 
                        cos(radians(services.lng) - radians(?)) + 
                        sin(radians(?)) * 
                        sin(radians(services.lat))
                    )) AS distance
                ", [$lat, $lng, $lat])
                ->whereNotNull('lat')
                ->whereNotNull('lng')
                ->orderBy('distance', 'asc');
            } else {
                // If no coordinates, just order by latest
                $query->latest();
            }

            return $query->limit(10)->get();
        });

        // Transform to ServiceResource
        $relatedServices = $relatedServices->map(fn($service) => new ServiceResource($service));

        return ApiResponse::success(
            $relatedServices,
            'Related services retrieved successfully'
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
            $service->address = $this->normalizeOptionalTranslations($request->input('address'));
            $service->city_id = $this->handleCityLogic($request->input('city'));
            $service->status = Service::STATUS_ACTIVE;
            $service->attributes = $validated['attributes'] ?? null;
            $service->published_at = ($validated['status'] ?? Service::STATUS_ACTIVE) === Service::STATUS_ACTIVE ? now() : null;
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
     * Accepts both PUT (for JSON) and POST (for form-data) requests
     */
    public function update(Request $request, $serviceId)
    {
        $user = $request->user();
        
        $service = Service::find($serviceId);
        
        if (!$service) {
            return ApiResponse::error('Service not found', [], 404);
        }

        if (!$user || $user->type !== 'vendor' || $service->vendor_id !== $user->id) {
            return ApiResponse::error('Unauthorized', [], 403);
        }

        if ($service->admin_status === Service::ADMIN_STATUS_SUSPENDED) {
            return ApiResponse::error('Service is suspended and cannot be updated', [], 403);
        }

        // For PUT with multipart/form-data, manually parse the request
        // Laravel doesn't parse multipart/form-data with PUT automatically
        $allData = [];
        
        if ($request->method() === 'PUT' && str_contains($request->header('Content-Type', ''), 'multipart/form-data')) {
            // Parse multipart form data manually
            $content = $request->getContent();
            $boundary = '';
            
            // Extract boundary from Content-Type header
            if (preg_match('/boundary=(.*)$/is', $request->header('Content-Type', ''), $matches)) {
                $boundary = '--' . trim($matches[1]);
            }
            
            if ($boundary && $content) {
                $parts = explode($boundary, $content);
                foreach ($parts as $part) {
                    if (preg_match('/name="([^"]+)"\s*\r?\n\r?\n(.*?)(?=\r?\n--|$)/s', $part, $matches)) {
                        $fieldName = $matches[1];
                        $fieldValue = trim($matches[2]);
                        
                        // Handle array notation like title[ar]
                        if (preg_match('/^(.+)\[(.+)\]$/', $fieldName, $nameMatches)) {
                            $allData[$nameMatches[1]][$nameMatches[2]] = $fieldValue;
                        } else {
                            $allData[$fieldName] = $fieldValue;
                        }
                    }
                }
                
                // Merge parsed data into request
                $request->merge($allData);
            }
        } else {
            $allData = $request->all();
        }
        
        // Validate
        $validated = $this->validateServiceData($request, $service);
        
        // Merge validated data
        $allData = array_merge($allData, $validated);
        
        Log::info('Update Request', [
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'all_data' => $allData,
            'validated' => $validated,
            'price_type' => $allData['price_type'] ?? 'NOT_SET',
            'price' => $allData['price'] ?? 'NOT_SET',
        ]);

        DB::beginTransaction();
        try {
            // Update category_id if sent
            if (isset($allData['category_id']) || $request->input('category_id') !== null) {
                $service->category_id = $validated['category_id'] ?? ($allData['category_id'] ?? $request->input('category_id'));
            }

            // Update title if sent
            $titleInput = $allData['title'] ?? $request->input('title');
            if (isset($allData['title']) || $titleInput !== null || isset($allData['title.ar']) || isset($allData['title.en'])) {
                if (is_array($titleInput) && !empty($titleInput)) {
                    $service->title = normalize_translations($titleInput);
                } else {
                    $service->title = normalize_translations([
                        'ar' => $allData['title.ar'] ?? $request->input('title.ar', ''),
                        'en' => $allData['title.en'] ?? $request->input('title.en', ''),
                    ]);
                }
            }

            // Update description if sent
            $descInput = $allData['description'] ?? $request->input('description');
            if (isset($allData['description']) || $descInput !== null || isset($allData['description.ar']) || isset($allData['description.en'])) {
                if (is_array($descInput) && !empty($descInput)) {
                    $service->description = $this->normalizeOptionalTranslations($descInput);
                } else {
                    $service->description = $this->normalizeOptionalTranslations([
                        'ar' => $allData['description.ar'] ?? $request->input('description.ar', ''),
                        'en' => $allData['description.en'] ?? $request->input('description.en', ''),
                    ]);
                }
            }

            // Update price_type if sent - prioritize validated data
            $priceTypeValue = $validated['price_type'] ?? ($allData['price_type'] ?? $request->input('price_type'));
            if ($priceTypeValue !== null && $priceTypeValue !== '') {
                $service->price_type = $priceTypeValue;
                Log::info('Updating price_type', ['old' => $service->getOriginal('price_type'), 'new' => $priceTypeValue]);
            }

            // Update price if sent
            $priceValue = $validated['price'] ?? ($allData['price'] ?? $request->input('price'));
            if ($priceValue !== null) {
                if ($priceValue === '' || $priceValue === null) {
                    $service->price = null;
                } else {
                    $service->price = is_numeric($priceValue) ? (float)$priceValue : null;
                }
                Log::info('Updating price', ['old' => $service->getOriginal('price'), 'new' => $service->price]);
            } elseif ($priceTypeValue !== null && $priceTypeValue === Service::PRICE_TYPE_UNSPECIFIED) {
                $service->price = null;
                Log::info('Setting price to null for unspecified type');
            }

            // Update address if sent
            $addressInput = $allData['address'] ?? $request->input('address');
            if (isset($allData['address']) || $addressInput !== null || isset($allData['address.ar']) || isset($allData['address.en'])) {
                if (is_array($addressInput) && !empty($addressInput)) {
                    $service->address = $this->normalizeOptionalTranslations($addressInput);
                } else {
                    $service->address = $this->normalizeOptionalTranslations([
                        'ar' => $allData['address.ar'] ?? $request->input('address.ar', ''),
                        'en' => $allData['address.en'] ?? $request->input('address.en', ''),
                    ]);
                }
            }

            // Update city if sent
            $cityInput = $allData['city'] ?? $request->input('city');
            if (isset($allData['city']) || $cityInput !== null || isset($allData['city.ar']) || isset($allData['city.en'])) {
                if (is_array($cityInput) && !empty($cityInput)) {
                    $service->city_id = $this->handleCityLogic($cityInput);
                } else {
                    $service->city_id = $this->handleCityLogic([
                        'ar' => $allData['city.ar'] ?? $request->input('city.ar', ''),
                        'en' => $allData['city.en'] ?? $request->input('city.en', ''),
                    ]);
                }
            }

            // Update lat/lng if sent
            if (isset($allData['lat']) || $request->input('lat') !== null) {
                $service->lat = $validated['lat'] ?? ($allData['lat'] ?? $request->input('lat'));
            }
            if (isset($allData['lng']) || $request->input('lng') !== null) {
                $service->lng = $validated['lng'] ?? ($allData['lng'] ?? $request->input('lng'));
            }

            // Update status if sent
            if (isset($allData['status']) || $request->input('status') !== null) {
                $service->status = $validated['status'] ?? ($allData['status'] ?? $request->input('status'));
                if ($service->status === Service::STATUS_ACTIVE && !$service->published_at) {
                    $service->published_at = now();
                } elseif ($service->status !== Service::STATUS_ACTIVE) {
                    $service->published_at = null;
                }
            }

            // Update attributes if sent
            if (isset($allData['attributes']) || $request->input('attributes') !== null) {
                $service->attributes = $validated['attributes'] ?? ($allData['attributes'] ?? null);
            }

            // Log before save
            Log::info('Before Save', [
                'is_dirty' => $service->isDirty(),
                'dirty_attributes' => $service->getDirty(),
                'category_id' => $service->category_id,
                'price_type' => $service->price_type,
                'price' => $service->price,
                'title' => $service->title,
            ]);
            
            // Force save even if nothing changed (to trigger events)
            $saved = $service->save();
            
            Log::info('After Save', [
                'saved' => $saved,
                'category_id' => $service->category_id,
                'price_type' => $service->price_type,
                'price' => $service->price,
                'title' => $service->title,
            ]);

            // Handle media deletions
            if ($request->has('keep_media_ids')) {
                $keepMediaIds = $request->input('keep_media_ids', []);
                if (!is_array($keepMediaIds)) {
                    $keepMediaIds = [$keepMediaIds];
                }
                $service->media()
                    ->whereNotIn('id', $keepMediaIds)
                    ->get()
                    ->each(function ($media) {
                        if ($media->path && Storage::exists($media->path)) {
                            Storage::delete($media->path);
                        }
                        $media->delete();
                    });
            }

            // Handle media uploads
            if ($request->has('media')) {
                $this->handleMediaUpload($service, $request);
            }

            // Handle FAQs
            if ($request->has('faqs')) {
                $this->handleFaqsUpdate($service, $request);
            }

            DB::commit();

            $service->refresh();
            $service->load(['category', 'vendor', 'images', 'videos', 'faqs']);

            return ApiResponse::success(
                new ServiceResource($service),
                'Service updated successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update service: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::error('Failed to update service: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Validate service data.
     */
    protected function validateServiceData(Request $request, ?Service $service = null): array
    {
        $isUpdate = $service !== null;
        
        $rules = [
            'category_id' => $isUpdate ? ['sometimes', 'nullable', 'exists:categories,id'] : ['required', 'exists:categories,id'],
            'title' => $isUpdate ? ['sometimes', 'nullable', 'array'] : ['required', 'array'],
            'title.ar' => ['nullable', 'string', 'max:255'],
            'title.en' => ['nullable', 'string', 'max:255'],
            'title.*' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.*' => ['nullable', 'string', 'max:5000'],
            'price_type' => $isUpdate ? ['sometimes', 'nullable', Rule::in(Service::priceTypes())] : ['required', Rule::in(Service::priceTypes())],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'address' => ['nullable', 'array'],
            'address.ar' => ['nullable', 'string', 'max:1000'],
            'address.en' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'array'],
            'city.ar' => ['nullable', 'string', 'max:100'],
            'city.en' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(Service::vendorStatuses())],
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
            'faqs.*.question' => ['nullable', 'array'],
            'faqs.*.question.*' => ['nullable', 'string', 'max:500'],
            'faqs.*.answer' => ['nullable', 'array'],
            'faqs.*.answer.*' => ['nullable', 'string', 'max:2000'],
            'faqs.*.order' => ['nullable', 'integer', 'min:0'],
            'faqs.*.delete' => ['nullable', 'boolean'],
            'keep_media_ids' => ['nullable', 'array'],
            'keep_media_ids.*' => ['integer', 'exists:media,id'],
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

    /**
     * Remove the specified service.
     */
    public function destroy(Request $request, $serviceId)
    {
        $user = $request->user();
        
        $service = Service::find($serviceId);
        
        if (!$service) {
            return ApiResponse::error('Service not found', [], 404);
        }

        if (!$user || $user->type !== 'vendor' || $service->vendor_id !== $user->id) {
            return ApiResponse::error('Unauthorized', [], 403);
        }

        DB::beginTransaction();
        try {
            // Delete associated media files
            $service->media()->each(function ($media) {
                if ($media->path && Storage::exists($media->path)) {
                    Storage::delete($media->path);
                }
                $media->delete();
            });

            // Delete associated FAQs
            $service->faqs()->delete();

            // Delete the service
            $service->delete();

            DB::commit();

            return ApiResponse::success(
                null,
                'Service deleted successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete service: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::error('Failed to delete service: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Handle city and country logic.
     */
    protected function handleCityLogic(?array $cityData): ?int
    {
        if (empty($cityData) || !is_array($cityData)) {
            return null;
        }

        $cityNameAr = $cityData['ar'] ?? null;
        $cityNameEn = $cityData['en'] ?? $cityNameAr;

        if (empty($cityNameAr)) {
            return null;
        }

        // Check/Create Country ID 1 (Saudi Arabia)
        $country = Country::find(1);
        if (!$country) {
            $country = Country::create([
                'id' => 1,
                'name' => ['en' => 'Saudi Arabia', 'ar' => 'السعودية'],
            ]);
        }

        // Normalize city name translations
        $cityName = normalize_translations([
            'ar' => $cityNameAr,
            'en' => $cityNameEn,
        ]);

        // Check/Create City
        // Search in both English and Arabic translations
        $city = City::where('country_id', $country->id)
            ->where(function ($query) use ($cityNameAr, $cityNameEn) {
                $query->where('name->ar', $cityNameAr)
                    ->orWhere('name->en', $cityNameEn);
            })->first();

        if (!$city) {
            $city = City::create([
                'country_id' => $country->id,
                'name' => $cityName,
            ]);
        } else {
            // Update city name if it doesn't have both translations
            $currentName = $city->name;
            if (empty($currentName['en']) || empty($currentName['ar'])) {
                $city->name = $cityName;
                $city->save();
            }
        }

        return $city->id;
    }
}
