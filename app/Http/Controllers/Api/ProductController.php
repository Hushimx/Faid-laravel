<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Media;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
  /**
   * Display a listing of products (public or vendor's own).
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
      'in_stock' => $request->boolean('in_stock'),
      'price_min' => $request->has('price_min') ? (float) $request->input('price_min') : null,
      'price_max' => $request->has('price_max') ? (float) $request->input('price_max') : null,
      'per_page' => $request->integer('per_page', 15),
    ];

    $query = Product::query()
      ->with(['category', 'vendor'])
      ->withCount('images');

    // Public users only see visible products
    if (!$isVendor) {
      $query->where('status', Product::STATUS_ACTIVE)
        ->whereNull('admin_status');
    }

    // Vendors see only their own products (any status except suspended)
    if ($isVendor) {
      $query->where('vendor_id', $user->id)
        ->where(function ($q) {
          $q->whereNull('admin_status')
            ->orWhere('admin_status', '!=', Product::ADMIN_STATUS_SUSPENDED);
        });
    }

    // Apply filters
    if ($filters['search']) {
      $locale = app()->getLocale();
      $search = '%' . trim($filters['search']) . '%';
      $query->where(function ($q) use ($search, $locale) {
        $q->where("title->{$locale}", 'like', $search)
          ->orWhere("description->{$locale}", 'like', $search)
          ->orWhere('sku', 'like', $search);
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

    if ($filters['in_stock']) {
      $query->where('stock_quantity', '>', 0);
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

    $products = $query->latest()->paginate($filters['per_page']);

    $products->setCollection(
      $products->getCollection()->map(fn($product) => new ProductResource($product))
    );

    return ApiResponse::paginated(
      $products,
      'Products retrieved successfully',
      200,
      ['applied_filters' => $filters]
    );
  }

  /**
   * Display the specified product.
   */
  public function show(Request $request, Product $product)
  {
    $user = $request->user();
    $isVendor = $user && $user->type === 'vendor';

    // Public users can only see visible products
    if (!$isVendor && !$product->isVisible()) {
      return ApiResponse::error('Product not found', [], 404);
    }

    // Vendors can only see their own products (unless admin suspended)
    if ($isVendor && $product->vendor_id !== $user->id) {
      if (!$product->isVisible()) {
        return ApiResponse::error('Product not found', [], 404);
      }
    }

    $product->load(['category', 'vendor', 'images', 'videos']);

    return ApiResponse::success(
      new ProductResource($product),
      'Product retrieved successfully'
    );
  }

  /**
   * Store a newly created product (vendor only).
   */
  public function store(Request $request)
  {
    $user = $request->user();

    if (!$user || $user->type !== 'vendor') {
      return ApiResponse::error('Only vendors can create products', [], 403);
    }

    $validated = $this->validateProductData($request);

    DB::beginTransaction();
    try {
      $product = new Product();
      $product->vendor_id = $user->id;
      $product->category_id = $validated['category_id'];
      $product->title = normalize_translations($request->input('title'));
      $product->description = $this->normalizeOptionalTranslations($request->input('description'));
      $product->price_type = $validated['price_type'];
      $product->price = $validated['price'] ?? null;
      $product->stock_quantity = $validated['stock_quantity'] ?? 0;
      $product->status = $validated['status'];
      $product->attributes = $validated['attributes'] ?? null;
      $product->sku = $validated['sku'] ?? null;
      $product->published_at = $validated['status'] === Product::STATUS_ACTIVE ? now() : null;
      $product->save();

      // Handle media uploads
      $this->handleMediaUpload($product, $request);

      DB::commit();

      $product->load(['category', 'vendor', 'images', 'videos']);

      return ApiResponse::success(
        new ProductResource($product),
        'Product created successfully',
        201
      );
    } catch (\Exception $e) {
      DB::rollBack();
      return ApiResponse::error('Failed to create product: ' . $e->getMessage(), [], 500);
    }
  }

  /**
   * Update the specified product.
   */
  public function update(Request $request, Product $product)
  {
    $user = $request->user();

    // Only vendor owner can update
    if (!$user || $user->type !== 'vendor' || $product->vendor_id !== $user->id) {
      return ApiResponse::error('Unauthorized', [], 403);
    }

    // Vendors cannot update if admin suspended
    if ($product->admin_status === Product::ADMIN_STATUS_SUSPENDED) {
      return ApiResponse::error('Product is suspended and cannot be updated', [], 403);
    }

    $validated = $this->validateProductData($request, $product);

    DB::beginTransaction();
    try {
      $product->category_id = $validated['category_id'];
      $product->title = normalize_translations($request->input('title'));
      $product->description = $this->normalizeOptionalTranslations($request->input('description'));
      $product->price_type = $validated['price_type'];
      $product->price = $validated['price'] ?? null;
      $product->stock_quantity = $validated['stock_quantity'] ?? 0;
      $product->status = $validated['status'];
      $product->attributes = $validated['attributes'] ?? null;
      $product->sku = $validated['sku'] ?? null;

      // Update published_at based on status
      if ($product->status === Product::STATUS_ACTIVE && !$product->published_at) {
        $product->published_at = now();
      } elseif ($product->status !== Product::STATUS_ACTIVE) {
        $product->published_at = null;
      }

      $product->save();

      // Handle media uploads if provided
      if ($request->has('media')) {
        $this->handleMediaUpload($product, $request);
      }

      DB::commit();

      $product->load(['category', 'vendor', 'images', 'videos']);

      return ApiResponse::success(
        new ProductResource($product),
        'Product updated successfully'
      );
    } catch (\Exception $e) {
      DB::rollBack();
      return ApiResponse::error('Failed to update product: ' . $e->getMessage(), [], 500);
    }
  }

  /**
   * Remove the specified product.
   */
  public function destroy(Request $request, Product $product)
  {
    $user = $request->user();

    // Only vendor owner can delete
    if (!$user || $user->type !== 'vendor' || $product->vendor_id !== $user->id) {
      return ApiResponse::error('Unauthorized', [], 403);
    }

    DB::beginTransaction();
    try {
      // Delete associated media files
      foreach ($product->media as $media) {
        if ($media->path && Storage::exists($media->path)) {
          Storage::delete($media->path);
        }
      }

      $product->delete();
      DB::commit();

      return ApiResponse::success(null, 'Product deleted successfully');
    } catch (\Exception $e) {
      DB::rollBack();
      return ApiResponse::error('Failed to delete product', [], 500);
    }
  }


  /**
   * Validate product data.
   */
  protected function validateProductData(Request $request, ?Product $product = null): array
  {
    $skuRule = ['nullable', 'string', 'max:100', 'unique:products,sku'];
    if ($product) {
      $skuRule[] = Rule::unique('products', 'sku')->ignore($product->id);
    }

    $rules = [
      'category_id' => ['required', 'exists:categories,id'],
      'title' => ['required', 'array'],
      'title.en' => ['required', 'string', 'max:255'],
      'title.*' => ['nullable', 'string', 'max:255'],
      'description' => ['nullable', 'array'],
      'description.*' => ['nullable', 'string', 'max:5000'],
      'price_type' => ['required', Rule::in(Product::priceTypes())],
      'price' => ['nullable', 'required_if:price_type,fixed', 'numeric', 'min:0', 'max:999999.99'],
      'stock_quantity' => ['nullable', 'integer', 'min:0'],
      'status' => ['required', Rule::in(Product::vendorStatuses())],
      'attributes' => ['nullable', 'array'],
      'sku' => $skuRule,
      'media' => ['nullable', 'array'],
      'media.*.file' => ['required_with:media', 'file', 'mimes:jpeg,jpg,png,gif,mp4,mov,avi', 'max:10240'],
      'media.*.type' => ['required_with:media', Rule::in(['image', 'video'])],
      'media.*.is_primary' => ['nullable', 'boolean'],
      'media.*.order' => ['nullable', 'integer', 'min:0'],
    ];

    return $request->validate($rules);
  }

  /**
   * Handle media uploads.
   */
  protected function handleMediaUpload(Product $product, Request $request)
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
        $path = uploadImage($file, 'products', ['width' => 1200, 'height' => 1200]);
      } else {
        $path = uploadFile($file, 'products/videos');
      }

      if ($path) {
        // If this is primary, unset other primary media
        if ($isPrimary) {
          $product->media()->where('is_primary', true)->update(['is_primary' => false]);
        }

        Media::create([
          'mediable_type' => Product::class,
          'mediable_id' => $product->id,
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
}
