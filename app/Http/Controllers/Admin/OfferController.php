<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class OfferController extends Controller
{
  public function index()
  {
    $offers = Offer::orderBy('created_at', 'desc')->paginate(20);
    return view('pages.offers', compact('offers'));
  }

  public function store(Request $request): RedirectResponse
  {
    $data = $request->validate([
      'image' => ['required', 'image', 'max:5120'],
      'status' => ['required', 'in:' . implode(',', Offer::statuses())],
    ]);

    $path = uploadImage($request->file('image'), 'offers', ['width' => 1200, 'height' => 600]);
    if (!$path) {
      return redirect()->back()->with('error', 'Failed to upload image');
    }

    Offer::create([
      'image' => $path,
      'status' => $data['status'],
    ]);

    return redirect()->route('offers.index')->with('success', 'Offer created');
  }

  public function update(Request $request, Offer $offer): RedirectResponse
  {
    $data = $request->validate([
      'image' => ['nullable', 'image', 'max:5120'],
      'status' => ['required', 'in:' . implode(',', Offer::statuses())],
    ]);

    if ($request->hasFile('image')) {
      $path = uploadImage($request->file('image'), 'offers', ['width' => 1200, 'height' => 600], $offer->image);
      if (!$path) {
        return redirect()->back()->with('error', 'Failed to upload image');
      }
      $offer->image = $path;
    }

    $offer->status = $data['status'];
    $offer->save();

    return redirect()->route('offers.index')->with('success', 'Offer updated');
  }

  public function destroy(Offer $offer): RedirectResponse
  {
    if ($offer->image) deleteFile($offer->image);
    $offer->delete();
    return redirect()->route('offers.index')->with('success', 'Offer deleted');
  }
}
