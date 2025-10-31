<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users with filters and search
     */
    /**
     * Common method to apply filters and search
     */
    private function applyFilters($query, Request $request)
    {
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%")
                    ->orWhere('phone', 'like', "%{$searchTerm}%");
            });
        }

        // Sort
        $sortField = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        return $query;
    }

    public function index(Request $request)
    {
        $query = User::query();
        $query = $this->applyFilters($query, $request);
        $users = $query->paginate(20);

        return view('pages.users.index', [
            'users' => $users,
            'type' => 'all',
            'filters' => [
                'status' => $request->status,
                'search' => $request->search,
                'sort_by' => $request->input('sort_by', 'created_at'),
                'sort_direction' => $request->input('sort_direction', 'desc')
            ]
        ]);
    }


    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('pages.users.create');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'type' => ['required', Rule::in(['admin', 'user', 'vendor'])],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'profile_picture' => ['nullable', 'image', 'max:1024'], // Max 1MB
        ]);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $validated['profile_picture'] = uploadImage(
                $request->file('profile_picture'),
                'profile-pictures',
                ['width' => 300, 'height' => 300]
            );

            if (!$validated['profile_picture']) {
                return back()
                    ->withInput()
                    ->with('error', 'Failed to upload profile picture');
            }
        }

        // Hash password
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully');
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $countries = Country::orderBy('name')->get();
        $cities = City::orderBy('name')->get();
        $user->load('vendorProfile');
        return view('pages.users.edit', compact('user', 'countries', 'cities'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'type' => ['required', Rule::in(['admin', 'user', 'vendor'])],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'profile_picture' => ['nullable', 'image'], // Max 1MB
        ]);

        // Additional validation if vendor
        $vendorValidated = [];
        if ($request->input('type') === 'vendor') {
            $vendorValidated = $request->validate([
                'vendor.country_id' => ['nullable', 'exists:countries,id'],
                'vendor.city_id' => ['nullable', 'exists:cities,id'],
                'vendor.lat' => ['nullable', 'numeric', 'between:-90,90'],
                'vendor.lng' => ['nullable', 'numeric', 'between:-180,180'],
                'vendor.banner' => ['nullable', 'image'],
                'vendor.bio' => ['nullable', 'string', 'max:255'],
                // meta can be structured inputs (array) or an advanced JSON string
                'vendor.meta' => ['nullable'],
            ]);
        }

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $validated['profile_picture'] = uploadImage(
                $request->file('profile_picture'),
                'profile-pictures',
                ['width' => 300, 'height' => 300],
                $user->profile_picture // سيتم حذف الصورة القديمة تلقائياً
            );

            if (!$validated['profile_picture']) {
                return back()
                    ->withInput()
                    ->with('error', 'Failed to upload profile picture');
            }
        }

        // Only update password if provided
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        // Upsert vendor profile when type is vendor
        if ($request->input('type') === 'vendor') {
            $vendorData = [
                'country_id' => data_get($vendorValidated, 'vendor.country_id'),
                'city_id' => data_get($vendorValidated, 'vendor.city_id'),
                'lat' => data_get($vendorValidated, 'vendor.lat'),
                'lng' => data_get($vendorValidated, 'vendor.lng'),
                'bio' => data_get($vendorValidated, 'vendor.bio'),
                'meta' => null,
            ];

            // Normalize meta (accept array of fields or JSON string)
            $metaInput = data_get($vendorValidated, 'vendor.meta');
            if (!is_null($metaInput) && $metaInput !== '') {
                if (is_array($metaInput)) {
                    $normalized = $metaInput;
                    // Normalize tags as array if provided as comma-separated string
                    if (isset($normalized['tags']) && is_string($normalized['tags'])) {
                        $tags = array_filter(array_map('trim', explode(',', $normalized['tags'])));
                        $normalized['tags'] = array_values(array_unique($tags));
                    }
                    $vendorData['meta'] = $normalized;
                } elseif (is_string($metaInput)) {
                    $decoded = json_decode($metaInput, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $vendorData['meta'] = $decoded;
                    } else {
                        return back()
                            ->withInput()
                            ->withErrors(['vendor.meta' => 'Meta must be valid JSON']);
                    }
                }
            }

            // Handle vendor banner upload
            if ($request->hasFile('vendor.banner')) {
                $vendorData['banner'] = uploadImage(
                    $request->file('vendor.banner'),
                    'vendor-banners',
                    ['width' => 1200, 'height' => 300],
                    optional($user->vendorProfile)->banner
                );

                if (!$vendorData['banner']) {
                    return back()
                        ->withInput()
                        ->with('error', 'Failed to upload vendor banner');
                }
            }

            $user->vendorProfile()->updateOrCreate(
                ['user_id' => $user->id],
                $vendorData
            );
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Don't allow deleting your own account
        if ($user->id === Auth::id()) {
            return redirect()
                ->route('users.index')
                ->with('error', 'You cannot delete your own account');
        }

        // Delete profile picture
        if ($user->profile_picture) {
            deleteFile($user->profile_picture);
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully');
    }
}
