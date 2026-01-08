<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VendorApplication;
use App\Models\VendorProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VendorApplicationController extends Controller
{
    public function __construct()
    {
        // Allow only admins to access
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if ($user->type !== 'admin') {
                abort(403, 'Unauthorized access');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of vendor applications with filters
     */
    public function index(Request $request): View
    {
        $query = VendorApplication::with(['user', 'category', 'reviewer']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', $search)
                  ->orWhere('bio', 'like', $search)
                  ->orWhere('custom_category', 'like', $search)
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where(function($q) use ($search) {
                          $q->where('first_name', 'like', $search)
                            ->orWhere('last_name', 'like', $search)
                            ->orWhere('email', 'like', $search);
                      });
                  })
                  ->orWhere('city', 'like', $search)
                  ->orWhereHas('category', function ($categoryQuery) use ($search) {
                      $categoryQuery->where('name', 'like', $search);
                  });
            });
        }

        // Sort
        $sortField = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $applications = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => VendorApplication::count(),
            'pending' => VendorApplication::where('status', 'pending')->count(),
            'approved' => VendorApplication::where('status', 'approved')->count(),
            'rejected' => VendorApplication::where('status', 'rejected')->count(),
        ];

        return view('pages.vendor-applications.index', [
            'applications' => $applications,
            'stats' => $stats,
            'filters' => [
                'status' => $request->status,
                'search' => $request->search,
                'sort_by' => $sortField,
                'sort_direction' => $sortDirection,
            ]
        ]);
    }

    /**
     * Display the specified vendor application
     */
    public function show(VendorApplication $vendorApplication): View
    {
        $vendorApplication->loadMissing(['user', 'category', 'reviewer']);

        return view('pages.vendor-applications.show', [
            'application' => $vendorApplication
        ]);
    }

    /**
     * Approve a vendor application
     */
    public function approve(Request $request, VendorApplication $vendorApplication): RedirectResponse
    {
        if ($vendorApplication->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'This application has already been processed');
        }

        try {
            DB::transaction(function () use ($vendorApplication) {
                $user = $vendorApplication->user;

                // Update user type to vendor
                $user->update(['type' => 'vendor']);

                // Create vendor profile from application data
                VendorProfile::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'bio' => $vendorApplication->bio,
                        'category_id' => $vendorApplication->category_id,
                    ]
                );

                // Update application status
                $vendorApplication->update([
                    'status' => 'approved',
                    'reviewed_by' => Auth::id(),
                    'reviewed_at' => now(),
                ]);
            });

            return redirect()->route('vendor-applications.index')
                ->with('success', 'Vendor application approved successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to approve application: ' . $e->getMessage());
        }
    }

    /**
     * Reject a vendor application
     */
    public function reject(Request $request, VendorApplication $vendorApplication): RedirectResponse
    {
        if ($vendorApplication->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'This application has already been processed');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $vendorApplication->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('vendor-applications.index')
            ->with('success', 'Vendor application rejected');
    }
}


