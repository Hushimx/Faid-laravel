<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
  /**
   * Show profile edit form
   */
  public function edit()
  {
    $user = Auth::user();
    return view('pages.profile.edit', compact('user'));
  }

  /**
   * Update profile data
   */
  public function update(Request $request)
  {
    $user = $request->user();

    $validated = $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
      'phone' => ['nullable', 'string', 'max:20'],
      'address' => ['nullable', 'string', 'max:255'],
      'profile_picture' => ['nullable', 'image'],
      'current_password' => ['nullable', 'required_with:new_password', 'string'],
      'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
    ]);

    // Handle password change
    if (!empty($validated['current_password'])) {
      if (!Hash::check($validated['current_password'], $user->password)) {
        return back()->withErrors(['current_password' => 'Current password is incorrect'])->withInput();
      }
      $validated['password'] = Hash::make($validated['new_password']);
    }

    // Remove helper fields
    unset($validated['current_password'], $validated['new_password'], $validated['new_password_confirmation']);

    // Handle profile picture
    if ($request->hasFile('profile_picture')) {
      $path = uploadImage(
        $request->file('profile_picture'),
        'profile-pictures',
        ['width' => 300, 'height' => 300],
        $user->profile_picture
      );

      if (!$path) {
        return back()->with('error', 'Failed to upload profile picture')->withInput();
      }

      $validated['profile_picture'] = $path;
    }

    $user->update($validated);

    return redirect()->route('profile.edit')->with('success', 'Profile updated successfully');
  }

  /**
   * Update authenticated user's password
   */
  public function updatePassword(Request $request)
  {
    $user = $request->user();

    $validated = $request->validate([
      'current_password' => ['required', 'string'],
      'new_password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);

    if (!Hash::check($validated['current_password'], $user->password)) {
      return back()->withErrors(['current_password' => 'Current password is incorrect']);
    }

    $user->password = Hash::make($validated['new_password']);
    $user->save();

    return redirect()->route('profile.edit')->with('success', 'Password updated successfully');
  }

  /**
   * Update only the profile picture
   */
  public function updatePicture(Request $request)
  {
    $user = $request->user();

    $validated = $request->validate([
      'profile_picture' => ['required', 'image', 'max:2048'],
    ]);

    if ($request->hasFile('profile_picture')) {
      $path = uploadImage(
        $request->file('profile_picture'),
        'profile-pictures',
        ['width' => 300, 'height' => 300],
        $user->profile_picture
      );

      if (!$path) {
        return back()->with('error', 'Failed to upload profile picture');
      }

      $user->profile_picture = $path;
      $user->save();

      return redirect()->route('profile.edit')->with('success', 'Profile picture updated successfully');
    }

    return back()->with('error', 'No image uploaded');
  }
}
