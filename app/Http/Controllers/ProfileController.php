<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use App\Models\UserNotification;

class ProfileController extends Controller
{
    /**
     * /account already renders the profile screen (photo, password and details forms),
     * and AccountController supplies the rank + spin statistics it needs.
     */
    public function edit(): RedirectResponse
    {
        return redirect()->route('account');
    }

    /** Profile details + address. */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isOfficial(), 403, 'Only officials can edit account information.');

        $data = $request->validate([
            'first_name'     => ['required', 'string', 'max:255'],
            'middle_name'    => ['nullable', 'string', 'max:255'],
            'last_name'      => ['required', 'string', 'max:255'],
            'suffix'         => ['nullable', 'string', 'max:20'],
            'gender'         => ['required', Rule::in(['female', 'male', 'others'])],
            'gender_other'   => ['nullable', 'required_if:gender,others', 'string', 'max:255'],
            'birthdate'      => ['required', 'date', 'before_or_equal:today'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'is_student'     => ['nullable', 'boolean'],
            'school'         => ['nullable', 'required_if:is_student,1', 'string', 'max:255'],
            'occupation'     => ['nullable', 'string', 'max:255'],
            'house_no'       => ['required', 'string', 'max:255'],
            'street'         => ['required', 'string', 'max:255'],
            'zone'           => ['required', 'string', 'max:10'],
            'email'          => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        if ($user->isOfficial()) {
            $positionGroups = config('talafair.official_positions');
            $officialData = $request->validate([
                'official_group' => ['required', 'in:' . implode(',', array_keys($positionGroups))],
                'official_position' => ['required', 'in:' . implode(',', collect($positionGroups)->flatMap(fn ($group) => array_keys($group['positions']))->all())],
            ]);

            if (! in_array($officialData['official_position'], array_keys($positionGroups[$officialData['official_group']]['positions']), true)) {
                return back()->withErrors(['official_position' => 'Select a position from the selected official group.'])->withInput();
            }

            $data = array_merge($data, $officialData);

            $limit = config("talafair.official_position_limits.{$data['official_group']}.{$data['official_position']}");
            if ($limit !== null && User::where('role', 'official')->where('official_group', $data['official_group'])->where('official_position', $data['official_position'])->whereKeyNot($user->id)->count() >= $limit) {
                return back()->withErrors(['official_position' => "The selected position is already filled (maximum {$limit})."])->withInput();
            }
        }

        $data['gender_other'] = $data['gender'] === 'others' ? $data['gender_other'] : null;
        $data['is_student'] = (bool) ($data['is_student'] ?? false);
        $data['school'] = $data['is_student'] ? ($data['school'] ?? null) : null;
        $data['name'] = trim(collect([
            $data['first_name'], $data['middle_name'] ?? null, $data['last_name'], $data['suffix'] ?? null,
        ])->filter()->implode(' '));

        $user->fill($data)->save();   // Auditable trait logs who changed what
        UserNotification::create([
            'user_id' => $user->id,
            'title' => 'Profile information updated',
            'body' => 'Your profile information was updated successfully.',
            'created_by' => $user->id,
        ]);

        return back()->with('success', 'Profile updated.');
    }

    /** Profile picture. */
    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $user = $request->user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
        $user->save();
        UserNotification::create([
            'user_id' => $user->id,
            'title' => 'Profile picture updated',
            'body' => 'Your profile picture was updated successfully.',
            'created_by' => $user->id,
        ]);

        return back()->with('success', 'Profile picture updated.');
    }

    public function destroyPhoto(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->avatar_path = null;
            $user->save();
            UserNotification::create([
                'user_id' => $user->id,
                'title' => 'Profile picture removed',
                'body' => 'Your profile picture was removed.',
                'created_by' => $user->id,
            ]);
        }

        return back()->with('success', 'Profile picture removed.');
    }

    /** Change password — this is the one that has to actually work. */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::defaults()],
        ], [
            'current_password.current_password' => 'That is not your current password.',
        ]);

        $user = $request->user();
        $user->password = $validated['password'];   // 'hashed' cast handles Hash::make
        $user->save();
        UserNotification::create([
            'user_id' => $user->id,
            'title' => 'Password changed',
            'body' => 'Your account password was changed successfully.',
            'created_by' => $user->id,
        ]);

        // Keep the current session valid after the password hash changes
        $request->session()->regenerate();

        return back()->with('success', 'Password changed.');
    }
}