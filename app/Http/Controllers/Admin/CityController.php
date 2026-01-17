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
        
        $validated = $request->validate([
            'name' => 'required|array',
            'name.en' => 'required|string|max:255',
            'name.*' => 'nullable|string|max:255',
            'country_id' => 'required|exists:countries,id',
        ]);

        try {
            $city = new City();
            $city->name = normalize_translations($validated['name']);
            $city->country_id = $validated['country_id'];
            $city->save();

            return redirect()->back()->with('success', __('dashboard.City added successfully'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', __('dashboard.Failed to add city: :error', ['error' => $e->getMessage()]));
        }
    }

    public function update(Request $request, string $id)
    {
        $this->authorize('cities.edit');
        
        $validated = $request->validate([
            'name' => 'required|array',
            'name.en' => 'required|string|max:255',
            'name.*' => 'nullable|string|max:255',
            'country_id' => 'required|exists:countries,id',
        ]);

        try {
            $city = City::findOrFail($id);
            $city->name = normalize_translations($validated['name']);
            $city->country_id = $validated['country_id'];
            $city->save();

            return redirect()->back()->with('success', __('dashboard.City updated successfully'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', __('dashboard.Failed to update city: :error', ['error' => $e->getMessage()]));
        }
    }

    public function destroy(string $id)
    {
        $this->authorize('cities.delete');
        
        $city = City::findOrFail($id);
        $city->delete();

        return redirect()->back()->with('success', __('dashboard.City deleted successfully'));
    }
}