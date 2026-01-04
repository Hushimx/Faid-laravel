<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Allow all authenticated admin users to access dashboard
        // If you want to restrict access, ensure users have 'dashboard.view' permission
        // $this->authorize('dashboard.view');
        
        // Users Statistics
        $usersStats = [
            'total' => User::count(),
            'admins' => User::where('type', 'admin')->count(),
            'vendors' => User::where('type', 'vendor')->count(),
            'active' => User::where('status', 'active')->count(),
            'inactive' => User::where('status', 'inactive')->count(),
        ];

        // Products Statistics
        $productsStats = [
            'total' => 0, // Product::count(),
            'active' => 0, // Product::where('status', Product::STATUS_ACTIVE)->where('admin_status', '!=', Product::ADMIN_STATUS_SUSPENDED)->count(),
            'suspended' => 0, // Product::where('admin_status', Product::ADMIN_STATUS_SUSPENDED)->count(),
            'pending' => 0, // Product::where('status', Product::STATUS_PENDING)->count(),
            'draft' => 0, // Product::where('status', Product::STATUS_DRAFT)->count(),
            'out_of_stock' => 0, // Product::where('stock_quantity', '<=', 0)->count(),
        ];

        // Services Statistics
        $servicesStats = [
            'total' => Service::count(),
            'active' => Service::where('status', Service::STATUS_ACTIVE)
                ->where('admin_status', '!=', Service::ADMIN_STATUS_SUSPENDED)
                ->count(),
            'suspended' => Service::where('admin_status', Service::ADMIN_STATUS_SUSPENDED)->count(),
            'pending' => Service::where('status', Service::STATUS_PENDING)->count(),
            'draft' => Service::where('status', Service::STATUS_DRAFT)->count(),
        ];

        // Categories Statistics
        $categoriesStats = [
            'total' => Category::count(),
            'active' => Category::where('status', Category::STATUS_ACTIVE)->count(),
            'inactive' => Category::where('status', Category::STATUS_INACTIVE)->count(),
        ];

        // Countries & Cities Statistics
        $locationsStats = [
            'countries' => Country::count(),
            'cities' => City::count(),
        ];

        // Recent Activity (Last 30 days)
        $recentProducts = [];
        // $recentProducts = Product::with(['vendor', 'category'])
        //     ->orderBy('created_at', 'desc')
        //     ->limit(5)
        //     ->get();

        $recentServices = Service::with(['vendor', 'category'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Chart Data - Products & Services created over last 7 days
        $chartData = $this->getChartData();

        return view('dashboard', compact(
            'usersStats',
            'productsStats',
            'servicesStats',
            'categoriesStats',
            'locationsStats',
            'recentProducts',
            'recentServices',
            'chartData'
        ));
    }

    private function getChartData()
    {
        $days = 7;
        $dates = [];
        $productsData = [];
        $servicesData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dates[] = now()->subDays($i)->format('M d');

            $productsData[] = 0; // Product::whereDate('created_at', $date)->count();
            $servicesData[] = Service::whereDate('created_at', $date)->count();
        }

        return [
            'dates' => $dates,
            'products' => $productsData,
            'services' => $servicesData,
        ];
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Logged out successfully');
    }

    public function lang()
    {
        App::setLocale('ar');
        session(['lang' => 'ar']);
        return redirect()->back();
    }
}
