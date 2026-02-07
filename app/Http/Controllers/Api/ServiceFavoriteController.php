<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class ServiceFavoriteController extends Controller
{
    /**
     * List current user's favorited services (paginated).
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = $request->integer('per_page', 20);
        $includeMedia = $request->boolean('include_media', true);

        $query = Service::query()
            ->whereHas('favoritedByUsers', fn($q) => $q->where('users.id', $user->id))
            ->with(['category', 'vendor'])
            ->withCount('images')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('status', Service::STATUS_ACTIVE)
            ->whereNull('admin_status')
            ->latest();

        if ($includeMedia) {
            $query->with(['images', 'videos']);
        }

        $services = $query->paginate($perPage);
        $services->setCollection(
            $services->getCollection()->map(fn($service) => new ServiceResource($service))
        );

        return ApiResponse::paginated($services, 'Favorites retrieved successfully');
    }

    /**
     * Add service to favorites (idempotent).
     */
    public function store(Request $request, Service $service)
    {
        $user = $request->user();

        if (!$service->isVisible()) {
            return ApiResponse::error('Service not found', [], 404);
        }

        $exists = $user->favoriteServices()->where('services.id', $service->id)->exists();
        if (!$exists) {
            $user->favoriteServices()->attach($service->id);
            return ApiResponse::success(
                ['is_favorited' => true],
                'Added to favorites',
                201
            );
        }

        return ApiResponse::success(['is_favorited' => true], 'Already in favorites', 200);
    }

    /**
     * Remove service from favorites.
     */
    public function destroy(Request $request, Service $service)
    {
        $user = $request->user();
        $user->favoriteServices()->detach($service->id);

        return ApiResponse::success(['is_favorited' => false], 'Removed from favorites');
    }
}
