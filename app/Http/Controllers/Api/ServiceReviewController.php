<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Support\ApiResponse;

use App\Services\FirebaseService;
use App\Notifications\NewReviewNotification;

class ServiceReviewController extends Controller
{
  protected FirebaseService $firebaseService;

  public function __construct(FirebaseService $firebaseService)
  {
    $this->firebaseService = $firebaseService;
  }
  /**
   * List reviews for a service (public).
   */
  public function index(Service $service)
  {
    $reviews = $service->reviews()
      ->with('user')
      ->orderBy('created_at', 'desc')
      ->paginate(10);
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

    // Notify Vendor
    $service->load('vendor'); // Ensure vendor is loaded
    $vendor = $service->vendor;

    if ($vendor && $vendor->id !== $user->id) { // Don't notify if reviewing own service
      // 1. Save to Database (Inbox)
      $vendor->notify(new NewReviewNotification($review));

      // 2. Send Push Notification (FCM)
      $titleEn = 'New Review';
      $titleAr = 'تقييم جديد';
      $bodyEn = "{$user->name} rated your service {$service->title}";
      $bodyAr = "قام {$user->name} بتقييم خدمتك {$service->title}";

      $this->firebaseService->sendToUsers(
        [$vendor->id],
        $titleAr,
        $titleEn,
        $bodyAr,
        $bodyEn,
        [
          'type' => 'review',
          'service_id' => $service->id,
          'review_id' => $review->id
        ]
      );
    }

    return ApiResponse::success(new ReviewResource($review->load('user')), 'Review saved successfully');
  }
}
