<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
  /**
   * Display a listing of the categories.
   */
  public function index(Request $request)
  {
    // Get locale - it's already set by middleware, but we can still get it from request
    $locale = app()->getLocale();

    // Order by
    $orderBy = $request->string('order_by', 'created_at')->toString();
    $orderDirection = $request->string('order_direction', 'desc')->toString();

    $filters = [
      'search' => $request->string('search')->toString(),
      'parent_id' => $request->integer('parent_id') ?: null,
      'include_children' => $request->boolean('include_children', false),
      'only_parents' => $request->boolean('only_parents', false),
      'per_page' => $request->integer('per_page', 15),
      'order_by' => $orderBy,
      'order_direction' => $orderDirection,
      'locale' => $locale,
    ];

    $perPageOptions = [12, 24, 48, 96];
    if (!in_array($filters['per_page'], $perPageOptions, true)) {
      $filters['per_page'] = 15;
    }

    // Only show active categories for public users
    $categoriesQuery = Category::query()
      ->where('status', Category::STATUS_ACTIVE)
      ->withCount('children')
      // Always load parent (only active parents)
      ->with(['parent' => function ($query) {
        $query->where('status', Category::STATUS_ACTIVE);
      }]);

    // Load children if requested (only active children)
    if ($filters['include_children']) {
      $categoriesQuery->with(['children' => function ($query) {
        $query->where('status', Category::STATUS_ACTIVE);
      }]);
    }

    // Search filter
    if ($filters['search']) {
      $search = '%' . trim($filters['search']) . '%';
      $categoriesQuery->where(function ($query) use ($search, $locale) {
        $query->where("name->{$locale}", 'like', $search)
          ->orWhere("description->{$locale}", 'like', $search);
      });
    }

    // Note: Status filter is removed - only active categories are shown
    // Parent filter
    if ($filters['parent_id'] !== null) {
      // Filter by parent_id (the parent will be loaded if it's active via the eager loading)
      $categoriesQuery->where('parent_id', $filters['parent_id']);
    } elseif ($filters['only_parents']) {
      $categoriesQuery->whereNull('parent_id');
    }

    if ($orderBy === 'name') {
      $categoriesQuery->orderBy("name->{$locale}", $orderDirection);
    } elseif (in_array($orderBy, ['id', 'status', 'created_at', 'updated_at'], true)) {
      $categoriesQuery->orderBy($orderBy, $orderDirection);
    } else {
      $categoriesQuery->latest();
    }

    $categories = $categoriesQuery->paginate($filters['per_page']);

    // Transform categories using CategoryResource
    $categories->setCollection(
      $categories->getCollection()->map(fn($category) => new CategoryResource($category))
    );

    return ApiResponse::paginated(
      $categories,
      'Categories retrieved successfully',
      200,
      [
        'applied_filters' => $filters,
      ]
    );
  }

  /**
   * Display the specified category.
   */
  public function show(Request $request, Category $category)
  {
    // Only show active categories
    if ($category->status !== Category::STATUS_ACTIVE) {
      return ApiResponse::error(
        'Category not found',
        [],
        404
      );
    }

    // Always load parent if exists (only active parents)
    $category->load(['parent' => function ($query) {
      $query->where('status', Category::STATUS_ACTIVE);
    }]);

    if ($request->boolean('include_children', false)) {
      // Only load active children
      $category->load(['children' => function ($query) {
        $query->where('status', Category::STATUS_ACTIVE);
      }]);
    }

    // Count only active children
    $category->loadCount(['children' => function ($query) {
      $query->where('status', Category::STATUS_ACTIVE);
    }]);

    return ApiResponse::success(
      new CategoryResource($category),
      'Category retrieved successfully'
    );
  }

  /**
   * Get all parent categories (for dropdowns, etc).
   */
  public function parents(Request $request)
  {
    $locale = app()->getLocale();

    // Only return active parent categories
    $parents = Category::whereNull('parent_id')
      ->where('status', Category::STATUS_ACTIVE)
      ->orderBy("name->{$locale}")
      ->get();

    return ApiResponse::success(
      CategoryResource::collection($parents),
      'Parent categories retrieved successfully'
    );
  }

  /**
   * Get category tree (all categories with children).
   */
  public function tree(Request $request)
  {
    $locale = app()->getLocale();

    // Only return active categories in the tree
    $query = Category::whereNull('parent_id')
      ->where('status', Category::STATUS_ACTIVE)
      ->with(['children' => function ($query) {
        // Only load active children
        $query->where('status', Category::STATUS_ACTIVE)
          ->orderBy('name');
      }]);

    $tree = $query->orderBy("name->{$locale}")->get();

    return ApiResponse::success(
      CategoryResource::collection($tree),
      'Category tree retrieved successfully'
    );
  }
}
