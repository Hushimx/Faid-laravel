<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Support\ApiResponse;

class ServiceReviewController extends Controller
{
  /**
   * List reviews for a service (public).
   */
  public function index(Service $service)
  {
    $reviews = $service->reviews()->with('user')->paginate(10);
    // Wrap each item with the resource so ApiResponse::paginated returns formatted items
    $reviews->setCollection($reviews->getCollection()->map(fn($r) => new ReviewResource($r)));

    return ApiResponse::paginated($reviews, 'Reviews retrieved successfully');
  }

  /**
   * Store or update a review for the authenticated user.
   */
  public function store(Request $request, Service $service)
  {
    $validator = Validator::make($request->all(), [
      'rating' => ['required', 'integer', 'min:1', 'max:5'],
      'comment' => ['nullable', 'string', 'max:1000'],
    ]);
    if ($validator->fails()) {
      return ApiResponse::error('Validation failed', $validator->errors()->toArray(), 422);
    }

    $user = $request->user();

    // Either update existing review or create a new one
    $review = Review::updateOrCreate(
      ['user_id' => $user->id, 'service_id' => $service->id],
      ['rating' => $request->input('rating'), 'comment' => $request->input('comment')]
    );

    return ApiResponse::success(new ReviewResource($review->load('user')), 'Review saved successfully');
  }
}
