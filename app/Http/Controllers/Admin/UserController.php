<?php

namespace App\Http\Controllers\Admin;

use App\Models\City;
use App\Models\User;
use App\Models\Country;
use Illuminate\Http\Request;
use App\Models\VendorProfile;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                    ->orWhere('last_name', 'like', "%{$searchTerm}%")
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

    /**
     * Get redirect route based on user type
     */
    private function getRedirectRouteForType($type)
    {
        return match ($type) {
            'admin' => 'users.admins',
            'vendor' => 'users.vendors',
            'user' => 'users.users',
            default => 'users.all',
        };
    }

    public function index(Request $request)
    {
        $this->authorize('users.view');
        
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
     * Display a listing of admins
     */
    public function admins(Request $request)
    {
        $this->authorize('users.view');
        
        $query = User::where('type', 'admin');
        $query = $this->applyFilters($query, $request);
        $users = $query->paginate(20);

        return view('pages.users.index', [
            'users' => $users,
            'type' => 'admin',
            'filters' => [
                'status' => $request->status,
                'search' => $request->search,
                'sort_by' => $request->input('sort_by', 'created_at'),
                'sort_direction' => $request->input('sort_direction', 'desc')
            ]
        ]);
    }

    /**
     * Display a listing of regular users
     */
    public function users(Request $request)
    {
        $this->authorize('users.view');
        
        $query = User::where('type', 'user');
        $query = $this->applyFilters($query, $request);
        $users = $query->paginate(20);

        return view('pages.users.index', [
            'users' => $users,
            'type' => 'user',
            'filters' => [
                'status' => $request->status,
                'search' => $request->search,
                'sort_by' => $request->input('sort_by', 'created_at'),
                'sort_direction' => $request->input('sort_direction', 'desc')
            ]
        ]);
    }

    /**
     * Display a listing of vendors
     */
    public function vendors(Request $request)
    {
        $this->authorize('users.view');
        
        $query = User::where('type', 'vendor');
        $query = $this->applyFilters($query, $request);
        $users = $query->paginate(20);

        return view('pages.users.index', [
            'users' => $users,
            'type' => 'vendor',
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
    public function create(Request $request)
    {
        $this->authorize('users.create');
        
        $type = $request->get('type', 'user'); // Default to 'user' if not provided

        // Validate type
        if (!in_array($type, ['admin', 'user', 'vendor'])) {
            $type = 'user';
        }

        $countries = Country::orderBy('name')->get();
        $cities = City::orderBy('name')->get();
        $roles = Role::all();

        return view('pages.users.create', compact('type', 'countries', 'cities', 'roles'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $this->authorize('users.create');
        
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'regex:/^[a-zA-Z0-9]+$/'],
            'type' => ['required', Rule::in(['admin', 'user', 'vendor'])],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'profile_picture' => ['nullable', 'image', 'max:1024'], // Max 1MB
            'roles' => ['required_if:type,admin', 'array'],
            'roles.*' => ['required_with:roles', 'exists:roles,name'],
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
                ['width' => 300, 'height' => 300]
            );

            if (!$validated['profile_picture']) {
                return back()
                    ->withInput()
                    ->with('error', __('dashboard.Failed to upload profile picture'));
            }
        }

        // Hash password
        $validated['password'] = Hash::make($validated['password']);

        if ($request->has('roles')) {
            unset($validated['roles']);
        }

        $user = User::create($validated);

        if ($request->has('roles')) {
            $user->assignRole($request->input('roles'));
        }


        // Create vendor profile if type is vendor
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
            $metaFromRequest = $request->input('vendor.meta', []);

            // Build meta from individual fields if they exist
            $meta = [];
            if (isset($metaFromRequest['website']) && !empty($metaFromRequest['website'])) {
                $meta['website'] = $metaFromRequest['website'];
            }
            if (isset($metaFromRequest['whatsapp']) && !empty($metaFromRequest['whatsapp'])) {
                $meta['whatsapp'] = $metaFromRequest['whatsapp'];
            }
            if (isset($metaFromRequest['instagram']) && !empty($metaFromRequest['instagram'])) {
                $meta['instagram'] = $metaFromRequest['instagram'];
            }
            if (isset($metaFromRequest['facebook']) && !empty($metaFromRequest['facebook'])) {
                $meta['facebook'] = $metaFromRequest['facebook'];
            }
            if (isset($metaFromRequest['tags']) && !empty($metaFromRequest['tags'])) {
                if (is_string($metaFromRequest['tags'])) {
                    $tags = array_filter(array_map('trim', explode(',', $metaFromRequest['tags'])));
                    $meta['tags'] = array_values(array_unique($tags));
                } else {
                    $meta['tags'] = $metaFromRequest['tags'];
                }
            }

            // If meta is provided as JSON string, use it instead
            if (!is_null($metaInput) && $metaInput !== '') {
                if (is_string($metaInput)) {
                    $decoded = json_decode($metaInput, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $meta = array_merge($meta, $decoded);
                    }
                } elseif (is_array($metaInput)) {
                    $meta = array_merge($meta, $metaInput);
                }
            }

            if (!empty($meta)) {
                $vendorData['meta'] = $meta;
            }

            // Handle vendor banner upload
            if ($request->hasFile('vendor.banner')) {
                $vendorData['banner'] = uploadImage(
                    $request->file('vendor.banner'),
                    'vendor-banners',
                    ['width' => 1200, 'height' => 300]
                );

                if (!$vendorData['banner']) {
                    return back()
                        ->withInput()
                        ->with('error', __('dashboard.Failed to upload vendor banner'));
                }
            }

            $user->vendorProfile()->create($vendorData);
        }

        return redirect()
            ->route($this->getRedirectRouteForType($validated['type']))
            ->with('success', __('dashboard.User created successfully'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $this->authorize('users.edit');
        
        $countries = Country::orderBy('name')->get();
        $cities = City::orderBy('name')->get();
        $user->load('vendorProfile');
        $user->load('roles');
        $roles = Role::all();
        return view('pages.users.edit', compact('user', 'countries', 'cities', 'roles'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('users.edit');
        
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'regex:/^[a-zA-Z0-9]+$/'],
            'type' => ['required', Rule::in(['admin', 'user', 'vendor'])],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'profile_picture' => ['nullable', 'image'], // Max 1MB
            'roles' => ['required_if:type,admin', 'array'],
            'roles.*' => ['required_with:roles', 'exists:roles,name'],
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
                    ->with('error', __('dashboard.Failed to upload profile picture'));
            }
        }

        // Only update password if provided
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Remove roles from validated data as it will be handled separately
        if (isset($validated['roles'])) {
            unset($validated['roles']);
        }

        $user->update($validated);

        // Sync roles if user is admin
        if ($request->input('type') === 'admin') {
            if ($request->has('roles')) {
                $user->syncRoles($request->input('roles', []));
            } else {
                // If admin but no roles selected, remove all roles
                $user->syncRoles([]);
            }
        } else {
            // If user is not admin, remove all roles
            $user->syncRoles([]);
        }

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
                            ->withErrors(['vendor.meta' => __('dashboard.Meta must be valid JSON')]);
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
                        ->with('error', __('dashboard.Failed to upload vendor banner'));
                }
            }

            $user->vendorProfile()->updateOrCreate(
                ['user_id' => $user->id],
                $vendorData
            );
        }

        return redirect()
            ->route($this->getRedirectRouteForType($user->type))
            ->with('success', __('dashboard.User updated successfully'));
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        $this->authorize('users.delete');
        
        // Don't allow deleting your own account
        if ($user->id === Auth::id()) {
            return redirect()
                ->route($this->getRedirectRouteForType($user->type))
                ->with('error', __('dashboard.You cannot delete your own account'));
        }

        // Store user type before deletion
        $userType = $user->type;

        // Delete profile picture
        if ($user->profile_picture) {
            deleteFile($user->profile_picture);
        }

        $user->delete();

        return redirect()
            ->route($this->getRedirectRouteForType($userType))
            ->with('success', __('dashboard.User deleted successfully'));
    }
}
