<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Models\City;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class CityController extends Controller
{
    /**
     * Display a listing of cities.
     */
    public function index(Request $request)
    {
        $filters = [
            'country_id' => $request->integer('country_id') ?: null,
            'search' => $request->string('search')->toString(),
            'per_page' => $request->integer('per_page', 15),
        ];

        $query = City::query()->with('country');

        // Filter by country
        if ($filters['country_id']) {
            $query->where('country_id', $filters['country_id']);
        }

        // Search by name
        if ($filters['search']) {
            $locale = app()->getLocale();
            $search = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($search, $locale) {
                $q->where("name->{$locale}", 'like', $search)
                    ->orWhere("name->en", 'like', $search)
                    ->orWhere("name->ar", 'like', $search);
            });
        }

        $cities = $query->orderBy('name->en')->paginate($filters['per_page']);

        $cities->setCollection(
            $cities->getCollection()->map(fn($city) => new CityResource($city))
        );

        

        return ApiResponse::paginated(
            $cities,
            'Cities retrieved successfully',
            200,
            ['applied_filters' => $filters]
        );
    }
}
