<?php

namespace App\Http\Controllers\Admin;

use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CityController extends Controller
{
    public function index()
    {
        $this->authorize('cities.view');
        
        $cities = City::with('country')->latest()->paginate(15);
        $countries = Country::all();
        return view('pages.cities', compact('cities', 'countries'));
    }

    public function store(Request $request)
    {
        $this->authorize('cities.create');
        
        $request->validate([
            'name' => 'required|array',
            'name.en' => 'required|string|max:255',
            'name.*' => 'nullable|string|max:255',
            'country_id' => 'required|exists:countries,id',
        ]);

        $city = new City();
        $city->name = normalize_translations($request->input('name'));
        $city->country_id = $request->country_id;
        $city->save();

        return redirect()->back()->with('success', __('dashboard.City added successfully'));
    }

    public function update(Request $request, string $id)
    {
        $this->authorize('cities.edit');
        
        $request->validate([
            'name' => 'required|array',
            'name.en' => 'required|string|max:255',
            'name.*' => 'nullable|string|max:255',
            'country_id' => 'required|exists:countries,id',
        ]);

        $city = City::findOrFail($id);
        $city->name = normalize_translations($request->input('name'));
        $city->country_id = $request->country_id;
        $city->save();

        return redirect()->back()->with('success', __('dashboard.City updated successfully'));
    }

    public function destroy(string $id)
    {
        $this->authorize('cities.delete');
        
        $city = City::findOrFail($id);
        $city->delete();

        return redirect()->back()->with('success', __('dashboard.City deleted successfully'));
    }
}