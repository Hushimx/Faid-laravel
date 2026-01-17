<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class BannerController extends Controller
{
    /**
     * Display a listing of the banners with filters & stats.
     */
    public function index(Request $request): View
    {
        $this->authorize('banners.view');
        
        $filters = [
            'status' => $request->string('status')->toString(),
            'per_page' => $request->integer('per_page', 15),
        ];

        $perPageOptions = [15, 30, 50, 100];
        if (!in_array($filters['per_page'], $perPageOptions, true)) {
            $filters['per_page'] = 15;
        }

        $bannersQuery = Banner::query()
            ->orderByRaw('`order` IS NULL')
            ->orderBy('order', 'asc')
            ->latest('created_at');

        if ($filters['status'] && in_array($filters['status'], Banner::statuses(), true)) {
            $bannersQuery->where('status', $filters['status']);
        }

        $banners = $bannersQuery->paginate($filters['per_page'])->withQueryString();

        $stats = [
            'total' => Banner::count(),
            'active' => Banner::where('status', Banner::STATUS_ACTIVE)->count(),
            'inactive' => Banner::where('status', Banner::STATUS_INACTIVE)->count(),
        ];

        return view('pages.banners', compact('banners', 'stats', 'filters', 'perPageOptions'));
    }

    /**
     * Store a newly created banner.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('banners.create');
        
        try {
            $data = $this->validateData($request);

            // Image is required for new banners
            if (!$request->hasFile('image')) {
                return redirect()->back()->withInput($request->except('image'))
                    ->with('error', __('dashboard.Banner image is required'));
            }

            $banner = new Banner();
            $banner->link = $data['link'] ?? null;
            $banner->status = $data['status'];
            $banner->order = $data['order'] ?? null;

            $path = uploadImage($request->file('image'), 'banners', ['width' => 1200, 'height' => 600]);

            if (!$path) {
                \Log::error('Banner image upload returned null', [
                    'file_name' => $request->file('image')->getClientOriginalName(),
                    'file_size' => $request->file('image')->getSize(),
                    'mime_type' => $request->file('image')->getMimeType(),
                ]);
                return redirect()->back()->withInput($request->except('image'))
                    ->with('error', __('dashboard.Banner image upload failed. Please check the logs for details.'));
            }

            $banner->image = $path;
            $banner->save();

            return redirect()->route('banners.index')->with('success', __('dashboard.Banner created successfully'));
        } catch (\Exception $e) {
            \Log::error('Banner creation failed: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()
                ->withInput($request->except('image'))
                ->with('error', __('dashboard.Failed to create banner: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Update the specified banner.
     */
    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $this->authorize('banners.edit');
        
        $data = $this->validateData($request);
        $banner->link = $data['link'];
        $banner->status = $data['status'];
        $banner->order = $data['order'] ?? null;

        if ($request->hasFile('image')) {
            $path = uploadImage($request->file('image'), 'banners', ['width' => 1200, 'height' => 600], $banner->image);

            if (!$path) {
                return redirect()->back()->withInput($request->except('image'))
                    ->with('error', __('dashboard.Banner image upload failed'));
            }

            $banner->image = $path;
        }

        $banner->save();

        return redirect()->route('banners.index')->with('success', __('dashboard.Banner updated successfully'));
    }

    /**
     * Remove the specified banner.
     */
    public function destroy(Banner $banner): RedirectResponse
    {
        $this->authorize('banners.delete');
        
        if ($banner->image) {
            deleteFile($banner->image);
        }

        $banner->delete();

        return redirect()->route('banners.index')->with('success', __('dashboard.Banner deleted successfully'));
    }

    /**
     * Update the order of banners.
     */
    public function updateOrder(Request $request)
    {
        $this->authorize('banners.edit');
        
        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['required', 'integer', 'exists:banners,id'],
        ]);

        // Get current order
        $currentOrder = Banner::whereIn('id', $request->order)
            ->pluck('order', 'id')
            ->toArray();
        
        $orderChanged = false;
        foreach ($request->order as $index => $bannerId) {
            $newOrder = $index + 1;
            if (!isset($currentOrder[$bannerId]) || $currentOrder[$bannerId] != $newOrder) {
                $orderChanged = true;
                Banner::where('id', $bannerId)->update(['order' => $newOrder]);
            }
        }

        return response()->json([
            'success' => true,
            'changed' => $orderChanged,
            'message' => $orderChanged 
                ? __('dashboard.Banner order updated successfully')
                : 'Order unchanged',
        ]);
    }

    /**
     * Validate incoming request data for store & update.
     */
    protected function validateData(Request $request): array
    {
        $rules = [
            'image' => ['nullable', 'image', 'max:5120'], // 5MB max
            'link' => ['nullable', 'url', 'max:255'],
            'status' => ['required', Rule::in(Banner::statuses())],
            'order' => ['nullable', 'integer', 'min:0'],
        ];

        $data = $request->validate($rules);

        return $data;
    }
}
