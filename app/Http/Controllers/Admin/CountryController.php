<?php

namespace App\Http\Controllers\Admin;

use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CountryController extends Controller
{
    public function index()
    {
        $this->authorize('countries.view');
        
        $countries = Country::latest()->paginate(15);
        return view('pages.countries', compact('countries'));
    }

    public function store(Request $request)
    {
        $this->authorize('countries.create');
        
        $request->validate([
            'name' => 'required|array',
            'name.en' => 'required|string|max:255',
            'name.*' => 'nullable|string|max:255',
        ]);

        $country = new Country();
        $country->name = normalize_translations($request->input('name'));
        $country->save();

        return redirect()->back()->with('success', __('dashboard.Country added successfully'));
    }

    public function update(Request $request, string $id)
    {
        $this->authorize('countries.edit');
        
        $request->validate([
            'name' => 'required|array',
            'name.en' => 'required|string|max:255',
            'name.*' => 'nullable|string|max:255',
        ]);

        $country = Country::findOrFail($id);
        $country->name = normalize_translations($request->input('name'));
        $country->save();

        return redirect()->back()->with('success', __('dashboard.Country updated successfully'));
    }

    public function destroy(string $id)
    {
        $this->authorize('countries.delete');
        
        // Check if there are any cities associated with the country
        $cities = City::where('country_id', $id)->get();
        if ($cities->count() > 0) {
            return redirect()->back()->with('error', __('dashboard.You cannot delete a country that has cities'));
        }

        $country = Country::findOrFail($id);
        $country->delete();

        return redirect()->back()->with('success', __('dashboard.Country deleted successfully'));
    }
}
