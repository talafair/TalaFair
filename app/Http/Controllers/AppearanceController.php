<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Officials only: the home page background image. */
class AppearanceController extends Controller
{
    public function edit()
    {
        return view('pages.appearance', [
            'backgroundUrl' => Setting::url('home_background'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'background' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);

        if ($old = Setting::get('home_background')) {
            Storage::disk('public')->delete($old);
        }

        Setting::put('home_background', $request->file('background')->store('backgrounds', 'public'));

        return back()->with('success', 'Background updated.');
    }

    public function destroy(): RedirectResponse
    {
        if ($old = Setting::get('home_background')) {
            Storage::disk('public')->delete($old);
        }

        Setting::put('home_background', null);

        return back()->with('success', 'Background removed.');
    }
}