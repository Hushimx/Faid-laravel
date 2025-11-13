<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfferResource;
use App\Models\Offer;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class OfferController extends Controller
{
  public function index(Request $request)
  {
    $offers = Offer::where('status', Offer::STATUS_ACTIVE)
      ->orderBy('created_at', 'desc')
      ->get();

    return ApiResponse::success(OfferResource::collection($offers), 'Offers retrieved successfully');
  }
}
