<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
  /**
   * Display a listing of the categories with filters & stats.
   */
  public function index(Request $request): View
  {
    $this->authorize('categories.view');
    
    $filters = [
      'search' => $request->string('search')->toString(),
      'status' => $request->string('status')->toString(),
      'per_page' => $request->integer('per_page', 12),
    ];

    $perPageOptions = [12, 24, 48, 96];
    if (!in_array($filters['per_page'], $perPageOptions, true)) {
      $filters['per_page'] = 12;
    }

    $categoriesQuery = Category::query()->latest();

    if ($filters['search']) {
      $locale = app()->getLocale();
      $search = '%' . trim($filters['search']) . '%';
      $categoriesQuery->where(function ($query) use ($search, $locale) {
        $query->where("name->{$locale}", 'like', $search)
          ->orWhere("description->{$locale}", 'like', $search);
      });
    }

    if ($filters['status'] && in_array($filters['status'], Category::statuses(), true)) {
      $categoriesQuery->where('status', $filters['status']);
    }

    $categories = $categoriesQuery->paginate($filters['per_page'])->withQueryString();

    $stats = [
      'total' => Category::count(),
      'active' => Category::where('status', Category::STATUS_ACTIVE)->count(),
      'inactive' => Category::where('status', Category::STATUS_INACTIVE)->count(),
    ];

    return view('pages.categories', compact('categories', 'stats', 'filters', 'perPageOptions'));
  }

  /**
   * Store a newly created category.
   */
  public function store(Request $request): RedirectResponse
  {
    $this->authorize('categories.create');
    
    try {
      $data = $this->validateData($request);

      $category = new Category();
      $category->name = normalize_translations($request->input('name'));
      $category->description = $this->normalizeOptionalTranslations($request->input('description'));
      $category->status = $data['status'];
      // parent_id removed - flat categories only

      if ($request->hasFile('image')) {
        $path = uploadImage($request->file('image'), 'categories', ['width' => 600, 'height' => 600]);

        if (!$path) {
          \Log::error('Category image upload returned null', [
            'file_name' => $request->file('image')->getClientOriginalName(),
            'file_size' => $request->file('image')->getSize(),
            'mime_type' => $request->file('image')->getMimeType(),
          ]);
          return redirect()->back()->withInput($request->except('image'))
            ->with('error', __('dashboard.Category image upload failed. Please check the logs for details.'));
        }

        $category->image = $path;
      }

      $category->save();

      return redirect()->route('categories.index')->with('success', __('dashboard.Category created successfully'));
    } catch (\Exception $e) {
      return redirect()->back()
        ->withInput($request->except('image'))
        ->with('error', __('dashboard.Failed to create category: :error', ['error' => $e->getMessage()]));
    }
  }

  /**
   * Update the specified category.
   */
  public function update(Request $request, Category $category): RedirectResponse
  {
    $this->authorize('categories.edit');
    
    $data = $this->validateData($request, (int) $category->id);
    $category->name = normalize_translations($request->input('name'));
    $category->description = $this->normalizeOptionalTranslations($request->input('description'));
    $category->status = $data['status'];
    // parent_id removed - flat categories only

    if ($request->hasFile('image')) {
      $path = uploadImage($request->file('image'), 'categories', ['width' => 600, 'height' => 600], $category->image);

      if (!$path) {
        return redirect()->back()->withInput($request->except('image'))
          ->with('error', __('dashboard.Category image upload failed'));
      }

      $category->image = $path;
    }

    $category->save();

    return redirect()->route('categories.index')->with('success', __('dashboard.Category updated successfully'));
  }

  /**
   * Remove the specified category.
   */
  public function destroy(Category $category): RedirectResponse
  {
    $this->authorize('categories.delete');
    
    if ($category->image) {
      deleteFile($category->image);
    }

    $category->delete();

    return redirect()->route('categories.index')->with('success', __('dashboard.Category deleted successfully'));
  }

  /**
   * Validate incoming request data for store & update.
   */
  protected function validateData(Request $request, ?int $categoryId = null): array
  {
    $rules = [
      'name' => ['required', 'array'],
      'name.en' => ['required', 'string', 'max:255'],
      'name.*' => ['nullable', 'string', 'max:255'],
      'description' => ['nullable', 'array'],
      'description.*' => ['nullable', 'string', 'max:1000'],
      'status' => ['required', Rule::in(Category::statuses())],
      'image' => ['nullable', 'image', 'max:3072'],
    ];

    $data = $request->validate($rules);

    return $data;
  }

  /**
   * Normalize optional translation array.
   */
  protected function normalizeOptionalTranslations(?array $input): ?array
  {
    $hasContent = collect($input ?? [])->filter(fn($value) => filled($value))->isNotEmpty();

    return $hasContent ? normalize_translations($input) : null;
  }
}
