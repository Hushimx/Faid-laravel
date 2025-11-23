<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
  /**
   * Display a listing of the products with filters & stats.
   */
  public function index(Request $request): View
  {
    $this->authorize('products.view');
    
    $filters = [
      'search' => $request->string('search')->toString(),
      'status' => $request->string('status')->toString(),
      'admin_status' => $request->string('admin_status')->toString(),
      'vendor_id' => $request->integer('vendor_id'),
      'category_id' => $request->integer('category_id'),
      'in_stock' => $request->boolean('in_stock'),
      'per_page' => $request->integer('per_page', 15),
    ];

    $perPageOptions = [15, 30, 50, 100];
    if (!in_array($filters['per_page'], $perPageOptions, true)) {
      $filters['per_page'] = 15;
    }

    $productsQuery = Product::query()
      ->with(['category', 'vendor'])
      ->withCount('images');

    if ($filters['search']) {
      $locale = app()->getLocale();
      $search = '%' . trim($filters['search']) . '%';
      $productsQuery->where(function ($query) use ($search, $locale) {
        $query->where("title->{$locale}", 'like', $search)
          ->orWhere("description->{$locale}", 'like', $search)
          ->orWhere('sku', 'like', $search);
      });
    }

    if ($filters['status'] && in_array($filters['status'], Product::vendorStatuses(), true)) {
      $productsQuery->where('status', $filters['status']);
    }

    if ($filters['admin_status'] === 'suspended') {
      $productsQuery->where('admin_status', Product::ADMIN_STATUS_SUSPENDED);
    } elseif ($filters['admin_status'] === 'active') {
      $productsQuery->whereNull('admin_status');
    }

    if ($filters['vendor_id']) {
      $productsQuery->where('vendor_id', $filters['vendor_id']);
    }

    if ($filters['category_id']) {
      $productsQuery->where('category_id', $filters['category_id']);
    }

    if ($filters['in_stock']) {
      $productsQuery->where('stock_quantity', '>', 0);
    }

    $products = $productsQuery->latest()->paginate($filters['per_page'])->withQueryString();

    $stats = [
      'total' => Product::count(),
      'active' => Product::where('status', Product::STATUS_ACTIVE)->whereNull('admin_status')->count(),
      'suspended' => Product::where('admin_status', Product::ADMIN_STATUS_SUSPENDED)->count(),
      'pending' => Product::where('status', Product::STATUS_PENDING)->count(),
      'out_of_stock' => Product::where('stock_quantity', '<=', 0)->count(),
    ];

    $vendors = \App\Models\User::where('type', 'vendor')->get(['id', 'first_name', 'last_name']);
    $categories = \App\Models\Category::all(['id', 'name']);

    return view('pages.products', compact('products', 'stats', 'filters', 'perPageOptions', 'vendors', 'categories'));
  }

  /**
   * Display the specified product.
   */
  public function show(Product $product): View
  {
    $this->authorize('products.view');
    
    $product->load(['category', 'vendor', 'images', 'videos']);
    return view('pages.products-show', compact('product'));
  }

  /**
   * Show the form for editing the specified product.
   */
  public function edit(Product $product): View
  {
    $this->authorize('products.edit');
    
    $product->load(['category', 'vendor', 'images', 'videos']);
    $categories = \App\Models\Category::all(['id', 'name']);
    return view('pages.products-edit', compact('product', 'categories'));
  }

  /**
   * Update the specified product.
   */
  public function update(Request $request, Product $product): RedirectResponse
  {
    $this->authorize('products.edit');
    
    $validated = $request->validate([
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
      'attributes' => ['nullable'],
      'sku' => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($product->id)],
    ]);

    $product->category_id = $validated['category_id'];
    $product->title = normalize_translations($request->input('title'));
    $product->description = $this->normalizeOptionalTranslations($request->input('description'));
    $product->price_type = $validated['price_type'];
    $product->price = $validated['price'] ?? null;
    $product->stock_quantity = $validated['stock_quantity'] ?? 0;
    $product->status = $validated['status'];

    // Handle attributes - can be array or JSON string
    $attributes = $validated['attributes'] ?? null;
    if (is_string($attributes)) {
      $decoded = json_decode($attributes, true);
      $product->attributes = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    } else {
      $product->attributes = $attributes;
    }

    $product->sku = $validated['sku'] ?? null;

    if ($product->status === Product::STATUS_ACTIVE && !$product->published_at) {
      $product->published_at = now();
    } elseif ($product->status !== Product::STATUS_ACTIVE) {
      $product->published_at = null;
    }

    $product->save();

    return redirect()->route('products.show', $product)
      ->with('success', __('dashboard.Product updated successfully'));
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
   * Update the product admin status.
   */
  public function updateStatus(Request $request, Product $product): RedirectResponse
  {
    $this->authorize('products.manage');
    
    $validated = $request->validate([
      'admin_status' => ['nullable', Rule::in([null, Product::ADMIN_STATUS_SUSPENDED])],
    ]);

    $product->admin_status = $validated['admin_status'] ?? null;
    $product->save();

    $message = $product->admin_status === Product::ADMIN_STATUS_SUSPENDED
      ? __('dashboard.Product suspended successfully')
      : __('dashboard.Product activated successfully');

    return redirect()->back()->with('success', $message);
  }

  /**
   * Remove the specified product.
   */
  public function destroy(Product $product): RedirectResponse
  {
    $this->authorize('products.delete');
    
    // Delete associated media files
    foreach ($product->media as $media) {
      if ($media->path && Storage::exists($media->path)) {
        Storage::delete($media->path);
      }
    }

    $product->delete();

    return redirect()->back()->with('success', __('dashboard.Product deleted successfully'));
  }
}
