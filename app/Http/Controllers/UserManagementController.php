<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserNotification;
use App\Services\BadgeService;
use App\Services\RaffleService;
use App\Services\UniqueIdGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserManagementController extends Controller
{
    public function index()
    {
        return view('pages.users', [
            'users' => User::orderByDesc('points')->orderBy('created_at')->get(),
            'familyHeads' => User::where('is_head_of_family', true)->orderBy('last_name')->orderBy('first_name')->get(),
        ]);
    }

    public function edit(User $user)
    {
        return view('pages.user-edit', [
            'user' => $user,
            'familyHeads' => User::where('is_head_of_family', true)
                ->whereKeyNot($user->id)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(),
        ]);
    }

    public function create()
    {
        return view('pages.user-create', [
            'familyHeads' => User::where('is_head_of_family', true)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProfile($request, creating: true);
        $data['name'] = $this->fullName($data);
        $data['role'] = 'resident';
        $data['is_verified'] = true;
        $data['password'] = Hash::make($data['password']);
        $data['gender_other'] = $data['gender'] === 'others' ? ($data['gender_other'] ?? null) : null;
        $data['is_student'] = (bool) ($data['is_student'] ?? false);
        $data['school'] = $data['is_student'] ? ($data['school'] ?? null) : null;
        $data['points'] = 0;
        $data['barangay'] = 'San Jose';
        $data['city'] = 'Iriga City';
        $data['province'] = 'Camarines Sur';
        $data['country'] = 'Philippines';
        $data['postal_code'] = '4431';

        if (! empty($data['head_of_family_id'])) {
            $data['head_of_family_name'] = User::findOrFail($data['head_of_family_id'])->full_name;
        }

        $user = User::create($data);
        $user->forceFill(['unique_id' => UniqueIdGenerator::for($user)])->save();
        RaffleService::ensureEntry($user);
        UserNotification::create([
            'user_id' => $user->id,
            'title' => 'Account created by an official',
            'body' => 'An official created your resident account. You can now sign in using the credentials provided to you.',
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('users.index')->with('success', "Created resident account for {$user->full_name}.");
    }

    public function update(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot change your own account from here.');
        }

        $positions = config('talafair.official_positions');
        $data = $this->validateProfile($request);

        if ($data['role'] === 'official' && ! in_array($data['official_position'], array_keys($positions[$data['official_group']]['positions']), true)) {
            return back()->withErrors(['official_position' => 'Select a position from the selected official group.'])->withInput();
        }

        if ($data['role'] === 'official') {
            $limit = config("talafair.official_position_limits.{$data['official_group']}.{$data['official_position']}");
            if ($limit !== null && User::where('role', 'official')->where('official_group', $data['official_group'])->where('official_position', $data['official_position'])->whereKeyNot($user->id)->count() >= $limit) {
                return back()->withErrors(['official_position' => "The selected position is already filled (maximum {$limit})."])->withInput();
            }
        }

        $user->role = $data['role'];
        $user->official_group = $data['role'] === 'official' ? $data['official_group'] : null;
        $user->official_position = $data['role'] === 'official' ? $data['official_position'] : null;
        $user->fill(collect($data)->only([
            'first_name', 'middle_name', 'last_name', 'suffix', 'gender', 'gender_other', 'birthdate',
            'contact_number', 'is_student', 'school', 'occupation', 'house_no', 'street', 'zone',
            'email', 'head_of_family_id', 'head_of_family_name', 'is_head_of_family',
        ])->all());
        if ($user->is_head_of_family) {
            $user->head_of_family_id = null;
            $user->head_of_family_name = null;
        }
        $user->head_of_family_name = ! empty($data['head_of_family_id'])
            ? User::find($data['head_of_family_id'])?->full_name
            : null;
        $user->name = $this->fullName($data);
        if (filled($data['password'] ?? null)) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();
        if (filled($data['password'] ?? null)) {
            $user->writeAuditLog('password_updated', [], ['password_changed' => true]);
        }
        BadgeService::awardEligible($user);
        UserNotification::create([
            'user_id' => $user->id,
            'title' => 'Account information updated',
            'body' => 'An official updated your account information.',
            'created_by' => $request->user()->id,
        ]);

        return back()->with('success', "Updated {$user->name}'s account.");
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own account from here.');
        }

        $user->delete();

        return back()->with('success', "Deleted {$user->name}'s account.");
    }

    public function toggleVerification(Request $request, User $user)
    {
        if ($user->isOfficial()) {
            return back()->with('error', 'Official accounts do not need resident verification.');
        }

        $user->update(['is_verified' => ! $user->is_verified]);
        UserNotification::create([
            'user_id' => $user->id,
            'title' => $user->is_verified ? 'Account verified' : 'Account verification changed',
            'body' => $user->is_verified
                ? 'Your resident account has been verified by an official.'
                : 'Your resident account was marked as unverified by an official.',
            'created_by' => $request->user()->id,
        ]);

        return back()->with('success', $user->is_verified
            ? "Verified {$user->name}'s resident account."
            : "Marked {$user->name}'s account as unverified.");
    }

    private function validateProfile(Request $request, bool $creating = false): array
    {
        $positions = config('talafair.official_positions');

        return $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'gender' => ['required', Rule::in(['female', 'male', 'others'])],
            'gender_other' => ['nullable', 'required_if:gender,others', 'string', 'max:255'],
            'birthdate' => ['required', 'date', 'before_or_equal:today'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'is_student' => ['nullable', 'boolean'],
            'school' => ['nullable', 'required_if:is_student,1', 'string', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'house_no' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'zone' => ['required', 'string', 'max:10'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->route('user'))],
            'head_of_family_id' => [
                'nullable',
                Rule::requiredIf(fn () => $request->input('role') === 'resident' && ! $request->boolean('is_head_of_family')),
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('is_head_of_family', true)),
            ],
            'head_of_family_name' => ['nullable', 'string', 'max:255'],
            'is_head_of_family' => ['nullable', 'boolean'],
            'role' => [$creating ? 'nullable' : 'required', 'in:resident,guest,official'],
            'official_group' => ['nullable', 'required_if:role,official', 'in:' . implode(',', array_keys($positions))],
            'official_position' => ['nullable', 'required_if:role,official', 'in:' . implode(',', collect($positions)->flatMap(fn ($group) => array_keys($group['positions']))->all())],
            'points' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'username' => [$creating ? 'required' : 'nullable', 'string', 'min:3', 'max:30', 'alpha_dash', Rule::unique('users', 'username')->ignore($request->route('user'))],
            'password' => [$creating ? 'required' : 'nullable', 'confirmed', Password::defaults()],
        ], [
            'head_of_family_id.required' => 'Select a registered Household Head for this resident member.',
            'head_of_family_id.exists' => 'Select a valid registered Household Head.',
        ]);
    }

    private function fullName(array $data): string
    {
        return trim(collect([$data['first_name'], $data['middle_name'] ?? null, $data['last_name'], $data['suffix'] ?? null])->filter()->implode(' '));
    }
}
