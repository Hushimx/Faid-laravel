<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    /**
     * Display a listing of the banners (active only).
     */
    public function index(Request $request)
    {
        // Only show active banners for public users
        $bannersQuery = Banner::query()
            ->where('status', Banner::STATUS_ACTIVE)
            ->orderBy('order')
            ->latest();

        $banners = $bannersQuery->get();

        // Transform banners using BannerResource
        $banners = $banners->map(fn($banner) => new BannerResource($banner));

        return ApiResponse::success(
            $banners,
            'Banners retrieved successfully'
        );
    }

    /**
     * Display the specified banner.
     */
    public function show(Request $request, Banner $banner)
    {
        // Only show active banners
        if ($banner->status !== Banner::STATUS_ACTIVE) {
            return ApiResponse::error(
                'Banner not found',
                [],
                404
            );
        }

        return ApiResponse::success(
            new BannerResource($banner),
            'Banner retrieved successfully'
        );
    }
}
