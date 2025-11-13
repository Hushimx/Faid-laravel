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
      ->where('status', Category::STATUS_ACTIVE);


    // Search filter
    if ($filters['search']) {
      $search = '%' . trim($filters['search']) . '%';
      $categoriesQuery->where(function ($query) use ($search, $locale) {
        $query->where("name->{$locale}", 'like', $search)
          ->orWhere("description->{$locale}", 'like', $search);
      });
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

    // No parent/children relationships - return category as is

    return ApiResponse::success(
      new CategoryResource($category),
      'Category retrieved successfully'
    );
  }
}
