<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Show the profile edit form.
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user profile details.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('profile_image')) {
            // Delete old profile image if exists
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // Store new profile image
            if ($user->employee) {
                $path = $request->file('profile_image')->store('employee_photos', 'public');
            } else {
                $path = $request->file('profile_image')->store('profile_images', 'public');
            }
            $user->profile_image = $path;
        }

        $user->save();

        if ($user->employee) {
            $employee = $user->employee;
            $employee->name = $user->name;
            $employee->email = $user->email;
            if ($request->filled('password')) {
                $employee->password = $request->password; // raw password
            }
            if ($request->hasFile('profile_image')) {
                $employee->photo = $user->profile_image;
            }
            $employee->save();
        }

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
    }
}
